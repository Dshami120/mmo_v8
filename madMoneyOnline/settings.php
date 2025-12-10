<?php
ob_start();
session_start();

// Require login (same pattern as other pages)
if (!(isset($_SESSION['account_loggedin']))) {
    header('Location: index.html');
    exit;
}

require 'includes/dbOperations.php';

$accountId = $_SESSION['account_id'];
$msg = '';

// Grab "editing" IDs from GET (to pre-fill forms)
$editTypeId     = isset($_GET['edit_type'])     ? (int)$_GET['edit_type'] : 0;
$editCatId      = isset($_GET['edit_cat'])      ? (int)$_GET['edit_cat']  : 0;
$editAccountId  = isset($_GET['edit_account'])  ? (int)$_GET['edit_account'] : 0;

// Pre-load data for account editing, if needed
$editAccountRow = null;
if ($editAccountId > 0) {
    if ($stmt = $con->prepare(
        "SELECT SYS_ACCOUNT_ID, ACCOUNT_TYPE, ACCOUNT_NAME, MONTHLY_LIMIT, ACCOUNT_START_BALANCE
         FROM money_account
         WHERE SYS_USER_ID = ? AND SYS_ACCOUNT_ID = ?"
    )) {
        $stmt->bind_param('ii', $accountId, $editAccountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $editAccountRow = $result->fetch_assoc();
        $stmt->close();
    }
}

// ----------------------
// HANDLE FORM SUBMISSIONS
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    /* ======================
       ACCOUNT TYPE SAVE / DELETE
       ====================== */
    if ($formType === 'account_type_save') {
        $name = trim($_POST['account_type_name'] ?? '');
        $id   = isset($_POST['account_type_id']) ? (int)$_POST['account_type_id'] : 0;

        if ($name !== '') {
            if ($id > 0) {
                // UPDATE
                if ($stmt = $con->prepare(
                    "UPDATE money_account_type
                     SET ACCOUNT_TYPE_NAME = ?
                     WHERE SYS_ACCOUNT_TYPE_ID = ?"
                )) {
                    $stmt->bind_param('si', $name, $id);
                    if ($stmt->execute()) {
                        $msg = "Account type updated.";
                    } else {
                        $msg = "Failed to update account type.";
                    }
                    $stmt->close();
                }
            } else {
                // INSERT
                if ($stmt = $con->prepare(
                    "INSERT INTO money_account_type (ACCOUNT_TYPE_NAME)
                     VALUES (?)"
                )) {
                    $stmt->bind_param('s', $name);
                    if ($stmt->execute()) {
                        $msg = "Account type added.";
                    } else {
                        $msg = "Failed to add account type.";
                    }
                    $stmt->close();
                }
            }
        } else {
            $msg = "Account type name cannot be empty.";
        }

    } elseif ($formType === 'account_type_delete') {
        $id = isset($_POST['account_type_id']) ? (int)$_POST['account_type_id'] : 0;
        if ($id > 0) {
            if ($stmt = $con->prepare(
                "DELETE FROM money_account_type
                 WHERE SYS_ACCOUNT_TYPE_ID = ?"
            )) {
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $msg = "Account type deleted.";
                } else {
                    $msg = "Failed to delete account type.";
                }
                $stmt->close();
            }
        }

    /* ======================
       CATEGORY SAVE / DELETE
       ====================== */
    } elseif ($formType === 'category_save') {
        $name = trim($_POST['category_name'] ?? '');
        $id   = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        if ($name !== '') {
            if ($id > 0) {
                // UPDATE
                if ($stmt = $con->prepare(
                    "UPDATE money_category
                     SET ACCOUNT_CATEGORY_NAME = ?
                     WHERE SYS_ACCOUNT_CATEGORY_ID = ?"
                )) {
                    $stmt->bind_param('si', $name, $id);
                    if ($stmt->execute()) {
                        $msg = "Category updated.";
                    } else {
                        $msg = "Failed to update category.";
                    }
                    $stmt->close();
                }
            } else {
                // INSERT
                if ($stmt = $con->prepare(
                    "INSERT INTO money_category (ACCOUNT_CATEGORY_NAME)
                     VALUES (?)"
                )) {
                    $stmt->bind_param('s', $name);
                    if ($stmt->execute()) {
                        $msg = "Category added.";
                    } else {
                        $msg = "Failed to add category.";
                    }
                    $stmt->close();
                }
            }
        } else {
            $msg = "Category name cannot be empty.";
        }

    } elseif ($formType === 'category_delete') {
        $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        if ($id > 0) {
            if ($stmt = $con->prepare(
                "DELETE FROM money_category
                 WHERE SYS_ACCOUNT_CATEGORY_ID = ?"
            )) {
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $msg = "Category deleted.";
                } else {
                    $msg = "Failed to delete category.";
                }
                $stmt->close();
            }
        }

    /* ======================
       ACCOUNT SAVE / DELETE
       ====================== */
    } elseif ($formType === 'account_save') {
        $id         = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        $accType    = trim($_POST['accType'] ?? '');
        $accName    = trim($_POST['accName'] ?? '');
        $budget     = isset($_POST['budget']) ? (float)$_POST['budget'] : 0.0;
        $startBal   = isset($_POST['stBal'])  ? (float)$_POST['stBal']  : 0.0;

        if ($accType !== '' && $accName !== '') {
            if ($id > 0) {
                // UPDATE existing account
                if ($stmt = $con->prepare(
                    "UPDATE money_account
                     SET ACCOUNT_TYPE = ?, ACCOUNT_NAME = ?, MONTHLY_LIMIT = ?, ACCOUNT_START_BALANCE = ?
                     WHERE SYS_ACCOUNT_ID = ? AND SYS_USER_ID = ?"
                )) {
                    $stmt->bind_param('ssdsii',
                        $accType,
                        $accName,
                        $budget,
                        $startBal,
                        $id,
                        $accountId
                    );
                    if ($stmt->execute()) {
                        $msg = "Account updated.";
                    } else {
                        $msg = "Failed to update account.";
                    }
                    $stmt->close();
                }
            } else {
                // INSERT new account (same as accounts.php)
                if ($stmt = $con->prepare(
                    "INSERT INTO money_account
                     (SYS_USER_ID, ACCOUNT_TYPE, MONTHLY_LIMIT, ACCOUNT_NAME, ACCOUNT_START_BALANCE)
                     VALUES (?, ?, ?, ?, ?)"
                )) {
                    $stmt->bind_param('isdsd',
                        $accountId,
                        $accType,
                        $budget,
                        $accName,
                        $startBal
                    );
                    if ($stmt->execute()) {
                        $msg = "Account created.";
                    } else {
                        $msg = "Failed to create account.";
                    }
                    $stmt->close();
                }
            }
        } else {
            $msg = "Account type and name cannot be empty.";
        }

    } elseif ($formType === 'account_delete') {
        $id = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        if ($id > 0) {
            if ($stmt = $con->prepare(
                "DELETE FROM money_account
                 WHERE SYS_ACCOUNT_ID = ? AND SYS_USER_ID = ?"
            )) {
                $stmt->bind_param('ii', $id, $accountId);
                if ($stmt->execute()) {
                    $msg = "Account deleted.";
                } else {
                    $msg = "Failed to delete account.";
                }
                $stmt->close();
            }
        }
    }
}

// ----------------------
// LOAD DATA FOR DISPLAY
// ----------------------

// Account types
$accountTypes = [];
$result = mysqli_query(
    $con,
    "SELECT SYS_ACCOUNT_TYPE_ID, ACCOUNT_TYPE_NAME
     FROM money_account_type
     ORDER BY ACCOUNT_TYPE_NAME"
);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $accountTypes[] = $row;
    }
}

// Categories
$categories = [];
$result = mysqli_query(
    $con,
    "SELECT SYS_ACCOUNT_CATEGORY_ID, ACCOUNT_CATEGORY_NAME
     FROM money_category
     ORDER BY ACCOUNT_CATEGORY_NAME"
);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}

// Accounts (for this user)
$accounts = [];
if ($stmt = $con->prepare(
    "SELECT SYS_ACCOUNT_ID, ACCOUNT_TYPE, ACCOUNT_NAME, MONTHLY_LIMIT, ACCOUNT_START_BALANCE
     FROM money_account
     WHERE SYS_USER_ID = ?
     ORDER BY ACCOUNT_NAME"
)) {
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $accounts[] = $row;
    }
    $stmt->close();
}

// For pre-filling account form if editing
$accFormId      = $editAccountRow['SYS_ACCOUNT_ID']        ?? 0;
$accFormType    = $editAccountRow['ACCOUNT_TYPE']          ?? '';
$accFormName    = $editAccountRow['ACCOUNT_NAME']          ?? '';
$accFormBudget  = $editAccountRow['MONTHLY_LIMIT']         ?? '';
$accFormStBal   = $editAccountRow['ACCOUNT_START_BALANCE'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Mad Money Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h1 class="mb-4">Settings</h1>
            <?php if ($msg !== ''): ?>
                <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <section class="row g-4">

                <!-- ================== ACCOUNT TYPES ================== -->
                <section class="col-12 col-lg-4">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Account Types</h2>

                        <!-- Add / Edit Account Type -->
                        <?php
                        $editTypeName = '';
                        if ($editTypeId > 0) {
                            foreach ($accountTypes as $t) {
                                if ((int)$t['SYS_ACCOUNT_TYPE_ID'] === $editTypeId) {
                                    $editTypeName = $t['ACCOUNT_TYPE_NAME'];
                                    break;
                                }
                            }
                        }
                        ?>
                        <form method="post" class="mb-3">
                            <input type="hidden" name="form_type" value="account_type_save">
                            <input type="hidden" name="account_type_id" value="<?= $editTypeId ?>">

                            <label class="form-label">Type Name</label>
                            <input type="text"
                                   name="account_type_name"
                                   class="form-control mb-2"
                                   value="<?= htmlspecialchars($editTypeName) ?>"
                                   placeholder="Income, Expense, Investment, ...">

                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editTypeId ? 'Update Type' : 'Add Type' ?>
                            </button>
                        </form>

                        <!-- List of account types -->
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($accountTypes as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['ACCOUNT_TYPE_NAME']) ?></td>
                                    <td class="text-end">
                                        <a href="settings.php?edit_type=<?= (int)$t['SYS_ACCOUNT_TYPE_ID'] ?>"
                                           class="btn btn-sm btn-outline-secondary">Edit</a>

                                        <form method="post" class="d-inline"
                                              onsubmit="return confirm('Delete this account type?');">
                                            <input type="hidden" name="form_type" value="account_type_delete">
                                            <input type="hidden" name="account_type_id"
                                                   value="<?= (int)$t['SYS_ACCOUNT_TYPE_ID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                </section>

                <!-- ================== CATEGORIES ================== -->
                <section class="col-12 col-lg-4">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Categories</h2>

                        <?php
                        $editCatName = '';
                        if ($editCatId > 0) {
                            foreach ($categories as $c) {
                                if ((int)$c['SYS_ACCOUNT_CATEGORY_ID'] === $editCatId) {
                                    $editCatName = $c['ACCOUNT_CATEGORY_NAME'];
                                    break;
                                }
                            }
                        }
                        ?>

                        <!-- Add / Edit Category -->
                        <form method="post" class="mb-3">
                            <input type="hidden" name="form_type" value="category_save">
                            <input type="hidden" name="category_id" value="<?= $editCatId ?>">

                            <label class="form-label">Category Name</label>
                            <input type="text"
                                   name="category_name"
                                   class="form-control mb-2"
                                   value="<?= htmlspecialchars($editCatName) ?>"
                                   placeholder="Rent, Groceries, Salary, ...">

                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editCatId ? 'Update Category' : 'Add Category' ?>
                            </button>
                        </form>

                        <!-- List of categories -->
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['ACCOUNT_CATEGORY_NAME']) ?></td>
                                    <td class="text-end">
                                        <a href="settings.php?edit_cat=<?= (int)$c['SYS_ACCOUNT_CATEGORY_ID'] ?>"
                                           class="btn btn-sm btn-outline-secondary">Edit</a>

                                        <form method="post" class="d-inline"
                                              onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="form_type" value="category_delete">
                                            <input type="hidden" name="category_id"
                                                   value="<?= (int)$c['SYS_ACCOUNT_CATEGORY_ID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                </section>

                <!-- ================== ACCOUNTS ================== -->
                <section class="col-12 col-lg-4">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Accounts</h2>

                        <!-- Add / Edit Account -->
                        <form method="post" class="mb-3">
                            <input type="hidden" name="form_type" value="account_save">
                            <input type="hidden" name="account_id" value="<?= (int)$accFormId ?>">

                            <label class="form-label">Account Type</label>
                            <select class="form-select mb-2" name="accType" required>
                                <?php foreach ($accountTypes as $t): ?>
                                    <?php
                                    $val = $t['ACCOUNT_TYPE_NAME'];
                                    $selected = ($val === $accFormType) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($val) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="form-label">Account Name</label>
                            <input type="text"
                                   name="accName"
                                   class="form-control mb-2"
                                   value="<?= htmlspecialchars($accFormName) ?>"
                                   placeholder="Checking, Savings, Credit Card, ...">

                            <label class="form-label">Monthly Limit</label>
                            <input type="number" step="0.01"
                                   name="budget"
                                   class="form-control mb-2"
                                   value="<?= htmlspecialchars($accFormBudget) ?>">

                            <label class="form-label">Starting Balance</label>
                            <input type="number" step="0.01"
                                   name="stBal"
                                   class="form-control mb-3"
                                   value="<?= htmlspecialchars($accFormStBal) ?>">

                            <button type="submit" class="btn btn-primary w-100">
                                <?= $accFormId ? 'Update Account' : 'Add Account' ?>
                            </button>
                        </form>

                        <!-- List of accounts -->
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($accounts as $a): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($a['ACCOUNT_NAME']) ?><br>
                                        <small class="text-muted">
                                            Limit: $<?= htmlspecialchars($a['MONTHLY_LIMIT']) ?>,
                                            Start: $<?= htmlspecialchars($a['ACCOUNT_START_BALANCE']) ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($a['ACCOUNT_TYPE']) ?></td>
                                    <td class="text-end">
                                        <a href="settings.php?edit_account=<?= (int)$a['SYS_ACCOUNT_ID'] ?>"
                                           class="btn btn-sm btn-outline-secondary mb-1">Edit</a>

                                        <form method="post" class="d-inline"
                                              onsubmit="return confirm('Delete this account?');">
                                            <input type="hidden" name="form_type" value="account_delete">
                                            <input type="hidden" name="account_id"
                                                   value="<?= (int)$a['SYS_ACCOUNT_ID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                </section>

            </section> <!-- /.row -->
        </section> <!-- /.main content -->

    </section>
</main>

</body>
</html>
