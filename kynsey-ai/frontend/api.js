// API Configuration
const API_BASE = 'http://localhost:3002'; // Base URL for all API endpoints (matches backend port)
const CHAT_ENDPOINT = `${API_BASE}/api/chat`;
const MODELS_ENDPOINT = `${API_BASE}/api/models`;

// Fetch chat response with streaming support
export async function fetchChatResponse(message, history = [], image = null) {
    try {
        const response = await fetch(CHAT_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message,
                history,
                image
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to get response from AI');
        }

        return response;
    } catch (error) {
        console.error('Error in fetchChatResponse:', error);
        throw error;
    }
}

// Get current model profiles and active profile
export async function getModelProfiles() {
    try {
        const response = await fetch(MODELS_ENDPOINT);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching model profiles:', error);
        throw error;
    }
}

// Set active model profile (admin only)
export async function setActiveProfile(profileName, adminSecret) {
    try {
        const response = await fetch(`${MODELS_ENDPOINT}/active`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-admin-secret': adminSecret
            },
            body: JSON.stringify({ profileName })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to set active profile');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error setting active profile:', error);
        throw error;
    }
}

// Utility function to validate response streams
export function isValidJSONResponse(chunk) {
    try {
        return JSON.parse(chunk);
    } catch (e) {
        console.warn('Invalid JSON chunk received:', chunk);
        return null;
    }
}
