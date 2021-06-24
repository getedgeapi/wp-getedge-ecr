jQuery(document).ready(function($) {

    // General Detail Page Validation
    $("#companyRegistrationFormGeneral").validate({
        rules: {
            jurisdiction: {
                required: true
            },
            company_subclass: {
                required: true
            },
            holding_company: {
                required: true
            },
            bn_when: {
                required: true
            },
            holding_name: {
                required: true,
                maxlength: 200 
            },
            holding_country: {
                required: true
            },
            holding_abn: {
                required: true,
                minlength: 11,
                maxlength: 11,
                digits: true
            },
            holding_acn: {
                required: true,
                maxlength: 9,
                digits: true
            },
            bn_state: {
                required: true
            },
            bn_number: {
                required: true,
                maxlength: 10,
            },
            bn_abn: {
                required: true,
                minlength: 11,
                maxlength: 11,
                digits: true
            }
                            
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




