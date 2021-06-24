function showNextStep($) {
    $("#search_result:text").attr('value', 'available');
    $("#company_name_full:text").attr('value', $(".company_name").val().toUpperCase() + ' ' + $(".legal_elements").val());
    $("#register").css("display", "inline-block");
}
function showNextStepACN($) {
    $("#search_result:text").attr('value', 'acnonly');
    $("#company_name_full:text").attr('value', $(".company_name").val().toUpperCase() + ' ' + $(".legal_elements").val());
    $("#register").css("display", "inline-block");
}
function hideNextStep($) {
    $("#search_result:text").attr('value', '');
    $("#company_name_full:text").attr('value', '');
    $("#register").css("display", "none");
}
function showCheckAvailability($) {
    $('.check-name-now').html('Check Availability');
    $('.check-name-now').prop('disabled', false);
}
