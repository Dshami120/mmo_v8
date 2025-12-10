<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Budgets - Mad Money Online</title>
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
            <h1 class="mb-4">Budgets</h1>

            <section class="row g-3">
                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Create / Edit Budget</h2>

                        <form>
                            <label class="form-label">Category</label>
                            <select class="form-select mb-3">
                                <option>Rent / Housing</option>
                                <option>Groceries</option>
                                <option>Transportation</option>
                                <option>Entertainment</option>
                                <option>Utilities</option>
                            </select>

                            <label class="form-label">Monthly Limit</label>
                            <input type="number" class="form-control mb-3" placeholder="0.00">

                            <label class="form-label">Target Date</label>
                            <input type="date" name="targetDate" class="form-control mb-3">

                            <button type="submit" class="btn btn-primary w-100">Save Budget</button>
                        </form>
                    </section>
                </section>

                <section class="col-12 col-lg-6">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Current Budgets</h2>

                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Category</th>
                                <th>Limit</th>
                                <th>Spent</th>
                                <th>Used</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Groceries</td>
                                <td>$400</td>
                                <td>$260</td>
                                <td>65%</td>
                            </tr>
                            <tr>
                                <td>Transportation</td>
                                <td>$150</td>
                                <td>$90</td>
                                <td>60%</td>
                            </tr>
                            <tr>
                                <td>Entertainment</td>
                                <td>$100</td>
                                <td>$70</td>
                                <td>70%</td>
                            </tr>
                            </tbody>
                        </table>
                    </section>

                    <!-- chart Under right column table -->
                    <section class="mt-4 border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Budget Usage Chart</h2>
                        <canvas id="budgetChart"></canvas>
                    </section>

                </section>
            </section>

        </section>

    </section>
</main>

</body>
</html>

