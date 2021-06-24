jQuery(document).ready(function($) {

    //Entities Page Validation
    $("#companyRegistrationFormEntities").validate({
        rules: {
            mindir: {
                min: 1 
            },
            minsha: {
                min: 1 
            },
        },
        messages: {
        },
        errorClass: "is-invalid",
        errorLabelContainer: "#messageBox",
        ignore: ".ignore",
        invalidHandler: function(event, validator) {
            // 'this' refers to the form
            var errors = validator.numberOfInvalids();
            if (errors) {
              var message = 'Minimum requirements: <strong>1 shareholder</strong> and <strong>1 Australian director</strong>';
              $("div.error span").html('<div class="alert alert-danger" role="alert">'+message+'</div>');
              $("div.error").show();
            } else {
              $("div.error").hide();
            }
        }  
    });    
});