document.addEventListener("DOMContentLoaded", () => {
  // --- Cache DOM elements for modals & forms ---
  const createTaskModal = document.getElementById("createTaskModal");
  const editTaskModal = document.getElementById("editTaskModal");
  const deleteTaskModal = document.getElementById("deleteTaskModal");

  const createTaskForm = document.getElementById("createTaskForm");
  const editTaskForm = document.getElementById("editTaskForm");

  // --- Cache DOM elements for dashboard content ---
  const createdContainer = document.getElementById("tab-content-created");
  const takenContainer = document.getElementById("tab-content-taken");
  const archivedContainer = document.getElementById("tab-content-archived");

  const createTaskBtn = document.getElementById("createTaskBtn");
  const closeCreateModalBtn = document.getElementById("closeCreateModal");
  const closeEditModalBtn = document.getElementById("closeEditModal");
  const confirmDeleteBtn = document.getElementById("confirmDelete");
  const cancelDeleteBtn = document.getElementById("cancelDelete");

  const searchInput = document.getElementById("searchInput");
  
  // --- Tab switching elements ---
  const tabButtons = document.querySelectorAll('.tab-button');
  const tabContents = document.querySelectorAll('.tab-content');

  let taskToDeleteId = null;
  const today = new Date().toISOString().slice(0, 16);
  
  // --- Set minimum date for deadlines ---
  const createDeadlineInput = document.getElementById("deadline");
  if (createDeadlineInput) createDeadlineInput.setAttribute("min", today);
  const editDeadlineInput = document.getElementById("editDeadline");
  if (editDeadlineInput) editDeadlineInput.setAttribute("min", today);

  // --- Helper: Get CSRF Token ---
  const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

  // --- Tab Switching Logic ---
  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      // Remove active styles from all tab buttons and hide all tab contents
      tabButtons.forEach(btn => btn.classList.remove('active', 'border-blue-500', 'text-blue-500'));
      tabContents.forEach(content => content.classList.add('hidden'));
      
      // Activate clicked tab
      button.classList.add('active', 'border-blue-500', 'text-blue-500');
      const tabKey = button.getAttribute('data-tab'); // Expected values: "created", "taken", "archived"
      document.getElementById(`tab-content-${tabKey}`).classList.remove('hidden');
    });
  });

  // --- Refresh Dashboard Tasks List ---
  const refreshTasksList = () => {
    fetch("/dashboard", {
      headers: {
        "Accept": "application/json", // Request JSON format
        "X-Requested-With": "XMLHttpRequest" // Mark as AJAX
      }
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        // Clear current contents
        createdContainer.innerHTML = "";
        takenContainer.innerHTML = "";
        archivedContainer.innerHTML = "";
        
        // Populate each container with task cards
        data.createdTasks.forEach(task => {
          createdContainer.insertAdjacentHTML("beforeend", generateTaskCard(task, "created"));
        });
        data.takenTasks.forEach(task => {
          takenContainer.insertAdjacentHTML("beforeend", generateTaskCard(task, "taken"));
        });
        data.archivedTasks.forEach(task => {
          archivedContainer.insertAdjacentHTML("beforeend", generateTaskCard(task, "archived"));
        });
      })
      .catch(error => {
        console.error("Error refreshing tasks list:", error);
      });
  };

  // --- Generate HTML for a Task Card ---
  function generateTaskCard(task, type) {
    let cardHTML = `
      <div class="card p-4 border rounded mb-4">
        <h3 class="text-lg font-semibold">${task.title}</h3>
        <p class="text-gray-700">${task.description}</p>
        <p class="text-sm text-gray-500">Category: ${task.category}</p>
        <p class="text-sm text-gray-500">Skills: ${Array.isArray(task.skills) ? task.skills.join(', ') : task.skills}</p>
        <p class="text-sm text-gray-500">Deadline: ${task.deadline ? new Date(task.deadline).toLocaleString() : 'No deadline'}</p>
        <p class="text-sm text-gray-500">Status: ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}</p>
    `;

    if (type === "created") {
      cardHTML += `<p class="text-sm text-gray-500">Taker: ${task.taker ? task.taker.name : 'No taker yet'}</p>`;
    } else if (type === "taken") {
      cardHTML += `<p class="text-sm text-gray-500">Posted by: ${task.user.name}</p>`;
    } else if (type === "archived") {
      cardHTML += `<p class="text-sm text-gray-500">Posted by: ${task.user.name}</p>`;
      if (task.taker) {
        cardHTML += `<p class="text-sm text-gray-500">Taker: ${task.taker.name}</p>`;
      }
    }
    
    // Include action buttons (Edit and Delete) for tasks you created
    if (type === "created") {
      cardHTML += `
        <div class="flex space-x-2 mt-2">
          <button class="edit-btn bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm" data-task='${JSON.stringify(task)}'>Edit</button>
          <button class="delete-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm" data-task-id="${task.id}">Delete</button>
        </div>
      `;
    }
    
    cardHTML += `</div>`;
    return cardHTML;
  }

  // --- Filtering & Sorting (Client-Side Example) ---
  // (This example simply filters visible cards based on search text;
  // sorting could be implemented by reordering the DOM elements.)
  searchInput.addEventListener('input', () => {
    const searchTerm = searchInput.value.toLowerCase();
    // For the currently active tab:
    const activeTab = document.querySelector('.tab-button.active').getAttribute('data-tab');
    const container = document.getElementById(`tab-content-${activeTab}`);
    const cards = container.querySelectorAll(".card");
    cards.forEach(card => {
      if (card.textContent.toLowerCase().includes(searchTerm)) {
        card.classList.remove("hidden");
      } else {
        card.classList.add("hidden");
      }
    });
  });

  // --- Modal Open/Close Handlers ---
  createTaskBtn.addEventListener("click", () => {
    createTaskModal.classList.remove("hidden");
  });
  closeCreateModalBtn.addEventListener("click", () => {
    createTaskModal.classList.add("hidden");
  });
  closeEditModalBtn.addEventListener("click", () => {
    editTaskModal.classList.add("hidden");
  });

  // --- Create Task ---
  createTaskForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(createTaskForm);
    const data = Object.fromEntries(formData.entries());
    const submitButton = createTaskForm.querySelector("button[type='submit']");

    submitButton.disabled = true;
    submitButton.textContent = "Creating...";

    fetch("/tasks", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(data => {
        if (data.id) {
          createTaskModal.classList.add("hidden");
          createTaskForm.reset();
          refreshTasksList();
        } else {
          alert("Error creating task. Please try again.");
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
      })
      .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = "Create Task";
      });
  });

  // --- Edit Task ---
  editTaskForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(editTaskForm);
    const data = Object.fromEntries(formData.entries());
    const taskId = document.getElementById("editTaskId").value;
    const submitButton = editTaskForm.querySelector("button[type='submit']");

    submitButton.disabled = true;
    submitButton.textContent = "Updating...";
    fetch(`/tasks/${taskId}`, {
      method: "PUT",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(data => {
        if (data.id) {
          editTaskModal.classList.add("hidden");
          editTaskForm.reset();
          refreshTasksList();
        } else {
          alert("Error updating task. Please try again.");
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
      })
      .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = "Update Task";
      });
  });

  // --- Event Delegation for Edit and Delete Buttons in Tab Containers ---
  const handleTaskAction = (e) => {
    // Edit Button
    if (e.target.closest(".edit-btn")) {
      const btn = e.target.closest(".edit-btn");
      const task = JSON.parse(btn.getAttribute("data-task"));
      openEditModal(task);
    }
    // Delete Button
    if (e.target.closest(".delete-btn")) {
      const btn = e.target.closest(".delete-btn");
      const taskId = btn.getAttribute("data-task-id");
      openDeleteModal(taskId);
    }
  };

  // Attach delegation on each tab container
  createdContainer.addEventListener("click", handleTaskAction);
  takenContainer.addEventListener("click", handleTaskAction);
  archivedContainer.addEventListener("click", handleTaskAction);

  // --- Open Edit Modal ---
  function openEditModal(task) {
    document.getElementById("editTaskId").value = task.id;
    document.getElementById("editTitle").value = task.title;
    document.getElementById("editDescription").value = task.description;
    document.getElementById("editCategory").value = task.category;
    document.getElementById("editSkills").value = Array.isArray(task.skills) ? task.skills.join(", ") : task.skills;
    
    let deadlineVal = task.deadline;
    if (deadlineVal) {
      // Create a Date object from the deadline string
      const dateObj = new Date(deadlineVal);
      // Format the date parts (ensuring two digits for month, day, hours, and minutes)
      const year = dateObj.getFullYear();
      const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);
      const day = ('0' + dateObj.getDate()).slice(-2);
      const hours = ('0' + dateObj.getHours()).slice(-2);
      const minutes = ('0' + dateObj.getMinutes()).slice(-2);
      // Build the string in the required format: YYYY-MM-DDTHH:MM
      deadlineVal = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    document.getElementById("editDeadline").value = deadlineVal; 
    editTaskModal.classList.remove("hidden");
  }
  

  // --- Delete Task Handlers ---
  function openDeleteModal(taskId) {
    taskToDeleteId = taskId;
    deleteTaskModal.classList.remove("hidden");
  }
  function closeDeleteModal() {
    deleteTaskModal.classList.add("hidden");
    taskToDeleteId = null;
  }
  function deleteTask() {
    if (!taskToDeleteId) return;
    confirmDeleteBtn.disabled = true;
    confirmDeleteBtn.textContent = "Deleting...";

    fetch(`/tasks/${taskToDeleteId}`, {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "Accept": "application/json"
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closeDeleteModal();
          refreshTasksList();
        } else {
          alert("Error deleting task. Please try again.");
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
      })
      .finally(() => {
        confirmDeleteBtn.disabled = false;
        confirmDeleteBtn.textContent = "Delete";
      });
  }
  cancelDeleteBtn.addEventListener("click", closeDeleteModal);
  confirmDeleteBtn.addEventListener("click", deleteTask);

  // --- Initial load ---
  refreshTasksList();
});
