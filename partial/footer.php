<style>
/* ===============================
   AUTHPADI – RESPONSIVE FOOTER
================================ */

.authpadi-footer{
  background:#ffffff;
  border-top:2px solid #6f2dbd;
  padding:24px 14px;
}

/* WRAPPER */
.authpadi-footer .footer-inner{
  max-width:1000px;
  margin:0 auto;
  text-align:center;
}

/* TITLE */
.authpadi-footer .footer-title{
  color:#000;
  font-size:14px;
  font-weight:600;
  margin-bottom:10px;
}

/* TEXT */
.authpadi-footer .footer-text{
  color:#000;
  font-size:13px;
  line-height:1.7;
  margin-bottom:10px;
}

/* ===============================
   TABLET
================================ */
@media (min-width: 576px){
  .authpadi-footer{
    padding:26px 18px;
  }

  .authpadi-footer .footer-title{
    font-size:15px;
  }

  .authpadi-footer .footer-text{
    font-size:13.5px;
  }
}

/* ===============================
   DESKTOP
================================ */
@media (min-width: 992px){
  .authpadi-footer{
    padding:30px 20px;
  }

  .authpadi-footer .footer-title{
    font-size:15px;
  }

  .authpadi-footer .footer-text{
    font-size:14px;
  }
}
</style>
<br>
<footer class="authpadi-footer">
  <div class="footer-inner">

    <p class="footer-title">
      © <?php echo date("Y"); ?> smsbyog | All rights reserved.
    </p>

    <p class="footer-text">
      smsbyog operates in full compliance with global SMS, OTP, and virtual number regulations.
      All services provided are strictly intended for legitimate verification, authentication,
      and testing purposes only. Use of smsbyog numbers for fraud, impersonation, spamming,
      financial crime, or any illegal activity is strictly prohibited.
    </p>

    <p class="footer-text">
      smsbyog shall not be held liable for misuse, abuse, or unlawful activities carried out
      by users. By using our platform, you agree to comply with all applicable local and
      international telecommunications, data protection, and anti-fraud laws.
    </p>

  </div>
</footer>
