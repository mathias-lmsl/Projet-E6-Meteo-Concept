document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('graphiqueCapteur').getContext('2d');

    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,  // Données passées via PHP
            datasets: [{
                label: 'Température (°C)',
                data: values,  // Données passées via PHP
                borderColor: 'red',
                backgroundColor: 'rgba(255, 0, 0, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: 'Horodatage' }
                },
                y: {
                    title: { display: true, text: 'Valeur' },
                    beginAtZero: false
                }
            }
        }
    });
});
