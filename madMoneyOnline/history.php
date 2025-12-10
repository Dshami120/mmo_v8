<?php
   ob_start();
   session_start();
   // If the user is logged in, redirect to the home page
    if ( !(isset($_SESSION['account_loggedin'])) ) {
        header('Location: index.html');
        exit;
    }
?>    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial History - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/charts.js"></script>
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
            <h1 class="mb-4">Financial History</h1>

            <section class="border rounded p-3 bg-white mb-4">
                <form class="row g-3">
                    <section class="col-12 col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control">
                    </section>
                    <section class="col-12 col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control">
                    </section>
                    <section class="col-12 col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select">
                            <option>All</option>
                            <option>Income</option>
                            <option>Expense</option>
                        </select>
                    </section>
                    <section class="col-12">
                        <button type="submit" class="btn btn-primary mt-2">
                            Filter History
                        </button>
                    </section>
                </form>
            </section>

            <section class="border rounded p-3 bg-white">
                <h2 class="h5 mb-3">Transactions</h2>

                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        require  'includes/dbOperations.php';
                        $sql = "SELECT t.Transaction_date as dat, a.account_type as acc, t.transaction_category as cat, a.account_name as nam, t.transaction_amount as amo FROM monery_transactions t, money_account a "; 
                        $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and t.to_account_id =  a.sys_account_id order by t.transaction_date desc";
                        $result = mysqli_query($con, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            // Output data of each row
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr><td>" .$row["dat"]."</td>";
                                echo "<td>" .$row["acc"]."</td>";
                                echo "<td>" .$row["cat"]."</td>";
                                echo "<td>" .$row["nam"]."</td>";
                                echo "<td class=\"text-end\">" .$row["amo"]."</td></tr>";
                            }
                        }
                        mysqli_close($con);
                    ?>    
                    </tbody>
                </table>
            </section>

                <!-- CHART UNDER TABLE (INSIDE SAME RIGHT COLUMN) -->
                <section class="border rounded p-3 bg-white">
                    <h2 class="h5 mb-3">Transaction Trend</h2>
                    <canvas id="historyChart"></canvas>
                </section>
                <!-- 2nd CHART -->
                 <section class="col-lg-6">
                        <div class="border rounded p-3 bg-white">
                            <h2 class="h5 mb-3">Income vs Expenses</h2>
                            <canvas id="incomeExpenseChart"></canvas>
                        </div>
                    </section>

        </section>

    </section>
</main>

</body>
</html>

