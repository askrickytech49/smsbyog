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
    url: "api/service/getService",
    data: params,
    dataType: "json", 
    error: function (e) {
       Notiflix.Block.remove('#radiumsahil');
        console.log("AJAX error:", e);
    },
    success: function (data) {
       Notiflix.Block.remove('#radiumsahil');
            console.log("ok");
         const jsonResponse = data;
       // Get the list element
       const listElement = document.getElementById('list');

       // Remove existing list items
       while (listElement.firstChild) {
           listElement.removeChild(listElement.firstChild);
       }

       // Add list items
       jsonResponse.service.forEach(service => {
           const listItem = document.createElement('li');
           listItem.className = 'clearfix shadow-sm shadow-showcase point';
           listItem.style = 'border: 2px solid gray; padding: 5px; margin-bottom: 5px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;';
           
           // Constructing the inner HTML
           listItem.innerHTML = `
           <div style="display: flex; align-items: center;">
               
               <div class="about" style="margin-left: 10px;">
                   <div class="name">${service.service_name}</div>
                   <div class="status" style="color: ${service.stock ? (service.stock < 10 ? 'red' : 'green') : 'transparent'}">
                       ${service.stock ? `${service.stock} Stocks` : ''}
                   </div>
               </div>
           </div>
           <input type="hidden" class="service_id" value="${service.id}">
           <div class="amount" style="margin-left: auto; font-weight: bolder;">₦${service.service_price}</div>
       `;
       

           listElement.appendChild(listItem);
       });


       const listItems = document.querySelectorAll('.list li');
       const show_amount = document.getElementById('show_amount');
       const show_name = document.getElementById('show_name');
   
       show_amount.innerHTML = `??`;
       show_name.innerHTML = `??`;
       document.getElementById('service_code').value = "";
       listItems.forEach(item => {
           item.addEventListener('click', function () {
               // Reset border color of all items
               listItems.forEach(item => {
                   item.style.borderColor = 'gray';
               });
   
               // Set border color of clicked item to blue
               this.style.borderColor = 'blue';
               const name = this.querySelector('.name').innerText;
               const amount = this.querySelector('.amount').innerText;
               const  serviceop = this.querySelector('.service_id').value;

               show_amount.innerHTML = `${amount}`;
               show_name.innerHTML = `${name}`;
               document.getElementById('service_code').value = serviceop;
   
           });
       });
       var inputElement = document.getElementById("search_op");
       inputElement.removeAttribute("disabled");
    //    Notiflix.Block.remove('#radiumsahil');
     
       document.getElementById('server_no').value = server;
       console.log(server);

    }
});

    });
 
    
  $("#buy-numbers").click(function () {
       var server = $("#server_no").val();  
         var service = $("#service_code").val();       
         var token = $("#token").val();
          if (server == "") {
    console.log("Select Server");
    toast.error('Please Select Server');        
    return;
  }
 
        if (service == '') {
            toast.error('Select Service');
            return; // Stop execution if email or password is blank
        }

        $('#buy-numbers').prop("disabled", true);
        $('#buy-numbers').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Finding Number...');

        var params = {
            server: server,
            service: service,
            token: token,
        };
        $.ajax({
            type: "GET",
            url: "api/service/buynumber",
            data: params,
            dataType: "json",
            error: function (e) {
                console.log(e);
                toast.error('An error occurred.');
                $('#buy-numbers').html("<span class='fa fa-cart-plus' style='margin-right: 8px;'></span>Buy Number");
                $('#buy-numbers').prop("disabled", false);
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

