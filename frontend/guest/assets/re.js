document.addEventListener("DOMContentLoaded", function () {
  const dropdownItems = document.querySelectorAll(".dropdown-item");
  const tableRows = document.querySelectorAll("#requestsTable tbody tr");
  const searchInput = document.getElementById("searchInput");
  const selectedDepartmentText = document.getElementById("selectedDepartment");

  dropdownItems.forEach((item) => {
    item.addEventListener("click", function () {
      dropdownItems.forEach((i) => i.classList.remove("active"));
      this.classList.add("active");

      const department = this.getAttribute("data-department").toLowerCase();
      selectedDepartmentText.textContent = this.textContent;

      filterTable(department, searchInput.value.toLowerCase());
    });
  });

  searchInput.addEventListener("input", function () {
    const searchText = this.value.toLowerCase();
    const activeItem = document.querySelector(".dropdown-item.active");
    const department = activeItem
      ? activeItem.getAttribute("data-department").toLowerCase()
      : "all";
    filterTable(department, searchText);
  });

  function filterTable(department, searchText = "") {
    tableRows.forEach((row) => {
      const rowDept = row.getAttribute("data-department").toLowerCase();
      const text = row.textContent.toLowerCase();
      const matchesDept = department === "all" || rowDept === department;
      const matchesSearch = text.includes(searchText);
      row.style.display = matchesDept && matchesSearch ? "" : "none";
    });
  }
});