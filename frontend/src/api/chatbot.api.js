import axios from './axios';

/**
 * ChatbotApi interaction layer
 * Uses the main axios instance to benefit from global interceptors (auth, data extraction)
 */
const ChatbotApi = {
  /**
   * Query the AI chatbot
   * @param {String} query
   * @returns {Promise} - Returns the inner response object { response, module }
   */
  async query(query) {
    // We use the main axios instance directly to keep the response interceptors
    // We override timeout specifically for AI calls
    return await axios.post('/chatbot/query', { query }, { timeout: 60000 });
  },

  /**
   * Get role-based suggestions
   * @returns {Promise} - Returns array of suggestions
   */
  async getSuggestions() {
    // The interceptor already handles data extraction
    // Backend route /api/v1/chatbot/suggest is a POST route
    return await axios.post('/chatbot/suggest');
  }
};

export default ChatbotApi;
