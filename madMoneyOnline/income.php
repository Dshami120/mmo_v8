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
    <title>Income - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Your chart code -->
    <script src="js/charts.js"></script>
</head>

<body class="bg-light">
  <?php
        $msg = '';
        require  'includes/dbOperations.php';
        // Check if form data was submitted via POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //get data out
            $accountID = $_SESSION['account_id'];
            $accID     = $_POST['accID'];
            $transDate = $_POST['transDate'];
            $transAmt  = $_POST['transAmt'];
            $transDesc = $_POST['transDesc'];
            $cateG     = $_POST['cateG'];
            // lets add account to DB
            if ($stmt = $con->prepare('INSERT INTO monery_transactions (SYS_USER_ID, TO_ACCOUNT_ID, TRANSACTION_DATE, TRANSACTION_AMOUNT, TRANSACTION_DESC, TRANSACTION_CATEGORY) values(?, ?, ?, ?, ?, ? )' ) ) {
                // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                $stmt->bind_param('issdss', $accountID, $accID, $transDate, $transAmt, $transDesc, $cateG);
               
                if ($stmt->execute()) {
                    // Account created, we are good, send to login page
                    $msg="Income booked";
                } else {
                    $msg="Income booking Failed. Please retry.";
                }
                // Close the prepared statement
                $stmt->close();
            }
            $con->close();
        }    

   ?>    
<main class="container-fluid">
    <section class="row min-vh-100">

        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
            <?php
                include 'nav.html';
            ?>

        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Income</h1>
            <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>

            <section class="row g-3">
                <section class="col-12 col-lg-5">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Add Income</h2>

                        <form action="income.php" method="post">
                            <label class="form-label">Account</label>
                            <select class="form-select mb-3" name="accID" id="accID">
                            <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT sys_account_id, account_type, account_name  FROM  money_account ";
                                $sql = $sql."where sys_user_id = ".$_SESSION['account_id']." and account_type =\"Income\" "; 
                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value=" .$row["sys_account_id"].">".$row["account_type"]."-> ".$row["account_name"] ."</option>";
                                    }
                                }
                                mysqli_close($con);
                            ?>
                            </select>

                            <label class="form-label">Transaction Category</label>
                            <select class="form-select mb-3" id="cateG" name="cateG">
                            <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT account_category_name as accC FROM  money_category order by account_category_name"; 
                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value=\"" .$row["accC"]."\">".$row["accC"]."</option>";
                                    }
                                }
                                mysqli_close($con);
                            ?>
                            </select>

                            <label class="form-label">Description</label>
                            <input type="text" class="form-control mb-3" id="transDesc" name="transDesc" placeholder="e.g., Salary, Freelance">

                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control mb-3"  id="transAmt" name="transAmt" placeholder="0.00">

                            <label class="form-label">Date</label>
                            <input type="date" class="form-control mb-3"  id="transDate" name="transDate" >

                            <button type="button" class="btn btn-primary w-100" onClick="validate(this);">Save Income</button>
                        </form>
                    </section>
                </section>

                <section class="col-12 col-lg-7">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Recent Income</h2>
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
                                $sql = $sql ." where t.sys_user_id =".$_SESSION['account_id']." and a.account_type in (\"Income\", \"Saving\", \"Investment\") ";
                                $sql = $sql ." and t.to_account_id =  a.sys_account_id order by t.transaction_date desc";
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

                    <!-- CHART UNDER TABLE (INSIDE SAME RIGHT COLUMN) -->
                    <section class="mt-4 border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Income Over Time</h2>
                        <canvas id="incomeChart"></canvas>
                    </section>

                </section>
            </section>

        </section>

    </section>
</main>
<script>
    function validate(butt) {

        var inputValue = $("#transAmt").val();
        if( $("#accID").val() == ""){
            alert("Please select Income Account");
            return;
        }
        if( $("#transDate").val() == ""){
            alert("Please select transaction date");
            return;
        }

        if( ! ($.isNumeric(inputValue)) )  {
            alert("Invalid transaction amount");
            return;
        }
        
        butt.form.submit();
    }
</script>
<!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>   
</body>
</html>

