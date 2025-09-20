<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Broadcast Message</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="main-container" style="padding:16px; margin-left:var(--sidebar-width);">
    <div class="card p-3">
      <h5 class="mb-3">Broadcast to Users</h5>
      <form id="bcForm">
        <div class="row g-2">
          <div class="col-12 col-md-3">
            <select id="channel" class="form-select">
              <option value="sms">SMS</option>
              <option value="whatsapp">WhatsApp</option>
            </select>
          </div>
          <div class="col-12 col-md-9">
            <textarea id="msg" class="form-control" rows="3" placeholder="Your message"></textarea>
          </div>
        </div>
        <div class="mt-2 d-flex justify-content-end">
          <button class="btn btn-success" id="sendBtn">
            <span class="save-text">Send</span>
            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>
        <div id="bcErr" class="text-danger small d-none mt-2"></div>
        <div id="bcOk" class="text-success small d-none mt-2"></div>
      </form>
    </div>
  </div>

  <script>
    const bcErr = document.getElementById('bcErr');
    const bcOk  = document.getElementById('bcOk');
    const sendBtn = document.getElementById('sendBtn');

    function toastErr(m){ bcErr.textContent=m; bcErr.classList.remove('d-none'); }
    function toastOk(m){ bcOk.textContent=m; bcOk.classList.remove('d-none'); setTimeout(()=>bcOk.classList.add('d-none'),1500); }

    document.getElementById('bcForm').addEventListener('submit', async (e)=>{
      e.preventDefault(); bcErr.classList.add('d-none'); bcOk.classList.add('d-none');
      const channel = document.getElementById('channel').value;
      const message = document.getElementById('msg').value.trim();
      if(!message){ toastErr('Enter a message'); return; }
      sendBtn.disabled=true; sendBtn.querySelector('.spinner-border').classList.remove('d-none'); sendBtn.querySelector('.save-text').classList.add('d-none');
      try{
        const res = await fetch('broadcast_send.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({channel,message})});
        const d = await res.json();
        if(d.ok){ toastOk('Sent to '+(d.sent_count||0)+' users'); document.getElementById('msg').value=''; } else { toastErr(d.error||'Failed'); }
      }catch(_){ toastErr('Network error'); }
      finally{ sendBtn.disabled=false; sendBtn.querySelector('.spinner-border').classList.add('d-none'); sendBtn.querySelector('.save-text').classList.remove('d-none'); }
    });
  </script>
</body>
</html>