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
 */
comments.post = function(area, post) {
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
 * Delete comment
 * @param area
 * @param post
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