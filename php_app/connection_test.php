<?php
// Start building the page content
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test</title>
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
</head>
<body class="min-h-screen bg-dashboard-dark text-dashboard-text">
    <header class="bg-dashboard-panel border-b border-gray-700 flex justify-between items-center px-4 py-2">
        <div class="flex items-center">
            <div class="text-purple-500 font-bold text-xl mr-2">BookSL</div>
            <div class="text-dashboard-header text-lg flex items-center">
                Train Management System
                <!-- Simulating a disconnected state -->
                <div class="ml-2 flex items-center">
                    <div class="bg-red-500 h-3 w-3 rounded-full mr-1"></div>
                    <span class="text-xs text-red-400">Disconnected</span>
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
        </aside>
        
        <main class="flex-1">
            <div class="p-4">
                <div class="mb-4 px-2">
                    <h1 class="text-xl font-medium text-dashboard-header">Connection Test Page</h1>
                    <p class="text-dashboard-text mt-4">
                        This page is simulating a disconnected PostgreSQL connection to test the notification dot.
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
echo ob_get_clean();
?>
