jQuery(document).ready(function($) {	

	/*
     * Select/Upload file(s) event
     */
    $('body').on('click', '.public_cert_file_button', function(e){
        e.preventDefault();

            var button = $(this),
                custom_uploader = wp.media({
            title: 'Upload File',
            library : {
                type : 'image'
            },
            button: {
                text: 'Upload File' // button label text
            },
            multiple: false // for multiple upload_file1 selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            //$(button).removeClass('button').html('<img class="upload_file1" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
            $('.wpd-ws-settings-public-cert-file').val(attachment.url);    

        })
        .open();
    });

        
    //  * Select/Upload file(s) event
     
    $('body').on('click', '.private_key_file_button', function(e){
        e.preventDefault();
            var button = $(this),
                custom_uploader = wp.media({
            title: 'Upload File',
            library : {
                // uncomment the next line if you want to attach upload_file1 to the current post
                // uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Upload File' // button label text
            },
            multiple: false // for multiple upload_file1 selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            //$(button).removeClass('button').html('<img class="upload_file2" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
            $('.wpd-ws-settings-private-key-file').val(attachment.url); 
        })
        .open();
    });
	

}); // end jQuery(document).ready
