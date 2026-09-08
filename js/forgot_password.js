$(document).ready(function(){
  $("#forgot").click(function(){
    let email = $("#email").val().trim();

    if(email === ""){
      alert("Please enter your email");
      return;
    }

    $.ajax({
      url: "forgot_password.php",
      type: "POST",
      data: { email: email },
      success: function(response){
        try {
          let res = JSON.parse(response);
          alert(res.message);

          // Fire Meta Pixel event ONLY when reset email was sent successfully
          if (res.status === "success" && typeof fbq === "function") {
            fbq('trackCustom', 'PasswordResetRequest');
            // If you prefer using a standard event instead:
            // fbq('track', 'Lead');
          }

        } catch (e) {
          console.error("Invalid JSON:", response);
          alert("Something went wrong. Please try again.");
        }
      },
      error: function(xhr, status, error){
        console.error("AJAX Error:", error);
        alert("Server error: " + xhr.status + " â€” please try again.");
      }
    });
  });
});