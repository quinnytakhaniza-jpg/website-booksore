document.addEventListener(
    "DOMContentLoaded",
    function () {

        const registerForm =
            document.getElementById(
                "registerForm"
            );

        const password =
            document.getElementById(
                "password"
            );

        const confirmPassword =
            document.getElementById(
                "confirmPassword"
            );

        const togglePassword =
            document.getElementById(
                "togglePassword"
            );

        const toggleConfirmPassword =
            document.getElementById(
                "toggleConfirmPassword"
            );

        const passwordMessage =
            document.getElementById(
                "passwordMessage"
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
        // SHOW / HIDE KONFIRMASI PASSWORD
        // ==================================

        if (
            toggleConfirmPassword &&
            confirmPassword
        ) {

            toggleConfirmPassword
                .addEventListener(
                    "click",
                    function () {

                        if (
                            confirmPassword
                                .type ===
                            "password"
                        ) {

                            confirmPassword
                                .type =
                                "text";

                            toggleConfirmPassword
                                .textContent =
                                "🙈";

                        } else {

                            confirmPassword
                                .type =
                                "password";

                            toggleConfirmPassword
                                .textContent =
                                "👁";

                        }

                    }
                );

        }


        // ==================================
        // VALIDASI REGISTER
        // ==================================

        if (
            registerForm &&
            password &&
            confirmPassword
        ) {

            registerForm.addEventListener(
                "submit",
                function (event) {

                    // Bersihkan pesan lama
                    if (
                        passwordMessage
                    ) {

                        passwordMessage
                            .style
                            .display =
                            "none";

                        passwordMessage
                            .textContent =
                            "";

                    }


                    // ==========================
                    // MINIMAL 6 KARAKTER
                    // ==========================

                    if (
                        password.value
                            .length < 6
                    ) {

                        event
                            .preventDefault();


                        if (
                            passwordMessage
                        ) {

                            passwordMessage
                                .textContent =
                                "Password minimal 6 karakter.";

                            passwordMessage
                                .style
                                .display =
                                "block";

                        }

                        return;

                    }


                    // ==========================
                    // PASSWORD HARUS SAMA
                    // ==========================

                    if (
                        password.value !==
                        confirmPassword.value
                    ) {

                        event
                            .preventDefault();


                        if (
                            passwordMessage
                        ) {

                            passwordMessage
                                .textContent =
                                "Password dan konfirmasi password tidak sama.";

                            passwordMessage
                                .style
                                .display =
                                "block";

                        }

                        return;

                    }

                }
            );

        }

    }
);