var forum = {};

/**
 * Apply response to a view
 * @param response
 */
forum.response = function(response) {
    if ( typeof response.error != 'undefined' ) {
    } else {
        tinyMCE.activeEditor.setContent('');
        $('#posts-section').html(response);
    }
};

/**
 * Post comment
 * @param topic
 * @param disableAutoResize
 */
forum.post = function(topic, disableAutoResize) {
    if (event.keyCode == 13 && !disableAutoResize) {
        $(area).attr('rows', $(area).val().split("\n").length + 1);
    }

    if (event.keyCode == 13 && event.ctrlKey) {
        $.post(
            '/comment/post/' + topic,
            {
                'comment': tinyMCE.activeEditor.getContent('')
            },
            function (r) { forum.response(r); }
        );
    }
};

/**
 * Post comment
 * @param topic
 */
forum.postEnter = function(topic) {
    $.post(
        '/forum/post/' + topic,
        {
            'comment': tinyMCE.activeEditor.getContent('')
        },
        function (r) { forum.response(r); }
    );
};

/**
 * Delete comment
 * @param post
 */
forum.delete = function(post) {
    $.post(
        '/forum/delete',
        {
            'post': post
        },
        function (r) { forum.response(r); }
    );
};