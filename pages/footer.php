</main>

			<footer class="footer">
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-left">
							<p class="mb-0">
								&copy; <a href="index.html" class="text-muted">Winners Select</a>
							</p>
						</div>
						<div class="col-6 text-right">
							
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	
</body>

<script>
	$(document).ready(function () {

        $.ajax({
            type: "GET",
            url: "pages/utils/auth/get_profile.php",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#profile-username").text(response.data.name);
                } else {
                    $("#profile-username").text("Unknown User");
                }
            },
            error: function () {
                $("#profile-username").text("Error Loading");
            }
        });
    $("#logout_form").submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: "GET",
            url: "pages/utils/auth/logout.php",
            dataType: "json",
            // beforeSend: function () {
            //     toastr.info("Logging out...", "Please Wait", {
            //         timeOut: 2000,
            //         closeButton: true,
            //         progressBar: true
            //     });
            // },
            success: function (response) {
                if (response.status === "success") {
                    toastr.success("You have been logged out.", "Success", {
                        timeOut: 4500,
                        closeButton: true,
                        progressBar: true
                    });
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    toastr.error("Logout failed. Please try again.", "Error", {
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

</html>