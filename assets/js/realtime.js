/* SpeedEx Realtime + UX layer */
(function(){
  const BASE = window.SPEEDEX_BASE || '';

  /* ---------- App loader ---------- */
  window.addEventListener('load', () => {
    const l = document.getElementById('appLoader');
    if (l) setTimeout(() => l.classList.add('hidden'), 350);
  });

  /* ---------- Toasts ---------- */
  window.toast = function(msg, type='success'){
    const c = document.getElementById('toastContainer'); if(!c) return;
    const el = document.createElement('div');
    el.className = 'spx-toast ' + (type === 'error' ? 'error' : type === 'warn' ? 'warn' : '');
    el.innerHTML = `<i class="fas fa-${type==='error'?'circle-exclamation':type==='warn'?'triangle-exclamation':'circle-check'} me-2"></i>${msg}`;
    c.appendChild(el);
    setTimeout(() => { el.style.opacity='0'; el.style.transform='translateX(20px)'; setTimeout(()=>el.remove(),300); }, 3500);
  };

  /* ---------- Sidebar collapse + mobile ---------- */
  const sidebar = document.getElementById('speedexSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggle');
  const collapseBtn = document.getElementById('sidebarCollapseBtn');

  if (localStorage.getItem('speedex-sidebar-collapsed') === '1') {
    document.body.classList.add('sidebar-collapsed');
  }
  collapseBtn?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('speedex-sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
  });
  toggleBtn?.addEventListener('click', () => {
    sidebar?.classList.toggle('open');
    overlay?.classList.toggle('show');
  });
  overlay?.addEventListener('click', () => {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('show');
  });

  /* ---------- Notification dropdown ---------- */
  const bellBtn = document.getElementById('notifBellBtn');
  const dropdown = document.getElementById('notifDropdown');
  const badge = document.getElementById('notifBadge');
  bellBtn?.addEventListener('click', (e) => { e.stopPropagation(); dropdown?.classList.toggle('open'); loadNotifications(); });
  document.addEventListener('click', (e) => { if (dropdown && !dropdown.contains(e.target)) dropdown.classList.remove('open'); });

  async function loadNotifications(){
    if(!dropdown) return;
    try{
      const res = await fetch(`${BASE}/api/notifications.php`);
      const data = await res.json();
      if (badge) {
        badge.textContent = data.unread > 99 ? '99+' : data.unread;
        badge.style.display = data.unread > 0 ? 'inline-flex' : 'none';
      }
      if (!data.items.length) { dropdown.innerHTML = '<div class="notif-empty">No notifications yet</div>'; return; }
      dropdown.innerHTML = data.items.map(n => `
        <div class="notif-item">
          <div class="t">${escapeHtml(n.title)}</div>
          <div class="m">${escapeHtml(n.message)}</div>
          <div class="d">${timeAgo(n.created_at)}</div>
        </div>`).join('');
    }catch(e){}
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  function timeAgo(dt){ const d = new Date(dt.replace(' ','T')); const s = (Date.now()-d.getTime())/1000; if(s<60)return 'just now'; if(s<3600)return Math.floor(s/60)+'m ago'; if(s<86400)return Math.floor(s/3600)+'h ago'; return Math.floor(s/86400)+'d ago'; }

  /* ---------- Dashboard live data ---------- */
  async function loadDashboardData(){
    const grid = document.getElementById('liveStatsGrid');
    if(!grid) return;
    try{
      const r = await fetch(`${BASE}/api/dashboard-stats.php`); const d = await r.json();
      setText('stat-total', d.total);
      setText('stat-transit', d.in_transit);
      setText('stat-delivered', d.delivered);
      setText('stat-pending', d.pending);
      setText('stat-revenue', '৳ ' + (d.revenue||0).toLocaleString());
    }catch(e){}
    refreshRecent();
    refreshCharts();
    loadNotifications();
  }
  function setText(id, v){ const el = document.getElementById(id); if(el) el.textContent = (typeof v==='number') ? v.toLocaleString() : v; }

  async function refreshRecent(){
    const tbody = document.getElementById('recentParcelsBody'); if(!tbody) return;
    try{
      const r = await fetch(`${BASE}/api/recent-orders.php`); const d = await r.json();
      tbody.innerHTML = d.parcels.map(p => `
        <tr>
          <td class="text-primary-green">${escapeHtml(p.tracking_id)}</td>
          <td>${escapeHtml(p.sender_name)}</td>
          <td>${escapeHtml(p.receiver_name)}</td>
          <td>${escapeHtml(p.from_hub)}</td>
          <td>${escapeHtml(p.to_hub)}</td>
          <td><span class="badge-status badge-${p.status_class}">${escapeHtml(p.status_label)}</span></td>
          <td>${escapeHtml(p.payment_method)}</td>
          <td>${escapeHtml(p.created_at)}</td>
        </tr>`).join('') || '<tr><td colspan="8" class="text-center" style="color:var(--text-muted);padding:20px;">No parcels yet</td></tr>';
    }catch(e){}
  }

  /* ---------- Charts ---------- */
  let charts = {};
  async function refreshCharts(){
    if (typeof Chart === 'undefined') return;
    try{
      const r = await fetch(`${BASE}/api/chart-data.php`); const d = await r.json();
      drawLine('deliveryChart', d.monthly?.labels, d.monthly?.parcels, 'Parcels');
      drawDonut('parcelOverviewChart', ['Delivered','In Transit','Pending'], [d.overview?.delivered||0, d.overview?.in_transit||0, d.overview?.pending||0]);
      drawBar('topRoutesChart', d.routes?.labels, d.routes?.values, 'Parcels');
      drawLine('revenueChart', d.monthly?.labels, d.monthly?.revenue, 'Revenue (৳)');
    }catch(e){}
  }
  function drawLine(id, labels, values, label){
    const c = document.getElementById(id); if(!c||!labels) return;
    if (charts[id]) { charts[id].data.labels = labels; charts[id].data.datasets[0].data = values; charts[id].update(); return; }
    charts[id] = new Chart(c, { type:'line', data:{labels, datasets:[{label, data:values, borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,.18)', fill:true, tension:.4, pointRadius:3}]}, options:chartOpts() });
  }
  function drawBar(id, labels, values, label){
    const c = document.getElementById(id); if(!c||!labels) return;
    if (charts[id]) { charts[id].data.labels = labels; charts[id].data.datasets[0].data = values; charts[id].update(); return; }
    charts[id] = new Chart(c, { type:'bar', data:{labels, datasets:[{label, data:values, backgroundColor:'rgba(34,197,94,.7)', borderRadius:6}]}, options:chartOpts() });
  }
  function drawDonut(id, labels, values){
    const c = document.getElementById(id); if(!c) return;
    if (charts[id]) { charts[id].data.datasets[0].data = values; charts[id].update(); return; }
    charts[id] = new Chart(c, { type:'doughnut', data:{labels, datasets:[{data:values, backgroundColor:['#22c55e','#3b82f6','#f59e0b'], borderWidth:0}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:'#94a3b8'}}}} });
  }
  function chartOpts(){ return { responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{color:'#94a3b8'}}}, scales:{x:{ticks:{color:'#64748b'},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'#64748b'},grid:{color:'rgba(255,255,255,.05)'}}}}; }

  if (document.getElementById('liveStatsGrid')) {
    loadDashboardData();
    setInterval(loadDashboardData, 5000);
  } else {
    setInterval(loadNotifications, 10000);
    loadNotifications();
  }

  /* ---------- Live tracking ---------- */
  const trackForm = document.getElementById('liveTrackForm');
  trackForm?.addEventListener('submit', e => { e.preventDefault(); doTrack(document.getElementById('trackingInput').value.trim()); });
  const url = new URLSearchParams(location.search);
  if (url.get('id')) { const i = document.getElementById('trackingInput'); if (i) i.value = url.get('id'); doTrack(url.get('id')); }
  let trackInterval = null;
  async function doTrack(id){
    if (!id) return;
    const out = document.getElementById('trackingResults'); if(!out) return;
    out.style.display='block'; out.innerHTML = '<div class="text-center" style="padding:30px;color:var(--text-muted);"><div class="loader-ring" style="margin:0 auto 14px;width:42px;height:42px;border-width:2px;"></div>Fetching live data…</div>';
    try{
      const r = await fetch(`${BASE}/api/track.php?id=${encodeURIComponent(id)}`); const d = await r.json();
      if (!d.success) { out.innerHTML = `<div class="glass-card text-center" style="padding:30px;color:#ef4444;"><i class="fas fa-circle-exclamation me-2"></i>${escapeHtml(d.message||'Not found')}</div>`; return; }
      const p = d.parcel;
      const steps = d.tracking.map((t,i)=>`
        <div class="tracking-step ${i===d.tracking.length-1?'active':'completed'}">
          <div class="tracking-dot"><i class="fas fa-circle-check"></i></div>
          <div>
            <h6 style="font-weight:600;font-size:.9rem;margin-bottom:2px;">${escapeHtml(t.status)}</h6>
            <p style="color:var(--text-secondary);font-size:.8rem;margin-bottom:0;">${escapeHtml(t.location||'')}</p>
            <small style="color:var(--text-muted);">${escapeHtml(t.created_at)}</small>
          </div>
        </div>`).join('');
      out.innerHTML = `
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div><h5 class="mb-1">Tracking <span class="text-primary-green">${escapeHtml(p.tracking_id)}</span></h5>
              <small style="color:var(--text-muted);"><span class="live-dot"></span>Auto-refreshing every 5s</small></div>
            <span class="badge-status badge-${p.status_class}">${escapeHtml(p.status_label)}</span>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="hover-glow" style="background:rgba(255,255,255,.04);padding:14px;border-radius:12px;"><h6 style="color:var(--primary);font-size:.8rem;margin-bottom:6px;">FROM (Sender)</h6><div>${escapeHtml(p.sender_name)}</div><small style="color:var(--text-muted);">${escapeHtml(p.sender_address)}</small></div></div>
            <div class="col-md-6"><div class="hover-glow" style="background:rgba(255,255,255,.04);padding:14px;border-radius:12px;"><h6 style="color:var(--primary);font-size:.8rem;margin-bottom:6px;">TO (Receiver)</h6><div>${escapeHtml(p.receiver_name)}</div><small style="color:var(--text-muted);">${escapeHtml(p.receiver_address)}</small></div></div>
          </div>
          <div class="tracking-timeline">${steps || '<p style="color:var(--text-muted);">No updates yet.</p>'}</div>
        </div>`;
    }catch(e){ out.innerHTML = '<div class="glass-card text-center" style="color:#ef4444;padding:30px;">Failed to load tracking.</div>'; }
    if (trackInterval) clearInterval(trackInterval);
    trackInterval = setInterval(()=>doTrack(id), 5000);
  }

  /* ---------- Send Parcel cost calculator ---------- */
  const cost = document.getElementById('estCost');
  const weight = document.getElementById('weightInput');
  const dtype = document.getElementById('deliveryType');
  function recalc(){
    if(!cost) return;
    const w = parseFloat(weight?.value||'0')||0;
    const t = dtype?.value || 'standard';
    const base = t==='same_day'?180:t==='express'?120:80;
    const total = base + Math.max(0,w-1)*20;
    cost.textContent = '৳ ' + total.toFixed(2);
    const hidden = document.getElementById('totalAmountHidden'); if(hidden) hidden.value = total.toFixed(2);
    const hidden2 = document.getElementById('chargeHidden'); if(hidden2) hidden2.value = total.toFixed(2);
  }
  weight?.addEventListener('input', recalc); dtype?.addEventListener('change', recalc); recalc();
})();
