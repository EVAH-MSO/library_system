// ============================
// CONFIRM DELETE BOOK
// ============================
function confirmDelete(bookTitle = "this book") {
  return window.confirm(
    `⚠️ Are you sure you want to delete "${bookTitle}"? This action cannot be undone.`,
  );
}

// ============================
// SIMPLE SEARCH VALIDATION
// ============================
function validateSearch(inputId = "searchInput") {
  const search = document.getElementById(inputId).value.trim();

  if (search === "") {
    // modern alert style
    alert("🔍 Please enter a search term before searching!");
    return false;
  }

  return true;
}

// ============================
// OPTIONAL: ADD ENTER KEY SEARCH
// ============================
document.addEventListener("DOMContentLoaded", () => {
  const searchBox = document.getElementById("searchInput");
  if (searchBox) {
    searchBox.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        const form = searchBox.closest("form");
        if (form) form.submit();
      }
    });
  }
});
const ctx = document.getElementById("booksChart").getContext("2d");

const myChart = new Chart(ctx, {
  type: "doughnut",
  data: {
    labels: ["Borrowed", "Available"],
    datasets: [
      {
        data: [borrowedCount, availableCount], // set these dynamically
        backgroundColor: ["#1e90ff", "#4caf50"],
        borderWidth: 1,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false, // allows CSS height to control size
    plugins: {
      legend: { position: "bottom" },
    },
  },
});