<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="""UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>test - dashboard</title>
  <style>
    .chart-container {
      width: 550px;
      height: 300px;
      margin: auto;
    }
    canvas {
      width: 100% !important;
      height: 100% !important;
      background-color: #000;
      border-radius: 20px;
    }
  </style>
</head>
<body>
  <div class="chart-container">
    <canvas id="myBarChart"></canvas>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const today = new Date();
  const day = String(today.getDate()).padStart(2, '0');
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const formattedDate = `${day}/${month}`

  document.addEventListener("DOMContentLoaded", function () {
  var ctx = document.getElementById("myBarChart").getContext("2d");

  function getLast7Days() {
    const dates = [];
    const today = new Date();

    for (let i = 6; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(today.getDate() - i);

      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');

      dates.push(`${day}/${month}`);
    }

    return dates;
  }
  var myBarChart = new Chart(ctx, {
    type: "bar",
    data: {
    labels: getLast7Days(),
    datasets: [
      {
        
      label: "Nouveaux inscrits cette semaine",
      data: [12, 19, 3, 5, 2, 3, 9],
      backgroundColor: "rgba(160, 66, 240, 0.8)",
      borderColor: "rgba(0, 0, 0, 1)",
      borderWidth: 1,
      },
    ],
    },
    options: {
    responsive: true,
    maintainAspectRatio: false, 
    plugins: {
      legend: {
      position: 'top',
      },
    },
    scales: {
      y: {
      beginAtZero: true,
      },
    },
    },
  });
  });
</script>
</html>