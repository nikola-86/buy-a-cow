const { series, rows } = JSON.parse(document.getElementById('report-data').textContent);

new Chart(document.getElementById('report-chart'), {
    type: 'line',
    data: {
        labels: rows.map((row) => row.day),
        datasets: Object.entries(series).map(([seriesKey, label]) => ({
            label,
            data: rows.map((row) => row[seriesKey]),
        })),
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 },
            },
        },
    },
});
