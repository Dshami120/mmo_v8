<?php
ob_start();
session_start();

// Redirect if not logged in
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: index.html');
    exit;
}

require 'includes/dbOperations.php';

/* ============================================================
   1. READ FILTER + CONTROL VALUES
   ============================================================ */

$range     = $_GET['range']      ?? 'custom'; // quick range: week/month/year/all/custom
$fromDate  = $_GET['from']       ?? '';
$toDate    = $_GET['to']         ?? '';
$type      = $_GET['type']       ?? 'All';
$category  = $_GET['category']   ?? 'All';
$accountId = $_GET['account']    ?? 'All';
$amountMin = $_GET['amount_min'] ?? '';
$amountMax = $_GET['amount_max'] ?? '';
$search    = trim($_GET['search'] ?? '');
$page      = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$export    = $_GET['export'] ?? ''; // 'csv' when exporting

/* ============================================================
   2. APPLY QUICK RANGE (THIS WEEK / MONTH / YEAR / ALL)
   ============================================================ */

$todayTs = time();
$todayY  = date('Y', $todayTs);
$todayM  = date('m', $todayTs);
$todayD  = date('d', $todayTs);

if ($range !== 'custom') {
    switch ($range) {
        case 'week':
            // Monday–Sunday of current week
            $weekStartTs = strtotime('monday this week', $todayTs);
            $weekEndTs   = strtotime('sunday this week', $todayTs);
            $fromDate    = date('Y-m-d', $weekStartTs);
            $toDate      = date('Y-m-d', $weekEndTs);
            break;

        case 'month':
            $fromDate = date('Y-m-01', $todayTs);         // first day of month
            $toDate   = date('Y-m-t',  $todayTs);         // last day of month
            break;

        case 'year':
            $fromDate = $todayY . '-01-01';
            $toDate   = $todayY . '-12-31';
            break;

        case 'all':
            // Remove date restrictions
            $fromDate = '';
            $toDate   = '';
            break;
    }
}

/* ============================================================
   3. LOAD DROPDOWN DATA (AUTO-POPULATED)
   ============================================================ */

// 3.1 Account Types (Income / Expense / Saving / Investment, etc.)
$typeOptions = [];
$typeRes = $con->query("SELECT ACCOUNT_TYPE_NAME FROM money_account_type ORDER BY ACCOUNT_TYPE_NAME");
if ($typeRes) {
    while ($row = $typeRes->fetch_assoc()) {
        $typeOptions[] = $row['ACCOUNT_TYPE_NAME'];
    }
}

// 3.2 Categories (Salary, Rent/Housing, Groceries, etc.)
$categoryOptions = [];
$catRes = $con->query("SELECT ACCOUNT_CATEGORY_NAME FROM money_category ORDER BY ACCOUNT_CATEGORY_NAME");
if ($catRes) {
    while ($row = $catRes->fetch_assoc()) {
        if (!empty($row['ACCOUNT_CATEGORY_NAME'])) {
            $categoryOptions[] = $row['ACCOUNT_CATEGORY_NAME'];
        }
    }
}

// 3.3 Accounts for this user (Checking, Savings, Cash, etc.)
$accountOptions = [];
$accStmt = $con->prepare("
    SELECT SYS_ACCOUNT_ID, ACCOUNT_NAME 
    FROM money_account 
    WHERE SYS_USER_ID = ?
    ORDER BY ACCOUNT_NAME
");
$accStmt->bind_param("i", $_SESSION['account_id']);
$accStmt->execute();
$accRes = $accStmt->get_result();
while ($row = $accRes->fetch_assoc()) {
    $accountOptions[] = $row;  // includes SYS_ACCOUNT_ID + ACCOUNT_NAME
}
$accStmt->close();

/* ============================================================
   4. BUILD MAIN FILTERED QUERY (NO LIMIT → ALL RESULTS)
   ============================================================ */

$sql = "
    SELECT 
        t.TRANSACTION_DATE      AS dat,
        a.ACCOUNT_TYPE          AS acc,
        t.TRANSACTION_CATEGORY  AS cat,
        a.ACCOUNT_NAME          AS nam,
        t.TRANSACTION_AMOUNT    AS amo,
        t.TRANSACTION_DESC      AS descr,
        a.SYS_ACCOUNT_ID        AS acc_id
    FROM monery_transactions t
    JOIN money_account a 
        ON t.TO_ACCOUNT_ID = a.SYS_ACCOUNT_ID
    WHERE t.SYS_USER_ID = ?
";

$params   = [$_SESSION['account_id']];
$typesStr = "i";  // first param is user id (int)

// DATE RANGE
if ($fromDate !== '') {
    $sql       .= " AND t.TRANSACTION_DATE >= ? ";
    $params[]   = $fromDate;
    $typesStr  .= "s";
}
if ($toDate !== '') {
    $sql       .= " AND t.TRANSACTION_DATE <= ? ";
    $params[]   = $toDate;
    $typesStr  .= "s";
}

// TYPE (Income, Expense, Saving, Investment, etc.)
if ($type !== 'All' && $type !== '') {
    $sql       .= " AND a.ACCOUNT_TYPE = ? ";
    $params[]   = $type;
    $typesStr  .= "s";
}

// CATEGORY
if ($category !== 'All' && $category !== '') {
    $sql       .= " AND t.TRANSACTION_CATEGORY = ? ";
    $params[]   = $category;
    $typesStr  .= "s";
}

// ACCOUNT
if ($accountId !== 'All' && $accountId !== '') {
    $sql       .= " AND a.SYS_ACCOUNT_ID = ? ";
    $params[]   = (int)$accountId;
    $typesStr  .= "i";
}

// AMOUNT RANGE
if ($amountMin !== '' && is_numeric($amountMin)) {
    $sql       .= " AND t.TRANSACTION_AMOUNT >= ? ";
    $params[]   = (float)$amountMin;
    $typesStr  .= "d";
}
if ($amountMax !== '' && is_numeric($amountMax)) {
    $sql       .= " AND t.TRANSACTION_AMOUNT <= ? ";
    $params[]   = (float)$amountMax;
    $typesStr  .= "d";
}

// TEXT SEARCH (description, category, account name)
if ($search !== '') {
    $sql      .= " AND (
        t.TRANSACTION_DESC     LIKE ? OR
        t.TRANSACTION_CATEGORY LIKE ? OR
        a.ACCOUNT_NAME         LIKE ?
    ) ";
    $like      = "%".$search."%";
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $typesStr .= "sss";
}

$sql .= " ORDER BY t.TRANSACTION_DATE DESC ";

// EXECUTE query (all filtered rows)
$stmt = $con->prepare($sql);
$stmt->bind_param($typesStr, ...$params);
$stmt->execute();
$result = $stmt->get_result();

/* ============================================================
   5. BUILD ARRAYS (ALL FILTERED RESULTS)
   ============================================================ */

$transactionsAll = [];
$chartDates      = [];
$chartAmounts    = [];
$incomeTotal     = 0;
$expenseTotal    = 0;

while ($row = $result->fetch_assoc()) {
    $transactionsAll[] = $row;

    // For charts
    $chartDates[]   = $row['dat'];
    $chartAmounts[] = (float)$row['amo'];

    if ($row['acc'] === 'Income')  { $incomeTotal  += $row['amo']; }
    if ($row['acc'] === 'Expense') { $expenseTotal += $row['amo']; }
}

$stmt->close();
$con->close();

/* ============================================================
   6. EXPORT CSV (ALL FILTERED ROWS, IGNORE PAGINATION)
   ============================================================ */
if ($export === 'csv') {
    // Output CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    $filename = 'financial_history_' . date('Ymd_His') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $output = fopen('php://output', 'w');

    // CSV header row
    fputcsv($output, ['Date', 'Type', 'Category', 'Account', 'Description', 'Amount']);

    // All filtered transactions
    foreach ($transactionsAll as $t) {
        fputcsv($output, [
            $t['dat'],
            $t['acc'],
            $t['cat'],
            $t['nam'],
            $t['descr'],
            $t['amo']
        ]);
    }

    fclose($output);
    exit; // Important: stop normal HTML output
}

/* ============================================================
   7. PAGINATION (ON PHP ARRAY)
   ============================================================ */

$pageSize   = 20; // rows per page
$totalRows  = count($transactionsAll);
$totalPages = max(1, (int)ceil($totalRows / $pageSize));
if ($page > $totalPages) $page = $totalPages;

$offset           = ($page - 1) * $pageSize;
$transactionsPage = array_slice($transactionsAll, $offset, $pageSize);

// For charts (all filtered data)
$chartDatesJSON   = json_encode($chartDates);
$chartAmountsJSON = json_encode($chartAmounts);
$incomeJSON       = json_encode($incomeTotal);
$expenseJSON      = json_encode($expenseTotal);

// Build base params for links (keep filters, change page/range/etc.)
$baseParams = $_GET;
unset($baseParams['page'], $baseParams['export']); // will set them manually later

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial History - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<main class="container-fluid">
    <section class="row min-vh-100">

        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
            <?php include 'nav.html'; ?>
        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Financial History</h1>

            <!-- ===================== QUICK RANGE BUTTONS ===================== -->
            <section class="border rounded p-3 bg-white mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="mb-2 mb-md-0">
                        <strong>Quick Ranges:</strong>
                    </div>
                    <div class="btn-group">
                        <?php
                        // helper to build URLs with range
                        function rangeUrl($baseParams, $rangeValue) {
                            $params = $baseParams;
                            $params['range'] = $rangeValue;
                            // when switching range, reset page
                            $params['page']  = 1;
                            return '?' . http_build_query($params);
                        }

                        $btnClass = function($r) use ($range) {
                            return $range === $r ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
                        };
                        ?>
                        <a href="<?= htmlspecialchars(rangeUrl($baseParams, 'week')) ?>"   class="<?= $btnClass('week') ?>">This Week</a>
                        <a href="<?= htmlspecialchars(rangeUrl($baseParams, 'month')) ?>"  class="<?= $btnClass('month') ?>">This Month</a>
                        <a href="<?= htmlspecialchars(rangeUrl($baseParams, 'year')) ?>"   class="<?= $btnClass('year') ?>">This Year</a>
                        <a href="<?= htmlspecialchars(rangeUrl($baseParams, 'all')) ?>"    class="<?= $btnClass('all') ?>">All Time</a>
                        <a href="<?= htmlspecialchars(rangeUrl($baseParams, 'custom')) ?>" class="<?= $btnClass('custom') ?>">Custom</a>
                    </div>
                </div>
            </section>

            <!-- ===================== FILTER BAR ===================== -->
            <section class="border rounded p-3 bg-white mb-4">
                <form method="GET" class="row g-3">
                    <!-- Ensure form sets range to custom -->
                    <input type="hidden" name="range" value="custom">

                    <!-- Row 1: Dates + Type -->
                    <section class="col-12 col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($fromDate) ?>">
                    </section>

                    <section class="col-12 col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($toDate) ?>">
                    </section>

                    <section class="col-12 col-md-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="All" <?= $type === 'All' ? 'selected' : '' ?>>All Types</option>
                            <?php foreach ($typeOptions as $tOpt): ?>
                                <option value="<?= htmlspecialchars($tOpt) ?>" 
                                        <?= $type === $tOpt ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tOpt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </section>

                    <!-- Row 2: Category + Account -->
                    <section class="col-12 col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="All" <?= $category === 'All' ? 'selected' : '' ?>>All Categories</option>
                            <?php foreach ($categoryOptions as $cOpt): ?>
                                <option value="<?= htmlspecialchars($cOpt) ?>"
                                        <?= $category === $cOpt ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cOpt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </section>

                    <section class="col-12 col-md-4">
                        <label class="form-label">Account</label>
                        <select class="form-select" name="account">
                            <option value="All" <?= $accountId === 'All' ? 'selected' : '' ?>>All Accounts</option>
                            <?php foreach ($accountOptions as $acc): ?>
                                <option value="<?= $acc['SYS_ACCOUNT_ID'] ?>"
                                        <?= (string)$accountId === (string)$acc['SYS_ACCOUNT_ID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc['ACCOUNT_NAME']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </section>

                    <!-- Row 3: Amount Range -->
                    <section class="col-6 col-md-2">
                        <label class="form-label">Min Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount_min" 
                               value="<?= htmlspecialchars($amountMin) ?>">
                    </section>

                    <section class="col-6 col-md-2">
                        <label class="form-label">Max Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount_max" 
                               value="<?= htmlspecialchars($amountMax) ?>">
                    </section>

                    <!-- Row 4: Text Search + Buttons -->
                    <section class="col-12 col-md-6">
                        <label class="form-label">Search (description, category, account)</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="e.g. rent, groceries, paycheck..."
                               value="<?= htmlspecialchars($search) ?>">
                    </section>

                    <section class="col-12 col-md-6 d-flex align-items-end flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            Apply Filters
                        </button>

                        <a href="financial_history.php" class="btn btn-outline-secondary">
                            Reset
                        </a>

                        <?php
                        // Export CSV link preserving current filters
                        $csvParams           = $baseParams;
                        $csvParams['export'] = 'csv';
                        $csvParams['page']   = 1; // export all, ignore pagination
                        $csvUrl              = '?' . http_build_query($csvParams);
                        ?>
                        <a href="<?= htmlspecialchars($csvUrl) ?>" class="btn btn-success">
                            Export CSV
                        </a>
                    </section>

                </form>
            </section>

            <!-- ===================== TRANSACTIONS TABLE ===================== -->
            <section class="border rounded p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h5 mb-0">Transactions</h2>
                    <small class="text-muted">
                        Showing <?= $totalRows === 0 ? 0 : ($offset + 1) ?>–<?= min($offset + $pageSize, $totalRows) ?> of <?= $totalRows ?>
                    </small>
                </div>

                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($transactionsPage) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No transactions found for the selected filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactionsPage as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t["dat"]) ?></td>
                                <td><?= htmlspecialchars($t["acc"]) ?></td>
                                <td><?= htmlspecialchars($t["cat"]) ?></td>
                                <td><?= htmlspecialchars($t["nam"]) ?></td>
                                <td><?= htmlspecialchars($t["descr"]) ?></td>
                                <td class="text-end"><?= number_format($t["amo"], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- ===================== PAGINATION CONTROLS ===================== -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Transaction pages">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <?php
                            // Helper for page URLs
                            function pageUrl($baseParams, $pageNum) {
                                $params         = $baseParams;
                                $params['page'] = $pageNum;
                                return '?' . http_build_query($params);
                            }

                            // Previous
                            $prevDisabled = ($page <= 1) ? ' disabled' : '';
                            ?>
                            <li class="page-item<?= $prevDisabled ?>">
                                <a class="page-link" href="<?= $page > 1 ? htmlspecialchars(pageUrl($baseParams, $page - 1)) : '#' ?>">
                                    &laquo;
                                </a>
                            </li>

                            <?php
                            // Simple windowed pagination (up to 7 pages around current)
                            $startPage = max(1, $page - 3);
                            $endPage   = min($totalPages, $page + 3);

                            for ($p = $startPage; $p <= $endPage; $p++):
                                $active = ($p == $page) ? ' active' : '';
                                ?>
                                <li class="page-item<?= $active ?>">
                                    <a class="page-link" href="<?= htmlspecialchars(pageUrl($baseParams, $p)) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php
                            // Next
                            $nextDisabled = ($page >= $totalPages) ? ' disabled' : '';
                            ?>
                            <li class="page-item<?= $nextDisabled ?>">
                                <a class="page-link" href="<?= $page < $totalPages ? htmlspecialchars(pageUrl($baseParams, $page + 1)) : '#' ?>">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            </section>

            <!-- ===================== INCOME vs EXPENSES CHART ===================== -->
            <section class="mt-4">
                <div class="border rounded p-4 bg-white text-center">
                    <h2 class="h5 mb-4">Income vs Expenses</h2>
                    <div class="chart-container" style="width:100%; max-width:900px; margin:0 auto;">
                        <canvas id="incomeExpenseChart" style="height:350px !important;"></canvas>
                    </div>
                </div>
            </section>


        </section> <!-- /main right column -->

    </section>
</main>

<script>
// PHP → JS data for charts (all filtered rows, NOT paginated)
const historyLabels  = <?= $chartDatesJSON ?>;
const historyAmounts = <?= $chartAmountsJSON ?>;
const incomeTotal    = <?= $incomeJSON ?>;
const expenseTotal   = <?= $expenseJSON ?>;

// Bar chart: income vs expenses for current filters
if (document.getElementById("incomeExpenseChart")) {
    new Chart(incomeExpenseChart, {
        type: 'bar',
        data: {
            labels: ['Income', 'Outgoing'],
            datasets: [{
                label: 'Amount',
                data: [incomeTotal, expenseTotal],
                backgroundColor: ['#4CAF50', '#F44336'],
                borderColor: ['#388E3C', '#D32F2F'],
                borderWidth: 1
            }]
        },
        options: {
            plugins: { 
                legend: { display: false } 
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => '$' + val
                    }
                }
            }
        }
    });
}
</script>

</body>
</html>
