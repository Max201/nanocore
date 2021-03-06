/**
 * GUI Package
 */
var gui = {
    'active_input': {},
    'get': {},
    'init': function() {
        $.ajaxSetup({
            'beforeSend': function() {
                gui.preloader.start();
            }
        });

        $('input').on('click', function(){
            gui.active_input = $(this);
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
            setTimeout(function(){ document.location.href = url; }, timeout * 1000)
        } else {
            document.location.href = url;
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
            $('.preloader').removeClass('off');
        },
        'stop': function() {
            $('.preloader').addClass('off');
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
    },
    'copy': function(element) {
        var $el = $(element);
        $el.attr('contenteditable', true).focus();

        document.execCommand('selectAll', false, null);
        document.execCommand('copy', false, null);
        $el.removeAttr('contenteditable');
    },
    'snippetclip': function(element) {
        var val = $(element).text();
        $(element).text('{{ ' + val + ' }}');
        gui.copy(element);
        $(element).text(val);
    }
};


$(function(){gui.init();});