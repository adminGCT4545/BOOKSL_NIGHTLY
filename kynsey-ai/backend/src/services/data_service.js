import { query, logDatabaseActivity } from '../db/db_connection.js';
import { DateTime } from 'luxon';

/**
 * Transform data from the database into a format usable by the dashboard
 * @returns {Promise<Array>} Transformed train data
 */
export async function transformData() {
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

    const result = await query(sql);
    const entries = [];
    let id = 1;

    for (const row of result.rows) {
      const scheduledTime = row.scheduled_time.substring(0, 8); // Format: HH:MM:SS
      const delayMinutes = parseInt(row.delay_minutes);
      const actualTime = calculateActualTime(scheduledTime, delayMinutes);
      const date = DateTime.fromJSDate(new Date(row.journey_date));
      
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
        capacity: parseInt(row.total_seats),
        occupancy: parseInt(row.reserved_seats),
        revenue: parseFloat(row.revenue),
        occupancyRate: (parseInt(row.reserved_seats) / parseInt(row.total_seats)) * 100,
        date: date,
        year: date.year,
        month: date.month,
        quarter: Math.ceil(date.month / 3)
      });
    }

    return entries;
  } catch (error) {
    console.error('Database query failed:', error);
    await logDatabaseActivity('QUERY', 'SELECT', 'transformData query failed', null, 'FAILURE', error.message);
    return getFallbackData();
  }
}

/**
 * Calculate actual time based on scheduled time and delay
 * @param {string} scheduledTime Scheduled time in HH:MM:SS format
 * @param {number} delayMinutes Delay in minutes
 * @returns {string} Actual time in HH:MM:SS format
 */
function calculateActualTime(scheduledTime, delayMinutes) {
  const [hours, minutes, seconds] = scheduledTime.split(':').map(Number);
  const scheduled = DateTime.fromObject({ hour: hours, minute: minutes, second: seconds });
  const actual = scheduled.plus({ minutes: delayMinutes });
  return actual.toFormat('HH:mm:ss');
}

/**
 * Get all available years in the data
 * @returns {Promise<Array>} Array of years
 */
export async function getAvailableYears() {
  try {
    const sql = `
      SELECT DISTINCT EXTRACT(YEAR FROM journey_date) as year
      FROM train_journeys
      ORDER BY year
    `;
    
    const result = await query(sql);
    const years = result.rows.map(row => parseInt(row.year));
    
    return years;
  } catch (error) {
    console.error('Error getting available years:', error);
    await logDatabaseActivity('QUERY', 'SELECT', 'getAvailableYears query failed', null, 'FAILURE', error.message);
    
    // Extract years from fallback data
    const fallbackData = getFallbackData();
    const years = [...new Set(fallbackData.map(entry => entry.year))];
    years.sort();
    return years;
  }
}

/**
 * Get upcoming departures
 * @param {number} count Number of departures to return
 * @returns {Promise<Array>} Array of upcoming departures
 */
export async function getUpcomingDepartures(count = 5) {
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
    
    const result = await query(sql, [count]);
    
    const departures = result.rows.map(row => {
      const scheduledTime = row.scheduled_time.substring(0, 8);
      const delayMinutes = parseInt(row.delay_minutes);
      
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
    
    return departures;
  } catch (error) {
    console.error('Error getting upcoming departures:', error);
    await logDatabaseActivity('QUERY', 'SELECT', 'getUpcomingDepartures query failed', null, 'FAILURE', error.message);
    
    // Use the first few entries from fallback data as upcoming departures
    const fallbackData = getFallbackData();
    const departures = fallbackData.slice(0, count);
    
    return departures.map(entry => ({
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
}

/**
 * Calculate summary statistics from the data
 * @param {Array} data Train data
 * @returns {Object} Summary statistics
 */
export function calculateSummaryStats(data) {
  const stats = {
    totalRevenue: 0,
    averageOccupancyRate: 0,
    averageDelay: 0,
    trainStats: {},
    cityPairStats: {},
    classStats: {}
  };
  
  // Initialize train stats
  for (const train of ['101', '102', '103', '104', '105', '106', '107', '108']) {
    stats.trainStats[train] = {
      count: 0,
      totalRevenue: 0,
      avgOccupancyRate: 0,
      avgDelay: 0,
      delayTrend: []
    };
  }
  
  // Aggregate data
  for (const entry of data) {
    // Overall stats
    stats.totalRevenue += entry.revenue;
    stats.averageOccupancyRate += entry.occupancyRate;
    stats.averageDelay += entry.delay_minutes;
    
    // Train specific stats
    const train = entry.train_id;
    if (stats.trainStats[train]) {
      stats.trainStats[train].count += 1;
      stats.trainStats[train].totalRevenue += entry.revenue;
      stats.trainStats[train].avgOccupancyRate += entry.occupancyRate;
      stats.trainStats[train].avgDelay += entry.delay_minutes;
      
      // Track delay by month for trend analysis
      const monthKey = `${entry.year}-${entry.month.toString().padStart(2, '0')}`;
      let found = false;
      
      for (let i = 0; i < stats.trainStats[train].delayTrend.length; i++) {
        const trend = stats.trainStats[train].delayTrend[i];
        if (trend.month === monthKey) {
          stats.trainStats[train].delayTrend[i].delay += entry.delay_minutes;
          stats.trainStats[train].delayTrend[i].count += 1;
          found = true;
          break;
        }
      }
      
      if (!found) {
        stats.trainStats[train].delayTrend.push({
          month: monthKey,
          delay: entry.delay_minutes,
          count: 1
        });
      }
    }
    
    // City pair stats
    const cityPair = `${entry.from_city}-${entry.to_city}`;
    if (!stats.cityPairStats[cityPair]) {
      stats.cityPairStats[cityPair] = {
        count: 0,
        totalRevenue: 0,
        avgDelay: 0
      };
    }
    stats.cityPairStats[cityPair].count += 1;
    stats.cityPairStats[cityPair].totalRevenue += entry.revenue;
    stats.cityPairStats[cityPair].avgDelay += entry.delay_minutes;
    
    // Class stats
    if (!stats.classStats[entry.class]) {
      stats.classStats[entry.class] = {
        count: 0,
        totalRevenue: 0,
        avgOccupancyRate: 0
      };
    }
    stats.classStats[entry.class].count += 1;
    stats.classStats[entry.class].totalRevenue += entry.revenue;
    stats.classStats[entry.class].avgOccupancyRate += entry.occupancyRate;
  }
  
  // Calculate averages
  const dataCount = data.length;
  if (dataCount > 0) {
    stats.averageOccupancyRate = stats.averageOccupancyRate / dataCount;
    stats.averageDelay = stats.averageDelay / dataCount;
  }
  
  // Calculate averages for train stats
  for (const train in stats.trainStats) {
    const count = stats.trainStats[train].count;
    if (count > 0) {
      stats.trainStats[train].avgOccupancyRate = stats.trainStats[train].avgOccupancyRate / count;
      stats.trainStats[train].avgDelay = stats.trainStats[train].avgDelay / count;
      
      // Calculate average delay by month
      for (let i = 0; i < stats.trainStats[train].delayTrend.length; i++) {
        const month = stats.trainStats[train].delayTrend[i];
        stats.trainStats[train].delayTrend[i].avgDelay = month.delay / month.count;
      }
      
      // Sort delay trend by month
      stats.trainStats[train].delayTrend.sort((a, b) => a.month.localeCompare(b.month));
    }
  }
  
  // Calculate averages for city pair stats
  for (const cityPair in stats.cityPairStats) {
    const count = stats.cityPairStats[cityPair].count;
    if (count > 0) {
      stats.cityPairStats[cityPair].avgDelay = stats.cityPairStats[cityPair].avgDelay / count;
    }
  }
  
  // Calculate averages for class stats
  for (const className in stats.classStats) {
    const count = stats.classStats[className].count;
    if (count > 0) {
      stats.classStats[className].avgOccupancyRate = stats.classStats[className].avgOccupancyRate / count;
    }
  }
  
  return stats;
}

/**
 * Fallback data in case database connection fails
 * @returns {Array} Fallback data
 */
function getFallbackData() {
  console.log('Using fallback data due to database connection issues');
  
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
      date: DateTime.fromISO('2025-01-05'), 
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
      date: DateTime.fromISO('2025-01-05'), 
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
      date: DateTime.fromISO('2025-01-06'), 
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
      date: DateTime.fromISO('2025-01-06'), 
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
      date: DateTime.fromISO('2024-06-15'), 
      year: 2024, 
      month: 6, 
      quarter: 2 
    }
  ];
}

export default {
  transformData,
  getAvailableYears,
  getUpcomingDepartures,
  calculateSummaryStats
};
