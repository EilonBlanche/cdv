;( function( $ ) {

    'use strict';

    var XT_WELCOME = {};

    XT_WELCOME.notices = {

        init: function() {

            setTimeout(function() {

                $('.xt-notice').each(function() {
                    $(this).prependTo('#wpbody-content');
                });

            },10);
        }
    };

    $(document).ready(function() {

        XT_WELCOME.notices.init();
    });

})( jQuery );