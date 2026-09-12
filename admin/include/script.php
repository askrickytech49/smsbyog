  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <!-- Sidebar toggle script -->
  <script>
  (function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar   = document.getElementById('adminSidebar');
    const overlay   = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', function() {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Desktop collapse
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const wrapper     = document.getElementById('adminWrapper');
    if (collapseBtn) collapseBtn.addEventListener('click', function() {
        wrapper.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', wrapper.classList.contains('sidebar-collapsed'));
    });
    // Restore collapse state — ONLY on desktop
    if (wrapper && window.innerWidth >= 992 && localStorage.getItem('sidebarCollapsed') === 'true') {
        wrapper.classList.add('sidebar-collapsed');
    }
    // On resize to mobile — always remove collapsed state
    window.addEventListener('resize', function() {
        if (window.innerWidth < 992 && wrapper) {
            wrapper.classList.remove('sidebar-collapsed');
        }
    });
  })();

  // Initialize DataTables
  $(function() {
    $('.admin-datatable').each(function() {
        var orderAttr = $(this).attr('data-order');
        var dtOrder = orderAttr ? JSON.parse(orderAttr) : [[0, 'asc']];
        $(this).DataTable({
            responsive: true,
            pageLength: 25,
            order: dtOrder,
            language: { search: '', searchPlaceholder: 'Search...' }
        });
    });
  });
  </script>
