//test
document.addEventListener("DOMContentLoaded", () => {
  // --- Cache DOM elements ---
  const createTaskModal = document.getElementById("createTaskModal");
  const editTaskModal = document.getElementById("editTaskModal");
  const deleteTaskModal = document.getElementById("deleteTaskModal");

  const createTaskForm = document.getElementById("createTaskForm");
  const editTaskForm = document.getElementById("editTaskForm");

  const tasksList = document.getElementById("tasksList");
  const createTaskBtn = document.getElementById("createTaskBtn");

  const closeCreateModalBtn = document.getElementById("closeCreateModal");
  const closeEditModalBtn = document.getElementById("closeEditModal");

  const confirmDeleteBtn = document.getElementById("confirmDelete");
  const cancelDeleteBtn = document.getElementById("cancelDelete");

  let taskToDeleteId = null;

  // --- Helper: Get CSRF Token ---
  const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

  // --- Helper: Refresh Tasks List ---
  const refreshTasksList = () => {
    fetch("/dashboard")
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newTasksList = doc.getElementById("tasksList");
        if (newTasksList) tasksList.innerHTML = newTasksList.innerHTML;
      })
      .catch(error => console.error("Error refreshing tasks list:", error));
  };

  // --- Modal open/close handlers ---
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

  // --- Event Delegation for Edit and Delete Buttons ---
  tasksList.addEventListener("click", (e) => {
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
  });

  // --- Open Edit Modal ---
  function openEditModal(task) {
    document.getElementById("editTaskId").value = task.id;
    document.getElementById("editTitle").value = task.title;
    document.getElementById("editDescription").value = task.description;
    document.getElementById("editCategory").value = task.category;
    document.getElementById("editSkills").value = Array.isArray(task.skills) ? task.skills.join(", ") : task.skills;
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
});
