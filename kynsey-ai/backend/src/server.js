import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import ollama from 'ollama';
import { getSystemPrompt, getAvailableStyles } from './config/systemPrompt.js';
import fs from 'fs/promises';
import path from 'path';
import { modelProfiles, getProfileByName, getModelIdByName, getDefaultProfile, getAvailableProfileNames } from './config/profiles.js';
import { loadAppState, updateActiveProfile } from './config/appState.js';
import { requireAdmin, logApiRequest } from './middleware/auth.js';

// Load environment variables
dotenv.config();

const app = express();
const port = process.env.PORT || 3001;

// Middleware
app.use(cors({
  origin: 'http://localhost:9000', // Allow our frontend server
  credentials: true,
  methods: ['GET', 'POST', 'OPTIONS']
})); // Configure CORS
app.use(express.json({ limit: '50mb' })); // Increase limit for base64 images

// Basic Ollama client configuration
const ollamaHost = process.env.OLLAMA_HOST || 'http://localhost:11434';
console.log(`Connecting to Ollama at: ${ollamaHost}`);
// Note: The 'ollama' package configures the host via OLLAMA_HOST env var automatically
// or you can pass { host: ollamaHost } to methods if needed.

// --- API Endpoints ---

// Chat Endpoint
app.post('/api/chat', async (req, res) => {
    // Use the globally set activeStyle if not provided in the request
    const { message, history = [], responseStyle = activeStyle, image = null } = req.body;

    if (!message && !image) {
        return res.status(400).json({ error: 'No message or image provided' });
    }

    try {
        const systemPrompt = getSystemPrompt(responseStyle);
        const messages = [
            { role: 'system', content: systemPrompt },
            ...history, // Add past conversation history
        ];

        // Construct the user message, potentially including the image
        const userMessage = {
            role: 'user',
            content: message || "Analyze this image.", // Use default text if only image is sent
        };
        if (image) {
            // Add the base64 image data (ensure frontend sends only base64 part)
            userMessage.images = [image];
        }
        messages.push(userMessage);

        console.log(`Sending request to Ollama model: ${activeModel}`); // Use activeModel variable
        // console.log('Messages:', JSON.stringify(messages, null, 2)); // Debug: Log messages being sent

        // Set up SSE
        res.setHeader('Content-Type', 'text/event-stream');
        res.setHeader('Cache-Control', 'no-cache');
        res.setHeader('Connection', 'keep-alive');

        // Start streaming response
        let fullResponse = '';
        const stream = await ollama.chat({
            model: activeModel,
            messages: messages,
            stream: true,
            options: {
                temperature: 0.7
            }
        });

        // Send each chunk as it arrives
        for await (const chunk of stream) {
            if (chunk.message?.content) {
                fullResponse += chunk.message.content;
                res.write(`data: ${JSON.stringify({ response: chunk.message.content, done: false })}\n\n`);
            }
        }

        // Send final message
        res.write(`data: ${JSON.stringify({ response: fullResponse, done: true })}\n\n`);
        res.end();

    } catch (error) {
        console.error('Ollama API Error:', error);
        res.write(`data: ${JSON.stringify({ error: error.message, done: true })}\n\n`);
        res.end();
    }
});

// --- Configuration State ---
let activeProfileName = getDefaultProfile().name; // Will be updated from stored state
let activeModel = getDefaultProfile().modelId;    // Will be updated from stored state
let activeStyle = 'normal'; // Default style
let activeFileBase = null;  // No default file base initially
let availableFileBases = ['None'];

// Load stored state on startup
loadAppState().then(state => {
    activeProfileName = state.activeProfileName;
    activeModel = getModelIdByName(activeProfileName);
    console.log(`Initialized with profile: ${activeProfileName} (Model: ${activeModel})`);
}).catch(error => {
    console.error('Failed to load app state:', error);
    // Continue with defaults set above
});

// --- API Endpoints for Admin Panel ---

// --- Model Profile API Endpoints ---

// Get available profiles and active profile
app.get('/api/models', async (req, res) => {
    console.log(`[${new Date().toISOString()}] Received GET /api/models request`);
    try {
        console.log(`[${new Date().toISOString()}] Mapping model profiles...`);
        const profiles = modelProfiles.map(p => ({
            name: p.name,
            description: p.description,
            isActive: p.name === activeProfileName
        }));
        console.log(`[${new Date().toISOString()}] Current active profile: ${activeProfileName} (Model: ${activeModel})`);

        // Check if active model is available in Ollama
        console.log(`[${new Date().toISOString()}] Checking availability of model ${activeModel} in Ollama...`);
        const modelAvailable = await ollama.show({ name: activeModel })
            .then(() => {
                console.log(`[${new Date().toISOString()}] Model ${activeModel} is available.`);
                return true;
            })
            .catch((err) => {
                console.warn(`[${new Date().toISOString()}] Model ${activeModel} not found in Ollama:`, err.message);
                return false;
            });

        console.log(`[${new Date().toISOString()}] Sending response for GET /api/models`);
        res.json({
            profiles,
            activeProfile: {
                name: activeProfileName,
                modelId: activeModel,
                isModelAvailable: modelAvailable
            }
        });
    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error in GET /api/models:`, error);
        res.status(500).json({
            error: `Failed to fetch model profiles: ${error.message}`,
            profiles: [],
            activeProfile: {
                name: activeProfileName,
                modelId: activeModel,
                isModelAvailable: false
            }
        });
    }
});

// Set active profile (admin only)
app.post('/api/models/active', requireAdmin, async (req, res) => {
    console.log(`[${new Date().toISOString()}] Received POST /api/models/active request`);
    const { profileName } = req.body;
    console.log(`[${new Date().toISOString()}] Requested profileName: ${profileName}`);

    if (!profileName) {
        console.warn(`[${new Date().toISOString()}] Bad request: profileName missing`);
        return res.status(400).json({ error: 'profileName is required' });
    }

    const profile = getProfileByName(profileName);
    if (!profile) {
        console.warn(`[${new Date().toISOString()}] Invalid profile name requested: ${profileName}`);
        return res.status(400).json({ error: `Invalid profile name: ${profileName}` });
    }

    try {
        console.log(`[${new Date().toISOString()}] Verifying model ${profile.modelId} exists in Ollama...`);
        // Verify model exists in Ollama
        await ollama.show({ name: profile.modelId });
        console.log(`[${new Date().toISOString()}] Model ${profile.modelId} verified.`);

        // Update active profile
        console.log(`[${new Date().toISOString()}] Updating active profile state to ${profileName}...`);
        await updateActiveProfile(profileName);
        activeProfileName = profileName;
        activeModel = profile.modelId;
        console.log(`[${new Date().toISOString()}] Active profile successfully set to: ${activeProfileName} (Model: ${activeModel})`);

        console.log(`[${new Date().toISOString()}] Sending success response for POST /api/models/active`);
        res.json({
            message: `Active profile set to ${activeProfileName}`,
            profile: {
                name: activeProfileName,
                modelId: activeModel
            }
        });
    } catch (error) {
        console.error(`[${new Date().toISOString()}] Error in POST /api/models/active for profile ${profileName}:`, error);
        if (error.message.includes('not found')) {
            console.warn(`[${new Date().toISOString()}] Model ${profile.modelId} not found during profile switch.`);
            return res.status(404).json({
                error: `Model ${profile.modelId} not found in Ollama. Please ensure it is installed.`
            });
        }
        console.error(`[${new Date().toISOString()}] Generic error during profile switch.`);
        res.status(500).json({
            error: `Failed to set active profile: ${error.message}`
        });
    }
});

// Get available styles and the active one
app.get('/api/styles', (req, res) => {
    const availableStyles = getAvailableStyles();
    res.json({ availableStyles, activeStyle });
});

// Set active style
app.post('/api/styles/active', (req, res) => {
    const { styleName } = req.body;
    const availableStyles = getAvailableStyles();
    if (styleName && availableStyles.includes(styleName)) {
        activeStyle = styleName;
        console.log(`Active style set to: ${activeStyle}`);
        res.json({ message: `Active style set to ${activeStyle}.` });
    } else {
        res.status(400).json({ error: 'Invalid or missing styleName' });
    }
});

// Get available file bases and the active one (Placeholder)
app.get('/api/filebases', async (req, res) => {
    // Placeholder: List files in a specific directory or return a fixed list
    // Example: List files in a './data' directory (create if needed)
    // const dataDir = path.join(__dirname, 'data'); // Requires __dirname setup for ES modules
    // try {
    //     await fs.mkdir(dataDir, { recursive: true });
    //     const files = await fs.readdir(dataDir);
    //     availableFileBases = ['None', ...files];
    // } catch (error) {
    //     console.error("Could not read file bases directory:", error);
    //     availableFileBases = ['None']; // Fallback
    // }
    res.json({ availableFileBases, activeFileBase });
});

// Set active file base (Placeholder)
app.post('/api/filebases/active', (req, res) => {
    const { fileBaseName } = req.body;
    // Add validation if listing actual files
    if (fileBaseName && availableFileBases.includes(fileBaseName)) {
         activeFileBase = fileBaseName === 'None' ? null : fileBaseName;
         console.log(`Active file base set to: ${activeFileBase}`);
         res.json({ message: `Active file base set to ${activeFileBase || 'None'}.` });
         // Note: Backend logic to actually USE the file base in chat is not implemented.
    } else {
         res.status(400).json({ error: 'Invalid or missing fileBaseName' });
    }
});


// --- Basic Endpoints ---

// Basic root endpoint
app.get('/', (req, res) => {
    res.send('KYNSEY AI Backend is running!');
});

// Export the app for testing purposes
export default app;

// Start server only if running the script directly
// This allows importing 'app' in tests without starting the server.
// See: https://nodejs.org/api/modules.html#modules_accessing_the_main_module
import { fileURLToPath } from 'url';
const __filename = fileURLToPath(import.meta.url);

if (process.argv[1] === __filename) {
    app.listen(port, () => {
        console.log(`KYNSEY AI Backend listening at http://localhost:${port}`);
    });
}
