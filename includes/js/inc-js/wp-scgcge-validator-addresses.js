jQuery(document).ready(function($) {

  // Addresses Page Validation
  var placeSearch, autocomplete;
  var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name'
  };

  function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
        (document.getElementById('autocomplete')),
        {types: ['geocode']});
    autocomplete.addListener('place_changed', fillInAddress);
  }

  function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete.getPlace();

    for (var component in componentForm) {
      document.getElementById(component).value = '';
      document.getElementById(component).disabled = false;
    }

    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) {
        var val = place.address_components[i][componentForm[addressType]];
        document.getElementById(addressType).value = val;
      }
    }
  }

  function geolocate() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        var geolocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        var circle = new google.maps.Circle({
          center: geolocation,
          radius: position.coords.accuracy
        });
        autocomplete.setBounds(circle.getBounds());
      });
    }
  }

  $("#companyRegistrationFormAddresses").validate({
      rules: {
          ro_care: {
              maxlength: 50 
          },
          ro_line2: {
              maxlength: 50 
          },
          ro_street: {
              required: true,
              maxlength: 52
          },
          ro_suburb: {
              required: true,
              maxlength: 30
          },
          ro_state: {
              required: true
          },
          ro_postcode: {
              required: true,
              minlength: 4,
              maxlength: 4,
              digits: true
          },
          ppb_occupy: {
              required: true
          },
          ppb_occupier : {
              required: true,
              maxlength: 200 
          },
          ppb_same: {
              required: true
          },
          ppb_care: {
              maxlength: 50 
          },
          ppb_line2: {
              maxlength: 50 
          },
          ppb_street: {
              required: true,
              maxlength: 52
          },
          ppb_suburb: {
              required: true,
              maxlength: 30
          },
          ppb_state: {
              required: true
          },
          ppb_postcode: {
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