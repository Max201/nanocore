var likes = {};

/**
 * Message element
 * @type {string}
 */
likes.response = '#liked';

/**
 * Post like
 * @param plus
 * @param post
 * @param counter
 */
likes.add = function(plus, post, counter) {
    counter = counter ? $(counter) : $('.' + post);
    plus = plus ? '+' : '-';
    $.post('/like/post/' + plus+post, {}, function(response){
        if ( 'error' in response ) {
            $(likes.response).show().text(response['message']);
        } else {
            if ( counter ) {
                counter.text(response['success']);
            }

            $(likes.response).show().text(response['message']);
        }
    })
};

/**
 * Plus like
 * @param post
 * @param counter
 */
likes.plus = function(post, counter) {
    likes.add(true, post, counter);
};

/**
 * Minus like
 * @param post
 * @param counter
 */
likes.minus = function(post, counter) {
    likes.add(false, post, counter);
};