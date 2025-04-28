import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';

interface LayoutProps {
  children: React.ReactNode;
  title: string;
}

const Layout: React.FC<LayoutProps> = ({ children, title }) => {
  const location = useLocation();
  const [isConnected, setIsConnected] = useState<boolean>(true);
  
  // Check database connection status
  useEffect(() => {
    const checkConnection = async () => {
      try {
        const response = await fetch('/api/connection-status');
        const data = await response.json();
        setIsConnected(data.connected);
      } catch (error) {
        console.error('Error checking connection:', error);
        setIsConnected(false);
      }
    };
    
    checkConnection();
    // Check connection every 30 seconds
    const interval = setInterval(checkConnection, 30000);
    
    return () => clearInterval(interval);
  }, []);
  
  // Helper function to determine if a link is active
  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <div className="min-h-screen bg-dashboard-dark text-dashboard-text">
      <header className="bg-dashboard-panel border-b border-gray-700 flex justify-between items-center px-4 py-2">
        <div className="flex items-center">
          <div className="text-purple-500 font-bold text-xl mr-2">BookSL</div>
          <div className="text-dashboard-header text-lg flex items-center">
            Train Management System
            <div className="ml-2 flex items-center">
              <div className={`${isConnected ? 'bg-green-500' : 'bg-red-500'} h-3 w-3 rounded-full mr-1`}></div>
              <span className={`text-xs ${isConnected ? 'text-green-400' : 'text-red-400'}`}>
                {isConnected ? 'Connected' : 'Disconnected'}
              </span>
            </div>
          </div>
        </div>
        <div className="flex items-center">
          <span className="text-dashboard-text mr-2">administrator</span>
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
          </svg>
        </div>
      </header>
      
      <div className="flex">
        <aside className="w-48 bg-dashboard-panel min-h-screen border-r border-gray-700">
          <div className="p-4 border-b border-gray-700">
            <div className="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
              </svg>
              <span className="font-medium">Train Operations</span>
            </div>
          </div>
          <nav className="p-2">
            <ul>
              <li className="mb-1">
                <Link 
                  to="/"
                  className={`block w-full text-left p-2 rounded ${isActive('/') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}`}
                >
                  Dashboard
                </Link>
              </li>
              <li className="mb-1">
                <Link 
                  to="/train-schedules"
                  className={`block w-full text-left p-2 rounded ${isActive('/train-schedules') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}`}
                >
                  <span className="ml-6">Train Schedules</span>
                </Link>
              </li>
              <li className="mb-1">
                <Link 
                  to="/ticket-sales"
                  className={`block w-full text-left p-2 rounded ${isActive('/ticket-sales') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}`}
                >
                  <span className="ml-6">Ticket Sales</span>
                </Link>
              </li>
              <li className="mb-1">
                <Link 
                  to="/remote-management"
                  className={`block w-full text-left p-2 rounded ${isActive('/remote-management') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}`}
                >
                  <span className="ml-6">Remote Management</span>
                </Link>
              </li>
              <li className="mb-1">
                <Link 
                  to="/train-fleet"
                  className={`block w-full text-left p-2 rounded ${isActive('/train-fleet') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}`}
                >
                  <span className="ml-6">Train Fleet</span>
                </Link>
              </li>
            </ul>
            <div className="mt-4 border-t border-gray-700 pt-4">
              <Link 
                to="/reports"
                className={`block w-full text-left p-2 rounded ${isActive('/reports') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3 1h10v8H5V6zm6 6a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" />
                </svg>
                <span>Reports</span>
              </Link>
              <Link 
                to="/erp-modeling"
                className={`block w-full text-left p-2 rounded ${isActive('/erp-modeling') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 2a6 6 0 00-6 6c0 1.887.87 3.568 2.23 4.679l.27.27.91.91.27.27.91.91c1.11 1.36 2.79 2.23 4.68 2.23A6 6 0 0010 2zm0 9a3 3 0 110-6 3 3 0 010 6z" clipRule="evenodd" />
                </svg>
                <span>Modeling</span>
              </Link>
              <Link 
                to="/passengers"
                className={`block w-full text-left p-2 rounded ${isActive('/passengers') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                </svg>
                <span>Passengers</span>
              </Link>
              <Link 
                to="/system-logs"
                className={`block w-full text-left p-2 rounded ${isActive('/system-logs') ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v7h-2l-1 2H8l-1-2H5V5z" clipRule="evenodd" />
                </svg>
                <span>System Logs</span>
              </Link>
            </div>
          </nav>
        </aside>
        
        <main className="flex-1">
          {children}
        </main>
      </div>
    </div>
  );
};

export default Layout;
