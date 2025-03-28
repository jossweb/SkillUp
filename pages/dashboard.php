<?php
    require_once("../include/config.php"); 
    $titre = SITE_NAME . ' - Accueil';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH; ?>/dashboard.css"> 
    <title>Dashboard</title>
<body>
    <div class="stats-container">
        <h1>Statistiques du professeur</h1>
        <p class="subtitle">Aperçu de vos performances et interactions</p>
        
        <div class="stats-header">
            <div class="stat-box">
                <h3>Cours publiés :</h3>
                <p>9</p>
            </div>
            <div class="stat-box">
                <h3>Vues</h3>
                <p>121</p>
            </div>
            <div class="stat-box">
                <h3>J’aimes :</h3>
                <p>14</p>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="myBarChart"></canvas>
          </div>
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
      data: [8, 10, 2, 7, 17, 15, 19],
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