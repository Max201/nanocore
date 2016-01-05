/**
 * Filemanager
 * @type {{}}
 */
gui.fm = {};

/**
 * Function to display filemanager section
 */
gui.fm.toggle = function () {
    var $fm = $('#filemanager');

    if ( !$fm.is(':visible') ) {
        gui.fm.reload(function($fm){
            $fm.slideToggle('fast');
        });
    } else {
        $fm.slideToggle('fast');
    }
};

/**
 * Function to reload contents
 */
gui.fm.reload = function (callback) {
    callback = callback || function () {};
    gui.fm.request({}, callback);
};

/**
 * Function to load request contents
 */
gui.fm.request = function (data, callback) {
    var $fm = $('#filemanager');
    callback = callback || function () {};
    data = data || {};
    data['wyi'] = typeof _wyi_name != 'undefined' ? _wyi_name : 'edit';
    $.get('/admin/files/', data, function(r){
        $fm.html(r);
        callback($fm, r);
    });
};

/**
 * Function to upload files via ajax
 * @param callback
 * @param progress
 */
gui.fm.upload = function (callback, progress) {
    var progress_handler = progress || function(p) { $('#filemanager #progress').text(p + '%'); };
    var callback_handler = callback || function(r) {  };

    // Data
    var formData = new FormData($('#filemanager form#upload')[0]);

    // Sending
    $.ajax({
        url: '/admin/files',
        type: 'POST',
        xhr: function() {
            myXhr = $.ajaxSettings.xhr();
            if(myXhr.upload){
                myXhr.upload.addEventListener('progress', function(e){
                    var loaded = Math.round(e.loaded / e.total * 100);
                    progress_handler(loaded);
                }, false);
            }
            return myXhr;
        },
        success: function(){ gui.fm.request({'d': $('#filemanager header').text()}, callback_handler); },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
};