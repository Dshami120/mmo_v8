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

        <nav class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3">
            <?php
                include 'nav.html';
            ?>

        </nav>

        <section class="col-12 col-md-9 col-lg-10 p-4">
            <h1 class="mb-4">Settings</h1>

            <section class="row g-3">
                <section class="col-12 col-lg-5">
                    <section class="border rounded p-3 bg-white mb-4">
                        <h2 class="h5 mb-3">Add Setting</h2>

                        <form>
                            <label class="form-label">Asset Name</label>
                            <input type="text" class="form-control mb-3" placeholder="e.g., VOO, BTC, AAPL">

                            <label class="form-label">Type</label>
                            <select class="form-select mb-3">
                                <option>Stock</option>
                                <option>ETF</option>
                                <option>Crypto</option>
                                <option>Bond</option>
                                <option>Other</option>
                            </select>

                            <label class="form-label">Current Value</label>
                            <input type="number" class="form-control mb-3" placeholder="0.00">

                            <button type="submit" class="btn btn-primary w-100">Save Investment</button>
                        </form>
                    </section>
                </section>

                <section class="col-12 col-lg-7">
                    <section class="border rounded p-3 bg-white">
                        <h2 class="h5 mb-3">Portfolio Snapshot</h2>

                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Type</th>
                                <th class="text-end">Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>VOO</td>
                                <td>ETF</td>
                                <td class="text-end">$2,500</td>
                            </tr>
                            <tr>
                                <td>AAPL</td>
                                <td>Stock</td>
                                <td class="text-end">$1,200</td>
                            </tr>
                            <tr>
                                <td>BTC</td>
                                <td>Crypto</td>
                                <td class="text-end">$900</td>
                            </tr>
                            </tbody>
                        </table>

                        <p class="text-muted mt-2">
                            (Chart placeholder – portfolio allocation)
                        </p>
                    </section>
                </section>
            </section>

        </section>

    </section>
</main>

</body>
</html>

