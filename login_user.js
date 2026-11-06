document.addEventListener(
    "DOMContentLoaded",
    function () {

        const password =
            document.getElementById(
                "password"
            );

        const togglePassword =
            document.getElementById(
                "togglePassword"
            );

        const email =
            document.getElementById(
                "email"
            );

        const rememberMe =
            document.getElementById(
                "rememberMe"
            );

        const loginForm =
            document.getElementById(
                "loginForm"
            );


        // ==================================
        // SHOW / HIDE PASSWORD
        // ==================================

        if (
            togglePassword &&
            password
        ) {

            togglePassword.addEventListener(
                "click",
                function () {

                    if (
                        password.type ===
                        "password"
                    ) {

                        password.type =
                            "text";

                        togglePassword
                            .textContent =
                            "🙈";

                    } else {

                        password.type =
                            "password";

                        togglePassword
                            .textContent =
                            "👁";

                    }

                }
            );

        }


        // ==================================
        // INGAT EMAIL
        // ==================================

        if (
            email &&
            rememberMe
        ) {

            const savedEmail =
                localStorage.getItem(
                    "nijbookUserEmail"
                );


            if (savedEmail) {

                email.value =
                    savedEmail;

                rememberMe.checked =
                    true;

            }

        }


        // ==================================
        // SAAT FORM LOGIN DIKIRIM
        // ==================================

        if (
            loginForm &&
            email &&
            rememberMe
        ) {

            loginForm.addEventListener(
                "submit",
                function () {

                    if (
                        rememberMe.checked
                    ) {

                        localStorage
                            .setItem(
                                "nijbookUserEmail",
                                email.value
                            );

                    } else {

                        localStorage
                            .removeItem(
                                "nijbookUserEmail"
                            );

                    }

                }
            );

        }

    }
);