// This service transforms the PostgreSQL data into a format that can be used by the Dashboard component
import { query } from './dbService';

export interface TrainJourneyStats {
  journeyId: number;
  trainId: number;
  departureCity: string;
  arrivalCity: string;
  journeyDate: string;
  class: string;
  totalSeats: number;
  reservedSeats: number;
  isDelayed: boolean;
  revenue: number;
}

export interface TrainEntry {
  id: number;
  train_id: string;
  train_name: string;
  departure_date: string;
  from_city: string;
  to_city: string;
  class: string;
  scheduled_time: string;
  actual_time: string;
  delay_minutes: number;
  capacity: number;
  occupancy: number;
  revenue: number;
  occupancyRate: number;
  date: Date;
  year: number;
  month: number;
  quarter: number;
}

// Function to calculate actual time based on scheduled time and delay
const calculateActualTime = (scheduledTime: string, delayMinutes: number): string => {
  const scheduled = new Date(`2000-01-01T${scheduledTime}`);
  scheduled.setMinutes(scheduled.getMinutes() + delayMinutes);
  return scheduled.toTimeString().substring(0, 8);
};

// Function to transform the data into the format expected by the Dashboard component
export const transformData = async (): Promise<TrainEntry[]> => {
  try {
    // Query to join train_journeys with trains and train_schedules
    const sql = `
      SELECT 
        j.journey_id, 
        j.train_id, 
        t.train_name, 
        j.departure_city, 
        j.arrival_city, 
        j.journey_date, 
        j.class, 
        s.scheduled_time, 
        j.is_delayed, 
        CASE WHEN j.is_delayed THEN s.default_delay_minutes ELSE 0 END as delay_minutes,
        j.total_seats, 
        j.reserved_seats, 
        j.revenue
      FROM 
        train_journeys j
      JOIN 
        trains t ON j.train_id = t.train_id
      JOIN 
        train_schedules s ON j.train_id = s.train_id
      ORDER BY 
        j.journey_date
    `;

    try {
      const result = await query(sql);
      const entries: TrainEntry[] = [];
      let id = 1;

      for (const row of result.rows) {
        const scheduledTime = row.scheduled_time.substring(0, 8); // Format: HH:MM:SS
        const delayMinutes = row.delay_minutes;
        const actualTime = calculateActualTime(scheduledTime, delayMinutes);
        const date = new Date(row.journey_date);
        
        entries.push({
          id: id++,
          train_id: row.train_id.toString(),
          train_name: row.train_name,
          departure_date: row.journey_date,
          from_city: row.departure_city,
          to_city: row.arrival_city,
          class: row.class,
          scheduled_time: scheduledTime,
          actual_time: actualTime,
          delay_minutes: delayMinutes,
          capacity: row.total_seats,
          occupancy: row.reserved_seats,
          revenue: parseFloat(row.revenue),
          occupancyRate: (row.reserved_seats / row.total_seats) * 100,
          date,
          year: date.getFullYear(),
          month: date.getMonth() + 1,
          quarter: Math.floor(date.getMonth() / 3) + 1
        });
      }

      return entries;
    } catch (dbError) {
      console.warn('Database query failed, using fallback data:', dbError);
      return getFallbackData();
    }
  } catch (error) {
    console.error('Error transforming data:', error);
    return [];
  }
};

// Function to get all available years in the data
export const getAvailableYears = async (): Promise<number[]> => {
  try {
    const sql = `
      SELECT DISTINCT EXTRACT(YEAR FROM journey_date) as year
      FROM train_journeys
      ORDER BY year
    `;
    
    try {
      const result = await query(sql);
      return result.rows.map((row: { year: string }) => parseInt(row.year));
    } catch (dbError) {
      console.warn('Database query failed, using fallback years:', dbError);
      // Extract years from fallback data
      const fallbackData = getFallbackData();
      const years = [...new Set(fallbackData.map(entry => entry.year))];
      return years.sort();
    }
  } catch (error) {
    console.error('Error getting available years:', error);
    return [];
  }
};

// Function to get upcoming departures
export const getUpcomingDepartures = async (count: number = 5): Promise<any[]> => {
  try {
    const sql = `
      SELECT DISTINCT ON (j.train_id, j.departure_city, j.arrival_city)
        j.train_id,
        t.train_name,
        j.departure_city as from_city,
        j.arrival_city as to_city,
        s.scheduled_time,
        j.is_delayed,
        CASE WHEN j.is_delayed THEN s.default_delay_minutes ELSE 0 END as delay_minutes,
        j.journey_date as departure_date
      FROM 
        train_journeys j
      JOIN 
        trains t ON j.train_id = t.train_id
      JOIN 
        train_schedules s ON j.train_id = s.train_id
      ORDER BY 
        j.train_id, j.departure_city, j.arrival_city, j.journey_date
      LIMIT $1
    `;
    
    try {
      const result = await query(sql, [count]);
      
      return result.rows.map((row: any) => {
        const scheduledTime = row.scheduled_time.substring(0, 8);
        const delayMinutes = row.delay_minutes;
        
        return {
          train_id: row.train_id,
          train_name: row.train_name,
          from_city: row.from_city,
          to_city: row.to_city,
          scheduled_time: scheduledTime,
          actual_time: calculateActualTime(scheduledTime, delayMinutes),
          delay_minutes: delayMinutes,
          departure_date: row.departure_date
        };
      });
    } catch (dbError) {
      console.warn('Database query failed, using fallback departures:', dbError);
      // Use the first few entries from fallback data as upcoming departures
      const fallbackData = getFallbackData();
      return fallbackData.slice(0, count).map(entry => ({
        train_id: entry.train_id,
        train_name: entry.train_name,
        from_city: entry.from_city,
        to_city: entry.to_city,
        scheduled_time: entry.scheduled_time,
        actual_time: entry.actual_time,
        delay_minutes: entry.delay_minutes,
        departure_date: entry.departure_date
      }));
    }
  } catch (error) {
    console.error('Error getting upcoming departures:', error);
    return [];
  }
};

// Fallback data in case database connection fails
const getFallbackData = () => {
  console.warn('Using fallback data due to database connection issues');
  
  // Return a small subset of mock data
  return [
    { 
      id: 1, 
      train_id: '101', 
      train_name: 'Perali Express', 
      departure_date: '2025-01-05', 
      from_city: 'Colombo', 
      to_city: 'Kandy', 
      class: 'First', 
      scheduled_time: '06:00:00', 
      actual_time: '06:05:00', 
      delay_minutes: 5, 
      capacity: 50, 
      occupancy: 45, 
      revenue: 22500.00, 
      occupancyRate: 90, 
      date: new Date('2025-01-05'), 
      year: 2025, 
      month: 1, 
      quarter: 1 
    },
    { 
      id: 2, 
      train_id: '102', 
      train_name: 'Udarata Menike', 
      departure_date: '2025-01-05', 
      from_city: 'Colombo', 
      to_city: 'Ella', 
      class: 'Second', 
      scheduled_time: '07:30:00', 
      actual_time: '07:38:00', 
      delay_minutes: 8, 
      capacity: 80, 
      occupancy: 65, 
      revenue: 19500.00, 
      occupancyRate: 81.25, 
      date: new Date('2025-01-05'), 
      year: 2025, 
      month: 1, 
      quarter: 1 
    },
    { 
      id: 3, 
      train_id: '103', 
      train_name: 'Kandy Intercity', 
      departure_date: '2025-01-06', 
      from_city: 'Kandy', 
      to_city: 'Ella', 
      class: 'Third', 
      scheduled_time: '14:00:00', 
      actual_time: '14:00:00', 
      delay_minutes: 0, 
      capacity: 120, 
      occupancy: 95, 
      revenue: 14250.00, 
      occupancyRate: 79.17, 
      date: new Date('2025-01-06'), 
      year: 2025, 
      month: 1, 
      quarter: 1 
    },
    { 
      id: 4, 
      train_id: '104', 
      train_name: 'Ella Odyssey', 
      departure_date: '2025-01-06', 
      from_city: 'Colombo', 
      to_city: 'Kandy', 
      class: 'Second', 
      scheduled_time: '16:30:00', 
      actual_time: '16:45:00', 
      delay_minutes: 15, 
      capacity: 80, 
      occupancy: 78, 
      revenue: 23400.00, 
      occupancyRate: 97.5, 
      date: new Date('2025-01-06'), 
      year: 2025, 
      month: 1, 
      quarter: 1 
    },
    { 
      id: 5, 
      train_id: '105', 
      train_name: 'Rajarata Express', 
      departure_date: '2024-06-15', 
      from_city: 'Colombo', 
      to_city: 'Ella', 
      class: 'Third', 
      scheduled_time: '08:15:00', 
      actual_time: '08:25:00', 
      delay_minutes: 10, 
      capacity: 120, 
      occupancy: 81, 
      revenue: 12478.04, 
      occupancyRate: 67.5, 
      date: new Date('2024-06-15'), 
      year: 2024, 
      month: 6, 
      quarter: 2 
    }
  ];
};
