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
    <title>Manage Bookings - Mazvikadei Resort</title>
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
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-checked_in { background: #dbeafe; color: #1e40af; }
        .status-checked_out { background: #f3f4f6; color: #374151; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-no_show { background: #f3f4f6; color: #6b7280; }
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
        .booking-details {
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
        .booking-items {
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
            <div class="brand">Mazvikadei Resort - Booking Management</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php">Dashboard</a>
                <a href="manage_rooms.php">Rooms</a>
                <a href="manage_activities.php">Activities</a>
                <a href="manage_events.php">Events</a>
                <a href="manage_bookings.php" class="active">Bookings</a>
                <a href="manage_customers.php">Customers</a>
                <a href="contact_messages.php">Messages</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-container">
            <div class="page-header">
                <h1>Booking Management</h1>
                <div>
                    <button class="btn btn-primary" onclick="exportBookings()">Export Bookings</button>
                    <button class="btn btn-secondary" onclick="refreshBookings()">Refresh</button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid" id="bookingStats">
                <div class="stat-card">
                    <div class="stat-number" id="totalBookings">-</div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pendingBookings">-</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="confirmedBookings">-</div>
                    <div class="stat-label">Confirmed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalRevenue">-</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3>Filter Bookings</h3>
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="typeFilter">Type</label>
                        <select id="typeFilter">
                            <option value="">All Types</option>
                            <option value="room">Room</option>
                            <option value="activity">Activity</option>
                            <option value="event">Event</option>
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
                        <input type="text" id="searchFilter" placeholder="Search by name, email, or reference...">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="filterBookings()">Apply Filters</button>
            </div>

            <!-- Bookings Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading bookings...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Booking Details Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <span class="close" onclick="closeBookingModal()">&times;</span>
            </div>
            <div id="bookingDetails">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Booking Status</h2>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <form id="statusForm">
                <input type="hidden" id="bookingId" name="booking_id">
                <div class="form-group">
                    <label for="newStatus">New Status</label>
                    <select id="newStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="checked_in">Checked In</option>
                        <option value="checked_out">Checked Out</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statusNotes">Notes</label>
                    <textarea id="statusNotes" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let bookings = [];
        let currentFilters = {};

        document.addEventListener('DOMContentLoaded', function() {
            loadBookings();
            setupFilters();
        });

        async function loadBookings() {
            try {
                const response = await fetch('../php/api/bookings.php');
                const data = await response.json();
                bookings = data.bookings || [];
                displayBookings();
                updateStats();
            } catch (error) {
                console.error('Error loading bookings:', error);
                document.getElementById('bookingsTableBody').innerHTML = 
                    '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #dc2626;">Error loading bookings</td></tr>';
            }
        }

        function displayBookings(filteredBookings = null) {
            const tbody = document.getElementById('bookingsTableBody');
            const bookingsToShow = filteredBookings || bookings;
            
            if (bookingsToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">No bookings found</td></tr>';
                return;
            }
            
            const rowsHTML = bookingsToShow.map(booking => {
                const date = new Date(booking.created_at).toLocaleDateString();
                const items = JSON.parse(booking.items_json || '[]');
                const itemsText = items.map(item => `${item.title} (${item.quantity})`).join(', ');
                
                return `
                    <tr>
                        <td>${booking.booking_reference}</td>
                        <td>${booking.customer_name}</td>
                        <td>${booking.type}</td>
                        <td>${itemsText}</td>
                        <td>$${parseFloat(booking.total_amount).toFixed(2)}</td>
                        <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
                        <td>${date}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-small" onclick="viewBooking(${booking.id})">View</button>
                                <button class="btn btn-success btn-small" onclick="updateBookingStatus(${booking.id})">Update</button>
                                <button class="btn btn-danger btn-small" onclick="cancelBooking(${booking.id})">Cancel</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            tbody.innerHTML = rowsHTML;
        }

        function updateStats() {
            const total = bookings.length;
            const pending = bookings.filter(b => b.status === 'pending').length;
            const confirmed = bookings.filter(b => b.status === 'confirmed').length;
            const revenue = bookings
                .filter(b => ['confirmed', 'checked_in', 'checked_out'].includes(b.status))
                .reduce((sum, b) => sum + parseFloat(b.total_amount), 0);
            
            document.getElementById('totalBookings').textContent = total;
            document.getElementById('pendingBookings').textContent = pending;
            document.getElementById('confirmedBookings').textContent = confirmed;
            document.getElementById('totalRevenue').textContent = '$' + revenue.toFixed(2);
        }

        function setupFilters() {
            document.getElementById('statusFilter').addEventListener('change', filterBookings);
            document.getElementById('typeFilter').addEventListener('change', filterBookings);
            document.getElementById('dateFrom').addEventListener('change', filterBookings);
            document.getElementById('dateTo').addEventListener('change', filterBookings);
            document.getElementById('searchFilter').addEventListener('input', filterBookings);
        }

        function filterBookings() {
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const search = document.getElementById('searchFilter').value.toLowerCase();
            
            let filteredBookings = bookings;
            
            if (status) {
                filteredBookings = filteredBookings.filter(b => b.status === status);
            }
            
            if (type) {
                filteredBookings = filteredBookings.filter(b => b.type === type);
            }
            
            if (dateFrom) {
                filteredBookings = filteredBookings.filter(b => b.created_at >= dateFrom);
            }
            
            if (dateTo) {
                filteredBookings = filteredBookings.filter(b => b.created_at <= dateTo);
            }
            
            if (search) {
                filteredBookings = filteredBookings.filter(b => 
                    b.customer_name.toLowerCase().includes(search) ||
                    b.customer_email.toLowerCase().includes(search) ||
                    b.booking_reference.toLowerCase().includes(search)
                );
            }
            
            displayBookings(filteredBookings);
        }

        function viewBooking(bookingId) {
            const booking = bookings.find(b => b.id == bookingId);
            if (!booking) return;
            
            const modal = document.getElementById('bookingModal');
            const content = document.getElementById('bookingDetails');
            
            const items = JSON.parse(booking.items_json || '[]');
            
            content.innerHTML = `
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="detail-label">Reference:</span>
                        <span class="detail-value">${booking.booking_reference}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Customer:</span>
                        <span class="detail-value">${booking.customer_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${booking.customer_email}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${booking.customer_phone || 'Not provided'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">${booking.type}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${booking.status}">${booking.status}</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">$${parseFloat(booking.total_amount).toFixed(2)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Deposit:</span>
                        <span class="detail-value">$${parseFloat(booking.deposit_amount).toFixed(2)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Balance:</span>
                        <span class="detail-value">$${parseFloat(booking.balance_amount).toFixed(2)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Created:</span>
                        <span class="detail-value">${new Date(booking.created_at).toLocaleString()}</span>
                    </div>
                    ${booking.check_in_date ? `
                    <div class="detail-row">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value">${new Date(booking.check_in_date).toLocaleDateString()}</span>
                    </div>
                    ` : ''}
                    ${booking.check_out_date ? `
                    <div class="detail-row">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value">${new Date(booking.check_out_date).toLocaleDateString()}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="booking-items">
                    <h4>Booked Items</h4>
                    ${items.map(item => `
                        <div class="booking-item">
                            <div>
                                <div style="font-weight: 600;">${item.title}</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Quantity: ${item.quantity}</div>
                            </div>
                            <div style="font-weight: 600; color: #059669;">$${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    `).join('')}
                </div>
                
                ${booking.special_requests ? `
                <div class="booking-details">
                    <h4>Special Requests</h4>
                    <p>${booking.special_requests}</p>
                </div>
                ` : ''}
            `;
            
            modal.style.display = 'block';
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function updateBookingStatus(bookingId) {
            const booking = bookings.find(b => b.id == bookingId);
            if (!booking) return;
            
            document.getElementById('bookingId').value = bookingId;
            document.getElementById('newStatus').value = booking.status;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        async function cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }
            
            try {
                const response = await fetch('../php/api/bookings.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        reason: 'Cancelled by admin'
                    })
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    alert('Booking cancelled successfully');
                    loadBookings();
                } else {
                    alert('Error cancelling booking');
                }
            } catch (error) {
                console.error('Error cancelling booking:', error);
                alert('Error cancelling booking');
            }
        }

        // Handle form submission
        document.getElementById('statusForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const bookingId = formData.get('booking_id');
            const status = formData.get('status');
            const notes = formData.get('notes');
            
            try {
                const response = await fetch('../php/api/bookings.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        status: status,
                        notes: notes
                    })
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    alert('Booking status updated successfully');
                    closeStatusModal();
                    loadBookings();
                } else {
                    alert('Error updating booking status');
                }
            } catch (error) {
                console.error('Error updating booking status:', error);
                alert('Error updating booking status');
            }
        });

        function exportBookings() {
            // Implement export functionality
            alert('Export functionality would be implemented here');
        }

        function refreshBookings() {
            loadBookings();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const bookingModal = document.getElementById('bookingModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target == bookingModal) {
                closeBookingModal();
            }
            if (event.target == statusModal) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>