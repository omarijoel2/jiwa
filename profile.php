<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">
    <h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">User Profile</li>
			</ol>
		</nav>
	</h1>
    <div class="card">
        <div class="card-body">
            <h4>User Details</h4>
            <form id="profile_form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="name" id="name" required>
                    <span class="error-message text-danger"></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                    <span class="error-message text-danger"></span>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" class="form-control" id="role" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <h4>Change Password</h4>
            <form id="password_form">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" class="form-control" name="current_password" required>
                    <span class="error-message text-danger"></span>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" class="form-control" name="new_password" required minlength="6">
                    <span class="error-message text-danger"></span>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                    <span class="error-message text-danger"></span>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>

</div>

    <script src="pages/assets/js/app.js"></script>
    <script>
        $(document).ready(function () {
            // Fetch profile data on page load
            $.ajax({
                type: "GET",
                url: "pages/utils/auth/get_profile.php",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        $("#name").val(response.data.name);
                        $("#email").val(response.data.email);
                        $("#role").val(response.data.role_name);
                    } else {
                        toastr.error(response.message, "Error");
                    }
                },
                error: function () {
                    toastr.error("Failed to fetch profile data.", "Error");
                }
            });

            // Update profile using AJAX
            $("#profile_form").submit(function (e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: "GET",
                    url: "pages/utils/auth/update_profile.php",
                    data: formData,
                    dataType: "json",
                    // beforeSend: function () {
                    //     toastr.info("Updating profile...", "Please Wait");
                    // },
                    success: function (response) {
                        if (response.status === "success") {
                            toastr.success(response.message, "Success", {
                                timeOut: 4500,
                                closeButton: true,
                                progressBar: true
                            });
                        } else {
                            toastr.error(response.message, "Error", {
                                    timeOut: 4500,
                                    closeButton: true,
                                    progressBar: true
                                });
                        }
                    },
                    error: function () {
                        toastr.error("An error occurred. Please try again.", "Error", {
                                timeOut: 4500,
                                closeButton: true,
                                progressBar: true
                            });
                    }
                });
            });

            $("#password_form").submit(function (e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: "GET",
                    url: "pages/utils/auth/update_password.php",
                    data: formData,
                    dataType: "json",
                    // beforeSend: function () {
                    //     toastr.info("Updating password...", "Please Wait");
                    // },
                    success: function (response) {
                        if (response.status === "success") {
                            toastr.success(response.message, "Success", {
                                    timeOut: 4500,
                                    closeButton: true,
                                    progressBar: true
                                });
                            $("#password_form")[0].reset();
                        } else {
                            toastr.error(response.message, "Validation Error", {
                                    timeOut: 4500,
                                    closeButton: true,
                                    progressBar: true
                                });
                        }
                    },
                    error: function () {
                        toastr.error("An error occurred. Please try again.", "Error", {
                            timeOut: 4500,
                            closeButton: true,
                            progressBar: true
                        });
                    }
                });
            });
        });
    </script>
<?php include 'pages/footer.php'; ?>
