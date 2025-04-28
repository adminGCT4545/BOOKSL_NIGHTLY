/**
 * Dashboard JavaScript for BookSL Train Dashboard
 * Handles chart rendering and interactive elements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if they exist on the page
    initializeCharts();
    
    // Set up event listeners for filters
    setupFilters();
});

/**
 * Initialize all charts on the dashboard
 */
function initializeCharts() {
    // Bar charts
    const barChartElements = document.querySelectorAll('.bar-chart');
    barChartElements.forEach(element => {
        const ctx = element.getContext('2d');
        const data = JSON.parse(element.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label,
                    data: data.values,
                    backgroundColor: data.colors || getDefaultColors(data.values.length),
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9e9e9e'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9e9e9e'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#e0e0e0'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#282c3e',
                        titleColor: '#ffffff',
                        bodyColor: '#e0e0e0',
                        borderColor: '#444',
                        borderWidth: 1
                    }
                }
            }
        });
    });
    
    // Line charts
    const lineChartElements = document.querySelectorAll('.line-chart');
    lineChartElements.forEach(element => {
        const ctx = element.getContext('2d');
        const data = JSON.parse(element.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: data.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.values,
                    borderColor: dataset.color || getDefaultColors(1, index)[0],
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    pointBackgroundColor: dataset.color || getDefaultColors(1, index)[0]
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9e9e9e'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#9e9e9e'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#e0e0e0'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#282c3e',
                        titleColor: '#ffffff',
                        bodyColor: '#e0e0e0',
                        borderColor: '#444',
                        borderWidth: 1
                    }
                }
            }
        });
    });
    
    // Pie charts
    const pieChartElements = document.querySelectorAll('.pie-chart');
    pieChartElements.forEach(element => {
        const ctx = element.getContext('2d');
        const data = JSON.parse(element.getAttribute('data-chart'));
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: data.colors || getDefaultColors(data.values.length),
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#e0e0e0',
                            padding: 10,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#282c3e',
                        titleColor: '#ffffff',
                        bodyColor: '#e0e0e0',
                        borderColor: '#444',
                        borderWidth: 1
                    }
                }
            }
        });
    });
}

/**
 * Set up event listeners for dashboard filters
 */
function setupFilters() {
    // Year filter
    const yearSelect = document.getElementById('yearSelect');
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    // Metric filter
    const metricSelect = document.getElementById('metricSelect');
    if (metricSelect) {
        metricSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
}

/**
 * Get default colors for charts
 * @param {number} count Number of colors needed
 * @param {number} offset Color offset
 * @returns {Array} Array of colors
 */
function getDefaultColors(count, offset = 0) {
    const colors = [
        '#7e57c2', '#4e7fff', '#b39ddb', '#64b5f6', 
        '#9575cd', '#5c6bc0', '#7986cb', '#4fc3f7'
    ];
    
    const result = [];
    for (let i = 0; i < count; i++) {
        result.push(colors[(i + offset) % colors.length]);
    }
    
    return result;
}

/**
 * Format number with commas for thousands
 * @param {number} number Number to format
 * @returns {string} Formatted number
 */
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
