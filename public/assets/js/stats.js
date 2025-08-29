(() => {
  const base = document.documentElement.dataset.base || '';
  const url = `${base}/index.php?r=spital/stats/data`;

  const $ = (id) => document.getElementById(id);

  fetch(url, { credentials: 'same-origin' })
    .then((r) => r.json())
    .then((stats) => {
      Chart.defaults.color = '#e7eef7';
      Chart.defaults.borderColor = '#263258';

      const ap = stats.appointments_by_status || [];
      new Chart($('appointmentsChart'), {
        type: 'doughnut',
        data: {
          labels: ap.map((x) => x.status_name),
          datasets: [
            {
              data: ap.map((x) => +x.count),
              backgroundColor: ['#ffbe0b', '#06d6a0', '#ef476f'],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
        },
      });

      const bt = stats.patients_by_blood_type || [];
      new Chart($('bloodTypeChart'), {
        type: 'pie',
        data: {
          labels: bt.map((x) => x.blood_type || 'Unknown'),
          datasets: [
            {
              data: bt.map((x) => +x.count),
              backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40',
                '#FF6B6B',
                '#4ECDC4',
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
        },
      });

      const dd = stats.doctors_by_department || [];
      new Chart($('departmentChart'), {
        type: 'bar',
        data: {
          labels: dd.map((x) => x.department),
          datasets: [
            {
              label: 'Doctors',
              data: dd.map((x) => +x.count),
              backgroundColor: 'rgba(58,134,255,.7)',
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true } },
        },
      });

      const occ = stats.room_occupancy || [];
      new Chart($('occupancyChart'), {
        type: 'doughnut',
        data: {
          labels: occ.map((x) => x.status),
          datasets: [
            {
              data: occ.map((x) => +x.count),
              backgroundColor: ['#ef476f', '#06d6a0'],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } },
        },
      });
    })
    .catch((err) => {
      console.error('Stats fetch error', err);
      document.body.insertAdjacentHTML(
        'afterbegin',
        '<div style="background:#fee;color:#900;padding:10px;border:1px solid #f99;margin:10px;">Failed to load statistics.</div>',
      );
    });
})();
