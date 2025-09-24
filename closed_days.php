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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern & Professional Color Palette */
        :root {
            --bg-light: #F8F9FA;
            --card-bg: #FFFFFF;
            --border-color: #E2E8F0;
            --text-primary: #1A202C;
            --text-secondary: #6B7280;

            --closed-bg: #FEF2F2;
            --closed-border: #FCA5A5;
            --closed-text: #B91C1C;

            --today-bg: #E0F7FA;
            --today-border: #00BCD4;
            --today-text: #00796B;

            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.05);
            --radius-md: 14px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
        }

        .main-container { padding: 2rem; margin-left: var(--sidebar-width); }
        
        .cal-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        .cal-head {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cal-title { font-weight: 700; margin: 0; font-size: 1.5rem; }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        
        .cal-dow {
            padding: 0.75rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        .cal-cell {
            min-height: 120px;
            padding: 1rem;
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            position: relative;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        
        .cal-cell:hover {
            background-color: #F8FAFC;
        }
        
        .cal-cell:active {
            transform: scale(0.98);
        }

        .cal-cell:nth-child(7n) { border-right: none; }
        
        .cal-daynum { font-weight: 700; font-size: 1rem; color: var(--text-primary); }
        .cal-muted { opacity: 0.45; cursor: default !important; }
        .cal-muted:hover { background-color: transparent !important; }

        /* Closed Day Styling */
        .cal-closed {
            background-color: var(--closed-bg);
        }
        .cal-closed:hover { background-color: var(--closed-bg); }
        .cal-closed .cal-daynum { color: var(--closed-text); }
        
        /* Today Styling */
        .cal-today {
            outline: 2px solid var(--today-border);
            outline-offset: -2px;
            background: var(--today-bg);
        }
        .cal-today .cal-daynum { color: var(--today-text); }

        .reason-badge {
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 0.7rem;
            color: var(--text-primary);
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 500;
        }

        .legend { display: flex; gap: 1rem; align-items: center; }
        .legend > span { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-secondary); }
        .legend-dot { width: 14px; height: 14px; border-radius: 4px; border: 1px solid var(--border-color); }
        .legend-dot.closed { background: var(--closed-bg); border-color: var(--closed-border); }
        .legend-dot.today { background: var(--today-bg); border-color: var(--today-border); }

        @media (max-width: 576px) {
            .cal-cell { min-height: 90px; }
            .reason-badge { display: none; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="cal-card">
            <div class="cal-head">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="prevBtn">&laquo;</button>
                    <h4 class="cal-title mb-0" id="calTitle">Month YYYY</h4>
                    <button class="btn btn-outline-secondary btn-sm" id="nextBtn">&raquo;</button>
                    <button class="btn btn-outline-primary btn-sm" id="todayBtn">Today</button>
                </div>
                <div class="legend">
                    <span><span class="legend-dot closed"></span> Closed</span>
                    <span><span class="legend-dot today"></span> Today</span>
                </div>
            </div>

            <div class="cal-grid" id="calDow">
                <div class="cal-dow">Sun</div><div class="cal-dow">Mon</div><div class="cal-dow">Tue</div>
                <div class="cal-dow">Wed</div><div class="cal-dow">Thu</div><div class="cal-dow">Fri</div><div class="cal-dow">Sat</div>
            </div>
            <div class="cal-grid" id="calBody"></div>
        </div>
    </div>

    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="reasonForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Day as Closed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Date</label>
                            <input type="text" class="form-control" id="reasonDate" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Reason (e.g., Public Holiday)</label>
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
        function toYMD(y, m, day){
            const mm = String(m + 1).padStart(2, '0');
            const dd = String(day).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        }
        function monthName(m){ return ['January','February','March','April','May','June','July','August','September','October','November','December'][m]; }

        function setClosedMap(days){
            closedMap = Object.create(null);
            (days || []).forEach(d => { if (d.date) closedMap[d.date] = d.reason || ''; });
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
            const lastDay = new Date(y, m + 1, 0).getDate(); // days in month

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
                const isToday = (y === tY && m === tM && d === tD);
                if (isToday) cell.classList.add('cal-today');

                const dateStr = toYMD(y, m, d);
                const isClosed = !!closedMap[dateStr];
                if (isClosed) cell.classList.add('cal-closed');

                cell.innerHTML = `<div class="cal-daynum">${d}</div>${isClosed ? `<div class="reason-badge">${closedMap[dateStr] || 'Closed'}</div>` : ''}`;

                cell.addEventListener('click', () => onDateClick(dateStr, isClosed));
                calBody.appendChild(cell);
            }

            // next month filler
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
            if (isClosed) {
                if (!confirm(`Unmark ${dateStr} as closed?`)) return;
                try{
                    await fetch('closed_days_remove.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({date: dateStr})});
                    delete closedMap[dateStr];
                    buildCalendar(viewYear, viewMonth);
                } catch(_) {
                    alert('Failed to update');
                }
            } else {
                reasonErr.classList.add('d-none');
                reasonOk.classList.add('d-none');
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
            try {
                const res = await fetch('closed_days_save.php',{
                    method:'POST', headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({date: dateStr, reason})
                });
                const data = await res.json();
                if (data.ok){
                    await loadClosedDays();
                    reasonModal.hide();
                    buildCalendar(viewYear, viewMonth);
                } else {
                    reasonErr.textContent = data.error || 'Save failed';
                    reasonErr.classList.remove('d-none');
                }
            } catch(_) {
                reasonErr.textContent = 'Network error';
                reasonErr.classList.remove('d-none');
            } finally {
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