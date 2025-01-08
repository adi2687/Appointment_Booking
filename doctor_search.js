document.getElementById("doctor-search").addEventListener("input", function () {
    const query = this.value.trim();
    const suggestions = document.getElementById("suggestions");

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
                        const li = document.createElement("li");
                        li.innerHTML = `
                    <a href="../appointments/book_appointment_with_doctor.php?doctor_id=${item.unique_id}" class='doctor_search_result'>
                        ${item.fname} ${item.lname} (${item.specialty})
                    </a>`;
                        suggestions.appendChild(li);
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

// Close the dropdown when clicking outside the search bar
document.addEventListener("click", function (event) {
    const suggestions = document.getElementById("suggestions");
    const searchBox = document.getElementById("doctor-search");
    if (!searchBox.contains(event.target)) {
        suggestions.style.display = "none";
    }
});
