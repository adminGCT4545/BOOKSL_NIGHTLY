// Database service that makes API calls to the backend server
import { type QueryResult } from 'pg';

// Types
interface QueryOptions {
  text: string;
  params?: any[];
}

// Test mock data
const mockQueryResult: QueryResult = {
  rows: [{
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
  }],
  command: 'SELECT',
  rowCount: 1,
  oid: 0,
  fields: []
};

// Helper function to execute queries through the backend API
export const query = async (text: string, params?: any[]): Promise<QueryResult> => {
  // Mock the query function for testing
  if (process.env.NODE_ENV === 'test') {
    console.log('Using mock query function');
    return mockQueryResult;
  }

  try {
    const start = Date.now();
    const response = await fetch('/api/query', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ text, params }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const rows = await response.json();
    const duration = Date.now() - start;
    console.log('Executed query', { text, duration, rows: rows.length });

    return {
      rows,
      command: 'SELECT',
      rowCount: rows.length,
      oid: 0,
      fields: []
    };
  } catch (error) {
    console.error('Error executing query:', error);
    throw error;
  }
};

// No need to export pool anymore since we're using the API
export default query;
