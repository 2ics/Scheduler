$(document).ready(function() {
          $("#slider").slider({
              animate: true,
              value:$("#amount").val(),
              min: 0,
              max: 10000,
              step: 100,
              slide: function(event, ui) {
                  update(1,ui.value); //changed
              }
          });

          $("#slider2").slider({
              animate: true,
              value:$("#duration").val(),
              min: 0,
              max: 60,
              step: 1,
              slide: function(event, ui) {
                  update(2,ui.value); //changed
              }
          });

          //Added, set initial value.
          $('#slider a').html('<label><span class="glyphicon glyphicon-chevron-left"></span>0<span class="glyphicon glyphicon-chevron-right"></span></label>');
          $('#slider2 a').html('<label><span class="glyphicon glyphicon-chevron-left"></span>0<span class="glyphicon glyphicon-chevron-right"></span></label>');
          
          update(1,$("#amount").val());
          update(2,$("#duration").val());
      });

      //changed. now with parameter
      function update(slider,val) {
        //changed. Now, directly take value from ui.value. if not set (initial, will use current value.)
        var $amount = slider == 1?val:$("#amount").val();
        var $duration = slider == 2?val:$("#duration").val();
        slider == 1?$("#amount").val(val):$("#duration").val(val);
         $('#slider a').html('<label><span class="glyphicon glyphicon-chevron-left"></span> '+$amount+' <span class="glyphicon glyphicon-chevron-right"></span></label>');
         $('#slider2 a').html('<label><span class="glyphicon glyphicon-chevron-left"></span> '+$duration+' <span class="glyphicon glyphicon-chevron-right"></span></label>');
      }