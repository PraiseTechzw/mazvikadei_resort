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
    <title>Contact Messages - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css"/>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-small {
            padding: 0.5rem 1rem;
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
        .status-new { background: #dbeafe; color: #1e40af; }
        .status-read { background: #d1fae5; color: #065f46; }
        .status-replied { background: #fef3c7; color: #92400e; }
        .status-closed { background: #f3f4f6; color: #374151; }
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
        .message-details {
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
            width: 120px;
            color: #374151;
        }
        .detail-value {
            color: #6b7280;
        }
        .message-content {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            white-space: pre-wrap;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
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
        .form-group select,
        .form-group input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">Mazvikadei Resort - Contact Messages</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php">Dashboard</a>
                <a href="manage_rooms.php">Rooms</a>
                <a href="manage_activities.php">Activities</a>
                <a href="manage_events.php">Events</a>
                <a href="manage_bookings.php">Bookings</a>
                <a href="manage_customers.php">Customers</a>
                <a href="contact_messages.php" class="active">Messages</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-container">
            <div class="page-header">
                <h1>Contact Messages</h1>
                <div>
                    <span id="messageCount" class="muted">Loading...</span>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3>Filter Messages</h3>
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchFilter">Search</label>
                        <input type="text" id="searchFilter" placeholder="Search by name or email...">
                    </div>
                    <div class="form-group">
                        <label for="dateFilter">Date Range</label>
                        <input type="date" id="dateFilter">
                    </div>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messagesTableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading messages...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Message Details</h2>
                <span class="close" onclick="closeMessageModal()">&times;</span>
            </div>
            <div id="messageContent">
                <!-- Message content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let messages = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadMessages();
            setupFilters();
        });

        async function loadMessages() {
            try {
                const response = await fetch('../php/api/contact.php');
                const data = await response.json();
                messages = data.messages || [];
                displayMessages();
                updateMessageCount();
            } catch (error) {
                console.error('Error loading messages:', error);
                document.getElementById('messagesTableBody').innerHTML = 
                    '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #dc2626;">Error loading messages</td></tr>';
            }
        }

        function displayMessages(filteredMessages = null) {
            const tbody = document.getElementById('messagesTableBody');
            const messagesToShow = filteredMessages || messages;
            
            if (messagesToShow.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">No messages found</td></tr>';
                return;
            }
            
            const rowsHTML = messagesToShow.map(message => {
                const date = new Date(message.created_at).toLocaleDateString();
                const messagePreview = message.message.length > 100 ? 
                    message.message.substring(0, 100) + '...' : message.message;
                
                return `
                    <tr>
                        <td>${message.name}</td>
                        <td>${message.email}</td>
                        <td>${message.subject || 'No Subject'}</td>
                        <td class="message-preview">${messagePreview}</td>
                        <td><span class="status-badge status-${message.status}">${message.status}</span></td>
                        <td>${date}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-small" onclick="viewMessage(${message.id})">View</button>
                                <button class="btn btn-success btn-small" onclick="updateMessageStatus(${message.id}, 'read')">Mark Read</button>
                                <button class="btn btn-secondary btn-small" onclick="updateMessageStatus(${message.id}, 'closed')">Close</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            tbody.innerHTML = rowsHTML;
        }

        function updateMessageCount() {
            const total = messages.length;
            const newCount = messages.filter(m => m.status === 'new').length;
            document.getElementById('messageCount').textContent = `${total} messages (${newCount} new)`;
        }

        function setupFilters() {
            document.getElementById('statusFilter').addEventListener('change', filterMessages);
            document.getElementById('searchFilter').addEventListener('input', filterMessages);
            document.getElementById('dateFilter').addEventListener('change', filterMessages);
        }

        function filterMessages() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
            const dateFilter = document.getElementById('dateFilter').value;
            
            let filteredMessages = messages;
            
            if (statusFilter) {
                filteredMessages = filteredMessages.filter(m => m.status === statusFilter);
            }
            
            if (searchFilter) {
                filteredMessages = filteredMessages.filter(m => 
                    m.name.toLowerCase().includes(searchFilter) || 
                    m.email.toLowerCase().includes(searchFilter) ||
                    (m.subject && m.subject.toLowerCase().includes(searchFilter))
                );
            }
            
            if (dateFilter) {
                filteredMessages = filteredMessages.filter(m => 
                    m.created_at.startsWith(dateFilter)
                );
            }
            
            displayMessages(filteredMessages);
        }

        function viewMessage(messageId) {
            const message = messages.find(m => m.id == messageId);
            if (!message) return;
            
            const modal = document.getElementById('messageModal');
            const content = document.getElementById('messageContent');
            
            content.innerHTML = `
                <div class="message-details">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${message.name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${message.email}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${message.phone || 'Not provided'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Subject:</span>
                        <span class="detail-value">${message.subject || 'No subject'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${message.status}">${message.status}</span></span>
                    </div>
                </div>
                <div class="message-content">${message.message}</div>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="replyToMessage('${message.email}')">Reply</button>
                    <button class="btn btn-success" onclick="updateMessageStatus(${message.id}, 'read')">Mark as Read</button>
                    <button class="btn btn-secondary" onclick="updateMessageStatus(${message.id}, 'closed')">Close</button>
                </div>
            `;
            
            modal.style.display = 'block';
            
            // Mark as read when viewing
            if (message.status === 'new') {
                updateMessageStatus(messageId, 'read');
            }
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        async function updateMessageStatus(messageId, status) {
            try {
                const response = await fetch('../php/api/contact.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message_id: messageId,
                        status: status
                    })
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    // Update local data
                    const message = messages.find(m => m.id == messageId);
                    if (message) {
                        message.status = status;
                    }
                    
                    // Refresh display
                    loadMessages();
                    
                    if (status === 'read' || status === 'closed') {
                        closeMessageModal();
                    }
                } else {
                    alert('Error updating message status');
                }
            } catch (error) {
                console.error('Error updating message status:', error);
                alert('Error updating message status');
            }
        }

        function replyToMessage(email) {
            window.location.href = `mailto:${email}`;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                closeMessageModal();
            }
        }
    </script>
</body>
</html>
