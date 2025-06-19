<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>Klickbarer Kalender</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f4f4f4;
      margin: 0;
    }
    .calendar-container {
      max-width: 1200px;
      margin: auto;
      padding: 1rem;
    }
    .controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    #calendarGrid {
      display: grid;
      gap: 1rem;
      margin-top: 1rem;
    }
    .month, .week {
      background: white;
      padding: 0.5rem;
      border-radius: 6px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .month h3, .week h3 {
      text-align: center;
    }
    .days {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      font-size: 0.75rem;
    }
    .day {
      padding: 0.3rem;
      border: 1px solid #ddd;
      text-align: center;
      cursor: pointer;
    }
    .day:hover {
      background-color: #e3f2fd;
    }
  </style>
</head>
<body>
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

    function render() {
      grid.innerHTML = "";
      if (viewMode === "year") renderYear();
      if (viewMode === "month") renderMonth(currentDate.getFullYear(), currentDate.getMonth());
      if (viewMode === "week") renderWeek(currentDate);
    }

    function renderYear() {
      titleDisplay.textContent = `Jahr ${currentDate.getFullYear()}`;
      grid.style.gridTemplateColumns = "repeat(4, 1fr)";

      for (let m = 0; m < 12; m++) {
        const month = new Date(currentDate.getFullYear(), m);
        const el = createMonthElement(month);
        grid.appendChild(el);
      }
    }

    function renderMonth(year, monthIndex) {
      const month = new Date(year, monthIndex);
      titleDisplay.textContent = month.toLocaleString("de-DE", { month: "long", year: "numeric" });
      grid.style.gridTemplateColumns = "repeat(1, 1fr)";
      const el = createMonthElement(month);
      grid.appendChild(el);
    }

    function renderWeek(date) {
      grid.style.gridTemplateColumns = "repeat(1, 1fr)";
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

      for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const dayEl = document.createElement("div");
        dayEl.className = "day";
        dayEl.textContent = d.toLocaleDateString("de-DE", { weekday: "short", day: "numeric", month: "short" });
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

      const daysGrid = document.createElement("div");
      daysGrid.className = "days";

      const year = date.getFullYear();
      const month = date.getMonth();
      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const offset = firstDay === 0 ? 6 : firstDay - 1;

      for (let i = 0; i < offset; i++) {
        daysGrid.appendChild(document.createElement("div"));
      }

      for (let d = 1; d <= daysInMonth; d++) {
        const day = document.createElement("div");
        day.className = "day";
        day.textContent = d;

        const dayDate = new Date(year, month, d);
        day.addEventListener("click", () => onDayClick(dayDate));

        daysGrid.appendChild(day);
      }

      monthEl.appendChild(daysGrid);
      return monthEl;
    }

    function onDayClick(date) {
      const input = prompt(`Task für ${date.toLocaleDateString("de-DE")} eingeben:`);
      if (input) {
        alert(`Task gespeichert (nicht persistent): "${input}"`);
        // Hier könnte ein echter Speicheraufruf erfolgen
      }
    }

    render();
  </script>
</body>
</html>
