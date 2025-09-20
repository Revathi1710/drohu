<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';
ini_set('display_errors', 1);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Closed Days Calendar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --border:#e5e7eb; --muted:#6b7280; --closed:#fee2e2; --closed-text:#991b1b; --today:#ecfeff; --today-border:#06b6d4; }
    body{ font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
    .main-container{ padding:16px; margin-left:var(--sidebar-width); }
    .cal-card{ background:#fff; border:1px solid var(--border); border-radius:14px; box-shadow: 0 8px 24px rgba(0,0,0,.06); }
    .cal-head{ padding:12px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .cal-title{ font-weight:800; margin:0; }
    .cal-grid{ display:grid; grid-template-columns:repeat(7,1fr); }
    .cal-dow{ padding:10px 8px; text-align:center; font-weight:700; font-size:12px; color:var(--muted); border-bottom:1px solid var(--border); }
    .cal-cell{ min-height:90px; padding:8px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); position:relative; cursor:pointer; }
    .cal-cell:nth-child(7n){ border-right:none; }
    .cal-daynum{ font-weight:700; font-size:13px; color:#111827; }
    .cal-muted{ opacity:.45; }
    .cal-closed{ background:var(--closed); }
    .cal-closed .cal-daynum{ color:var(--closed-text); }
    .cal-today{ outline:2px solid var(--today-border); background:var(--today); }
    .reason-badge{ position:absolute; left:8px; right:8px; bottom:8px; background:#fff; border:1px solid var(--border); border-radius:8px; padding:4px 6px; font-size:11px; color:#374151; max-height:40px; overflow:hidden; }
    .legend{ display:flex; gap:10px; align-items:center; }
    .legend > span{ display:inline-flex; align-items:center; gap:6px; font-size:13px; color:#374151; }
    .legend-dot{ width:14px; height:14px; border-radius:4px; border:1px solid var(--border); background:#fff; }
    .legend-dot.closed{ background:var(--closed); border-color:#fecaca; }
    .legend-dot.today{ background:var(--today); border-color:var(--today-border); }
    @media (max-width: 576px){
      .cal-cell{ min-height:78px; }
      .reason-badge{ display:none; }
    }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="cal-card">
      <div class="cal-head">
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-secondary btn-sm" id="prevBtn">&laquo;</button>
          <h5 class="cal-title mb-0" id="calTitle">Month YYYY</h5>
          <button class="btn btn-outline-secondary btn-sm" id="nextBtn">&raquo;</button>
          <button class="btn btn-outline-primary btn-sm" id="todayBtn">Today</button>
        </div>
        <div class="legend">
          <span><span class="legend-dot closed"></span> Closed</span>
          <span><span class="legend-dot today"></span> Today</span>
          <span class="text-muted small">Click a date to toggle closed/open</span>
        </div>
      </div>

      <div class="cal-grid" id="calDow">
        <div class="cal-dow">Sun</div><div class="cal-dow">Mon</div><div class="cal-dow">Tue</div>
        <div class="cal-dow">Wed</div><div class="cal-dow">Thu</div><div class="cal-dow">Fri</div><div class="cal-dow">Sat</div>
      </div>
      <div class="cal-grid" id="calBody"></div>
    </div>
  </div>

  <!-- Add reason modal -->
  <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="reasonForm">
          <div class="modal-header">
            <h5 class="modal-title">Mark Closed</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Date</label>
              <input type="text" class="form-control" id="reasonDate" readonly>
            </div>
            <div class="mb-2">
              <label class="form-label">Reason (optional)</label>
              <input type="text" class="form-control" id="reasonInput" placeholder="e.g., Maintenance, Holiday" required>
            </div>
            <div id="reasonErr" class="text-danger small d-none"></div>
            <div id="reasonOk" class="text-success small d-none"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="reasonSaveBtn">
              <span class="save-text">Save</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const calTitle = document.getElementById('calTitle');
    const calBody  = document.getElementById('calBody');
    const prevBtn  = document.getElementById('prevBtn');
    const nextBtn  = document.getElementById('nextBtn');
    const todayBtn = document.getElementById('todayBtn');

    const reasonModalEl = document.getElementById('reasonModal');
    const reasonModal   = new bootstrap.Modal(reasonModalEl);
    const reasonDateEl  = document.getElementById('reasonDate');
    const reasonInputEl = document.getElementById('reasonInput');
    const reasonForm    = document.getElementById('reasonForm');
    const reasonErr     = document.getElementById('reasonErr');
    const reasonOk      = document.getElementById('reasonOk');
    const reasonSaveBtn = document.getElementById('reasonSaveBtn');

    let viewYear, viewMonth; // month is 0-11
    let closedMap = Object.create(null); // 'YYYY-MM-DD' -> reason

    function ymd(d){ return d.toISOString().slice(0,10); }
    function toYMD(y,m,day){
      const mm = String(m+1).padStart(2,'0');
      const dd = String(day).padStart(2,'0');
      return `${y}-${mm}-${dd}`;
    }
    function monthName(m){ return ['January','February','March','April','May','June','July','August','September','October','November','December'][m]; }

    function setClosedMap(days){
      closedMap = Object.create(null);
      (days||[]).forEach(d => { if (d.date) closedMap[d.date] = d.reason || ''; });
    }

    async function loadClosedDays(){
      const res = await fetch('closed_days_list.php',{cache:'no-store'});
      const data = await res.json();
      if (!data.ok) throw new Error(data.error || 'load_failed');
      setClosedMap(data.days);
    }

    function buildCalendar(y, m){
      calBody.innerHTML = '';
      calTitle.textContent = `${monthName(m)} ${y}`;

      const first = new Date(y, m, 1);
      const startDow = first.getDay(); // 0-6
      const lastDay = new Date(y, m+1, 0).getDate(); // days in month

      const today = new Date();
      const tY = today.getFullYear(), tM = today.getMonth(), tD = today.getDate();

      // previous month filler
      const prevLast = new Date(y, m, 0).getDate();
      for (let i = 0; i < startDow; i++){
        const dnum = prevLast - startDow + 1 + i;
        const cell = document.createElement('div');
        cell.className = 'cal-cell cal-muted';
        cell.innerHTML = `<div class="cal-daynum">${dnum}</div>`;
        cell.style.cursor = 'default';
        calBody.appendChild(cell);
      }

      // current month days
      for (let d = 1; d <= lastDay; d++){
        const cell = document.createElement('div');
        cell.className = 'cal-cell';
        const isToday = (y===tY && m===tM && d===tD);
        if (isToday) cell.classList.add('cal-today');

        const dateStr = toYMD(y, m, d);
        const isClosed = !!closedMap[dateStr];
        if (isClosed) cell.classList.add('cal-closed');

        cell.innerHTML = `<div class="cal-daynum">${d}</div>${isClosed?`<div class="reason-badge" title="${closedMap[dateStr]}">${closedMap[dateStr]||'Closed'}</div>`:''}`;

        cell.addEventListener('click', () => onDateClick(dateStr, isClosed));
        calBody.appendChild(cell);
      }

      // next month filler to complete 6 rows if needed
      const totalCells = startDow + lastDay;
      const rem = (totalCells % 7) ? (7 - (totalCells % 7)) : 0;
      for (let i = 1; i <= rem; i++){
        const cell = document.createElement('div');
        cell.className = 'cal-cell cal-muted';
        cell.innerHTML = `<div class="cal-daynum">${i}</div>`;
        cell.style.cursor = 'default';
        calBody.appendChild(cell);
      }
    }

    async function onDateClick(dateStr, isClosed){
      // toggle closed/open
      if (isClosed) {
        if (!confirm(`Unmark ${dateStr} as closed?`)) return;
        try{
          await fetch('closed_days_remove.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({date: dateStr})});
          delete closedMap[dateStr];
          buildCalendar(viewYear, viewMonth);
        }catch(_){ alert('Failed to update'); }
      } else {
        // open reason modal
        reasonErr.classList.add('d-none'); reasonOk.classList.add('d-none');
        reasonDateEl.value = dateStr;
        reasonInputEl.value = '';
        reasonModal.show();
      }
    }

  reasonForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const dateStr = reasonDateEl.value;
  const reason  = reasonInputEl.value.trim();
  reasonSaveBtn.disabled = true;
  reasonSaveBtn.querySelector('.spinner-border')?.classList.remove('d-none');
  reasonSaveBtn.querySelector('.save-text')?.classList.add('d-none');
  try{
    const res = await fetch('closed_days_save.php',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({date:dateStr,reason})
    });
    const data = await res.json();
    if (data.ok){
      await loadClosedDays();           // <- reload from server
      reasonModal.hide();
      buildCalendar(viewYear, viewMonth);
    } else {
      reasonErr.textContent = data.error || 'Save failed';
      reasonErr.classList.remove('d-none');
    }
  }catch(_){
    reasonErr.textContent='Network error';
    reasonErr.classList.remove('d-none');
  }finally{
    reasonSaveBtn.disabled = false;
    reasonSaveBtn.querySelector('.spinner-border')?.classList.add('d-none');
    reasonSaveBtn.querySelector('.save-text')?.classList.remove('d-none');
  }
});

    prevBtn.addEventListener('click', () => {
      if (viewMonth === 0){ viewMonth = 11; viewYear -= 1; } else { viewMonth -= 1; }
      buildCalendar(viewYear, viewMonth);
    });
    nextBtn.addEventListener('click', () => {
      if (viewMonth === 11){ viewMonth = 0; viewYear += 1; } else { viewMonth += 1; }
      buildCalendar(viewYear, viewMonth);
    });
    todayBtn.addEventListener('click', () => {
      const now = new Date();
      viewYear = now.getFullYear();
      viewMonth = now.getMonth();
      buildCalendar(viewYear, viewMonth);
    });

    (async function init(){
      const now = new Date();
      viewYear = now.getFullYear();
      viewMonth = now.getMonth();
      try { await loadClosedDays(); } catch(_){}
      buildCalendar(viewYear, viewMonth);
    })();
  </script>
</body>
</html>