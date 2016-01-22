/**
 * Changing password
 *
 * @param userId
 * @param newPassword
 */
function changePassword(userId, newPassword)
{
    $.post(
        '/control/user/profile/' + userId,
        {'new_password': newPassword},
        function (response) {
            var mclass = response['class'];
            var message = response['status'];
            var $msg = $('#change-password');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .text(message)
                .show();

            setTimeout(function(){
                $msg.hide();
            }, 3000);
        }
    );
}

/**
 * Changing user
 *
 * @param userId
 * @param newEmail
 * @param newUsername
 * @param newGroup
 * @param rating
 */
function editUser(userId, newEmail, newUsername, newGroup, rating, avatar)
{
    $.post(
        '/control/user/profile/' + userId,
        {
            'email': newEmail,
            'username': newUsername,
            'group': newGroup,
            'rating': rating,
            'avatar': avatar
        },
        function (response) {
            var mclass = response['class'];
            var message = response['status'];
            var $msg = $('#edit');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .text(message)
                .show();

            setTimeout(function(){
                $msg.hide();
            }, 3000);
        }
    );
}

/**
 * Changing user
 *
 * @param form
 */
function createUser(form)
{
    var data = {};
    $(form).find('input, select, textarea').each(function(i, e){
        data[$(e).attr('name')] = $(e).val();
    });

    $.post(
        '/control/user/create/',
        data,
        function (response) {
            if ( response['user_id'] ) {
                console.log(response);
                gui.go('/control/user/profile/' + response['user_id'], 2);
            }

            var mclass = response['class'];
            var errors = response['errors'];
            var message = response['message'];
            var $msg = $('#create');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .html(message || errors)
                .show();
        }
    );
}

/**
 * Ban user
 *
 * @param userId
 * @param time
 * @param reason
 */
function banUser(userId, time, reason)
{
    $.post(
        '/control/user/profile/' + userId,
        {
            'ban_time': time,
            'ban_reason': reason
        },
        function (response) {
            var mclass = response['class'];
            var message = response['status'];
            var $msg = $('#ban');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .text(message)
                .show();

            setTimeout(function(){
                $msg.hide();
            }, 3000);
        }
    );
}


/**
 * Updating group
 *
 * @param groupId
 * @param form
 */
function updateGroup(groupId, form)
{
    var data = {};
    $(form).find('input, select, textarea').each(function(i, e){
        if ( $(e).is('[type="checkbox"]') ) {
            data[$(e).attr('name')] = $(e).is(':checked') ? $(e).attr('value') : 0;
        } else {
            data[$(e).attr('name')] = $(e).val();
        }
    });

    $.post(
        '/control/user/groups/profile/' + groupId,
        data,
        function (response) {
            var mclass = response['class'];
            var message = response['status'];
            var $msg = $('#save');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .text(message)
                .show();

            setTimeout(function(){
                $msg.hide();
            }, 3000);
        }
    );
}


/**
 * Creation group
 *
 * @param form
 */
function createGroup(form)
{
    var data = {};
    $(form).find('input, select, textarea').each(function(i, e){
        if ( $(e).is('[type="checkbox"]') ) {
            data[$(e).attr('name')] = $(e).is(':not(:checked)')
        } else {
            data[$(e).attr('name')] = $(e).val();
        }
    });

    $.post(
        '/control/user/groups/create/',
        data,
        function (response) {
            var mclass = response['class'];
            var message = response['status'];
            var $msg = $('#save');

            $msg
                .removeClass('success')
                .removeClass('error')
                .addClass(mclass)
                .text(message)
                .show();


            if ( mclass == 'success' ) {
                gui.go('/control/user/groups', 3);
            }
        }
    );
}
