<?php
require_once '../_base.php';

// Ensure only Admin can access
require_admin();

$conn = db_connect();

// ----------------------------------------------------------------
// 1. DATA: SUMMARY CARDS
// ----------------------------------------------------------------
$summary = [
    'revenue' => 0,
    'orders' => 0,
    'low_stock' => 0
];

// Total Revenue
$res = $conn->query("SELECT SUM(total_amount) as total FROM `order`");
$summary['revenue'] = $res->fetch_object()->total ?? 0;

// Total Orders
$res = $conn->query("SELECT COUNT(*) as total FROM `order`");
$summary['orders'] = $res->fetch_object()->total ?? 0;

// Low Stock Count
$res = $conn->query("SELECT COUNT(*) as total FROM product WHERE stock_quantity < 5");
$summary['low_stock'] = $res->fetch_object()->total ?? 0;

// ----------------------------------------------------------------
// 1b. DATA: LOW STOCK LIST (Specific items for the table)
// ----------------------------------------------------------------
$low_stock_items = [];
$sql = "SELECT name, stock_quantity, price FROM product WHERE stock_quantity < 5 ORDER BY stock_quantity ASC";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $low_stock_items[] = $row;
}

// ----------------------------------------------------------------
// 2. DATA: SALES TREND (Last 7 Days)
// ----------------------------------------------------------------
$dates = [];
$sales_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($date)); // Format as "12 Dec"
    
    $sql = "SELECT SUM(total_amount) as total FROM `order` WHERE DATE(order_date) = '$date'";
    $res = $conn->query($sql);
    $sales_data[] = $res->fetch_object()->total ?? 0;
}

// ----------------------------------------------------------------
// 3. DATA: SALES BY CATEGORY
// ----------------------------------------------------------------
$cat_labels = [];
$cat_data = [];

$sql = "SELECT c.category_name, SUM(oi.quantity) as total_sold
        FROM order_item oi
        JOIN product p ON oi.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        GROUP BY c.category_id";
$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    $cat_labels[] = $row['category_name'];
    $cat_data[] = $row['total_sold'];
}

// ----------------------------------------------------------------
// 4. DATA: PAYMENT METHOD DISTRIBUTION
// ----------------------------------------------------------------
$pay_labels = [];
$pay_data = [];

$sql = "SELECT payment_method, COUNT(*) as count FROM `order` GROUP BY payment_method";
$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    $pay_labels[] = $row['payment_method'];
    $pay_data[] = $row['count'];
}

// ----------------------------------------------------------------
// 5. DATA: TOP 5 PRODUCTS
// ----------------------------------------------------------------
$top_labels = [];
$top_data = [];

$sql = "SELECT p.name, SUM(oi.quantity) as total_qty
        FROM order_item oi
        JOIN product p ON oi.product_id = p.product_id
        GROUP BY p.product_id
        ORDER BY total_qty DESC
        LIMIT 5";
$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    $top_labels[] = substr($row['name'], 0, 20) . '...';
    $top_data[] = $row['total_qty'];
}

require_once '../_head.php';
?>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #report-container, #report-container * {
            visibility: visible;
        }
        #report-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        /* Hide buttons and nav when printing */
        .no-print, nav, footer { 
            display: none !important; 
        }
        /* Ensure charts resize for paper */
        .chart-container {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        /* Force background colors */
        .bg-white {
            background-color: white !important;
            border: 1px solid #ddd;
        }
        /* Titles */
        h1, h2, h3 {
            color: black !important;
        }
    }
</style>

<div id="report-container" class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard & Reports</h1>
            <p class="text-sm text-gray-500 mt-1">Generated on: <?= date('d M Y, h:i A') ?></p>
        </div>
        <button onclick="window.print()" class="no-print bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 font-semibold shadow-md flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm uppercase font-bold">Total Revenue</h3>
            <p class="text-3xl font-bold text-gray-800">RM<?= number_format($summary['revenue'], 2) ?></p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm uppercase font-bold">Total Orders</h3>
            <p class="text-3xl font-bold text-gray-800"><?= $summary['orders'] ?></p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
            <h3 class="text-gray-500 text-sm uppercase font-bold">Low Stock Alerts</h3>
            <p class="text-3xl font-bold text-red-600"><?= $summary['low_stock'] ?></p>
            <p class="text-xs text-gray-500 mt-1">Action Needed</p>
        </div>
    </div>

    <!-- CHARTS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        
        <!-- 1. Sales Trend (FIXED: Added wrapper div) -->
        <div class="chart-container bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Sales Trend (Last 7 Days)</h3>
            <div class="h-80 relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- 2. Category Distribution -->
        <div class="chart-container bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Sales by Category</h3>
            <div class="h-80 flex justify-center relative">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- 3. Top Products (FIXED: Added wrapper div) -->
        <div class="chart-container bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top 5 Best Selling Products</h3>
            <div class="h-80 relative">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <!-- 4. Payment Methods -->
        <div class="chart-container bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Methods</h3>
            <div class="h-80 flex justify-center relative">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- LOW STOCK ALERT TABLE -->
    <?php if (!empty($low_stock_items)): ?>
    <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-red-500 mb-8 break-inside-avoid">
        <h3 class="text-xl font-bold text-red-700 mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Low Stock Inventory (Urgent Restock)
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product Name</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Current Stock</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_items as $item): ?>
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-medium"><?= e($item['name']) ?></p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                <span aria-hidden="true" class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                <span class="relative"><?= e($item['stock_quantity']) ?> units left</span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">RM<?= number_format($item['price'], 2) ?></p>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    // Configuration for Charts
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    };

    // --- 1. SALES TREND CHART ---
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue (RM)',
                data: <?= json_encode($sales_data) ?>,
                borderColor: '#10B981', 
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            scales: { y: { beginAtZero: true } }
        }
    });

    // --- 2. CATEGORY CHART ---
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($cat_labels) ?>,
            datasets: [{
                data: <?= json_encode($cat_data) ?>,
                backgroundColor: ['#3B82F6', '#EF4444', '#F59E0B', '#10B981', '#6366F1', '#8B5CF6']
            }]
        },
        options: commonOptions
    });

    // --- 3. TOP PRODUCTS CHART ---
    const ctxTop = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: <?= json_encode($top_labels) ?>,
            datasets: [{
                label: 'Units Sold',
                data: <?= json_encode($top_data) ?>,
                backgroundColor: '#6366F1',
                borderRadius: 4
            }]
        },
        options: { 
            indexAxis: 'y',
            ...commonOptions
        }
    });

    // --- 4. PAYMENT METHOD CHART ---
    const ctxPay = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctxPay, {
        type: 'pie',
        data: {
            labels: <?= json_encode($pay_labels) ?>,
            datasets: [{
                data: <?= json_encode($pay_data) ?>,
                backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'] 
            }]
        },
        options: commonOptions
    });
</script>

<?php require_once '../_foot.php'; ?>