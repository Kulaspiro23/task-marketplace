document.addEventListener("DOMContentLoaded", () => {
  // Cache references to DOM elements
  const filterSelect = document.getElementById("filter");
  const categorySelect = document.getElementById("category");
  const skillsSelect = document.getElementById("skills");
  const searchInput = document.getElementById("search");
  const tasksContainer = document.getElementById("tasks-container");

  let searchTimer;

  // Attach event listeners to filter elements
  filterSelect.addEventListener("change", updateTasks);
  categorySelect.addEventListener("change", updateTasks);
  skillsSelect.addEventListener("change", updateTasks);

  // Debounce search input events (500ms delay)
  searchInput.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(updateTasks, 500);
  });

  // Optionally poll the server every 5 seconds for updated tasks
  setInterval(updateTasks, 5000);

  /**
   * Update the tasks container based on current filter values.
   */
  function updateTasks() {
    // Get current filter values
    const filter = filterSelect.value;
    const category = categorySelect.value;
    const skills = Array.from(skillsSelect.selectedOptions).map(option => option.value);
    const search = searchInput.value;

    // Build URL with query parameters
    const url = new URL(window.location.origin + "/");
    url.searchParams.set("filter", filter);
    url.searchParams.set("category", category);
    url.searchParams.set("skills", skills.join(",")); // Pass skills as comma-separated values
    url.searchParams.set("search", search);
    url.searchParams.set("page", 1); // Reset to page 1 when filters change

    // Optionally update the browser's address bar without reloading the page
    window.history.pushState({}, "", url);

    // Fetch updated HTML and replace the tasks container's content
    fetch(url)
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const updatedTasksContainer = doc.getElementById("tasks-container");
        if (updatedTasksContainer) {
          tasksContainer.innerHTML = updatedTasksContainer.innerHTML;
        }
      })
      .catch(error => console.error("Error fetching tasks:", error));
  }
});
