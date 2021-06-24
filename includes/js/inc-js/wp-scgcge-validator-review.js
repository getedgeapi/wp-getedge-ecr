jQuery(document).ready(function($) {    

    // Review Form Validation
    $("#companyRegistrationFormReview").validate({
        rules: {
            agree: {
                required: true
            },
            applicant: {
                required: true
            },
            applicant_first_name: {
                required: true,
                maxlength: 20 
            },
            applicant_middle_name: {
                maxlength: 20 
            },
            applicant_last_name: {
                required: true,
                maxlength: 20 
            },
            applicant_line2: {
                maxlength: 50 
            },
            applicant_street: {
                required: true,
                maxlength: 52
            },
            applicant_suburb: {
                required: true,
                maxlength: 30
            },
            applicant_state: {
                required: true
            },
            applicant_postcode: {
                required: true,
                minlength: 4,
                maxlength: 4,
                digits: true
            },
        },
        messages: {
        },
        errorClass: "is-invalid",
        errorLabelContainer: "#messageBox",
        invalidHandler: function(event, validator) {
            // 'this' refers to the form
            var errors = validator.numberOfInvalids();
            if (errors) {
              var message = errors == 1
                ? 'You have <strong>1 error</strong>, and it has been highlighted.'
                : 'You have <strong>' + errors + ' errors</strong>, and they have been highlighted.';
              $("div.error span").html('<div class="alert alert-danger" role="alert">'+message+'</div>');
              $("div.error").show();
            } else {
              $("div.error").hide();
            }
        }
  
    });
        
});
      
      




