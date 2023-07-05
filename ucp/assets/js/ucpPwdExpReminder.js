function checkPasswordReminder() {

    return new Promise(function (resolve, reject) {

        let username = $("input[name=username]").val().trim();
        let password = $("input[name=password]").val().trim();
        password = encodeURIComponent(window.btoa(password))
        $.post(UCP.ajaxUrl + "?module=userman&command=checkPasswordReminder",
            {
                username: username,
                password: password,
                loginpanel: 'ucp'
            }).done(function (response) {

                if (response.isSessionAlreadyUnlocked) {

                    window.location.reload();

                } else if (response.loginfailed) {

                    $("#login-window").height("300");
                    $("#error-msg").html(response.message).fadeIn("fast");

                } else if (response.mustresetpassword) {

                    alert(_(response.message));

                    if (response.resetlink) {
                        setTimeout(() => {
                            window.location.href = response.resetlink;
                        }, 300);
                    }

                } else {

                    if (!response.status) {
                        alert(response.message);
                    }

                    resolve(true);
                }
            }).fail(function (xhr, status, error) {
                UCP.showAlert(_(error), "error");
                reject(true);
            });
    });
};