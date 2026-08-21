(function(){
  var sidebar = document.getElementById('sidebar');
  var mainWrapper = document.getElementById('mainWrapper');
  var backdrop = document.getElementById('sidebarBackdrop');
  var toggleBtn = document.getElementById('sidebarToggle');

  function isMobile(){
    return window.innerWidth < 992;
  }

  function toggleSidebar(){
    if(isMobile()){
      sidebar.classList.toggle('show');
      backdrop.classList.toggle('show');
    } else {
      sidebar.classList.toggle('collapsed');
      mainWrapper.classList.toggle('collapsed');
    }
  }

  function closeMobileSidebar(){
    sidebar.classList.remove('show');
    backdrop.classList.remove('show');
  }

  function applyDefaultState(){
    if(isMobile()){
      // Mobile default: sidebar stays hidden until the user opens it.
      sidebar.classList.remove('collapsed');
      mainWrapper.classList.remove('collapsed');
    } else {
      // Desktop default: sidebar stays visible, off-canvas classes cleared.
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    }
  }

  applyDefaultState();

  toggleBtn.addEventListener('click', toggleSidebar);
  backdrop.addEventListener('click', closeMobileSidebar);

  document.querySelectorAll('.nav-item-custom').forEach(function(item){
    item.addEventListener('click', function(e){
      e.preventDefault();
      document.querySelectorAll('.nav-item-custom').forEach(function(el){el.classList.remove('active');});
      item.classList.add('active');
      if(isMobile()) closeMobileSidebar();
    });
  });

  window.addEventListener('resize', applyDefaultState);

  var ctx = document.getElementById('revenueChart');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Mar','Apr','May','Jun','Jul','Aug'],
      datasets: [{
        label: 'Revenue',
        data: [31200, 34800, 33100, 39400, 42750, 48290],
        borderColor: '#2F6F5E',
        backgroundColor: 'rgba(47,111,94,0.08)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.35,
        pointRadius: 0,
        pointHoverRadius: 5,
        pointBackgroundColor: '#2F6F5E'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display:false }, ticks:{ color:'#64748B', font:{ size:11 } } },
        y: {
          grid: { color:'#EEF1F4' },
          ticks: {
            color:'#64748B',
            font:{ size:11 },
            callback: function(v){ return '$' + (v/1000) + 'k'; }
          }
        }
      }
    }
  });
})();
