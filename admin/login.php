<?php require_once __DIR__ . '/../php/config.php'; session_start(); if(isset($_SESSION['user_id'])){ header('Location: dashboard.php'); exit; } ?>
<!doctype html><html><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Admin Login - Mazvikadei</title><link rel="stylesheet" href="../styles.css"/></head><body>
<div class="container" style="max-width:420px;margin:4rem auto">
  <div class="card"><h2>Admin Login</h2><form id="loginForm"><div class="form-row"><input name="email" type="email" placeholder="Email" required></div><div class="form-row"><input name="password" type="password" placeholder="Password" required></div><div style="margin-top:.6rem"><button class="btn primary">Login</button></div><div id="loginStatus" class="muted" style="margin-top:.6rem"></div></form></div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const payload = { email: fd.get('email'), password: fd.get('password') };
  const res = await fetch('../php/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
  const data = await res.json();
  if(data.ok){ window.location = 'dashboard.php'; } else { document.getElementById('loginStatus').textContent = 'Invalid login'; }
});
</script>
</body></html>
