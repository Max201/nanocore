/**
 * GUI Package
 */
var gui = {
    'get': {},
    'init': function() {
        $.ajaxSetup({
            'beforeSend': function() {
                gui.preloader.start();
            }
        });

        $(document).ajaxComplete(function() {
            gui.preloader.stop();
        });

        (window.onpopstate = function () {
            var match,
                pl     = /\+/g,  // Regex for replacing addition symbol with a space
                search = /([^&=]+)=?([^&]*)/g,
                decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
                query  = window.location.search.substring(1);

            gui.get = {};
            while (match = search.exec(query))
                gui.get[decode(match[1])] = decode(match[2]);
        })();
    },
    'go': function(url, timeout) {
        timeout = timeout || 0;
        if ( timeout > 0 ) {
            setTimeout(function(){ document.location = url; }, timeout * 1000)
        } else {
            document.location = url;
        }
    },
    'uri': function(data, timeout) {
        // Updating data
        data = data || {};
        $.each(gui.get, function(k, v){
            if ( !data.hasOwnProperty(k) ) {
                data[k] = v;
            }
        });

        // Build url
        var params = new Array();
        $.each(data, function(k, v){
            params.push(k + '=' + encodeURIComponent(v));
        });

        gui.go(document.location.pathname + '?' + params.join('&'), timeout);
    },
    'order_toggle': function (order) {
        if ( typeof gui.get.order != 'undefined' && gui.get.order == order ) {
            return '-' + order;
        }

        return order;
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
    },
    'popup': function(url, options) {
        var $opt = $.extend({
            'name': "Untitled",
            'width': 600,
            'height': 300
        }, options || {});

        _wnd = window.open(url, $opt.name, 'height=' + $opt.height + ',width=' + $opt.width);
        return _wnd;
    }
};


$(function(){gui.init();});