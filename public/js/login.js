document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("login-form").addEventListener("submit", async function (event) {
        event.preventDefault(); // Prevent page reload

        let form = event.target;
        let formData = new FormData(form);

        // Clear previous errors
        document.querySelectorAll(".error-message").forEach(el => el.textContent = "");
        document.getElementById("general-error").textContent = "";

        try {
            let response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                }
            });

            let result = await response.json(); // Parse JSON response

            if (response.ok) {
                window.location.href = result.redirect; // Redirect if successful
            } else if (response.status === 422) {
                // Handle Laravel validation errors
                for (let field in result.errors) {
                    let errorSpan = document.getElementById(`${field}-error`);
                    if (errorSpan) {
                        errorSpan.textContent = result.errors[field][0];
                    }
                }
            } else {
                document.getElementById("general-error").textContent = "Invalid login credentials.";
            }
        } catch (error) {
            console.error("Fetch error:", error);
            document.getElementById("general-error").textContent = "Something went wrong. Please try again.";
        }
    });
});
