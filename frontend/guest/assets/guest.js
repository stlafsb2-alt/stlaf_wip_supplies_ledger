const searchInput = document.getElementById("itemSearch"),
      itemList = document.getElementById("itemList"),
      selectedItemInput = document.getElementById("selectedItem"),
      productIDInput = document.getElementById("productID"),
      unitField = document.getElementById("unitField"),
      form = document.getElementById("requestForm"),
      loader = document.getElementById("loader"),
      submitBtn = form.querySelector('button[type="submit"]'),
      addItemBtn = document.getElementById("addItemBtn"),
      itemsTableBody = document.querySelector("#itemsTable tbody"),
      quantityField = document.getElementById("quantityField");

searchInput.addEventListener("input", function() {
    const query = this.value.toLowerCase().trim();
    itemList.innerHTML = "";
    if (!query) return;
    items.filter(item => item.name.toLowerCase().includes(query))
         .forEach(item => {
             const itemEl = document.createElement("a");
             itemEl.classList.add("list-group-item", "list-group-item-action");
             itemEl.textContent = `${item.name} (${item.unit})`;
             itemEl.onclick = () => {
                 searchInput.value = item.name;
                 selectedItemInput.value = item.name;
                 productIDInput.value = item.id;
                 unitField.value = item.unit;
                 itemList.innerHTML = "";
             };
             itemList.appendChild(itemEl);
         });
});

document.addEventListener("click", e => {
    if (!searchInput.contains(e.target) && !itemList.contains(e.target)) {
        itemList.innerHTML = "";
    }
});

addItemBtn.addEventListener("click", () => {
    const name = selectedItemInput.value,
          id = productIDInput.value,
          unit = unitField.value,
          qty = quantityField.value;
    if (!name || !id || !unit || !qty) {
        alert("Please fill all item fields before adding.");
        return;
    }
    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td><input type="hidden" name="item[]" value="${name}">${name}</td>
        <td><input type="hidden" name="product_id[]" value="${id}">${id}</td>
        <td><input type="hidden" name="quantity[]" value="${qty}">${qty}</td>
        <td><input type="hidden" name="unit[]" value="${unit}">${unit}</td>
        <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>
    `;
    itemsTableBody.appendChild(tr);
    searchInput.value = "";
    selectedItemInput.value = "";
    productIDInput.value = "";
    unitField.value = "";
    quantityField.value = "";
});

itemsTableBody.addEventListener("click", e => {
    if (e.target.classList.contains("remove-item")) {
        e.target.closest("tr").remove();
    }
});

form.addEventListener("submit", e => {
    e.preventDefault();
    const itemRows = itemsTableBody.querySelectorAll("tr");
    if (itemRows.length === 0) {
        alert("Please add at least one item before submitting.");
        return;
    }
    loader.style.display = "block";
    submitBtn.disabled = true;
    const formData = new FormData(form);
    fetch("", { method: "POST", body: formData })
    .then(res => res.text())
    .then(() => {
        alert("Request submitted successfully!");
        form.reset();
        itemsTableBody.innerHTML = "";
    })
    .catch(() => {
        alert("Failed to submit request.");
    })
    .finally(() => {
        loader.style.display = "none";
        submitBtn.disabled = false;
    });
});

document.getElementById("cancelBtn").addEventListener("click", () => {
    form.reset();
    searchInput.value = "";
    selectedItemInput.value = "";
    productIDInput.value = "";
    unitField.value = "";
    quantityField.value = "";
    itemsTableBody.innerHTML = "";
});