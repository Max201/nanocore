var comments = {};

/**
 * Apply response to a view
 * @param response
 */
comments.response = function(response) {
    if ( typeof response.error != 'undefined' ) {
    } else {
        $('#comments-section').html(response);
    }
};

/**
 * Post comment
 * @param area
 * @param post
 * @param disableAutoResize
 */
comments.post = function(area, post, disableAutoResize) {
    if (event.keyCode == 13 && !disableAutoResize) {
        $(area).attr('rows', $(area).val().split("\n").length + 1);
    }

    if (event.keyCode == 13 && event.ctrlKey) {
        var body = $(area).val();
        $.post(
            '/comment/post/' + post,
            {
                'comment': body,
                'post': post
            },
            function (r) { comments.response(r); }
        );
    }
};

/**
 * Post comment
 * @param area
 * @param post
 */
comments.postEnter = function(area, post) {
    var body = $(area).val();
    $.post(
        '/comment/post/' + post,
        {
            'comment': body,
            'post': post
        },
        function (r) { comments.response(r); }
    );
};

/**
 * Delete comment
 * @param com
 */
comments.delete = function(com) {
    $.post(
        '/comment/delete',
        {
            'comment': com
        },
        function (r) { comments.response(r); }
    );
};