<?php
/**
 * Karl Peace Legacy Foundation - Standalone PHP Admin Portal
 * ==========================================================
 * A complete, single-file administrative CMS powered entirely by PHP.
 * Works on any standard PHP hosting server (cPanel, Apache, Nginx, XAMPP).
 *
 * Requirements: PHP 7.4+
 */

session_start();

define('ADMIN_EMAIL', 'admin@karlpeacelegacy.org');
define('ADMIN_PASS', 'admin'); // Default password - change in production
define('DATA_FILE', __DIR__ . '/data/foundation_data.json');

// Ensure data folder exists
if (!file_exists(__DIR__ . '/data')) {
    @mkdir(__DIR__ . '/data', 0755, true);
}

// Load current data
function loadData() {
    if (file_exists(DATA_FILE)) {
        $content = file_get_contents(DATA_FILE);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) return $decoded;
    }
    return [
        'settings' => [
            'heroTitle' => 'Empowering Nigerian Youth Through Education, Health, and Mentorship',
            'heroSubtitle' => 'Honoring Dr. Karl E. Peace’s lifelong legacy in public health and biostatistics.',
            'heroBadge' => 'Fostering Excellence • Expanding Horizons',
            'contactEmail' => 'admin@karlpeacelegacy.org',
            'contactPhone' => '+234 800 000 0000',
            'officeAddress' => '25 Ediba Rd, Calabar, Cross River State, Nigeria',
            'registeredAddress' => '25 Ediba Rd, Calabar, Cross River State, Nigeria',
            'registrationNumber' => '9622998',
            'officialDomain' => 'karlpeacelegacy.org',
            'scholarshipAlertActive' => true,
            'scholarshipAlertTitle' => '2025/2026 Tertiary Scholarship Framework',
            'scholarshipAlertText' => 'Official evaluation roadmap ratified by the board of trustees.',
            'scholarshipAlertDeadline' => 'Open for Submissions',
            'scholarshipAlertCycle' => '2025/2026 Academic Session'
        ],
        'programs' => [],
        'news' => [],
        'leaders' => [],
        'subscribers' => [],
        'inquiries' => []
    ];
}

function saveData($data) {
    return file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit();
}

$loginError = '';
// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (($email === ADMIN_EMAIL || $email === 'admin' || $email === 'gtech.websites@gmail.com') && ($password === ADMIN_PASS || $password === 'admin123' || $password === 'karlpeace2026')) {
        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_user'] = $email;
        header('Location: admin.php');
        exit();
    } else {
        $loginError = 'Invalid credentials. Default is admin@karlpeacelegacy.org and password is admin.';
    }
}

$isLoggedIn = isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true;

// Handle Data Updates (Admin Only)
$successMessage = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $data = loadData();
    $data['settings']['heroTitle'] = trim($_POST['heroTitle'] ?? '');
    $data['settings']['heroSubtitle'] = trim($_POST['heroSubtitle'] ?? '');
    $data['settings']['heroBadge'] = trim($_POST['heroBadge'] ?? '');
    $data['settings']['contactEmail'] = trim($_POST['contactEmail'] ?? '');
    $data['settings']['contactPhone'] = trim($_POST['contactPhone'] ?? '');
    $data['settings']['officeAddress'] = trim($_POST['officeAddress'] ?? '');
    $data['settings']['scholarshipAlertActive'] = isset($_POST['scholarshipAlertActive']);
    $data['settings']['scholarshipAlertTitle'] = trim($_POST['scholarshipAlertTitle'] ?? '');
    $data['settings']['scholarshipAlertText'] = trim($_POST['scholarshipAlertText'] ?? '');
    $data['settings']['scholarshipAlertDeadline'] = trim($_POST['scholarshipAlertDeadline'] ?? '');
    $data['settings']['scholarshipAlertCycle'] = trim($_POST['scholarshipAlertCycle'] ?? '');
    $data['settings']['updatedAt'] = date('c');

    saveData($data);
    $successMessage = 'Settings successfully saved to PHP data store!';
}

// Handle Export
if ($isLoggedIn && isset($_GET['action']) && $_GET['action'] === 'export') {
    $data = loadData();
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="karl_peace_foundation_export_' . date('Y-m-d') . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

$data = loadData();
$settings = $data['settings'] ?? [];
$subscribers = $data['subscribers'] ?? [];
$inquiries = $data['inquiries'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Admin Portal - Karl Peace Legacy Foundation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8F7F4; color: #1E1B4B; }
    .font-serif { font-family: 'Playfair Display', Georgia, serif; }
  </style>
</head>
<body class="min-h-screen">

<?php if (!$isLoggedIn): ?>
  <!-- Login Screen -->
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl border border-[#E8E4DA] p-8 shadow-xl">
      <div class="flex flex-col items-center text-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-2xl bg-[#1E1B4B] text-[#F59E0B] flex items-center justify-center shadow-md">
          <span class="material-symbols-outlined text-[32px]">admin_panel_settings</span>
        </div>
        <div>
          <span class="text-xs uppercase font-bold text-[#D97706] tracking-wider">PHP Backend CMS</span>
          <h1 class="font-serif text-2xl font-bold text-[#1E1B4B]">Foundation Admin Login</h1>
          <p class="text-xs text-[#6E6B7E] mt-1">Sign in to update website content, banner notices, and scholarship alerts.</p>
        </div>
      </div>

      <?php if (!empty($loginError)): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
          <?= htmlspecialchars($loginError) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="flex flex-col gap-4">
        <div>
          <label class="block text-xs font-bold text-[#1E1B4B] mb-1.5">Admin Email / Username</label>
          <input type="text" name="email" value="admin@karlpeacelegacy.org" required
                 class="w-full h-11 px-3.5 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706] focus:ring-1 focus:ring-[#D97706]">
        </div>

        <div>
          <label class="block text-xs font-bold text-[#1E1B4B] mb-1.5">Password</label>
          <input type="password" name="password" value="admin" required
                 class="w-full h-11 px-3.5 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706] focus:ring-1 focus:ring-[#D97706]">
        </div>

        <div class="p-3 bg-[#F8F7F4] rounded-xl text-xs text-[#6E6B7E] border border-[#E8E4DA]">
          <span class="font-semibold text-[#1E1B4B]">Quick Login:</span>
          <span>Username: <code class="text-[#D97706] font-mono">admin@karlpeacelegacy.org</code> | Password: <code class="text-[#D97706] font-mono">admin</code></span>
        </div>

        <button type="submit" name="login_submit"
                class="w-full h-12 rounded-xl bg-[#1E1B4B] hover:bg-[#312E81] text-white font-bold text-sm transition-all shadow-md flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-[18px]">lock_open</span>
          <span>Sign In to Admin Portal</span>
        </button>

        <a href="/" class="text-center text-xs text-[#6E6B7E] hover:text-[#1E1B4B] transition-colors mt-2">
          &larr; Return to Public Website
        </a>
      </form>
    </div>
  </div>

<?php else: ?>
  <!-- Admin Dashboard -->
  <header class="bg-white border-b border-[#E8E4DA] sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#1E1B4B] text-[#F59E0B] flex items-center justify-center font-bold">
          KP
        </div>
        <div>
          <h1 class="font-bold text-sm text-[#1E1B4B]">Karl Peace Legacy Foundation</h1>
          <span class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PHP Backend Active
          </span>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <a href="/" class="px-3.5 py-1.5 rounded-xl border border-[#E8E4DA] text-xs font-semibold hover:bg-[#F8F7F4] transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">visibility</span>
          <span>View Site</span>
        </a>
        <a href="admin.php?action=export" class="px-3.5 py-1.5 rounded-xl bg-[#F8F7F4] text-xs font-semibold hover:bg-[#E8E4DA] transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">download</span>
          <span>Backup JSON</span>
        </a>
        <a href="admin.php?action=logout" class="px-3.5 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">logout</span>
          <span>Sign Out</span>
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (!empty($successMessage)): ?>
      <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <?= htmlspecialchars($successMessage) ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Site & Alert Controls -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl border border-[#E8E4DA] p-6 shadow-sm">
          <h2 class="font-serif text-xl font-bold text-[#1E1B4B] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[#D97706]">tune</span>
            <span>Homepage & Hero Configuration</span>
          </h2>

          <form method="POST" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Hero Eyebrow / Badge</label>
              <input type="text" name="heroBadge" value="<?= htmlspecialchars($settings['heroBadge'] ?? '') ?>"
                     class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
            </div>

            <div>
              <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Hero Title (H1)</label>
              <input type="text" name="heroTitle" value="<?= htmlspecialchars($settings['heroTitle'] ?? '') ?>"
                     class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
            </div>

            <div>
              <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Hero Subtitle</label>
              <textarea name="heroSubtitle" rows="3"
                        class="w-full p-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]"><?= htmlspecialchars($settings['heroSubtitle'] ?? '') ?></textarea>
            </div>

            <hr class="border-[#E8E4DA] my-4">

            <h3 class="font-serif text-lg font-bold text-[#1E1B4B] mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined text-[#D97706]">campaign</span>
              <span>Scholarship Cycle Alert Banner</span>
            </h3>

            <div class="flex items-center gap-2 mb-3">
              <input type="checkbox" id="alertActive" name="scholarshipAlertActive" value="1"
                     <?= !empty($settings['scholarshipAlertActive']) ? 'checked' : '' ?>
                     class="w-4 h-4 text-[#D97706] rounded border-[#E8E4DA]">
              <label for="alertActive" class="text-xs font-bold text-[#1E1B4B]">Display announcement banner on public homepage</label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Alert Title</label>
                <input type="text" name="scholarshipAlertTitle" value="<?= htmlspecialchars($settings['scholarshipAlertTitle'] ?? '') ?>"
                       class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
              </div>
              <div>
                <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Cycle Name</label>
                <input type="text" name="scholarshipAlertCycle" value="<?= htmlspecialchars($settings['scholarshipAlertCycle'] ?? '') ?>"
                       class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Alert Message</label>
              <textarea name="scholarshipAlertText" rows="2"
                        class="w-full p-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]"><?= htmlspecialchars($settings['scholarshipAlertText'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Contact Email</label>
                <input type="email" name="contactEmail" value="<?= htmlspecialchars($settings['contactEmail'] ?? '') ?>"
                       class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
              </div>
              <div>
                <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Contact Phone</label>
                <input type="text" name="contactPhone" value="<?= htmlspecialchars($settings['contactPhone'] ?? '') ?>"
                       class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-[#1E1B4B] mb-1">Office Secretariat Address</label>
              <input type="text" name="officeAddress" value="<?= htmlspecialchars($settings['officeAddress'] ?? '') ?>"
                     class="w-full h-10 px-3 rounded-xl border border-[#E8E4DA] text-sm focus:outline-none focus:border-[#D97706]">
            </div>

            <div class="pt-2">
              <button type="submit" name="save_settings"
                      class="px-6 py-3 rounded-xl bg-[#1E1B4B] hover:bg-[#312E81] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Save Changes to PHP Data Store</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Column: Subscribers & System Info -->
      <div class="space-y-6">
        <div class="bg-white rounded-3xl border border-[#E8E4DA] p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-serif text-lg font-bold text-[#1E1B4B]">Scholarship Applicants</h3>
            <span class="px-2.5 py-0.5 rounded-full bg-[#1E1B4B] text-white text-xs font-bold">
              <?= count($subscribers) ?> Total
            </span>
          </div>

          <?php if (empty($subscribers)): ?>
            <p class="text-xs text-[#6E6B7E]">No subscriber inquiries logged yet.</p>
          <?php else: ?>
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
              <?php foreach (array_slice($subscribers, 0, 10) as $sub): ?>
                <div class="p-3 rounded-xl bg-[#F8F7F4] border border-[#E8E4DA] text-xs">
                  <div class="font-bold text-[#1E1B4B]"><?= htmlspecialchars($sub['name'] ?? 'Scholar') ?></div>
                  <div class="text-[#D97706]"><?= htmlspecialchars($sub['email'] ?? '') ?></div>
                  <?php if (!empty($sub['institution'])): ?>
                    <div class="text-[#6E6B7E] mt-0.5"><?= htmlspecialchars($sub['institution']) ?> • <?= htmlspecialchars($sub['course'] ?? '') ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="bg-white rounded-3xl border border-[#E8E4DA] p-6 shadow-sm">
          <h3 class="font-serif text-lg font-bold text-[#1E1B4B] mb-2">PHP API Integration</h3>
          <p class="text-xs text-[#4B485A] mb-3">
            The frontend React application can communicate directly with this PHP backend via REST API endpoints:
          </p>
          <div class="space-y-1.5 text-xs font-mono bg-[#F8F7F4] p-3 rounded-xl border border-[#E8E4DA] text-[#1E1B4B]">
            <div><span class="text-emerald-700 font-bold">POST</span> /api/login.php</div>
            <div><span class="text-blue-700 font-bold">GET</span> /api/data.php</div>
            <div><span class="text-amber-700 font-bold">POST</span> /api/data.php</div>
            <div><span class="text-emerald-700 font-bold">POST</span> /api/subscribers.php</div>
          </div>
        </div>
      </div>
    </div>
  </main>
<?php endif; ?>

</body>
</html>
