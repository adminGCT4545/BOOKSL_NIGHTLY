// This service fetches data from the dashboard backend API and handles live updates
import { query } from './dbService';
import { EventSourcePolyfill } from 'event-source-polyfill';

// Event listeners for live updates
type UpdateListener = (data: any) => void;
const journeyUpdateListeners: UpdateListener[] = [];
const scheduleUpdateListeners: UpdateListener[] = [];
const trainUpdateListeners: UpdateListener[] = [];

// Add event listeners
export const addJourneyUpdateListener = (listener: UpdateListener) => {
  journeyUpdateListeners.push(listener);
};

export const addScheduleUpdateListener = (listener: UpdateListener) => {
  scheduleUpdateListeners.push(listener);
};

export const addTrainUpdateListener = (listener: UpdateListener) => {
  trainUpdateListeners.push(listener);
};

// Remove event listeners
export const removeJourneyUpdateListener = (listener: UpdateListener) => {
  const index = journeyUpdateListeners.indexOf(listener);
  if (index !== -1) {
    journeyUpdateListeners.splice(index, 1);
  }
};

export const removeScheduleUpdateListener = (listener: UpdateListener) => {
  const index = scheduleUpdateListeners.indexOf(listener);
  if (index !== -1) {
    scheduleUpdateListeners.splice(index, 1);
  }
};

export const removeTrainUpdateListener = (listener: UpdateListener) => {
  const index = trainUpdateListeners.indexOf(listener);
  if (index !== -1) {
    trainUpdateListeners.splice(index, 1);
  }
};

// Connect to the SSE endpoint for live updates
let eventSource: EventSourcePolyfill | null = null;

export const connectToLiveUpdates = () => {
  if (eventSource) {
    return; // Already connected
  }
  
  try {
    console.log('Connecting to live updates...');
    eventSource = new EventSourcePolyfill('/api/live-updates');
    
    eventSource.onopen = () => {
      console.log('Connected to live updates');
    };
    
    eventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        console.log('Received live update:', data);
        
        // Dispatch the update to the appropriate listeners
        if (data.type === 'journey_changes') {
          journeyUpdateListeners.forEach(listener => listener(data.data));
        } else if (data.type === 'schedule_changes') {
          scheduleUpdateListeners.forEach(listener => listener(data.data));
        } else if (data.type === 'train_changes') {
          trainUpdateListeners.forEach(listener => listener(data.data));
        }
      } catch (error) {
        console.error('Error processing live update:', error);
      }
    };
    
    eventSource.onerror = (error) => {
      console.error('Error in live updates connection:', error);
      // Try to reconnect after a delay
      if (eventSource) {
        eventSource.close();
        eventSource = null;
        setTimeout(connectToLiveUpdates, 5000);
      }
    };
  } catch (error) {
    console.error('Error connecting to live updates:', error);
    eventSource = null;
  }
};

// Disconnect from live updates
export const disconnectFromLiveUpdates = () => {
  if (eventSource) {
    eventSource.close();
    eventSource = null;
    console.log('Disconnected from live updates');
  }
};

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

// Function to fetch and transform the data from the dashboard API
export const transformData = async (): Promise<TrainEntry[]> => {
  try {
    const response = await fetch('/api/dashboard/journeys');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const journeys = await response.json();
    
    // Ensure we're connected to live updates
    connectToLiveUpdates();
    
    return journeys;
  } catch (error) {
    console.error('Error fetching journey data:', error);
    return getFallbackData();
  }
};

// Function to get all available years in the data
export const getAvailableYears = async (): Promise<number[]> => {
  try {
    const response = await fetch('/api/dashboard/years');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const years = await response.json();
    return years;
  } catch (error) {
    console.error('Error fetching available years:', error);
    // Extract years from fallback data
    const fallbackData = getFallbackData();
    const years = [...new Set(fallbackData.map(entry => entry.year))];
    return years.sort();
  }
};

// Function to get upcoming departures
export const getUpcomingDepartures = async (count: number = 5): Promise<any[]> => {
  try {
    const response = await fetch(`/api/dashboard/upcoming?count=${count}`);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const departures = await response.json();
    return departures;
  } catch (error) {
    console.error('Error fetching upcoming departures:', error);
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

// Function to send a message to the chatbot
export const sendMessageToChatbot = async (message: string, context: any): Promise<any> => {
  try {
    const response = await fetch('/api/chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}` // Ensure OAuth2 protection
      },
      body: JSON.stringify({ message, context })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error('Error sending message to chatbot:', error);
    throw error;
  }
};

// Function to stream chatbot responses using SSE
export const streamChatbotResponse = (onMessage: (data: any) => void, onError: (error: any) => void) => {
  const eventSource = new EventSource('/api/chat/stream');

  eventSource.onmessage = (event) => {
    try {
      const data = JSON.parse(event.data);
      onMessage(data);
    } catch (error) {
      console.error('Error parsing SSE data:', error);
      onError(error);
    }
  };

  eventSource.onerror = (error) => {
    console.error('SSE error:', error);
    onError(error);
  };

  return eventSource;
};
