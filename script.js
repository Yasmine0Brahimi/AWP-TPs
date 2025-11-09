/* ============================
   script.js — unified logic
   ============================ */

/* Utility: safe query */
const qs = (sel, ctx = document) => ctx.querySelector(sel);

/* Wait once and initialize page-specific features */
document.addEventListener("DOMContentLoaded", () => {
  initAddStudent();
  initAttendanceTable();
  initChartsIfNeeded();
});

/* ============================
   Add Student Validation
   ============================ */
function initAddStudent() {
  const form = qs("#studentForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    document.querySelectorAll(".error-message").forEach((el) => el.remove());

    const id = qs("#studentId");
    const lastName = qs("#lastName");
    const firstName = qs("#firstName");
    const email = qs("#email");
    let valid = true;

    function error(field, message) {
      const span = document.createElement("span");
      span.className = "error-message";
      span.textContent = message;
      field.insertAdjacentElement("afterend", span);
      valid = false;
    }

    if (id.value.trim() === "") error(id, "Student ID cannot be empty.");
    else if (!/^[0-9]+$/.test(id.value.trim()))
      error(id, "Student ID must contain only digits.");

    if (lastName.value.trim() === "")
      error(lastName, "Last name cannot be empty.");
    else if (!/^[A-Za-z]+$/.test(lastName.value.trim()))
      error(lastName, "Last name must contain letters only.");

    if (firstName.value.trim() === "")
      error(firstName, "First name cannot be empty.");
    else if (!/^[A-Za-z]+$/.test(firstName.value.trim()))
      error(firstName, "First name must contain letters only.");

    if (email.value.trim() === "") {
  error(email, "Email cannot be empty.");
} else {
  const value = email.value.trim();
  if (!value.includes("@")) {
    error(email, "Email must contain '@'.");
  } else if (!value.includes(".")) {
    error(email, "Email must contain a domain (e.g. .com).");
  } else if (!/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(value)) {
    error(email, "Invalid email structure. Example: name@example.com");
  }
}

    if (!valid) return;

    const newStudent = {
      id: id.value.trim(),
      lastName: lastName.value.trim(),
      firstName: firstName.value.trim(),
      email: email.value.trim(),
    };

    const students = JSON.parse(localStorage.getItem("students") || "[]");
    students.push(newStudent);
    localStorage.setItem("students", JSON.stringify(students));

    alert("✅ New student added successfully!");
    form.reset();
  });
}

/* ============================
   Attendance Table — logic & storage
   ============================ */
function initAttendanceTable() {
  const table = qs("#attendanceTable");
  if (!table) return;
  const tbody = table.querySelector("tbody");
  if (!tbody) return;

  const savedStudents = JSON.parse(localStorage.getItem("students") || "[]");
  const savedAttendance = JSON.parse(localStorage.getItem("attendanceData") || "[]");

  if (savedStudents.length > 0) {
    tbody.innerHTML = "";
    savedStudents.forEach((s) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${s.lastName}</td>
        <td>${s.firstName}</td>
        <td></td><td></td><td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td>
        <td>0</td><td>0</td><td>New Student</td>
      `;
      tbody.appendChild(tr);
    });
  }

  if (savedAttendance.length > 0) {
    const rows = Array.from(tbody.rows);
    const limit = Math.min(rows.length, savedAttendance.length);
    for (let i = 0; i < limit; i++) {
      const cells = Array.from(rows[i].children);
      savedAttendance[i].forEach((val, j) => {
        if (cells[j]) cells[j].textContent = val;
      });
      computeAttendanceForRow(rows[i]);
    }
  }

  if (!table.dataset.listenerAdded) {
    table.addEventListener("click", (e) => {
      const td = e.target.closest("td");
      if (!td) return;
      const colIndex = td.cellIndex;
      if (colIndex >= 2 && colIndex <= 13) {
        td.textContent = td.textContent.trim() === "✓" ? "" : "✓";
        computeAttendanceForRow(td.parentElement);
        saveAttendanceToStorage();
        triggerChartUpdate();
      }
    });
    table.dataset.listenerAdded = "true";
  }

  tbody.querySelectorAll("tr").forEach((r) => computeAttendanceForRow(r));
  triggerChartUpdate();
}

function saveAttendanceToStorage() {
  const table = qs("#attendanceTable");
  if (!table) return;
  const data = Array.from(table.tBodies[0].rows).map((row) =>
    Array.from(row.children).map((td) => td.textContent.trim())
  );
  localStorage.setItem("attendanceData", JSON.stringify(data));
}

function computeAttendanceForRow(row) {
  if (!row) return;
  const cells = Array.from(row.children);
  const sessions = cells.slice(2, 8);
  const parts = cells.slice(8, 14);

  const present = sessions.filter((c) => c.textContent.trim() === "✓").length;
  const absences = sessions.length - present;
  const participated = parts.filter((c) => c.textContent.trim() === "✓").length;

  if (!cells[14]) {
    for (let i = cells.length; i <= 16; i++) {
      const td = document.createElement("td");
      row.appendChild(td);
    }
  }

  row.children[14].textContent = absences;
  row.children[15].textContent = participated;

  row.classList.remove("success", "warning", "danger");
  if (absences < 3) row.classList.add("success");
  else if (absences <= 4) row.classList.add("warning");
  else row.classList.add("danger");

  let msg = "";
  if (absences >= 5) msg = "Excluded – too many absences";
  else if (absences >= 3) msg = "Warning – attendance low";
  else msg = "Good attendance – keep it up!";
  row.children[16].textContent = msg;
}

/* =================================================
   Charts (pie + donut)
   ================================================= */
let globalChart = null;

function computeTotalsFromTable() {
  const table = qs("#attendanceTable");
  if (!table) return null;
  const rows = Array.from(table.tBodies[0].rows);
  const totals = { students: rows.length, present: 0, absent: 0, participated: 0 };

  rows.forEach((row) => {
    const cells = Array.from(row.children);
    const sessions = cells.slice(2, 8);
    const parts = cells.slice(8, 14);
    const present = sessions.filter((c) => c.textContent.trim() === "✓").length;
    const participated = parts.filter((c) => c.textContent.trim() === "✓").length;
    totals.present += present;
    totals.participated += participated;
    totals.absent += sessions.length - present;
  });
  return totals;
}

function initChartsIfNeeded() {
  const canvas = qs("#comboChart");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  let totals = JSON.parse(localStorage.getItem("attendanceTotals") || "null");
  if (!totals) totals = { students: 0, present: 0, absent: 0, participated: 0 };

  if (globalChart) globalChart.destroy();

  globalChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Present", "Absent", "Participation"],
      datasets: [
        {
          label: "Outer ring",
          data: [totals.present, totals.absent, totals.participated],
          backgroundColor: ["#4caf50", "#f44336", "#2b9bd3"],
          cutout: "65%",
          borderColor: "rgba(14, 13, 13, 0.7)",
          borderWidth: 1,
        },
        {
          label: "Inner pie (students)",
          data: [totals.students, 0],
          backgroundColor: ["#f0e87fff", "#e9e9e9"],
          cutout: "0%",
          borderColor: "rgba(14, 13, 13, 0.7)",
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
        title: { display: true, text: "Attendance & Participation Summary" },
      },
    },
  });
}

function triggerChartUpdate() {
  const totals = computeTotalsFromTable();
  if (!totals) return;
  localStorage.setItem("attendanceTotals", JSON.stringify(totals));
  if (!qs("#comboChart")) return;
  if (!globalChart) return initChartsIfNeeded();
  globalChart.data.datasets[0].data = [totals.present, totals.absent, totals.participated];
  globalChart.data.datasets[1].data = [totals.students, 0];
  globalChart.update();
}

/* =================================================
   Exercise 5 — jQuery row interactions (fixed)
   ================================================= */
$(document).ready(function () {
  const table = $("#attendanceTable");
  if (!table.length) return;

  // Highlight row on hover
  table.on("mouseenter", "tbody tr", function () {
    $(this).css("background-color", "#f3f8f3");
  });

  // Remove highlight when mouse leaves
  table.on("mouseleave", "tbody tr", function () {
    $(this).css("background-color", "");
  });

  // ✅ Show alert only when clicking on the student's name (first two columns)
  table.on("click", "tbody td:nth-child(1), tbody td:nth-child(2)", function (e) {
    e.stopPropagation(); // prevent the click from triggering other handlers
    const row = $(this).closest("tr");
    const lastName = row.find("td").eq(0).text().trim();
    const firstName = row.find("td").eq(1).text().trim();
    const absences = row.find("td").eq(14).text().trim();
    if (!lastName || !firstName) return;
    alert(`👩‍🎓 Student: ${firstName} ${lastName}\nAbsences: ${absences}`);
  });
});
