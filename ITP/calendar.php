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
      <option value="day">Tagesansicht</option>
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
        <div class="form-group">
            <label class="checkbox-container">
                <input type="checkbox" id="allDayCheckbox" checked>
                <span class="checkmark"></span>
                Ganztägig
            </label>
        </div>
        <div id="timeSelection" class="time-selection" style="display: none;">
            <div class="time-row">
                <div class="time-group">
                    <label for="startTime">Startzeit:</label>
                    <input type="time" id="startTime" value="09:00">
                </div>
                <div class="time-group">
                    <label for="endTime">Endzeit:</label>
                    <input type="time" id="endTime" value="10:00">
                </div>
            </div>
        </div>
        
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
    const allDayCheckbox = document.getElementById("allDayCheckbox");
    const timeSelection = document.getElementById("timeSelection");
    const startTimeInput = document.getElementById("startTime");
    const endTimeInput = document.getElementById("endTime");

  allDayCheckbox.addEventListener("change", () => {
    if (allDayCheckbox.checked) {
      timeSelection.style.display = "none";
    } else {
      timeSelection.style.display = "block";
    }
  });

  document.getElementById("prev").addEventListener("click", () => {
    if (viewMode === "year") currentDate.setFullYear(currentDate.getFullYear() - 1);
    else if (viewMode === "month") currentDate.setMonth(currentDate.getMonth() - 1);
    else if (viewMode === "week") currentDate.setDate(currentDate.getDate() - 7);
    else if (viewMode === "day") currentDate.setDate(currentDate.getDate() - 1);
    render();
});

  document.getElementById("next").addEventListener("click", () => {
    if (viewMode === "year") currentDate.setFullYear(currentDate.getFullYear() + 1);
    else if (viewMode === "month") currentDate.setMonth(currentDate.getMonth() + 1);
    else if (viewMode === "week") currentDate.setDate(currentDate.getDate() + 7);
    else if (viewMode === "day") currentDate.setDate(currentDate.getDate() + 1);
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
          const isAllDay = allDayCheckbox.checked;
          let startTimestamp, endTimestamp = null;
          
          if (isAllDay) { // For All-Day Tasks it defaults to the beginning of the day
              const dayStart = new Date(selectedDate);
              dayStart.setHours(0, 0, 0, 0);
              startTimestamp = Math.floor(dayStart.getTime() / 1000);
          } else {
              const [startHour, startMinute] = startTimeInput.value.split(':').map(Number);
              const [endHour, endMinute] = endTimeInput.value.split(':').map(Number);
              
              const startDateTime = new Date(selectedDate);
              startDateTime.setHours(startHour, startMinute, 0, 0);
              startTimestamp = Math.floor(startDateTime.getTime() / 1000);
              
              const endDateTime = new Date(selectedDate);
              endDateTime.setHours(endHour, endMinute, 0, 0);
              endTimestamp = Math.floor(endDateTime.getTime() / 1000);
              
              if (endTimestamp <= startTimestamp) {
                  alert("Endzeit muss nach der Startzeit liegen!");
                  return;
              }
          }
          
          const success = await saveTaskToDatabase(startTimestamp, endTimestamp, taskText, isAllDay);
          if (success) 
          {
              newTaskInput.value = "";
              allDayCheckbox.checked = true;
              timeSelection.style.display = "none";
              startTimeInput.value = "09:00";
              endTimeInput.value = "10:00";
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
        } else if (viewMode === "day") {
            const startOfDay = new Date(currentDate);
            startOfDay.setHours(0, 0, 0, 0);
            const endOfDay = new Date(currentDate);
            endOfDay.setHours(23, 59, 59, 999);
            startTimestamp = Math.floor(startOfDay.getTime() / 1000);
            endTimestamp = Math.floor(endOfDay.getTime() / 1000);
        }

        if (startTimestamp === null || endTimestamp === null) {
            console.error("Timestamps not set for fetch.");
            return;
        }

        const url = `fetch_task.php?start=${startTimestamp}&end=${endTimestamp}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) 
        {
            userTasks = data.tasks;
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
    } else if (viewMode === "day") {
      grid.style.gridTemplateColumns = "1fr";
      grid.style.justifyContent = "center";
      renderDay(currentDate);
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

  function renderDay(date) 
  {
    titleDisplay.textContent = date.toLocaleDateString("de-DE", { weekday: "long", year: "numeric", month: "long", day: "numeric" });

    const dayContainer = document.createElement("div");
    dayContainer.className = "day-view";

    const timeGrid = document.createElement("div");
    timeGrid.className = "time-grid";

    for (let h = 0; h < 24; h++) 
    {
      const hourBlock = document.createElement("div");
      hourBlock.className = "hour-block";

      const timeLabel = document.createElement("strong");
      timeLabel.textContent = `${h}:00`;
      hourBlock.appendChild(timeLabel);

      const currentHourStart = new Date(date.getFullYear(), date.getMonth(), date.getDate(), h, 0, 0, 0);
      const currentHourEnd = new Date(date.getFullYear(), date.getMonth(), date.getDate(), h + 1, 0, 0, 0);

      const tasksIntersectingHour = userTasks.filter(task => {
          const taskStartDate = new Date(task.datum_unix * 1000);
          const taskEndDate = task.end_time ? new Date(task.end_time * 1000) : null;

          if (task.all_day) 
          {
              const taskDayStart = new Date(taskStartDate);
              taskDayStart.setHours(0, 0, 0, 0);
              const currentDayStart = new Date(date);
              currentDayStart.setHours(0, 0, 0, 0);
              return taskDayStart.getTime() === currentDayStart.getTime();
          } else {
              const taskStartTimestamp = taskStartDate.getTime();
              const taskEndTimestamp = taskEndDate ? taskEndDate.getTime() : taskStartTimestamp;
              const startsIn = taskStartTimestamp >= currentHourStart.getTime() && taskStartTimestamp < currentHourEnd.getTime();
              const endsIn = taskEndTimestamp > currentHourStart.getTime() && taskEndTimestamp <= currentHourEnd.getTime();
              const spansAcross = taskStartTimestamp < currentHourStart.getTime() && taskEndTimestamp > currentHourEnd.getTime();

              return startsIn || endsIn || spansAcross;
          }
      });

        tasksIntersectingHour.sort((a, b) => (new Date(a.datum_unix * 1000)).getTime() - (new Date(b.datum_unix * 1000)).getTime());

        if (tasksIntersectingHour.length > 0) 
        {
            hourBlock.classList.add("has-task-in-hour"); 
            const taskListDiv = document.createElement("div");
            taskListDiv.className = "tasks-summary";
            hourBlock.appendChild(taskListDiv);
            tasksIntersectingHour.forEach(task => {
                const taskLine = document.createElement("p");
                taskLine.className = "task-display-line"; 

                let taskText = task.beschreibung;
                if (!task.all_day) {
                    const startTime = new Date(task.datum_unix * 1000);
                    const endTime = task.end_time ? new Date(task.end_time * 1000) : null;
                    const startStr = startTime.toLocaleTimeString("de-DE", { hour: '2-digit', minute: '2-digit' });
                    const endStr = endTime ? endTime.toLocaleTimeString("de-DE", { hour: '2-digit', minute: '2-digit' }) : '';
                    taskText = `${startStr}${endStr ? ' - ' + endStr : ''} ${taskText}`;
                } else {
                    taskLine.classList.add("all-day-task-display");
                }
                taskLine.textContent = taskText;
                taskListDiv.appendChild(taskLine);
            });
        }

        hourBlock.addEventListener("click", () => {
            const clicked = new Date(date);
            clicked.setHours(h, 0, 0, 0);
            onDayClick(clicked);
        });

        timeGrid.appendChild(hourBlock);
    }
    dayContainer.appendChild(timeGrid);
    grid.appendChild(dayContainer);
  }

  function createMonthElement(date) 
  {
    const monthEl = document.createElement("div");
    monthEl.className = "month";

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

  async function saveTaskToDatabase(startTimestamp, endTimestamp, task, isAllDay)
  {
    const formData = new FormData();
    formData.append('start_timestamp', startTimestamp);
    formData.append('end_timestamp', endTimestamp || '');
    formData.append('task', task);
    formData.append('all_day', isAllDay ? '1' : '0');

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
            taskDiv.className = "task-item";
            
            let taskDisplayText = task.beschreibung;
            let taskTypeClass = "";
            
            if (task.all_day) {
                taskDisplayText += " (Ganztägig)";
                taskTypeClass = "all-day-task";
            } else {
                const startTime = new Date(task.datum_unix * 1000);
                const startStr = startTime.toLocaleTimeString("de-DE", { hour: '2-digit', minute: '2-digit' });
                
                if (task.end_time) {
                    const endTime = new Date(task.end_time * 1000);
                    const endStr = endTime.toLocaleTimeString("de-DE", { hour: '2-digit', minute: '2-digit' });
                    taskDisplayText += ` (${startStr} - ${endStr})`;
                } else {
                    taskDisplayText += ` (${startStr})`;
                }
                taskTypeClass = "timed-task";
            }
            
            taskDiv.innerHTML = `
                <span class="task-text ${taskTypeClass}">${taskDisplayText}</span>
                <button class="delete-btn" data-termin-id="${task.termin_id}">Löschen</button>
            `;
            tasksList.appendChild(taskDiv);

            taskDiv.querySelector('.delete-btn').addEventListener('click', (event) => {
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
  
  if (date.getHours() !== 0 || date.getMinutes() !== 0) {
    const startHour = String(date.getHours()).padStart(2, '0');
    const startMinute = String(date.getMinutes()).padStart(2, '0');
    startTimeInput.value = `${startHour}:${startMinute}`;
    
    const endDate = new Date(date);
    endDate.setHours(endDate.getHours() + 1);
    const endHour = String(endDate.getHours()).padStart(2, '0');
    const endMinute = String(endDate.getMinutes()).padStart(2, '0');
    endTimeInput.value = `${endHour}:${endMinute}`;
    
    allDayCheckbox.checked = false;
    timeSelection.style.display = "block";
  } else {
    allDayCheckbox.checked = true;
    timeSelection.style.display = "none";
    startTimeInput.value = "09:00";
    endTimeInput.value = "10:00";
  }
  
  displayTasksForSelectedDate(date); 
  taskModal.style.display = "block"; 
  newTaskInput.focus();
}

  render();
</script>