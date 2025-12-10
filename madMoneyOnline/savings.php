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
        <?php
            include 'nav.html';
        
        ?>
        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Savings Goals</h1>

            <section class="row g-3">
                <section class="col-12 col-lg-5">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Add Savings Goal</h2>

                        <form>
                            <label class="form-label">Goal Name</label>
                            <input type="text" class="form-control mb-3" placeholder="e.g., Emergency Fund">

                            <label class="form-label">Target Amount</label>
                            <input type="number" class="form-control mb-3" placeholder="0.00">

                            <label class="form-label">Target Date</label>
                            <input type="date" class="form-control mb-3">

                            <label class="form-label">Current Saved</label>
                            <input type="number" class="form-control mb-3" placeholder="0.00">

                            <button type="submit" class="btn btn-primary w-100">Save Goal</button>
                        </form>
                    </section>
                </section>

                <section class="col-12 col-lg-7">
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
                            <tr>
                                <td>Emergency Fund</td>
                                <td>$1,200</td>
                                <td>$3,000</td>
                                <td>40%</td>
                            </tr>
                            <tr>
                                <td>Vacation</td>
                                <td>$400</td>
                                <td>$2,000</td>
                                <td>20%</td>
                            </tr>
                            <tr>
                                <td>New Laptop</td>
                                <td>$700</td>
                                <td>$1,000</td>
                                <td>70%</td>
                            </tr>
                            </tbody>
                        </table>
                    </section>

                    <!-- CHART UNDER TABLE (INSIDE SAME RIGHT COLUMN) -->
                    <section class="mt-4 border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Savings Progress Overview</h2>
                        <canvas id="savingsChart"></canvas>
                    </section>

                </section>
            </section>

        </section>

    </section>
</main>

</body>
</html>

