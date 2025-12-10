<?php
   ob_start();
   session_start();
   // If the user is logged in, redirect to the home page
    if ( !(isset($_SESSION['account_loggedin'])) ) {
        header('Location: index.html');
        exit;
    }

/* ============================================================
   DASHBOARD DATE FILTER (WEEK / MONTH / YEAR / ALL / CUSTOM)
   ============================================================ */

// Read incoming GET params
$range    = $_GET['range'] ?? 'month'; // default to "This Month"
$fromDate = $_GET['from']  ?? '';
$toDate   = $_GET['to']    ?? '';

// Compute quick ranges
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
            $fromDate = date('Y-m-01', $todayTs);
            $toDate   = date('Y-m-t',  $todayTs);
            break;

        case 'year':
            $fromDate = $todayY . '-01-01';
            $toDate   = $todayY . '-12-31';
            break;

        case 'all':
            // No date filter
            $fromDate = '';
            $toDate   = '';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<main class="container-fluid">
    <section class="row min-vh-100">

        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
            <?php
                include 'nav.html';
            ?>
        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Dashboard Overview</h1>

            <!-- ===================== DASHBOARD DATE FILTERS ===================== -->
            <?php
                // Build base params (so we can keep other future GET params if needed)
                $dashBaseParams = $_GET;
                unset($dashBaseParams['range'], $dashBaseParams['from'], $dashBaseParams['to']);

                function dashRangeUrl($baseParams, $rangeValue) {
                    $params = $baseParams;
                    $params['range'] = $rangeValue;
                    return '?' . http_build_query($params);
                }

                $dashBtnClass = function($r) use ($range) {
                    return $range === $r ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
                };
            ?>
            <section class="border rounded p-3 bg-white mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div class="mb-2 mb-md-0">
                        <strong>Quick Ranges:</strong>
                    </div>
                    <div class="btn-group">
                        <a href="<?= htmlspecialchars(dashRangeUrl($dashBaseParams, 'week'))   ?>" class="<?= $dashBtnClass('week') ?>">This Week</a>
                        <a href="<?= htmlspecialchars(dashRangeUrl($dashBaseParams, 'month'))  ?>" class="<?= $dashBtnClass('month') ?>">This Month</a>
                        <a href="<?= htmlspecialchars(dashRangeUrl($dashBaseParams, 'year'))   ?>" class="<?= $dashBtnClass('year') ?>">This Year</a>
                        <a href="<?= htmlspecialchars(dashRangeUrl($dashBaseParams, 'all'))    ?>" class="<?= $dashBtnClass('all') ?>">All Time</a>
                        <a href="<?= htmlspecialchars(dashRangeUrl($dashBaseParams, 'custom')) ?>" class="<?= $dashBtnClass('custom') ?>">Custom</a>
                    </div>
                </div>

                <!-- Custom date range form -->
                <form method="GET" class="row g-3">
                    <!-- When user submits custom dates, force range=custom -->
                    <input type="hidden" name="range" value="custom">

                    <section class="col-12 col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from"
                               value="<?= htmlspecialchars($fromDate) ?>">
                    </section>

                    <section class="col-12 col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to"
                               value="<?= htmlspecialchars($toDate) ?>">
                    </section>

                    <section class="col-12 col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            Apply
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </section>
                </form>

                <?php if ($fromDate !== '' || $toDate !== ''): ?>
                    <div class="mt-3 small text-muted">
                        Active date range:
                        <?php
                            $labelFrom = $fromDate !== '' ? $fromDate : '…';
                            $labelTo   = $toDate   !== '' ? $toDate   : '…';
                            echo htmlspecialchars($labelFrom . ' to ' . $labelTo);
                        ?>
                    </div>
                <?php endif; ?>
            </section>
            <!-- ================== END DASHBOARD DATE FILTERS ==================== -->
            <!-- ================== END DASHBOARD DATE FILTERS ==================== -->

<?php
// =============================================================
//   CONTINUOUS BALANCE CALCULATION (Corrected Logic)
// =============================================================
require 'includes/dbOperations.php';

$userId = $_SESSION['account_id'];

// Balance must be calculated from the beginning of time → end of filter date
$balanceEndDate = ($toDate !== '' ? $toDate : date('Y-m-d'));

// ===== Total Income Up To End Date =====
$sqlInc = "
    SELECT COALESCE(SUM(t.transaction_amount), 0) AS total
    FROM monery_transactions t
    JOIN money_account a ON t.to_account_id = a.sys_account_id
    WHERE t.sys_user_id = $userId
      AND a.account_type = 'Income'
      AND t.transaction_date <= '$balanceEndDate'
";
$resInc = mysqli_query($con, $sqlInc);
$rowInc = mysqli_fetch_assoc($resInc);
$totalIncomeUpToNow = $rowInc['total'] ?? 0;

// ===== Total Expense Up To End Date =====
$sqlExp = "
    SELECT COALESCE(SUM(t.transaction_amount), 0) AS total
    FROM monery_transactions t
    JOIN money_account a ON t.to_account_id = a.sys_account_id
    WHERE t.sys_user_id = $userId
      AND a.account_type = 'Expense'
      AND t.transaction_date <= '$balanceEndDate'
";
$resExp = mysqli_query($con, $sqlExp);
$rowExp = mysqli_fetch_assoc($resExp);
$totalExpenseUpToNow = $rowExp['total'] ?? 0;

$trueContinuousBalance = $totalIncomeUpToNow - $totalExpenseUpToNow;
?>


            <?php
                    $exp =0;
                    $inc =0;
                    $myExp = [];
                    $myInc =[];
                $sql = "SELECT a.account_type as typ, t.transaction_category as cat, sum(t.transaction_amount) as tot 
                        FROM madmoneyonline.monery_transactions t, madmoneyonline.money_account a ";
                $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and t.to_account_id =  a.sys_account_id ";

                // ===== Apply date filter to summary query =====
                if ($fromDate !== '') {
                    $sql .= " and t.transaction_date >= '" . $fromDate . "' ";
                }
                if ($toDate !== '') {
                    $sql .= " and t.transaction_date <= '" . $toDate . "' ";
                }

                $sql = $sql ." group by transaction_category order by 1 ";
                $result = mysqli_query($con, $sql);
                if (mysqli_num_rows($result) > 0) {
                    // Output data of each row

                    while($row = mysqli_fetch_assoc($result)) {
                        
                        if( $row["typ"]  == "Income" ) {
                           // echo "INCOME" . $row["typ"] ." ->" . $row["cat"] ." ->" . $row["tot"];
                            $inc = $inc + $row["tot"];
                            $incRec =[ $row["cat"] , $row["tot"] ];
                            array_push($myInc, $incRec);
                        }
                         if( $row["typ"]  == "Expense" ) {
                            // echo "EXPENSE" . $row["typ"] ." ->" . $row["cat"] ." ->" . $row["tot"];
                            $exp = $exp + $row["tot"];
                            $expRec =[ $row["cat"] , $row["tot"] ];
                            array_push($myExp, $expRec);

                        }
                    }
 
                }
                mysqli_close($con);
                ?>

            <!-- Summary cards row -->
            <section class="row g-3">
                <section class="col-12 col-md-4">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5">Current Balance</h2>
                        <p class="display-6 text-success mb-0">$ <?php echo number_format($trueContinuousBalance, 2) ?></p>
                        <p class="text-muted">
                            <?php
                                if ($fromDate === '' && $toDate === '') {
                                    echo "Across all accounts (All Time)";
                                } else {
                                    echo "Across all accounts (Filtered Range)";
                                }
                            ?>
                        </p>
                    </section>
                </section>

                <section class="col-12 col-md-4">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5">Income</h2>
                        <p class="display-6 text-primary mb-0">$ <?php echo $inc ?></p>
                        <p class="text-muted">
                            <?php
                                if ($fromDate === '' && $toDate === '') {
                                    echo "All Time";
                                } else {
                                    echo "Filtered Range";
                                }
                            ?>
                        </p>
                    </section>
                </section>

                <section class="col-12 col-md-4">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5">Expenses</h2>
                        <p class="display-6 text-danger mb-0">$ <?php echo $exp ?></p>
                        <p class="text-muted">
                            <?php
                                if ($fromDate === '' && $toDate === '') {
                                    echo "All Time";
                                } else {
                                    echo "Filtered Range";
                                }
                            ?>
                        </p>
                    </section>
                </section>
            </section>

            <!-- Charts / tables row -->
            <section class="row g-3 mt-4">
                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Spending by Category</h2>
                        <p class="text-muted"></p>
                        <ul class="list-group">
                            <?php
                                $ss = count($myExp);
                                for ($i=0; $i < $ss; $i++){
                                    echo '<li class="list-group-item d-flex justify-content-between">';
                                    echo "<span>" .$myExp[$i][0] ."</span><span>$" .number_format($myExp[$i][1], 2). "</span>";
                                    echo '</li>';
                                }
                                if ($ss === 0) {
                                    echo '<li class="list-group-item text-muted text-center">No expenses in this date range.</li>';
                                }
                            ?>
                          </ul>
                    </section>
                </section>

                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Recent Transactions</h2>
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT t.Transaction_date as dat, a.account_type as acc, t.transaction_category as cat, t.transaction_Desc as Description, t.transaction_amount as amo 
                                        FROM monery_transactions t, money_account a "; 
                                $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and t.to_account_id =  a.sys_account_id ";

                                // ===== Apply date filter to recent-transactions query =====
                                if ($fromDate !== '') {
                                    $sql .= " and t.transaction_date >= '" . $fromDate . "' ";
                                }
                                if ($toDate !== '') {
                                    $sql .= " and t.transaction_date <= '" . $toDate . "' ";
                                }

                                $sql = $sql ." order by t.transaction_date desc";

                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    $i =0;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr><td>" .$row["dat"]."</td>";
                                        echo "<td>" .$row["acc"]."</td>";
                                        echo "<td>" .$row["cat"]."</td>";
                                        echo "<td>" .htmlspecialchars($row["Description"])."</td>";
                                        echo "<td class=\"text-end\">" .number_format($row["amo"], 2)."</td></tr>";
                                        $i = $i+1;
                                        if($i > 10) { break; }
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center text-muted">No transactions in this date range.</td></tr>';
                                }
                                mysqli_close($con);
                                ?>
                            </tbody>
                        </table>
                    </section>
                </section>
            </section>
            
            <!--  *******************************Charts .js ****************************************** -->
            <section class="row g-3 mt-4">
                <section class="col-12 col-lg-6">
                    <canvas id="myPieChart" width="200" height="200"></canvas>
                </section>

                <section class="col-12 col-lg-6">
                    <canvas id="myPieChart2" width="200" height="200"></canvas>
                </section>
            </section>  




        </section>

    </section>
</main>
    <script>
       const ctx = document.getElementById('myPieChart').getContext('2d');
    const myPieChart = new Chart(ctx, {
    type: 'pie',
    data: {
        <?php
            $ss = count($myExp);
            echo "labels: [";
            for ($i=0; $i < $ss; $i++){
                echo "\"".$myExp[$i][0] ."\", ";
            }
            echo "],"
        ?>
        //labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: 'My First Dataset',
            <?php
                $ss = count($myExp);
                echo "data: [";
                for ($i=0; $i < $ss; $i++){
                    echo $myExp[$i][1] .", ";
                }
                echo "],"
            ?>

            //data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Spending by Category'
            }
        }
    }
});
    </script>

        <script>
       const ctx1 = document.getElementById('myPieChart2').getContext('2d');
    const myPieChart2 = new Chart(ctx1, {
    type: 'pie',
    data: {
        <?php
            $ss = count($myInc);
            echo "labels: [";
            for ($i=0; $i < $ss; $i++){
                echo "\"".$myInc[$i][0] ."\", ";
            }
            echo "],"
        ?>
        //labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: 'My First Dataset',
            <?php
                $ss = count($myInc);
                echo "data: [";
                for ($i=0; $i < $ss; $i++){
                    echo $myInc[$i][1] .", ";
                }
                echo "],"
            ?>

            //data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Income by Category'
            }
        }
    }
});
    </script>
</body>
</html>
