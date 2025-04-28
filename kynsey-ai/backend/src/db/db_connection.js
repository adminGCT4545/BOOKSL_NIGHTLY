import pg from 'pg';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Create a PostgreSQL connection pool
const pool = new pg.Pool({
  host: process.env.PGHOST || 'localhost',
  user: process.env.PGUSER || 'postgres',
  password: process.env.PGPASSWORD || 'postgres',
  database: process.env.PGDATABASE || 'booksl_train',
  port: process.env.PGPORT || 5432,
});

/**
 * Execute a database query
 * @param {string} text - SQL query text
 * @param {Array} params - Query parameters
 * @returns {Promise} - Query result
 */
export async function query(text, params) {
  try {
    const start = Date.now();
    const res = await pool.query(text, params);
    const duration = Date.now() - start;
    console.log('Executed query', { text, duration, rows: res.rowCount });
    return res;
  } catch (error) {
    console.error('Database query error:', error);
    throw error;
  }
}

/**
 * Check if database connection is available
 * @returns {Promise<boolean>} - Whether database is available
 */
export async function isDatabaseAvailable() {
  try {
    const res = await pool.query('SELECT 1');
    return res.rows.length > 0;
  } catch (error) {
    console.error('Database connection check failed:', error);
    return false;
  }
}

/**
 * Log a database activity
 * @param {string} logType - Type of log (QUERY, CONNECTION, SYSTEM)
 * @param {string} operation - Operation being performed
 * @param {string} details - Details of the operation
 * @param {Object} parameters - Parameters used in the query
 * @param {string} status - Status of the operation
 * @param {string} errorMessage - Error message if operation failed
 * @returns {Promise<boolean>} - Whether logging was successful
 */
export async function logDatabaseActivity(logType, operation, details, parameters = null, status = 'SUCCESS', errorMessage = null) {
  try {
    // Check if the system_logs table exists
    const checkTableRes = await query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'system_logs'
      );
    `);
    
    const tableExists = checkTableRes.rows[0].exists;

    // Create the table if it doesn't exist
    if (!tableExists) {
      await query(`
        CREATE TABLE system_logs (
          log_id SERIAL PRIMARY KEY,
          log_type VARCHAR(50) NOT NULL,
          operation VARCHAR(255) NOT NULL,
          query_details TEXT,
          parameters TEXT,
          result_status VARCHAR(50),
          error_message TEXT,
          user_id VARCHAR(100),
          ip_address VARCHAR(45),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
      `);
    }

    // Insert the log entry
    await query(`
      INSERT INTO system_logs (
        log_type, operation, query_details, parameters, result_status, error_message, user_id, ip_address
      ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8
      );
    `, [
      logType,
      operation,
      details,
      parameters ? JSON.stringify(parameters) : null,
      status,
      errorMessage,
      'admin', // Hardcoded for now, could be from session
      'unknown' // Could be from request.ip in Express
    ]);
    
    return true;
  } catch (error) {
    console.error('Error logging database activity:', error);
    return false;
  }
}

export default {
  query,
  isDatabaseAvailable,
  logDatabaseActivity
};
