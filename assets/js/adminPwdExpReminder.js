function checkPasswordReminder(thisPointerFromLogin) {

    return new Promise(function (resolve, reject) {

        window.loginFormPointer = thisPointerFromLogin;
        window.username = $('input[name="username"]', thisPointerFromLogin).val();
        window.password = $('input[name="password"]', thisPointerFromLogin).val();
        window.password = encodeURIComponent(window.btoa(window.password))

        $.post("/admin/ajax.php?module=userman&command=checkPasswordReminder",
            {
                username: window.username,
                password: window.password,
                loginpanel: 'admin'
            }).done(function (response) {

                if (response.isSessionAlreadyUnlocked) {

                    window.location.reload();

                } else if (response.loginfailed) {

                    fpbxToast(_(response.message), _('Error'), "error");

                } else if (response.mustresetpassword) {

                    alert(_(response.message));

                    if (response.usertype && response.usertype == 'ucp') {
                        setTimeout(() => {
                            window.location.href = response.resetlink
                        }, 300);
                    } else {

                        // Displaying Reset form
                        $('.ui-dialog-content').html(`
                            <div id="reset_form">
                                <h5> ${_('Your password has been expired. Please reset it to continue.')}</h5>
                                <div class="form-group">
                                    <input type="password" id="newPassword" name="newPassword" class="form-control" value="" placeholder="Password" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <input type="password" id="confirmPaswword" name="confirmPaswword" class="form-control" value="" placeholder="Confirm Paswword" autocomplete="off">
                                </div>
                            </div>
                        `);

                        $('.ui-dialog-titlebar').text('Reset Password');
                        $('div.ui-dialog-buttonpane.ui-widget-content.ui-helper-clearfix > div > button:nth-child(1)').addClass('resetPasswordButton');

                        window.resetToken = response.resetPasswordToken;
                    }

                } else {

                    if (!response.status) {
                        alert(_(response.message));
                    }

                    resolve(true);
                }
            }).fail(function (xhr, status, error) {
                fpbxToast(_(error), _('Error'), "error");
                reject(true);
            });
    })
}

$('#reset_form input[name="newPassword"]').on('keyup', function () {
    if ($('#reset_form input[name="newPassword"]').val() != $('#reset_form input[name="confirmPaswword"]').val()) {
        fpbxToast(_('Confirm password does not match'), _("Error"), 'error');
    }
});

function resetAdminPassswordWithToken(thisPointerFromLogin) {
    window.newPassword = $('#reset_form input[name="newPassword"]', thisPointerFromLogin).val();
    window.confirmPassword = $('#reset_form input[name="confirmPaswword"]', thisPointerFromLogin).val();

    if (window.newPassword == '' || window.confirmPassword == '') {
        fpbxToast(_('Invalid Password'), _('Error'), 'error');
        return false;
    }
    if ($('#newPassword').val() != $('#confirmPaswword').val()) {
        fpbxToast(_('Confirm password does not match'), _('Error'), 'error');
        return false;
    }

    $.post("/admin/ajax.php?module=userman&command=resetAdminPasswordWithToken",
        {
            newpassword: window.newPassword,
            token: window.resetToken,
            confirmpassword: window.confirmPassword,
        }).done(function (response) {
            if (response.status) {
                fpbxToast(_(response.message));
            } else {
                fpbxToast(_(response.message), _('Error'), "error");
            }
            setTimeout(() => {
                window.location.reload();
            }, 300);
        }).fail(function (xhr, status, error) {
            fpbxToast(_(error), _('Error'), "error");
        });

};