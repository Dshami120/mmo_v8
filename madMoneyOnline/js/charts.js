document.addEventListener("DOMContentLoaded", () => {

    /* ======================================================
       DASHBOARD CHARTS (unchanged from your version)
    ====================================================== */

    if (document.getElementById("spendingCategoryChart")) {
        new Chart(spendingCategoryChart, {
            type: 'doughnut',
            data: {
                labels: ['Rent', 'Groceries', 'Transportation', 'Entertainment'],
                datasets: [{
                    data: [900, 320, 180, 140],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8BC34A']
                }]
            }
        });
    }

    if (document.getElementById("incomeExpenseChart")) {
        new Chart(incomeExpenseChart, {
            type: 'bar',
            data: {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    data: [3200, 2150],
                    backgroundColor: ['#4CAF50', '#F44336']
                }]
            }
        });
    }

    if (document.getElementById("accountsChart")) {
        new Chart(accountsChart, {
            type: 'pie',
            data: {
                labels: ['Checking', 'Savings', 'Cash'],
                datasets: [{
                    data: [3200, 5600, 150],
                    backgroundColor: ['#42A5F5', '#66BB6A', '#FFA726']
                }]
            }
        });
    }

    if (document.getElementById("budgetChart")) {
        new Chart(budgetChart, {
            type: 'bar',
            data: {
                labels: ['Groceries', 'Transportation', 'Entertainment'],
                datasets: [{
                    label: 'Used %',
                    data: [65, 60, 70],
                    backgroundColor: ['#03A9F4', '#8BC34A', '#FF9800']
                }]
            }
        });
    }

    if (document.getElementById("expensesChart")) {
        new Chart(expensesChart, {
            type: 'bar',
            data: {
                labels: ['Groceries', 'Utilities', 'Transportation', 'Entertainment'],
                datasets: [{
                    label: 'Amount',
                    data: [260, 95, 90, 70],
                    backgroundColor: '#E91E63'
                }]
            }
        });
    }

    if (document.getElementById("incomeChart")) {
        new Chart(incomeChart, {
            type: 'line',
            data: {
                labels: ['Nov 1', 'Nov 7', 'Nov 14'],
                datasets: [{
                    label: 'Income',
                    data: [1200, 240, 1600],
                    borderColor: '#2196F3',
                    tension: 0.4
                }]
            }
        });
    }

    if (document.getElementById("savingsChart")) {
        new Chart(savingsChart, {
            type: 'doughnut',
            data: {
                labels: ['Emergency (40%)', 'Vacation (20%)', 'Laptop (70%)'],
                datasets: [{
                    data: [40, 20, 70],
                    backgroundColor: ['#FF5722', '#009688', '#9C27B0']
                }]
            }
        });
    }

    if (document.getElementById("historyChart")) {
        new Chart(historyChart, {
            type: 'line',
            data: {
                labels: ['Nov 13', 'Nov 14', 'Nov 15'],
                datasets: [{
                    data: [-95.20, 1600, -52.80],
                    borderColor: '#673AB7',
                    tension: 0.3
                }]
            }
        });
    }




    /* ======================================================
       INVESTMENTS PAGE – DB DRIVEN CHARTS ONLY
    ====================================================== */

    // RADAR CHART
    if (document.getElementById("investmentsChart") &&
        typeof investmentLabels !== "undefined" &&
        investmentLabels.length > 0) {

        new Chart(investmentsChart, {
            type: "radar",
            data: {
                labels: investmentLabels,
                datasets: [{
                    label: "Total Invested",
                    data: investmentValues,
                    backgroundColor: "rgba(63, 81, 181, 0.25)",
                    borderColor: "#3F51B5",
                    pointBackgroundColor: "#3F51B5"
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "top" },
                    title: {
                        display: true,
                        text: "Total Amount Invested by Asset"
                    }
                }
            }
        });
    }


    // POLAR AREA CHART
    if (document.getElementById("investmentsPolar") &&
        typeof investmentLabels !== "undefined" &&
        investmentLabels.length > 0) {

        const total = investmentValues.reduce((a, b) => a + b, 0);
        const percentages = investmentValues.map(v =>
            ((v / total) * 100).toFixed(1)
        );

        new Chart(investmentsPolar, {
            type: "polarArea",
            data: {
                labels: investmentLabels.map((name, i) => 
                    `${name} (${percentages[i]}%)`
                ),
                datasets: [{
                    data: investmentValues,
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
                        text: "Portfolio Value Distribution"
                    }
                }
            }
        });
    }

});
