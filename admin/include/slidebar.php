<style>
  @import url(https://fonts.googleapis.com/css?family=Signika:700,300,600);

  .radium {
    font-size: 18px;
    font: bold 18px/1.6 'Signika', sans-serif;
    user-select: none;
  }

  .radium span {
    display: inline-block;
    animation: float .2s ease-in-out infinite;
  }

  @keyframes float {

    0%,
    100% {
      transform: none;
    }

    33% {
      transform: translateY(-1px) rotate(-2deg);
    }

    66% {
      transform: translateY(1px) rotate(2deg);
    }
  }

  .radium:hover span {
    animation: bounce .6s;
  }

  @keyframes bounce {

    0%,
    100% {
      transform: translate(0);
    }

    25% {
      transform: rotateX(20deg) translateY(2px) rotate(-3deg);
    }

    50% {
      transform: translateY(-20px) rotate(3deg) scale(1.1);
    }
  }

  .radiumspan:nth-child(4n) {
    color: hsl(50, 75%, 55%);
    text-shadow: 1px 1px hsl(50, 75%, 45%), 2px 2px hsl(50, 45%, 45%), 3px 3px hsl(50, 45%, 45%), 4px 4px hsl(50, 75%, 45%);
  }

  .radium span:nth-child(4n-1) {
    color: hsl(135, 35%, 55%);
    text-shadow: 1px 1px hsl(135, 35%, 45%), 2px 2px hsl(135, 35%, 45%), 3px 3px hsl(135, 35%, 45%), 4px 4px hsl(135, 35%, 45%);
  }

  .radium span:nth-child(4n-2) {
    color: hsl(155, 35%, 60%);
    text-shadow: 1px 1px hsl(155, 25%, 50%), 2px 2px hsl(155, 25%, 50%), 3px 3px hsl(155, 25%, 50%), 4px 4px hsl(140, 25%, 50%);
  }

  .radium span:nth-child(4n-3) {
    color: hsl(30, 65%, 60%);
    text-shadow: 1px 1px hsl(30, 45%, 50%), 2px 2px hsl(30, 45%, 50%), 3px 3px hsl(30, 45%, 50%), 4px 4px hsl(30, 45%, 50%);
  }

  .radium span:nth-child(4n) {
    color: hsl(50, 75%, 55%);
    text-shadow: 1px 1px hsl(50, 75%, 45%), 2px 2px hsl(50, 45%, 45%), 3px 3px hsl(50, 45%, 45%), 4px 4px hsl(50, 75%, 45%);
  }

  .radium span:nth-child(4n-1) {
    color: hsl(135, 35%, 55%);
    text-shadow: 1px 1px hsl(135, 35%, 45%), 2px 2px hsl(135, 35%, 45%), 3px 3px hsl(135, 35%, 45%), 4px 4px hsl(135, 35%, 45%);
  }

  .radium span:nth-child(4n-2) {
    color: hsl(155, 35%, 60%);
    text-shadow: 1px 1px hsl(155, 25%, 50%), 2px 2px hsl(155, 25%, 50%), 3px 3px hsl(155, 25%, 50%), 4px 4px hsl(140, 25%, 50%);
  }

  .radium span:nth-child(4n-3) {
    color: hsl(30, 65%, 60%);
    text-shadow: 1px 1px hsl(30, 45%, 50%), 2px 2px hsl(30, 45%, 50%), 3px 3px hsl(30, 45%, 50%), 4px 4px hsl(30, 45%, 50%);
  }

  .radium span:nth-child(2) {
    animation-delay: .05s;
  }

  .radium span:nth-child(3) {
    animation-delay: .1s;
  }

  .radium span:nth-child(4) {
    animation-delay: .15s;
  }

  .radium span:nth-child(5) {
    animation-delay: .2s;
  }

  .radium span:nth-child(6) {
    animation-delay: .25s;
  }

  .radium span:nth-child(7) {
    animation-delay: .3s;
  }

  .radium span:nth-child(8) {
    animation-delay: .35s;
  }

  .radium span:nth-child(9) {
    animation-delay: .4s;
  }

  .radium span:nth-child(10) {
    animation-delay: .45s;
  }

  .radium span:nth-child(11) {
    animation-delay: .5s;
  }

  .radium span:nth-child(12) {
    animation-delay: .55s;
  }

  .radium span:nth-child(13) {
    animation-delay: .6s;
  }

  .radium span:nth-child(14) {
    animation-delay: .65s;
  }
</style>
<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
    <div class="sidebar-brand-icon rounded-circle">
      <img src="<?= $site_data['logo_url'] ?? 'https://i.ibb.co/ySRhxqh/default.png' ?>"
           style="width:50px; height:50px; object-fit:cover; border-radius:50%;"
           onerror="this.src='https://i.ibb.co/ySRhxqh/default.png'">
    </div>
  </a>
  <hr class="sidebar-divider my-0">
  <li class="nav-item active" id="dashboard">
    <a class="nav-link " href="dashboard">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span></a>
  </li>
  <li class="nav-item" id="all_user">
    <a class="nav-link " href="all_user">
      <i class="fas fa-fw fa-users"></i>
      <span>All User</span></a>
  </li>
  <li class="nav-item" id="all_user">
    <a class="nav-link " href="transactions">
      <i class="fas fa-fw fa-money-check"></i>
      <span>All Transactions</span></a>
  </li>
  <li class="nav-item" id="block_user">
    <a class="nav-link " href="block_user">
      <i class="fas fa-fw fa-user-alt-slash"></i>
      <span>Blocked User</span></a>
  </li>
  <li class="nav-item" id="admins">
    <a class="nav-link " href="admins">
      <i class="fas fa-fw fa-user-tie"></i>
      <span>View Admins</span></a>
  </li>
  
   <li class="nav-item" id="admins">
    <a class="nav-link " href="admin_manual_payments">
      <i class="fas fa-fw fa-user-tie"></i>
      <span>Manual Payments</span></a>
  </li>
  
  <li class="nav-item" id="admins">
    <a class="nav-link " href="admin_bank_settings">
      <i class="fas fa-fw fa-user-tie"></i>
      <span>Change Bank Details</span></a>
  </li>
  
  
  <li class="nav-item" id="top">
    <a class="nav-link " href="top">
      <i class="fas fa-fw fa-chart-bar"></i>
       <span>Sell History</span></a>
  </li>
  <li class="nav-item" id="find_user">
    <a class="nav-link " href="find_user">
      <i class="fas fa-fw fa-user-cog"></i>
      <span>Find User</span></a>
  </li>
  <!--<li class="nav-item" id="show_service">-->
  <!--  <a class="nav-link " href="show_service">-->
  <!--    <i class="fas fa-fw fa-server"></i>-->
  <!--    <span>Show Service</span></a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="add_service">-->
  <!--  <a class="nav-link" href="add_service">-->
  <!--    <i class="fas fa-fw fa-folder-plus"></i>-->
  <!--    <span>Add Service</span>-->
  <!--  </a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="show_server">-->
  <!--  <a class="nav-link " href="show_server">-->
  <!--    <i class="fas fa-fw fa-server"></i>-->
  <!--    <span>Show Server</span></a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="add_server">-->
  <!--  <a class="nav-link" href="add_server">-->
  <!--    <i class="fas fa-fw fa-folder-plus"></i>-->
  <!--    <span>Add Server</span>-->
  <!--  </a>-->
  <!--</li>-->

  <li class="nav-item" id="add_api">
    <a class="nav-link" href="add_api">
      <i class="fas fa-fw fa-code-branch"></i>
      <span>Add APIs</span>
    </a>
  </li>
  <li class="nav-item" id="show_api">
    <a class="nav-link " href="show_api">
      <i class="fas fa-fw fa-server"></i>
      <span>Show Api</span></a>
  </li>
  <!--<li class="nav-item" id="upi_transaction">-->
  <!--  <a class="nav-link " href="upi_transaction">-->
  <!--    <i class="fas fa-fw fa-compress-arrows-alt"></i>-->
  <!--    <span>Upi Transactions</span></a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="crypto_transaction">-->
  <!--  <a class="nav-link " href="crypto_transaction">-->
  <!--    <i class="fas fa-fw fa-ticket-alt"></i>-->
  <!--    <span>Crypto Transactions</span></a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="refer_transaction">-->
  <!--  <a class="nav-link" href="refer_transaction">-->
  <!--    <i class="fas fa-fw fa-money-check"></i>-->
  <!--    <span>Refer Transaction</span>-->
  <!--  </a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="bharatpe">-->
  <!--  <a class="nav-link " href="bharatpe">-->
  <!--    <i class="fas fa-fw fa-file-code"></i>-->
  <!--    <span>Bharatpe Settings</span></a>-->
  <!--</li>-->
  <li class="nav-item" id="number-wait">
    <a class="nav-link " href="number-wait">
      <i class="fas fa-fw fa-wrench"></i>
      <span>Cancelled Numbers</span></a>
  </li>
  <!--<li class="nav-item" id="promocode">-->
  <!--  <a class="nav-link" href="promocode">-->
  <!--    <i class="fas fa-fw fa-gift"></i>-->
  <!--    <span>Promocode Settings</span>-->
  <!--  </a>-->
  <!--</li>-->
  <li class="nav-item" id="today_otp">
    <a class="nav-link" href="today_otp">
      <i class="fas fa-fw fa-portrait"></i>
      <span>Number History</span>
    </a>
  </li>
  <!--<li class="nav-item" id="custom_price">-->
  <!--  <a class="nav-link" href="custom_price">-->
  <!--    <i class="fas fa-fw fa-pen"></i>-->
  <!--    <span>Set Custom Price</span>-->
  <!--  </a>-->
  <!--</li>-->
  <!--<li class="nav-item" id="image_data">-->
  <!--  <a class="nav-link" href="image_data">-->
  <!--    <i class="fas fa-fw fa-palette"></i>-->
  <!--    <span>Service Image</span>-->
  <!--  </a>-->
  <!--</li>-->
  <li class="nav-item" id="top_service">
    <a class="nav-link" href="top_service">
      <i class="fas fa-fw fa-award"></i>
      <span>Top Services</span>
    </a>
  </li>
  <hr class="sidebar-divider">
  <div class="version" style="color:red; font-size:15px; font-weight: bold;">v2.0</div>
</ul>