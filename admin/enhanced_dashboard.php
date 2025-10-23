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
    <title>Enhanced Admin Dashboard - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css"/>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .chart-container {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 1rem 0;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th,
        .table-container td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-container th {
            background: #f9fafb;
            font-weight: 600;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            margin: 0 0.25rem;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">Mazvikadei Resort - Admin Dashboard</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php" class="active">Dashboard</a>
                <a href="manage_rooms.php">Rooms</a>
                <a href="manage_activities.php">Activities</a>
                <a href="manage_events.php">Events</a>
                <a href="manage_bookings.php">Bookings</a>
                <a href="manage_customers.php">Customers</a>
                <a href="contact_messages.php">Messages</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Enhanced Admin Dashboard</h1>
        
        <!-- Statistics Cards -->
        <div class="dashboard-grid" id="statsGrid">
            <div class="stat-card">
                <h3 id="totalBookings">-</h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3 id="pendingBookings">-</h3>
                <p>Pending Bookings</p>
            </div>
            <div class="stat-card">
                <h3 id="totalRevenue">-</h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <h3 id="monthlyRevenue">-</h3>
                <p>Monthly Revenue</p>
            </div>
            <div class="stat-card">
                <h3 id="availableRooms">-</h3>
                <p>Available Rooms</p>
            </div>
            <div class="stat-card">
                <h3 id="totalCustomers">-</h3>
                <p>Total Customers</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;">
            <div class="chart-container">
                <h3>Booking Trends (Last 7 Days)</h3>
                <canvas id="bookingChart" width="400" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h3>Revenue Trends (Last 7 Days)</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="table-container">
            <h3 style="padding: 1rem; margin: 0; background: #f9fafb;">Recent Bookings</h3>
            <div id="recentBookingsTable">
                <div style="padding: 2rem; text-align: center; color: #6b7280;">Loading...</div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let dashboardData = null;

        async function loadDashboardData() {
            try {
                const response = await fetch('../php/api/dashboard.php');
                dashboardData = await response.json();
                
                // Update statistics
                document.getElementById('totalBookings').textContent = dashboardData.stats.total_bookings;
                document.getElementById('pendingBookings').textContent = dashboardData.stats.pending_bookings;
                document.getElementById('totalRevenue').textContent = '$' + parseFloat(dashboardData.stats.total_revenue).toFixed(2);
                document.getElementById('monthlyRevenue').textContent = '$' + parseFloat(dashboardData.stats.monthly_revenue).toFixed(2);
                document.getElementById('availableRooms').textContent = dashboardData.stats.available_rooms;
                document.getElementById('totalCustomers').textContent = dashboardData.stats.total_customers;
                
                // Update recent bookings table
                updateRecentBookingsTable();
                
                // Create charts
                createBookingChart();
                createRevenueChart();
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        function updateRecentBookingsTable() {
            const tableContainer = document.getElementById('recentBookingsTable');
            const bookings = dashboardData.recent_bookings;
            
            if (bookings.length === 0) {
                tableContainer.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;">No recent bookings</div>';
                return;
            }
            
            let tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            bookings.forEach(booking => {
                const statusClass = `status-${booking.status}`;
                const createdDate = new Date(booking.created_at).toLocaleDateString();
                
                tableHTML += `
                    <tr>
                        <td>${booking.booking_reference}</td>
                        <td>${booking.customer_name}</td>
                        <td>${booking.type}</td>
                        <td>$${parseFloat(booking.total_amount).toFixed(2)}</td>
                        <td><span class="status-badge ${statusClass}">${booking.status}</span></td>
                        <td>${createdDate}</td>
                        <td>
                            <button class="btn btn-small" onclick="viewBooking(${booking.id})">View</button>
                            <button class="btn btn-small" onclick="updateBookingStatus(${booking.id})">Update</button>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table>';
            tableContainer.innerHTML = tableHTML;
        }

        function createBookingChart() {
            const ctx = document.getElementById('bookingChart').getContext('2d');
            const trends = dashboardData.booking_trends;
            
            const labels = trends.map(t => new Date(t.date).toLocaleDateString());
            const data = trends.map(t => t.count);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function createRevenueChart() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const trends = dashboardData.revenue_trends;
            
            const labels = trends.map(t => new Date(t.date).toLocaleDateString());
            const data = trends.map(t => parseFloat(t.revenue));
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: data,
                        backgroundColor: '#10b981',
                        borderColor: '#059669'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function viewBooking(bookingId) {
            // Implement view booking functionality
            alert('View booking: ' + bookingId);
        }

        function updateBookingStatus(bookingId) {
            // Implement update booking status functionality
            alert('Update booking status: ' + bookingId);
        }

        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', loadDashboardData);
    </script>
</body>
</html>
