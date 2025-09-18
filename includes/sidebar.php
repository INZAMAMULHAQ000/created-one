<?php
// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="Sun.jpeg" alt="Company Logo" style="height: 60px; margin-bottom: 10px;">
        <h5 class="main-text">Madhu PaperBags</h5>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-section">Main Modules</li>
        <li><a href="billing.php" <?php echo ($current_page == 'billing.php') ? 'class="active"' : ''; ?>>📄 Billing</a></li>
        <li><a href="purchase_order.php" <?php echo ($current_page == 'purchase_order.php') ? 'class="active"' : ''; ?>>📋 Purchase Order</a></li>
        <li><a href="sales_quotation.php" <?php echo ($current_page == 'sales_quotation.php') ? 'class="active"' : ''; ?>>📦 Quotation</a></li>
        <li><a href="daily_expenses.php" <?php echo ($current_page == 'daily_expenses.php') ? 'class="active"' : ''; ?>>💸 Daily Expenses</a></li>
        <li><a href="#" onclick="alert('Coming Soon!')">🚚 Delivery Challan</a></li>
        <li><a href="overall_profit.php" <?php echo ($current_page == 'overall_profit.php') ? 'class="active"' : ''; ?>>📊 Reports</a></li>
        
        <li class="menu-section">History & Records</li>
        <li><a href="customer_history.php" <?php echo ($current_page == 'customer_history.php') ? 'class="active"' : ''; ?>>📋 Invoice History</a></li>
        <li><a href="purchase_order_history.php" <?php echo ($current_page == 'purchase_order_history.php') ? 'class="active"' : ''; ?>>📄 PO History</a></li>
        <li><a href="quotation_history.php" <?php echo ($current_page == 'quotation_history.php') ? 'class="active"' : ''; ?>>📦 Quotation History</a></li>
        <li><a href="expense_history.php" <?php echo ($current_page == 'expense_history.php') ? 'class="active"' : ''; ?>>💸 Expense History</a></li>
        
        <li class="menu-section">Management</li>
        <li><a href="customers.php" <?php echo ($current_page == 'customers.php') ? 'class="active"' : ''; ?>>👥 Customer Master</a></li>
        <li><a href="suppliers.php" <?php echo ($current_page == 'suppliers.php') ? 'class="active"' : ''; ?>>🏪 Supplier Master</a></li>
        <li><a href="materials.php" <?php echo ($current_page == 'materials.php') ? 'class="active"' : ''; ?>>🏗️ Materials</a></li>
        <li><a href="transport.php" <?php echo ($current_page == 'transport.php') ? 'class="active"' : ''; ?>>🚛 Transport</a></li>
        
        <li class="menu-section">Settings</li>
        <li><a href="change_password.php" <?php echo ($current_page == 'change_password.php') ? 'class="active"' : ''; ?>>🔒 Change Password</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <button class="sidebar-toggle" id="sidebarToggle">
            ☰
        </button>
        <a class="navbar-brand main-text" href="#">
            <img src="Sun.jpeg" alt="Company Logo" style="height: 50px; margin-right: 10px; vertical-align: middle;">
            Madhu PaperBags
        </a>
        <div class="ms-auto">
            <button id="themeToggle" class="btn btn-secondary">Toggle Theme</button>
        </div>
    </div>
</nav>
