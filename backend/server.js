import express from 'express';
import pg from 'pg';
import dotenv from 'dotenv';
import cors from 'cors';
import llmService from './services/llmService.js';

dotenv.config();

const app = express();
const port = process.env.PORT || 3001;

// PostgreSQL connection pool
let pool = null;
let isPostgresAvailable = false;

// Initialize pool
const initializePool = async () => {
  try {
    pool = new pg.Pool({
      host: process.env.PGHOST || 'localhost',
      user: process.env.PGUSER || 'postgres',
      password: process.env.PGPASSWORD || 'postgres',
      database: process.env.PGDATABASE || 'booksl_train',
      port: parseInt(process.env.PGPORT || '5432'),
    });

    // Test the connection
    await pool.query('SELECT NOW()');
    console.log('Connected to PostgreSQL database');
    isPostgresAvailable = true;
  } catch (err) {
    console.error('Failed to connect to PostgreSQL:', err);
    isPostgresAvailable = false;
    // Don't throw error, let the server run with fallback data
  }
};

initializePool();

// Middleware
app.use(cors());
app.use(express.json());

// Fallback data for development
const fallbackData = {
  trains: [
    {
      train_id: '101',
      train_name: 'Test Train',
      departure_city: 'Test City',
      arrival_city: 'Test City',
      journey_date: '2025-04-27',
      class: 'Test Class',
      scheduled_time: '12:00:00',
      is_delayed: false,
      delay_minutes: 0,
      total_seats: 100,
      reserved_seats: 50,
      revenue: 1000
    }
  ]
};

// API endpoints
app.get('/api/test', (req, res) => {
  res.json({ 
    message: 'Backend server is running',
    postgresStatus: isPostgresAvailable ? 'connected' : 'unavailable'
  });
});

// Generic query endpoint
app.post('/api/query', async (req, res) => {
  try {
    const { text, params } = req.body;

    if (!isPostgresAvailable) {
      console.log('[Database] PostgreSQL not available, using fallback data');
      // Return mock data with proper query result structure
      return res.json({
        rows: fallbackData.trains,
        command: 'SELECT',
        rowCount: fallbackData.trains.length,
        oid: 0,
        fields: []
      });
    }

    const result = await pool.query(text, params);
    res.json({
      rows: result.rows,
      command: result.command,
      rowCount: result.rowCount,
      oid: result.oid,
      fields: result.fields
    });
  } catch (err) {
    console.error('Error executing query:', err);
    res.status(500).json({ error: err.message });
  }
});

// Chat endpoints
app.get('/api/chat/stream', (req, res) => {
  res.setHeader('Content-Type', 'text/event-stream');
  res.setHeader('Cache-Control', 'no-cache');
  res.setHeader('Connection', 'keep-alive');
  
  // Function to send SSE message
  const sendMessage = (content) => {
    const message = {
      id: Date.now().toString(),
      sender: 'ai',
      content: String(content),
      timestamp: new Date().toISOString()
    };
    res.write(`data: ${JSON.stringify(message)}\n\n`);
  };

  // Store the connection for later use
  const clientId = Date.now();
  activeConnections.set(clientId, sendMessage);

  // Send initial connection message
  sendMessage("Connected to chat stream");

  // Handle client disconnect
  req.on('close', () => {
    activeConnections.delete(clientId);
  });
});

// Store active SSE connections
const activeConnections = new Map();

app.post('/api/chat', async (req, res) => {
  try {
    const { message, context } = req.body;

    if (!message || typeof message !== 'string') {
      throw new Error('Invalid message format');
    }

    const response = await llmService.generateResponse(message, context);
    
    // Send response back directly
    res.json({
      id: Date.now().toString(),
      sender: 'ai',
      content: response,
      timestamp: new Date().toISOString()
    });
  } catch (err) {
    console.error('Error in chat endpoint:', err);
    res.status(500).json({ 
      error: err.message,
      id: Date.now().toString(),
      sender: 'ai',
      content: 'Sorry, I encountered an error processing your request.',
      timestamp: new Date().toISOString()
    });
  }
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Something broke!' });
});

// Start server with LLM initialization
const startServer = async () => {
  try {
    // Initialize LLM service
    await llmService.initialize();
    
    app.listen(port, () => {
      console.log(`Backend server running on port ${port}`);
      console.log(`PostgreSQL status: ${isPostgresAvailable ? 'connected' : 'unavailable (using fallback data)'}`);
      console.log('[LLM] Service status: initialized');
    });
  } catch (error) {
    console.error('[LLM] Failed to initialize:', error);
    process.exit(1);
  }
};

startServer();
