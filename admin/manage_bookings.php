<?php require_once __DIR__ . '/../php/config.php'; session_start(); if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; } ?>
<!doctype html><html><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Manage Bookings - Mazvikadei</title><link rel="stylesheet" href="../styles.css"/></head><body>
<header class="site-header"><div class="container header-inner"><div class="brand">Mazvikadei Admin</div><nav class="nav"><a href="dashboard.php">Dashboard</a><a href="manage_bookings.php" class="active">Bookings</a><a href="logout.php">Logout</a></nav></div></header>
<main class="container">
  <h1>Bookings</h1>
  <div class="card" id="bookingsArea">Loading bookings...</div>
</main>
<script>
async function loadBookings(){
  const res = await fetch('../php/get_bookings.php');
  const data = await res.json();
  const area = document.getElementById('bookingsArea');
  if(!data.length) { area.textContent = 'No bookings yet.'; return; }
  area.innerHTML = '';
  data.forEach(b=>{
    const el = document.createElement('div');
    el.className = 'card';
    el.style.marginBottom = '.6rem';
    el.innerHTML = `<div style="display:flex;justify-content:space-between"><div><strong>#${b.id}</strong><div class="muted">${b.created_at}</div><div class="muted">Customer: ${b.customer_name} (${b.customer_email})</div></div><div><button class="btn" data-id="${b.id}">Delete</button></div></div><div style="margin-top:.6rem"><pre class="muted">${b.items_json}</pre></div>`;
    area.appendChild(el);
  });
}
loadBookings();
</script>
</body></html>
