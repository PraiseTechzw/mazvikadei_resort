<?php 
require_once __DIR__ . '/../php/config.php'; 
session_start(); 
if(!isset($_SESSION['user_id'])){ 
    header('Location: login.php'); 
    exit; 
} 
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Reports & Analytics - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .report-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .report-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-group input,
        .form-group select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th,
        .table-container td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-container th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .export-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">Mazvikadei Resort - Reports & Analytics</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php">Dashboard</a>
                <a href="manage_rooms.php">Rooms</a>
                <a href="manage_activities.php">Activities</a>
                <a href="manage_events.php">Events</a>
                <a href="manage_bookings.php">Bookings</a>
                <a href="manage_customers.php">Customers</a>
                <a href="contact_messages.php">Messages</a>
                <a href="reports.php" class="active">Reports</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-container">
            <div class="page-header">
                <h1>Reports & Analytics</h1>
                <div>
                    <button class="btn btn-primary" onclick="generateReport()">Generate Report</button>
                    <button class="btn btn-secondary" onclick="refreshData()">Refresh Data</button>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="export-buttons">
                <button class="btn btn-primary" onclick="exportToPDF()">Export to PDF</button>
                <button class="btn btn-primary" onclick="exportToExcel()">Export to Excel</button>
                <button class="btn btn-primary" onclick="exportToCSV()">Export to CSV</button>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3>Report Filters</h3>
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="dateFrom">From Date</label>
                        <input type="date" id="dateFrom" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="dateTo">To Date</label>
                        <input type="date" id="dateTo" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType">
                            <option value="overview">Overview</option>
                            <option value="revenue">Revenue</option>
                            <option value="bookings">Bookings</option>
                            <option value="customers">Customers</option>
                            <option value="rooms">Rooms</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="groupBy">Group By</label>
                        <select id="groupBy">
                            <option value="day">Day</option>
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid" id="keyMetrics">
                <div class="stat-card">
                    <div class="stat-number" id="totalRevenue">-</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalBookings">-</div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="averageBookingValue">-</div>
                    <div class="stat-label">Average Booking Value</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="occupancyRate">-</div>
                    <div class="stat-label">Occupancy Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="customerSatisfaction">-</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="repeatCustomers">-</div>
                    <div class="stat-label">Repeat Customers</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="reports-grid">
                <div class="report-card">
                    <h3>Revenue Trends</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Booking Trends</h3>
                    <div class="chart-container">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Room Occupancy</h3>
                    <div class="chart-container">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Customer Demographics</h3>
                    <div class="chart-container">
                        <canvas id="demographicsChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Activity Popularity</h3>
                    <div class="chart-container">
                        <canvas id="activitiesChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Payment Methods</h3>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: #f9fafb;">Top Performing Rooms</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Occupancy Rate</th>
                            <th>Average Rating</th>
                        </tr>
                    </thead>
                    <tbody id="topRoomsTable">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Recent Activity -->
            <div class="table-container">
                <h3 style="padding: 1rem; margin: 0; background: #f9fafb;">Recent Activity</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="recentActivityTable">
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let reportData = {};
        let charts = {};

        document.addEventListener('DOMContentLoaded', function() {
            loadReportData();
            setupFilters();
        });

        async function loadReportData() {
            try {
                const dateFrom = document.getElementById('dateFrom').value;
                const dateTo = document.getElementById('dateTo').value;
                const reportType = document.getElementById('reportType').value;
                const groupBy = document.getElementById('groupBy').value;
                
                const response = await fetch(`../php/api/reports.php?date_from=${dateFrom}&date_to=${dateTo}&type=${reportType}&group_by=${groupBy}`);
                const data = await response.json();
                
                reportData = data;
                updateMetrics();
                createCharts();
                updateTables();
            } catch (error) {
                console.error('Error loading report data:', error);
            }
        }

        function updateMetrics() {
            const metrics = reportData.metrics || {};
            
            document.getElementById('totalRevenue').textContent = '$' + (metrics.total_revenue || 0).toFixed(2);
            document.getElementById('totalBookings').textContent = metrics.total_bookings || 0;
            document.getElementById('averageBookingValue').textContent = '$' + (metrics.average_booking_value || 0).toFixed(2);
            document.getElementById('occupancyRate').textContent = (metrics.occupancy_rate || 0).toFixed(1) + '%';
            document.getElementById('customerSatisfaction').textContent = (metrics.customer_satisfaction || 0).toFixed(1) + '/5';
            document.getElementById('repeatCustomers').textContent = metrics.repeat_customers || 0;
        }

        function createCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            if (charts.revenue) charts.revenue.destroy();
            charts.revenue = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: reportData.revenue_trends?.map(t => t.date) || [],
                    datasets: [{
                        label: 'Revenue',
                        data: reportData.revenue_trends?.map(t => t.revenue) || [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Booking Chart
            const bookingCtx = document.getElementById('bookingChart').getContext('2d');
            if (charts.booking) charts.booking.destroy();
            charts.booking = new Chart(bookingCtx, {
                type: 'bar',
                data: {
                    labels: reportData.booking_trends?.map(t => t.date) || [],
                    datasets: [{
                        label: 'Bookings',
                        data: reportData.booking_trends?.map(t => t.count) || [],
                        backgroundColor: '#059669'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Occupancy Chart
            const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
            if (charts.occupancy) charts.occupancy.destroy();
            charts.occupancy = new Chart(occupancyCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Occupied', 'Available'],
                    datasets: [{
                        data: [
                            reportData.occupancy?.occupied || 0,
                            reportData.occupancy?.available || 0
                        ],
                        backgroundColor: ['#dc2626', '#059669']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Demographics Chart
            const demographicsCtx = document.getElementById('demographicsChart').getContext('2d');
            if (charts.demographics) charts.demographics.destroy();
            charts.demographics = new Chart(demographicsCtx, {
                type: 'pie',
                data: {
                    labels: reportData.demographics?.map(d => d.category) || [],
                    datasets: [{
                        data: reportData.demographics?.map(d => d.count) || [],
                        backgroundColor: ['#3b82f6', '#059669', '#f59e0b', '#dc2626']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Activities Chart
            const activitiesCtx = document.getElementById('activitiesChart').getContext('2d');
            if (charts.activities) charts.activities.destroy();
            charts.activities = new Chart(activitiesCtx, {
                type: 'bar',
                data: {
                    labels: reportData.popular_activities?.map(a => a.name) || [],
                    datasets: [{
                        label: 'Bookings',
                        data: reportData.popular_activities?.map(a => a.bookings) || [],
                        backgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Payment Chart
            const paymentCtx = document.getElementById('paymentChart').getContext('2d');
            if (charts.payment) charts.payment.destroy();
            charts.payment = new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: reportData.payment_methods?.map(p => p.method) || [],
                    datasets: [{
                        data: reportData.payment_methods?.map(p => p.count) || [],
                        backgroundColor: ['#3b82f6', '#059669', '#f59e0b', '#dc2626']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function updateTables() {
            // Top Rooms Table
            const topRoomsTable = document.getElementById('topRoomsTable');
            const topRooms = reportData.top_rooms || [];
            
            if (topRooms.length === 0) {
                topRoomsTable.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">No data available</td></tr>';
            } else {
                topRoomsTable.innerHTML = topRooms.map(room => `
                    <tr>
                        <td>${room.title}</td>
                        <td>${room.bookings}</td>
                        <td>$${parseFloat(room.revenue).toFixed(2)}</td>
                        <td>${parseFloat(room.occupancy_rate).toFixed(1)}%</td>
                        <td>${parseFloat(room.average_rating).toFixed(1)}/5</td>
                    </tr>
                `).join('');
            }

            // Recent Activity Table
            const recentActivityTable = document.getElementById('recentActivityTable');
            const recentActivity = reportData.recent_activity || [];
            
            if (recentActivity.length === 0) {
                recentActivityTable.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">No recent activity</td></tr>';
            } else {
                recentActivityTable.innerHTML = recentActivity.map(activity => `
                    <tr>
                        <td>${new Date(activity.date).toLocaleDateString()}</td>
                        <td>${activity.activity}</td>
                        <td>${activity.user}</td>
                        <td>${activity.details}</td>
                    </tr>
                `).join('');
            }
        }

        function setupFilters() {
            document.getElementById('dateFrom').addEventListener('change', loadReportData);
            document.getElementById('dateTo').addEventListener('change', loadReportData);
            document.getElementById('reportType').addEventListener('change', loadReportData);
            document.getElementById('groupBy').addEventListener('change', loadReportData);
        }

        function applyFilters() {
            loadReportData();
        }

        function generateReport() {
            // Implement report generation
            alert('Report generation functionality would be implemented here');
        }

        function refreshData() {
            loadReportData();
        }

        function exportToPDF() {
            // Implement PDF export
            alert('PDF export functionality would be implemented here');
        }

        function exportToExcel() {
            // Implement Excel export
            alert('Excel export functionality would be implemented here');
        }

        function exportToCSV() {
            // Implement CSV export
            alert('CSV export functionality would be implemented here');
        }
    </script>
</body>
</html>
