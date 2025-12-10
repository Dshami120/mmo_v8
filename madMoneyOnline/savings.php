<?php
ob_start();
session_start();

if (!(isset($_SESSION['account_loggedin']))) {
    header('Location: index.html');
    exit;
}

require 'includes/dbOperations.php';

// =====================================================
// FETCH SAVINGS GOALS (Saving Accounts)
// =====================================================

$savingsLabels = [];
$savingsPercents = [];
$savingsData = [];

$sql = "
    SELECT 
        ACCOUNT_NAME,
        ACCOUNT_START_BALANCE AS saved,
        MONTHLY_LIMIT AS target
    FROM money_account
    WHERE sys_user_id = {$_SESSION['account_id']}
      AND account_type = 'Saving'
";

$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        $goal = $row['ACCOUNT_NAME'];
        $saved = floatval($row['saved']);
        $target = floatval($row['target']);
        $percent = $target > 0 ? round(($saved / $target) * 100, 1) : 0;

        // Table data
        $savingsData[] = [
            "goal" => $goal,
            "saved" => $saved,
            "target" => $target,
            "percent" => $percent
        ];

        // Chart data
        $savingsLabels[] = $goal;
        $savingsPercents[] = $percent;
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Savings - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/charts.js"></script>
</head>

<body class="bg-light">

<main class="container-fluid">
    <section class="row min-vh-100">

        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
        <?php include 'nav.html'; ?>
        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Savings Goals</h1>

            <section class="row g-3">

                <!-- LEFT: ADD GOAL FORM -->
                <section class="col-12 col-lg-5">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Add Savings Goal</h2>

                        <form action="savings_add.php" method="post">
                            <label class="form-label">Goal Name</label>
                            <input type="text" name="goalName" class="form-control mb-3" placeholder="e.g., Emergency Fund" required>

                            <label class="form-label">Target Amount</label>
                            <input type="number" name="targetAmt" class="form-control mb-3" placeholder="0.00" required>

                            <label class="form-label">Target Date</label>
                            <input type="date" name="targetDate" class="form-control mb-3">

                            <label class="form-label">Current Saved</label>
                            <input type="number" name="savedAmt" class="form-control mb-3" placeholder="0.00" required>

                            <button type="submit" class="btn btn-primary w-100">Save Goal</button>
                        </form>
                    </section>
                </section>

                <!-- RIGHT: SAVINGS TABLE + CHART -->
                <section class="col-12 col-lg-7">

                    <!-- TABLE OF SAVINGS -->
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Your Goals</h2>

                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Goal</th>
                                <th>Saved</th>
                                <th>Target</th>
                                <th>Progress</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php if (count($savingsData) === 0) { ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No savings goals added yet.
                                    </td>
                                </tr>
                            <?php } else {
                                foreach ($savingsData as $g) {
                                    echo "<tr>";
                                    echo "<td>{$g['goal']}</td>";
                                    echo "<td>$" . number_format($g['saved'], 2) . "</td>";
                                    echo "<td>$" . number_format($g['target'], 2) . "</td>";
                                    echo "<td>{$g['percent']}%</td>";
                                    echo "</tr>";
                                }
                            } ?>

                            </tbody>
                        </table>
                    </section>

                    <!-- CHART -->
                    <section class="mt-4 border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Savings Progress Overview</h2>
                        <canvas id="savingsChart"></canvas>
                    </section>

                </section>
            </section>

        </section>

    </section>
</main>

<!-- PASS DATA TO JS -->
<script>
    const savingsLabels = <?php echo json_encode($savingsLabels); ?>;
    const savingsPercents = <?php echo json_encode($savingsPercents); ?>;
</script>

</body>
</html>
