const labels = stats.map(item => item.item_name ?? item.description ?? "Item");
const data = stats.map(item => parseInt(item.total_qty_out ?? item.qty_out ?? 0));

const chartCanvas = document.getElementById("stockOutChart");

// Each bar is 30px, spacing 5px
const barHeight = 20;
const barSpacing = 23;

// Total canvas height
chartCanvas.height = labels.length * (barHeight + barSpacing);

const ctx = chartCanvas.getContext("2d");

Chart.register(ChartDataLabels);

new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [{
            label: "Total Quantity Out",
            data: data,
            backgroundColor: "rgba(18, 55, 101, 0.7)",
            borderColor: "rgba(18, 55, 101, 1)",
            borderWidth: 1,
            barThickness: barHeight,
            datalabels: {
                anchor: "end",
                align: "right",
                color: "#000",
                formatter: v => v,
                offset: 10,
                font: { size: 14, weight: "bold" }
            }
        }]
    },
    options: {
        indexAxis: "y", // horizontal bars
        responsive: false, // IMPORTANT: let canvas height control itself
        maintainAspectRatio: false,
        layout: { padding: { right: 40 } },
        plugins: { legend: { display: false }, tooltip: { enabled: true } },
        scales: {
            x: { beginAtZero: true },
            y: { ticks: { autoSkip: false } }
        }
    }
});
