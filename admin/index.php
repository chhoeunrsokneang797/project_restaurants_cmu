<?php include 'include/cn.php';


if (!isset($_SESSION['RoleID']) || $_SESSION['RoleID'] != '2') {
  header("Location: login.php");
  exit;
}

$monthKeys = [];
for ($i = 11; $i >= 0; $i--) {
  $monthKeys[] = date('Y-m', strtotime("-$i months"));
}

$sql = "
SELECT
    DATE_FORMAT(OrderDate, '%Y-%m') AS YearMonth,
    SUM(GrandTotal) AS Revenue,
    COUNT(OrderID) AS TotalOrders
FROM Orders
WHERE OrderDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY YearMonth
";

$result = mysqli_query($myCon, $sql);

$dataByMonth = [];
while ($row = mysqli_fetch_assoc($result)) {
  $dataByMonth[$row['YearMonth']] = [
    'revenue' => (float)$row['Revenue'],
    'orders'  => (int)$row['TotalOrders'],
  ];
}

$months   = [];
$revenues = [];
$orders   = [];

foreach ($monthKeys as $key) {
  $months[]   = date('M', strtotime($key . '-01'));
  $revenues[] = $dataByMonth[$key]['revenue'] ?? 0;
  $orders[]   = $dataByMonth[$key]['orders'] ?? 0;
}

$hasTrendData = array_sum($orders) > 0;



$sql = "
SELECT
    OrderType,
    COUNT(*) AS TotalOrders
FROM Orders
WHERE DATE(OrderDate) = CURDATE()
GROUP BY OrderType
";

$result = mysqli_query($myCon, $sql);

$orderTypes = [];
$orderCounts = [];

while ($row = mysqli_fetch_assoc($result)) {
  $orderTypes[] = $row['OrderType'];
  $orderCounts[] = $row['TotalOrders'];
}


$sql = "
SELECT
    OrderStatus,
    COUNT(*) AS Total
FROM Orders
WHERE DATE(OrderDate) = CURDATE()
GROUP BY OrderStatus
";

$result = mysqli_query($myCon, $sql);

$funnel = [];

while ($row = mysqli_fetch_assoc($result)) {
  $funnel[$row['OrderStatus']] = $row['Total'];
}

$max = max($funnel ?: [1]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../img/logo-ang.png">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/dist/css/index.css">
  <style>
    .font-bayon {
      font-family: 'Bayon', sans-serif;
    }

    .font-kantumruy {
      font-family: 'Kantumruy Pro', sans-serif;
    }
  </style>
</head>

<body>
  <?php include 'include/adminsbd.php'; ?>
  <?php include 'include/adminhd.php'; ?>

  <!-- <pre>
  Months:
  <?php print_r($months); ?>

  Revenue:
  <?php print_r($revenues); ?>

  Orders:
  <?php print_r($orders); ?>
  </pre> -->

  <!-- Content -->
  <div class="content">
    <!-- ═══ PAGE: DASHBOARD ═══ -->
    <div class="page active" id="page-dashboard">
      <div class="section-header">
        <div>
          <div class="section-title">Overview</div>
          <div class="section-sub">Tracking all key metrics for May 2026</div>
        </div>
        <div style="display:flex;gap:8px;">
          <button class="btn btn-ghost">
            <svg viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export
          </button>
          <button class="btn btn-primary">
            <svg viewBox="0 0 24 24">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            New Report
          </button>
        </div>
      </div>

      <!-- Metrics -->
      <div class="metrics-grid">
        <div class="metric-card mc-blue font-kantumruy">
          <div class="mc-label">
            <div class="mc-icon mc-icon-blue"><svg viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
              </svg></div>
            Total Revenue
          </div>
          <?php
          $sql = "SELECT IFNULL(SUM(GrandTotal),0) AS TotalRevenue
                    FROM Orders
                    WHERE PaymentStatus='Paid' AND DATE(OrderDate) = CURDATE()";
          $result = mysqli_query($myCon, $sql);
          $row = mysqli_fetch_assoc($result);
          ?>
          <div class="mc-value">$ <?= number_format($row['TotalRevenue'], 2) ?></div>
        </div>
        <div class="metric-card mc-green font-kantumruy">
          <div class="mc-label">
            <div class="mc-icon mc-icon-green"><svg viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                <line x1="3" y1="6" x2="21" y2="6" />
                <path d="M16 10a4 4 0 01-8 0" />
              </svg></div>
            Total Orders
          </div>
          <?php
          $sql = "SELECT COUNT(*) AS TotalOrders FROM Orders WHERE DATE(OrderDate) = CURDATE()";
          $result = mysqli_query($myCon, $sql);
          $row = mysqli_fetch_assoc($result);
          ?>
          <div class="mc-value"><?= $row['TotalOrders'] ?> Order</div>
        </div>

        <div class="metric-card mc-purple font-kantumruy">
          <div class="mc-label">
            <div class="mc-icon mc-icon-purple"><svg viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                <path d="M16 3.13a4 4 0 010 7.75" />
              </svg></div>
            Customers
          </div>
          <?php
          $sql = "SELECT COUNT(DISTINCT CustomerID) AS TotalCustomers
                    FROM Orders WHERE DATE(OrderDate) = CURDATE()
                    AND CustomerID IS NOT NULL";

          $result = mysqli_query($myCon, $sql);
          $row = mysqli_fetch_assoc($result);
          ?>
          <div class="mc-value"><?= $row['TotalCustomers'] ?> Customer</div>
        </div>

        <div class="metric-card mc-amber font-kantumruy">
          <div class="mc-label">
            <div class="mc-icon mc-icon-amber"><svg viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
              </svg></div>
            Available Tables
          </div>
          <?php
          $sql = "
            SELECT COUNT(*) AS AvailableTables
            FROM RestaurantTables
            WHERE Status='Available'
            ";

          $result = mysqli_query($myCon, $sql);
          $row = mysqli_fetch_assoc($result);
          ?>
          <div class="mc-value"><?= $row['AvailableTables'] ?> Table</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-grid">
        <div class="chart-card chart-card-wide">
          <div class="chart-header">
            <div>
              <div class="chart-title">Revenue & Orders — 12 months</div>
              <div class="chart-sub">Monthly performance trend</div>
            </div>
          </div>
          <div style="position:relative;width:100%;height:240px;">
            <canvas id="mainChart"></canvas>
          </div>
        </div>

        <div class="chart-card">
          <div class="chart-header">
            <div>
              <div class="chart-title">Sales by Order Type</div>
              <div class="chart-sub">Order distribution this month</div>
            </div>
          </div>
          <div style="position:relative;width:100%;height:220px;">
            <!-- <canvas id="channelChart"
                    role="img"
                    aria-label="Doughnut chart: Dine In 45%, Take Away 30%, Online 5%">
            </canvas> -->
            <canvas id="channelChart"></canvas>
            <!-- <canvas id="channelChart" role="img" aria-label="Doughnut chart: Online 48%, Retail 28%, Wholesale 14%, Partner 10%">Online 48% Retail 28% Wholesale 14% Partner 10%</canvas> -->
          </div>
          <div class="legend-row" style="justify-content:center;margin-top:12px;">
            <?php
            $chartColors = ['#4f8ef7', '#22c97a', '#f5a623', '#9b7ffa', '#2dd4c0', '#ef4444'];
            $totalOrdersToday = array_sum($orderCounts);

            if (empty($orderTypes) || $totalOrdersToday === 0):
            ?>
              <div class="legend-item">
                <div class="legend-dot" style="background: #dfe6ef"></div>
                No orders yet today
              </div>
              <?php
            else:
              foreach ($orderTypes as $i => $type):
                $count = $orderCounts[$i];
                $pct = $totalOrdersToday > 0 ? round(($count / $totalOrdersToday) * 100) : 0;
                $color = $chartColors[$i % count($chartColors)];
              ?>
                <div class="legend-item">
                  <div class="legend-dot" style="background:<?= $color ?>"></div>
                  <?= htmlspecialchars($type) ?> <?= $pct ?>%
                </div>
            <?php
              endforeach;
            endif;
            ?>

            <!-- <div class="legend-item"><div class="legend-dot" style="background:#9b7ffa"></div>Partner 10%</div> -->
          </div>
        </div>

        <div class="chart-card">
          <div class="chart-header">
            <div>
              <div class="chart-title">Order Status Funnel</div>
              <div class="chart-sub">Current order processing stages</div>
            </div>
          </div>
          <div style="padding: 8px 0;">
            <?php if (empty($funnel)): ?>
              <div class="funnel-step" style="justify-content:center; color:var(--text3);">
                No orders yet today
              </div>
            <?php else: ?>
              <?php foreach ($funnel as $status => $total): $percent = ($total / $max) * 100; ?>
                <div class="funnel-step">
                  <div class="funnel-name"> <?= $status !== '' ? htmlspecialchars($status) : 'Unknown' ?></div>
                  <!-- New Order -->
                  <div class="funnel-bar-wrap">
                    <div
                      class="funnel-fill"
                      style="width:<?= $percent ?>%; background:#dfe6ef;">
                    </div>
                    <!-- <div class="funnel-fill" style="width:100%;background:var(--accent)"></div> -->
                  </div>
                  <div class="funnel-val"><?= number_format($total) ?></div>
                  <div class="funnel-pct"><?= round($percent) ?>%</div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="table-card">
        <!-- Top Selling Menu -->
        <div class="table-card-header">
          <div>
            <div class="chart-title">Recent Orders</div>
            <div class="chart-sub">Last 10 transactions</div>
          </div>
          <a href="order.php" class="btn btn-ghost" onclick="showPage('orders', document.querySelector('.nav-item:nth-child(3)'))">View all →</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Payment</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "
              SELECT
                  o.OrderID,
                  customer_name,
                  o.OrderType,
                  o.PaymentStatus,
                  o.OrderDate,
                  o.GrandTotal,
                  o.OrderStatus
              FROM Orders o
              LEFT JOIN Customers c
                  ON o.CustomerID = c.CustomerID
              WHERE DATE(o.OrderDate) = CURDATE()
              ORDER BY o.OrderDate DESC
              LIMIT 10
              ";

            $result = mysqli_query($myCon, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
            ?>
              <tr class="font-kantumruy">
                <td class="td-main">#ORD-<?= $row['OrderID'] ?></td>
                <td class="td-main"><?= $row['customer_name'] ?? 'Walk-in Customer' ?></td>
                <td class="td-main">
                  <?= $row['OrderType'] ?>
                </td>
                <td class="font-kantumruy">
                  <?php if ($row['PaymentStatus'] == 'Paid') { ?>
                    <span class="status st-green">Paid</span>
                  <?php } else { ?>
                    <span class="status st-red">Unpaid</span>
                  <?php } ?>
                </td>
                <td><?= date('d / M / Y h:i A', strtotime($row['OrderDate'])) ?></td>
                <td>$ <?= number_format($row['GrandTotal'], 2) ?></td>
                <!-- <td>$189.00</td> -->
                <td><span><?php
                          $status = $row['OrderStatus'];
                          $paid   = $row['PaymentStatus'] == 'Paid';

                          if ($status == 'Completed' || ($paid && $status === '')) {
                            echo '<span class="status st-green">Completed</span>';
                          } elseif ($status == 'Pending') {
                            echo '<span class="status st-amber">Pending</span>';
                          } elseif ($status == 'Accept') {
                            echo '<span class="status st-blue">In Progress</span>';
                          } elseif ($status == 'Reject') {
                            echo '<span class="status st-red">Cancelled</span>';
                          } else {
                            echo '<span class="status st-secondary">' . htmlspecialchars($status !== '' ? $status : 'Unknown') . '</span>';
                          }
                          ?></span>
                </td>
              </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ═══ PAGE: ANALYTICS ═══ -->
    <div class="page" id="page-analytics">
      <div class="section-header">
        <div>
          <div class="section-title">Analytics</div>
          <div class="section-sub">Deep dive into performance data</div>
        </div>
        <button class="btn btn-primary">Export PDF</button>
      </div>
      <div class="metrics-grid">
        <div class="metric-card mc-blue">
          <div class="mc-label">
            <div class="mc-icon mc-icon-blue"><svg viewBox="0 0 24 24">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                <polyline points="17 6 23 6 23 12" />
              </svg></div>Conversion Rate
          </div>
          <div class="mc-value">5.9%</div>
          <div class="mc-delta delta-up">▲ 0.4% vs last month</div>
        </div>
        <div class="metric-card mc-green">
          <div class="mc-label">
            <div class="mc-icon mc-icon-green"><svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg></div>Bounce Rate
          </div>
          <div class="mc-value">38.2%</div>
          <div class="mc-delta delta-down">▼ 1.8% improvement</div>
        </div>
        <div class="metric-card mc-purple">
          <div class="mc-label">
            <div class="mc-icon mc-icon-purple"><svg viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                <circle cx="9" cy="7" r="4" />
              </svg></div>New Visitors
          </div>
          <div class="mc-value">14,820</div>
          <div class="mc-delta delta-up">▲ 22% vs last month</div>
        </div>
        <div class="metric-card mc-amber">
          <div class="mc-label">
            <div class="mc-icon mc-icon-amber"><svg viewBox="0 0 24 24">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
              </svg></div>Session Duration
          </div>
          <div class="mc-value">3m 42s</div>
          <div class="mc-delta delta-up">▲ 14s longer</div>
        </div>
      </div>
      <div class="charts-grid">
        <div class="chart-card chart-card-wide">
          <div class="chart-header">
            <div>
              <div class="chart-title">Traffic Sources</div>
              <div class="chart-sub">Monthly breakdown by acquisition channel</div>
            </div>
          </div>
          <div class="legend-row">
            <div class="legend-item">
              <div class="legend-dot" style="background:#4f8ef7"></div>Organic
            </div>
            <div class="legend-item">
              <div class="legend-dot" style="background:#22c97a"></div>Paid
            </div>
            <div class="legend-item">
              <div class="legend-dot" style="background:#9b7ffa"></div>Referral
            </div>
            <div class="legend-item">
              <div class="legend-dot" style="background:#f5a623"></div>Direct
            </div>
          </div>
          <div style="position:relative;width:100%;height:260px;">
            <canvas id="trafficChart" role="img" aria-label="Stacked bar chart of traffic sources over 12 months">Traffic breakdown by channel over the year.</canvas>
          </div>
        </div>
        <div class="chart-card">
          <div class="chart-header">
            <div>
              <div class="chart-title">Device Breakdown</div>
              <div class="chart-sub">Sessions by device type</div>
            </div>
          </div>
          <div style="position:relative;width:100%;height:220px;">
            <canvas id="deviceChart" role="img" aria-label="Doughnut chart showing device split">Mobile 54%, Desktop 36%, Tablet 10%</canvas>
          </div>
          <div class="legend-row" style="justify-content:center;margin-top:12px;">
            <div class="legend-item">
              <div class="legend-dot" style="background:#4f8ef7"></div>Mobile 54%
            </div>
            <div class="legend-item">
              <div class="legend-dot" style="background:#9b7ffa"></div>Desktop 36%
            </div>
            <div class="legend-item">
              <div class="legend-dot" style="background:#2dd4c0"></div>Tablet 10%
            </div>
          </div>
        </div>
        <div class="chart-card">
          <div class="chart-header">
            <div>
              <div class="chart-title">Hourly Traffic</div>
              <div class="chart-sub">Average sessions by hour today</div>
            </div>
          </div>
          <div style="position:relative;width:100%;height:220px;">
            <canvas id="hourlyChart" role="img" aria-label="Bar chart showing hourly traffic peaks">Peak traffic around 10am–2pm.</canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ PAGE: ORDERS ═══ -->


    <!-- ═══ PAGE: PRODUCTS ═══ -->
    <div class="page" id="page-reports">
      <div class="section-header">
        <div>
          <div class="section-title">Reports</div>
          <div class="section-sub">Scheduled & generated reports</div>
        </div>
        <button class="btn btn-primary">+ Generate Report</button>
      </div>
      <div class="three-col">
        <div class="table-card" style="margin-bottom:0;">
          <div class="table-card-header">
            <div class="chart-title">Recent Reports</div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Generated</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="td-main">Q1 2026 Sales Summary</td>
                <td>Monthly</td>
                <td>Apr 1, 2026</td>
                <td><span class="status st-green">Ready</span></td>
              </tr>
              <tr>
                <td class="td-main">April Revenue Deep Dive</td>
                <td>Custom</td>
                <td>May 2, 2026</td>
                <td><span class="status st-green">Ready</span></td>
              </tr>
              <tr>
                <td class="td-main">Top Customers LTV</td>
                <td>Customer</td>
                <td>May 10, 2026</td>
                <td><span class="status st-green">Ready</span></td>
              </tr>
              <tr>
                <td class="td-main">Channel Attribution</td>
                <td>Analytics</td>
                <td>May 15, 2026</td>
                <td><span class="status st-amber">Pending</span></td>
              </tr>
              <tr>
                <td class="td-main">May 2026 Full Report</td>
                <td>Monthly</td>
                <td>Scheduled</td>
                <td><span class="status st-blue">Queued</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="chart-card" style="display:flex;flex-direction:column;gap:0;">
          <div class="chart-header">
            <div>
              <div class="chart-title">Recent Activity</div>
              <div class="chart-sub">System events & alerts</div>
            </div>
          </div>
          <div style="margin: 0 -22px;">
            <div class="activity-item">
              <div class="activity-icon ai-green"><svg viewBox="0 0 24 24">
                  <polyline points="20 6 9 17 4 12" />
                </svg></div>
              <div>
                <div class="activity-text"><strong>Order #4821 completed</strong> — Sarah Johnson paid $189.00</div>
                <div class="activity-time">2 minutes ago</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon ai-blue"><svg viewBox="0 0 24 24">
                  <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <line x1="19" y1="8" x2="19" y2="14" />
                  <line x1="22" y1="11" x2="16" y2="11" />
                </svg></div>
              <div>
                <div class="activity-text"><strong>New customer registered</strong> — Jordan Smith joined</div>
                <div class="activity-time">18 minutes ago</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon ai-amber"><svg viewBox="0 0 24 24">
                  <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                  <line x1="12" y1="9" x2="12" y2="13" />
                  <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg></div>
              <div>
                <div class="activity-text"><strong>Low stock alert</strong> — Webcam HD 4K (89 units left)</div>
                <div class="activity-time">1 hour ago</div>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon ai-red"><svg viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="15" y1="9" x2="9" y2="15" />
                  <line x1="9" y1="9" x2="15" y2="15" />
                </svg></div>
              <div>
                <div class="activity-text"><strong>Order #4817 cancelled</strong> — Emma Wilson, $99.00 refunded</div>
                <div class="activity-time">3 hours ago</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ PAGE: CUSTOMERS ═══ -->
    <div class="page" id="page-settings">
      <div class="section-header">
        <div>
          <div class="section-title">Settings</div>
          <div class="section-sub">Manage your account and preferences</div>
        </div>
        <button class="btn btn-primary">Save Changes</button>
      </div>
      <div class="settings-grid">
        <div class="settings-nav">
          <div class="settings-nav-item active"><svg viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
              <circle cx="9" cy="7" r="4" />
            </svg>Profile</div>
          <div class="settings-nav-item"><svg viewBox="0 0 24 24">
              <rect x="2" y="3" width="20" height="14" rx="2" />
              <path d="M8 21h8m-4-4v4" />
            </svg>Appearance</div>
          <div class="settings-nav-item"><svg viewBox="0 0 24 24">
              <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.73 21a2 2 0 01-3.46 0" />
            </svg>Notifications</div>
          <div class="settings-nav-item"><svg viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2" />
              <path d="M7 11V7a5 5 0 0110 0v4" />
            </svg>Security</div>
          <div class="settings-nav-item"><svg viewBox="0 0 24 24">
              <line x1="12" y1="1" x2="12" y2="23" />
              <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
            </svg>Billing</div>
          <div class="settings-nav-item"><svg viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="3" />
              <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
            </svg>Integrations</div>
        </div>
        <div class="settings-panel">
          <div class="settings-section">
            <div class="settings-section-title">Personal Information</div>
            <div class="settings-section-sub">Update your name, email and role.</div>
            <div class="form-row">
              <div class="form-field"><label class="form-label">First Name</label><input class="form-input" type="text" value="Alex" /></div>
              <div class="form-field"><label class="form-label">Last Name</label><input class="form-input" type="text" value="Kim" /></div>
            </div>
            <div class="form-row">
              <div class="form-field"><label class="form-label">Email Address</label><input class="form-input" type="email" value="alex.kim@vanta.io" /></div>
              <div class="form-field"><label class="form-label">Role</label><input class="form-input" type="text" value="Sales Manager" /></div>
            </div>
          </div>
          <hr class="settings-divider" />
          <div class="settings-section">
            <div class="settings-section-title">Notifications</div>
            <div class="settings-section-sub">Choose what updates you receive.</div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info">New order alerts</div>
                <div class="toggle-sub">Get notified when a new order comes in</div>
              </div>
              <div class="toggle on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info">Weekly summary emails</div>
                <div class="toggle-sub">Receive a report every Monday morning</div>
              </div>
              <div class="toggle on" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info">Low stock warnings</div>
                <div class="toggle-sub">Alert when inventory drops below threshold</div>
              </div>
              <div class="toggle" onclick="this.classList.toggle('on')"></div>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-info">Customer signup alerts</div>
                <div class="toggle-sub">Notify on every new customer registration</div>
              </div>
              <div class="toggle" onclick="this.classList.toggle('on')"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /content -->
  </div><!-- /main -->

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {

      const months = <?= json_encode($months) ?>;
      const revenues = <?= json_encode($revenues) ?>;
      const orders = <?= json_encode($orders) ?>;

      console.log(months);
      console.log(revenues);
      console.log(orders);

      const ctx = document.getElementById('mainChart');

      if (!ctx) {
        console.log('Canvas not found');
        return;
      }

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: months,
          datasets: [{
              label: 'Revenue ($)',
              data: revenues,
              borderColor: '#4f8ef7',
              backgroundColor: 'rgba(79,142,247,0.1)',
              fill: true,
              tension: 0.4
            },
            {
              label: 'Orders',
              data: orders,
              borderColor: '#22c97a',
              backgroundColor: 'rgba(34,201,122,0.1)',
              fill: true,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });

      const orderLabels = <?= json_encode($orderTypes) ?>;
      const orderData = <?= json_encode($orderCounts) ?>;

      const hasOrderData = orderData.length > 0 && orderData.reduce((a, b) => a + b, 0) > 0;

      new Chart(
        document.getElementById('channelChart'), {
          type: 'doughnut',
          data: hasOrderData ? {
            labels: orderLabels,
            datasets: [{
              data: orderData,
              backgroundColor: ['#4f8ef7', '#22c97a', '#f5a623', '#9b7ffa', '#2dd4c0', '#ef4444']
            }]
          } : {
            labels: ['No data'],
            datasets: [{
              data: [1],
              backgroundColor: ['#dfe6ef'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: hasOrderData
              }
            }
          }
        }
      );

    });
  </script>
</body>

</html>