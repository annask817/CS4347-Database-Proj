// js/script.js
document.addEventListener("DOMContentLoaded", () => {
  // Initialize data loading
  loadAllData();

  // Update sort button text to show initial state
  const sortButton = document.querySelector('button[onclick="sortTransactions()"]');
  if (sortButton) {
      sortButton.textContent = `Sort by Date (↓)`;
  }

  // Add event listeners to forms when they exist
  const transactionForm = document.querySelector("form");
  if (transactionForm) {
      transactionForm.addEventListener("submit", async (e) => {
          e.preventDefault();
          if (validateTransactionForm(transactionForm)) {
              const formData = new FormData(transactionForm);
              try {
                  const response = await fetch('addnew.php', {
                      method: 'POST',
                      body: formData
                  });
                  
                  if (response.ok) {
                      window.location.href = 'overview.php';
                  } else {
                      throw new Error('Failed to add transaction');
                  }
              } catch (error) {
                  console.error('Error:', error);
                  alert('Failed to add transaction: ' + error.message);
              }
          }
      });
  }

  // Add amount preview listeners if on add/edit page
  const amountInput = document.querySelector('input[name="amount"]');
  const typeSelect = document.querySelector('select[name="type"]');
  if (amountInput && typeSelect) {
      amountInput.addEventListener("input", updateAmountPreview);
      typeSelect.addEventListener("change", updateAmountPreview);
      updateAmountPreview(); // Initial preview
  }
});

async function loadAllData() {
  await filterTransactions();
  initTooltips();
}

// Render transactions table
function renderTransactions(transactions) {
  const tbody = document.querySelector("#transactionTable tbody");
  if (!tbody) return;
  
  tbody.innerHTML = "";

  transactions.forEach((transaction) => {
      const row = document.createElement("tr");
      const date = new Date(transaction.dateOf);
      const formattedDate = date.toLocaleDateString();
      
      const categoryDisplay = transaction.category || 'Uncategorized';
      const amountClass = transaction.type === "Income" ? "text-success" : "text-danger";
      const amountPrefix = transaction.type === "Income" ? "+" : "-";
      const amountValue = Math.abs(parseFloat(transaction.amount)).toFixed(2);

      row.innerHTML = `
          <td>${transaction.transaction_id}</td>
          <td>${formattedDate}</td>
          <td>${escapeHtml(transaction.description || '')}</td>
          <td>${escapeHtml(categoryDisplay)}</td>
          <td class="${amountClass}">
              ${amountPrefix}$${amountValue}
          </td>
          <td>
              <a href="edit_transaction.php?id=${transaction.transaction_id}" class="edit-btn">Edit</a>
              <button onclick="deleteTransaction(${transaction.transaction_id})" class="delete-btn">Delete</button>
          </td>
      `;
      tbody.appendChild(row);
  });
}

// Filter transactions by category
async function filterTransactions() {
  const selectedCategory = document.getElementById("typeFilter")?.value || 'All';
  try {
      const response = await fetch(
          `fetch_transactions.php?category=${encodeURIComponent(selectedCategory)}`
      );
      if (!response.ok) {
          throw new Error("Network response was not ok");
      }
      const transactions = await response.json();
      renderTransactions(transactions);
      updateSummary();
  } catch (error) {
      console.error("Error fetching transactions:", error);
      alert("Error filtering transactions: " + error.message);
  }
}

// Update summary values
async function updateSummary() {
  try {
      const response = await fetch('get_summary.php');
      if (response.ok) {
          const summary = await response.json();
          
          // Update summary values
          document.getElementById('incomeValue').textContent = `$${summary.income.toFixed(2)}`;
          document.getElementById('expenseValue').textContent = `$${summary.expenses.total.toFixed(2)}`;
          
          const balance = summary.income - summary.expenses.total;
          const balanceElement = document.getElementById('balanceValue');
          balanceElement.textContent = `$${Math.abs(balance).toFixed(2)}`;
          balanceElement.className = `summary-value ${balance >= 0 ? 'income-value' : 'expense-value'}`;
          
          // Update expense breakdown
          const breakdownContainer = document.querySelector('.expense-breakdown');
          if (breakdownContainer && summary.expenses.categories) {
              const categoriesHTML = Object.entries(summary.expenses.categories)
                  .map(([category, amount]) => `
                      <div class="category-item">
                          <span class="category-name">${escapeHtml(category)}</span>
                          <span class="category-amount">$${amount.toFixed(2)}</span>
                      </div>
                  `).join('');
              
              const breakdownContent = document.createElement('div');
              breakdownContent.innerHTML = `
                  <h3>Expense Breakdown</h3>
                  ${categoriesHTML}
              `;
              
              breakdownContainer.innerHTML = breakdownContent.innerHTML;
          }
      }
  } catch (error) {
      console.error('Error updating summary:', error);
  }
}

// Sort transactions by date
let sortAscending = true;
function sortTransactions() {
  const tbody = document.querySelector("#transactionTable tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((a, b) => {
      const dateA = new Date(a.cells[1].textContent);
      const dateB = new Date(b.cells[1].textContent);
      return sortAscending ? dateA - dateB : dateB - dateA;
  });

  tbody.innerHTML = "";
  rows.forEach((row) => tbody.appendChild(row));
  sortAscending = !sortAscending;

  const sortButton = document.querySelector('button[onclick="sortTransactions()"]');
  if (sortButton) {
      sortButton.textContent = `Sort by Date (${sortAscending ? "↑" : "↓"})`;
  }
}

// Delete transaction
async function deleteTransaction(transactionId) {
  if (!confirm("Are you sure you want to delete this transaction?")) {
      return;
  }

  try {
      const response = await fetch("delete_transaction.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `transaction_id=${transactionId}`,
      });

      if (!response.ok) {
          throw new Error("Network response was not ok");
      }

      const result = await response.json();
      if (result.success) {
          await loadAllData();
      } else {
          throw new Error(result.error || "Failed to delete transaction");
      }
  } catch (error) {
      console.error("Error:", error);
      alert("Failed to delete transaction: " + error.message);
  }
}

// Form validation
function validateTransactionForm(form) {
  const amount = form.querySelector('input[name="amount"]');
  const date = form.querySelector('input[name="date"]');
  const notes = form.querySelector('textarea[name="notes"]');

  if (parseFloat(amount.value) <= 0) {
      alert("Amount must be greater than 0");
      amount.focus();
      return false;
  }

  const selectedDate = new Date(date.value);
  const today = new Date();
  if (selectedDate > today) {
      alert("Date cannot be in the future");
      date.focus();
      return false;
  }

  if (notes.value.trim().length < 3) {
      alert("Please provide a meaningful description");
      notes.focus();
      return false;
  }

  return true;
}

// Helper function to safely escape HTML
function escapeHtml(unsafe) {
  return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

// Update amount preview
function updateAmountPreview() {
  const amountInput = document.querySelector('input[name="amount"]');
  const typeSelect = document.querySelector('select[name="type"]');
  const previewElement = document.getElementById("amountPreview");

  if (amountInput && typeSelect && previewElement) {
      const amount = parseFloat(amountInput.value) || 0;
      const type = typeSelect.value;
      const formattedAmount = new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
      }).format(amount);
      
      previewElement.textContent = `${type === "Income" ? "+" : "-"}${formattedAmount}`;
      previewElement.className = type === "Income" ? "text-success" : "text-danger";
  }
}

// Initialize tooltips
function initTooltips() {
  const tooltips = document.querySelectorAll("[data-tooltip]");
  tooltips.forEach((tooltip) => {
      tooltip.addEventListener("mouseover", (e) => {
          const tip = document.createElement("div");
          tip.className = "tooltip";
          tip.textContent = e.target.dataset.tooltip;
          document.body.appendChild(tip);

          const rect = e.target.getBoundingClientRect();
          tip.style.top = rect.bottom + 5 + "px";
          tip.style.left = rect.left + (rect.width - tip.offsetWidth) / 2 + "px";

          e.target.addEventListener("mouseout", () => tip.remove());
      });
  });
}
