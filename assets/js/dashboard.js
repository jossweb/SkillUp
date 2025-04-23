var blurredBg = document.getElementById('blurred-bg');
  var popup = document.getElementById('delete-check');
  var hiddenInput = document.getElementById('id-c');

  function OpenPopup(id){
    blurredBg.style.display = "flex";
    blurredBg.style.opacity = 1;
    popup.style.display = "flex";
    popup.style.opacity = 1;
    hiddenInput.value = id;
    
  }
  function CloseDeleteCheck(){
    blurredBg.style.display = "none";
    blurredBg.style.opacity = 0;
    popup.style.display = "none";
    popup.style.opacity = 0;
  }
  const today = new Date();
  const day = String(today.getDate()).padStart(2, '0');
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const formattedDate = `${day}/${month}`

  document.addEventListener("DOMContentLoaded", function () {
  var ctx = document.getElementById("myBarChart").getContext("2d");

  function getLast7Days() {
    const dates = [];
    const today = new Date();

    for (let i = 30; i >= 0; i--) {
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
      data: [8, 10, 2, 7, 17, 15, 19, 8, 10, 2, 7, 17, 15, 19, 8, 10, 2, 7, 17, 15, 19, 8, 10, 2, 7, 17, 15, 19, 25, 19, 3],
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