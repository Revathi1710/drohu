<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Assets (safe to keep once per layout) -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>
:root{
	--brand-a:#7a1fa2;
	--brand-b:#b42acb;
	--ink:#0b1020;
	--muted:#6b7280;
	--surface:#ffffff;
	--divider:#eef2f7;
	--hover:rgba(122,31,162,.08);
	--active:rgba(122,31,162,.18);
	--sidebar-width: 264px;
	--sidebar-collapsed-width: 76px;
}
*{box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',Inter,-apple-system,'Segoe UI',Roboto,Arial,sans-serif !important;color:var(--ink)}
/* Sidebar shell */
.sidebar{position:fixed;top:0;left:0;bottom:0;z-index:1000;width:var(--sidebar-width);
	background:linear-gradient(135deg, rgb(31 38 162 / 95%) 0%, rgb(42 84 203 / 92%) 100%);
	color:#fff;border-right:1px solid rgba(255,255,255,.22);box-shadow:0 10px 30px rgba(0,0,0,.12);overflow:auto;transition:width .25s ease;}
.sidebar.collapsed{width:var(--sidebar-collapsed-width)}
.sidebar-header{padding:16px 14px;border-bottom:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:space-between}
.sidebar-header h4{margin:0;font-weight:800;letter-spacing:.2px;color:#fff;white-space:nowrap}
.sidebar-toggle{margin-left:10px;background:#fff;border:none;color:#7a1fa2;width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(0,0,0,.18)}
/* Sections */
.sidebar-nav{padding:10px 6px}
.nav-section-title{color:#e7dbf2;opacity:.9;padding:8px 10px;font-size:11px;letter-spacing:.12em;text-transform:uppercase}
.nav-item{margin:6px 4px}
.nav-link{display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;border-radius:10px;padding:10px 12px !important}
.nav-icon{width:22px;text-align:center;font-size:16px;opacity:.95}
.nav-text{font-weight:700;letter-spacing:.2px;white-space:nowrap}
.nav-link:hover{background:var(--hover);transform:translateX(4px)}
.nav-link.active{background:var(--active);box-shadow:inset 0 0 0 1px rgba(255,255,255,.25)}
/* Collapsed */
.sidebar.collapsed .nav-link{justify-content:center;padding:10px 0 !important}
.sidebar.collapsed .nav-text{display:none}
/* Main content shift (use in pages wrapping this include) */
.main-content{margin-left:var(--sidebar-width);transition:margin-left .25s ease}
.main-content.expanded{margin-left:var(--sidebar-collapsed-width)}
/* Mobile slide-in helpers */
.mobile-toggle{position:fixed;top:14px;left:14px;z-index:1100;background:#fff;color:#7a1fa2;border:none;width:44px;height:44px;border-radius:12px;display:none;align-items:center;justify-content:center;box-shadow:0 8px 20px rgba(0,0,0,.15)}
.sidebar-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;display:none}
@media (max-width: 991.98px){
	.mobile-toggle{display:flex}
	.sidebar{transform:translateX(-100%);transition:transform .25s ease, width .25s ease}
	.sidebar.show{transform:translateX(0)}
	.main-content{margin-left:0}
}
.sidebar::-webkit-scrollbar{width:6px}
.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.28);border-radius:6px}
.main-content-div{
    margin-left:280px;
}
</style>

<!-- Mobile Toggle + Overlay -->
<button class="mobile-toggle d-lg-none" id="mobileToggle" aria-label="Open menu"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Admin sidebar">
	<div class="sidebar-header">
		<h4>Water</h4>
		<button class="sidebar-toggle d-none d-lg-flex" id="sidebarToggle" aria-label="Collapse sidebar"><i class="fas fa-chevron-left"></i></button>
	</div>

	<nav class="sidebar-nav">
		<div class="nav-section">
			<div class="nav-section-title">Main</div>
			<div class="nav-item">
				<a href="dashboard.php" class="nav-link <?php echo ($current_page=='dashboard.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-house nav-icon"></i><span class="nav-text">Dashboard</span>
				</a>
			</div>
		</div>

		<div class="nav-section">
			<div class="nav-section-title">Customer Management</div>
			<div class="nav-item">
				<a href="addCustomer.php" class="nav-link <?php echo ($current_page=='addCustomer.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-user-plus nav-icon"></i><span class="nav-text">Add Customer</span>
				</a>
			</div>
			<div class="nav-item">
				<a href="allCustomer.php" class="nav-link <?php echo ($current_page=='allCustomer.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-users nav-icon"></i><span class="nav-text">All Customer</span>
				</a>
			</div>
		</div>

		<div class="nav-section">
			<div class="nav-section-title">Delivery Person</div>
			<div class="nav-item">
				<a href="addDeliveryPerson.php" class="nav-link <?php echo ($current_page=='addDeliveryPerson.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-person-circle-plus nav-icon"></i><span class="nav-text">Add Delivery Person</span>
				</a>
			</div>
			<div class="nav-item">
				<a href="allDeliveryPerson.php" class="nav-link <?php echo ($current_page=='allDeliveryPerson.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-people-group nav-icon"></i><span class="nav-text">All Delivery Person</span>
				</a>
			</div>
		</div>

		<div class="nav-section">
			<div class="nav-section-title">Product Management</div>
			<div class="nav-item">
				<a href="addProduct.php" class="nav-link <?php echo ($current_page=='addProduct.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-plus-circle nav-icon"></i><span class="nav-text">Add Product</span>
				</a>
			</div>
			<div class="nav-item">
				<a href="allProduct.php" class="nav-link <?php echo ($current_page=='allProduct.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-boxes-stacked nav-icon"></i><span class="nav-text">All Product</span>
				</a>
			</div>
		</div>

		<div class="nav-section">
			<div class="nav-section-title">Orders</div>
			<div class="nav-item">
				<a href="allOrder.php" class="nav-link <?php echo ($current_page=='allOrder.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-receipt nav-icon"></i><span class="nav-text">All Orders</span>
				</a>
			</div>
		</div>

		<div class="nav-section">
			<div class="nav-item">
				<a href="setting.php" class="nav-link <?php echo ($current_page=='setting.php')?'active':''; ?>" data-bs-placement="right">
					<i class="fas fa-gear nav-icon"></i><span class="nav-text">Settings</span>
				</a>
			</div>
			<div class="nav-item">
				<a href="logout.php" class="nav-link" data-bs-placement="right">
					<i class="fas fa-right-from-bracket nav-icon"></i><span class="nav-text">Logout</span>
				</a>
			</div>
		</div>
	</nav>
</aside>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
	const sidebar = document.getElementById('sidebar');
	const mainContent = document.querySelector('.main-content');
	const sidebarToggle = document.getElementById('sidebarToggle');
	const mobileToggle = document.getElementById('mobileToggle');
	const sidebarOverlay = document.getElementById('sidebarOverlay');
	let tooltipInstances = [];

	function disposeTooltips(){
		tooltipInstances.forEach(t => t.dispose && t.dispose());
		tooltipInstances = [];
		document.querySelectorAll('.nav-link').forEach(a=>{
			a.removeAttribute('data-bs-toggle'); a.removeAttribute('title');
		});
	}

	function initCollapsedTooltips(){
		disposeTooltips();
		if (!sidebar.classList.contains('collapsed')) return;
		document.querySelectorAll('.nav-link').forEach(a=>{
			const label = a.querySelector('.nav-text')?.textContent?.trim() || '';
			a.setAttribute('data-bs-toggle','tooltip');
			a.setAttribute('title', label);
			tooltipInstances.push(new bootstrap.Tooltip(a, { placement:'right', container:'body' }));
		});
	}

	// Desktop collapse
	sidebarToggle?.addEventListener('click', function(){
		sidebar.classList.toggle('collapsed');
		mainContent?.classList.toggle('expanded');
		this.querySelector('i').className = sidebar.classList.contains('collapsed') ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
		initCollapsedTooltips();
	});

	// Mobile slide-in
	mobileToggle?.addEventListener('click', function(){
		sidebar.classList.add('show'); sidebarOverlay.classList.add('show');
	});
	sidebarOverlay?.addEventListener('click', function(){
		sidebar.classList.remove('show'); sidebarOverlay.classList.remove('show');
	});

	// Init on load
	initCollapsedTooltips();
})();
</script>