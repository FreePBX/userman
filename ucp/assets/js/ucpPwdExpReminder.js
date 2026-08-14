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
                    resolve(true);
                    setTimeout(() => window.location.reload(), 50);
                    return;
                } else if (response.loginfailed) {
                    if(response.hasOwnProperty("samlenabled") && response.samlenabled){
                        handelSamlAuth('ucp',username);
                        return ;
                     }
                    $("#error-msg").html(response.message).fadeIn("fast");
                    resolve(false);
                    return;
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

function handelSamlAuth(loginpanel,username){
    if (typeof buildAuthTemplate === "function") {
        if(loginpanel == 'ucp'){
            let response =  buildAuthTemplate(loginpanel,username);
        }
    }
}
