// This service transforms the SQL data into a format that can be used by the Dashboard component

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

// Mock data based on the SQL dataset from complete_train_dataset(2).sql
const trainJourneyStats: TrainJourneyStats[] = [
  // January 2025 data
  { journeyId: 1, trainId: 101, departureCity: 'Colombo', arrivalCity: 'Kandy', journeyDate: '2025-01-05', class: 'First', totalSeats: 50, reservedSeats: 45, isDelayed: true, revenue: 22500.00 },
  { journeyId: 2, trainId: 101, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2025-01-05', class: 'First', totalSeats: 50, reservedSeats: 48, isDelayed: false, revenue: 24000.00 },
  { journeyId: 3, trainId: 102, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2025-01-05', class: 'Second', totalSeats: 80, reservedSeats: 65, isDelayed: false, revenue: 19500.00 },
  { journeyId: 4, trainId: 102, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2025-01-05', class: 'Second', totalSeats: 80, reservedSeats: 72, isDelayed: true, revenue: 21600.00 },
  { journeyId: 5, trainId: 103, departureCity: 'Kandy', arrivalCity: 'Ella', journeyDate: '2025-01-06', class: 'Third', totalSeats: 120, reservedSeats: 95, isDelayed: false, revenue: 14250.00 },
  { journeyId: 6, trainId: 103, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2025-01-06', class: 'Third', totalSeats: 120, reservedSeats: 110, isDelayed: false, revenue: 16500.00 },
  { journeyId: 7, trainId: 104, departureCity: 'Colombo', arrivalCity: 'Kandy', journeyDate: '2025-01-06', class: 'Second', totalSeats: 80, reservedSeats: 78, isDelayed: true, revenue: 23400.00 },
  { journeyId: 8, trainId: 104, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2025-01-06', class: 'Second', totalSeats: 80, reservedSeats: 76, isDelayed: false, revenue: 22800.00 },
  { journeyId: 9, trainId: 101, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2025-01-07', class: 'First', totalSeats: 50, reservedSeats: 42, isDelayed: false, revenue: 25200.00 },
  { journeyId: 10, trainId: 101, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2025-01-07', class: 'First', totalSeats: 50, reservedSeats: 47, isDelayed: true, revenue: 28200.00 },
  { journeyId: 11, trainId: 102, departureCity: 'Kandy', arrivalCity: 'Ella', journeyDate: '2025-01-07', class: 'Third', totalSeats: 120, reservedSeats: 85, isDelayed: false, revenue: 12750.00 },
  { journeyId: 12, trainId: 102, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2025-01-07', class: 'Third', totalSeats: 120, reservedSeats: 92, isDelayed: true, revenue: 13800.00 },
  
  // February 2025 data
  { journeyId: 13, trainId: 103, departureCity: 'Colombo', arrivalCity: 'Kandy', journeyDate: '2025-02-10', class: 'First', totalSeats: 50, reservedSeats: 49, isDelayed: false, revenue: 24500.00 },
  { journeyId: 14, trainId: 103, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2025-02-10', class: 'First', totalSeats: 50, reservedSeats: 50, isDelayed: false, revenue: 25000.00 },
  { journeyId: 15, trainId: 104, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2025-02-10', class: 'Second', totalSeats: 80, reservedSeats: 64, isDelayed: true, revenue: 19200.00 },
  { journeyId: 16, trainId: 104, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2025-02-10', class: 'Second', totalSeats: 80, reservedSeats: 70, isDelayed: false, revenue: 21000.00 },
  { journeyId: 17, trainId: 101, departureCity: 'Kandy', arrivalCity: 'Ella', journeyDate: '2025-02-11', class: 'Third', totalSeats: 120, reservedSeats: 98, isDelayed: false, revenue: 14700.00 },
  { journeyId: 18, trainId: 101, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2025-02-11', class: 'Third', totalSeats: 120, reservedSeats: 105, isDelayed: false, revenue: 15750.00 },
  
  // 2024 data
  { journeyId: 19, trainId: 105, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2024-01-11', class: 'Third', totalSeats: 120, reservedSeats: 113, isDelayed: false, revenue: 18211.05 },
  { journeyId: 20, trainId: 101, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2024-07-22', class: 'Third', totalSeats: 120, reservedSeats: 74, isDelayed: false, revenue: 10718.51 },
  { journeyId: 21, trainId: 105, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2024-06-15', class: 'Third', totalSeats: 120, reservedSeats: 81, isDelayed: false, revenue: 12478.04 },
  { journeyId: 22, trainId: 102, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'Third', totalSeats: 120, reservedSeats: 72, isDelayed: false, revenue: 11056.98 },
  { journeyId: 23, trainId: 103, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'First', totalSeats: 50, reservedSeats: 37, isDelayed: true, revenue: 24074.57 },
  { journeyId: 24, trainId: 103, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2024-06-15', class: 'Second', totalSeats: 80, reservedSeats: 54, isDelayed: false, revenue: 14909.75 },
  { journeyId: 25, trainId: 103, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'Third', totalSeats: 120, reservedSeats: 98, isDelayed: true, revenue: 13754.50 },
  { journeyId: 26, trainId: 108, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'Second', totalSeats: 80, reservedSeats: 60, isDelayed: true, revenue: 17081.62 },
  { journeyId: 27, trainId: 102, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'Third', totalSeats: 120, reservedSeats: 106, isDelayed: false, revenue: 14861.04 },
  { journeyId: 28, trainId: 103, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2024-06-15', class: 'Second', totalSeats: 80, reservedSeats: 70, isDelayed: false, revenue: 19544.18 },
  { journeyId: 29, trainId: 105, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2024-06-15', class: 'First', totalSeats: 50, reservedSeats: 48, isDelayed: true, revenue: 30462.16 },
  { journeyId: 30, trainId: 103, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2024-06-15', class: 'First', totalSeats: 50, reservedSeats: 47, isDelayed: false, revenue: 26636.58 },
  
  // Additional data
  { journeyId: 31, trainId: 106, departureCity: 'Kandy', arrivalCity: 'Ella', journeyDate: '2024-03-22', class: 'Third', totalSeats: 120, reservedSeats: 102, isDelayed: true, revenue: 15956.40 },
  { journeyId: 32, trainId: 104, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2024-07-14', class: 'Second', totalSeats: 80, reservedSeats: 65, isDelayed: false, revenue: 20345.85 },
  { journeyId: 33, trainId: 107, departureCity: 'Colombo', arrivalCity: 'Kandy', journeyDate: '2024-10-05', class: 'First', totalSeats: 50, reservedSeats: 43, isDelayed: true, revenue: 27144.90 },
  { journeyId: 34, trainId: 108, departureCity: 'Kandy', arrivalCity: 'Ella', journeyDate: '2024-11-18', class: 'Third', totalSeats: 120, reservedSeats: 96, isDelayed: true, revenue: 15342.72 },
  { journeyId: 35, trainId: 105, departureCity: 'Ella', arrivalCity: 'Colombo', journeyDate: '2024-05-29', class: 'Second', totalSeats: 80, reservedSeats: 75, isDelayed: false, revenue: 21956.25 },
  { journeyId: 36, trainId: 107, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2025-03-12', class: 'First', totalSeats: 50, reservedSeats: 47, isDelayed: false, revenue: 28752.06 },
  { journeyId: 37, trainId: 104, departureCity: 'Ella', arrivalCity: 'Kandy', journeyDate: '2025-01-23', class: 'Third', totalSeats: 120, reservedSeats: 108, isDelayed: true, revenue: 17562.60 },
  { journeyId: 38, trainId: 106, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2025-02-09', class: 'Second', totalSeats: 80, reservedSeats: 62, isDelayed: false, revenue: 18937.50 },
  { journeyId: 39, trainId: 105, departureCity: 'Colombo', arrivalCity: 'Ella', journeyDate: '2024-12-24', class: 'First', totalSeats: 50, reservedSeats: 48, isDelayed: true, revenue: 29760.96 },
  { journeyId: 40, trainId: 108, departureCity: 'Kandy', arrivalCity: 'Colombo', journeyDate: '2025-04-07', class: 'Third', totalSeats: 120, reservedSeats: 97, isDelayed: false, revenue: 15941.25 }
];

// Train names mapping
const trainNames: Record<number, string> = {
  101: 'Perali Express',
  102: 'Udarata Menike',
  103: 'Kandy Intercity',
  104: 'Ella Odyssey',
  105: 'Rajarata Express',
  106: 'Yal Devi',
  107: 'Podi Menike',
  108: 'Ruhunu Kumari'
};

// Default scheduled and actual times for each train
const trainSchedules: Record<number, { scheduled: string, delay: number }> = {
  101: { scheduled: '06:00:00', delay: 5 },
  102: { scheduled: '07:30:00', delay: 8 },
  103: { scheduled: '14:00:00', delay: 0 },
  104: { scheduled: '16:30:00', delay: 15 },
  105: { scheduled: '08:15:00', delay: 10 },
  106: { scheduled: '10:45:00', delay: 0 },
  107: { scheduled: '12:30:00', delay: 7 },
  108: { scheduled: '18:00:00', delay: 12 }
};

// Function to calculate actual time based on scheduled time and delay
const calculateActualTime = (scheduledTime: string, delayMinutes: number): string => {
  const scheduled = new Date(`2000-01-01T${scheduledTime}`);
  scheduled.setMinutes(scheduled.getMinutes() + delayMinutes);
  return scheduled.toTimeString().substring(0, 8);
};

// Function to transform the data into the format expected by the Dashboard component
export const transformData = (): TrainEntry[] => {
  const entries: TrainEntry[] = [];
  let id = 1;

  for (const journey of trainJourneyStats) {
    const trainName = trainNames[journey.trainId] || `Train ${journey.trainId}`;
    const schedule = trainSchedules[journey.trainId] || { scheduled: '12:00:00', delay: 0 };
    const scheduledTime = schedule.scheduled;
    const delayMinutes = journey.isDelayed ? schedule.delay : 0;
    const actualTime = calculateActualTime(scheduledTime, delayMinutes);
    
    const date = new Date(journey.journeyDate);
    
    entries.push({
      id: id++,
      train_id: journey.trainId.toString(),
      train_name: trainName,
      departure_date: journey.journeyDate,
      from_city: journey.departureCity,
      to_city: journey.arrivalCity,
      class: journey.class,
      scheduled_time: scheduledTime,
      actual_time: actualTime,
      delay_minutes: delayMinutes,
      capacity: journey.totalSeats,
      occupancy: journey.reservedSeats,
      revenue: journey.revenue,
      occupancyRate: (journey.reservedSeats / journey.totalSeats) * 100,
      date,
      year: date.getFullYear(),
      month: date.getMonth() + 1,
      quarter: Math.floor(date.getMonth() / 3) + 1
    });
  }

  return entries;
};

// Function to get all available years in the data
export const getAvailableYears = (): number[] => {
  const years = new Set<number>();
  trainJourneyStats.forEach(journey => {
    const year = new Date(journey.journeyDate).getFullYear();
    years.add(year);
  });
  return Array.from(years).sort();
};

// Function to get upcoming departures
export const getUpcomingDepartures = (count: number = 5): any[] => {
  const result: any[] = [];
  
  // Sort journeys by date
  const sortedJourneys = [...trainJourneyStats].sort((a, b) => {
    return new Date(a.journeyDate).getTime() - new Date(b.journeyDate).getTime();
  });
  
  // Get unique train-route combinations
  const uniqueTrainRoutes = new Map<string, TrainJourneyStats>();
  sortedJourneys.forEach(journey => {
    const key = `${journey.trainId}-${journey.departureCity}-${journey.arrivalCity}`;
    if (!uniqueTrainRoutes.has(key)) {
      uniqueTrainRoutes.set(key, journey);
    }
  });
  
  // Convert to array and take first 'count' entries
  const uniqueJourneys = Array.from(uniqueTrainRoutes.values()).slice(0, count);
  
  // Transform to the expected format
  for (const journey of uniqueJourneys) {
    const trainName = trainNames[journey.trainId] || `Train ${journey.trainId}`;
    const schedule = trainSchedules[journey.trainId] || { scheduled: '12:00:00', delay: 0 };
    const scheduledTime = schedule.scheduled;
    const delayMinutes = journey.isDelayed ? schedule.delay : 0;
    
    result.push({
      train_id: journey.trainId,
      train_name: trainName,
      from_city: journey.departureCity,
      to_city: journey.arrivalCity,
      scheduled_time: scheduledTime,
      actual_time: calculateActualTime(scheduledTime, delayMinutes),
      delay_minutes: delayMinutes,
      departure_date: journey.journeyDate
    });
  }
  
  return result;
};
