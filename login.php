<?php require 'pages/utils/auth/auth_check.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login | Winners Select</title>
    <link href="pages/assets/css/app.css" rel="stylesheet">
    <style>
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <main class="main h-100 w-100">
        <div class="container h-100">
            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Sign In</h3>
                            <form id="login_form" novalidate>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email">
                                    <span class="error-message text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                                    <span class="error-message text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="remember_me" name="remember_me">
                                        <label class="custom-control-label" for="remember_me">Remember Me</label>
                                    </div>
                                </div>
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Login
                                        <div class="loading-spinner"></div>
                                    </button>
                                </div>
                                <div class="text-center">
                                    <a href="forgot-password.php">Forgot Password?</a>
                                </div>
                            </form>
                            <div id="login_message" class="text-center text-danger mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

	<script src="pages/assets/js/app.js"></script>
    <script>
        $(document).ready(function () {
            $("#login_form").submit(function (e) {
                e.preventDefault();
                
                let formData = $(this).serialize();
                let $loginBtn = $("button[type='submit']");
                let $spinner = $(".loading-spinner");

                // Clear previous errors
                $(".error-message").text("");
                $(".form-control").removeClass("is-invalid");

                // Disable button & show spinner
                $loginBtn.prop("disabled", true);
                $spinner.show();

				console.log(formData);

                $.ajax({
                    type: "GET",
                    url: "pages/utils/auth/login_process.php",
                    data: formData,
                    dataType: "json",
					// beforeSend: function () {
					// 	console.log(formData);
					// },
                    success: function (response) {
                        if (response.status === "success") {
                            toastr.success(response.message, "Success", {
                                timeOut: 3000,
                                closeButton: true,
                                progressBar: true
                            });
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else if (response.status === "error" && response.errors) {
                            $.each(response.errors, function (field, message) {
                                let $input = $(`#${field}`);
                                $input.addClass("is-invalid");
                                $input.siblings(".error-message").text(message);
                                toastr.error(message, "Validation Error", {
                                    timeOut: 3000,
                                    closeButton: true,
                                    progressBar: true
                                });
                            });
                        } else {
                            toastr.error(response.message, "Error", {
                                timeOut: 3000,
                                closeButton: true,
                                progressBar: true
                            });
                        }
                    },
                    error: function () {
                        toastr.error("An error occurred. Please try again.", "Error", {
                            timeOut: 3000,
                            closeButton: true,
                            progressBar: true
                        });
                    },
                    complete: function () {
                        $loginBtn.prop("disabled", false);
                        $spinner.hide();
                    }
                });
            });
        });
    </script>
</body>
</html>
