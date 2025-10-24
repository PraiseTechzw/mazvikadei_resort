/* app.js for Mazvikadei Resort - frontend interactions */
/* Uses localStorage for demo cart/bookings. Set USE_SERVER = true to enable php/book.php endpoint. */
const USE_SERVER = false;
const ROOMS_URL = 'php_api/rooms.json'; // local sample used by frontend
const ACTIVITIES_URL = 'php_api/activities.json';

function $$(sel){return Array.from(document.querySelectorAll(sel))}
function $(sel){return document.querySelector(sel)}

function setYear(){ $$('.year').forEach(el=>el.textContent = new Date().getFullYear()); $$('#year').forEach(el=>el.textContent=new Date().getFullYear()); }
setYear();

// demo storage keys
const CART_KEY = 'mz_cart_v1';

function getCart(){ try{return JSON.parse(localStorage.getItem(CART_KEY))||[]}catch(e){return []} }
function saveCart(c){ localStorage.setItem(CART_KEY, JSON.stringify(c)); updateChosen(); }
function addToCart(item){ const c=getCart(); c.push(item); saveCart(c); alert('Added to selection'); }
function clearCart(){ localStorage.removeItem(CART_KEY); updateChosen(); }

function updateChosen(){
  const chosen = getCart();
  const el = $('#chosenList');
  if(!el) return;
  if(!chosen.length){ el.textContent = 'No items selected. Use Rooms or Activities pages to add items.'; return; }
  el.innerHTML = '';
  chosen.forEach(it=>{
    const d = document.createElement('div'); d.className='cart-item'; d.innerHTML = `<div><strong>${it.title}</strong><div class="muted">${it.note||''}</div></div><div><button class="btn" data-id="${it.id}">Remove</button></div>`;
    d.querySelector('button').addEventListener('click', ()=>{ const remaining = getCart().filter(x=>x.id!==it.id); saveCart(remaining); updateChosen(); });
    el.appendChild(d);
  });
}

// load rooms and activities from packaged JSON (php_api folder)
async function fetchJSON(path){ try{ const r = await fetch(path); return await r.json(); }catch(e){ return []; } }

async function renderRoomsPage(){
  const container = $('#roomsList'); if(!container) return;
  const rooms = await fetchJSON(ROOMS_URL);
  const q = $('#roomSearch')?$('#roomSearch').value.toLowerCase():'';
  const checkin = $('#checkin')?$('#checkin').value:'';
  const checkout = $('#checkout')?$('#checkout').value:'';
  container.innerHTML = '';
  rooms.filter(r=> !q || r.title.toLowerCase().includes(q) || (r.amenities||'').toLowerCase().includes(q)).forEach(r=>{
    const el = document.createElement('div'); el.className='card room';
    el.innerHTML = `<img src="${r.image}" alt=""><div class="meta"><div style="display:flex;justify-content:space-between"><strong>${r.title}</strong><div class="price">$${r.price}/night</div></div><div class="muted">${r.amenities||r.description||''}</div><div style="margin-top:.6rem"><button class="btn primary">Add & Book</button></div></div>`;
    el.querySelector('button').addEventListener('click', ()=>{
      if(!checkin || !checkout){ alert('Select check-in and check-out dates first'); return; }
      addToCart({ id: Date.now(), type:'room', title:r.title, room_id:r.id, checkin, checkout, note: (r.amenities||'') });
      updateChosen();
    });
    container.appendChild(el);
  });
}

async function renderActivitiesPage(){
  const container = $('#activitiesList'); if(!container) return;
  const acts = await fetchJSON(ACTIVITIES_URL);
  container.innerHTML = '';
  acts.forEach(a=>{
    const el = document.createElement('div'); el.className='card room';
    el.innerHTML = `<div class="meta"><div style="display:flex;justify-content:space-between"><strong>${a.title}</strong><div class="price">$${a.price}</div></div><div class="muted">${a.description}<div>${a.duration} • ${a.schedule}</div></div><div style="margin-top:.6rem"><button class="btn primary">Add & Book</button></div></div>`;
    el.querySelector('button').addEventListener('click', ()=>{
      addToCart({ id: Date.now(), type:'activity', title: a.title, activity_id: a.id, note: a.duration });
      updateChosen();
    });
    container.appendChild(el);
  });
}

document.addEventListener('DOMContentLoaded', ()=>{
  updateChosen();
  if($('#searchBtn')){ $('#searchBtn').addEventListener('click', renderRoomsPage); renderRoomsPage(); }
  if($('#activitiesList')){ renderActivitiesPage(); }

  // checkout form
  const checkoutForm = $('#checkoutForm');
  if(checkoutForm){
    checkoutForm.addEventListener('submit', async e=>{
      e.preventDefault();
      const fd = new FormData(checkoutForm);
      const payload = {
        type: 'mixed',
        items: getCart(),
        customer: { fullname: fd.get('fullname'), email: fd.get('email'), phone: fd.get('phone') },
        extras: ''
      };
      // optional: upload attachment for event page - not included here
      if (USE_SERVER){
        try{
          const res = await fetch('php/book.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const data = await res.json();
          if(data.ok){ document.getElementById('checkoutStatus').textContent = 'Booking placed (server). Ref: '+data.id; clearCart(); updateChosen(); }
          else document.getElementById('checkoutStatus').textContent = 'Booking failed (server). Saved locally instead.';
        }catch(e){ document.getElementById('checkoutStatus').textContent = 'Server error. Saved locally.'; }
      } else {
        // local fallback: save into localStorage for demo
        const bookings = JSON.parse(localStorage.getItem('mz_bookings_v1')||'[]');
        const booking = { id: Date.now(), items: payload.items, customer: payload.customer, extras: '', created_at: new Date().toISOString(), type:'mixed' };
        bookings.push(booking); localStorage.setItem('mz_bookings_v1', JSON.stringify(bookings));
        document.getElementById('checkoutStatus').textContent = 'Booking stored locally (demo). Ref: ' + booking.id;
        clearCart(); updateChosen();
      }
    });
  }

  // event form (client-side upload base64)
  const eventForm = $('#eventForm');
  if(eventForm){
    eventForm.addEventListener('submit', async e=>{
      e.preventDefault();
      const fd = new FormData(eventForm);
      const f = $('#attachment')?.files?.[0];
      let base64 = null;
      if (f){
        base64 = await new Promise(res=>{
          const reader = new FileReader(); reader.onload = ()=>res(reader.result); reader.readAsDataURL(f);
        });
      }
      const payload = { type:'event', items:[{ title: fd.get('title'), date: fd.get('date'), time: fd.get('time'), guests: fd.get('guests'), catering: fd.get('catering') }], customer: { fullname:'Guest', email:'guest@local' }, extras: '', attachment_base64: base64 };
      if (USE_SERVER){
        const r = await fetch('php/book.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const j = await r.json();
        if(j.ok) $('#eventStatus').textContent = 'Event request submitted. Ref: ' + j.id;
      } else {
        // store locally
        const bookings = JSON.parse(localStorage.getItem('mz_bookings_v1')||'[]');
        const b = { id: Date.now(), items: payload.items, customer: { fullname: 'Guest', email: 'guest@local' }, extras: '', created_at: new Date().toISOString(), type:'event' };
        bookings.push(b); localStorage.setItem('mz_bookings_v1', JSON.stringify(bookings));
        $('#eventStatus').textContent = 'Event request saved locally (demo). Ref: ' + b.id;
      }
    });
  }
});
