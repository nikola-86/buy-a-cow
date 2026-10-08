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
        elements: {
            point: { radius: 4, hoverRadius: 6 },
        },
        scales: {
            x: { offset: true },
            y: {
                beginAtZero: true,
                ticks: { precision: 0 },
            },
        },
    },
});
