<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
include __DIR__ . '/include/api_status.php';
require __DIR__ . '/class/class.control.php';

if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_ck = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
        $_cv = mysqli_query($conn, "SELECT lt.user_id FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$_ck' AND lt.status='1' AND u.status='1' LIMIT 1");
        if ($_cv && mysqli_num_rows($_cv) === 1) {
            $_SESSION['token'] = $_COOKIE['remember_me'];
        } else {
            session_destroy();
            setcookie('remember_me', '', ['expires' => time()-3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            redirect('login');
        }
    } else {
        session_destroy();
        redirect('login');
    }
}

$wallet   = new radiumsahil();
$userdata = $wallet->userdata();

if ($userdata === false) {
    unset($_SESSION['token']);
    session_destroy();
    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true]);
    redirect('login');
}

$userwallet = $wallet->userwallet();
$wallet->closeConnection();
// Check if this API is active — redirect if disabled
$_api_st = get_api_status($conn);
if (!$_api_st['server2']) { redirect('buy-number'); }


// Fetch 5sim countries and sort alphabetically
$raw = @file_get_contents('https://5sim.net/v1/guest/countries');
$countriesData = $raw ? json_decode($raw, true) : [];
if ($countriesData) {
    uasort($countriesData, fn($a, $b) => strcmp($a['text_en'], $b['text_en']));
}

// 5sim uses full lowercase country names as keys (e.g. "afghanistan", "unitedstates")
// Map them to ISO 3166-1 alpha-2 codes for flag-icons library
function countryFlag(string $fiveSimKey): string {
    static $map = [
        'afghanistan'          => 'af', 'albania'              => 'al', 'algeria'            => 'dz',
        'angola'               => 'ao', 'antiguaandbarbuda'    => 'ag', 'argentina'          => 'ar',
        'armenia'              => 'am', 'aruba'                => 'aw', 'australia'          => 'au',
        'austria'              => 'at', 'azerbaijan'           => 'az', 'bahamas'            => 'bs',
        'bahrain'              => 'bh', 'bangladesh'           => 'bd', 'barbados'           => 'bb',
        'belarus'              => 'by', 'belgium'              => 'be', 'belize'             => 'bz',
        'benin'                => 'bj', 'bhutane'              => 'bt', 'bih'                => 'ba',
        'bolivia'              => 'bo', 'botswana'             => 'bw', 'brazil'             => 'br',
        'brunei'               => 'bn', 'bulgaria'             => 'bg', 'burkinafaso'        => 'bf',
        'burundi'              => 'bi', 'cambodia'             => 'kh', 'cameroon'           => 'cm',
        'canada'               => 'ca', 'capeverde'            => 'cv', 'chad'               => 'td',
        'chile'                => 'cl', 'colombia'             => 'co', 'comoros'            => 'km',
        'congo'                => 'cg', 'costarica'            => 'cr', 'croatia'            => 'hr',
        'cyprus'               => 'cy', 'czech'                => 'cz', 'denmark'            => 'dk',
        'djibouti'             => 'dj', 'dominicana'           => 'do', 'easttimor'          => 'tl',
        'ecuador'              => 'ec', 'egypt'                => 'eg', 'england'            => 'gb',
        'equatorialguinea'     => 'gq', 'estonia'              => 'ee', 'ethiopia'           => 'et',
        'finland'              => 'fi', 'france'               => 'fr', 'frenchguiana'       => 'gf',
        'gabon'                => 'ga', 'gambia'               => 'gm', 'georgia'            => 'ge',
        'germany'              => 'de', 'ghana'                => 'gh', 'greece'             => 'gr',
        'guadeloupe'           => 'gp', 'guatemala'            => 'gt', 'guinea'             => 'gn',
        'guineabissau'         => 'gw', 'guyana'               => 'gy', 'haiti'              => 'ht',
        'honduras'             => 'hn', 'hongkong'             => 'hk', 'hungary'            => 'hu',
        'india'                => 'in', 'indonesia'            => 'id', 'ireland'            => 'ie',
        'israel'               => 'il', 'italy'                => 'it', 'ivorycoast'         => 'ci',
        'jamaica'              => 'jm', 'jordan'               => 'jo', 'kazakhstan'         => 'kz',
        'kenya'                => 'ke', 'kuwait'               => 'kw', 'kyrgyzstan'         => 'kg',
        'laos'                 => 'la', 'latvia'               => 'lv', 'lesotho'            => 'ls',
        'liberia'              => 'lr', 'lithuania'            => 'lt', 'luxembourg'         => 'lu',
        'macau'                => 'mo', 'madagascar'           => 'mg', 'malawi'             => 'mw',
        'malaysia'             => 'my', 'maldives'             => 'mv', 'mauritania'         => 'mr',
        'mauritius'            => 'mu', 'mexico'               => 'mx', 'moldova'            => 'md',
        'mongolia'             => 'mn', 'montenegro'           => 'me', 'morocco'            => 'ma',
        'mozambique'           => 'mz', 'namibia'              => 'na', 'nepal'              => 'np',
        'netherlands'          => 'nl', 'newcaledonia'         => 'nc', 'nicaragua'          => 'ni',
        'nigeria'              => 'ng', 'northmacedonia'       => 'mk', 'norway'             => 'no',
        'oman'                 => 'om', 'pakistan'             => 'pk', 'panama'             => 'pa',
        'papuanewguinea'       => 'pg', 'paraguay'             => 'py', 'peru'               => 'pe',
        'philippines'          => 'ph', 'poland'               => 'pl', 'portugal'           => 'pt',
        'puertorico'           => 'pr', 'reunion'              => 're', 'romania'            => 'ro',
        'rwanda'               => 'rw', 'russia'               => 'ru', 'saintkittsandnevis' => 'kn',
        'saintlucia'           => 'lc', 'saintvincentandgrenadines' => 'vc', 'salvador'      => 'sv',
        'samoa'                => 'ws', 'saudiarabia'          => 'sa', 'senegal'            => 'sn',
        'serbia'               => 'rs', 'seychelles'           => 'sc', 'sierraleone'        => 'sl',
        'slovakia'             => 'sk', 'slovenia'             => 'si', 'solomonislands'     => 'sb',
        'southafrica'          => 'za', 'spain'                => 'es', 'srilanka'           => 'lk',
        'suriname'             => 'sr', 'swaziland'            => 'sz', 'sweden'             => 'se',
        'taiwan'               => 'tw', 'tajikistan'           => 'tj', 'tanzania'           => 'tz',
        'thailand'             => 'th', 'tit'                  => 'tt', 'togo'               => 'tg',
        'tunisia'              => 'tn', 'turkmenistan'         => 'tm', 'uganda'             => 'ug',
        'ukraine'              => 'ua', 'uruguay'              => 'uy', 'usa'                => 'us',
        'uzbekistan'           => 'uz', 'venezuela'            => 've', 'vietnam'            => 'vn',
        'zambia'               => 'zm', 'singapore'            => 'sg', 'switzerland'        => 'ch',
        'turkey'               => 'tr', 'southkorea'           => 'kr', 'japan'              => 'jp',
        'china'                => 'cn', 'myanmar'              => 'mm', 'mali'               => 'ml',
    ];

    // Normalise: strip spaces and lowercase
    $key = strtolower(preg_replace('/\s+/', '', $fiveSimKey));
    $iso = $map[$key] ?? 'un'; // UN flag as fallback
    return '<span class="fi fi-' . $iso . ' country-flag-img"></span>';
}

$page_title = "Buy Numbers (Server 2) — " . $site_data['web_name'];
?>
<?php include 'partial/header.php'; ?>
<link rel="stylesheet" href="css/buy-flow.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
<style>
/* Server 2 country grid — more compact for 150+ countries */
.country-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
.country-search-wrap { position:relative; margin-bottom:14px; }
.country-search-wrap input {
  width:100%; height:46px; border:1.5px solid #e5e7eb; border-radius:12px;
  padding:0 16px 0 42px; font-size:14px; background:#f9fafb; outline:none; transition:border-color .2s;
}
.country-search-wrap input:focus { border-color:#e10700; background:#fff; }
.country-search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:15px; }
.country-scroll { max-height:420px; overflow-y:auto; padding-right:4px; }
.country-scroll::-webkit-scrollbar { width:5px; }
.country-scroll::-webkit-scrollbar-thumb { background:#e5e7eb; border-radius:10px; }
</style>
<?php include 'partial/loader.php'; ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <?php include 'partial/topbar.php'; ?>
  <div class="page-body-wrapper">
    <?php include 'partial/sidebar.php'; ?>
    <div class="page-body">
      <div class="container-fluid py-4">
        <div class="row justify-content-center">
          <div class="col-xl-7 col-lg-8 col-md-10 col-12">

            <!-- SERVER SWITCHER — dynamic -->
            <div class="server-switcher">
              <?php $_as = get_api_status($conn); ?>
              <?php if($_as['server1']): ?><a href="buy-number-1" class="server-switch-btn"><i class="bi bi-flag-fill"></i> Server 1</a><?php endif; ?>
              <?php if($_as['server2']): ?><span class="server-switch-btn current"><i class="bi bi-globe"></i> Server 2</span><?php endif; ?>
              <?php if($_as['usa']): ?><a href="buy-usa-number" class="server-switch-btn"><i class="bi bi-flag"></i> USA Only</a><?php endif; ?>
              <?php if($_as['usaca']): ?><a href="buy-us-ca-number" class="server-switch-btn"><i class="bi bi-globe2"></i> USA + Canada</a><?php endif; ?>
            </div>

            <!-- MAIN CARD -->
            <div class="card shadow-sm" style="border-radius:20px; border:1.5px solid #f0f0f0;">
              <div class="card-body p-4">

                <!-- STEP INDICATOR -->
                <div class="step-indicator">
                  <div class="step-item active" id="si-1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Country</span>
                  </div>
                  <div class="step-item" id="si-2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Service</span>
                  </div>
                  <div class="step-item" id="si-3">
                    <div class="step-circle">3</div>
                    <span class="step-label">OTP</span>
                  </div>
                </div>

                <!-- HIDDEN FIELDS -->
                <input type="hidden" id="token"       value="<?= htmlspecialchars($_SESSION['token']) ?>">
                <input type="hidden" id="server_no"   value="">
                <input type="hidden" id="service_id"  value="">
                <input type="hidden" id="operator_id" value="">

                <!-- ── STEP 1: COUNTRY ── -->
                <div class="step-panel active" id="step1">
                  <h6 class="fw-bold mb-3 text-center" style="color:#111;">Select a Country</h6>

                  <div class="country-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="country-search" placeholder="Search country...">
                  </div>

                  <div class="country-scroll">
                    <div class="country-grid" id="country-grid">
                      <?php foreach ($countriesData as $iso => $details):
                        if (empty($details['text_en'])) continue;
                      ?>
                      <div class="country-card" data-server="<?= htmlspecialchars($iso) ?>"
                           data-name="<?= htmlspecialchars(strtolower($details['text_en'])) ?>"
                           onclick="selectCountry(this)">
                        <?= countryFlag($iso) ?>
                        <div class="country-name"><?= htmlspecialchars($details['text_en']) ?></div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <!-- ── STEP 2: SERVICE ── -->
                <div class="step-panel" id="step2">
                  <button class="step-back-btn" onclick="goStep(1)">
                    <i class="bi bi-arrow-left"></i> Back
                  </button>
                  <h6 class="fw-bold mb-3" id="step2-title" style="color:#111;">Select a Service</h6>

                  <div class="buy-bar">
                    <div class="buy-bar-info">
                      <span class="buy-bar-label">Selected service</span>
                      <span class="buy-bar-name"  id="selected-name">—</span>
                      <span class="buy-bar-price" id="selected-price">₦0</span>
                    </div>
                    <button class="buy-bar-btn" id="buy-btn" disabled onclick="doBuy()">
                      <i class="bi bi-cart-plus-fill"></i> Buy Number
                    </button>
                  </div>

                  <div class="service-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="service-search" placeholder="Search service...">
                  </div>

                  <div class="service-list" id="service-list">
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                  </div>
                </div>

                <!-- ── STEP 3: OTP ── -->
                <div class="step-panel" id="step3">
                  <div class="step3-header" id="step3-header"><div class="success-icon"><i class="bi bi-check-lg"></i></div><h5 id="step3-title-text">Your Active Number</h5><p id="step3-subtitle">Waiting for your OTP code…</p></div>
                  <div id="card-container"></div>
                  <div class="text-center mt-3">
                    <button class="step-back-btn" onclick="buyAnother()">
                      <i class="bi bi-plus-circle"></i> Buy Another Number
                    </button>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <?php include 'partial/footer.php'; ?>
  </div>
</div>

<?php include 'partial/scripts.php'; ?>
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>')</script>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script>
// Country search filter (step 1 only — large list)
document.getElementById('country-search').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#country-grid .country-card').forEach(card => {
        card.style.display = (card.dataset.name || '').includes(q) ? '' : 'none';
    });
});
</script>
<script src="js/main_server2.js"></script>
<?php include 'partial/footer-end.php'; ?>
