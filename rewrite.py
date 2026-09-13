import re

with open("c:\\xampp\\htdocs\\smsbyog\\buy-number-1.php", "r", encoding="utf-8") as f:
    content = f.read()

target = """$userwallet = $wallet->userwallet();
$servers    = $wallet->all_server();  // from otp_server WHERE status=1
$wallet->closeConnection();
// Check if this API is active — redirect if disabled
$_api_st = get_api_status($conn);
if (!$_api_st['server1']) { redirect('buy-number'); }


$page_title = "Buy Numbers — " . $site_data['web_name'];

// Map otp_server IDs to ISO 3166-1 alpha-2 codes for flag-icons library
$serverFlags = [
    187 => 'us',   // USA (VerifySMS)
    286 => 'ng',   // Nigeria (Server 1)
    999 => 'us',   // USA + Canada (show US flag, CA noted in name)
];

function getServerIso(string $name, int $id, array $map): string {
    if (isset($map[$id])) return $map[$id];
    $n = strtolower($name);
    if (str_contains($n, 'nigeria'))                                 return 'ng';
    if (str_contains($n, 'usa') || str_contains($n, 'united states')) return 'us';
    if (str_contains($n, 'canada'))                                  return 'ca';
    if (str_contains($n, 'uk') || str_contains($n, 'united kingdom')) return 'gb';
    if (str_contains($n, 'india'))                                   return 'in';
    if (str_contains($n, 'russia'))                                  return 'ru';
    if (str_contains($n, 'ghana'))                                   return 'gh';
    if (str_contains($n, 'kenya'))                                   return 'ke';
    return 'un'; // UN flag as fallback
}"""

replacement = """$userwallet = $wallet->userwallet();
$wallet->closeConnection();
// Check if this API is active — redirect if disabled
$_api_st = get_api_status($conn);
if (!$_api_st['server1']) { redirect('buy-number'); }

$page_title = "Buy Numbers — " . $site_data['web_name'];

// Get TigerSMS API details
$sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($sql4);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];

// Fetch TigerSMS countries and sort alphabetically
$countries_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getCountries";
$ch = curl_init($countries_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
curl_close($ch);
$countriesData = $raw ? json_decode($raw, true) : [];
if ($countriesData) {
    uasort($countriesData, function($a, $b) {
        $nameA = $a['eng'] ?? $a['name'] ?? '';
        $nameB = $b['eng'] ?? $b['name'] ?? '';
        return strcmp($nameA, $nameB);
    });
}

// Map them to ISO 3166-1 alpha-2 codes for flag-icons library
function countryFlag(string $engName): string {
    static $map = [
        'afghanistan'          => 'af', 'albania'              => 'al', 'algeria'            => 'dz',
        'angola'               => 'ao', 'antigua and barbuda'  => 'ag', 'argentinas'         => 'ar',
        'armenia'              => 'am', 'aruba'                => 'aw', 'australia'          => 'au',
        'austria'              => 'at', 'azerbaijan'           => 'az', 'bahamas'            => 'bs',
        'bahrain'              => 'bh', 'bangladesh'           => 'bd', 'barbados'           => 'bb',
        'belarus'              => 'by', 'belgium'              => 'be', 'belize'             => 'bz',
        'benin'                => 'bj', 'bhutane'              => 'bt', 'bih'                => 'ba',
        'bolivia'              => 'bo', 'botswana'             => 'bw', 'brazil'             => 'br',
        'brunei'               => 'bn', 'bulgaria'             => 'bg', 'burkina faso'       => 'bf',
        'burundi'              => 'bi', 'cambodia'             => 'kh', 'cameroon'           => 'cm',
        'canada'               => 'ca', 'cape verde'           => 'cv', 'cayman islands'     => 'ky',
        'chad'                 => 'td', 'chile'                => 'cl', 'china'              => 'cn',
        'colombia'             => 'co', 'comoros'              => 'km', 'congo'              => 'cg',
        'costa rica'           => 'cr', 'croatia'              => 'hr', 'cyprus'             => 'cy',
        'czech republic'       => 'cz', 'denmark'              => 'dk', 'djibouti'           => 'dj',
        'dominican republic'   => 'do', 'ecuador'              => 'ec', 'egypt'              => 'eg',
        'el salvador'          => 'sv', 'england'              => 'gb', 'equatorial guinea'  => 'gq',
        'eritrea'              => 'er', 'estonia'              => 'ee', 'ethiopia'           => 'et',
        'finland'              => 'fi', 'france'               => 'fr', 'french guiana'      => 'gf',
        'gabon'                => 'ga', 'gambia'               => 'gm', 'georgia'            => 'ge',
        'germany'              => 'de', 'ghana'                => 'gh', 'greece'             => 'gr',
        'guadeloupe'           => 'gp', 'guatemala'            => 'gt', 'guinea'             => 'gn',
        'guinea-bissau'        => 'gw', 'guyana'               => 'gy', 'haiti'              => 'ht',
        'honduras'             => 'hn', 'hong kong'            => 'hk', 'hungary'            => 'hu',
        'iceland'              => 'is', 'india'                => 'in', 'indonesia'          => 'id',
        'iraq'                 => 'iq', 'ireland'              => 'ie', 'israel'             => 'il',
        'italy'                => 'it', 'ivory coast'          => 'ci', 'jamaica'            => 'jm',
        'japan'                => 'jp', 'jordan'               => 'jo', 'kazakhstan'         => 'kz',
        'kenya'                => 'ke', 'kuwait'               => 'kw', 'kyrgyzstan'         => 'kg',
        'laos'                 => 'la', 'latvia'               => 'lv', 'lebanon'            => 'lb',
        'lesotho'              => 'ls', 'liberia'              => 'lr', 'lithuania'          => 'lt',
        'luxembourg'           => 'lu', 'macau'                => 'mo', 'madagascar'         => 'mg',
        'malawi'               => 'mw', 'malaysia'             => 'my', 'maldives'           => 'mv',
        'mali'                 => 'ml', 'mauritania'           => 'mr', 'mauritius'          => 'mu',
        'mexico'               => 'mx', 'moldova'              => 'md', 'monaco'             => 'mc',
        'mongolia'             => 'mn', 'montenegro'           => 'me', 'montserrat'         => 'ms',
        'morocco'              => 'ma', 'mozambique'           => 'mz', 'myanmar'            => 'mm',
        'namibia'              => 'na', 'nepal'                => 'np', 'netherlands'        => 'nl',
        'new caledonia'        => 'nc', 'new zealand'          => 'nz', 'nicaragua'          => 'ni',
        'niger'                => 'ne', 'nigeria'              => 'ng', 'north macedonia'    => 'mk',
        'norway'               => 'no', 'oman'                 => 'om', 'pakistan'           => 'pk',
        'palestine'            => 'ps', 'panama'               => 'pa', 'papua new guinea'   => 'pg',
        'paraguay'             => 'py', 'peru'                 => 'pe', 'philippines'        => 'ph',
        'poland'               => 'pl', 'portugal'             => 'pt', 'puerto rico'        => 'pr',
        'qatar'                => 'qa', 'reunion'              => 're', 'romania'            => 'ro',
        'russia'               => 'ru', 'rwanda'               => 'rw', 'samoa'              => 'ws',
        'saudi arabia'         => 'sa', 'senegal'              => 'sn', 'serbia'             => 'rs',
        'seychelles'           => 'sc', 'sierra leone'         => 'sl', 'singapore'          => 'sg',
        'slovakia'             => 'sk', 'slovenia'             => 'si', 'somalia'            => 'so',
        'south africa'         => 'za', 'south korea'          => 'kr', 'spain'              => 'es',
        'sri lanka'            => 'lk', 'sudan'                => 'sd', 'suriname'           => 'sr',
        'sweden'               => 'se', 'switzerland'          => 'ch', 'syrian arab republic' => 'sy',
        'taiwan'               => 'tw', 'tajikistan'           => 'tj', 'tanzania'           => 'tz',
        'thailand'             => 'th', 'timor-leste'          => 'tl', 'togo'               => 'tg',
        'tonga'                => 'to', 'trinidad and tobago'  => 'tt', 'tunisia'            => 'tn',
        'turkey'               => 'tr', 'turkmenistan'         => 'tm', 'turks and caicos islands' => 'tc',
        'uganda'               => 'ug', 'ukraine'              => 'ua', 'united arab emirates' => 'ae',
        'uruguay'              => 'uy', 'usa'                  => 'us', 'uzbekistan'         => 'uz',
        'venezuela'            => 've', 'vietnam'              => 'vn', 'yemen'              => 'ye',
        'zambia'               => 'zm', 'zimbabwe'             => 'zw'
    ];
    $n = strtolower($engName);
    return $map[$n] ?? 'un';
}"""

if target in content:
    content = content.replace(target, replacement)
    with open("c:\\xampp\\htdocs\\smsbyog\\buy-number-1.php", "w", encoding="utf-8") as f:
        f.write(content)
    print("Replaced chunk 1 successfully")
else:
    print("Chunk 1 not found")

target2 = """                  <div class="country-grid" id="country-grid">
                    <?php foreach ($servers as $s):
                        $iso = getServerIso($s['server_name'], (int)$s['id'], $serverFlags);
                    ?>
                    <div class="country-card" data-server="<?= $s['id'] ?>" onclick="selectCountry(this)">
                      <span class="fi fi-<?= $iso ?> country-flag-img"></span>
                      <div class="country-name"><?= htmlspecialchars($s['server_name']) ?></div>
                    </div>
                    <?php endforeach; ?>
                  </div>"""

replacement2 = """                  <!-- Search Bar for Countries -->
                  <div class="service-search-wrap mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="country-search" placeholder="Search countries..." onkeyup="filterCountries()">
                  </div>

                  <div class="country-grid" id="country-grid" style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($countriesData as $code => $country): 
                        $name = $country['eng'] ?? $country['name'] ?? $code;
                        $iso = countryFlag($name);
                    ?>
                    <div class="country-card" data-server="<?= htmlspecialchars($code) ?>" data-name="<?= htmlspecialchars(strtolower($name)) ?>" onclick="selectCountry(this)">
                      <span class="fi fi-<?= $iso ?> country-flag-img"></span>
                      <div class="country-name"><?= htmlspecialchars($name) ?></div>
                    </div>
                    <?php endforeach; ?>
                  </div>

                  <script>
                  function filterCountries() {
                      const query = document.getElementById('country-search').value.toLowerCase();
                      const cards = document.querySelectorAll('.country-card');
                      cards.forEach(card => {
                          const name = card.getAttribute('data-name');
                          if (name.includes(query)) {
                              card.style.display = 'flex';
                          } else {
                              card.style.display = 'none';
                          }
                      });
                  }
                  </script>"""

if target2 in content:
    content = content.replace(target2, replacement2)
    with open("c:\\xampp\\htdocs\\smsbyog\\buy-number-1.php", "w", encoding="utf-8") as f:
        f.write(content)
    print("Replaced chunk 2 successfully")
else:
    print("Chunk 2 not found")
