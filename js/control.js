import { toast } from 'https://esm.sh/wc-toast';

function validateEmail(email) {
  const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  return emailRegex.test(email);
}

$(document).ready(function () {

  /* ===================== LOGIN ===================== */
  $("#login").click(function () {
    var email = $("#email").val();
    var password = $("#password").val();

    if (email === '' || password === '') {
      toast.error('Enter Email & Password.');
      return;
    }

    if (!validateEmail(email)) {
      toast.error('Enter Valid Email');
      return;
    }

    $('#login')
      .prop("disabled", true)
      .html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 inline-block align-middle"></span> Sign in...');

    $.ajax({
      type: "POST",
      url: "api/auth/login",
      data: {
        email: email,
        password: password
      },
      dataType: "json", // âœ… IMPORTANT
      error: function (e) {
        console.error(e);
        toast.error('An error occurred during login.');
        $('#login').html("Sign In").prop("disabled", false);
      },
      success: function (json) {
        $('#login').html("Sign In").prop("disabled", false);

        if (json.status === "1") {
          toast.success(json.msg);
          setTimeout(function () {
            window.location.href = 'dashboard';
          }, 1000);
        } else {
          toast.error(json.msg);
        }
      }
    });
  });

  /* ===================== FORGOT PASSWORD ===================== */
  $("#forgot").click(function () {
    var email = $("#email").val();

    if (email === '') {
      toast.error('Enter Email');
      return;
    }

    if (!validateEmail(email)) {
      toast.error('Enter Valid Email');
      return;
    }

    $('#forgot')
      .prop("disabled", true)
      .html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 inline-block align-middle"></span> Sending...');

    $.ajax({
      type: "POST",
      url: "api/auth/forgot",
      data: { email: email },
      dataType: "json", // âœ… IMPORTANT
      success: function (json) {
        $('#forgot').html("Send Recovery Email").prop("disabled", false);

        if (json.status === "1") {
          toast.success(json.msg);
        } else {
          toast.error(json.msg);
        }
      },
      error: function (e) {
        console.error(e);
        toast.error('An error occurred.');
        $('#forgot').html("Send Recovery Email").prop("disabled", false);
      }
    });
  });

  /* ===================== RESET PASSWORD ===================== */
  $("#change_pass").click(function () {
    var new_password = $("#new_password").val();
    var confirm_password = $("#confirm_password").val();
    var token = $("#tokens").val();

    if (new_password === '' || confirm_password === '') {
      toast.error('Please enter new password and confirm it.');
      return;
    }

    $('#change_pass')
      .prop("disabled", true)
      .html('<span class="spinner-border spinner-border-sm"></span> Loading...');

    $.ajax({
      type: "POST",
      url: "api/auth/new_password",
      data: {
        new_password: new_password,
        confirm_password: confirm_password,
        token: token
      },
      dataType: "json",
      success: function (json) {
        $('#change_pass').html("Set New Password").prop("disabled", false);

        if (json.status === "1") {
          toast.success(json.msg);
          setTimeout(function () {
            window.location.href = 'password_changed';
          }, 1000);
        } else {
          toast.error(json.msg);
        }
      },
      error: function (e) {
        console.error(e);
        toast.error('Error updating password.');
        $('#change_pass').html("Set New Password").prop("disabled", false);
      }
    });
  });

  /* ===================== REGISTER ===================== */
  $("#register").click(function () {
    var first_name = $("#first_name").val();
    var last_name = $("#last_name").val();
    var email = $("#email").val();
    var password = $("#password").val();
    var refer_id = $("#refer_id").val();
    var name = first_name + " " + last_name;

    if (name === '' || email === '' || password === '') {
      toast.error('Please fill all details.');
      return;
    }

    if (!validateEmail(email)) {
      toast.error('Please enter a valid email.');
      return;
    }

    var recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      toast.error("Please complete the captcha.");
      return;
    }

    $('#register')
      .prop("disabled", true)
      .html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 inline-block align-middle"></span> Signing up...');

    $.ajax({
      type: "POST",
      url: "api/auth/register",
      data: {
        name: name,
        email: email,
        password: password,
        refer_id: refer_id,
        "g-recaptcha-response": recaptchaResponse
      },
      dataType: "json",
      success: function (json) {
        $('#register').html("Create Account").prop("disabled", false);
        grecaptcha.reset();

        if (json.status === "1") {
          toast.success(json.msg);
          setTimeout(function () {
            window.location.href = 'account_created';
          }, 1000);
        } else {
          toast.error(json.msg);
        }
      },
      error: function (e) {
        console.error(e);
        toast.error('Registration error.');
        $('#register').html("Create Account").prop("disabled", false);
      }
    });
  });

});