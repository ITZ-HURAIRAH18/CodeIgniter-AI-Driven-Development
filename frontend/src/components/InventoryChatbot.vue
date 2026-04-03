<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';
import { useAuthStore } from '@/store/auth.store';
import { Bot as BotIcon, Send as SendIcon, X as XIcon, MessageSquare as ChatIcon } from 'lucide-vue-next';
import ChatbotApi from '@/api/chatbot.api';

const authStore = useAuthStore();
const isLoggedIn = computed(() => !!authStore.user);
const currentRoleName = computed(() => {
  const roles = { 1: 'Admin', 2: 'Manager', 3: 'Sales' };
  return roles[authStore.user?.role] || 'User';
});

const isOpen = ref(false);
const query = ref('');
const isTyping = ref(false);
const messageBox = ref(null);
const messages = ref([]);
const suggestions = ref([]);

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value && messages.value.length === 0) {
    autoWelcome();
    loadSuggestions();
  }
};

const autoWelcome = () => {
  const greetings = {
    1: 'Hello Admin! I can help you with users, branch summaries, and full inventory audits. What list do you need today?',
    2: 'Manager, welcome. How can I help with your assigned branches and transfers?',
    3: 'Welcome back. Need your order list for this month or product lookup?'
  };
  
  messages.value.push({
    role: 'bot',
    content: greetings[authStore.user?.role] || 'How can I assist you today?',
    type: 'text',
    timestamp: new Date()
  });
};

const loadSuggestions = async () => {
  try {
    const data = await ChatbotApi.getSuggestions();
    // The axios interceptor already extracts the inner data property.
    // If it's undefined or not an array, we fallback.
    suggestions.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (err) {
    console.error('Failed to load suggestions', err);
    suggestions.value = ['Show users list', 'My branches', 'Recent orders'];
  }
};

const sendQuery = async (forceQuery = null) => {
  const text = forceQuery || query.value;
  if (!text.trim() || isTyping.value) return;

  messages.value.push({
    role: 'user',
    content: text,
    type: 'text',
    timestamp: new Date()
  });

  query.value = '';
  isTyping.value = true;
  await scrollDown();

  try {
    const response = await ChatbotApi.query(text);
    // Response format after axios interceptor: { success, message, data: { response: "..." } }
    
    let textResp = "";
    
    // Primary: Check data.response
    if (response?.data?.response) {
      textResp = response.data.response;
    } 
    // Fallback: Check direct response property
    else if (response?.response) {
      textResp = response.response;
    } 
    // Fallback: Check message
    else if (response?.message) {
      textResp = response.message;
    } 
    // Fallback: Raw string
    else if (typeof response === 'string') {
      textResp = response;
    } 
    // Last resort
    else {
      textResp = "I received an empty response. Please try again.";
    }

    const parsedResp = parseMarkdown(textResp);

    messages.value.push({
      role: 'bot',
      ...parsedResp,
      timestamp: new Date()
    });
  } catch (err) {
    console.error('Chatbot error:', err);
    messages.value.push({
      role: 'bot',
      content: 'Sorry, I encountered an error. Please check your connection and try again.',
      type: 'text',
      timestamp: new Date()
    });
  } finally {
    isTyping.value = false;
    scrollDown();
  }
};

const parseMarkdown = (rawText) => {
  if (typeof rawText !== 'string') return { type: 'text', content: String(rawText) };

  // Check for tables (Markdown tables with | and ---)
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
                i++; // Skip separator line (---)
            } else {
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
                rows: tableRows
            }
        };
    }
  }

  // Default: return as formatted text (supports **bold**, *italic*, etc.)
  return {
    type: 'text',
    content: rawText
  };
};

const formatContent = (content) => {
  if (!content) return '';
  return content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br/>');
};

const formatTime = (date) => {
  return new Intl.DateTimeFormat('default', {
    hour: 'numeric',
    minute: 'numeric'
  }).format(date);
};

const scrollDown = async () => {
  await nextTick();
  if (messageBox.value) {
    messageBox.value.scrollTop = messageBox.value.scrollHeight;
  }
};

watch(() => messages.value.length, () => {
    scrollDown();
});
</script>

<template>
  <div v-if="isLoggedIn" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
    
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-8 opacity-0 scale-90"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100 scale-100"
      leave-to-class="transform translate-y-8 opacity-0 scale-90"
    >
      <div 
        v-if="isOpen" 
        class="mb-4 w-[400px] h-[550px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden backdrop-blur-sm"
      >
        <div class="p-4 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex justify-between items-center shrink-0 shadow-md">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-md">
              <BotIcon :size="20" class="text-white" />
            </div>
            <div>
              <h3 class="font-bold text-sm">Inventory AI</h3>
              <p class="text-[10px] opacity-80 flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                Role: {{ currentRoleName }}
              </p>
            </div>
          </div>
          <button @click="isOpen = false" class="p-2 hover:bg-white/10 rounded-full transition-colors">
            <XIcon :size="18" />
          </button>
        </div>

        <div ref="messageBox" class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth bg-slate-50 dark:bg-slate-950/20">
          <div v-for="(msg, idx) in messages" :key="idx" 
               :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start mr-8']">
            
            <div :class="[
              'max-w-[100%] rounded-2xl p-3 text-sm shadow-sm transition-all animate-in fade-in slide-in-from-bottom-2',
              msg.role === 'user' 
                ? 'bg-indigo-600 text-white rounded-tr-none' 
                : 'bg-white dark:bg-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-700/50 rounded-tl-none'
            ]">
              <div v-if="msg.type === 'text'" v-html="formatContent(msg.content)"></div>
              <div v-else-if="msg.type === 'table' && msg.tableData" class="overflow-x-auto my-2">
                <table class="w-full border-collapse border border-slate-200 dark:border-slate-700 text-xs text-left">
                  <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                      <th v-for="h in msg.tableData.headers" :key="h" class="p-2 border border-slate-200 dark:border-slate-700 font-bold uppercase tracking-wider text-[10px]">{{ h }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, ridx) in msg.tableData.rows" :key="ridx" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                      <td v-for="(val, vidx) in row" :key="vidx" class="p-2 border border-slate-200 dark:border-slate-700 whitespace-nowrap">{{ val }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p class="text-[9px] mt-1 opacity-50 flex items-center gap-1 justify-end">
                {{ formatTime(msg.timestamp) }}
              </p>
            </div>
          </div>

          <div v-if="isTyping" class="flex justify-start flex-col gap-1">
             <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none border border-slate-100 dark:border-slate-700/50 shadow-sm w-fit animate-pulse">
                <div class="flex gap-1">
                  <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-600 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                  <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-600 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                  <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-600 rounded-full animate-bounce"></span>
                </div>
             </div>
             <p class="text-[9px] opacity-40 italic">AI is thinking...</p>
          </div>
        </div>

        <div v-if="messages.length < 2 && !isTyping" class="px-4 py-2 border-t border-slate-100 dark:border-slate-800 space-y-1 bg-white dark:bg-slate-900">
          <p class="text-[10px] text-slate-400 font-medium uppercase mb-2">Suggested for you:</p>
          <div class="flex flex-wrap gap-2">
            <button 
              v-for="suggest in suggestions" :key="suggest"
              @click="sendQuery(suggest)"
              class="text-[11px] px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-full transition-all border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/30"
            >
              {{ suggest }}
            </button>
          </div>
        </div>

        <div class="px-4 py-1.5 text-[9px] text-center bg-slate-50 dark:bg-slate-950/20 text-slate-400 border-t border-slate-50 dark:border-slate-800/50">
          Supports English, Urdu, Chinese & Natural Language queries
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
          <div class="flex gap-2">
            <input 
              v-model="query" 
              @keyup.enter="sendQuery()"
              type="text" 
              placeholder="Ask me something about inventory..." 
              class="flex-1 text-sm bg-slate-100 dark:bg-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 dark:text-slate-100 placeholder:text-slate-400 border border-transparent"
              :disabled="isTyping"
            />
            <button 
              @click="sendQuery()"
              :disabled="!query.trim() || isTyping"
              class="p-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl disabled:opacity-50 disabled:hover:bg-indigo-600 transition-all active:scale-95 shadow-md shadow-indigo-200 dark:shadow-none"
            >
              <SendIcon :size="20" />
            </button>
          </div>
        </div>
      </div>
    </transition>

    <button 
      @click="toggleChat"
      :class="[
        'w-14 h-14 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-90 relative group',
        isOpen 
          ? 'bg-red-500 hover:bg-red-600' 
          : 'bg-gradient-to-tr from-indigo-600 to-violet-600 ring-4 ring-indigo-500/20'
      ]"
    >
      <transition mode="out-in">
        <XIcon v-if="isOpen" key="close" class="text-white" />
        <BotIcon v-else key="bot" class="text-white" />
      </transition>
      
      <span v-if="!isOpen && messages.length === 0" class="absolute -top-1 -right-1 flex h-4 w-4">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-4 w-4 bg-sky-500 border-2 border-white dark:border-slate-900"></span>
      </span>
    </button>
  </div>
</template>

<style scoped>
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
}
</style>
