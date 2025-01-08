fetch("back.php")
            .then(response => response.json())
            .then(data => {
                const rootDiv = document.querySelector(".root");

                if (data.error) {
                    rootDiv.innerHTML = `<p>Error: ${data.error}</p>`;
                    return;
                }

                if (data.message) {
                    rootDiv.innerHTML = `<p>${data.message}</p>`;
                    return;
                }

                let htmlContent = '<h2>Appointments</h2>';
                data.forEach(appointment => {
                    htmlContent += `
                    <div>
                        <strong>Doctor:</strong> ${appointment.preferred_doctors}<br>
                        <strong>Date of Appointment:</strong> ${appointment.time}<br>
                        <strong>Message to the Doctor:</strong> ${appointment.message}<br>
                        <strong>Registered At:</strong> ${appointment.time_of_reg}<br>
                        <p onclick='more(event, ${JSON.stringify(appointment)})'>View more</p>
                        <hr>
                    </div>
                `;
                });

                rootDiv.innerHTML = htmlContent;
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                document.querySelector(".roots").innerHTML = "<p>Failed to fetch data.</p>";
            });

       

        function more(event, appointment) {
            event.stopPropagation(); // Prevent the click from bubbling up to the parent div
            let details = document.querySelector(".main .details");
            details.innerHTML = `
            <strong>Doctor:</strong> ${appointment.preferred_doctors}<br>
            <strong>Date of Appointment:</strong> ${appointment.time}<br>
            <strong>Message to the Doctor:</strong> ${appointment.message}<br>
            <strong>Registered At:</strong> ${appointment.time_of_reg}<br>
            <strong>Dosage:</strong> ${appointment.dosage}<br>
            <strong>Frequency:</strong> ${appointment.frequency}<br>
            <strong>Duration:</strong> ${appointment.duration}<br>
            <strong>Route:</strong> ${appointment.route}<br>
            <strong>Instructions:</strong> ${appointment.instructions}<br>
            <strong>Refills:</strong> ${appointment.refills}<br>
            <strong>Signature:</strong> ${appointment.signature}<br>
            <strong>Drug Interaction Warnings:</strong> ${appointment.drug_interaction_warnings}<br>
            <strong>Additional Notes:</strong> ${appointment.additional_notes}<br>`;
        }