// Example data fetched from a "database"
const data = {
    income: 7500,
    expenses: {
        total: 5000,
        categories: {
            Rent: 3000,
            Groceries: 1000,
            Utilities: 1000
        },
        transactions: [
            { id: "T001", date: "2024-10-01", source: "Rent", amount: 3000 },
            { id: "T002", date: "2024-10-05", source: "Groceries", amount: 1000 },
            { id: "T003", date: "2024-10-10", source: "Utilities", amount: 1000 }
        ]
    }
};

// Render the overview
function renderOverview(data) {
    const incomeBar = document.getElementById("incomeBar");
    const expenseBar = document.getElementById("expenseBar");
    const expenseCategoriesContainer = document.getElementById("expenseCategories");

    const maxAmount = Math.max(data.income, data.expenses.total);
    incomeBar.style.width = `${(data.income / maxAmount) * 100}%`;
    incomeBar.textContent = `$${data.income}`;
    expenseBar.style.width = `${(data.expenses.total / maxAmount) * 100}%`;
    expenseBar.textContent = `$${data.expenses.total}`;

    expenseCategoriesContainer.innerHTML = "";
    Object.keys(data.expenses.categories).forEach(category => {
        const categoryAmount = data.expenses.categories[category];
        const categoryWidth = (categoryAmount / data.expenses.total) * 100;

        const categorySection = document.createElement("div");
        categorySection.classList.add("bar-section");

        const label = document.createElement("div");
        label.classList.add("label");
        label.textContent = category;
        categorySection.appendChild(label);

        const bar = document.createElement("div");
        bar.classList.add("bar");

        const barFill = document.createElement("div");
        barFill.classList.add("bar-fill", "category");
        barFill.style.width = `${categoryWidth}%`;
        barFill.textContent = `$${categoryAmount}`;

        bar.appendChild(barFill);
        categorySection.appendChild(bar);
        expenseCategoriesContainer.appendChild(categorySection);
    });
}

// Render transactions
function renderTransactions(transactions) {
    const transactionTableBody = document.getElementById("transactionTable").querySelector("tbody");
    transactionTableBody.innerHTML = "";

    transactions.forEach(transaction => {
        const row = document.createElement("tr");
        Object.values(transaction).forEach(value => {
            const cell = document.createElement("td");
            cell.textContent = value;
            row.appendChild(cell);
        });
        transactionTableBody.appendChild(row);
    });
}

// Sort and Filter Functions
function sortTransactions() {
    data.expenses.transactions.sort((a, b) => new Date(a.date) - new Date(b.date));
    renderTransactions(data.expenses.transactions);
}

function filterTransactions() {
    const selectedType = document.getElementById("typeFilter").value;
    const filteredTransactions = selectedType === "All"
        ? data.expenses.transactions
        : data.expenses.transactions.filter(transaction => transaction.source === selectedType);
    renderTransactions(filteredTransactions);
}

// Initialize page with data
renderOverview(data);
renderTransactions(data.expenses.transactions);
