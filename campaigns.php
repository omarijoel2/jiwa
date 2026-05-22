<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">

	<h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">Campaigns</li>
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

					/* Ensure dropdowns are not hidden */
					.dataTables_wrapper {
						overflow: visible !important;
					}

					/* Fix dropdowns inside tables */
					table.dataTable tbody tr {
						position: relative; /* Keeps dropdown within row */
					}

					/* Fix Bootstrap dropdowns inside DataTables */
					.dataTables_wrapper .dropdown-menu {
						z-index: 1050 !important; /* Ensure it's above other elements */
						position: absolute !important;
						will-change: transform;
					}

					/* Fix the dropdown to not cut off */
					.dataTables_wrapper .dropdown {
						position: relative !important;
					}

				</style>
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Campaigns</h5>
					<!-- <h6 class="card-subtitle text-muted">All Campaigns are listed here.</h6> -->
			 
					<div class="d-flex gap-2"> 
						<button type="button" class="btn btn-sm btn-primary ml-3" data-toggle="modal" data-target="#add_campaign">Add Campaign</button>
					</div>
					
				</div>

				<div class="collapse p-3 bg-light" id="customSearch">
					<div class="row">
						<div class="col-md-2">
							<label>Campaign Name</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="0">
						</div>
						<div class="col-md-2">
							<label>Shortcode / Paybill</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="1">
						</div>
						<div class="col-md-2">
							<label>Keyword</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="2">
						</div>
						<div class="col-md-2">
							<label>Min. Amount</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="3">
						</div>
						<div class="col-md-2">
							<label>Status</label>
							<select class="form-control form-control-sm column-search" data-column="4">
								<option value="">All</option>
								<option value="Active">Active</option>
								<option value="Inactive">Inactive</option>
							</select>
						</div>
					</div>
    			</div>

				<div class="card-body">
					<table id="datatables-campaigns" class="table table-striped" style="width:100%">
						<thead>
							<tr>
								<th>Campaign Name</th>
								<th>Shortcode</th>
								<th>Keyword</th>
								<th>Min. Amount</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						
						<tfoot>
							<tr>
								<th>Campaign Name</th>
								<th>Shortcode</th>
								<th>Keyword</th>
								<th>Min. Amount</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>

<!-- Add Campaign Modal -->
<div class="modal fade" id="add_campaign" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="campaign_form">
                <div class="modal-header">
                    <h5 class="modal-title">Add Campaign</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body m-3">

                    <!-- Campaign Name -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Campaign Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="campaign_name" name="campaign_name" placeholder="Name of campaign">
                            <span class="font-13 text-muted">e.g 494980-jiwa-radio_station_name</span>
                        </div>
                    </div>

                    <!-- Campaign Description -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Campaign Description</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" placeholder="Campaign Description" id="campaign_description" name="campaign_description" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Shortcode Dropdown -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Shortcode</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="campaign_shortcode" name="campaign_shortcode">
                                <option value="">Select Shortcode</option>
                            </select>
                            <span class="font-13 text-muted">Choose a shortcode from the list</span>
                        </div>
                    </div>

                    <!-- Keyword -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Keyword</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="campaign_keyword" name="campaign_keyword" placeholder="Keyword">
                            <span class="font-13 text-muted">e.g jamoko</span>
                        </div>
                    </div>

                    <!-- Minimum Amount -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Minimum Amount (Ksh)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="minimum_amount" name="minimum_amount" placeholder="Minimum Amount">
                            <span class="font-13 text-muted">The minimum amount for selecting a winner</span>
                        </div>
                    </div>

                    <!-- Enable Campaign -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enable_campaign" name="enable_campaign">
                        <label class="custom-control-label" for="enable_campaign">Activate Campaign</label>
                        <span class="font-13 text-muted"><br>If this switch is checked the campaign will be active</span>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

				
<!-- Edit Campaign Modal -->
<div class="modal fade" id="edit_campaign_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form id="edit_campaign_form">
				<div class="modal-header">
					<h5 class="modal-title">Edit Campaign</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
            	<div class="modal-body m-3">
                
                    <input type="hidden" id="edit_campaign_id" name="edit_campaign_id" >
                    
					<!-- campaign name -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Campaign Name</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="edit_campaign_name" name="edit_campaign_name" placeholder="Name of campaign" required>
						</div>
					 </div>
					
					<!-- shortcode -->

					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Shortcode / Paybill</label>
						<div class="col-sm-10">
							<select class="form-control" id="edit_campaign_shortcode" name="edit_campaign_shortcode" required>
								<!-- Options will be loaded dynamically -->
							</select>
						</div>
					 </div>
					


					<!-- keyword -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Keyword</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="edit_campaign_keyword" name="edit_campaign_keyword" placeholder="Keyword" required>
						</div>
					</div>
                    

					<!-- minimum amount -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Minimum Amount (ksh)</label>
						<div class="col-sm-10">
								<input type="number" class="form-control" id="edit_minimum_amount" name="edit_minimum_amount" placeholder="Minimum Amount" required>
						</div>
					</div>
                    

					<!-- campaign description -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Campaign Description</label>
						<div class="col-sm-10">
							<textarea class="form-control" placeholder="Campaign Description" id="edit_campaign_description" name="edit_campaign_description" rows="3"></textarea>
						</div>
					</div>
					

                    <!-- Enable Campaign Switch -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="edit_enable_campaign" name="enable_campaign">
                        <label class="custom-control-label" for="edit_enable_campaign">Activate Campaign</label>
                        <span class="font-13 text-muted"><br>If this switch is checked the campaign will be active</span>
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
<div class="modal fade" id="delete_campaign_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this campaign?</p>
                <input type="hidden" id="delete_campaign_id">
				<div id="delete_campaign_name"> </div>
				<div id="delete_campaign_shortcode"> </div>
				<div id="delete_campaign_account"> </div>
				<div id="delete_campaign_minimum_amount"></div>
				<div id="delete_campaign_status"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm_delete_campaign">Delete</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="pages/assets/js/app.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function(event) {
	$('#campaigns').addClass('active');


	function loadShortcodes() {
        $.ajax({
            url: "pages/utils/campaigns/fetch_shortcodes.php", // Backend script to fetch shortcodes
            type: "GET",
            dataType: "json",
            success: function (data) {
                $("#campaign_shortcode").empty().append('<option value="">Select Shortcode</option>');
                $.each(data, function (index, shortcode) {
                    $("#campaign_shortcode").append(`<option value="${shortcode.shortcode_id}">${shortcode.shortcode}</option>`);
                });
            },
            error: function () {
                console.error("Failed to load shortcodes.");
            }
        });
    }

    // Call the function when the modal opens
    $("#add_campaign").on("shown.bs.modal", function () {
        loadShortcodes();
    });

    // Form validation and submission
    $("#campaign_form").validate({
        focusInvalid: true,
        rules: {
            campaign_name: {
                required: true,
                minlength: 5
            },
            campaign_shortcode: {
                required: true
            },
            campaign_keyword: {
                required: true,
                minlength: 3
            },
            minimum_amount: {
                required: true,
                number: true,
                min: 10
            }
        },
        messages: {
            campaign_name: {
                required: "Please enter a campaign name.",
                minlength: "Campaign name must be at least 5 characters long."
            },
            campaign_shortcode: {
                required: "Please select a campaign shortcode."
            },
            campaign_keyword: {
                required: "Please enter a campaign keyword.",
                minlength: "Campaign keyword must be at least 3 characters long."
            },
            minimum_amount: {
                required: "Please enter a minimum amount.",
                number: "Minimum amount must be a valid number.",
                min: "Minimum amount must be greater than 10."
            }
        },
        errorElement: "span",
        errorClass: "text-danger",
        submitHandler: function (form) {
            let formData = $(form).serialize();

            $.ajax({
                type: "GET",
                url: "pages/utils/campaigns/add_campaign.php", // Backend handler
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        toastr.success(response.message, "Success");
                        $("#add_campaign").modal("hide");
                        $("#campaign_form")[0].reset();
                        $('#datatables-campaigns').DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(response.message, "Error");
                    }
                },
                error: function () {
                    toastr.error("An error occurred. Please try again.", "Error");
                }
            });
        }
    });


	$(document).on("click", ".update-status", function (e) {
    	e.preventDefault();

		let id = $(this).attr("data-id"); 
		let status = $(this).attr("data-status"); 

    
		$.ajax({
			type: "GET",
			url: "pages/utils/campaigns/update_campaign_status.php",
			data: { id: id, status: status }, 
			dataType: "json",
			// beforeSend: function () {
			//     toastr.info("Updating status...", "Please Wait", {
			//         timeOut: 1500,
			//         closeButton: true,
			//         progressBar: true
			//     });
			// },
			success: function (response) {

				if (response.status === "success") {
					toastr.success(response.message, "Success", {
						timeOut: 4500,
						closeButton: true,
						progressBar: true
					});

					$('#datatables-campaigns').DataTable().ajax.reload(null, false);
				} else {
					toastr.error(response.message, "Error", {
						timeOut: 4500,
						closeButton: true,
						progressBar: true
					});
				}
			},
			error: function (xhr) {
				toastr.error("An error occurred. Please try again.", "Error", {
					timeOut: 4500,
					closeButton: true,
					progressBar: true
				});
			}
		});
	});


	// Open Edit Modal and Load Data
	$(document).on("click", ".edit-campaign", function () {
		let campaignId = $(this).data("id");
		let campaignName = $(this).data("name");
		let campaignShortcode = $(this).data("shortcode");
		let campaignKeyword = $(this).data("account");
		let minimumAmount = $(this).data("minimum_amount");
		let campaignDescription = $(this).data("description");
		let isEnabled = $(this).data("enabled") == 1 ? true : false;

		$("#edit_campaign_id").val(campaignId);
		$("#edit_campaign_name").val(campaignName);
		$("#edit_campaign_keyword").val(campaignKeyword);
		$("#edit_minimum_amount").val(minimumAmount);
		$("#edit_campaign_description").val(campaignDescription);
		$("#edit_enable_campaign").prop("checked", isEnabled);

		// Load Shortcodes dynamically
		$.ajax({
			type: "GET",
			url: "pages/utils/campaigns/fetch_shortcodes.php", // Ensure this API returns a JSON list of shortcodes
			dataType: "json",
			success: function (response) {
				let $shortcodeSelect = $("#edit_campaign_shortcode");
				$shortcodeSelect.empty(); // Clear existing options
				response.forEach(function (item) {
					let selected = item.shortcode == campaignShortcode ? "selected" : "";
					$shortcodeSelect.append(`<option value="${item.shortcode_id}" ${selected}>${item.shortcode}</option>`);
				});
			}
		});

		$("#edit_campaign_modal").modal("show");
	});



    // Validate and Submit Edit Form
    $("#edit_campaign_form").on("submit", function (e) {
        e.preventDefault();

        let campaignName = $("#edit_campaign_name").val().trim();
        let campaignShortcode = $("#edit_campaign_shortcode").val().trim();
        let campaignKeyword = $("#edit_campaign_keyword").val().trim();
        let minimumAmount = $("#edit_minimum_amount").val().trim();
        let enableCampaign = $("#edit_enable_campaign").is(":checked") ? 1 : 0;
		let campaignDescription = $("#edit_campaign_description").val().trim();

        if (campaignName === "" || campaignShortcode === "" || campaignKeyword === "" || minimumAmount === "") {
            toastr.error("All fields are required.", "Validation Error");
            return;
        }

        if (isNaN(minimumAmount) || minimumAmount < 10) {
            toastr.error("Minimum Amount must be a valid number greater than or equal to 10.", "Validation Error");
            return;
        }

        let formData = {
            id: $("#edit_campaign_id").val(),
            campaign_name: campaignName,
            campaign_shortcode: campaignShortcode,
            campaign_keyword: campaignKeyword,
            minimum_amount: minimumAmount,
            enable_campaign: enableCampaign,
			campaign_description: campaignDescription,
        };


        $.ajax({
            type: "GET",
            url: "pages/utils/campaigns/update_campaign.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#edit_campaign_modal").modal("hide");
                    $('#datatables-campaigns').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            }
        });
    });


	// Open Delete Confirmation Modal
    $(document).on("click", ".delete-campaign", function () {
        let id = $(this).data("id");
        $("#delete_campaign_id").val(id);
		document.getElementById("delete_campaign_name").innerHTML = "Campaign Name: <b>"+$(this).data("name")+"</b>";
        document.getElementById("delete_campaign_shortcode").innerHTML = "Shortcode: <b>"+$(this).data("shortcode")+"</b>";
        document.getElementById("delete_campaign_account").innerHTML = "Keyword: <b>"+$(this).data("account")+"</b>";
        document.getElementById("delete_campaign_minimum_amount").innerHTML = "Minimum Amount: <b>"+$(this).data("minimum_amount")+"</b>";
        document.getElementById("delete_campaign_status").innerHTML = "Campaign Status: <b style='color:"+($(this).data("enabled") == 1 ? 'green' : 'red')+"'>"+($(this).data("enabled") == 1 ? 'Enabled' : 'Disabled')+"</b>";

        $("#delete_campaign_modal").modal("show");
    });

    // Confirm Delete
    $("#confirm_delete_campaign").on("click", function () {
        let id = $("#delete_campaign_id").val();

        $.ajax({
            type: "GET",
            url: "pages/utils/campaigns/delete_campaign.php",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#delete_campaign_modal").modal("hide");
                    $('#datatables-campaigns').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            }
        });
    });

	let table = $("#datatables-campaigns").DataTable({
        processing: true,
        serverSide: true,
		scrollY: "400px", 
        scrollX: true, 
		autoWidth: false, // Prevent automatic width adjustment
        responsive: true, // Make it responsive
        columnDefs: [
            { width: "250px", targets: 0 }, // Campaign Name
            { width: "150px", targets: 1 }, // Shortcode
            { width: "200px", targets: 2 }, // Keyword
            { width: "120px", targets: 3 }, // Min. Amount
            { width: "100px", targets: 4 }, // Status
            { width: "150px", targets: 5 }  // Action
        ],
        ajax: {
            url: "pages/utils/campaigns/fetch_campaigns.php",
            type: "GET",
            data: function (d) {
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    d["column_" + columnIndex] = $(this).val(); // Send column search values
                });
            }
        },
        columns: [
            { title: "Campaign Name", data: 0 },
            { title: "Shortcode", data: 1 },
            { title: "Keyword", data: 2 },
            { title: "Min. Amount", data: 3 },
            { title: "Status", data: 4 },
            { title: "Action", data: 5 }
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
                window.location.href = "pages/utils/campaigns/export_campaigns.php?" + queryParams;
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