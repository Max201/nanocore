function changePassword(userId, newPassword)
{
    $.post(
        '/control/user/profile/' + userId,
        {'new_password': newPassword},
        function (response) {
            var status = response['code'];
            var message = response['status'];

            $('#change-password')
                .removeClass('success')
                .removeClass('error')
                .addClass(status)
                .text(message)
                .slideDown(300);
        }
    );
}
