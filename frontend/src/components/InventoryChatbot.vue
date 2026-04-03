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
  ThumbsUp as ThumbsUpIcon,
  ThumbsDown as ThumbsDownIcon,
  ChevronDown as ChevronDownIcon,
  ChevronUp as ChevronUpIcon,
  Trash2 as TrashIcon,
  Download as DownloadIcon,
  Minimize2 as MinimizeIcon,
  Mic as MicIcon,
  Paperclip as PaperclipIcon,
  Clock as ClockIcon,
  Zap as ZapIcon,
  Database as DatabaseIcon,
  AlertCircle as AlertCircleIcon,
  CheckCircle2 as CheckCircleIcon,
  Loader2 as LoaderIcon,
  Search as SearchIcon,
  ArrowUpDown as SortIcon,
  ExternalLink as ExternalLinkIcon,
  HelpCircle as HelpCircleIcon,
  Settings as SettingsIcon,
  Maximize2 as MaximizeIcon,
  Lightbulb as LightbulbIcon,
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
const showSuggestions = ref(true);
const connectionStatus = ref('connected'); // 'connected' | 'connecting' | 'error'
const responseCache = new Map();
const tooltipState = ref({ visible: false, text: '', x: 0, y: 0 });

// Per-message metadata
const responseTime = ref(0);
const lastProcessedAt = ref(null);
const sessionId = computed(() => `sess_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`);

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
    isMinimized.value = false;
    if (messages.value.length === 0) {
      autoWelcome();
      loadSuggestions();
      loadQueryHistory();
    }
    loadCachedMessages();
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
  connectionStatus.value = 'connecting';
  await scrollDown();

  const startTime = performance.now();

  try {
    const response = await ChatbotApi.query(text);
    const endTime = performance.now();
    const procTime = Math.round(endTime - startTime);
    responseTime.value = procTime;
    lastProcessedAt.value = new Date();
    connectionStatus.value = 'connected';

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
    const endTime = performance.now();
    responseTime.value = Math.round(endTime - startTime);
    lastProcessedAt.value = new Date();
    connectionStatus.value = 'error';

    messages.value.push({
      id: generateId(),
      role: 'bot',
      content: 'Sorry, I encountered an error. Please check your connection and try again.',
      type: 'text',
      timestamp: new Date(),
      confidence: 0,
      sources: [],
      processingTime: responseTime.value,
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

const formatLastUpdate = (date) => {
  if (!date) return '—';
  const now = new Date();
  const diff = Math.floor((now - new Date(date)) / 1000);
  if (diff < 5) return 'Just now';
  if (diff < 60) return `${diff}s ago`;
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  return formatTime(date);
};

const getConfidenceColor = (confidence) => {
  if (confidence >= 90) return 'text-emerald-600 dark:text-emerald-400';
  if (confidence >= 70) return 'text-amber-600 dark:text-amber-400';
  return 'text-red-600 dark:text-red-400';
};

const getConfidenceBg = (confidence) => {
  if (confidence >= 90) return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400';
  if (confidence >= 70) return 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400';
  return 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400';
};

const getConfidenceBarColor = (confidence) => {
  if (confidence >= 90) return 'bg-emerald-500';
  if (confidence >= 70) return 'bg-amber-500';
  return 'bg-red-500';
};

// ─── Actions ────────────────────────────────────────────────────
const clearChat = () => {
  messages.value = [];
  responseCache.clear();
  localStorage.removeItem('chatbot_messages');
  autoWelcome();
};

const exportConversation = () => {
  const text = messages.value.map(m => {
    const role = m.role === 'user' ? 'You' : 'Assistant';
    const time = formatTime(m.timestamp);
    let content = m.content || '';
    if (m.type === 'table' && m.tableData) {
      content += '\n[Table: ' + m.tableData.headers.join(', ') + ']';
    }
    return `[${time}] ${role}: ${content}`;
  }).join('\n\n');

  const header = `Inventory Assistant - Conversation Export\nSession: ${sessionId.value}\nDate: ${new Date().toLocaleString()}\n${'='.repeat(60)}\n\n`;
  const blob = new Blob([header + text], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `chat-export-${new Date().toISOString().slice(0, 10)}.txt`;
  a.click();
  URL.revokeObjectURL(url);
};

const copyResponse = async (content) => {
  try {
    await navigator.clipboard.writeText(content);
    showTooltip('Copied to clipboard');
  } catch {
    showTooltip('Failed to copy');
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

const giveFeedback = (msgIndex, feedback) => {
  if (messages.value[msgIndex]) {
    messages.value[msgIndex].feedback = feedback;
    cacheMessages();
    showTooltip(feedback === 'up' ? 'Thanks for the feedback!' : 'We\'ll improve this response');
  }
};

const showTooltip = (text) => {
  tooltipState.value = { visible: true, text, x: 0, y: 0 };
  setTimeout(() => {
    tooltipState.value.visible = false;
  }, 2000);
};

// ─── Table Features ─────────────────────────────────────────────
const tableSortStates = ref({});

const sortTable = (tableKey, colIndex) => {
  const key = `${tableKey}_${colIndex}`;
  const current = tableSortStates.value[key] || 'none';
  const next = current === 'none' ? 'asc' : current === 'asc' ? 'desc' : 'none';
  tableSortStates.value[key] = next;
  // Sorting would be applied during render
};

const getSortIcon = (tableKey, colIndex) => {
  const key = `${tableKey}_${colIndex}`;
  const state = tableSortStates.value[key] || 'none';
  if (state === 'asc') return '↑';
  if (state === 'desc') return '↓';
  return '↕';
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

    <!-- Global Tooltip -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 transform -translate-y-1"
      enter-to-class="opacity-100 transform translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="tooltipState.visible"
        class="fixed z-[9999] px-2.5 py-1.5 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-md shadow-lg pointer-events-none"
        style="transform: translate(-50%, -100%)"
      >
        {{ tooltipState.text }}
        <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
      </div>
    </transition>

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
        class="mb-3 w-[400px] h-[600px] bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
        role="dialog"
        aria-label="Inventory Assistant Chat"
      >
        <!-- ═══ Professional Header Toolbar ═══ -->
        <div class="shrink-0">
          <!-- Main Header -->
          <div class="px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                <SparklesIcon :size="16" class="text-white" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-semibold text-sm text-white tracking-tight">Inventory Assistant</h3>
                  <span class="px-1.5 py-0.5 text-[10px] font-medium bg-white/20 text-white rounded-md backdrop-blur-sm">
                    AI
                  </span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="relative flex h-1.5 w-1.5">
                    <span
                      class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                      :class="connectionStatus === 'connected' ? 'bg-emerald-300' : connectionStatus === 'connecting' ? 'bg-amber-300' : 'bg-red-300'"
                    ></span>
                    <span
                      class="relative inline-flex rounded-full h-1.5 w-1.5"
                      :class="connectionStatus === 'connected' ? 'bg-emerald-300' : connectionStatus === 'connecting' ? 'bg-amber-300' : 'bg-red-300'"
                    ></span>
                  </span>
                  <p class="text-[11px] text-white/80">
                    {{ currentRoleName }}
                    <span class="mx-1">·</span>
                    <span class="capitalize">{{ connectionStatus }}</span>
                  </p>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <button
                @click="exportConversation"
                class="p-1.5 hover:bg-white/20 rounded-lg transition-colors text-white/70 hover:text-white"
                title="Export conversation"
                aria-label="Export conversation"
              >
                <DownloadIcon :size="14" />
              </button>
              <button
                @click="clearChat"
                class="p-1.5 hover:bg-white/20 rounded-lg transition-colors text-white/70 hover:text-white"
                title="Clear chat"
                aria-label="Clear chat"
              >
                <TrashIcon :size="14" />
              </button>
              <button
                @click="isMinimized = !isMinimized"
                class="p-1.5 hover:bg-white/20 rounded-lg transition-colors text-white/70 hover:text-white"
                title="Minimize"
                aria-label="Minimize chat"
              >
                <MinimizeIcon :size="14" />
              </button>
              <button
                @click="isOpen = false"
                class="p-1.5 hover:bg-white/20 rounded-lg transition-colors text-white/70 hover:text-white"
                title="Close"
                aria-label="Close chat"
              >
                <XIcon :size="14" />
              </button>
            </div>
          </div>

          <!-- Minimized State -->
          <div v-if="isMinimized" class="px-4 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-center">
            <button
              @click="isMinimized = false"
              class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
            >
              <MaximizeIcon :size="14" />
              Click to expand
            </button>
          </div>
        </div>

        <!-- ═══ Messages Area ═══ -->
        <div
          v-show="!isMinimized"
          ref="messageBox"
          class="flex-1 overflow-y-auto scroll-smooth bg-gray-50 dark:bg-gray-950/30"
          role="log"
          aria-label="Chat messages"
          aria-live="polite"
        >
          <!-- Welcome / Empty State -->
          <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center py-12 px-6">
            <div class="p-4 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/10 rounded-2xl mb-4">
              <ChatIcon :size="32" class="text-primary-500 dark:text-primary-400" />
            </div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Inventory Assistant</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Your intelligent ERP assistant powered by AI</p>

            <!-- Guided Onboarding -->
            <div class="w-full space-y-2 mt-2">
              <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Quick start</p>
              <button
                v-for="action in quickActions"
                :key="action.label"
                @click="sendQuery(action.query)"
                class="w-full flex items-center gap-3 px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-sm transition-all group text-left"
              >
                <div class="p-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-md group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                  <ZapIcon :size="14" class="text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ action.label }}</p>
                  <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ action.query }}</p>
                </div>
              </button>
            </div>
          </div>

          <!-- Message List -->
          <div class="px-4 py-4 space-y-4">
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
                class="max-w-[85%] rounded-xl px-4 py-2.5 text-sm shadow-sm bg-gradient-to-br from-primary-600 to-primary-500 text-white"
              >
                <p class="leading-relaxed">{{ msg.content }}</p>
                <div class="flex items-center justify-end gap-2 mt-1.5">
                  <ClockIcon :size="10" class="text-white/60" />
                  <span class="text-[10px] text-white/60">{{ formatTime(msg.timestamp) }}</span>
                </div>
              </div>

              <!-- Bot Message -->
              <div
                v-else
                class="max-w-[92%] w-full"
              >
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
                  <!-- Bot Header -->
                  <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <div class="p-1 bg-primary-100 dark:bg-primary-900/30 rounded-md">
                        <BotIcon :size="12" class="text-primary-600 dark:text-primary-400" />
                      </div>
                      <span class="text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                        {{ msg.agent || 'Assistant' }}
                      </span>
                      <span v-if="msg.fromCache" class="flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-md">
                        <ClockIcon :size="9" />
                        Cached
                      </span>
                    </div>
                    <!-- Response Actions -->
                    <div class="flex items-center gap-0.5">
                      <button
                        @click="copyResponse(msg.content)"
                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        title="Copy response"
                        aria-label="Copy response"
                      >
                        <CopyIcon :size="12" />
                      </button>
                      <button
                        @click="regenerateResponse(idx)"
                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        title="Regenerate"
                        aria-label="Regenerate response"
                      >
                        <RefreshIcon :size="12" />
                      </button>
                      <button
                        @click="giveFeedback(idx, 'up')"
                        :class="[
                          'p-1 rounded transition-colors',
                          msg.feedback === 'up'
                            ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                            : 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400',
                        ]"
                        title="Helpful"
                        aria-label="Mark as helpful"
                      >
                        <ThumbsUpIcon :size="12" />
                      </button>
                      <button
                        @click="giveFeedback(idx, 'down')"
                        :class="[
                          'p-1 rounded transition-colors',
                          msg.feedback === 'down'
                            ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20'
                            : 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-400 hover:text-red-600 dark:hover:text-red-400',
                        ]"
                        title="Not helpful"
                        aria-label="Mark as not helpful"
                      >
                        <ThumbsDownIcon :size="12" />
                      </button>
                    </div>
                  </div>

                  <!-- Message Content -->
                  <div class="px-3 py-2.5">
                    <!-- Error State -->
                    <div v-if="msg.isError" class="flex items-start gap-2.5 p-2.5 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/30 rounded-lg">
                      <AlertCircleIcon :size="16" class="text-red-500 dark:text-red-400 shrink-0 mt-0.5" />
                      <div>
                        <p class="text-sm text-red-700 dark:text-red-300 font-medium">Response Error</p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">{{ msg.content }}</p>
                        <button
                          @click="regenerateResponse(idx)"
                          class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/30 rounded-md transition-colors"
                        >
                          <RefreshIcon :size="12" />
                          Retry
                        </button>
                      </div>
                    </div>

                    <!-- Text Content -->
                    <div v-else-if="msg.type === 'text'" v-html="formatContent(msg.content)" class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed"></div>

                    <!-- Table Content -->
                    <div v-else-if="msg.type === 'table' && msg.tableData" class="-mx-1">
                      <p v-if="msg.content" class="text-sm text-gray-700 dark:text-gray-300 mb-2.5 leading-relaxed" v-html="formatContent(msg.content)"></p>
                      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <!-- Table Toolbar -->
                        <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                          <div class="flex items-center gap-1.5">
                            <DatabaseIcon :size="12" class="text-gray-400" />
                            <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">
                              {{ msg.tableData.rows.length }} records
                            </span>
                          </div>
                          <div class="flex items-center gap-1">
                            <button
                              class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                              title="Export table"
                              aria-label="Export table"
                            >
                              <DownloadIcon :size="12" />
                            </button>
                            <button
                              class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                              title="Expand table"
                              aria-label="Expand table"
                            >
                              <ExternalLinkIcon :size="12" />
                            </button>
                          </div>
                        </div>
                        <table class="w-full border-collapse text-xs text-left">
                          <thead class="bg-gray-100 dark:bg-gray-700/50">
                            <tr>
                              <th
                                v-for="(h, hidx) in msg.tableData.headers"
                                :key="hidx"
                                class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-[10px] text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors select-none"
                                @click="sortTable(getTableKey(msg.id), hidx)"
                              >
                                <span class="inline-flex items-center gap-1">
                                  {{ h || 'N/A' }}
                                  <SortIcon :size="10" class="text-gray-400" />
                                </span>
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

                  <!-- Response Metadata Footer -->
                  <div class="px-3 py-2 bg-gray-50/80 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-700/50">
                    <div class="flex flex-wrap items-center gap-2">
                      <!-- Confidence Badge -->
                      <span
                        v-if="msg.confidence !== undefined && msg.confidence > 0"
                        :class="['inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium', getConfidenceBg(msg.confidence)]"
                      >
                        <CheckCircleIcon :size="10" />
                        {{ msg.confidence }}% confidence
                      </span>

                      <!-- Processing Time -->
                      <span
                        v-if="msg.processingTime"
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400"
                      >
                        <ZapIcon :size="9" />
                        {{ msg.processingTime }}ms
                      </span>

                      <!-- Sources -->
                      <span
                        v-if="msg.sources && msg.sources.length > 0"
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400"
                      >
                        <DatabaseIcon :size="9" />
                        {{ msg.sources[0] }}
                        <span v-if="msg.sources.length > 1" class="text-gray-400">+{{ msg.sources.length - 1 }}</span>
                      </span>

                      <!-- Timestamp -->
                      <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 dark:text-gray-500 ml-auto">
                        <ClockIcon :size="10" />
                        {{ formatTime(msg.timestamp) }}
                      </span>
                    </div>

                    <!-- Confidence Bar -->
                    <div
                      v-if="msg.confidence !== undefined && msg.confidence > 0"
                      class="mt-1.5 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden"
                    >
                      <div
                        :class="['h-full rounded-full transition-all duration-500', getConfidenceBarColor(msg.confidence)]"
                        :style="{ width: `${msg.confidence}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="isTyping" class="flex justify-start flex-col gap-1.5">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xs w-fit overflow-hidden">
                <div class="px-3 py-2.5 flex items-center gap-3">
                  <div class="flex gap-1.5">
                    <span class="w-1.5 h-1.5 bg-primary-400 dark:bg-primary-500 rounded-full animate-pulse"></span>
                    <span class="w-1.5 h-1.5 bg-primary-400 dark:bg-primary-500 rounded-full animate-pulse" style="animation-delay: 0.15s"></span>
                    <span class="w-1.5 h-1.5 bg-primary-400 dark:bg-primary-500 rounded-full animate-pulse" style="animation-delay: 0.3s"></span>
                  </div>
                  <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Analyzing query...
                  </span>
                </div>
                <!-- Skeleton Loader -->
                <div class="px-3 pb-2.5 space-y-1.5">
                  <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full w-3/4 animate-pulse"></div>
                  <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full w-1/2 animate-pulse"></div>
                </div>
              </div>
              <div class="flex items-center gap-1.5 px-1">
                <LoaderIcon :size="10" class="text-gray-400 animate-spin" />
                <p class="text-[10px] text-gray-400 dark:text-gray-500">Fetching data from inventory systems</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ═══ Suggestion Chips ═══ -->
        <div
          v-show="!isMinimized"
          v-if="messages.length < 3 && !isTyping"
          class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shrink-0"
        >
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1.5">
              <LightbulbIcon :size="12" class="text-amber-500" />
              <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Suggested queries</span>
            </div>
            <button
              @click="showSuggestions = !showSuggestions"
              class="p-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors text-gray-400"
              aria-label="Toggle suggestions"
            >
              <ChevronUpIcon v-if="showSuggestions" :size="12" />
              <ChevronDownIcon v-else :size="12" />
            </button>
          </div>
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 transform -translate-y-1"
            enter-to-class="opacity-100 transform translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showSuggestions" class="flex flex-wrap gap-1.5">
              <button
                v-for="suggest in suggestions"
                :key="suggest"
                @click="sendQuery(suggest)"
                class="text-xs px-3 py-1.5 bg-gray-50 dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 text-gray-600 dark:text-gray-300 hover:text-primary-700 dark:hover:text-primary-400 rounded-md transition-all border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 font-medium hover:shadow-xs"
              >
                {{ suggest }}
              </button>
            </div>
          </transition>
        </div>

        <!-- ═══ Query History Quick Access ═══ -->
        <div
          v-show="!isMinimized"
          v-if="queryHistory.length > 0 && messages.length >= 2"
          class="px-4 py-2 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0"
        >
          <div class="flex items-center gap-1.5 mb-1.5">
            <ClockIcon :size="11" class="text-gray-400" />
            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Recent queries</span>
          </div>
          <div class="flex flex-wrap gap-1">
            <button
              v-for="(q, qidx) in queryHistory.slice(0, 5)"
              :key="qidx"
              @click="sendQuery(q)"
              class="text-[10px] px-2 py-1 bg-gray-100 dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 text-gray-500 dark:text-gray-400 hover:text-primary-700 dark:hover:text-primary-400 rounded transition-colors truncate max-w-[140px]"
              :title="q"
            >
              {{ q }}
            </button>
          </div>
        </div>

        <!-- ═══ Input Area ═══ -->
        <div v-show="!isMinimized" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shrink-0">
          <div class="flex gap-2">
            <div class="flex-1 relative">
              <input
                v-model="query"
                @input="handleInput"
                @keyup.enter="sendQuery()"
                type="text"
                placeholder="Ask about inventory, users, or branches..."
                class="w-full text-sm bg-gray-100 dark:bg-gray-800 rounded-lg pl-3.5 pr-16 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 border border-gray-200 dark:border-gray-700 transition-all"
                :disabled="isTyping"
                aria-label="Type your message"
              />
              <div class="absolute right-1 top-1/2 -translate-y-1/2 flex items-center gap-0.5">
                <button
                  class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  title="Voice input"
                  aria-label="Voice input"
                  :disabled="isTyping"
                >
                  <MicIcon :size="14" />
                </button>
                <button
                  class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  title="Attach file"
                  aria-label="Attach file"
                  :disabled="isTyping"
                >
                  <PaperclipIcon :size="14" />
                </button>
              </div>
            </div>
            <button
              @click="sendQuery()"
              :disabled="!query.trim() || isTyping"
              class="px-3.5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-700 hover:to-primary-600 text-white rounded-lg disabled:opacity-40 disabled:hover:from-primary-600 disabled:hover:to-primary-500 transition-all shadow-sm hover:shadow-md disabled:cursor-not-allowed"
              aria-label="Send message"
            >
              <SendIcon :size="16" />
            </button>
          </div>
          <div class="flex items-center justify-between mt-2">
            <p class="text-[10px] text-gray-400 dark:text-gray-500">
              Supports English, Urdu, Chinese &amp; Natural Language queries
            </p>
            <div class="flex items-center gap-1">
              <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[9px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                Ctrl+Enter
              </kbd>
              <span class="text-[9px] text-gray-400 dark:text-gray-500">to send</span>
            </div>
          </div>
        </div>

        <!-- ═══ Status Bar ═══ -->
        <div v-show="!isMinimized" class="px-4 py-1.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shrink-0 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1 text-[10px] text-gray-400 dark:text-gray-500">
              <ZapIcon :size="10" />
              {{ responseTime > 0 ? `${responseTime}ms` : '—' }}
            </span>
            <span class="flex items-center gap-1 text-[10px] text-gray-400 dark:text-gray-500">
              <ClockIcon :size="10" />
              {{ formatLastUpdate(lastProcessedAt) }}
            </span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-[10px] text-gray-400 dark:text-gray-500">Session:</span>
            <code class="text-[9px] font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">
              {{ sessionId.slice(0, 14) }}...
            </code>
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
          ? 'bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 shadow-gray-400/20'
          : 'bg-gradient-to-br from-primary-600 to-primary-500 hover:from-primary-700 hover:to-primary-600 shadow-primary-300/40 dark:shadow-primary-900/30'
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
  width: 5px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.12);
  border-radius: 3px;
  transition: background 0.2s ease;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.2);
}
.dark ::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.12);
}
.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* ─── Markdown Content Styling ─── */
:deep(strong) {
  font-weight: 600;
  color: inherit;
}
:deep(em) {
  font-style: italic;
}
:deep(.inline-code) {
  font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
  font-size: 0.875em;
  padding: 0.15em 0.4em;
  background-color: rgba(0, 0, 0, 0.06);
  border-radius: 0.25rem;
  color: #d63fa9;
}
.dark :deep(.inline-code) {
  background-color: rgba(255, 255, 255, 0.08);
  color: #e75ab8;
}

/* ─── Table Styling ─── */
:deep(table) {
  border-spacing: 0;
}
:deep(th) {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
:deep(tr:hover td) {
  background-color: rgba(231, 90, 184, 0.04);
}
.dark :deep(tr:hover td) {
  background-color: rgba(231, 90, 184, 0.08);
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
</style>
