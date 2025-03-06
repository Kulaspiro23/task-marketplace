document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("register-form").addEventListener("submit", async function (event) {
        event.preventDefault(); // Prevent page reload

        let form = event.target;
        let formData = new FormData(form);

        // Clear previous error messages
        document.querySelectorAll(".error-message").forEach(el => el.textContent = "");

        try {
            let response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                }
            });

            let result = await response.json();

            if (response.ok) {
                window.location.href = result.redirect; // Redirect on success
            } else if (response.status === 422) {
                // Display validation errors
                for (let field in result.errors) {
                    let errorSpan = document.getElementById(`${field}-error`);
                    if (errorSpan) {
                        errorSpan.textContent = result.errors[field][0];
                    }
                }
            }
        } catch (error) {
            console.warn("Error:", error);
        }
    });
});
