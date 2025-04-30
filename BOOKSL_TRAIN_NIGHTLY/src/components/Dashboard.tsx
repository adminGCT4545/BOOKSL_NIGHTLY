import React, { createContext, useEffect } from 'react';
import { BarChart, Bar, LineChart, Line, PieChart, Pie, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, Cell, AreaChart, Area } from 'recharts';
import { 
  transformData, 
  getAvailableYears, 
  getUpcomingDepartures, 
  TrainEntry as DataServiceTrainEntry,
  addJourneyUpdateListener,
  addScheduleUpdateListener,
  addTrainUpdateListener,
  removeJourneyUpdateListener,
  removeScheduleUpdateListener,
  removeTrainUpdateListener
} from '../services/dataService';
import ChatInterface, { ChatProvider } from './ChatInterface';

// Rename to avoid conflict with imported type
type TrainEntryType = DataServiceTrainEntry;

interface DashboardContextType {
  selectedTrain?: string;
  selectedYear?: string;
  currentMetrics?: {
    occupancyRate?: number;
    delay?: number;
    revenue?: number;
  };
}

export const DashboardContext = createContext<DashboardContextType>({});

interface DelayTrendItem {
  month: string;
  delay: number;
  count: number;
  avgDelay?: number;
}

interface TrainStats {
  count: number;
  totalRevenue: number;
  avgOccupancyRate: number;
  avgDelay: number;
  delayTrend: DelayTrendItem[];
}

interface CityPairStats {
  count: number;
  totalRevenue: number;
  avgDelay: number;
}

interface ClassStats {
  count: number;
  totalRevenue: number;
  avgOccupancyRate: number;
}

interface SummaryStats {
  totalRevenue: number;
  averageOccupancyRate: number;
  averageDelay: number;
  trainStats: Record<string, TrainStats>;
  cityPairStats: Record<string, CityPairStats>;
  classStats: Record<string, ClassStats>;
}

interface TrainMetricDataItem {
  train: string;
  value: number;
}

interface DelayTrendDataItem {
  month: string;
  [key: string]: number | string;
}

interface CityPairDataItem {
  cityPair: string;
  avgDelay: number;
  totalRevenue: number;
}

interface ClassDataItem {
  name: string;
  value: number;
}

const Dashboard: React.FC = () => {
  const [data, setData] = React.useState<TrainEntryType[]>([]);
  const [isLoading, setIsLoading] = React.useState<boolean>(true);
  const [availableYears, setAvailableYears] = React.useState<number[]>([]);
  const [upcomingDepartures, setUpcomingDepartures] = React.useState<any[]>([]);
  const [summaryStats, setSummaryStats] = React.useState<SummaryStats>({
    totalRevenue: 0,
    averageOccupancyRate: 0,
    averageDelay: 0,
    trainStats: {},
    cityPairStats: {},
    classStats: {}
  });
  const [selectedMetric, setSelectedMetric] = React.useState<string>('occupancy');
  const [selectedYear, setSelectedYear] = React.useState<string>('all');

  // Colors for the charts
  const COLORS = ['#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'];
  const TRAIN_COLORS: Record<string, string> = {
    '101': '#7e57c2',
    '102': '#4e7fff',
    '103': '#b39ddb',
    '104': '#64b5f6',
    '105': '#9575cd',
    '106': '#5c6bc0',
    '107': '#7986cb',
    '108': '#4fc3f7'
  };

  React.useEffect(() => {
    const loadData = async () => {
      try {
        setIsLoading(true);
        
        // Get data from the data service
        const transformedData = await transformData();
        setData(transformedData);
        
        // Get upcoming departures
        const departures = await getUpcomingDepartures(5);
        setUpcomingDepartures(departures);
        
        // Calculate summary statistics
        calculateSummaryStats(transformedData);
        
        // Get available years for the filter
        const years = await getAvailableYears();
        if (years.length > 0) {
          setAvailableYears(years);
        }
        
        setIsLoading(false);
      } catch (error) {
        console.error('Error loading data:', error);
        setIsLoading(false);
      }
    };
    
    loadData();
    
    // Set up live update listeners
    const handleJourneyUpdate = async (journeyData: any) => {
      console.log('Journey update received:', journeyData);
      
      // Reload data to get the latest state
      try {
        const transformedData = await transformData();
        setData(transformedData);
        calculateSummaryStats(transformedData);
        
        // Also update upcoming departures
        const departures = await getUpcomingDepartures(5);
        setUpcomingDepartures(departures);
      } catch (error) {
        console.error('Error updating data after journey change:', error);
      }
    };
    
    const handleScheduleUpdate = async (scheduleData: any) => {
      console.log('Schedule update received:', scheduleData);
      
      // Reload upcoming departures
      try {
        const departures = await getUpcomingDepartures(5);
        setUpcomingDepartures(departures);
      } catch (error) {
        console.error('Error updating departures after schedule change:', error);
      }
    };
    
    const handleTrainUpdate = async (trainData: any) => {
      console.log('Train update received:', trainData);
      
      // Reload data to get the latest train names
      try {
        const transformedData = await transformData();
        setData(transformedData);
        calculateSummaryStats(transformedData);
      } catch (error) {
        console.error('Error updating data after train change:', error);
      }
    };
    
    // Add listeners
    addJourneyUpdateListener(handleJourneyUpdate);
    addScheduleUpdateListener(handleScheduleUpdate);
    addTrainUpdateListener(handleTrainUpdate);
    
    // Clean up listeners on unmount
    return () => {
      removeJourneyUpdateListener(handleJourneyUpdate);
      removeScheduleUpdateListener(handleScheduleUpdate);
      removeTrainUpdateListener(handleTrainUpdate);
    };
  }, []);
  
  const calculateSummaryStats = (data: TrainEntryType[]) => {
    const stats: SummaryStats = {
      totalRevenue: 0,
      averageOccupancyRate: 0,
      averageDelay: 0,
      trainStats: {} as Record<string, TrainStats>,
      cityPairStats: {} as Record<string, CityPairStats>,
      classStats: {} as Record<string, ClassStats>
    };
    
    // Initialize train stats
    ['101', '102', '103', '104', '105', '106', '107', '108'].forEach(train => {
      stats.trainStats[train] = {
        count: 0,
        totalRevenue: 0,
        avgOccupancyRate: 0,
        avgDelay: 0,
        delayTrend: []
      };
    });
    
    // Aggregate data
    data.forEach((entry: TrainEntryType) => {
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
        const monthKey = `${entry.year}-${String(entry.month).padStart(2, '0')}`;
        if (!stats.trainStats[train].delayTrend.find(d => d.month === monthKey)) {
          stats.trainStats[train].delayTrend.push({
            month: monthKey,
            delay: entry.delay_minutes,
            count: 1
          });
        } else {
          const index = stats.trainStats[train].delayTrend.findIndex(d => d.month === monthKey);
          stats.trainStats[train].delayTrend[index].delay += entry.delay_minutes;
          stats.trainStats[train].delayTrend[index].count += 1;
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
    });
    
    // Calculate averages
    stats.averageOccupancyRate = stats.averageOccupancyRate / data.length;
    stats.averageDelay = stats.averageDelay / data.length;
    
    // Calculate averages for train stats
    Object.keys(stats.trainStats).forEach(train => {
      const count = stats.trainStats[train].count;
      if (count > 0) {
        stats.trainStats[train].avgOccupancyRate = stats.trainStats[train].avgOccupancyRate / count;
        stats.trainStats[train].avgDelay = stats.trainStats[train].avgDelay / count;
        
        // Calculate average delay by month
        stats.trainStats[train].delayTrend.forEach(month => {
          month.avgDelay = month.delay / month.count;
        });
        
        // Sort delay trend by month
        stats.trainStats[train].delayTrend.sort((a, b) => a.month.localeCompare(b.month));
      }
    });
    
    // Calculate averages for city pair stats
    Object.keys(stats.cityPairStats).forEach(cityPair => {
      const count = stats.cityPairStats[cityPair].count;
      if (count > 0) {
        stats.cityPairStats[cityPair].avgDelay = stats.cityPairStats[cityPair].avgDelay / count;
      }
    });
    
    // Calculate averages for class stats
    Object.keys(stats.classStats).forEach(cls => {
      const count = stats.classStats[cls].count;
      if (count > 0) {
        stats.classStats[cls].avgOccupancyRate = stats.classStats[cls].avgOccupancyRate / count;
      }
    });
    
    setSummaryStats(stats);
  };
  
  // Filter data based on selected year
  const filteredData = selectedYear === 'all' 
    ? data 
    : data.filter((entry: TrainEntryType) => entry.year === parseInt(selectedYear));
  
  // Prepare data for charts
  const prepareTrainMetricData = (): TrainMetricDataItem[] => {
    const result: TrainMetricDataItem[] = [];
    
    if (summaryStats && summaryStats.trainStats) {
      Object.keys(summaryStats.trainStats).forEach(train => {
        let value: number = 0;
        
        if (selectedMetric === 'occupancy') {
          value = summaryStats.trainStats[train].avgOccupancyRate;
        } else if (selectedMetric === 'delay') {
          value = summaryStats.trainStats[train].avgDelay;
        } else if (selectedMetric === 'revenue') {
          value = summaryStats.trainStats[train].totalRevenue;
        }
        
        result.push({
          train,
          value
        });
      });
    }
    
    return result;
  };
  
  const prepareDelayTrendData = (): DelayTrendDataItem[] => {
    const result: DelayTrendDataItem[] = [];
    
    // Combine all months from all trains to get a complete list
    const allMonths = new Set<string>();
    if (summaryStats && summaryStats.trainStats) {
      Object.keys(summaryStats.trainStats).forEach(train => {
        summaryStats.trainStats[train].delayTrend.forEach(item => {
          allMonths.add(item.month);
        });
      });
    }
    
    // Create data points for all months and trains
    const sortedMonths = Array.from(allMonths).sort();
    sortedMonths.forEach((month: string) => {
      const dataPoint: DelayTrendDataItem = { month };
      
      if (summaryStats && summaryStats.trainStats) {
        Object.keys(summaryStats.trainStats).forEach(train => {
          const monthData = summaryStats.trainStats[train].delayTrend.find(item => item.month === month);
          dataPoint[train] = monthData && monthData.avgDelay !== undefined ? monthData.avgDelay : 0;
        });
      }
      
      result.push(dataPoint);
    });
    
    return result;
  };
  
  const prepareCityPairData = (): CityPairDataItem[] => {
    const result: CityPairDataItem[] = [];
    
    if (summaryStats && summaryStats.cityPairStats) {
      Object.keys(summaryStats.cityPairStats).forEach(cityPair => {
        result.push({
          cityPair,
          avgDelay: summaryStats.cityPairStats[cityPair].avgDelay,
          totalRevenue: summaryStats.cityPairStats[cityPair].totalRevenue
        });
      });
      
      // Sort by average delay
      result.sort((a, b) => b.avgDelay - a.avgDelay);
    }
    
    return result.slice(0, 5); // Top 5 city pairs by delay
  };
  
  const prepareClassData = (): ClassDataItem[] => {
    const result: ClassDataItem[] = [];
    
    if (summaryStats && summaryStats.classStats) {
      Object.keys(summaryStats.classStats).forEach(cls => {
        result.push({
          name: cls,
          value: summaryStats.classStats[cls].totalRevenue
        });
      });
    }
    
    return result;
  };
  
  if (isLoading) {
    return <div className="text-center p-10 text-dashboard-text">Loading data...</div>;
  }
  
  // Prepare dashboard context value
  const dashboardContextValue = {
    selectedTrain: undefined,
    selectedYear,
    currentMetrics: {
      occupancyRate: summaryStats.averageOccupancyRate,
      delay: summaryStats.averageDelay,
      revenue: summaryStats.totalRevenue / data.length
    }
  };

  return (
    <DashboardContext.Provider value={dashboardContextValue}>
      <ChatProvider>
        <div className="p-4">
      <div className="mb-4 px-2 flex justify-between items-center">
        <h1 className="text-xl font-medium text-dashboard-header">Train Operations Dashboard</h1>
        <div className="flex items-center space-x-4">
          <div>
            <label htmlFor="metricSelect" className="text-dashboard-subtext mr-2 text-sm">Metric:</label>
            <select 
              id="metricSelect"
              className="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
              value={selectedMetric}
              onChange={(e) => setSelectedMetric(e.target.value)}
            >
              <option value="occupancy">Occupancy Rate</option>
              <option value="delay">Delay</option>
              <option value="revenue">Revenue</option>
            </select>
          </div>
          <div>
            <label htmlFor="yearSelect" className="text-dashboard-subtext mr-2 text-sm">Year:</label>
            <select 
              id="yearSelect"
              className="bg-dashboard-dark text-dashboard-text border border-gray-700 rounded px-2 py-1 text-sm"
              value={selectedYear}
              onChange={(e) => setSelectedYear(e.target.value)}
            >
              <option value="all">All Years</option>
              {availableYears.map(year => (
                <option key={year} value={year.toString()}>{year}</option>
              ))}
            </select>
          </div>
        </div>
      </div>
      
      {/* Main Dashboard Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
        {/* Train Schedule & Status Panel */}
        <div className="bg-dashboard-panel rounded shadow p-4">
          <h2 className="text-dashboard-header text-lg mb-4">Train Schedule & Status</h2>
          
          <div className="mb-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Upcoming Departures</h3>
            <div className="bg-dashboard-dark rounded p-2">
              <table className="w-full text-dashboard-text text-sm">
                <thead>
                  <tr className="border-b border-gray-700">
                    <th className="text-left py-2">Train</th>
                    <th className="text-left py-2">Route</th>
                    <th className="text-right py-2">Time</th>
                    <th className="text-right py-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {upcomingDepartures.slice(0, 5).map((train, index) => (
                    <tr key={index}>
                      <td className="py-2">{train.train_name}</td>
                      <td className="py-2">{train.from_city} → {train.to_city}</td>
                      <td className="py-2 text-right">{train.scheduled_time}</td>
                      <td className={`py-2 text-right ${train.delay_minutes > 0 ? 'text-yellow-400' : 'text-green-400'}`}>
                        {train.delay_minutes > 0 ? `Delayed ${train.delay_minutes}m` : 'On Time'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
          
          <div className="mb-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Delay by Train</h3>
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={prepareTrainMetricData().filter(item => selectedMetric === 'delay')}>
                <XAxis dataKey="train" stroke="#666" />
                <YAxis stroke="#666" />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#282c3e', borderColor: '#444', color: '#e0e0e0' }}
                  labelStyle={{ color: '#e0e0e0' }}
                />
                <Bar dataKey="value" name="Avg. Delay (minutes)">
                  {prepareTrainMetricData().map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={TRAIN_COLORS[entry.train] || '#7e57c2'} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
          
          <div className="mt-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Route Performance</h3>
            <div className="bg-dashboard-dark rounded p-2">
              <table className="w-full text-dashboard-text text-sm">
                <thead>
                  <tr className="border-b border-gray-700">
                    <th className="text-left py-2">Route</th>
                    <th className="text-right py-2">Avg. Delay</th>
                  </tr>
                </thead>
                <tbody>
                  {Object.entries(summaryStats.cityPairStats).map(([cityPair, stats]) => (
                    <tr key={cityPair}>
                      <td className="py-2">{cityPair}</td>
                      <td className="py-2 text-right">{stats.avgDelay.toFixed(1)} min</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        {/* Ticket Sales & Revenue Panel */}
        <div className="bg-dashboard-panel rounded shadow p-4">
          <h2 className="text-dashboard-header text-lg mb-4">Ticket Sales & Revenue</h2>
          
          <div className="mb-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Revenue by Train</h3>
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={prepareTrainMetricData().filter(item => selectedMetric === 'revenue')}>
                <XAxis dataKey="train" stroke="#666" />
                <YAxis stroke="#666" />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#282c3e', borderColor: '#444', color: '#e0e0e0' }}
                  labelStyle={{ color: '#e0e0e0' }}
                />
                <Bar dataKey="value" name="Revenue (LKR)">
                  {prepareTrainMetricData().map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={TRAIN_COLORS[entry.train] || '#7e57c2'} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
          
          <div className="mb-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Revenue by Class</h3>
            <ResponsiveContainer width="100%" height={180}>
              <PieChart>
                <Pie
                  data={prepareClassData()}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  outerRadius={60}
                  fill="#8884d8"
                  dataKey="value"
                  nameKey="name"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                >
                  {prepareClassData().map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip 
                  contentStyle={{ backgroundColor: '#282c3e', borderColor: '#444', color: '#e0e0e0' }}
                  labelStyle={{ color: '#e0e0e0' }}
                  formatter={(value) => `${value.toLocaleString()} LKR`}
                />
              </PieChart>
            </ResponsiveContainer>
          </div>
          
          <div className="mt-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Revenue Summary</h3>
            <div className="bg-dashboard-dark rounded p-3">
              <div className="flex justify-between mb-2">
                <span className="text-dashboard-subtext">Total Revenue</span>
                <span className="text-dashboard-text">{summaryStats.totalRevenue.toLocaleString()} LKR</span>
              </div>
              <div className="flex justify-between">
                <span className="text-dashboard-subtext">Avg. Revenue per Trip</span>
                <span className="text-dashboard-text">
                  {(summaryStats.totalRevenue / data.length).toLocaleString()} LKR
                </span>
              </div>
            </div>
          </div>
        </div>
        
        {/* Occupancy & Capacity Panel */}
        <div className="bg-dashboard-panel rounded shadow p-4">
          <h2 className="text-dashboard-header text-lg mb-4">Occupancy & Capacity</h2>
          
          <div className="mb-6">
            <h3 className="text-dashboard-subtext text-sm mb-2">Overall Occupancy Rate</h3>
            <div className="w-full bg-gray-700 rounded-full h-4 mb-4">
              <div 
                className="bg-green-400 h-4 rounded-full" 
                style={{ width: `${summaryStats.averageOccupancyRate}%` }}
              ></div>
            </div>
            
            <div className="bg-dashboard-dark rounded">
              <div className="grid grid-cols-2 border-b border-gray-700">
                <div className="p-2 text-dashboard-subtext">Average Occupancy</div>
                <div className="p-2 text-dashboard-text text-right">{summaryStats.averageOccupancyRate.toFixed(1)}%</div>
              </div>
              {Object.entries(summaryStats.trainStats).map(([train, stats]) => (
                <div key={train} className="grid grid-cols-2 border-b border-gray-700">
                  <div className="p-2 text-dashboard-subtext">Train {train}</div>
                  <div className="p-2 text-dashboard-text text-right">{stats.avgOccupancyRate.toFixed(1)}%</div>
                </div>
              ))}
            </div>
          </div>
          
          <div className="mt-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Occupancy by Train</h3>
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={prepareTrainMetricData().filter(item => selectedMetric === 'occupancy')}>
                <XAxis dataKey="train" stroke="#666" />
                <YAxis stroke="#666" />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#282c3e', borderColor: '#444', color: '#e0e0e0' }}
                  labelStyle={{ color: '#e0e0e0' }}
                />
                <Bar dataKey="value" name="Occupancy Rate (%)">
                  {prepareTrainMetricData().map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={TRAIN_COLORS[entry.train] || '#7e57c2'} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
        
        {/* Performance Metrics Panel */}
        <div className="bg-dashboard-panel rounded shadow p-4">
          <h2 className="text-dashboard-header text-lg mb-4">Performance Metrics</h2>
          
          <div className="mb-6">
            <h3 className="text-dashboard-subtext text-sm mb-2">Delay Trend</h3>
            <ResponsiveContainer width="100%" height={180}>
              <LineChart data={prepareDelayTrendData()}>
                <XAxis dataKey="month" stroke="#666" />
                <YAxis stroke="#666" />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#282c3e', borderColor: '#444', color: '#e0e0e0' }}
                  labelStyle={{ color: '#e0e0e0' }}
                />
                <Legend />
                {Object.keys(summaryStats.trainStats).map((train, index) => (
                  <Line 
                    key={train}
                    type="monotone" 
                    dataKey={train} 
                    stroke={TRAIN_COLORS[train] || COLORS[index % COLORS.length]} 
                    name={`Train ${train}`} 
                  />
                ))}
              </LineChart>
            </ResponsiveContainer>
          </div>
          
          <div className="mt-4">
            <h3 className="text-dashboard-subtext text-sm mb-2">Key Performance Indicators</h3>
            <div className="bg-dashboard-dark rounded">
              <div className="grid grid-cols-2 border-b border-gray-700">
                <div className="p-2 text-dashboard-subtext">On-Time Performance</div>
                <div className="p-2 text-dashboard-text text-right">
                  {(data.filter(train => train.delay_minutes === 0).length / data.length * 100).toFixed(1)}%
                </div>
              </div>
              <div className="grid grid-cols-2 border-b border-gray-700">
                <div className="p-2 text-dashboard-subtext">Avg. Delay</div>
                <div className="p-2 text-dashboard-text text-right">{summaryStats.averageDelay.toFixed(1)} min</div>
              </div>
              <div className="grid grid-cols-2 border-b border-gray-700">
                <div className="p-2 text-dashboard-subtext">Total Trips</div>
                <div className="p-2 text-dashboard-text text-right">{data.length}</div>
              </div>
              <div className="grid grid-cols-2">
                <div className="p-2 text-dashboard-subtext">Total Passengers</div>
                <div className="p-2 text-dashboard-text text-right">
                  {data.reduce((sum, train) => sum + train.occupancy, 0)}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <ChatInterface />
    </div>
    </ChatProvider>
    </DashboardContext.Provider>
  );
};

export default Dashboard;
