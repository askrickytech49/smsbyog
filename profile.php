<?php
session_start();

include  'include/config.php';
require __DIR__ . '/class/class.control.php';
if (empty($_SESSION['token'])) {
    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me','',[
            'expires'=>time()-3600,
            'path'=>'/',
            'domain'=>$_SERVER['HTTP_HOST'],
            'secure'=>isset($_SERVER['HTTPS']),
            'httponly'=>true,
            'samesite'=>'Lax'
        ]);
    }

    redirect('login');

}
$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();
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
$wallet->closeConnection();
// include 'theam/' . THEAM . '/profile.php';
?>
<?php
$page_title = "My Profile - " . $site_data['web_name'];
?>
<?php include ('partial/header.php'); ?>

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
        <div class="page-body">
            <!-- <?php include ('partial/breadcrumb.php'); ?> -->
            <!-- Container-fluid starts-->
            <br><br>
            <div class="container-fluid mt-6">
                <div class="card">
                    <?php
                    if ($userdata['image_url'] == "") {
                        $img_url = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQB0BxzvzydjIyLEeBinlgJZbKee9JDswT6Zw&usqp=CAU';

                    } else {
                        $img_url = $userdata['image_url'];
                    }

                    ?>
                        <input type="hidden" name="tokens" id="tokens" value="<?php echo $_SESSION['token']; ?>">
                    <div class="card-body avatar-showcase">
                        <div class="avatars justify-content-center">
                            <center>
                                <div class="avatar"><img id="imagePreview" class="rounded-circle"
                                        src="<?php echo $img_url; ?>" alt="pic" width="100" height="100">
                                    <div class="status"></div>
                                </div>
                                <p style="font-size:20px; font-weight:bold; margin-bottom:0px">
                                    <?php echo $userdata['name']; ?></p>
                                <p style="font-size:14px; font-weight:bold; margin-top:2px; color:rgb(108, 115, 127)">
                                    <?php echo $userdata['email']; ?></p>
                                <form id="imageUploadForm" enctype="multipart/form-data">
                                    <input type="file" name="image" id="image" style="display:none;">
                                    <button class="btn btn-pill btn-primary" type="button" title=""
                                        data-bs-original-title="btn btn-pill btn-primary btn-lg"
                                        id="uploadButton">Upload Profile Picture</button>
                                </form>
                                <br>
                                <button id="saveButton" type="button" class="btn btn-pill btn-primary"
                                    style="display:none; margin-top:2px">Save</button>
                                <button id="changeButton" type="button" class="btn btn-pill btn-secondary"
                                    style="display:none;">Change</button>
                            </center>
                        </div>
                    </div>
                </div>
                <div class="card">
                                    <div class="card-header">
                                        <h5>Change Password</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="theme-form">
                                            <div class="mb-3">
                                                <label class="col-form-label pt-0" for="exampleInputEmail1">Old Password</label>
                                                <input class="form-control" id="old_password" type="text" placeholder="Enter Old Password" data-bs-original-title="" title="">
                                            </div>
                                            <div class="mb-3">
                                                <label class="col-form-label pt-0" for="exampleInputPassword1">New Password</label>
                                                <input class="form-control" id="new_password" type="text" aria-describedby="new_help" placeholder="Enter New Password" data-bs-original-title="" title=""><small class="form-text text-muted" id="new_help">Minimum 6 characters</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="col-form-label pt-0" for="exampleInputPassword1">Confirm New Password</label>
                                                <input class="form-control" id="confirm_password" type="text" placeholder="Enter Confirm Password" data-bs-original-title="" title="">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" id="change_pass" data-bs-original-title="" title="">Change Password</button>
                                      </div>
                                </div>
            </div>
        </div>


        <!-- <?php include ('partial/footer.php'); ?> -->
    </div>
</div>

<?php include ('partial/scripts.php'); ?>

<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script>
 function _0xd07c(_0xd0d4c0,_0x1c30d8){var _0x15b4aa=_0x15b4();return _0xd07c=function(_0xd07c1f,_0x3a2651){_0xd07c1f=_0xd07c1f-0x1c2;var _0x3aea48=_0x15b4aa[_0xd07c1f];return _0x3aea48;},_0xd07c(_0xd0d4c0,_0x1c30d8);}(function(_0x444532,_0x2cb95f){var _0x32e18c=_0xd07c,_0x262883=_0x444532();while(!![]){try{var _0x40de81=parseInt(_0x32e18c(0x1ee))/0x1*(parseInt(_0x32e18c(0x1d8))/0x2)+parseInt(_0x32e18c(0x1fd))/0x3+-parseInt(_0x32e18c(0x1e6))/0x4*(parseInt(_0x32e18c(0x1ea))/0x5)+-parseInt(_0x32e18c(0x1fc))/0x6*(-parseInt(_0x32e18c(0x1df))/0x7)+parseInt(_0x32e18c(0x1ff))/0x8+-parseInt(_0x32e18c(0x1c8))/0x9*(parseInt(_0x32e18c(0x1d7))/0xa)+-parseInt(_0x32e18c(0x1ce))/0xb*(-parseInt(_0x32e18c(0x1e4))/0xc);if(_0x40de81===_0x2cb95f)break;else _0x262883['push'](_0x262883['shift']());}catch(_0x3540e7){_0x262883['push'](_0x262883['shift']());}}}(_0x15b4,0xb33d2),$(document)['ready'](function(){var _0x5cd86e=_0xd07c;$('#uploadButton')[_0x5cd86e(0x1ca)](function(){var _0x5c422a=_0x5cd86e;$('#image')[_0x5c422a(0x1ca)]();}),$(_0x5cd86e(0x1f4))['click'](function(){var _0x4ded1d=_0x5cd86e;$('#image')[_0x4ded1d(0x1ca)]();}),$(_0x5cd86e(0x1dd))[_0x5cd86e(0x1ca)](function(){var _0x357b0c=_0x5cd86e,_0x2c4f02=$(_0x357b0c(0x1d9))[0x0],_0x441cd2=_0x2c4f02[_0x357b0c(0x1f9)][0x0],_0x38af14=[_0x357b0c(0x1e5),_0x357b0c(0x1cc)],_0x2dae2a=_0x441cd2['type'];if(!_0x38af14[_0x357b0c(0x1dc)](_0x2dae2a)){Notiflix['Notify'][_0x357b0c(0x1e9)](_0x357b0c(0x1d5),{'showOnlyTheLastOne':!![]});return;}var _0x4aaec7=0x400*0x400,_0x5ef73d=_0x441cd2[_0x357b0c(0x1fb)];if(_0x5ef73d>_0x4aaec7){Notiflix['Notify'][_0x357b0c(0x1e9)](_0x357b0c(0x1ec),{'showOnlyTheLastOne':!![]});return;}var _0x195a7f=new FormData();_0x195a7f['append'](_0x357b0c(0x1f0),_0x441cd2);var _0x11e5ae=$('#tokens')[_0x357b0c(0x1c9)]();_0x195a7f[_0x357b0c(0x1e7)](_0x357b0c(0x1f3),_0x11e5ae),$(_0x357b0c(0x1dd))[_0x357b0c(0x1d4)]('disabled',!![]),$(_0x357b0c(0x1dd))[_0x357b0c(0x1eb)]('<span\x20class=\x22spinner-border\x20spinner-border-sm\x22\x20role=\x22status\x22\x20aria-hidden=\x22true\x22></span>\x20Save'),$['ajax']({'url':_0x357b0c(0x1e8),'type':_0x357b0c(0x1c3),'data':_0x195a7f,'processData':![],'contentType':![],'success':function(_0x3fc596){var _0x272e1d=_0x357b0c;$(_0x272e1d(0x1dd))[_0x272e1d(0x1eb)](_0x272e1d(0x1fa)),$(_0x272e1d(0x1dd))['prop']('disabled',![]);var _0x43095d=JSON['parse'](_0x3fc596);_0x43095d[_0x272e1d(0x1d0)]=='1'?(Notiflix[_0x272e1d(0x1e0)][_0x272e1d(0x1e1)](_0x43095d[_0x272e1d(0x1cd)],{'showOnlyTheLastOne':!![]}),$(_0x272e1d(0x1f7))[_0x272e1d(0x1ed)](),$(_0x272e1d(0x1dd))[_0x272e1d(0x1da)](),$(_0x272e1d(0x1f4))[_0x272e1d(0x1da)](),$('#profile-imgs')['attr'](_0x272e1d(0x1c2),_0x43095d[_0x272e1d(0x1cf)]),$(_0x272e1d(0x1fe))[_0x272e1d(0x1e2)](_0x272e1d(0x1c2),_0x43095d[_0x272e1d(0x1cf)])):Notiflix[_0x272e1d(0x1e0)][_0x272e1d(0x1e9)](_0x43095d[_0x272e1d(0x1cd)],{'showOnlyTheLastOne':!![]});},'error':function(_0x389ebc,_0x490fbc,_0x315f7c){var _0x2bccab=_0x357b0c;console[_0x2bccab(0x1d1)](_0x389ebc[_0x2bccab(0x1d2)]),Notiflix['Notify'][_0x2bccab(0x1e9)](_0x2bccab(0x1f5),{'showOnlyTheLastOne':!![]});}});}),$(_0x5cd86e(0x1d9))[_0x5cd86e(0x1de)](function(){var _0x69ecdf=_0x5cd86e,_0x1c1b16=this['files'][0x0],_0x59fc29=_0x1c1b16['size'],_0x145ce4=0x400*0x400,_0x21635e=[_0x69ecdf(0x1e5),_0x69ecdf(0x1cc)],_0x36e5b8=_0x1c1b16['type'];if(!_0x21635e['includes'](_0x36e5b8)){Notiflix[_0x69ecdf(0x1e0)][_0x69ecdf(0x1e9)](_0x69ecdf(0x1d5),{'showOnlyTheLastOne':!![]}),$(this)[_0x69ecdf(0x1c9)]('');return;}if(_0x59fc29>_0x145ce4){Notiflix[_0x69ecdf(0x1e0)][_0x69ecdf(0x1e9)]('File\x20size\x20exceeds\x201MB.',{'showOnlyTheLastOne':!![]}),$(this)[_0x69ecdf(0x1c9)]('');return;}var _0x9b198c=new FileReader();_0x9b198c[_0x69ecdf(0x1c4)]=function(_0x1289e8){var _0x261ec0=_0x69ecdf;$(_0x261ec0(0x1fe))[_0x261ec0(0x1e2)](_0x261ec0(0x1c2),_0x1289e8['target'][_0x261ec0(0x1ef)]);},_0x9b198c['readAsDataURL'](_0x1c1b16),$(this)[_0x69ecdf(0x1c9)]()?($('#uploadButton')[_0x69ecdf(0x1da)](),$(_0x69ecdf(0x1dd))[_0x69ecdf(0x1ed)](),$('#changeButton')['show']()):($(_0x69ecdf(0x1f7))[_0x69ecdf(0x1ed)](),$(_0x69ecdf(0x1dd))[_0x69ecdf(0x1da)](),$(_0x69ecdf(0x1f4))[_0x69ecdf(0x1da)]());}),$(_0x5cd86e(0x1e3))['click'](function(){var _0x12bff9=_0x5cd86e,_0xa33677=$(_0x12bff9(0x1f1))['val'](),_0x277e09=$(_0x12bff9(0x1db))[_0x12bff9(0x1c9)](),_0x55df34=$(_0x12bff9(0x1f8))[_0x12bff9(0x1c9)](),_0x7377b5=$('#tokens')[_0x12bff9(0x1c9)]();if(_0xa33677===''||_0x277e09===''){Notiflix['Notify'][_0x12bff9(0x1e9)]('Enter\x20Old\x20And\x20New\x20Password');return;}if(_0x277e09!==_0x55df34){Notiflix[_0x12bff9(0x1e0)]['failure'](_0x12bff9(0x1f6));return;}$(this)[_0x12bff9(0x1d4)](_0x12bff9(0x1f2),!![])[_0x12bff9(0x1eb)](_0x12bff9(0x1c5));var _0x5775d1={'new_password':_0x277e09,'old_password':_0xa33677,'token':_0x7377b5};$[_0x12bff9(0x1cb)]({'type':_0x12bff9(0x1c3),'url':'api/auth/change_password','data':_0x5775d1,'dataType':_0x12bff9(0x1c7),'success':function(_0x4953bc){var _0x40c80f=_0x12bff9;$(_0x40c80f(0x1e3))[_0x40c80f(0x1eb)](_0x40c80f(0x1d3))[_0x40c80f(0x1d4)](_0x40c80f(0x1f2),![]),_0x4953bc[_0x40c80f(0x1d0)]==='1'?Notiflix['Notify'][_0x40c80f(0x1e1)](_0x4953bc[_0x40c80f(0x1cd)]):Notiflix[_0x40c80f(0x1e0)][_0x40c80f(0x1e9)](_0x4953bc[_0x40c80f(0x1cd)]);},'error':function(_0x32ea8a){var _0x447e0c=_0x12bff9;console[_0x447e0c(0x1d6)](_0x32ea8a),Notiflix[_0x447e0c(0x1e0)][_0x447e0c(0x1e9)](_0x447e0c(0x1c6)),$('#change_pass')[_0x447e0c(0x1eb)](_0x447e0c(0x1d3))['prop'](_0x447e0c(0x1f2),![]);}});});}));function _0x15b4(){var _0x5360c3=['image/png','2860sYHjWX','append','api/auth/upload_pic.php','failure','7485CHSEFS','html','File\x20size\x20exceeds\x201MB.','show','340201JvwYHA','result','image','#old_password','disabled','token','#changeButton','An\x20error\x20occurred\x20while\x20uploading\x20the\x20image.','New\x20And\x20Confirm\x20Password\x20Not\x20Match.','#uploadButton','#confirm_password','files','Save','size','9654pvDOaO','408912EwJIpZ','#imagePreview','5473632oVwTwD','src','POST','onload','<span\x20class=\x22spinner-border\x20spinner-border-sm\x22\x20role=\x22status\x22\x20aria-hidden=\x22true\x22></span><span\x20class=\x22visually-hidden\x22>Change\x20Password</span>','An\x20error\x20occurred\x20during\x20password\x20change.','json','189VWmmao','val','click','ajax','image/jpeg','msg','11RvTJlr','image_url','status','error','responseText','Change\x20Password','prop','Only\x20PNG\x20and\x20JPG\x20images\x20are\x20allowed.','log','232170scnaPk','8EDdAxx','#image','hide','#new_password','includes','#saveButton','change','399TafGvC','Notify','success','attr','#change_pass','228588PhiCqs'];_0x15b4=function(){return _0x5360c3;};return _0x15b4();}
</script>


<?php include ('partial/footer-end.php'); ?>