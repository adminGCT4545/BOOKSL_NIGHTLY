// In Vite, environment variables are accessed via import.meta.env
// No need to use dotenv in the browser environment

// Mock pool and query function for development without PostgreSQL
let pool: any = null;
let isPostgresAvailable = false;

// Try to import pg only if it's available
try {
  // Using dynamic import to avoid the direct dependency that causes the error
  const initializePool = async () => {
    try {
      const { Pool } = await import('pg');
      
      // Create a new pool using environment variables
      pool = new Pool({
        host: process.env.VITE_PGHOST || 'localhost',
        user: process.env.VITE_PGUSER || 'postgres',
        password: process.env.VITE_PGPASSWORD || 'postgres',
        database: process.env.VITE_PGDATABASE || 'booksl_train',
        port: parseInt(process.env.VITE_PGPORT || '5432'),
        // Add SSL configuration if needed
        // ssl: { rejectUnauthorized: false }
      });

      // Test the connection
      pool.on('connect', () => {
        console.log('Connected to PostgreSQL database');
        isPostgresAvailable = true;
      });

      pool.on('error', (err: Error) => {
        console.error('Unexpected error on idle client', err);
        isPostgresAvailable = false;
      });

      // Test connection immediately
      try {
        await pool.query('SELECT 1');
        isPostgresAvailable = true;
      } catch (err) {
        console.error('Failed to connect to PostgreSQL:', err);
        isPostgresAvailable = false;
      }
    } catch (err) {
      console.error('Failed to initialize PostgreSQL pool:', err);
      isPostgresAvailable = false;
    }
  };

  // Initialize pool but don't wait for it
  initializePool();
} catch (err) {
  console.error('PostgreSQL module not available:', err);
  isPostgresAvailable = false;
}

// Export the pool for use in other modules
export default pool;


// Helper function to execute queries
export const query = async (text: string, params?: any[]) => {
  // Mock the query function for testing
  if (process.env.NODE_ENV === 'test') {
    console.log('Using mock query function');
    return {
      rows: [
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
  }

  // Check if PostgreSQL is available
  if (!isPostgresAvailable || !pool) {
    console.warn('PostgreSQL is not available, query cannot be executed');
    throw new Error('PostgreSQL is not available');
  }

  try {
    const start = Date.now();
    const res = await pool.query(text, params);
    const duration = Date.now() - start;
    console.log('Executed query', { text, duration, rows: res.rowCount });
    return res;
  } catch (error) {
    console.error('Error executing query', error);
    throw error;
  }
};
