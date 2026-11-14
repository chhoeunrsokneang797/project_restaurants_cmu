
<style>
    /* ── Sidebar ── */
    .sidebar {
      width: var(--sidebar-w);
      background: var(--bg2);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      position: fixed; top: 0; left: 0; bottom: 0;
      z-index: 100;
      transition: transform .25s ease;
    }
    /* Sidebar Logo */
    .sidebar-logo {
        padding: 15px 20px;
        height: var(--header-h);
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }

    /* Admin Image */
    .admin-logo {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    /* Text Content */
    .admin-info {
        display: flex;
        flex-direction: column;
    }

    /* Admin Name */
    .admin-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        line-height: 1.2;
    }

    /* Admin Role */
    .admin-role {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
    }
  
    /* .nav-section { padding: 18px 12px 6px; flex-shrink: 0; } */
    /* Sidebar Scroll */
      .sidebar {
          overflow-y: auto;
          scroll-behavior: smooth;
      }

      /* Custom Smooth Scrollbar */
      .sidebar::-webkit-scrollbar {
          width: 6px;
      }

      .sidebar::-webkit-scrollbar-track {
          background: transparent;
      }

      .sidebar::-webkit-scrollbar-thumb {
          background: rgba(255,255,255,0.15);
          border-radius: 20px;
          transition: 0.3s;
      }

      .sidebar::-webkit-scrollbar-thumb:hover {
          background: rgba(255,255,255,0.35);
      }

      /* Navigation Section */
      .nav-section {
          padding: 18px 12px 6px;
          flex-shrink: 0;
      }
    
    .nav-label { 
      font-size: 10px; font-weight: 600; letter-spacing: 1px; color: var(--text3); text-transform: uppercase; padding: 0 8px 8px; 
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 10px; border-radius: 8px;
      font-size: 13.5px; color: var(--text2);
      cursor: pointer; transition: all .15s; margin-bottom: 2px;
      text-decoration: none;
    }
    .nav-item:hover { background: var(--bg3); color: var(--text); }
    .nav-item.active { background: rgba(79,142,247,0.12); color: var(--accent); }
    .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .nav-badge { margin-left: auto; font-size: 10px; background: var(--accent); color: #fff; padding: 2px 6px; border-radius: 20px; font-weight: 600; }
    .nav-badge-amber { background: var(--amber); }
  
    /* .sidebar-footer {
      margin-top: auto;
      padding: 14px 12px;
      border-top: 1px solid var(--border);
    }
    .user-card {
      display: flex; align-items: center; gap: 10px;
      padding: 8px 10px; border-radius: 8px; cursor: pointer;
      transition: background .15s;
    }
    .user-card:hover { background: var(--bg3); } */
    /* Sidebar Footer */
      .sidebar-footer {
          padding: 16px;
          border-top: 1px solid var(--border);
          margin-top: auto;
      }

      /* User Card */
      .user-card {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px;
          background: rgba(255,255,255,0.04);
          border: 1px solid rgba(255,255,255,0.06);
          border-radius: 16px;
          transition: 0.3s ease;
          cursor: pointer;
      }

      .user-card:hover {
          background: rgba(255,255,255,0.08);
          transform: translateY(-2px);
      }

      /* User Avatar */
      .user-avatar {
          width: 48px;
          height: 48px;
          border-radius: 50%;
          object-fit: cover;
          border: 2px solid var(--accent);
          box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      }

      /* User Info */
      .user-info {
          display: flex;
          flex-direction: column;
      }

      /* User Name */
      .user-name {
          font-size: 15px;
          font-weight: 600;
          color: #fff;
          line-height: 1.2;
      }

      /* User Role */
      .user-role {
          font-size: 12px;
          color: #94a3b8;
          margin-top: 2px;
      }
      /* Mini User Avatar */
      .mini-user-avatar{
          width: 36px;
          height: 36px;
          border-radius: 50%;
          object-fit: cover;
          border: 2px solid var(--accent);
          transition: 0.3s ease;
      }

      .mini-user-avatar:hover{
          transform: scale(1.08);
          box-shadow: 0 4px 12px rgba(0,0,0,0.25);
      }
    .avatar { 
      width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--purple)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #fff; flex-shrink: 0; 
    }
    .user-name { 
      font-size: 13px;
      font-weight: 500; color: var(--text); 
    }
    .user-role { 
      font-size: 11px;
      color: var(--text3);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
    :root {
      --bg: #f4f7fb;
      --bg2: #ffffff;
      --bg3: #eef2f7;
      --bg4: #dfe6ef;

      --border: rgba(15, 23, 42, 0.08);
      --border2: rgba(15, 23, 42, 0.14);

      --text: #1e293b;
      --text2: #475569;
      --text3: #94a3b8;

      --accent: #3b82f6;
      --accent2: #2563eb;

      --green: #10b981;
      --red: #ef4444;
      --amber: #f59e0b;
      --purple: #8b5cf6;
      --teal: #14b8a6;

      --sidebar-w: 230px;
      --header-h: 62px;
      --radius: 10px;
      --radius-lg: 14px;

      --font: 'DM Sans', sans-serif;
      --mono: 'DM Mono', monospace;
    }
    
    html { font-size: 14px; }
    body {
      font-family: var(--font);
      background: linear-gradient(to bottom right, #f8fbff, #eef4ff);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      overflow-x: hidden;
    }
</style>

<?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    
    
?>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar">
  <!-- <div class="sidebar-logo">
    <div class="logo-mark">V</div>
    <span class="logo-text">Vanta</span>
  </div> -->
 
  <!-- Sidebar Logo -->
  <div class="sidebar-logo">

      <!-- Admin Image -->
      <img src="../img/logo-ang.png" alt="Admin" class="admin-logo">

      <!-- Admin Text -->
      <div class="admin-info">
          <span class="admin-name" style="font-size: 14px;"><?php echo $_SESSION['Username']; ?></span>
          <span class="admin-role">Manager Dashboard</span>
      </div>

  </div>
  <!-- ========================= -->
  <!-- MAIN MENU -->
  <!-- ========================= -->
  <div class="nav-section">

      <div class="nav-label">Main</div>

      <!-- Dashboard -->
      <a href="index.php" class="nav-item <?= ($current_page == 'index.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
          Dashboard
      </a>

      <!-- Employees -->
      <a href="employee.php" class="nav-item <?= ($current_page == 'employee.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87"/>
              <path d="M16 3.13a4 4 0 010 7.75"/>
          </svg>
          Employees
      </a>

      <!-- Customers -->
      <a href="customers.php" class="nav-item <?= ($current_page == 'customers.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87"/>
              <path d="M16 3.13a4 4 0 010 7.75"/>
          </svg>
          Customers
      </a>

      <!-- Table Booking -->
      <a href="tblbooking.php" class="nav-item <?= ($current_page == 'tblbooking.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M3 10h18"/>
              <path d="M5 6h14v12H5z"/>
              <path d="M8 14h8"/>
          </svg>
          Table Booking
          <!-- <span class="nav-badge">5</span> -->
      </a>

      <!-- Orders -->
      <a href="order.php" class="nav-item <?= ($current_page == 'order.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          Orders
          <!-- <span class="nav-badge"><?php  ?></span> -->
      </a>

      <!-- Orders Details -->
      <!-- <a href="orderdetail.php" class="nav-item <?= ($current_page == 'orderdetail.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          Order Details
      </a> -->

      <!-- Orders History -->
      <!-- <a href="history_order.php" class="nav-item <?= ($current_page == 'history_order.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          Orders History
      </a> -->

      <!-- Contact Us -->
      <!-- <a href="contact.php" class="nav-item <?= ($current_page == 'contact.php') ? 'active' : ''; ?>">
          <i class="fa-regular fa-envelope"></i>
          Contact
          
      </a> -->

      <!-- Payments -->
      <a href="payment.php" class="nav-item <?= ($current_page == 'payment.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <rect x="2" y="5" width="20" height="14" rx="2"/>
              <line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
          Payments
      </a>

  </div>

  <!-- ========================= -->
  <!-- MENU MANAGEMENT -->
  <!-- ========================= -->
  <div class="nav-section">

      <div class="nav-label">Menu Management</div>

      <!-- Categories -->
      <a href="categories.php" class="nav-item <?= ($current_page == 'categories.php') ? 'active' : ''; ?>" >
          <svg viewBox="0 0 24 24">
              <path d="M4 4h16v6H4z"/>
              <path d="M4 14h16v6H4z"/>
          </svg>
          Category
      </a>

      <!-- Food -->
      <a href="food.php" class="nav-item <?= ($current_page == 'food.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-bowl-food"></i>
          Food Menu
      </a>

      <!-- Drinks -->
      <a href="drink.php" class="nav-item <?= ($current_page == 'drink.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-martini-glass-empty"></i>
          Drinks
      </a>

      <!-- Dessert -->
      <a href="desserts.php" class="nav-item <?= ($current_page == 'desserts.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M4 10h16"/>
              <path d="M6 10v8h12v-8"/>
              <path d="M8 6h8"/>
          </svg>
          Desserts
      </a>

      <!-- All Products -->
      <a href="product.php" class="nav-item <?= ($current_page == 'product.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
          Products
      </a>

       <!-- Inventory -->
      <a href="inventory.php" class="nav-item <?= ($current_page == 'inventory.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7L12 3L21 7L12 11L3 7Z"/>
            <path d="M3 12L12 16L21 12"/>
            <path d="M3 17L12 21L21 17"/>
        </svg>
          Inventory
      </a>

  </div>

  <!-- ========================= -->
  <!-- REPORTS -->
  <!-- ========================= -->
  <div class="nav-section">

      <div class="nav-label">Reports</div>

      <!-- Sales Report -->
      <a href="sale_report.php" class="nav-item <?= ($current_page == 'sale_report.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <path d="M3 3v18h18"/>
              <path d="M7 14l4-4 3 3 5-6"/>
          </svg>
          Sales Report
          <!-- <span class="nav-badge nav-badge-amber">3</span> -->
      </a>

      <!-- Booking Report -->
      <a href="booking_report.php" class="nav-item <?= ($current_page == 'booking_report.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
          </svg>
          Booking Report
      </a>

      <!-- Customer Report -->
      <!-- <a href="customer_report.php" class="nav-item <?= ($current_page == 'customer_report.php') ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24">
              <circle cx="9" cy="7" r="4"/>
              <path d="M17 11l2 2 4-4"/>
              <path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/>
          </svg>
          Customer Report
      </a> -->

  </div>

  <!-- ========================= -->
  <!-- SETTINGS -->
  <!-- ========================= -->
  <div class="nav-section">

      <div class="nav-label">System</div>

      <!-- Settings -->
      <!-- <a href="settings.php" class="nav-item">
          <svg viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
          Settings
      </a> -->

      <!-- Logout -->
      <a href="logout.php" class="nav-item">
          <svg viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Logout
      </a>

  </div>
 
  <div class="sidebar-footer">
    <div class="user-card">
      <!-- <div class="avatar">AK</div> -->
       <!-- <img src="../img/logo-ang.png" alt="Admin" class="user-avatar"> -->
      <div>
        <!-- User Info -->
        <!-- <div class="user-info">
            <div class="user-name">Admin</div>
            <div class="user-role">Restaurant Manager</div>
        </div> -->
      </div>
    </div>
  </div>
</aside>