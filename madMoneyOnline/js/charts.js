document.addEventListener("DOMContentLoaded", () => {

    /* ======================================================
       DASHBOARD CHARTS
    ====================================================== */

    // ------------------------------------------------------
    // Spending by Category (Doughnut Chart)
    // ------------------------------------------------------
    const spendingCategoryCanvas = document.getElementById("spendingCategoryChart");
    if (spendingCategoryCanvas && typeof Chart !== "undefined") {
        new Chart(spendingCategoryCanvas, {
            type: "doughnut",
            data: {
                labels: ["Rent", "Groceries", "Transportation", "Entertainment"],
                datasets: [{
                    data: [900, 320, 180, 140],
                    backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#8BC34A"]
                }]
            }
        });
    }

    // ------------------------------------------------------
    // Income vs Expenses (Bar Chart)
    // ------------------------------------------------------
    const incomeExpenseCanvas = document.getElementById("incomeExpenseChart");
    if (incomeExpenseCanvas && typeof Chart !== "undefined") {
        new Chart(incomeExpenseCanvas, {
            type: "bar",
            data: {
                labels: ["Income", "Expenses"],
                datasets: [{
                    data: [3200, 2150],
                    backgroundColor: ["#4CAF50", "#F44336"]
                }]
            }
        });
    }



    /* ======================================================
       ACCOUNTS CHART (Horizontal Bar Chart)
       Dynamically filled using PHP variables:
       - accountLabels
       - accountValues
    ====================================================== */
    const accountsCanvas = document.getElementById("accountsChart");

    if (
        accountsCanvas &&
        typeof Chart !== "undefined" &&
        typeof accountLabels !== "undefined" &&
        Array.isArray(accountLabels) &&
        accountLabels.length > 0
    ) {

        // Large professional color palette
        const ACCOUNT_COLORS = [
            "#4A90E2", "#50E3C2", "#B8E986", "#F8E71C", "#F5A623",
            "#D0021B", "#9013FE", "#8B572A", "#417505", "#BD10E0",
            "#7ED321", "#F66A6A", "#1ABC9C", "#16A085", "#2ECC71",
            "#27AE60", "#3498DB", "#2980B9", "#9B59B6", "#8E44AD",
            "#F1C40F", "#F39C12", "#E67E22", "#D35400", "#E74C3C",
            "#C0392B", "#95A5A6", "#7F8C8D"
        ];

        new Chart(accountsCanvas.getContext("2d"), {
            type: "bar",
            data: {
                labels: accountLabels,
                datasets: [{
                    label: "Balance ($)",
                    data: accountValues,
                    backgroundColor: ACCOUNT_COLORS.slice(0, accountLabels.length),
                    barThickness: 10,       // Make bars thick
                    maxBarThickness: 40     // Prevent overly huge bars
                }]
            },

            options: {
                indexAxis: "y", // Converts bar chart to horizontal orientation

                responsive: true,

                plugins: {
                    title: {
                        display: true,
                        text: "Account Balance Comparison"
                    },
                    legend: {
                        display: false // Not needed for single dataset
                    }
                },

                scales: {
                    x: {
                        ticks: {
                            callback: value => "$" + value.toLocaleString()
                        }
                    }
                }
            }
        });
    }



    /* ======================================================
       BUDGET CHART
    ====================================================== */
    const budgetCanvas = document.getElementById("budgetChart");
    if (budgetCanvas && typeof Chart !== "undefined") {
        new Chart(budgetCanvas, {
            type: "bar",
            data: {
                labels: ["Groceries", "Transportation", "Entertainment"],
                datasets: [{
                    label: "Used %",
                    data: [65, 60, 70],
                    backgroundColor: ["#03A9F4", "#8BC34A", "#FF9800"]
                }]
            }
        });
    }



    /* ======================================================
       EXPENSES CHART
    ====================================================== */
    const expensesCanvas = document.getElementById("expensesChart");
    if (expensesCanvas && typeof Chart !== "undefined") {
        new Chart(expensesCanvas, {
            type: "bar",
            data: {
                labels: ["Groceries", "Utilities", "Transportation", "Entertainment"],
                datasets: [{
                    label: "Amount",
                    data: [260, 95, 90, 70],
                    backgroundColor: "#E91E63"
                }]
            }
        });
    }



    /* ======================================================
       INCOME CHART
    ====================================================== */
    const incomeCanvas = document.getElementById("incomeChart");
    if (incomeCanvas && typeof Chart !== "undefined") {
        new Chart(incomeCanvas, {
            type: "line",
            data: {
                labels: ["Nov 1", "Nov 7", "Nov 14"],
                datasets: [{
                    label: "Income",
                    data: [1200, 240, 1600],
                    borderColor: "#2196F3",
                    tension: 0.4 // Rounded lines
                }]
            }
        });
    }



    /* ======================================================
       SAVINGS CHART (DB-driven)
       Requires PHP to define:
       - savingsLabels
       - savingsPercents
    ====================================================== */
    const savingsCanvas = document.getElementById("savingsChart");
    if (
        savingsCanvas &&
        typeof Chart !== "undefined" &&
        typeof savingsLabels !== "undefined" &&
        Array.isArray(savingsLabels) &&
        savingsLabels.length > 0
    ) {
        new Chart(savingsCanvas, {
            type: "doughnut",
            data: {
                labels: savingsLabels.map((goal, i) =>
                    `${goal} (${savingsPercents[i]}%)`
                ),
                datasets: [{
                    data: savingsPercents,
                    backgroundColor: [
                        "#FF5722", "#009688", "#9C27B0",
                        "#03A9F4", "#8BC34A", "#FFC107"
                    ]
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: "Savings Progress (%)"
                    }
                }
            }
        });
    }



    /* ======================================================
       HISTORY CHART
    ====================================================== */
    const historyCanvas = document.getElementById("historyChart");
    if (historyCanvas && typeof Chart !== "undefined") {
        new Chart(historyCanvas, {
            type: "line",
            data: {
                labels: ["Nov 13", "Nov 14", "Nov 15"],
                datasets: [{
                    data: [-95.20, 1600, -52.80],
                    borderColor: "#673AB7",
                    tension: 0.3
                }]
            }
        });
    }



    /* ======================================================
       INVESTMENTS POLAR AREA (DB-driven)
       Requires PHP variables:
       - investmentLabels
       - investmentValues
    ====================================================== */
    const investmentsPolarCanvas = document.getElementById("investmentsPolar");
    if (
        investmentsPolarCanvas &&
        typeof Chart !== "undefined" &&
        typeof investmentLabels !== "undefined" &&
        Array.isArray(investmentLabels) &&
        investmentLabels.length > 0
    ) {
        const total = investmentValues.reduce((a, b) => a + b, 0);
        const percentages = investmentValues.map(v =>
            ((v / total) * 100).toFixed(1)
        );

        new Chart(investmentsPolarCanvas, {
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
