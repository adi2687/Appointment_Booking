document.getElementById("doctor-search2").addEventListener("input", function () {
    const query = this.value.trim();
    const suggestions = document.getElementById("suggestions2");

    if (query.length > 0) {
        fetch(`../search_doctors1.php?query=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                suggestions.innerHTML = ""; // Clear previous results

                if (data.length > 0) {
                    data.forEach(item => {
                        const li = document.createElement("div");
                        li.innerHTML = `
                            <div class="doctor-item">
                                <input type="checkbox" data-doctor='${JSON.stringify(item)}'>
                                <span>${item.fname} ${item.lname} (${item.specialty})</span>
                            </div>`;
                        suggestions.appendChild(li);
                    });

                    // Add event listeners for checkboxes
                    const checkboxes = suggestions.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener("change", handleDoctorSelection);
                    });
                } else {
                    const li = document.createElement("li");
                    li.classList.add("no-results");
                    li.textContent = "No doctors found";
                    suggestions.appendChild(li);
                }

                // Show the dropdown
                suggestions.style.display = "block";
            })
            .catch(error => {
                console.error("Error fetching data:", error);
            });
    } else {
        // Hide the dropdown if input is empty
        suggestions.innerHTML = "";
        suggestions.style.display = "none";
    }
});

// Function to handle adding/removing doctors from the selected list
function handleDoctorSelection(event) {
    const selectedDoctorsDiv = document.querySelector(".selecteddoctors");
    const checkbox = event.target;
    const doctor = JSON.parse(checkbox.getAttribute("data-doctor"));

    if (checkbox.checked) {
        // Add doctor to the selected list
        const doctorDiv = document.createElement("div");
        doctorDiv.classList.add("selected-doctor");
        doctorDiv.setAttribute("data-id", doctor.id);
        doctorDiv.innerHTML = `
            <span>${doctor.fname} ${doctor.lname} (${doctor.specialty})</span>
            <button class="remove-btn">Remove</button>
            
            <br>
            <button>Book Appointment</button>`;
        selectedDoctorsDiv.appendChild(doctorDiv);

        // Add event listener to remove button
        doctorDiv.querySelector(".remove-btn").addEventListener("click", function () {
            // Uncheck the checkbox
            checkbox.checked = false;
            // Remove the doctor from the selected list
            selectedDoctorsDiv.removeChild(doctorDiv);
        });
    } else {
        // Remove doctor from the selected list
        const doctorDiv = selectedDoctorsDiv.querySelector(`.selected-doctor[data-id='${doctor.id}']`);
        if (doctorDiv) {
            selectedDoctorsDiv.removeChild(doctorDiv);
        }
    }
}


