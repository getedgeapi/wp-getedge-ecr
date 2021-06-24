jQuery(document).ready(function($) {

    // Entity Pge Validation
    $("#companyRegistrationFormEntity").validate({
        rules: {
            
            entity_first_name: {
                required: true,
                maxlength: 20 
            },
            entity_middle_name1: {
                maxlength: 20 
            },
            entity_middle_name2: {
                maxlength: 20 
            },
            entity_last_name: {
                required: true,
                maxlength: 20 
            },
            entity_role_dir: {
                require_from_group: [1, ".roles-group"]
            },
            entity_role_sec: {
                require_from_group: [1, ".roles-group"]
            },
            entity_role_sha: {
                require_from_group: [1, ".roles-group"]
            },
            entity_former: {
                required: true,
                maxlength: 20 
            },
            entity_former_first_name: {
                required: true,
                maxlength: 20 
            },
            entity_former_middle_name1: {
                maxlength: 20 
            },
            entity_former_middle_name2: {
                maxlength: 20 
            },
            entity_former_last_name: {
                required: true,
                maxlength: 20 
            },
            entity_birth_date: {
                required: true,
                maxlength: 20,
            },
            entity_birth_country: {
                required: true,
                maxlength: 20 
            },
            entity_birth_state: {
                required: function(element) {
                    return $("#entity_birth_country option:selected").text() == 'AU';
                  }
            },
            entity_birth_suburb: {
                required: true,
                maxlength: 30 
            },
            entity_company_name: {
                required: true,
                maxlength: 200 
            },
            entity_company_country: {
                required: true
            },
            entity_company_acn: {
                required: true,
                maxlength: 9,
                digits: true
            },             
            share_class: {
                required: true
            },
            share_number: {
                required: true,
                digits: true,
                min:1
            },
            share_paid: {
                required: true,
                number: true
            },
            share_unpaid: {
                required: true,
                number: true
            },
            share_beneficial: {
                required: true
            },
            share_beneficiary: {
                required: true,
                maxlength: 200 
            },
            address_care: {
                maxlength: 50 
            },
            address_line2: {
                maxlength: 50 
            },
            address_street: {
                required: true,
                maxlength: 52
            },
            address_suburb: {
                required: true,
                maxlength: 30
            },
            address_state: {
                required: true
            },
            address_postcode: {
                required: true,
                minlength: 4,
                maxlength: 4,
                digits: true
            },
            address_country: {
                required: true,
                maxlength: 30
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