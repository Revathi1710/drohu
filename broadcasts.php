<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Message</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3e82f7; /* Zoho-like blue */
            --border-color: #e0e6ed;
            --background-color: #f5f7f9;
            --text-color: #495057;
            --heading-color: #212529;
            --font-family: 'Inter', sans-serif;
            --sidebar-width: 280px; /* Assuming this value comes from sidebar.php */
        }
        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .main-container {
            padding: 24px;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .form-control, .btn {
            border-radius: 6px;
        }
        .form-control {
            border: 1px solid var(--border-color);
            padding: 12px;
            min-height: 100px;
            resize: vertical;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(62, 130, 247, 0.25);
        }
        .btn-success {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 24px;
            font-weight: 500;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .btn-success:hover {
            background-color: #2b6ce6;
            border-color: #2b6ce6;
        }
        .btn-success:focus {
            box-shadow: 0 0 0 0.25rem rgba(62, 130, 247, 0.25);
        }
        h5 {
            color: var(--heading-color);
            font-weight: 600;
        }
        .spinner-border-sm {
            width: 1.25rem;
            height: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="card p-4">
            <h5 class="mb-4">Broadcast to Users</h5>
            <form id="bcForm">
                <div class="mb-3">
                    <textarea id="msg" class="form-control" rows="5" placeholder="Your message"></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-success" id="sendBtn">
                        <span class="save-text">Send</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
                <div id="bcErr" class="text-danger small d-none mt-3"></div>
                <div id="bcOk" class="text-success small d-none mt-3"></div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const bcErr = document.getElementById('bcErr');
        const bcOk  = document.getElementById('bcOk');
        const sendBtn = document.getElementById('sendBtn');
        const msgInput = document.getElementById('msg');
    
        function toastErr(m) {
            bcErr.textContent = m;
            bcErr.classList.remove('d-none');
            setTimeout(() => bcErr.classList.add('d-none'), 3000);
        }
        function toastOk(m) {
            bcOk.textContent = m;
            bcOk.classList.remove('d-none');
            setTimeout(() => bcOk.classList.add('d-none'), 3000);
        }
    
        document.getElementById('bcForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            bcErr.classList.add('d-none');
            bcOk.classList.add('d-none');
            const channel = "WHATSAPP";
            const message = msgInput.value.trim();
            if (!message) {
                toastErr('Enter a message to broadcast.');
                return;
            }
            sendBtn.disabled = true;
            sendBtn.querySelector('.spinner-border').classList.remove('d-none');
            sendBtn.querySelector('.save-text').classList.add('d-none');
            try {
                const res = await fetch('broadcast_send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ channel, message })
                });
                const d = await res.json();
                if (d.ok) {
                    toastOk('Sent to ' + (d.sent_count || 0) + ' users.');
                    msgInput.value = '';
                } else {
                    toastErr(d.error || 'Failed to send broadcast.');
                }
            } catch (_) {
                toastErr('Network error. Please try again.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.querySelector('.spinner-border').classList.add('d-none');
                sendBtn.querySelector('.save-text').classList.remove('d-none');
            }
        });
    </script>
</body>
</html>