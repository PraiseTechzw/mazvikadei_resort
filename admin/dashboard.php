<?php require_once __DIR__ . '/../php/config.php'; session_start(); if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; } ?>
<!doctype html><html><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Admin Dashboard - Mazvikadei</title><link rel="stylesheet" href="../styles.css"/></head><body>
<header class="site-header"><div class="container header-inner"><div class="brand">Mazvikadei Admin</div><nav class="nav"><a href="dashboard.php" class="active">Dashboard</a><a href="manage_rooms.php">Rooms</a><a href="manage_activities.php">Activities</a><a href="manage_events.php">Events</a><a href="manage_bookings.php">Bookings</a><a href="logout.php">Logout</a></nav></div></header>
<main class="container">
  <h1>Admin Dashboard</h1>
  <div class="card"><h3>Quick Reports</h3><div id="reports" class="muted">Loading...</div></div>
  <div style="margin-top:1rem" class="card"><h3>Recent Bookings</h3><div id="recentBookings" class="muted">Loading...</div></div>
</main>
<script>
async function loadReports(){
  const res = await fetch('../php/get_bookings.php');
  const data = await res.json();
  document.getElementById('recentBookings').textContent = data.length + ' bookings (latest displayed in manage bookings).';
  document.getElementById('reports').textContent = 'Total bookings: ' + data.length;
}
loadReports();
</script>
</body></html>
