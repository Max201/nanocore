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
 */
function editUser(userId, newEmail, newUsername, newGroup)
{
    $.post(
        '/control/user/profile/' + userId,
        {
            'email': newEmail,
            'username': newUsername,
            'group': newGroup
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
