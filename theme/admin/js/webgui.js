/**
 * GUI Package
 */
var gui = {
    'init': function() {
        $.ajaxSetup({
            'beforeSend': function() {
                gui.preloader.start();
            }
        });

        $(document).ajaxComplete(function() {
            gui.preloader.stop();
        });
    },
    'go': function(url, timeout) {
        timeout = timeout || 0;
        if ( timeout > 0 ) {
            setTimeout(function(){ document.location = url; }, timeout * 1000)
        } else {
            document.location = url;
        }
    },
    'preloader': {
        'start': function() {
            $('.preloader').addClass('active');
        },
        'stop': function() {
            $('.preloader').removeClass('active');
        }
    },
    'randomString': function(length) {
        var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz~!@#$%^&*()-+|;:";
        var string_length = length || 8;
        var randomstring = '';
        for (var i = 0; i < string_length; i++) {
            var rnum = Math.floor(Math.random() * chars.length);
            randomstring += chars.substring(rnum, rnum+1);
        }

        return randomstring;
    }
};


$(function(){gui.init();});