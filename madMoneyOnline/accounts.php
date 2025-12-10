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
    <title>Accounts - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            $stBal     = $_POST['stBal'];
            $accType   = $_POST['accType'];
            $budget     = $_POST['budget'];
            $accName   = $_POST['accName'];
            // lets add account to DB
            if ($stmt = $con->prepare('INSERT INTO money_account (SYS_USER_ID, ACCOUNT_TYPE, MONTHLY_LIMIT, ACCOUNT_NAME, ACCOUNT_START_BALANCE) values(?, ?, ?, ?, ? )' ) ) {
                // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                $stmt->bind_param('isdsd', $accountID, $accType, $budget, $accName, $stBal);
               
                if ($stmt->execute()) {
                    // Account created, we are good, send to login page
                    $msg="Account Created";
                } else {
                    $msg="Account Creation Failed. Please retry.";
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
            <h1 class="mb-4">Accounts</h1>
            <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>

            <section class="row g-3">
                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Add / Edit Account</h2>

                        <form action="accounts.php" method="POST">
                            <label class="form-label">Account Type</label>
                            <select class="form-select mb-3" id="accType" name="accType">
                            <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT account_type_name as accT FROM  money_account_type order by account_type_name"; 
                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value=\"" .$row["accT"]."\">".$row["accT"]."</option>";
                                    }
                                }
                                mysqli_close($con);
                            ?>
                            </select>

                            <label class="form-label">Account Name</label>
                            <input type="text" class="form-control mb-3" id="accName" name="accName" placeholder="e.g., Checking, Savings">

                            <label class="form-label">Starting Balance</label>
                            <input type="text" class="form-control mb-3" id="stBal" name ="stBal" placeholder="0.00">

                            <label class="form-label">Budget</label>
                            <input type="text" class="form-control mb-3" id="budget" name ="budget" placeholder="0.00">


                            <button type="button" class="btn btn-primary w-100" onClick="validate(this);">Save Account</button>
                        </form>
                    </section>
                </section>

                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Your Accounts</h2>

                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <!-- <th>Category</th> -->
                                <th>Name</th>
                                <th class="text-end">Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                           <?php
                                require  'includes/dbOperations.php';
                                $sql = "SELECT account_type as accT, account_name as nam, account_start_balance as amo FROM  money_account  "; 
                                $sql = $sql ." where sys_user_id =".$_SESSION['account_id'] ." order by account_type ";
                                $result = mysqli_query($con, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    // Output data of each row
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr><td>" .$row["accT"]."</td>";
                                        //echo "<td>" .$row["cat"]."</td>";
                                        echo "<td>" .$row["nam"]."</td>";
                                        echo "<td class=\"text-end\">" .$row["amo"]."</td></tr>";
                                    }
                                }
                                mysqli_close($con);
                            ?>
                            </tbody>
                        </table>
                    </section>

                        <!-- chart Under right column table -->
                        <section class="mt-4 border rounded p-3 bg-white">
                            <h2 class="h5 mb-3">Account Balance Breakdown</h2>
                            <canvas id="accountsChart"></canvas>
                        </section>

                </section>
            </section>

        </section>

    </section>
</main>
<script>
    function validate(butt) {
        var inputValue = $("#stBal").val();
        var inputBug   = $("#budget").val();
        if( $("#accType").val() == ""){
            alert("Please select Account Type");
            return;
        }
        if( $("#accName").val() == ""){
            alert("Account name cannot be blank");
            return;
        }
        if( ! ($.isNumeric(inputValue)) )  {
            alert("Invalid starting balance");
            return;
        }
        if( ! ($.isNumeric(inputBug)) )  {
            alert("Invalid Budget value");
            return;
        }
        

        butt.form.submit();
    }
</script>
<!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>    
</body>
</html>

