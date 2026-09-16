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
    $('.admin-mobile-table').each(function() {
        var headers = [];
        $(this).find('thead th').each(function() {
            headers.push($(this).text().replace(/\s+/g, ' ').trim());
        });
        $(this).find('tbody tr').each(function() {
            var cells = $(this).children('td');
            cells.each(function(index) {
                if (!$(this).attr('data-label') && headers[index]) {
                    $(this).attr('data-label', headers[index]);
                }
            });
            if (cells.length > 3 && !cells.first().find('.table-expand-toggle').length) {
                cells.first().append('<button type="button" class="table-expand-toggle" aria-expanded="false" aria-label="Show more details"><i class="bi bi-chevron-down"></i></button>');
            }
        });
    });

    $('.admin-mobile-table tbody').on('click', '.table-expand-toggle', function() {
        var button = $(this);
        var row = button.closest('tr');
        var expanded = row.toggleClass('table-row-expanded').hasClass('table-row-expanded');
        button.attr('aria-expanded', expanded ? 'true' : 'false');
        button.attr('aria-label', expanded ? 'Hide more details' : 'Show more details');
    });

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
