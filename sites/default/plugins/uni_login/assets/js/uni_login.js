jQuery(document).ready(function($) {

    $('#codo_login_with_twitter').on('click', function() {
        
        
        window.location = codo_defs.url + 'uni_login/login/Twitter';
    });
    
    $('#codo_login_with_facebook').on('click', function() {
        
        
        window.location = codo_defs.url + 'uni_login/login/Facebook';
    });

    $('#codo_login_with_google').on('click', function() {
        
        
        window.location = codo_defs.url + 'uni_login/login/Google';
    });
    
});

