<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
require __DIR__ . '/class/class.control.php';
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
		redirect('login');
	}
}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();
if($userdata===false){
	unset($_SESSION['token']);
	session_destroy();
	if(isset($_COOKIE['remember_me'])) {
		unset($_COOKIE['remember_me']);
		setcookie('remember_me', $token, [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => $_SERVER['HTTP_HOST'],
			'secure' => true,
			'httponly' => true,
			'samesite' => 'radium'
		]);
		
	}
		redirect('login');	
}
$userwallet = $wallet->userwallet();
$api_data = $wallet->api_data();


$wallet->closeConnection();
// include 'theam/' . THEAM . '/api-tool.php';
?>
<?php
$page_title = "Api Tool - " . $site_data['web_name'];
?>
<?php include ('partial/header.php'); ?>
<link rel="stylesheet" type="text/css"
  href="assets/css/vendors/icofont.css">
<style>
.api-tool-page {
  background: #f5f7fa;
  min-height: calc(100vh - 72px);
  padding: 28px 24px 56px;
  overflow-x: hidden;
}

.api-tool-page .faq-wrap,
.api-tool-page .faq-wrap > .row {
  max-width: 980px;
  margin: 0 auto;
}

.api-tool-page .card {
  border: 1px solid #e6eaf0 !important;
  border-radius: 16px !important;
  box-shadow: 0 10px 28px rgba(15, 23, 42, .06) !important;
  overflow: hidden;
}

.api-tool-page .clipboaard-container {
  padding: 8px;
}

.api-tool-page .clipboaard-container .card-description {
  color: #172033;
  font-size: 16px !important;
  margin-bottom: 12px;
}

.api-tool-page #api_value {
  min-height: 46px;
  border: 1px solid #dce2ea !important;
  border-radius: 9px;
  background: #f8fafc !important;
  color: #172033;
  font-size: 14px;
}

.api-tool-page #api_value:focus {
  border-color: #e10700 !important;
  box-shadow: 0 0 0 3px rgba(225, 7, 0, .1) !important;
}

.api-tool-page .btn-clipboard,
.api-tool-page .btn-clipboard-cut {
  border: 1px solid #e10700 !important;
  border-radius: 8px !important;
  background: #e10700 !important;
  background-image: none !important;
  color: #fff !important;
  font-weight: 700;
  padding: 9px 18px;
}

.api-tool-page .btn-clipboard:hover,
.api-tool-page .btn-clipboard-cut:hover {
  background: #bd0600 !important;
  border-color: #bd0600 !important;
}

.api-tool-page .header-faq {
  margin: 28px 0 14px;
}

.api-tool-page .header-faq h5 {
  color: #172033;
  font-size: 21px;
  font-weight: 750;
}

.api-tool-page .faq-accordion > .col > .card {
  margin-bottom: 10px;
  box-shadow: 0 4px 14px rgba(15, 23, 42, .04) !important;
}

.api-tool-page .faq-accordion .card-header {
  padding: 0 !important;
  border-bottom: 0 !important;
  background: #fff !important;
}

.api-tool-page .faq-accordion .card-header button {
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 56px;
  padding: 14px 18px !important;
  color: #172033 !important;
  font-size: 14px;
  font-weight: 650;
  text-decoration: none;
}

.api-tool-page .faq-accordion .card-header button:hover,
.api-tool-page .faq-accordion .card-header button:focus {
  color: #e10700 !important;
  background: #fff8f5;
}

.api-tool-page .faq-accordion .card-header button svg {
  width: 18px;
  margin-right: 10px;
  color: #e10700;
}

.api-tool-page .faq-accordion .collapse > div {
  padding: 18px !important;
  max-width: 100%;
  min-width: 0;
  overflow: hidden;
}

.api-tool-page .faq-accordion pre {
  display: block;
  width: 100%;
  max-width: 100%;
  min-width: 0;
  margin: 0;
  padding: 14px !important;
  border-radius: 8px !important;
  background: #172033 !important;
  color: #ffd9d6 !important;
  font-size: 12px !important;
  line-height: 1.6;
  white-space: pre-wrap;
  overflow-x: hidden !important;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.api-tool-page .faq-accordion .collapse p {
  margin-left: 0 !important;
  color: #334155 !important;
  font-size: 13px !important;
  line-height: 1.6;
}

.api-tool-page .faq-accordion .collapse p[style*="color:blue"],
.api-tool-page .faq-accordion .collapse p[style*="color:red"] {
  color: #e10700 !important;
}

@media (max-width: 575px) {
  .api-tool-page { padding: 18px 12px 36px; }
  .api-tool-page .faq-wrap,
  .api-tool-page .faq-wrap > .row,
  .api-tool-page .faq-accordion,
  .api-tool-page .faq-accordion > .col,
  .api-tool-page .faq-accordion > .col > .card {
    width: 100%;
    max-width: 100%;
    min-width: 0;
  }
  .api-tool-page .clipboaard-container { padding: 2px; }
  .api-tool-page .clipboaard-container .text-end { display: flex; gap: 8px; }
  .api-tool-page .clipboaard-container .text-end button { flex: 1; padding: 9px 8px; }
  .api-tool-page .header-faq h5 { font-size: 18px; }
  .api-tool-page .faq-accordion .card-header button { min-height: 52px; padding: 12px 14px !important; }
}
</style>
<?php include ('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <!-- Page Header Start-->
  <?php include ('partial/topbar.php'); ?>
  <!-- Page Header Ends -->
  <!-- Page Body Start-->
  <div class="page-body-wrapper">
    <!-- Page Sidebar Start-->
    <?php include ('partial/sidebar.php'); ?>
    <!-- Page Sidebar Ends-->
    <div class="page-body api-tool-page">
      <!-- <?php include ('partial/breadcrumb.php'); ?> -->
      <!-- Container-fluid starts-->
      <br><br>
      <div class="container-fluid">
        <div class="faq-wrap">
          <div class="row">
            <div class="card">
              <!-- <div class="card-header">
               </div> -->
              <div class="card-body">
                <div class="clipboaard-container">
                  <p class="card-description" style="font-size:15px;font-weight:bold">Your Api Key</p>
                  <input type="hidden" name="tokens" id="tokens" value="<?php echo $_SESSION['token']; ?>">

                  <input class="form-control" id="api_value" type="text" value="<?php echo $api_data['api_key']; ?>"
                    data-bs-original-title="" title="" readonly>
                  <div class="mt-3 text-end">
                    <button class="btn btn-primary btn-clipboard" type="button" onclick="copy()"><i class="fa fa-copy"></i>
                      Copy</button>
                    <button class="btn btn-secondary btn-clipboard-cut" id="show_api" onclick="new_api()"><i
                        class="fa fa-cut"></i> Change Key</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="header-faq">
                <h5 class="mb-0">Api Documents</h5>
              </div>
              <div class="row default-according style-1 faq-accordion" id="accordionoc">
                <div class="col ">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#api_balance_open" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>Balance Check</button>
                      </h5>
                    </div>
                    <div class="collapse" id="api_balance_open" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?api_key=$api_key&amp;action=getBalance</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                   
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#api_buy_number" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>Purchase Number</button>
                      </h5>
                    </div>
                    <div class="collapse" id="api_buy_number" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?action=getNumber&api_key=$api_key&service=$service&country=$country</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$service<span class="text-black">- It is a Unique Service Id for every service. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$country<span class="text-black">- It is a Server id . (Required)</span></p>

                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">NO_NUMBERS<span class="text-black">- No numbers, try again later</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">NO_BALANCE<span class="text-black">- No Enough balance</span></p>
                   
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#api_active_number" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>Activation Status</button>
                      </h5>
                    </div>
                    <div class="collapse" id="api_active_number" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?action=getStatus&api_key=$api_key&id=$id</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$id<span class="text-black">- Activation id. (Required)</span></p>
                  
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">NO_ACTIVATION<span class="text-black">- There Is No Activation</span></p>
 
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#change_status" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>Change Activation Status</button>
                      </h5>
                    </div>
                    <div class="collapse" id="change_status" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?action=setStatus&api_key=$api_key&id=$id&status=$status</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$id<span class="text-black">- Activation id. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$status<span class="text-black">- (8 - cancel activation, 3 - Request another SMS). (Required)</span></p>
                  
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">NO_ACTIVATION<span class="text-black">- There Is No Activation</span></p>
 
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#api_country" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>List Of All Countries/Servers</button>
                      </h5>
                    </div>
                    <div class="collapse" id="api_country" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?action=getCountries&api_key=$api_key</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                    
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">
                        <button class="btn btn-link collapsed ps-0" data-bs-toggle="collapse"
                          data-bs-target="#api_server" aria-expanded="false" aria-controls="collapseicon"><i
                            data-feather="code"></i>List Of All Services</button>
                      </h5>
                    </div>
                    <div class="collapse" id="api_server" aria-labelledby="collapseicon"
                      data-bs-parent="#accordionoc">
                      <div class="p-3  font-semibold ">
                        <div class="pl-5 "
                          style="background:#eaf1ff; padding-left:5px; border-top-left-radius:8px; border-top-right-radius:8px; color:red; fonot-weight:bold">
                          Request Method -&gt; Get</div>
                        <pre class="code overflow-auto text-blue  pt-2 pb-2"
                          style="border-bottom-left-radius:8px; border-bottom-right-radius:8px;  background:#191e3a;  color:#805dca; font-size:15px; fonot-weight:bold;"> <?php echo $website_url; ?>/stubs/handler_api.php?action=getServices&api_key=$api_key&country=$country</pre>
                      </div>
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Parameters:</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$api_key<span class="text-black">- Your API key. (Required)</span></p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:blue">$country<span class="text-black">- Server id . (Required)</span></p>
                    
                      <p style="margin-left:19px; font-size:15px; font-weight:bold">Possible errors::</p>
                      <p style="margin-left:19px; font-size:14px; font-weight:bold; color:red">BAD_KEY<span class="text-black">- Incorrect API key</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>



      <!-- Container-fluid Ends-->
    </div>

    <?php include ('partial/footer.php'); ?>
  </div>
</div>

<?php include ('partial/scripts.php'); ?>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>


<script>

  function new_api() {
    var token = $("#tokens").val();


    $('#show_api').prop("disabled", true);
    $('#show_api').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>  Change Key');

    var params = {
      token: token,
    };
    $.ajax({
      type: "POST",
      url: "api/auth/generateApi",
      data: params,
      error: function (e) {
        console.log(e);
        Notiflix.Notify.failure('An error occurred during Connection.');
        $('#show_api').html("<i class='fa fa-cut'></i>  Change Key");
        $('#show_api').prop("disabled", false);
      },
      success: function (data) {
        $('#show_api').html("<i class='fa fa-cut'></i>  Change Key");
        $('#show_api').prop("disabled", false);
        var json = JSON.parse(data);
        if (json.status === "200") {
          document.getElementById("api_value").value = json.api_key;
          Notiflix.Notify.success(json.msg);
        } else {
          Notiflix.Notify.failure(json.msg);

        }
      }
    });
  } 
  function copy() {
    const el = document.createElement('textarea');
    el.value = document.getElementById('api_value').value;

    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';

    document.body.appendChild(el);

    el.select();

    document.execCommand('copy');

    document.body.removeChild(el);
    Notiflix.Notify.success("Copied: " + el.value);
}

  
</script>
<?php include ('partial/footer-end.php'); ?>