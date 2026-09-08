import { toast } from 'https://esm.sh/wc-toast';
//import { copyToClipboard } from 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.min.js';
    //     var options1 = {
    //         searchable: true,
    //         placeholder: 'Select Service'
    //     };
    //  var op =  NiceSelect.bind(document.getElementById("service-id"), options1);
          var options2 = {
             placeholder: 'Select Server'
        };
     var op1 =  NiceSelect.bind(document.getElementById("server-id"), options2);

                
$(document).ready(function () {

    $('#server-id').change(function () {
        // ...
        var token = $("#token").val();
        var params = { token: token, server: $(this).val() };
        var server = $("#server-id option:selected").val();  
        Notiflix.Block.standard('#radiumsahil');
     
         $.ajax({
    type: "GET",
    url: "api/service/getServiceUsa",
    data: params,
    dataType: "json", 
    error: function (e) {
       Notiflix.Block.remove('#radiumsahil');
        console.log("AJAX error:", e);
    },
    success: function (json) {
                $('#buy-numbers').html("<span class='fa fa-cart-plus' style='margin-right: 8px;'></span>Buy Number");
                $('#buy-numbers').prop("disabled", false);
                console.log("Server response:", json);
                if (json.status === "200" || json.status === 200) {
                    toast.success(json.message || 'Number Purchased!');
                    checkOrder();
                } else {
                    toast.error(json.message || 'Purchase failed. Please try again.');
                }
            }
        });
    });
           
             
    
    
    
    
});
var settime = 0; 

