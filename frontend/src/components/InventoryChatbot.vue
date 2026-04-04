<script setup>
import { ref, watch, onMounted, computed, nextTick, onUnmounted } from 'vue';
import { useAuthStore } from '@/store/auth.store';
import {
  Bot as BotIcon,
  Send as SendIcon,
  X as XIcon,
  MessageSquare as ChatIcon,
  Sparkles as SparklesIcon,
  Copy as CopyIcon,
  RefreshCw as RefreshIcon,
  Trash2 as TrashIcon,
  Maximize2 as MaximizeIcon,
  Zap as ZapIcon,
  AlertCircle as AlertCircleIcon,
  ChevronDown as ChevronDownIcon,
  ChevronUp as ChevronUpIcon,
  Clock as ClockIcon,
} from 'lucide-vue-next';
import ChatbotApi from '@/api/chatbot.api';

// ─── Auth & Role ────────────────────────────────────────────────
const authStore = useAuthStore();
const isLoggedIn = computed(() => !!authStore.user);
const currentRoleName = computed(() => {
  const roles = { 1: 'Admin', 2: 'Manager', 3: 'Sales' };
  return roles[authStore.user?.role] || 'User';
});

// ─── State ──────────────────────────────────────────────────────
const isOpen = ref(false);
const query = ref('');
const isTyping = ref(false);
const messageBox = ref(null);
const messages = ref([]);
const suggestions = ref([]);
const queryHistory = ref([]);
const historyIndex = ref(-1);
const isMinimized = ref(false);
const responseCache = new Map();

// Quick Actions
const quickActions = [
  { label: 'View Users', icon: 'users', query: 'Show all users' },
  { label: 'Inventory Status', icon: 'package', query: 'Show inventory status' },
  { label: 'Branch Summary', icon: 'building', query: 'Show branch summary' },
  { label: 'Recent Orders', icon: 'clock', query: 'Show recent orders' },
];

// ─── Lifecycle ──────────────────────────────────────────────────
const toggleChat = () => {
  isOpen.value = !isOpen.value;
  
  if (isOpen.value) {
    // Opening chat - initialize fresh
    isMinimized.value = false;
    if (messages.value.length === 0) {
      autoWelcome();
      loadSuggestions();
      loadQueryHistory();
    }
    loadCachedMessages();
  } else {
    // Closing chat - clear all messages for privacy
    messages.value = [];
    responseCache.clear();
    localStorage.removeItem('chatbot_messages');
    query.value = '';
    historyIndex.value = -1;
  }
};

const autoWelcome = () => {
  const greetings = {
    1: 'Hello Admin! I can help you with users, branch summaries, and full inventory audits. What list do you need today?',
    2: 'Manager, welcome. How can I help with your assigned branches and transfers?',
    3: 'Welcome back. Need your order list for this month or product lookup?'
  };

  messages.value.push({
    id: generateId(),
    role: 'bot',
    content: greetings[authStore.user?.role] || 'How can I assist you today?',
    type: 'text',
    timestamp: new Date(),
    agent: 'Assistant',
    confidence: 100,
    sources: ['System'],
    processingTime: 0,
  });
};

const loadSuggestions = async () => {
  try {
    const data = await ChatbotApi.getSuggestions();
    suggestions.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (err) {
    console.error('Failed to load suggestions', err);
    suggestions.value = ['Show users list', 'My branches', 'Recent orders'];
  }
};

const loadQueryHistory = () => {
  try {
    const stored = localStorage.getItem('chatbot_query_history');
    if (stored) {
      queryHistory.value = JSON.parse(stored);
    }
  } catch {
    queryHistory.value = [];
  }
};

const saveQueryHistory = (text) => {
  const history = [text, ...queryHistory.value.filter(q => q !== text)].slice(0, 20);
  queryHistory.value = history;
  try {
    localStorage.setItem('chatbot_query_history', JSON.stringify(history));
  } catch { /* silent fail */ }
};

const loadCachedMessages = () => {
  try {
    const cached = localStorage.getItem('chatbot_messages');
    if (cached) {
      const parsed = JSON.parse(cached);
      messages.value = parsed.map(m => ({
        ...m,
        timestamp: new Date(m.timestamp),
      }));
    }
  } catch { /* silent fail */ }
};

const cacheMessages = () => {
  try {
    localStorage.setItem('chatbot_messages', JSON.stringify(messages.value.slice(-50)));
  } catch { /* silent fail */ }
};

// ─── Core Query ─────────────────────────────────────────────────
const sendQuery = async (forceQuery = null) => {
  const text = forceQuery || query.value;
  if (!text.trim() || isTyping.value) return;

  // Check cache first
  const cacheKey = text.trim().toLowerCase();
  if (responseCache.has(cacheKey)) {
    const cached = responseCache.get(cacheKey);
    messages.value.push({
      id: generateId(),
      role: 'user',
      content: text,
      type: 'text',
      timestamp: new Date(),
    });
    messages.value.push({
      ...cached,
      id: generateId(),
      timestamp: new Date(),
      fromCache: true,
    });
    query.value = '';
    saveQueryHistory(text);
    cacheMessages();
    await scrollDown();
    return;
  }

  messages.value.push({
    id: generateId(),
    role: 'user',
    content: text,
    type: 'text',
    timestamp: new Date(),
  });

  query.value = '';
  historyIndex.value = -1;
  isTyping.value = true;
  await scrollDown();

  const startTime = performance.now();

  try {
    const response = await ChatbotApi.query(text);
    const endTime = performance.now();
    const procTime = Math.round(endTime - startTime);

    let textResp = '';

    if (response?.response) {
      textResp = response.response;
    } else if (response?.data?.response) {
      textResp = response.data.response;
    } else if (typeof response === 'string') {
      textResp = response;
    } else if (response?.message) {
      textResp = response.message;
    }

    if (!textResp || textResp.trim() === '') {
      textResp = 'I received an empty response from Gemini. Please try again or rephrase your query.';
    }

    const parsedResp = parseMarkdown(textResp);

    // Determine confidence based on response quality
    const confidence = calculateConfidence(textResp, procTime);

    // Determine sources from module info
    const sources = determineSources(response);

    const botMessage = {
      id: generateId(),
      role: 'bot',
      ...parsedResp,
      agent: response?.agent || (response?.data?.agent) || 'Assistant',
      timestamp: new Date(),
      confidence,
      sources,
      processingTime: procTime,
      module: response?.module || (response?.data?.module) || null,
      feedback: null,
    };

    messages.value.push(botMessage);

    // Cache the response
    responseCache.set(cacheKey, botMessage);

    saveQueryHistory(text);
    cacheMessages();
  } catch (err) {
    console.error('Chatbot error:', err);

    messages.value.push({
      id: generateId(),
      role: 'bot',
      content: 'Sorry, I encountered an error. Please check your connection and try again.',
      type: 'text',
      timestamp: new Date(),
      isError: true,
    });
  } finally {
    isTyping.value = false;
    scrollDown();
  }
};

// ─── Utility helpers ────────────────────────────────────────────
const generateId = () => `msg_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;

const calculateConfidence = (text, time) => {
  if (!text || text.trim() === '') return 0;
  let score = 85;
  if (text.length < 20) score -= 10;
  if (text.includes('not sure') || text.includes('cannot determine')) score -= 25;
  if (text.includes('empty response')) score -= 40;
  if (time > 5000) score -= 5;
  if (time > 10000) score -= 10;
  return Math.max(0, Math.min(100, score));
};

const determineSources = (response) => {
  const sources = [];
  const module = response?.module || (response?.data?.module);
  if (module) {
    const moduleMap = {
      'users': 'User Management DB',
      'inventory': 'Inventory DB',
      'branch': 'Branch Records',
      'orders': 'Order Management',
      'products': 'Product Catalog',
    };
    if (moduleMap[module]) sources.push(moduleMap[module]);
  }
  if (sources.length === 0) sources.push('Inventory DB');
  return sources;
};

// ─── Markdown Parser ────────────────────────────────────────────
const parseMarkdown = (rawText) => {
  if (typeof rawText !== 'string') return { type: 'text', content: String(rawText) };

  if (rawText.includes('|') && rawText.includes('---')) {
    const lines = rawText.trim().split('\n');
    let tableHeaders = [];
    let tableRows = [];
    let foundTable = false;
    let beforeText = '';

    for (let i = 0; i < lines.length; i++) {
      const line = lines[i].trim();
      if (line.startsWith('|')) {
        if (!foundTable) {
          tableHeaders = line.split('|').filter(c => c.trim()).map(c => c.trim());
          foundTable = true;
        } else if (!line.includes('---')) {
          const row = line.split('|').filter((c, idx) => idx > 0 && idx < line.split('|').length - 1).map(c => c.trim());
          if (row.length > 0) tableRows.push(row);
        }
      } else if (!foundTable && line) {
        beforeText += line + ' ';
      }
    }

    if (foundTable && tableHeaders.length > 0) {
      return {
        type: 'table',
        content: beforeText.trim(),
        tableData: {
          headers: tableHeaders,
          rows: tableRows,
        },
      };
    }
  }

  return { type: 'text', content: rawText };
};

const formatContent = (content) => {
  if (!content) return '';
  return content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code class="inline-code">$1</code>')
    .replace(/\n/g, '<br/>');
};

const formatTime = (date) => {
  return new Intl.DateTimeFormat('default', {
    hour: 'numeric',
    minute: 'numeric',
  }).format(date);
};

// ─── Actions ────────────────────────────────────────────────────
const clearChat = () => {
  messages.value = [];
  responseCache.clear();
  localStorage.removeItem('chatbot_messages');
  autoWelcome();
};

const copyResponse = async (content) => {
  try {
    await navigator.clipboard.writeText(content);
  } catch {
    // Silently fail
  }
};

const regenerateResponse = async (msgIndex) => {
  // Find the user message before this bot message
  const userMsg = messages.value[msgIndex - 1];
  if (userMsg && userMsg.role === 'user') {
    messages.value.splice(msgIndex, 1); // Remove the bot message
    await sendQuery(userMsg.content);
  }
};

// ─── Keyboard Shortcuts ─────────────────────────────────────────
const handleKeydown = (e) => {
  if (!isOpen.value) return;

  // Ctrl+Enter or Cmd+Enter to send
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault();
    sendQuery();
  }

  // Escape to close
  if (e.key === 'Escape') {
    isOpen.value = false;
  }

  // Arrow Up/Down for history when input is focused and empty
  if (e.target.tagName === 'INPUT') {
    if (e.key === 'ArrowUp' && queryHistory.value.length > 0) {
      e.preventDefault();
      historyIndex.value = Math.min(historyIndex.value + 1, queryHistory.value.length - 1);
      query.value = queryHistory.value[historyIndex.value];
    }
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      historyIndex.value = Math.max(historyIndex.value - 1, -1);
      query.value = historyIndex.value >= 0 ? queryHistory.value[historyIndex.value] : '';
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});

// ─── Debounced Input ────────────────────────────────────────────
let debounceTimer = null;
const handleInput = (e) => {
  query.value = e.target.value;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    // Could trigger typeahead suggestions here
  }, 300);
};

// ─── Scroll ─────────────────────────────────────────────────────
const scrollDown = async () => {
  await nextTick();
  if (messageBox.value) {
    messageBox.value.scrollTop = messageBox.value.scrollHeight;
  }
};

watch(() => messages.value.length, () => {
  scrollDown();
});

// Table key generator
const getTableKey = (msgId) => `table_${msgId}`;
</script>

<template>
  <div v-if="isLoggedIn" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform translate-y-4 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-4 opacity-0"
    >
      <div
        v-if="isOpen"
        class="mb-4 w-[420px] max-h-[80vh] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700/80 flex flex-col overflow-hidden"
        role="dialog"
        aria-label="Inventory Assistant Chat"
      >
        <!-- ═══ Simplified Professional Header ═══ -->
        <div class="shrink-0">
          <!-- Clean Header -->
          <div class="px-5 py-3.5 bg-gradient-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                <SparklesIcon :size="18" class="text-white" />
              </div>
              <div>
                <h3 class="font-semibold text-sm text-white">Inventory Assistant</h3>
                <p class="text-[11px] text-white/80">AI-powered assistant</p>
              </div>
            </div>
            <div class="flex items-center gap-1.5">
              <button
                @click="clearChat"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors text-white/80 hover:text-white"
                title="Clear chat"
                aria-label="Clear chat"
              >
                <TrashIcon :size="16" />
              </button>
              <button
                @click="isOpen = false"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors text-white/80 hover:text-white"
                title="Close"
                aria-label="Close chat"
              >
                <XIcon :size="16" />
              </button>
            </div>
          </div>

          <!-- Minimized State -->
          <div v-if="isMinimized" class="px-5 py-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-center">
            <button
              @click="isMinimized = false"
              class="flex items-center gap-2 px-4 py-2 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-lg transition-colors text-sm font-medium"
            >
              <MaximizeIcon :size="14" />
              Click to expand chat
            </button>
          </div>
        </div>

        <!-- ═══ Messages Area ═══ -->
        <div
          v-show="!isMinimized"
          ref="messageBox"
          class="flex-1 overflow-y-auto scroll-smooth bg-gradient-to-b from-gray-50/50 to-gray-100/30 dark:from-gray-950/20 dark:to-gray-950/40"
          role="log"
          aria-label="Chat messages"
          aria-live="polite"
        >
          <!-- Welcome / Empty State -->
          <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10 px-6 bg-gradient-to-b from-primary-50/30 to-transparent dark:from-primary-950/10">
            <div class="relative mb-5">
              <div class="absolute inset-0 bg-primary-400/20 dark:bg-primary-500/20 rounded-3xl blur-xl animate-pulse"></div>
              <div class="relative p-5 bg-gradient-to-br from-primary-500 to-primary-600 dark:from-primary-600 dark:to-primary-700 rounded-2xl shadow-xl border border-white/20">
                <ChatIcon :size="36" class="text-white" />
              </div>
            </div>
            <h4 class="text-base font-bold text-gray-900 dark:text-gray-50 mb-1.5">Inventory Assistant</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-5 max-w-[240px]">Your intelligent ERP assistant powered by AI & Gemini</p>

            <!-- Guided Onboarding -->
            <div class="w-full space-y-2.5 mt-1">
              <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Quick start actions</p>
              <button
                v-for="(action, idx) in quickActions"
                :key="action.label"
                @click="sendQuery(action.query)"
                class="w-full flex items-center gap-3 px-3.5 py-3 bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 rounded-xl hover:border-primary-400 dark:hover:border-primary-600 hover:shadow-lg hover:shadow-primary-500/10 hover:-translate-y-0.5 transition-all duration-200 group text-left"
                :style="{ animationDelay: `${idx * 75}ms` }"
              >
                <div class="p-2 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/20 rounded-xl group-hover:from-primary-100 dark:group-hover:from-primary-900/40 group-hover:to-primary-700/30 transition-all shadow-sm">
                  <ZapIcon :size="15" class="text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-gray-800 dark:text-gray-100">{{ action.label }}</p>
                  <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ action.query }}</p>
                </div>
                <ChevronDownIcon :size="14" class="text-gray-400 -rotate-90 group-hover:-translate-y-0.5 transition-transform" />
              </button>
            </div>
          </div>

          <!-- Message List -->
          <div class="px-4 py-4 space-y-4">
            <transition-group
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0 -translate-y-2"
            >
            <div
              v-for="(msg, idx) in messages"
              :key="msg.id || idx"
              :class="[
                'flex flex-col',
                msg.role === 'user' ? 'items-end' : 'items-start',
              ]"
            >
              <!-- User Message -->
              <div
                v-if="msg.role === 'user'"
                class="max-w-[88%] rounded-2xl px-4 py-3 text-sm shadow-lg shadow-primary-500/20 bg-gradient-to-br from-primary-600 via-primary-500 to-primary-600 text-white border border-white/10"
              >
                <p class="leading-relaxed whitespace-pre-wrap">{{ msg.content }}</p>
                <div class="flex items-center justify-end gap-1.5 mt-2">
                  <ClockIcon :size="10" class="text-white/50" />
                  <span class="text-[10px] text-white/50 font-medium">{{ formatTime(msg.timestamp) }}</span>
                </div>
              </div>

              <!-- Bot Message -->
              <div
                v-else
                class="max-w-[95%] w-full"
              >
                <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden">
                  <!-- Simplified Bot Header -->
                  <div class="px-4 py-2.5 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                      <div class="p-1.5 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                        <BotIcon :size="13" class="text-primary-600 dark:text-primary-400" />
                      </div>
                      <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ msg.agent || 'Assistant' }}
                      </span>
                    </div>
                    <!-- Minimal Actions -->
                    <div class="flex items-center gap-0.5">
                      <button
                        @click="copyResponse(msg.content)"
                        class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        title="Copy response"
                        aria-label="Copy response"
                      >
                        <CopyIcon :size="13" />
                      </button>
                      <button
                        @click="regenerateResponse(idx)"
                        class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        title="Regenerate"
                        aria-label="Regenerate response"
                      >
                        <RefreshIcon :size="13" />
                      </button>
                    </div>
                  </div>

                  <!-- Message Content -->
                  <div class="px-4 py-3">
                    <!-- Error State -->
                    <div v-if="msg.isError" class="flex items-start gap-2.5 p-3 bg-red-50 dark:bg-red-900/10 border border-red-200/50 dark:border-red-800/30 rounded-lg">
                      <AlertCircleIcon :size="16" class="text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                      <div>
                        <p class="text-sm text-red-700 dark:text-red-300 font-medium">Response Error</p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ msg.content }}</p>
                      </div>
                    </div>

                    <!-- Text Content -->
                    <div v-else-if="msg.type === 'text'" v-html="formatContent(msg.content)" class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed"></div>

                    <!-- Table Content -->
                    <div v-else-if="msg.type === 'table' && msg.tableData" class="-mx-1">
                      <p v-if="msg.content" class="text-sm text-gray-700 dark:text-gray-300 mb-2.5 leading-relaxed" v-html="formatContent(msg.content)"></p>
                      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full border-collapse text-xs text-left">
                          <thead class="bg-gray-50 dark:bg-gray-800/80">
                            <tr>
                              <th
                                v-for="(h, hidx) in msg.tableData.headers"
                                :key="hidx"
                                class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-[10px] text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                              >
                                {{ h || 'N/A' }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="(row, ridx) in msg.tableData.rows"
                              :key="ridx"
                              class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors"
                            >
                              <td
                                v-for="(val, vidx) in row"
                                :key="vidx"
                                class="px-3 py-2 border-b border-gray-100 dark:border-gray-700/50 text-gray-700 dark:text-gray-300 whitespace-nowrap"
                              >
                                {{ val }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <!-- Minimal Timestamp Footer -->
                  <div class="px-4 py-1.5 border-t border-gray-100 dark:border-gray-800/50">
                    <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ formatTime(msg.timestamp) }}</span>
                  </div>
                </div>
              </div>
            </div>
            </transition-group>

            <!-- Simplified Typing Indicator -->
            <div v-if="isTyping" class="flex justify-start">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3">
                <div class="flex gap-1.5">
                  <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce"></span>
                  <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                  <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ═══ Lightweight Suggestion Chips ═══ -->
        <div
          v-show="!isMinimized"
          v-if="messages.length < 3 && !isTyping"
          class="px-4 py-3 border-t border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-900 shrink-0"
        >
          <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-2">Suggested queries</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="suggest in suggestions"
              :key="suggest"
              @click="sendQuery(suggest)"
              class="text-xs px-4 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 text-gray-600 dark:text-gray-400 hover:text-primary-700 dark:hover:text-primary-400 rounded-full transition-colors border border-transparent hover:border-primary-200 dark:hover:border-primary-800"
            >
              {{ suggest }}
            </button>
          </div>
        </div>

        <!-- ═══ Clean Input Area ═══ -->
        <div v-show="!isMinimized" class="px-4 py-4 border-t border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-900 shrink-0">
          <div class="flex gap-2.5">
            <input
              v-model="query"
              @input="handleInput"
              @keyup.enter="sendQuery()"
              type="text"
              placeholder="Ask about inventory, users, branches... (Press Enter to send)"
              class="flex-1 text-sm bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 border border-gray-200 dark:border-gray-700 transition-colors"
              :disabled="isTyping"
              aria-label="Type your message"
            />
            <button
              @click="sendQuery()"
              :disabled="!query.trim() || isTyping"
              class="px-5 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white rounded-xl disabled:opacity-50 transition-colors disabled:cursor-not-allowed"
              aria-label="Send message"
            >
              <SendIcon :size="18" />
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ═══ Toggle Button ═══ -->
    <button
      @click="toggleChat"
      :class="[
        'w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 relative',
        isOpen
          ? 'bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700'
          : 'bg-gradient-to-br from-primary-600 to-primary-500 hover:from-primary-700 hover:to-primary-600 shadow-primary-300/40'
      ]"
      aria-label="Toggle chat assistant"
    >
      <transition mode="out-in">
        <XIcon v-if="isOpen" key="close" :size="20" class="text-white" />
        <SparklesIcon v-else key="bot" :size="20" class="text-white" />
      </transition>

      <!-- Notification Badge -->
      <span v-if="!isOpen && messages.length === 0" class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-4 w-4 bg-primary-500 border-2 border-white dark:border-gray-900"></span>
      </span>
    </button>
  </div>
</template>

<style scoped>
/* ─── Custom Scrollbar ─── */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 4px;
  transition: background 0.2s ease;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.25);
}
.dark ::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
}
.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* ─── Markdown Content Styling ─── */
:deep(strong) {
  font-weight: 700;
  color: inherit;
}
:deep(em) {
  font-style: italic;
}
:deep(.inline-code) {
  font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', 'Consolas', monospace;
  font-size: 0.875em;
  padding: 0.2em 0.5em;
  background-color: rgba(231, 90, 184, 0.1);
  border-radius: 0.35rem;
  color: #d63fa9;
  font-weight: 500;
}
.dark :deep(.inline-code) {
  background-color: rgba(231, 90, 184, 0.2);
  color: #e75ab8;
}

/* ─── Table Styling ─── */
:deep(table) {
  border-spacing: 0;
}
:deep(th) {
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
:deep(tr:hover td) {
  background-color: rgba(231, 90, 184, 0.06);
}
.dark :deep(tr:hover td) {
  background-color: rgba(231, 90, 184, 0.1);
}

/* ─── Focus Visible for Accessibility ─── */
:deep(button:focus-visible),
:deep(input:focus-visible) {
  outline: 2px solid #e75ab8;
  outline-offset: 2px;
}

/* ─── Smooth Transitions ─── */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ─── Message Content Links ─── */
:deep(a) {
  color: #e75ab8;
  text-decoration: underline;
  transition: color 0.2s;
}
:deep(a:hover) {
  color: #d147a3;
}
.dark :deep(a) {
  color: #f07cc8;
}
.dark :deep(a:hover) {
  color: #e75ab8;
}

/* ─── Selection Color ─── */
::selection {
  background-color: rgba(231, 90, 184, 0.3);
  color: #1f2937;
}
.dark ::selection {
  background-color: rgba(231, 90, 184, 0.4);
  color: #f9fafb;
}
</style>
