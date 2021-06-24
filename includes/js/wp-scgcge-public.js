jQuery(document).ready(function($) {

    var d = new Date();

    // Entity Page Radio Button Js
    $('input:radio[name="entity_type[]"]').bind('change', function(){
        if ($(this).val() == 'ORG') {
            $(":checkbox[value='SHA']").prop("checked",true);
            $(":checkbox[value='DIR']").prop("checked",false);
            $(":checkbox[value='SEC']").prop("checked",false);
        }
    });

    d.setFullYear(d.getFullYear()-18);
    if (d.getMonth() < 10) {
        var month = '0'+d.getMonth();
    } else {
        var month = d.getMonth();
    }
    if (d.getDate() < 10) {
        var day = '0'+d.getDate();
    } else {
        var day = d.getDate();
    }
    var max_date = day+'/'+month+'/'+d.getFullYear();
    
    // Dowhen Js // Show hide data-do-when
    $(document).doWhen();

    // Entity Page in Delete link js
    $(".confirm").on("click", function(e) {
        e.preventDefault();
        var link = this;
        var result = confirm("Delete the entity?");
        if (result) {
            window.location = link.href;
        }    
    });    

    // Form Attributes
    $('[data-toggle="tooltip"]').tooltip();
    
    // entity Form In Birthdate
    $('.birthdate').datepicker({
        format: 'dd/mm/yyyy',
        endDate: '-18y'
    });
    
     // entity Form In Birthdate
    $(".birthdate").inputmask("99/99/9999");  //static mask

    
    $("#register").css("display", "none");

    // Company Full name Show Hide
    $(".hideme").css("display", "none");

    // Company Search in Acn CheckBox To Show Hide
    $(".acn_only").change(function() {
        if(this.checked) {
            $(".business-name-question").css("display", "none");
            $(".company_name:text").attr('value', 'A.C.N. XXX XXX XXX'); 
            $(".company_name:text").prop('readonly', true);
            $(".check-name-now").prop('disabled', true);
            showNextStepACN($);
            document.getElementById("search-result").innerHTML = '';
        } else {
            $(".company_name:text").attr('value', '');
            $(".company_name:text").prop('readonly', false);
            $(".check-name-now").prop('disabled', false);
            hideNextStep($);
        }
    });
         
    // Company Check Availability Response Ajax  
    jQuery('.check-name-now').on('click', function(e){
        document.getElementById("search-result").innerHTML = '';
        e.preventDefault();
        if ($(".company_name").val() == "") {
            //jQuery('.check-name-now').html('No name specified');
        } else {
            jQuery('.check-name-now').prop('disabled', true);
            jQuery('.check-name-now').html('Please wait...');

            var companyName1 = $(".company_name").val().toUpperCase();
            var companyName2 = $(".legal_elements").val();
            var companyName = companyName1 + ' ' + companyName2;

            console.log("--- Initiating GetEDGE.com.au company name search ---");
            jQuery.ajax({
                url: 'https://getedge.com.au/do/search/',
                type: 'get',
                timeout: 10000,
                data: {'companyName': companyName, 'check': companyName1},
                success: function(data, status) {
                    var xdata = JSON.parse(data);
                    var thehtml = '';
                    var thehtmlExtra = '';
                    //console.log(xdata.availability);
                    if (xdata.availability == 'SubjectToNamesDetermination' || xdata.availability == 'ExistingBN') {
                        thehtmlExtra += '<p>Reason(s) for review: ';
                        thehtml = '<i class="fa fa-check-square-o yellow"></i> <strong>'+xdata.name+'</strong><br />is available but will be subject to a manual review';
                        if (xdata.objections.constructor === Array) { 
                            for (var i = 0; i < xdata.objections.length; i++) {
                                if (i==(xdata.objections.length-1)) {
                                    thehtmlExtra += xdata.objections[i]+'.';
                                } else {
                                    thehtmlExtra += xdata.objections[i]+', ';
                                }
                            }
                        } else {
                            thehtmlExtra += xdata.objections+'.';
                        }
                        thehtmlExtra += '</p>';
                        showNextStep($);

                        if (xdata.availability == 'ExistingBN') {
                            $("#search_result:text").attr('value', 'checkbn');
                            $("#company_name_full:text").attr('value', companyName);
                        }


                        
                    }
                    if (xdata.availability == 'Available') {
                        thehtml = '<i class="fa fa-check-square-o green"></i> <strong>'+xdata.name+'</strong><br />is available';
                        thehtmlExtra += '<p class="extram">Please proceed to finalize the registration process</p>';
                        showNextStep($);
                    }
                    if (xdata.availability == 'Unavailable') {
                        thehtml = '<i class="fa fa-times red"></i> <strong>'+xdata.name+'</strong><br />is unavailable';
                        thehtmlExtra += '<p class="extram">Reason(s) for rejection: ';
                        if (xdata.objections.constructor === Array) { 
                            for (var i = 0; i < xdata.objections.length; i++) {
                                if (i==(xdata.objections.length-1)) {
                                    thehtmlExtra += xdata.objections[i]+'.';
                                } else {
                                    thehtmlExtra += xdata.objections[i]+', ';
                                }
                            }
                        } else {
                            thehtmlExtra += xdata.objections+'.';
                        }
                        thehtmlExtra += '</p>';
                        hideNextStep($);
                    }           
                    if (xdata.availability == 'Company name search not available, please try again later.') {
                        thehtml = 'No response from ASIC.<br />ASIC company name search is currently Under Maintenance, please try again later.';
                        thehtmlExtra = 'You can proceed with the registration if you are confident that your name is unique, or choose to register an ACN only company.';
                        //console.log('asd');
                        //jQuery('.gverif').show();

                        showNextStep($);
                    } 
                    if (xdata.availability != 'Company name search not available, please try again later.' && xdata.availability != 'Unavailable' && xdata.availability != 'Available' && xdata.availability != 'SubjectToNamesDetermination' && xdata.availability != 'ExistingBN') {
                        thehtml = 'Communication error.<br />ASIC company name search is currently Under Maintenance, please try again later.';
                        thehtmlExtra = 'You can proceed with the registration if you are confident that your name is unique, or choose to register an ACN only company.';
                        //console.log('asd');
                        //jQuery('.gverif').show();

                        showNextStep($);
                    }
                        
                    showCheckAvailability($);
                    document.getElementById("search-result").innerHTML = '<h2>'+thehtml+'</h2>'+thehtmlExtra;
                    jQuery('#search-result').show();
                

                },
                error: function(xhr, desc, err) {
                    showCheckAvailability($);
                    document.getElementById("search-result").innerHTML = '<p class="hmess"><strong>ASIC company name search is currently Under Maintenance, please try again later.</strong></p><p class="extram">You can proceed with the registration if you are confident that your name is unique, or choose to register an ACN only company.</p>';
                    showNextStep($);
                    jQuery('#search-result').show();
                }
            }); // end ajax call
        }
    });


    //share class allow in entities page
    var doc = $(document);


    jQuery('a.add-type').off('click').on('click', function(e) {
        e.preventDefault(); 
        var content = jQuery('#type-container .type-row'),
        element = null;

        for(var i = 0; i<1; i++){
            element = content.clone();
            var type_div = 'teams_'+jQuery.now();
            element.attr('id', type_div);
            element.find('.remove-type').attr('targetDiv', type_div);
            element.appendTo('#type_container');
        }

        $('#type_container .type-row').each(function (i) {
            $(this).find('.type-row:last');

            var share_beneficials_show = $(this).find($('#share_beneficials_show'));
            var share_id=$(this).find($('input[name="share_id"]'));
            var share_class=$(this).find($('select[name="share_class"]'));
            var share_number=$(this).find($('input[name="share_number"]'));
            var share_paid=$(this).find($('input[name="share_paid"]'));
            var share_unpaid=$(this).find($('input[name="share_unpaid"]'));
            var share_beneficial=$(this).find($(':radio[name="share_beneficial"]'));
            var share_beneficiary=$(this).find($('input[name="share_beneficiary"]'));

            share_beneficials_show.eq(0).attr('id', 'share_beneficials_show'+i);
            share_id.eq(0).attr('name', 'share_details['+ i+'][share_id]');
            share_class.eq(0).attr('name', 'share_details['+ i+'][share_class]');
            share_number.eq(0).attr('name','share_details['+ i+'][share_number]');
            share_paid.eq(0).attr('name',  'share_details['+ i+'][share_paid]');
            share_unpaid.eq(0).attr('name', 'share_details['+ i+'][share_unpaid]');
          
            share_beneficial.each(function (k){
                var idLabel = 'share_beneficial_'+ i + k;
                share_beneficial.eq(k).attr('name',  'share_details['+ i+'][share_beneficial]');
                share_beneficial.eq(k).attr('id',  idLabel);
                share_beneficial.eq(k).next().eq(0).attr('for', idLabel);
            });

            share_beneficiary.eq(0).attr('name', 'share_details['+ i+'][share_beneficiary]');

			//$("div.share_beneficials_show").hide();

			$('input[name$="share_details['+ i+'][share_beneficial]"]').click(function() {
    			var share_beneficials = $(this).val();

                //$("div.share_beneficials_show").hide();
    			if(share_beneficials == 'N') {
    			  $("#share_beneficials_show" + (i-1) ).show();
    			} else {
                  $("#share_beneficials_show" + (i-1) ).hide();
                }
			});

            i++;
        });

    });

    
    $('#type_container .type-row').each(function (i) {
        
        $(this).find('.type-row:last'); 

        var share_beneficials_show = $(this).find($('#share_beneficials_show'));
        var share_id=$(this).find($('input[name="share_id"]'));
        var share_class=$(this).find($('select[name="share_class"]'));
        var share_number=$(this).find($('input[name="share_number"]'));
        var share_paid=$(this).find($('input[name="share_paid"]'));
        var share_unpaid=$(this).find($('input[name="share_unpaid"]'));
        var share_beneficiary=$(this).find($('input[name="share_beneficiary"]'));


        share_beneficials_show.eq(0).attr('id', 'share_beneficials_show'+i);
        share_id.eq(0).attr('name', 'share_details['+ i+'][share_id]');
        share_class.eq(0).attr('name', 'share_details['+ i+'][share_class]');
        share_number.eq(0).attr('name','share_details['+ i+'][share_number]');
        share_paid.eq(0).attr('name',  'share_details['+ i+'][share_paid]');
        share_unpaid.eq(0).attr('name', 'share_details['+ i+'][share_unpaid]');
        share_beneficiary.eq(0).attr('name', 'share_details['+ i+'][share_beneficiary]');

		//$("div.share_beneficials_show").hide();
		
        $('input[name$="share_details['+ i+'][share_beneficial]"]').click(function() {
    		var share_beneficials = $(this).val();
    		//$("div.share_beneficials_show").hide();
    		if(share_beneficials == 'N') {
    		  $("#share_beneficials_show" + (i-1) ).show();
    		} else {
              $("#share_beneficials_show" + (i-1) ).hide();
            }
		});

        i++;
    });

    jQuery(".remove-type").off('click').on('click', function (e) {
        var id = jQuery(this).attr('data-id');
        var targetDiv = jQuery(this).attr('targetDiv');
        jQuery('#' + targetDiv).remove();      
        return true;
    });

    $("#type_container").on('click', '.remove-type', function(){
       
        $(this).parent().parent().remove();
        var share_id = jQuery(this).attr('data-id');
        //var entity_id = jQuery(this).attr('id'); 

         if(share_id != '' && share_id > 0 ) {    
            jQuery.ajax({
            url:WpScgcgePublicScript.ajax_url,
            type: 'POST',
            data: {'action': 'share_entity_delete_action','share_id': share_id},
            success: function(data) {
                $(this).parent().parent().remove();
            }
            });
        }

    });


});