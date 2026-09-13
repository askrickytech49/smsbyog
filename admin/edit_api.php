<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$id=(int)($_GET['id']??0);
$api=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM api_detail WHERE id='$id'"));
if(!$api){ header('Location: show_api'); exit; }

$msg=''; $msg_type='';
if(isset($_POST['update'])){
    $name=mysqli_real_escape_string($conn,trim($_POST['api_name']??''));
    $url =mysqli_real_escape_string($conn,trim($_POST['api_url']??''));
    $key =mysqli_real_escape_string($conn,trim($_POST['api_key']??''));
    $rate=(float)($_POST['rate']??1500);
    $profit=(float)($_POST['profit_amount']??0);
    mysqli_query($conn,"UPDATE api_detail SET api_name='$name',api_url='$url',api_key='$key',rate='$rate',profit_amount='$profit' WHERE id='$id'");
    $api=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM api_detail WHERE id='$id'"));
    $msg='API updated.'; $msg_type='success';
}

// ==========================================
// API SERVICES TOGGLE LOGIC
// ==========================================
$api_id = $id;
$api_data = $api;

$dbServices = [];
$dbResult = mysqli_query($conn, "SELECT service_id, service_name FROM service");
if ($dbResult) {
    while ($row = mysqli_fetch_assoc($dbResult)) {
        $dbServices[strtolower($row['service_id'])] = trim($row['service_name']);
    }
}

$activeServices = [];
$activeOrders = [];
$activeRes = mysqli_query($conn, "SELECT service_code FROM api_active_services WHERE api_id='$api_id'");
while ($row = mysqli_fetch_assoc($activeRes)) {
    $activeServices[$row['service_code']] = true;
}

$orderRes = mysqli_query($conn, "SELECT service_code, sort_order FROM api_service_order WHERE api_id='$api_id'");
while ($row = mysqli_fetch_assoc($orderRes)) {
    $activeOrders[$row['service_code']] = (int)$row['sort_order'];
}
$availableCodes = [];
$apiServiceNames = []; // code => name from the API itself

if ($api_id == 8 || $api_id == 2) {
    include_once '../include/api_cache.php';
    $cache_key = ($api_id == 8) ? 'tigersms_getPrices' : '5sim_guest_prices';
    $allPrices = api_cache_get($cache_key, 120);
    if (!$allPrices) {
        if ($api_id == 8) {
            $url = "{$api_data['api_url']}/stubs/handler_api.php?api_key={$api_data['api_key']}&action=getPrices";
        } else {
            $url = "{$api_data['api_url']}/v1/guest/prices";
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw = curl_exec($ch);
        curl_close($ch);
        $allPrices = json_decode($raw, true);
    }
    if ($allPrices && is_array($allPrices)) {
        if ($api_id == 8) {
            foreach ($allPrices as $country => $services) {
                if (is_array($services)) {
                    foreach ($services as $code => $details) {
                        $availableCodes[$code] = true;
                    }
                }
            }
        } elseif ($api_id == 2) {
            foreach ($allPrices as $country => $products) {
                if (is_array($products)) {
                    foreach ($products as $productCode => $operators) {
                        if (is_array($operators)) {
                            $availableCodes[$productCode] = true;
                        }
                    }
                }
            }
        }
    }
} elseif ($api_id == 1) {
    // VerifySMS — fetch live services list
    $url = $api_data['api_url'] . '/api/services?api_key=' . $api_data['api_key'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    curl_close($ch);
    $services = json_decode($raw, true);
    if (is_array($services)) {
        foreach ($services as $code => $details) {
            $availableCodes[$code] = true;
            $apiServiceNames[$code] = $details['name'] ?? ucfirst($code);
        }
    }
} elseif ($api_id == 3) {
    // DinoSMS — fetch live services list
    $dino_url = rtrim($api_data['api_url'], '/') . '/sms-otp/services';
    $dino_data = getfunction($dino_url, $api_data['api_key']);
    if (is_array($dino_data)) {
        foreach ($dino_data as $details) {
            $code = $details['service_code'] ?? '';
            if ($code) {
                $availableCodes[$code] = true;
                $apiServiceNames[$code] = $details['service_name'] ?? ucfirst($code);
            }
        }
    }
}

if (empty($availableCodes)) {
    foreach ($dbServices as $code => $name) {
        $availableCodes[$code] = true;
    }
}
$displayList = [];
foreach ($availableCodes as $code => $val) {
    // Prefer API-provided name, then DB name, then fallback to ucfirst(code)
    $name = $apiServiceNames[$code] ?? $dbServices[strtolower($code)] ?? ucfirst($code);
    $displayList[] = [
        'code' => $code,
        'name' => strip_tags($name),
        'is_active' => isset($activeServices[$code]),
        'sort_order' => $activeOrders[$code] ?? 9999
    ];
}

// AUTO-ACTIVATE: If NO services are active for this API, activate ALL on first visit
if (empty($activeServices) && !empty($availableCodes)) {
    $values = [];
    foreach ($availableCodes as $code => $val) {
        $esc_code = mysqli_real_escape_string($conn, $code);
        $values[] = "('$api_id', '$esc_code')";
    }
    if (!empty($values)) {
        $batch = implode(',', $values);
        mysqli_query($conn, "INSERT IGNORE INTO api_active_services (api_id, service_code) VALUES $batch");
        // Mark all as active in displayList
        foreach ($displayList as &$item) {
            $item['is_active'] = true;
        }
        unset($item);
    }
}

usort($displayList, function($a, $b) {
    if ($a['sort_order'] !== $b['sort_order']) {
        return $a['sort_order'] <=> $b['sort_order'];
    }
    return strcasecmp($a['name'], $b['name']);
});
// ==========================================

$page_title='Edit API';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="show_api">API Providers</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
  </div>
  <a href="show_api" class="btn btn-light-action"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-12 col-md-5 mb-4">
    <?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header"><h6>Edit API Details</h6></div>
      <div class="admin-card-body">
        <form method="post">
          <div class="mb-3"><label class="form-label">API Name</label><input type="text" name="api_name" class="form-control" value="<?=htmlspecialchars($api['api_name'])?>" required></div>
          <div class="mb-3"><label class="form-label">API URL</label><input type="url" name="api_url" class="form-control" value="<?=htmlspecialchars($api['api_url'])?>" required></div>
          <div class="mb-3"><label class="form-label">API Key</label><textarea name="api_key" class="form-control" rows="3" required><?=htmlspecialchars($api['api_key'])?></textarea></div>
          <div class="row g-3 mb-4">
            <div class="col-6"><label class="form-label">Rate (₦ per $1)</label><input type="number" name="rate" class="form-control" value="<?=htmlspecialchars($api['rate']??1500)?>" step="0.01"></div>
            <div class="col-6"><label class="form-label">Profit (₦)</label><input type="number" name="profit_amount" class="form-control" value="<?=htmlspecialchars($api['profit_amount']??0)?>" step="0.01"></div>
          </div>
          <button type="submit" name="update" class="btn btn-primary w-100"><i class="bi bi-floppy me-2"></i>Save Changes</button>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-12 col-md-7">
    <div class="admin-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <h6>Manage Services</h6>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search services..." style="max-width: 200px;">
        </div>
        <div class="admin-card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="servicesTable">
                    <thead class="bg-light position-sticky top-0" style="z-index: 1;">
                        <tr>
                            <th class="ps-3" style="width: 40px;"></th> <!-- Drag Handle -->
                            <th class="ps-2" style="width: 50px;">Icon</th>
                            <th>Service Name</th>
                            <th>API Code</th>
                            <th class="text-end pe-4">Display</th>
                        </tr>
                    </thead>
                    <tbody id="sortableServicesList">
                        <?php 
                        include_once '../include/service_icons.php';
                        foreach($displayList as $svc): 
                            $currentIcon = getServiceIcon($svc['name'], $svc['code']);
                        ?>
                        <tr class="service-row" data-code="<?=htmlspecialchars($svc['code'])?>">
                            <td class="ps-3 text-muted drag-handle" style="cursor: grab;">
                                <i class="bi bi-grip-vertical"></i>
                            </td>
                            <td class="ps-2">
                                <div class="position-relative icon-container" style="width: 32px; height: 32px;">
                                    <img src="<?=htmlspecialchars($currentIcon)?>" class="rounded icon-preview" style="width: 32px; height: 32px; object-fit: contain; cursor: pointer; background: #f8f9fa; border: 1px dashed #dee2e6;" title="Click to change icon" data-code="<?=htmlspecialchars($svc['code'])?>" id="icon-preview-<?=htmlspecialchars($svc['code'])?>" onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23cbd5e1\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z\'/%3E%3C/svg%3E';">
                                    <div class="edit-overlay" style="position:absolute; top:0; left:0; width:32px; height:32px; background:rgba(0,0,0,0.5); border-radius:4px; display:flex; align-items:center; justify-content:center; pointer-events:none; opacity:0; transition:0.2s;">
                                        <i class="bi bi-pencil-fill text-white" style="font-size:12px;"></i>
                                    </div>
                                    <input type="file" class="d-none icon-upload-input" accept="image/png,image/jpeg,image/svg+xml,image/webp" data-code="<?=htmlspecialchars($svc['code'])?>" id="icon-input-<?=htmlspecialchars($svc['code'])?>">
                                </div>
                            </td>
                            <td class="fw-medium service-name"><?=htmlspecialchars($svc['name'])?></td>
                            <td><span class="badge bg-secondary"><?=htmlspecialchars($svc['code'])?></span></td>
                            <td class="text-end pe-4">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input service-toggle" type="checkbox" role="switch"
                                           data-code="<?=htmlspecialchars($svc['code'])?>"
                                           <?=$svc['is_active'] ? 'checked' : ''?>
                                           style="width:40px;height:22px;cursor:pointer;<?=$svc['is_active']?'background-color:#e10700!important;border-color:#e10700!important;':''?>">
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($displayList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No services found for this API.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</div>

<!-- Custom Icon Modal -->
<div class="modal fade" id="iconModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title">Change Icon</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
            <img id="modalIconPreview" src="" class="rounded bg-light p-2 mb-2" style="width: 64px; height: 64px; object-fit: contain;">
            <div class="fw-medium text-muted small" id="modalServiceCode"></div>
        </div>
        
        <input type="hidden" id="modalTargetCode">
        
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary btn-sm" id="btnUploadIcon">
                <i class="bi bi-upload me-1"></i> Upload Image
            </button>
            <div class="position-relative d-flex align-items-center my-1">
                <hr class="flex-grow-1 m-0">
                <span class="px-2 text-muted small">OR</span>
                <hr class="flex-grow-1 m-0">
            </div>
            <div class="input-group input-group-sm">
                <input type="url" class="form-control" id="iconUrlInput" placeholder="Image URL...">
                <button class="btn btn-outline-primary" type="button" id="btnSaveUrlIcon">Save</button>
            </div>
            
            <button type="button" class="btn btn-outline-danger btn-sm mt-2" id="btnRemoveIcon">
                <i class="bi bi-trash me-1"></i> Revert to Default
            </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/include/layout_end.php'; ?>
<script>
// Search filter
document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.service-row');
    rows.forEach(row => {
        const name = row.querySelector('.service-name').textContent.toLowerCase();
        if (name.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Toggle handler
document.querySelectorAll('.service-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const code = this.dataset.code;
        const isActive = this.checked ? 1 : 0;
        const apiId = <?=$api_id?>;
        
        // Update styling instantly
        if (isActive) {
            this.style.backgroundColor = '#e10700';
            this.style.borderColor = '#e10700';
        } else {
            this.style.backgroundColor = '';
            this.style.borderColor = '';
        }
        this.disabled = true;

        fetch('ajax/toggle_api_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `api_id=${apiId}&service_code=${encodeURIComponent(code)}&is_active=${isActive}`
        })
        .then(r => r.json())
        .then(data => {
            this.disabled = false;
            if (!data.success) {
                alert('Error updating service: ' + data.message);
                // revert visually
                this.checked = !isActive;
            }
        })
        .catch(e => {
            this.disabled = false;
            this.checked = !isActive;
            alert('Network error.');
        });
    });
});

// Bulk Select All / Deselect All
document.getElementById('btnSelectAll').addEventListener('click', function() {
    if (!confirm('Activate ALL services for this API?')) return;
    const apiId = <?=$api_id?>;
    const codes = [];
    document.querySelectorAll('.service-toggle').forEach(t => codes.push(t.dataset.code));
    
    const formData = new URLSearchParams();
    formData.append('api_id', apiId);
    formData.append('action', 'select_all');
    formData.append('codes', JSON.stringify(codes));
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Working...';
    
    fetch('ajax/bulk_toggle_services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.service-toggle').forEach(t => {
                t.checked = true;
                t.style.backgroundColor = '#e10700';
                t.style.borderColor = '#e10700';
            });
        } else {
            alert('Error: ' + data.message);
        }
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-check-all me-1"></i>All ON';
    })
    .catch(() => {
        alert('Network error');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-check-all me-1"></i>All ON';
    });
});

document.getElementById('btnDeselectAll').addEventListener('click', function() {
    if (!confirm('Deactivate ALL services for this API? Nothing will show on user side.')) return;
    const apiId = <?=$api_id?>;
    
    const formData = new URLSearchParams();
    formData.append('api_id', apiId);
    formData.append('action', 'deselect_all');
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Working...';
    
    fetch('ajax/bulk_toggle_services.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.service-toggle').forEach(t => {
                t.checked = false;
                t.style.backgroundColor = '';
                t.style.borderColor = '';
            });
        } else {
            alert('Error: ' + data.message);
        }
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-x-lg me-1"></i>All OFF';
    })
    .catch(() => {
        alert('Network error');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-x-lg me-1"></i>All OFF';
    });
});

// Custom Icon Management
let iconModal;
if (typeof bootstrap !== 'undefined') {
    iconModal = new bootstrap.Modal(document.getElementById('iconModal'));
}

document.querySelectorAll('.icon-preview').forEach(img => {
    img.addEventListener('click', function() {
        const code = this.dataset.code;
        const currentSrc = this.src;
        
        document.getElementById('modalTargetCode').value = code;
        document.getElementById('modalIconPreview').src = currentSrc;
        document.getElementById('modalServiceCode').textContent = 'Code: ' + code;
        document.getElementById('iconUrlInput').value = '';
        
        if (iconModal) iconModal.show();
    });
});

// File upload button
document.getElementById('btnUploadIcon').addEventListener('click', () => {
    const code = document.getElementById('modalTargetCode').value;
    document.getElementById('icon-input-' + code).click();
});

// Handle file selection
document.querySelectorAll('.icon-upload-input').forEach(input => {
    input.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        
        const code = this.dataset.code;
        const formData = new FormData();
        formData.append('service_code', code);
        formData.append('icon_file', this.files[0]);
        formData.append('action', 'upload');
        
        uploadIcon(formData, code);
    });
});

// Handle URL save
document.getElementById('btnSaveUrlIcon').addEventListener('click', () => {
    const code = document.getElementById('modalTargetCode').value;
    const url = document.getElementById('iconUrlInput').value.trim();
    if (!url) return;
    
    const formData = new FormData();
    formData.append('service_code', code);
    formData.append('icon_url', url);
    formData.append('action', 'url');
    
    uploadIcon(formData, code);
});

// Handle Revert/Remove
document.getElementById('btnRemoveIcon').addEventListener('click', () => {
    if (!confirm('Revert this service icon back to default?')) return;
    
    const code = document.getElementById('modalTargetCode').value;
    const formData = new FormData();
    formData.append('service_code', code);
    formData.append('action', 'remove');
    
    fetch('ajax/update_service_icon.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to let PHP generate the default fallback URL
        } else {
            alert(data.message || 'Failed to remove custom icon');
        }
    })
    .catch(() => alert('Network error'));
});

function uploadIcon(formData, code) {
    const btnUpload = document.getElementById('btnUploadIcon');
    const originalText = btnUpload.innerHTML;
    btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    btnUpload.disabled = true;

    fetch('ajax/update_service_icon.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        btnUpload.innerHTML = originalText;
        btnUpload.disabled = false;
        
        if (data.success) {
            // Update preview
            document.getElementById('icon-preview-' + code).src = data.icon_url;
            if (iconModal) iconModal.hide();
        } else {
            alert(data.message || 'Failed to update icon');
        }
    })
    .catch(() => {
        btnUpload.innerHTML = originalText;
        btnUpload.disabled = false;
        alert('Network error');
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('sortableServicesList');
    if (el) {
        Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                // Reorder occurred
                const rows = el.querySelectorAll('.service-row');
                const orderData = [];
                rows.forEach((row, index) => {
                    const code = row.dataset.code;
                    orderData.push({ code: code, order: index });
                });

                // Update server
                const apiId = <?=$api_id?>;
                fetch('ajax/update_service_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        api_id: apiId,
                        orders: orderData
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating order: ' + data.message);
                    }
                })
                .catch(e => {
                    console.error('Network error during reorder', e);
                });
            }
        });
    }
});
</script>


<style> .icon-container:hover .edit-overlay { opacity: 1 !important; } </style>


