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

<div id="taskModal" class="modal">
  <div class="modal-content">
    <span class="close-button">&times;</span>
    <h3 id="modalDateDisplay"></h3>
    <div id="tasksList"></div>
    <div id="addTaskForm">
        <input type="text" id="newTaskInput" placeholder="Neue Aufgabe eingeben">
        <button id="saveTaskButton">Aufgabe speichern</button>
    </div>
  </div>
</div>

<script>
  let currentDate = new Date();
  let viewMode = "year";
  let userTasks = []; 
  let selectedDate = null;
  // Calendar
  const grid = document.getElementById("calendarGrid");
  const titleDisplay = document.getElementById("titleDisplay");
  const viewSelect = document.getElementById("viewSelect");
    // Modal
    const taskModal = document.getElementById("taskModal");
    const closeButton = document.querySelector(".close-button");
    const modalDateDisplay = document.getElementById("modalDateDisplay");
    const tasksList = document.getElementById("tasksList");
    const newTaskInput = document.getElementById("newTaskInput");
    const saveTaskButton = document.getElementById("saveTaskButton");

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

  closeButton.addEventListener("click", () => {
      taskModal.style.display = "none";
  });

  window.addEventListener("click", (event) => {
      if (event.target === taskModal) {
          taskModal.style.display = "none";
      }
  });

  saveTaskButton.addEventListener("click", async () => {
      const taskText = newTaskInput.value.trim();
      if (taskText && selectedDate) 
      {
          const timestamp = Math.floor(selectedDate.getTime() / 1000);
          const success = await saveTaskToDatabase(timestamp, taskText);
          if (success) 
          {
              newTaskInput.value = "";
              await render();
              displayTasksForSelectedDate(selectedDate);
          }
      } else {
          alert("Bitte geben Sie eine Aufgabe ein.");
      }
  });

  async function fetchTasks() 
  {
      try 
      {
          let startTimestamp = null;
          let endTimestamp = null;

          if (viewMode === "year") 
          {
              startTimestamp = Math.floor(new Date(currentDate.getFullYear(), 0, 1).getTime() / 1000);
              endTimestamp = Math.floor(new Date(currentDate.getFullYear(), 11, 31, 23, 59, 59).getTime() / 1000);
          } else if (viewMode === "month") {
              startTimestamp = Math.floor(new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getTime() / 1000);
              endTimestamp = Math.floor(new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0, 23, 59, 59).getTime() / 1000);
          } else if (viewMode === "week") {
              const startOfWeek = new Date(currentDate);
              const dayOfWeek = startOfWeek.getDay();
              const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek
              startOfWeek.setDate(startOfWeek.getDate() + diff);
              startOfWeek.setHours(0, 0, 0, 0);

              const endOfWeek = new Date(startOfWeek);
              endOfWeek.setDate(endOfWeek.getDate() + 6);
              endOfWeek.setHours(23, 59, 59, 999);

              startTimestamp = Math.floor(startOfWeek.getTime() / 1000);
              endTimestamp = Math.floor(endOfWeek.getTime() / 1000);
          }

          const url = `fetch_tasks.php?start=${startTimestamp}&end=${endTimestamp}`;
          const response = await fetch(url);
          const data = await response.json();

          if (data.success) 
          {
              userTasks = data.tasks;
              console.log("Fetched tasks:", userTasks);
          } else {
              console.error("Error fetching tasks:", data.message);
              userTasks = [];
          }
      } catch (error) {
          console.error("Network or parsing error fetching tasks:", error);
          userTasks = [];
      }
  }

  async function render()
  {
    grid.innerHTML = "";
    grid.className = viewMode;

    await fetchTasks();

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
    start.setHours(0,0,0,0); 

    const end = new Date(start);
    end.setDate(end.getDate() + 6);
    end.setHours(23,59,59,999); 

    titleDisplay.textContent = `Woche ${start.toLocaleDateString("de-DE")} - ${end.toLocaleDateString("de-DE")}`;

    const daysGrid = document.createElement("div");
    daysGrid.className = "days";
    week.appendChild(createWeekdayHeader());

    const today = new Date();
    today.setHours(0,0,0,0); 

    for (let i = 0; i < 7; i++) {
      const d = new Date(start);
      d.setDate(start.getDate() + i);
      d.setHours(0,0,0,0);

      const dayEl = document.createElement("div");
      dayEl.className = "day";
      dayEl.textContent = d.toLocaleDateString("de-DE", { day: "numeric" });

      if (d.getTime() === today.getTime()) { 
        dayEl.classList.add("today");
      }

        const tasksForDay = userTasks.filter(task => {
            const taskDate = new Date(task.datum_unix * 1000);
            taskDate.setHours(0,0,0,0); 
            return taskDate.getTime() === d.getTime(); 
        });

        if (tasksForDay.length > 0) {
            dayEl.classList.add("has-task");
        }

      dayEl.addEventListener("click", () => onDayClick(d));
      daysGrid.appendChild(dayEl);
    }

    week.appendChild(daysGrid);
    grid.appendChild(week);
  }

  function createMonthElement(date) 
  {
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
    today.setHours(0,0,0,0); 

    for (let d = 1; d <= daysInMonth; d++) {
      const day = document.createElement("div");
      day.className = "day";
      day.textContent = d;
      const dayDate = new Date(year, month, d);
      dayDate.setHours(0,0,0,0); 

      if (dayDate.getTime() === today.getTime()) { 
        day.classList.add("today");
      }

        const tasksForDay = userTasks.filter(task => {
            const taskDate = new Date(task.datum_unix * 1000);
            taskDate.setHours(0,0,0,0); 
            return taskDate.getTime() === dayDate.getTime(); 
        });

        if (tasksForDay.length > 0) {
            day.classList.add("has-task");
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


  async function saveTaskToDatabase(timestamp, task)
  {
    const formData = new FormData();
    formData.append('timestamp', timestamp);
    formData.append('task', task);

    try {
      const response = await fetch('save_task.php',
      {
        method: 'POST',
        body: formData
      });
      const data = await response.json();
      if(data.success)
      {
        alert(`Task gespeichert: "${task}"`);
          return true;
      } else {
        alert(`Fehler beim Speichern der Task: ${data.message}`);
          return false;
      }
    } catch (error) {
      console.error('Error: ', error);
      alert('Ein unerwarteter Fehler ist aufgetreten.');
      return false; 
    }
  }

  async function deleteTaskFromDatabase(terminId) 
  {
    if (!confirm("Sind Sie sicher, dass Sie diese Aufgabe löschen möchten?")) {
        return;
    }

    const formData = new FormData();
    formData.append('termin_id', terminId);

    try {
        const response = await fetch('delete_task.php', { 
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert("Aufgabe erfolgreich gelöscht.");
            await render(); 
            displayTasksForSelectedDate(selectedDate);
        } else {
            alert(`Fehler beim Löschen der Aufgabe: ${data.message}`);
        }
    } catch (error) {
        console.error('Error deleting task:', error);
        alert('Ein unerwarteter Fehler ist beim Löschen aufgetreten.');
    }
  }

  function displayTasksForSelectedDate(date) 
  {
    tasksList.innerHTML = "";
    modalDateDisplay.textContent = `Aufgaben für ${date.toLocaleDateString("de-DE")}`;

    const dayStart = new Date(date);
    dayStart.setHours(0, 0, 0, 0); 

    const tasksOnThisDay = userTasks.filter(task => {
        const taskDate = new Date(task.datum_unix * 1000);
        taskDate.setHours(0, 0, 0, 0); 
        return taskDate.getTime() === dayStart.getTime();
    });

    if (tasksOnThisDay.length > 0) {
        tasksOnThisDay.forEach(task => {
            const taskDiv = document.createElement("div");
            taskDiv.innerHTML = `
                <span>${task.beschreibung}</span>
                <button data-termin-id="${task.termin_id}">Löschen</button>
            `;
            tasksList.appendChild(taskDiv);

            taskDiv.querySelector('button').addEventListener('click', (event) => {
                const terminIdToDelete = event.target.dataset.terminId;
                deleteTaskFromDatabase(terminIdToDelete);
            });
        });
    } else {
        const noTasksDiv = document.createElement("div");
        noTasksDiv.textContent = "Keine Aufgaben für diesen Tag.";
        tasksList.appendChild(noTasksDiv);
    }
  }


function onDayClick(date) 
{
  selectedDate = date;
  displayTasksForSelectedDate(date); 
  taskModal.style.display = "block"; 
  newTaskInput.focus();
}

/*
  function onDayClick(date) {
    const input = prompt(`Task für ${date.toLocaleDateString("de-DE")} eingeben:`);
    if (input) 
    {
      const timestamp = Math.floor(date.getTime() / 1000);
      saveTaskToDatabase(timestamp, input);
    }
  }
*/
  render();
</script>
