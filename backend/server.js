import express from 'express';
import pg from 'pg';
import dotenv from 'dotenv';
import cors from 'cors';

dotenv.config();

const app = express();
const port = process.env.PORT || 3000;

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
      console.log('PostgreSQL not available, using fallback data');
      // Return mock data based on the query
      return res.json(fallbackData.trains);
    }

    const result = await pool.query(text, params);
    res.json(result.rows);
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
  
  // Send a test message
  res.write('data: {"message": "Connected to chat stream"}\n\n');
});

app.post('/api/chat', async (req, res) => {
  try {
    const { message } = req.body;
    // For now, just echo back a simple response
    res.json({ response: "Message received: " + message });
  } catch (err) {
    console.error('Error in chat endpoint:', err);
    res.status(500).json({ error: err.message });
  }
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Something broke!' });
});

// Start server
app.listen(port, () => {
  console.log(`Backend server running on port ${port}`);
  console.log(`PostgreSQL status: ${isPostgresAvailable ? 'connected' : 'unavailable (using fallback data)'}`);
});
