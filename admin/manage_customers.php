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
    <title>Manage Customers - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css"/>
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
        .btn-success {
            background: #059669;
            color: white;
        }
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
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
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #f3f4f6; color: #6b7280; }
        .status-suspended { background: #fee2e2; color: #991b1b; }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .customer-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .detail-row {
            display: flex;
            margin-bottom: 0.5rem;
        }
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #374151;
        }
        .detail-value {
            color: #6b7280;
        }
        .customer-bookings {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .booking-item:last-child {
            border-bottom: none;
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
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">Mazvikadei Resort - Customer Management</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php">Dashboard</a>
                <a href="manage_rooms.php">Rooms</a>
                <a href="manage_activities.php">Activities</a>
                <a href="manage_events.php">Events</a>
                <a href="manage_bookings.php">Bookings</a>
                <a href="manage_customers.php" class="active">Customers</a>
                <a href="contact_messages.php">Messages</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-container">
            <div class="page-header">
                <h1>Customer Management</h1>
                <div>
                    <button class="btn btn-primary" onclick="exportCustomers()">Export Customers</button>
                    <button class="btn btn-secondary" onclick="refreshCustomers()">Refresh</button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid" id="customerStats">
                <div class="stat-card">
                    <div class="stat-number" id="totalCustomers">-</div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="activeCustomers">-</div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="newCustomers">-</div>
                    <div class="stat-label">New This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="topSpenders">-</div>
                    <div class="stat-label">Top Spenders</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3>Filter Customers</h3>
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="roleFilter">Role</label>
                        <select id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="customer">Customer</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dateFrom">From Date</label>
                        <input type="date" id="dateFrom">
                    </div>
                    <div class="form-group">
                        <label for="dateTo">To Date</label>
                        <input type="date" id="dateTo">
                    </div>
                    <div class="form-group">
                        <label for="searchFilter">Search</label>
                        <input type="text" id="searchFilter" placeholder="Search by name or email...">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="filterCustomers()">Apply Filters</button>
            </div>

            <!-- Customers Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Total Spent</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading customers...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Customer Details Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Customer Details</h2>
                <span class="close" onclick="closeCustomerModal()">&times;</span>
            </div>
            <div id="customerDetails">
                <!-- Customer details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let customers = [];
        let customerBookings = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadCustomers();
            setupFilters();
        });

        async function loadCustomers() {
            try {
                const response = await fetch('../php/api/customers.php');
                const data = await response.json();
                customers = data.customers || [];
                displayCustomers();
                updateStats();
            } catch (error) {
                console.error('Error loading customers:', error);
                document.getElementById('customersTableBody').innerHTML = 
                    '<tr><td colspan="9" style="text-align: center; padding: 2rem; color: #dc2626;">Error loading customers</td></tr>';
            }
        }

        function displayCustomers(filteredCustomers = null) {
            const tbody = document.getElementById('customersTableBody');
            const customersToShow = filteredCustomers || customers;
            
            if (customersToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 2rem; color: #6b7280;">No customers found</td></tr>';
                return;
            }
            
            const rowsHTML = customersToShow.map(customer => {
                const joinedDate = new Date(customer.created_at).toLocaleDateString();
                const totalSpent = customer.total_spent || 0;
                const bookingCount = customer.booking_count || 0;
                
                return `
                    <tr>
                        <td>${customer.name}</td>
                        <td>${customer.email}</td>
                        <td>${customer.phone || 'Not provided'}</td>
                        <td>${customer.role}</td>
                        <td><span class="status-badge status-${customer.status}">${customer.status}</span></td>
                        <td>${bookingCount}</td>
                        <td>$${parseFloat(totalSpent).toFixed(2)}</td>
                        <td>${joinedDate}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-small" onclick="viewCustomer(${customer.id})">View</button>
                                <button class="btn btn-success btn-small" onclick="editCustomer(${customer.id})">Edit</button>
                                <button class="btn btn-small" onclick="toggleCustomerStatus(${customer.id}, '${customer.status}')">
                                    ${customer.status === 'active' ? 'Suspend' : 'Activate'}
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            tbody.innerHTML = rowsHTML;
        }

        function updateStats() {
            const total = customers.length;
            const active = customers.filter(c => c.status === 'active').length;
            const thisMonth = new Date();
            thisMonth.setMonth(thisMonth.getMonth() - 1);
            const newThisMonth = customers.filter(c => new Date(c.created_at) >= thisMonth).length;
            const topSpenders = customers
                .filter(c => c.total_spent > 0)
                .sort((a, b) => parseFloat(b.total_spent) - parseFloat(a.total_spent))
                .slice(0, 5).length;
            
            document.getElementById('totalCustomers').textContent = total;
            document.getElementById('activeCustomers').textContent = active;
            document.getElementById('newCustomers').textContent = newThisMonth;
            document.getElementById('topSpenders').textContent = topSpenders;
        }

        function setupFilters() {
            document.getElementById('statusFilter').addEventListener('change', filterCustomers);
            document.getElementById('roleFilter').addEventListener('change', filterCustomers);
            document.getElementById('dateFrom').addEventListener('change', filterCustomers);
            document.getElementById('dateTo').addEventListener('change', filterCustomers);
            document.getElementById('searchFilter').addEventListener('input', filterCustomers);
        }

        function filterCustomers() {
            const status = document.getElementById('statusFilter').value;
            const role = document.getElementById('roleFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const search = document.getElementById('searchFilter').value.toLowerCase();
            
            let filteredCustomers = customers;
            
            if (status) {
                filteredCustomers = filteredCustomers.filter(c => c.status === status);
            }
            
            if (role) {
                filteredCustomers = filteredCustomers.filter(c => c.role === role);
            }
            
            if (dateFrom) {
                filteredCustomers = filteredCustomers.filter(c => c.created_at >= dateFrom);
            }
            
            if (dateTo) {
                filteredCustomers = filteredCustomers.filter(c => c.created_at <= dateTo);
            }
            
            if (search) {
                filteredCustomers = filteredCustomers.filter(c => 
                    c.name.toLowerCase().includes(search) ||
                    c.email.toLowerCase().includes(search)
                );
            }
            
            displayCustomers(filteredCustomers);
        }

        async function viewCustomer(customerId) {
            try {
                const response = await fetch(`../php/api/customers.php?id=${customerId}`);
                const data = await response.json();
                const customer = data.customer;
                const bookings = data.bookings || [];
                
                const modal = document.getElementById('customerModal');
                const content = document.getElementById('customerDetails');
                
                content.innerHTML = `
                    <div class="customer-details">
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">${customer.name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${customer.email}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">${customer.phone || 'Not provided'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Role:</span>
                            <span class="detail-value">${customer.role}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><span class="status-badge status-${customer.status}">${customer.status}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Spent:</span>
                            <span class="detail-value">$${parseFloat(customer.total_spent || 0).toFixed(2)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Bookings:</span>
                            <span class="detail-value">${customer.booking_count || 0}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Joined:</span>
                            <span class="detail-value">${new Date(customer.created_at).toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <div class="customer-bookings">
                        <h4>Recent Bookings</h4>
                        ${bookings.length > 0 ? bookings.map(booking => `
                            <div class="booking-item">
                                <div>
                                    <div style="font-weight: 600;">${booking.booking_reference}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">${booking.type} - ${new Date(booking.created_at).toLocaleDateString()}</div>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #059669;">$${parseFloat(booking.total_amount).toFixed(2)}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;"><span class="status-badge status-${booking.status}">${booking.status}</span></div>
                                </div>
                            </div>
                        `).join('') : '<p style="color: #6b7280;">No bookings found</p>'}
                    </div>
                `;
                
                modal.style.display = 'block';
            } catch (error) {
                console.error('Error loading customer details:', error);
                alert('Error loading customer details');
            }
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').style.display = 'none';
        }

        function editCustomer(customerId) {
            // Implement edit customer functionality
            alert('Edit customer functionality would be implemented here');
        }

        async function toggleCustomerStatus(customerId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'suspend';
            
            if (!confirm(`Are you sure you want to ${action} this customer?`)) {
                return;
            }
            
            try {
                const response = await fetch('../php/api/customers.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_id: customerId,
                        status: newStatus
                    })
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    alert(`Customer ${action}d successfully`);
                    loadCustomers();
                } else {
                    alert(`Error ${action}ing customer`);
                }
            } catch (error) {
                console.error(`Error ${action}ing customer:`, error);
                alert(`Error ${action}ing customer`);
            }
        }

        function exportCustomers() {
            // Implement export functionality
            alert('Export functionality would be implemented here');
        }

        function refreshCustomers() {
            loadCustomers();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const customerModal = document.getElementById('customerModal');
            if (event.target == customerModal) {
                closeCustomerModal();
            }
        }
    </script>
</body>
</html>
