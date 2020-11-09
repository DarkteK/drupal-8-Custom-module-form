(function($){
    let inputs = $( "form .benefits-request-form input[id^='edit-']" );
    if (inputs) {
      inputs.each((i,e)=>{
        $(e).after('<div id="inline-error_' + e.id +'" class="lgs_forms-inline-error" ></div>' );
      });
    }
})(jQuery);


(function ($, Drupal) {
    Drupal.behaviors.benefitsRequestFormModal = {
      attach: function (context, settings) {
        $( document ).ready(function() {
          $('.close-modal-btn').unbind('click').click((event) => {
            event.preventDefault();
            $('#block-benefits_request_form-dialog').hide();
          });  
        });
      }
    };
})(jQuery, Drupal);