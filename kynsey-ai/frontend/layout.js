/**
 * Layout template for the BookSL Train Dashboard
 * Renders the common layout for all pages
 */

function renderLayout(title, currentPage, content) {
  return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title}</title>
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dashboard-dark': '#1e2130',
                        'dashboard-panel': '#282c3e',
                        'dashboard-purple': '#7e57c2',
                        'dashboard-blue': '#4e7fff',
                        'dashboard-light-purple': '#b39ddb',
                        'dashboard-text': '#e0e0e0',
                        'dashboard-header': '#ffffff',
                        'dashboard-subtext': '#9e9e9e',
                    },
                },
            },
        }
    </script>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-dashboard-dark text-dashboard-text">
    <header class="bg-dashboard-panel border-b border-gray-700 flex justify-between items-center px-4 py-2">
        <div class="flex items-center">
            <div class="text-purple-500 font-bold text-xl mr-2">BookSL</div>
            <div class="text-dashboard-header text-lg flex items-center">
                Train Management System
                <div class="ml-2 flex items-center">
                    <div class="bg-green-500 h-3 w-3 rounded-full mr-1" id="db-status-indicator"></div>
                    <span class="text-xs text-green-400" id="db-status-text">Connected</span>
                </div>
            </div>
        </div>
        <div class="flex items-center">
            <span class="text-dashboard-text mr-2">administrator</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
        </div>
    </header>
    
    <div class="flex">
        <aside class="w-48 bg-dashboard-panel min-h-screen border-r border-gray-700">
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
                    </svg>
                    <span class="font-medium">Train Operations</span>
                </div>
            </div>
            <nav class="p-2">
                <ul>
                    <li class="mb-1">
                        <a 
                            href="/dashboard"
                            class="block w-full text-left p-2 rounded ${currentPage === 'dashboard' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}"
                        >
                            Dashboard
                        </a>
                    </li>
                    <li class="mb-1">
                        <a 
                            href="/train-schedules"
                            class="block w-full text-left p-2 rounded ${currentPage === 'schedules' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}"
                        >
                            <span class="ml-6">Train Schedules</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a 
                            href="/ticket-sales"
                            class="block w-full text-left p-2 rounded ${currentPage === 'ticketSales' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}"
                        >
                            <span class="ml-6">Ticket Sales</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a 
                            href="/remote-management"
                            class="block w-full text-left p-2 rounded ${currentPage === 'remoteManagement' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}"
                        >
                            <span class="ml-6">Remote Management</span>
                        </a>
                    </li>
                    <li class="mb-1">
                        <a 
                            href="/train-fleet"
                            class="block w-full text-left p-2 rounded ${currentPage === 'trainFleet' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'}"
                        >
                            <span class="ml-6">Train Fleet</span>
                        </a>
                    </li>
                </ul>
                <div class="mt-4 border-t border-gray-700 pt-4">
                    <a 
                        href="/reports"
                        class="block w-full text-left p-2 rounded ${currentPage === 'reports' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3 1h10v8H5V6zm6 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span>Reports</span>
                    </a>
                    <a 
                        href="/modeling"
                        class="block w-full text-left p-2 rounded ${currentPage === 'erpModeling' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 1.887.87 3.568 2.23 4.679l.27.27.91.91.27.27.91.91c1.11 1.36 2.79 2.23 4.68 2.23A6 6 0 0010 2zm0 9a3 3 0 110-6 3 3 0 010 6z" clip-rule="evenodd" />
                        </svg>
                        <span>Modeling</span>
                    </a>
                    <a 
                        href="/passengers"
                        class="block w-full text-left p-2 rounded ${currentPage === 'passengers' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                        <span>Passengers</span>
                    </a>
                    <a 
                        href="/system-logs"
                        class="block w-full text-left p-2 rounded ${currentPage === 'systemLogs' ? 'bg-gray-700 text-dashboard-header' : 'hover:bg-gray-700 text-dashboard-subtext'} flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v7h-2l-1 2H8l-1-2H5V5z" clip-rule="evenodd" />
                        </svg>
                        <span>System Logs</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <main class="flex-1">
            ${content}
        </main>
    </div>

    <!-- JavaScript for charts and interactivity -->
    <script src="/js/dashboard.js"></script>
    <script>
      // Check database connection status
      fetch('/api/db-status')
        .then(response => response.json())
        .then(data => {
          const statusIndicator = document.getElementById('db-status-indicator');
          const statusText = document.getElementById('db-status-text');
          
          if (data.connected) {
            statusIndicator.className = 'bg-green-500 h-3 w-3 rounded-full mr-1';
            statusText.className = 'text-xs text-green-400';
            statusText.textContent = 'Connected';
          } else {
            statusIndicator.className = 'bg-red-500 h-3 w-3 rounded-full mr-1';
            statusText.className = 'text-xs text-red-400';
            statusText.textContent = 'Disconnected';
          }
        })
        .catch(error => {
          console.error('Error checking database status:', error);
          const statusIndicator = document.getElementById('db-status-indicator');
          const statusText = document.getElementById('db-status-text');
          
          statusIndicator.className = 'bg-red-500 h-3 w-3 rounded-full mr-1';
          statusText.className = 'text-xs text-red-400';
          statusText.textContent = 'Disconnected';
        });
    </script>
</body>
</html>
  `;
}

export default renderLayout;
