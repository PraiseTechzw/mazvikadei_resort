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
    <title>Manage Rooms - Mazvikadei Resort</title>
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
        .btn-primary:hover {
            background: #2563eb;
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
        .status-available { background: #d1fae5; color: #065f46; }
        .status-occupied { background: #fee2e2; color: #991b1b; }
        .status-maintenance { background: #fef3c7; color: #92400e; }
        .status-out_of_order { background: #f3f4f6; color: #374151; }
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
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">Mazvikadei Resort - Room Management</div>
            <nav class="nav">
                <a href="enhanced_dashboard.php">Dashboard</a>
                <a href="manage_rooms.php" class="active">Rooms</a>
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
        <div class="admin-container">
            <div class="page-header">
                <h1>Room Management</h1>
                <button class="btn btn-primary" onclick="openRoomModal()">Add New Room</button>
            </div>

            <!-- Rooms Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Max Occupancy</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomsTableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                Loading rooms...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Room Modal -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Room</h2>
                <span class="close" onclick="closeRoomModal()">&times;</span>
            </div>
            <form id="roomForm">
                <input type="hidden" id="roomId" name="room_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="roomNumber">Room Number *</label>
                        <input type="text" id="roomNumber" name="room_number" required>
                    </div>
                    <div class="form-group">
                        <label for="roomTitle">Title *</label>
                        <input type="text" id="roomTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="categoryId">Category *</label>
                        <select id="categoryId" name="category_id" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="maxOccupancy">Max Occupancy *</label>
                        <input type="number" id="maxOccupancy" name="max_occupancy" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="out_of_order">Out of Order</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="amenities">Amenities</label>
                    <textarea id="amenities" name="amenities" rows="3" placeholder="List amenities separated by commas"></textarea>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Room description"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeRoomModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Room</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let rooms = [];
        let categories = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadRooms();
            loadCategories();
        });

        async function loadRooms() {
            try {
                const response = await fetch('../php/api/rooms.php');
                const data = await response.json();
                rooms = data.rooms;
                categories = data.categories;
                displayRooms();
                populateCategorySelect();
            } catch (error) {
                console.error('Error loading rooms:', error);
                document.getElementById('roomsTableBody').innerHTML = 
                    '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #dc2626;">Error loading rooms</td></tr>';
            }
        }

        function displayRooms() {
            const tbody = document.getElementById('roomsTableBody');
            
            if (rooms.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">No rooms found</td></tr>';
                return;
            }
            
            const rowsHTML = rooms.map(room => `
                <tr>
                    <td>${room.room_number}</td>
                    <td>${room.title}</td>
                    <td>${room.category_name || 'N/A'}</td>
                    <td>$${parseFloat(room.price).toFixed(2)}</td>
                    <td>${room.max_occupancy}</td>
                    <td><span class="status-badge status-${room.status}">${room.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-small" onclick="editRoom(${room.id})">Edit</button>
                            <button class="btn btn-danger btn-small" onclick="deleteRoom(${room.id})">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            tbody.innerHTML = rowsHTML;
        }

        function populateCategorySelect() {
            const select = document.getElementById('categoryId');
            select.innerHTML = '<option value="">Select Category</option>';
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }

        function openRoomModal(roomId = null) {
            const modal = document.getElementById('roomModal');
            const form = document.getElementById('roomForm');
            const title = document.getElementById('modalTitle');
            
            if (roomId) {
                const room = rooms.find(r => r.id == roomId);
                if (room) {
                    title.textContent = 'Edit Room';
                    document.getElementById('roomId').value = room.id;
                    document.getElementById('roomNumber').value = room.room_number;
                    document.getElementById('roomTitle').value = room.title;
                    document.getElementById('categoryId').value = room.category_id;
                    document.getElementById('price').value = room.price;
                    document.getElementById('maxOccupancy').value = room.max_occupancy;
                    document.getElementById('status').value = room.status;
                    document.getElementById('amenities').value = room.amenities || '';
                    document.getElementById('description').value = room.description || '';
                    document.getElementById('image').value = room.image || '';
                }
            } else {
                title.textContent = 'Add New Room';
                form.reset();
                document.getElementById('roomId').value = '';
            }
            
            modal.style.display = 'block';
        }

        function closeRoomModal() {
            document.getElementById('roomModal').style.display = 'none';
        }

        function editRoom(roomId) {
            openRoomModal(roomId);
        }

        async function deleteRoom(roomId) {
            if (!confirm('Are you sure you want to delete this room?')) {
                return;
            }
            
            try {
                // In a real implementation, you would have a delete endpoint
                alert('Delete functionality would be implemented here');
            } catch (error) {
                console.error('Error deleting room:', error);
                alert('Error deleting room');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('roomModal');
            if (event.target == modal) {
                closeRoomModal();
            }
        }

        // Handle form submission
        document.getElementById('roomForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const roomData = Object.fromEntries(formData.entries());
            
            try {
                // In a real implementation, you would submit to a save endpoint
                alert('Save functionality would be implemented here');
                closeRoomModal();
                loadRooms(); // Refresh the table
            } catch (error) {
                console.error('Error saving room:', error);
                alert('Error saving room');
            }
        });
    </script>
</body>
</html>
