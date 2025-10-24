<?php 
require_once __DIR__ . '/../php/config.php'; 
session_start(); 
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){ 
    header('Location: ../auth/login.php'); 
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
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            color: white;
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 800;
        }
        
        .admin-welcome p {
            margin: 0.25rem 0;
            font-size: 1.1rem;
        }
        
        .last-login {
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .quick-actions {
            margin: 2rem 0;
            padding: 1.5rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .quick-actions h2 {
            margin: 0 0 1.5rem 0;
            color: #1f2937;
            font-size: 1.5rem;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .action-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .action-secondary { background: linear-gradient(135deg, #6b7280, #9ca3af); color: white; }
        .action-success { background: linear-gradient(135deg, #10b981, #34d399); color: white; }
        .action-info { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: white; }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .stat-primary { border-left-color: #667eea; }
        .stat-warning { border-left-color: #f59e0b; }
        .stat-success { border-left-color: #10b981; }
        .stat-info { border-left-color: #3b82f6; }
        .stat-secondary { border-left-color: #6b7280; }
        .stat-accent { border-left-color: #8b5cf6; }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-primary .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-warning .stat-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .stat-success .stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }
        .stat-info .stat-icon { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .stat-secondary .stat-icon { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .stat-accent .stat-icon { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        
        .stat-content h3 {
            margin: 0 0 0.25rem 0;
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
        }
        
        .stat-content p {
            margin: 0;
            color: #6b7280;
            font-weight: 600;
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
            <div class="brand">
                <i class="fas fa-shield-alt"></i>
                Mazvikadei Resort - Admin Dashboard
            </div>
            <nav class="nav">
                <a href="enhanced_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="manage_rooms.php">
                    <i class="fas fa-bed"></i>
                    Rooms
                </a>
                <a href="manage_activities.php">
                    <i class="fas fa-hiking"></i>
                    Activities
                </a>
                <a href="manage_events.php">
                    <i class="fas fa-calendar-alt"></i>
                    Events
                </a>
                <a href="manage_bookings.php">
                    <i class="fas fa-book"></i>
                    Bookings
                </a>
                <a href="manage_customers.php">
                    <i class="fas fa-users"></i>
                    Customers
                </a>
                <a href="contact_messages.php">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-header">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Enhanced Admin Dashboard
            </h1>
            <div class="admin-welcome">
                <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong>!</p>
                <p class="last-login">Last login: <span id="lastLogin">Loading...</span></p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="action-buttons">
                <a href="manage_bookings.php" class="action-btn action-primary">
                    <i class="fas fa-book"></i>
                    <span>Manage Bookings</span>
                </a>
                <a href="manage_rooms.php" class="action-btn action-secondary">
                    <i class="fas fa-bed"></i>
                    <span>Manage Rooms</span>
                </a>
                <a href="contact_messages.php" class="action-btn action-success">
                    <i class="fas fa-envelope"></i>
                    <span>View Messages</span>
                </a>
                <a href="reports.php" class="action-btn action-info">
                    <i class="fas fa-chart-bar"></i>
                    <span>Generate Reports</span>
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="dashboard-grid" id="statsGrid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3 id="totalBookings">-</h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 id="pendingBookings">-</h3>
                    <p>Pending Bookings</p>
                </div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3 id="totalRevenue">-</h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-calendar-month"></i>
                </div>
                <div class="stat-content">
                    <h3 id="monthlyRevenue">-</h3>
                    <p>Monthly Revenue</p>
                </div>
            </div>
            <div class="stat-card stat-secondary">
                <div class="stat-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-content">
                    <h3 id="availableRooms">-</h3>
                    <p>Available Rooms</p>
                </div>
            </div>
            <div class="stat-card stat-accent">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3 id="totalCustomers">-</h3>
                    <p>Total Customers</p>
                </div>
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

        // Load last login time
        async function loadLastLogin() {
            try {
                const response = await fetch('../php/api/admin_info.php');
                const data = await response.json();
                if (data.last_login) {
                    const lastLogin = new Date(data.last_login);
                    document.getElementById('lastLogin').textContent = lastLogin.toLocaleString();
                } else {
                    document.getElementById('lastLogin').textContent = 'First login';
                }
            } catch (error) {
                console.error('Error loading last login:', error);
                document.getElementById('lastLogin').textContent = 'Unknown';
            }
        }

        // Add real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();
            
            // Update any clock elements if they exist
            const clockElements = document.querySelectorAll('.current-time');
            clockElements.forEach(element => {
                element.textContent = `${dateString} ${timeString}`;
            });
        }

        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadLastLogin();
            updateClock();
            setInterval(updateClock, 1000); // Update every second
        });
    </script>
</body>
</html>
