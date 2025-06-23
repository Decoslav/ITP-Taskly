<?php 
  include "includes/header.php"; 
  /*
  if(!isset($_SESSION['username']))
  {
    header('login.php');
  }
  */

  // NOTE TO SELF: DO THING (Guard Page)
?>

<div class="calendar-container">
  <div class="controls">
    <button id="prev">« Zurück</button>
    <h2 id="titleDisplay">Kalender</h2>
    <button id="next">Weiter »</button>
    <select id="viewSelect">
      <option value="year">Jahresansicht</option>
      <option value="month">Monatsansicht</option>
      <option value="week">Wochenansicht</option>
    </select>
  </div>
  <div id="calendarGrid"></div>
</div>

<script>
  let currentDate = new Date();
  let viewMode = "year";

  const grid = document.getElementById("calendarGrid");
  const titleDisplay = document.getElementById("titleDisplay");
  const viewSelect = document.getElementById("viewSelect");

  document.getElementById("prev").addEventListener("click", () => {
    if (viewMode === "year") currentDate.setFullYear(currentDate.getFullYear() - 1);
    if (viewMode === "month") currentDate.setMonth(currentDate.getMonth() - 1);
    if (viewMode === "week") currentDate.setDate(currentDate.getDate() - 7);
    render();
  });

  document.getElementById("next").addEventListener("click", () => {
    if (viewMode === "year") currentDate.setFullYear(currentDate.getFullYear() + 1);
    if (viewMode === "month") currentDate.setMonth(currentDate.getMonth() + 1);
    if (viewMode === "week") currentDate.setDate(currentDate.getDate() + 7);
    render();
  });

  viewSelect.addEventListener("change", () => {
    viewMode = viewSelect.value;
    render();
  });

  function render() 
  {
    grid.innerHTML = "";
    grid.className = viewMode;

    if (viewMode === "year") {
      grid.style.gridTemplateColumns = "repeat(3, 1fr)";
      grid.style.justifyContent = "center";
      renderYear();
    } else if (viewMode === "month") {
      grid.style.gridTemplateColumns = "1fr";
      grid.style.justifyContent = "center";
      renderMonth(currentDate.getFullYear(), currentDate.getMonth());
    } else if (viewMode === "week") {
      grid.style.gridTemplateColumns = "1fr";
      grid.style.justifyContent = "center";
      renderWeek(currentDate);
    }
  }

  function renderYear() {
    titleDisplay.textContent = `Jahr ${currentDate.getFullYear()}`;
    for (let m = 0; m < 12; m++) {
      const month = new Date(currentDate.getFullYear(), m);
      grid.appendChild(createMonthElement(month));
    }
  }

  function renderMonth(year, monthIndex) {
    const month = new Date(year, monthIndex);
    titleDisplay.textContent = month.toLocaleString("de-DE", { month: "long", year: "numeric" });
    const el = createMonthElement(month);
    grid.appendChild(el);
  }

  function renderWeek(date) {
    const week = document.createElement("div");
    week.className = "week";

    const start = new Date(date);
    const dayOfWeek = start.getDay();
    const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    start.setDate(start.getDate() + diff);

    const end = new Date(start);
    end.setDate(end.getDate() + 6);

    titleDisplay.textContent = `Woche ${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;

    const daysGrid = document.createElement("div");
    daysGrid.className = "days";
    week.appendChild(createWeekdayHeader());

    const today = new Date();

    for (let i = 0; i < 7; i++) {
      const d = new Date(start);
      d.setDate(start.getDate() + i);
      const dayEl = document.createElement("div");
      dayEl.className = "day";
      dayEl.textContent = d.toLocaleDateString("de-DE", { day: "numeric" });

      if (
        d.getFullYear() === today.getFullYear() &&
        d.getMonth() === today.getMonth() &&
        d.getDate() === today.getDate()
      ) {
        dayEl.classList.add("today");
      }

      dayEl.addEventListener("click", () => onDayClick(d));
      daysGrid.appendChild(dayEl);
    }

    week.appendChild(daysGrid);
    grid.appendChild(week);
  }

  function createMonthElement(date) {
    const monthEl = document.createElement("div");
    monthEl.className = "month";

    const header = document.createElement("h3");
    header.textContent = date.toLocaleString("de-DE", { month: "long", year: "numeric" });
    monthEl.appendChild(header);

    const wrapper = document.createElement("div");
    wrapper.appendChild(createWeekdayHeader());

    const daysGrid = document.createElement("div");
    daysGrid.className = "days";

    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const offset = firstDay === 0 ? 6 : firstDay - 1;

    for (let i = 0; i < offset; i++) {
      const empty = document.createElement("div");
      daysGrid.appendChild(empty);
    }

    const today = new Date();

    for (let d = 1; d <= daysInMonth; d++) {
      const day = document.createElement("div");
      day.className = "day";
      day.textContent = d;
      const dayDate = new Date(year, month, d);

      if (
        dayDate.getFullYear() === today.getFullYear() &&
        dayDate.getMonth() === today.getMonth() &&
        dayDate.getDate() === today.getDate()
      ) {
        day.classList.add("today");
      }

      day.addEventListener("click", () => onDayClick(dayDate));
      daysGrid.appendChild(day);
    }

    wrapper.appendChild(daysGrid);
    monthEl.appendChild(wrapper);
    return monthEl;
  }

  function createWeekdayHeader() {
    const header = document.createElement("div");
    header.className = "weekdays";
    const days = ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"];
    for (const d of days) {
      const el = document.createElement("div");
      el.textContent = d;
      header.appendChild(el);
    }
    return header;
  }


  function saveTaskToDatabase(timestamp, task)
  {
    const formData = new FormData();
    formData.append('timestamp', timestamp);
    formData.append('task', task);

    fetch('save_task.php',
    {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => 
    {
      if(data.success)
      {
        alert(`Task gespeichert: "${task}"`);
      } else {
        alert(`Fehler beim Speicheren der Task: ${data.message}`);
      }
    })
    .catch(error => 
    {
      console.error('Error: ', error);
      alert('Ein unerwarterer Fehler ist aufgetreten.');
    });
  }


  function onDayClick(date) {
    const input = prompt(`Task für ${date.toLocaleDateString("de-DE")} eingeben:`);
    if (input) 
    {
      const timestamp = Math.floor(date.getTime() / 1000);
      saveTaskToDatabase(timestamp, input);
    }
  }

  render();
</script>
