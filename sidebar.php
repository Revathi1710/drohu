<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);session_start();


// Redirect to signin.php if 'username' is not set
if (!isset($_SESSION['adminId'])) {
    header("Location: admin.php");
    exit();
}
?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>
:root {
    --primary-color: #4A90E2;
    --primary-hover: #3A7BC8;
    --primary-active: #E6F0F9;
    --primary-border: #4A90E2;
    --text-primary: #212529;
    --text-secondary:#afcbff;
    --bg-light: #F8F9FA;
    --bg-dark: #343A40;
    --sidebar-bg: #FFFFFF;
    --sidebar-text:#d1d1d1;
    --sidebar-active-bg: #E3F2FD;
    --sidebar-active-text: #1976D2;
    --sidebar-width: 264px;
    --sidebar-collapsed-width: 76px;
    --divider-color: #E9ECEF;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Plus Jakarta Sans', Inter, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important;
    background-color: var(--bg-light);
    color: var(--text-primary);
}

.main-container {
    min-height: 100vh;
    padding: 1.5rem;
    margin-left: var(--sidebar-width);
    transition: margin-left .25s ease;
}

/* Sidebar shell */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 1000;
    width: var(--sidebar-width);
    background:linear-gradient(135deg, rgb(55 55 55 / 95%) 0%, rgb(0 17 65 / 92%) 100%);
    color: var(--sidebar-text);
    border-right: 1px solid var(--divider-color);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    transition: width .25s ease;
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--divider-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sidebar-header h4 {
    margin: 0;
    font-weight: 800;
    letter-spacing: .2px;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
}

.sidebar-header .logo {
    font-weight: 900;
    font-size: 1.5rem;
    color: var(--primary-color);
}

.sidebar-toggle {
    margin-left: 10px;
    background: transparent;
    border: none;
    color: var(--sidebar-text);
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color .2s ease;
}

.sidebar-toggle:hover {
    background-color: var(--bg-light);
}

/* Sections */
.sidebar-nav {
    padding: 10px 14px;
    overflow-y: auto;
    height: calc(100% - 66px); /* 66px is header height */
}

.nav-section-title {
    color: var(--text-secondary);
    opacity: .9;
    padding: 10px 10px 4px;
    font-size: 11px;
    letter-spacing: .12em;
    text-transform: uppercase;
    font-weight: 600;
}

.nav-item {
    margin: 4px 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--sidebar-text);
    text-decoration: none;
    border-radius: var(--radius-sm);
    padding: 12px 14px !important;
    transition: all .2s ease;
}

.nav-icon {
    width: 20px;
    text-align: center;
    font-size: 16px;
    color: var(--sidebar-text);
}

.nav-text {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;margin-left:8px;
}

.nav-link:hover {
   
    color: var(--sidebar-active-text) !important;
}

.nav-link:hover .nav-icon {
    color: var(--sidebar-active-text);
}

.nav-link.active {
    background-color: var(--primary-active);
    color: var(--sidebar-active-text);
    box-shadow: none;
    position: relative;
    border: none;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 80%;
    background-color: var(--primary-color);
    border-radius: 0 4px 4px 0;
}

.nav-link.active .nav-icon {
    color: var(--sidebar-active-text);
}

/* Collapsed */
.sidebar.collapsed .sidebar-header h4 {
    display: none;
}

.sidebar.collapsed .nav-text {
    display: none;
}

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 12px 0 !important;
}

.sidebar.collapsed .nav-item a {
    text-align: center;
}

.sidebar.collapsed .nav-link.active::before {
    content: none;
}

/* Main content shift (use in pages wrapping this include) */
.main-content {
    margin-left: var(--sidebar-width);
    transition: margin-left .25s ease;
}

.main-content.expanded {
    margin-left: var(--sidebar-collapsed-width);
}

/* Mobile slide-in helpers */
.mobile-toggle {
    position: fixed;
    top: 14px;
    left: 14px;
    z-index: 1100;
    background: var(--sidebar-bg);
    color: var(--primary-color);
    border: none;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-md);
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    transition: background-color .2s ease;
}

.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    z-index: 999;
    display: none;
}

@media (max-width: 991.98px) {
    .mobile-toggle {
        display: flex;
    }

    .sidebar {
        transform: translateX(-100%);
        transition: transform .25s ease, width .25s ease;
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .sidebar-overlay.show {
        display: block;
    }

    .main-container {
        margin-left: 0;
    }
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: var(--divider-color);
    border-radius: 6px;
}

.main-content-div {
    margin-left: var(--sidebar-width);
    transition: margin-left .25s ease;
}

.main-content-div.expanded {
    margin-left: var(--sidebar-collapsed-width);
} /* Scrollbar Styling */
        /* Hide scrollbar for Webkit browsers (Chrome, Safari) */
/* Scrollbar styling */
.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

</style>

<button class="mobile-toggle d-lg-none" id="mobileToggle" aria-label="Open menu"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar" role="navigation" aria-label="Admin sidebar">
    <div class="sidebar-header">
        <h4 class="logo">Drohu</h4>
        <button class="sidebar-toggle d-none d-lg-flex" id="sidebarToggle" aria-label="Collapse sidebar"><i class="fas fa-chevron-left"></i></button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <div class="nav-item">
                <a href="adminDashboard.php" class="nav-link <?php echo ($current_page=='dashboard.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                    <i class="fas fa-house nav-icon"></i><span class="nav-text">Dashboard</span>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Customer Management</div>
            <div class="nav-item">
                <a href="addCustomer.php" class="nav-link <?php echo ($current_page=='addCustomer.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Add Customer">
                    <i class="fas fa-user-plus nav-icon"></i><span class="nav-text">Add Customer</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="allCustomer.php" class="nav-link <?php echo ($current_page=='allCustomer.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="All Customers">
                    <i class="fas fa-users nav-icon"></i><span class="nav-text">All Customers</span>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Delivery Person</div>
            <div class="nav-item">
                <a href="addDeliveryPerson.php" class="nav-link <?php echo ($current_page=='addDeliveryPerson.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Add Delivery Person">
                    <i class="fas fa-person-circle-plus nav-icon"></i><span class="nav-text">Add Delivery Person</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="allDeliveryPerson.php" class="nav-link <?php echo ($current_page=='allDeliveryPerson.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="All Delivery Persons">
                    <i class="fas fa-people-group nav-icon"></i><span class="nav-text">All Delivery Persons</span>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Product Management</div>
            <div class="nav-item">
                <a href="addProduct.php" class="nav-link <?php echo ($current_page=='addProduct.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Add Product">
                    <i class="fas fa-plus-circle nav-icon"></i><span class="nav-text">Add Product</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="allProduct.php" class="nav-link <?php echo ($current_page=='allProduct.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="All Products">
                    <i class="fas fa-boxes-stacked nav-icon"></i><span class="nav-text">All Products</span>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Orders</div>
            <div class="nav-item">
                <a href="allOrder.php" class="nav-link <?php echo ($current_page=='allOrder.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="All Orders">
                    <i class="fas fa-receipt nav-icon"></i><span class="nav-text">All Orders</span>
                </a>
            </div>
        </div>
<div class="nav-section">
    <div class="nav-section-title">Announcement</div>
    <div class="nav-item">
        <a href="closed_days.php" 
           class="nav-link <?php echo ($current_page=='closed_days.php')?'active':''; ?>" 
           data-bs-toggle="tooltip" 
           data-bs-placement="right" 
           title="Closed Days">
            <i class="fas fa-calendar-times nav-icon"></i>
            <span class="nav-text">Closed Days</span>
        </a>
    </div>
</div>


        <div class="nav-section">
            <div class="nav-item">
                <a href="broadcasts.php" class="nav-link <?php echo ($current_page=='broadcasts.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
                    <i class="fas fa-gear nav-icon"></i><span class="nav-text">Common Message</span>
                </a>
            </div>  <div class="nav-item">
                <a href="setting.php" class="nav-link <?php echo ($current_page=='broadcasts.php')?'active':''; ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
                    <i class="fas fa-gear nav-icon"></i><span class="nav-text">Settings</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="adminlogout.php" class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout">
                    <i class="fas fa-right-from-bracket nav-icon"></i><span class="nav-text">Logout</span>
                </a>
            </div>
        </div>
    </nav>
</aside>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-container');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    let tooltipInstances = [];

    function disposeTooltips() {
        tooltipInstances.forEach(t => t.dispose && t.dispose());
        tooltipInstances = [];
    }

    function initCollapsedTooltips() {
        disposeTooltips();
        if (!sidebar.classList.contains('collapsed')) return;
        document.querySelectorAll('.nav-link').forEach(a => {
            const label = a.getAttribute('title');
            if (label) {
                tooltipInstances.push(new bootstrap.Tooltip(a, {
                    placement: 'right',
                    title: label,
                    container: 'body'
                }));
            }
        });
    }

    // Desktop collapse
    sidebarToggle?.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        if (mainContent) {
            mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? 'var(--sidebar-collapsed-width)' : 'var(--sidebar-width)';
        }
        this.querySelector('i').className = sidebar.classList.contains('collapsed') ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        initCollapsedTooltips();
    });

    // Mobile slide-in
    mobileToggle?.addEventListener('click', function() {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
    });
    sidebarOverlay?.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });

    // Initial check and tooltip setup
    window.addEventListener('load', () => {
        if (window.innerWidth < 992) {
            if (mainContent) mainContent.style.marginLeft = '0';
        } else {
            if (mainContent) mainContent.style.marginLeft = 'var(--sidebar-width)';
        }
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth < 992) {
            if (mainContent) mainContent.style.marginLeft = '0';
            sidebar.classList.remove('collapsed');
            sidebarToggle?.querySelector('i').className = 'fas fa-chevron-left';
            disposeTooltips();
        } else {
            if (mainContent && !sidebar.classList.contains('collapsed')) {
                mainContent.style.marginLeft = 'var(--sidebar-width)';
            }
            if (sidebar.classList.contains('collapsed')) {
                initCollapsedTooltips();
            }
        }
    });

    // Initial tooltip setup on page load
    initCollapsedTooltips();
})();
</script>