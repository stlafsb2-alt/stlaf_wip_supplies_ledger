function updateRequestStatus(req_id, status) {
  let reason = null;
  if (status === "Cancelled") {
    reason = prompt("Please enter a reason for cancellation:");
    if (!reason) {
      alert("Reason is required to cancel.");
      return;
    }
  }

  fetch("/stlaf_wip_supplies_ledger/auth/oop/request_form.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      method: "updateStatus",
      req_id: req_id,
      status: status,
      reason: reason,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "error") {
        alert(data.message);
        return;
      }
      alert(data.message);
      if (data.status === "success") location.reload();
    })
    .catch((err) => {
      console.error("Error:", err);
      alert("Connection error.");
    });
}

const toggler = document.querySelector(".toggler-btn");
toggler && toggler.addEventListener("click", function () {
  document.querySelector("#sidebar").classList.toggle("collapsed");
});
