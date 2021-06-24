// Google map Geocoder

var GoogleMapsApp = GoogleMapsApp || {};

GoogleMapsApp.Utilities = (function () {
    var _getUserLocation = function (successCallback, failureCallback) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                successCallback(position);
            }, function () {
                failureCallback(true);
            });
         } else {
             failureCallback(false);
         }
    };
    
    return {
        GetUserLocation: _getUserLocation
    }
})();

GoogleMapsApp.Application = (function () {
    var _geocoder;
    
    var _init = function () {
        _geocoder = new google.maps.Geocoder;
        
        GoogleMapsApp.Utilities.GetUserLocation(function (position) {
            var latLng = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            _autofillFromUserLocation(latLng);
            _initAutocompletes(latLng);
        }, function (browserHasGeolocation) {
            _initAutocompletes();
        });
    };
    
    var _initAutocompletes = function (latLng) {
        jQuery('.places-autocomplete').each(function () {
            var input = this;
            var isPostalCode = jQuery(input).is('[id$=PostalCode]');
            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: [isPostalCode ? '(regions)' : 'address']
            });
            if (latLng) {
                _setBoundsFromLatLng(autocomplete, latLng);
            }
            
            autocomplete.addListener('place_changed', function () {
                _placeChanged(autocomplete, input);
            });
            
            jQuery(input).on('keydown', function (e) {
                // Prevent form submit when selecting from autocomplete dropdown with enter key
                if (e.keyCode === 13 && jQuery('.pac-container:visible').length > 0) {
                    e.preventDefault();
                }
            });
        });
    }
    
    var _autofillFromUserLocation = function (latLng) {
        _reverseGeocode(latLng, function (result) {
            jQuery('.address').each(function (i, fieldset) {
                _updateAddress({
                    fieldset: fieldset,
                    address_components: result.address_components
                });
            });
        });
    };
    
    var _reverseGeocode = function (latLng, successCallback, failureCallback) {
        _geocoder.geocode({ 'location': latLng }, function(results, status) {
            if (status === 'OK') {
                if (results[1]) {
                    successCallback(results[1]);
                } else {
                    if (failureCallback)
                        failureCallback(status);
                }
            } else {
                if (failureCallback)
                    failureCallback(status);
            }
        });
    }
    
    var _setBoundsFromLatLng = function (autocomplete, latLng) {
        var circle = new google.maps.Circle({
            radius: 40233.6, // 25 mi radius
            center: latLng
        });
        autocomplete.setBounds(circle.getBounds());
    }
    
    var _placeChanged = function (autocomplete, input) {
        var place = autocomplete.getPlace();
        _updateAddress({
            input: input,
            address_components: place.address_components
        });
    }
    
    var _updateAddress = function (args) {
        var $fieldset;
        var isPostalCode = false;
        if (args.input) {
            $fieldset = jQuery(args.input).closest('.address-fields');
            isPostalCode = jQuery(args.input).is('[maps=postal-code]');
            console.log(isPostalCode);
        } else {
            $fieldset = jQuery(args.fieldset);
        }
        
        var $street = $fieldset.find('[maps=street-address]');
        var $street2 = $fieldset.find('[id$=Street2]');
        var $postalCode = $fieldset.find('[maps=postal-code]');
        var $city = $fieldset.find('[maps=address-level2]');
        var $country = $fieldset.find('[maps=country]');
        var $state = $fieldset.find('[maps=address-level1]');
        
        if (!isPostalCode) {
            $street.val('');
            $street2.val('');
        }
        $postalCode.val('');
        $city.val('');
        $country.val('');
        $state.val('');
        
        var streetNumber = '';
        var route = '';
        
        for (var i = 0; i < args.address_components.length; i++) {
            var component = args.address_components[i];
            var addressType = component.types[0];

            switch (addressType) {
                case 'street_number':
                    streetNumber = component.long_name;
                    break;
                case 'route':
                    route = component.long_name;
                    break;
                case 'locality':
                    $city.val(component.long_name);
                    break;
                case 'administrative_area_level_1':
                    $state.val(component.short_name);
                    break;
                case 'postal_code':
                    $postalCode.val(component.long_name);
                    break;
                case 'country':
                    $country.val(component.short_name);
                    break;
            }
        }
        
        if (route) {
            $street.val(streetNumber && route
                        ? streetNumber + ' ' + route
                        : route);
        }
        jQuery(document).doWhen();
    }
    
    return {
        Init: _init
    }
})();

/* This should ideally be a callback for the async version of the Google Maps script reference.
   However, Codepen doesn't give enough control over the document to ensure that the Google
   Maps script tag is placed after the JS code here. */
GoogleMapsApp.Application.Init();

