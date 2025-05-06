import axios from 'axios';

// Настройка Basic Auth
const auth = {
    username: import.meta.env.VITE_API_LOGIN,
    password: import.meta.env.VITE_API_PASS,
};

console.log('import.meta.env.BACKEND_API_URL = ', import.meta.env.VITE_BACKEND_API_URL);


const api = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_API_URL,
    auth,
    headers: {
        'Content-Type': 'application/json',
    },
});

const handleApiError = (error) => {
    if (error.response) {
        throw new Error(`API Error: ${error.response.status} - ${error.response.data.message || error.response.statusText}`);
    } else if (error.request) {
        throw new Error('No response from server');
    } else {
        throw new Error('Request setup error: ' . error.message);
    }
};

const apiCall = async (method, url, data = null) => {
    try {
        const response = await method(url, data);
        return response.data;
    } catch (error) {
        handleApiError(error);
    }
};

export default {
    // Получить список источников
    async getProfiles() {
        return await apiCall(api.get, '/profiles');
    },
    // Создать новый источник
    async createProfile(data) {
        return await apiCall(api.post, '/profiles', data);
    },
    async removeProfile(profileId) {
        return await apiCall(api.delete, `/profiles/${profileId}`);
    },
    // Получить блоки источника
    async getPofile(profileId) {
        return await apiCall(api.get, `/profiles/${profileId}`);
    },
    // Обновить источник
    async updateProfile(profileId, data) {
        return await apiCall(api.put, `/profiles/${profileId}`, data);
    },
    // Запустить парсинг
    async runParsing(profileId) {
        return await apiCall(api.get, `/parse/${profileId}`);
    },
    async refreshParsing(profileId) {
        return await apiCall(api.get, `/parse-refresh/${profileId}`);
    },
    // Получить состояние парсинга
    async getState(profileId) {
        return await apiCall(api.get, `/parse-state/${profileId}`);
    },

};