<?php
ob_start();
session_start();

if (!(isset($_SESSION['account_loggedin']))) {
    header('Location: index.html');
    exit;
}

$msg = '';

require 'includes/dbOperations.php';


// -----------------------------------------------------
// HANDLE FORM SUBMISSION (ADD INVESTMENT)
// -----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userID     = $_SESSION['account_id'];
    $accID      = $_POST['invAccID'];
    $assetName  = $_POST['assetName'];
    $assetType  = $_POST['assetType'];
    $amount     = $_POST['amountInvested'];
    $invDate    = $_POST['invDate'];

    if ($stmt = $con->prepare(
        "INSERT INTO investments 
         (sys_user_id, account_id, asset_name, asset_type, amount_invested, created_at)
         VALUES (?, ?, ?, ?, ?, ?)"
    )) {
        $stmt->bind_param(
            "iissds",
            $userID,
            $accID,
            $assetName,
            $assetType,
            $amount,
            $invDate
        );

        if ($stmt->execute()) {
            $msg = "Investment added successfully.";
        } else {
            $msg = "Failed to add investment.";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Investments - Mad Money Online</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>


<body class="bg-light">

<main class="container-fluid">
    <section class="row min-vh-100">

        <!-- NAVIGATION -->
        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
            <?php include 'nav.html'; ?>
        </nav>


        <!-- MAIN CONTENT -->
        <section class="col-12 col-md-9 col-lg-10 p-4">

            <h1 class="mb-4">Investments</h1>
            <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>


            <section class="row g-3">

                <!-- LEFT FORM -->
                <section class="col-12 col-lg-5">
                    <section class="border rounded p-3 bg-white mb-4">

                        <h2 class="h5 mb-3">Add Investment</h2>

                        <form action="investments.php" method="post">

                            <!-- Investment Accounts -->
                            <label class="form-label">Investment Account</label>
                            <select class="form-select mb-3" name="invAccID" required>
                                <?php
                                $sql = "SELECT sys_account_id, account_name 
                                        FROM money_account 
                                        WHERE sys_user_id = ".$_SESSION['account_id']."
                                        AND account_type = 'Investment'";

                                $result = mysqli_query($con, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='".$row["sys_account_id"]."'>"
                                                .$row["account_name"].
                                             "</option>";
                                    }
                                } else {
                                    echo "<option>No Investment Accounts Found</option>";
                                }
                                ?>
                            </select>

                            <label class="form-label">Asset Name</label>
                            <input type="text" class="form-control mb-3" 
                                   name="assetName" placeholder="VOO, BTC, AAPL" required>

                            <label class="form-label">Asset Type</label>
                            <select class="form-select mb-3" name="assetType" required>
                                <option>Stock</option>
                                <option>ETF</option>
                                <option>Crypto</option>
                                <option>Bond</option>
                                 <option>Business</option>
                                 <option>Real Estate</option>
                                 <option>Precious Metals</option>
                                 <option>Gold</option>
                                 <option>Silver</option>
                                <option>Other</option>
                            </select>

                            <label class="form-label">Amount Invested</label>
                            <input type="number" step="0.01" 
                                   class="form-control mb-3" 
                                   name="amountInvested" required>

                            <label class="form-label">Date</label>
                            <input type="date" class="form-control mb-3" 
                                   name="invDate" required>

                            <button type="submit" class="btn btn-primary w-100">
                                Save Investment
                            </button>

                        </form>
                    </section>
                </section>



                <!-- RIGHT COLUMN -->
                <section class="col-12 col-lg-7">

                    <!-- RECENT INVESTMENTS -->
                    <section class="border rounded p-3 bg-white">

                        <h2 class="h5 mb-3">Recent Investments</h2>

                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <!-- <th>purchase Value</th> -->
                                     <!-- <th>Current Value</th> -->
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $sql = "SELECT i.created_at, a.account_name, 
                                               i.asset_name, i.asset_type, 
                                               i.amount_invested
                                        FROM investments i 
                                        JOIN money_account a 
                                          ON i.account_id = a.sys_account_id
                                        WHERE i.sys_user_id = ".$_SESSION['account_id']."
                                        ORDER BY i.created_at DESC";

                                $result = mysqli_query($con, $sql);
                                $count = 0;

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {

                                        echo "<tr>
                                                <td>".$row["created_at"]."</td>
                                                <td>".$row["account_name"]."</td>
                                                <td>".$row["asset_name"]."</td>
                                                <td>".$row["asset_type"]."</td>
                                                <td class='text-end'>$".$row["amount_invested"]."</td>
                                              </tr>";

                                        $count++;
                                        if ($count >= 10) break;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>

                    </section>

                    <!-- SECOND CHART (POLAR AREA) -->
                    <section class="mt-4 border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Portfolio Distribution</h2>
                        <canvas id="investmentsPolar"></canvas>
                    </section>

                </section>
            </section>



            <?php
            // -----------------------------------------------------
            // BUILD DATA FOR BOTH CHARTS
            // -----------------------------------------------------
            $labels = [];
            $values = [];

            $sql = "SELECT asset_name, SUM(amount_invested) AS total
                    FROM investments
                    WHERE sys_user_id = ".$_SESSION['account_id']."
                    GROUP BY asset_name
                    ORDER BY total DESC";

            $chartData = mysqli_query($con, $sql);

            if (mysqli_num_rows($chartData) > 0) {
                while ($row = mysqli_fetch_assoc($chartData)) {
                    $labels[] = $row["asset_name"];
                    $values[] = (float)$row["total"];
                }
            }

            mysqli_close($con);
            ?>

            <!-- Polar CHART -->
            <script>
                const polarCtx = document.getElementById("investmentsPolar").getContext("2d");

                new Chart(polarCtx, {
                    type: "polarArea",
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($values); ?>,
                            backgroundColor: [
                                "rgba(63,81,181,0.8)",
                                "rgba(0,172,193,0.8)",
                                "rgba(255,202,40,0.8)",
                                "rgba(233,30,99,0.8)",
                                "rgba(139,195,74,0.8)",
                                "rgba(156,39,176,0.8)"
                            ],
                            borderColor: "#fff",
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: "top" },
                            title: {
                                display: true,
                                text: "Investment Value Distribution"
                            }
                        }
                    }
                });
            </script>


        </section>
    </section>
</main>

</body>
</html>
