<?php
$url  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page = basename($url);
?>
</head>

<?php 
echo ($page === 'button-builder.php')
  ? '<body class="button-builder">'
  : '<body>';
?>

<style>
/* ================= PRELOADER OVERLAY ================= */
.loader-wrapper {
  position: fixed;
  inset: 0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999;
  transition: opacity .35s ease, visibility .35s ease;
}

.loader-wrapper.hide {
  opacity: 0;
  visibility: hidden;
}

/* ================= LOADER CONTAINER ================= */
.loader {
  position: relative;
  width: 110px;
  height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ================= ROTATING RING ================= */
.loader::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 4px solid rgba(37, 99, 235, 0.2);
  border-top-color: #2563eb;
  animation: spin 1s linear infinite;
}

/* ================= LOGO ================= */
.loader img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  z-index: 1;
}

/* ================= ANIMATION ================= */
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>

<wc-toast id="tt" position="top-center"></wc-toast>

<!-- ================= PRELOADER ================= -->
<div class="loader-wrapper" id="page-loader">
  <div class="loader">
    <img src="https://smsbyog.com/favicon.ico" alt="AuthPadi Loading">
  </div>
</div>

<!-- ================= TAP TOP ================= -->
<div class="tap-top">
  <i data-feather="chevrons-up"></i>
</div>

<script>
window.addEventListener('load', () => {
  const loader = document.getElementById('page-loader');
  if (loader) {
    setTimeout(() => loader.classList.add('hide'), 250);
  }
});
</script>