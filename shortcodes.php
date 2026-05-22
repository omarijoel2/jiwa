<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">

	<h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">Shortcodes</li>
			</ol>
		</nav>
	</h1>

	<div class="row">
		<div class="col-12">
			<div class="card">
				<style>
					.custom-search {
						display: flex;
						justify-content: flex-end;
						align-items: center;
					}

					.custom-search button {
						margin-left: 10px;
					}

				</style>
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Shortcodes</h5>
			 
					<div class="d-flex gap-2"> 
						<button type="button" class="btn btn-sm btn-primary ml-3" data-toggle="modal" data-target="#add_shortcode">Add Shortcode</button>
					</div>
					
				</div>

				<div class="collapse p-3 bg-light" id="customSearch">
					<div class="row">
						<div class="col-md-2">
							<label>Shortcode / Paybill</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="1">
						</div>

                        <div class="col-md-2">
							<label>Shortcode ID</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="0">
						</div>
					</div>
    			</div>

				<div class="card-body">
					<table id="datatables-shortcodes" class="table table-striped" style="width:100%">
						<thead>
							<tr>
                                <th>Shortcode ID</th>
								<th>Shortcode / Paybill</th>
								<th>Action</th>
							</tr>
						</thead>
						
						<tfoot>
							<tr>
								<th>Shortcode ID</th>
								<th>Shortcode / Paybill</th>
								<th>Action</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>

<!-- add shortcode modal -->
<div class="modal fade" id="add_shortcode" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="shortcode_form">
				<div class="modal-header">
					<h5 class="modal-title">Add Shortcode</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body m-3">
				
					<!-- shortcode /paybill -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Shortcode / Paybill</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="d_shortcode" name="d_shortcode" placeholder="Paybill">
								<span class="font-13 text-muted">e.g 494980</span>
						</div>
					</div>

					<!-- shortcode ID -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Shortcode / Paybill ID </label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="d_id" name="d_id" placeholder="Paybill ID">
							<span class="font-13 text-muted">The Shortcode/ paybill ID stored in mpesa transactions table</span>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<!-- submit -->
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			</form>

		</div>
	</div>
</div>
				
<!-- Edit Shortcode Modal -->
<div class="modal fade" id="edit_shortcode_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form id="edit_shortcode_form">
				<div class="modal-header">
					<h5 class="modal-title">Edit Shortcode</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
            	<div class="modal-body m-3">
                
                    <input type="hidden" id="edit_shortcode_id" name="edit_shortcode_id" >

					<!-- shortcode -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Shortcode / Paybill</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="edit_d_shortcode" name="edit_d_shortcode" placeholder="Paybill" required>
                        </div>
                    </div>

					<!-- Shortcode ID -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Shortcode / Paybill ID</label>
						<div class="col-sm-10">
                            <input type="number" class="form-control" id="edit_d_id" name="edit_d_id" placeholder="Paybill ID" required>
                            <span class="font-13 text-muted">The Shortcode/ paybill ID stored in mpesa transactions table</span>
						</div>
					</div>

            	</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Update</button>
				</div>
			</form>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_shortcode_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this shortcode?</p>
                <p><span class="text-danger "> <b>NOTE: </b>Deleting this shortcode will also delete <b>registered campaigns</b> and the <b>winning conditions</b> under it.</span></p>
                <input type="hidden" id="delete_shortcode_id">
				<div id="delete_shortcode"> </div>
				<div id="delete_id"> </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm_delete_shortcode">Delete</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="pages/assets/js/app.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function(event) {
	$('#shortcodes_nav').addClass('active');


	$("#shortcode_form").validate({
		focusInvalid: true,
		rules: {
			
			d_shortcode: {
				required: true,
				number: true
			},
			d_id: {
				required: true,
				number: true
			}
		},
		messages: {
			d_shortcode: {
				required: "Please enter a valid shortcode / paybill value.",
				number: "The shortcode / Paybill must be a valid number."
			},
			
			d_id: {
				required: "Please enter the shortcode / paybill ID stored in the mpesa system.",
				number: "Shortcode ID must be a valid number."
			}
		},
		errorElement: "span",
		errorClass: "text-danger", 
		errorPlacement: function (error, element) {
			error.appendTo(element.closest(".col-sm-10")); 
		},
		highlight: function (element) {
			$(element).addClass("is-invalid");
		},
		unhighlight: function (element) {
			$(element).removeClass("is-invalid");
		},
		submitHandler: function (form) {
			let formData = $(form).serialize();
			console.log(formData);

			$.ajax({
				type: "GET",
				url: "pages/utils/shortcodes/add_shortcode.php", // Backend handler
				data: formData,
				dataType: "json",
				// beforeSend: function () {
				// 	toastr.info("Submitting campaign...", "Please Wait", {
				// 		timeOut: 3000,
				// 		closeButton: true,
				// 		progressBar: true
				// 	});
				// },
				success: function (response) {
					if (response.status === "success") {
						toastr.success(response.message, "Success", {
							timeOut: 3000,
							closeButton: true,
							progressBar: true
						});

						$("#add_shortcode").modal("hide");
						$("#shortcode_form")[0].reset();
						$('#datatables-shortcodes').DataTable().ajax.reload(null, false);
						
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
				}
			});
		}
	});



	// Open Edit Modal and Load Data
	$(document).on("click", ".edit-shortcode", function () {
        $("#edit_shortcode_id").val($(this).data("id"));
        $("#edit_d_shortcode").val($(this).data("shortcode"));
        $("#edit_d_id").val($(this).data("shortcode_id"));
       
        $("#edit_campaign_modal").modal("show");
    });

    // Validate and Submit Edit Form
    $("#edit_shortcode_form").on("submit", function (e) {
        e.preventDefault();

        let shortcode = $("#edit_d_shortcode").val().trim();
        let shortcode_id = $("#edit_d_id").val().trim();
        

        if (shortcode === "" || shortcode_id === "" ) {
            toastr.error("All fields are required.", "Validation Error");
            return;
        }

        if (isNaN(shortcode_id) || isNaN(shortcode)) {
            toastr.error("All fields must have a valid number.", "Validation Error");
            return;
        }

        let formData = {
            id: $("#edit_shortcode_id").val(),
            shortcode: shortcode,
            shortcode_id: shortcode_id
        };


        $.ajax({
            type: "GET",
            url: "pages/utils/shortcodes/update_shortcode.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#edit_shortcode_modal").modal("hide");
                    $('#datatables-shortcodes').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            }
        });
    });


	// Open Delete Confirmation Modal
    $(document).on("click", ".delete-shortcode", function () {
        let id = $(this).data("id");
        $("#delete_shortcode_id").val(id);
		document.getElementById("delete_shortcode").innerHTML = "Shortcode: <b>"+$(this).data("shortcode")+"</b>";
        document.getElementById("delete_id").innerHTML = "Shortcode ID: <b>"+$(this).data("shortcode_id")+"</b>";

        $("#delete_shortcode_modal").modal("show");
    });

    // Confirm Delete
    $("#confirm_delete_shortcode").on("click", function () {
        let id = $("#delete_shortcode_id").val();

        $.ajax({
            type: "GET",
            url: "pages/utils/shortcodes/delete_shortcode.php",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#delete_shortcode_modal").modal("hide");
                    $('#datatables-shortcodes').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            }
        });
    });

	let table = $("#datatables-shortcodes").DataTable({
        processing: true,
        serverSide: true,
		scrollY: "400px", 
        scrollX: true, 
		autoWidth: true, // Prevent automatic width adjustment
        responsive: true, // Make it responsive
        ajax: {
            url: "pages/utils/shortcodes/fetch_shortcodes.php",
            type: "GET",
            data: function (d) {
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    d["column_" + columnIndex] = $(this).val(); // Send column search values
                });
            }
        },
        columns: [
            { title: "Shortcode ID", data: 0 },
            { title: "Shortcode / Paybill", data: 1 },
            { title: "Action", data: 2 }
        ],
        paging: true,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, "desc"]],
        responsive: true,
		dom: `<"row"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center custom-search">>` + 
             `<"row"<"col-md-12"p>>` + // Pagination at the top
             `<"row"<"col-12"tr>>` +
             `<"row"<"col-md-5"i><"col-md-7"p>>`, // Pagination at the bottom
        initComplete: function () {
			
            // Move custom search button where the original search box was
            $(".custom-search").append(`
			<button id="export_excel" class="btn btn-sm btn-md btn-success">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#customSearch" aria-expanded="false" aria-controls="customSearch">
                    <i class="fas fa-search"></i> Search
                </button>
            `);

			// Attach event to dynamically created button
            $(document).on("click", "#export_excel", function () {
                let exportData = {};
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    exportData["column_" + columnIndex] = $(this).val(); // Pass filters to backend
                });

                let queryParams = new URLSearchParams(exportData).toString();
                window.location.href = "pages/utils/shortcodes/export_shortcodes.php?" + queryParams;
            });

            // Apply search filter
            $(".column-search").on("keyup change", function () {
                table.ajax.reload();
            });
        },
		drawCallback: function () {
            table.columns.adjust().responsive.recalc(); // Ensure columns adjust properly
        }
    });	

	// Detect sidebar toggle
    $(".sidebar-toggle").click(function () {
        setTimeout(function () {
            table.columns.adjust().responsive.recalc(); // Adjust columns after sidebar animation
        }, 300); // Small delay to allow animation
    });

    // Adjust on window resize (for mobile responsiveness)
    $(window).on("resize", function () {
        setTimeout(function () {
            table.columns.adjust().responsive.recalc();
        }, 300);
    });


});

</script>
<?php include 'pages/footer.php'; ?>