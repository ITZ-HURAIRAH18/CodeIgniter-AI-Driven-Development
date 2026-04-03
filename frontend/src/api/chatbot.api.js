import axios from './axios';
import { useAuthStore } from '@/store/auth.store';

/**
 * Creates a chatbot-specific axios instance with longer timeout
 */
function createChatbotAxios() {
  const chatbotAxios = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api/v1',
    headers: { 'Content-Type': 'application/json' },
    timeout: 60000, // 60 seconds for AI/LLM API calls
  });

  // Attach auth token
  const auth = useAuthStore();
  if (auth?.accessToken) {
    chatbotAxios.defaults.headers.common['Authorization'] = `Bearer ${auth.accessToken}`;
  }

  return chatbotAxios;
}

const ChatbotApi = {
  /**
   * Query the AI chatbot
   * @param {String} query
   * @returns {Promise} - Returns { success, message, data: { response } }
   */
  async query(query) {
    const chatbotAxios = createChatbotAxios();
    const response = await chatbotAxios.post('/chatbot/query', { query });
    return response;
  },

  /**
   * Get role-based suggestions
   * @returns {Promise} - Returns array of suggestions
   */
  async getSuggestions() {
    const chatbotAxios = createChatbotAxios();
    const response = await chatbotAxios.post('/chatbot/suggest');
    return response.data || response;
  }
};

export default ChatbotApi;
