<?php
   ob_start();
   session_start();
   // If the user is logged in, redirect to the home page
    if ( !(isset($_SESSION['account_loggedin'])) ) {
        header('Location: index.html');
        exit;
    }
 /*   
    // Access a single session variable
    if (isset($_SESSION['username'])) {
        echo "Welcome, " . $_SESSION['username'] . "!";
    } else {
        echo "No username found in session.";
    }

    // Access all session data (for debugging or inspection)
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    ?>
    */
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
            <?php
                require  'includes/dbOperations.php';
                    $exp =0;
                    $inc =0;
                    $myExp = [];
                    $myInc =[];
                $sql = "SELECT a.account_type as typ, t.transaction_category as cat, sum(t.transaction_amount) as tot FROM madmoneyonline.monery_transactions t, madmoneyonline.money_account a ";
                $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and t.to_account_id =  a.sys_account_id ";
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
                        <h2 class="h5">Total Balance</h2>
                        <p class="display-6 text-success mb-0">$ <?php echo ($inc - $exp) ?></p>
                        <p class="text-muted">Across all accounts</p>
                    </section>
                </section>

                <section class="col-12 col-md-4">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5">This Month’s Income</h2>
                        <p class="display-6 text-primary mb-0">$ <?php echo $inc ?></p>
                        <p class="text-muted">+8% vs last month</p>
                    </section>
                </section>

                <section class="col-12 col-md-4">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5">This Month’s Expenses</h2>
                        <p class="display-6 text-danger mb-0">$ <?php echo $exp ?></p>
                        <p class="text-muted">Budget used: 68%</p>
                    </section>
                </section>
            </section>

            <!-- Charts / tables row -->
            <section class="row g-3 mt-4">
                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Spending by Category</h2>
                        <p class="text-muted">
                            (Chart placeholder – connect Chart.js later)
                        </p>
                        <ul class="list-group">
                            <?php
                                $ss = count($myExp);
                                for ($i=0; $i < $ss; $i++){
                                    echo '<li class="list-group-item d-flex justify-content-between">';
                                    echo "<span>" .$myExp[$i][0] ."</span><span>$" .$myExp[$i][1]. "</span>";
                                    echo '</li>';
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
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT t.Transaction_date as dat, a.account_type as acc, t.transaction_category as cat, t.transaction_amount as amo FROM monery_transactions t, money_account a "; 
                                $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and t.to_account_id =  a.sys_account_id order by t.transaction_date desc";
                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    $i =0;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr><td>" .$row["dat"]."</td>";
                                        echo "<td>" .$row["acc"]."</td>";
                                        echo "<td>" .$row["cat"]."</td>";
                                        echo "<td class=\"text-end\">" .$row["amo"]."</td></tr>";
                                        $i = $i+1;
                                        if($i > 10) { break; }
                                    }
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

