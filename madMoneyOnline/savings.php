<?php
ob_start();
session_start();

if (!isset($_SESSION['account_loggedin'])) {
    header("Location: index.html");
    exit;
}

require "includes/dbOperations.php";

$msg = "";
$savingsData = [];
$chartLabels = [];
$chartValues = [];

/* ============================================================
   1. CREATE SAVINGS GOAL
============================================================ */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["createGoal"])) {

    $uid        = $_SESSION["account_id"];
    $goalName   = $_POST["goalName"];
    $targetAmt  = $_POST["targetAmt"];
    $savedAmt   = $_POST["savedAmt"];
    $targetDate = $_POST["targetDate"];

    $sql = "INSERT INTO money_savings_goals
            (sys_user_id, goal_name, target_amount, current_saved, target_date)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("isdds", $uid, $goalName, $targetAmt, $savedAmt, $targetDate);

    if ($stmt->execute()) {
        $msg = "Savings goal added!";
    } else {
        $msg = "Could not add goal.";
    }

    $stmt->close();
    
}


/* ============================================================
   2. ADD MONEY TO SAVINGS GOAL
============================================================ */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addMoney"])) {

    $goalID = $_POST["goal_id"];
    $amount = floatval($_POST["amountAdd"]);

    $sql = "UPDATE money_savings_goals 
            SET current_saved = current_saved + ?
            WHERE goal_id=? AND sys_user_id=?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("dii", $amount, $goalID, $_SESSION["account_id"]);

    if ($stmt->execute()) {
        $msg = "Money added to goal!";
    } else {
        $msg = "Error updating savings.";
    }

    $stmt->close();
}

/* ============================================================
   3. UPDATE (EDIT) SAVINGS GOAL
============================================================ */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateGoal"])) {

    $id         = $_POST["goal_id"];
    $goalName   = $_POST["goalName"];
    $targetAmt  = $_POST["targetAmt"];
    $savedAmt   = $_POST["savedAmt"];
    $targetDate = $_POST["targetDate"];

    $sql = "UPDATE money_savings_goals
            SET goal_name=?, target_amount=?, current_saved=?, target_date=?
            WHERE goal_id=? AND sys_user_id=?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("sddssi", $goalName, $targetAmt, $savedAmt, $targetDate, $id, $_SESSION["account_id"]);
    $stmt->execute();
    $stmt->close();

    $msg = "Goal updated!";
}

/* ============================================================
   4. DELETE SAVINGS GOAL
============================================================ */
if (isset($_GET["delete"])) {

    $id = $_GET["delete"];

    $sql = "DELETE FROM money_savings_goals
            WHERE goal_id=? AND sys_user_id=?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $id, $_SESSION["account_id"]);
    $stmt->execute();
    $stmt->close();

    $msg = "Goal deleted!";
}

/* ============================================================
   5. FETCH ALL SAVINGS GOALS
============================================================ */
$sql = "SELECT * FROM money_savings_goals 
        WHERE sys_user_id=".$_SESSION["account_id"]."
        ORDER BY created_at DESC";

$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $percent = ($row["target_amount"] > 0)
            ? round(($row["current_saved"] / $row["target_amount"]) * 100, 1)
            : 0;

        $savingsData[] = [
            "id"      => $row["goal_id"],
            "goal"    => $row["goal_name"],
            "saved"   => $row["current_saved"],
            "target"  => $row["target_amount"],
            "percent" => $percent,
            "tdate"   => $row["target_date"],
            "cdate"   => $row["created_at"]
        ];

        $chartLabels[] = $row["goal_name"];
        $chartValues[] = $percent;
    }
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Savings - Mad Money Online</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<main class="container-fluid">
<section class="row min-vh-100">

<!-- SIDEBAR -->
<nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
    <?php include "nav.html"; ?>
</nav>

<!-- CONTENT -->
<section class="col-12 col-md-9 col-lg-10 p-4">

<h1 class="mb-4">Savings Goals</h1>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<section class="row g-3">

<!-- LEFT: ADD GOAL -->
<section class="col-12 col-lg-5">
    <section class="border rounded bg-white p-3 mb-4">

        <h2 class="h5 mb-3">Add Savings Goal</h2>

        <form action="savings.php" method="post">
            <input type="hidden" name="createGoal" value="1">

            <label class="form-label">Goal Name</label>
            <input name="goalName" class="form-control mb-3" placeholder="ex: Emergency Fund" required>

            <label class="form-label">Target Amount</label>
            <input type="number" step="0.01" name="targetAmt" class="form-control mb-3" placeholder="ex: $1000" required>

            <label class="form-label">Current Saved</label>
            <input type="number" step="0.01" name="savedAmt" class="form-control mb-3" placeholder="ex: $100" required>

            <label class="form-label">Target Date</label>
            <input type="date" name="targetDate" class="form-control mb-3">

            <button class="btn btn-primary w-100">Save Goal</button>
        </form>

    </section>
</section>

<!-- RIGHT: TABLE + CHART -->
<section class="col-12 col-lg-7">

<!-- TABLE -->
<section class="border rounded bg-white p-3">

    <h2 class="h5 mb-3">Your Savings Goals</h2>

    <table class="table table-sm align-middle">
        <thead>
        <tr>
            <th>Goal</th>
            <th>Saved</th>
            <th>Target</th>
            <th>Progress</th>
            <th>Target Date</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php if (count($savingsData) == 0): ?>
            <tr><td colspan="6" class="text-center text-muted">No savings goals yet.</td></tr>
        <?php else: ?>
            <?php foreach ($savingsData as $g): ?>
            <tr>
                <td><?= $g["goal"] ?></td>
                <td>$<?= number_format($g["saved"], 2) ?></td>
                <td>$<?= number_format($g["target"], 2) ?></td>
                <td><?= $g["percent"] ?>%</td>
                <td><?= $g["tdate"] ?: "-" ?></td>

                <td>
                    <!-- ADD MONEY BUTTON -->
                    <button class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addMoney<?= $g["id"] ?>">
                        Add Money
                    </button>

                    <!-- EDIT BUTTON -->
                    <button class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $g["id"] ?>">
                        Edit
                    </button>

                    <!-- DELETE BUTTON -->
                    <a href="savings.php?delete=<?= $g["id"] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this goal?');">
                        Delete
                    </a>
                </td>
            </tr>

            <!-- ADD MONEY MODAL -->
            <div class="modal fade" id="addMoney<?= $g["id"] ?>">
                <div class="modal-dialog">
                    <div class="modal-content p-3">

                        <form action="savings.php" method="post">
                            <input type="hidden" name="addMoney" value="1">
                            <input type="hidden" name="goal_id" value="<?= $g["id"] ?>">

                            <h5>Add Money to <?= $g["goal"] ?></h5>

                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amountAdd" class="form-control mb-3" required>

                            <button class="btn btn-success w-100">Add Money</button>
                        </form>

                    </div>
                </div>
            </div>

            <!-- EDIT MODAL -->
            <div class="modal fade" id="editModal<?= $g["id"] ?>">
                <div class="modal-dialog">
                    <div class="modal-content p-3">

                        <form action="savings.php" method="post">
                            <input type="hidden" name="updateGoal" value="1">
                            <input type="hidden" name="goal_id" value="<?= $g["id"] ?>">

                            <h5>Edit Goal</h5>

                            <label class="form-label">Goal Name</label>
                            <input name="goalName" value="<?= $g["goal"] ?>" class="form-control mb-2">

                            <label class="form-label">Target Amount</label>
                            <input type="number" step="0.01" name="targetAmt" value="<?= $g["target"] ?>" class="form-control mb-2">

                            <label class="form-label">Current Saved</label>
                            <input type="number" step="0.01" name="savedAmt" value="<?= $g["saved"] ?>" class="form-control mb-2">

                            <label class="form-label">Target Date</label>
                            <input type="date" name="targetDate" value="<?= $g["tdate"] ?>" class="form-control mb-3">

                            <button class="btn btn-primary w-100">Save Changes</button>
                        </form>

                    </div>
                </div>
            </div>

            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

</section>

<!-- CHART -->
<section class="mt-4 border rounded bg-white p-3">
    <h2 class="h5 mb-3">Savings Progress Overview</h2>
    <canvas id="savingsChart1"></canvas>
</section>

</section>

</section>

</section>
</main>

<!-- CHART -->
<script>
<?php if (count($chartLabels) > 0): ?>
new Chart(document.getElementById("savingsChart1"), {
    type: "bar",
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: "Progress (%)",
            data: <?= json_encode($chartValues) ?>,
            backgroundColor: ["#3F51B5", "#00ACC1", "#E91E63", "#9C27B0", "#8BC34A", "#FFCA28"]
        }]
    }
});
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
