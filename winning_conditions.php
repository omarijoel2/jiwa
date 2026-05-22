<?php
require 'pages/utils/auth/auth_check.php';
$cronjob_id = $_GET['_id'] ?? null;
if (!$cronjob_id) {
    die("Invalid request. No campaign selected.");
}
?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">
    <h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Home</a></li>
                <li class="breadcrumb-item"><a href="campaigns">Campaigns</a></li>
                <li class="breadcrumb-item active"><span id="campaign_name1">Loading...</span></li>
                <li class="breadcrumb-item active">Winning Conditions</li>
			</ol>
		</nav>
	</h1>


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
            <h5 class="card-title mb-0">Winning Conditions for Campaign: <span id="campaign_name2" class="text-primary">Loading...</span></h5>
            
            <div class="d-flex gap-2"> 
                <a href="campaigns" class="btn btn-sm btn-outline-primary">Back to Campaigns</a>
                <button type="button" class="btn btn-sm btn-primary ml-3" data-toggle="modal" data-target="#add_condition">Add Condition</button>
            </div>
        </div>
        <div class="collapse p-3 bg-light" id="customSearch">
            <div class="row g-3 mt-3 align-items-end">
                <div class="col-md-2">
                    <label>Condition Name</label>
                    <input type="text" class="form-control form-control-sm column-search" data-column="0">
                </div>

                <div class="col-md-2">
                    <label>Minimum Amount (Ksh)</label>
                    <input type="number" class="form-control form-control-sm column-search" data-column="1">
                </div>

                <div class="col-md-2">
                    <label>Maximum Amount (Ksh)</label>
                    <input type="number" class="form-control form-control-sm column-search" data-column="2">
                </div>

                <div class="col-md-2">
                    <label>Winnings (Ksh)</label>
                    <input type="number" class="form-control form-control-sm column-search" data-column="3">
                </div>

                <div class="col-md-2">
                    <label>Winning Percentage %</label>
                    <input type="number" class="form-control form-control-sm column-search" data-column="4">
                </div>

            </div>

            <div class="row g-3 mt-3 align-items-end">
                <div class="col-md-3">
                    <label>Reset After Every (n Transactions)</label>
                    <input type="number" class="form-control form-control-sm column-search" data-column="5">
                </div>

                <div class="col-md-2">
                    <label>Status</label>
                    <select class="form-control form-control-sm column-search" data-column="6">
                        <option value="">All</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="datatables-winning-conditions" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Condition Name</th>
                        <th>Min Amount</th>
                        <th>Max Amount</th>
                        <th>Winnings</th>
                        <th>Winning %</th>
                        <th>Reset After Every (n Transactions)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th>Condition Name</th>
                        <th>Min Amount</th>
                        <th>Max Amount</th>
                        <th>Winnings</th>
                        <th>Winning %</th>
                        <th>Reset After Every (n Transactions)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- add condition modal -->
<div class="modal fade" id="add_condition" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="add_condition_form">
                <div class="modal-header">
                    <h5 class="modal-title">Add Winning Condition</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body m-3">

                    <!-- condition name -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Condition Name</label>
                        <div class="col-sm-10">
                                <input type="text" class="form-control" id="condition_name" name="condition_name" placeholder="Name of winning condition">
                                <!-- <span class="font-13 text-muted">e.g jiwa-50_100</span> -->
                        </div>
                    </div>

                    <!-- condition description -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Condtion Description</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" placeholder="Condtion Description" id="condtion_description" name="condtion_description" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- minimum amount -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Minimum Amount (Ksh)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="minimum_amount" name="minimum_amount" placeholder="Minimum Amount">
                            <span class="font-13 text-muted">The minimum amount for selecting a winner</span>
                        </div>
                    </div>

                    <!-- maximum amount -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Maximum Amount (Ksh)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="maximum_amount" name="maximum_amount" placeholder="Maximum Amount">
                            <span class="font-13 text-muted">The maximum amount for selecting a winner</span>
                        </div>
                    </div>

                    <!-- winnings amount -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Winnings (Ksh)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="winnings" name="winnings" placeholder="Winnings Amount">
                            <span class="font-13 text-muted">How much the winner will get</span>
                        </div>
                    </div>

                    <!-- winning percentage -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Winning Percentage (%)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="winning_percentage" name="winning_percentage" placeholder="Winning Percentage" default="40">
                            <span class="font-13 text-muted">The percentage of the winnings. Eg. If you set this to 40 and <b>Reset Every</b>(below) is set to 10: this means that for every 10 people who transaction and meet this condition, 4 people will get the winnings</span>
                        </div>
                    </div>

                    <!-- reset every -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Reset After Every n Transactions</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="reset_every" name="reset_every" placeholder="Reset After Every" default="40">
                            <span class="font-13 text-muted">Reset the winning condition after every n transactions</span>
                        </div>
                    </div>

                    <!-- enable condition -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enable_condition" name="enable_condition">
                        <label class="custom-control-label" for="enable_condition">Activate Condition</label>
                        <span class="font-13 text-muted"><br>If this switch is checked the condition will be active</span>
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

<!-- Edit Condition Modal -->
<div class="modal fade" id="edit_condition_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form id="edit_condition_form">
				<div class="modal-header">
					<h5 class="modal-title">Edit Condtion</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
            	<div class="modal-body m-3">
                
                    <input type="hidden" id="edit_condition_id" name="edit_condition_id" >
                    
					<!-- condition name -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Condition Name</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="edit_condition_name" name="edit_condition_name" placeholder="Name of condition" required>
						</div>
					 </div>
					
					<!-- condition description -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Condtion Description</label>
						<div class="col-sm-10">
                            <textarea class="form-control" placeholder="Condtion Description" id="edit_condtion_description" name="edit_condtion_description" rows="3"></textarea>
						</div>
					 </div>
                    

					<!-- minimum amount -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Minimum Amount (Ksh)</label>
						<div class="col-sm-10">
								<input type="number" class="form-control" id="edit_minimum_amount" name="edit_minimum_amount" placeholder="Minimum Amount" required>
                                <span class="font-13 text-muted">The minimum amount for selecting a winner</span>
						</div>
					</div>
                    

					<!-- maximum amount -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Maximum Amount (Ksh)</label>
						<div class="col-sm-10">
								<input type="number" class="form-control" id="edit_maximum_amount" name="edit_maximum_amount" placeholder="Maximum Amount" required>
						</div>
					</div>
                    

					<!-- winnings amount -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Winnings (Ksh)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="edit_condition_winnings" name="edit_condition_winnings" placeholder="Winnings Amount" required>
                            <span class="font-13 text-muted">How much the winner will get</span>
                        </div>
                    </div>

                    <!-- winning percentage -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Winning Percentage (%)</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="edit_winning_percentage" name="edit_winning_percentage" placeholder="Winning Percentage" required>
                            <span class="font-13 text-muted">The percentage of the winnings. Eg. If you set this to 40 and <b>Reset After Every</b>(below) is set to 10: this means that for every 10 people who transact and meet this condition, 4 people will get the winnings</span>
                        </div>
                    </div>
					
                    <!-- reset every -->
                    <div class="form-group row">
                        <label class="col-form-label col-sm-2 text-sm-right">Reset After Every n Transactions</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="edit_reset_every" name="edit_reset_every" placeholder="Reset After Every" required>
                            <span class="font-13 text-muted">Reset the winning condition after every n transactions</span>
                        </div>
                    </div>

                    <!-- enable condition -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="edit_enable_condition" name="edit_enable_condition">
                        <label class="custom-control-label" for="edit_enable_condition">Activate Condtion</label>
                        <span class="font-13 text-muted"><br>If this switch is checked the condition will be active</span>
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

<!-- condition message -->
<div class="modal fade" id="condition_message_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<form id="message_condition_form">
				<div class="modal-header">
					<h5 class="modal-title">Winning / Losing Condition Message(s)</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
            	<div class="modal-body m-3">
                
                    <input type="hidden" id="message_condition_id" name="message_condition_id" >
                    
					<!-- condition name -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Condition Name</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="message_condition_name" name="message_condition_name" readonly>
						</div>
					 </div>
					
					<!-- Winning Message -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Winning Message</label>
						<div class="col-sm-10">
                            <textarea class="form-control" placeholder="Winning Message" id="message_winning" name="message_winning" rows="3"></textarea>
                            <span class="font-13 text-muted"><b>Message Variables: </b>{username}, {sent_amount}, {winning_amount}, {keyword}, {paybill} </span>
						</div>
					 </div>

                    <!-- Losing Message -->
					 <div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Losing Message</label>
						<div class="col-sm-10">
                            <textarea class="form-control" placeholder="Losing Message" id="message_losing" name="message_losing" rows="3"></textarea>
                            <span class="font-13 text-muted"><b>Message Variables: </b>{username}, {sent_amount}, {winning_amount}, {keyword}, {paybill}</span>
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
<div class="modal fade" id="delete_condition_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this condition?</p>
                <input type="hidden" id="delete_condition_id">
				<div id="delete_condition_name"> </div>
				<div id="delete_condition_minimum_amount"></div>
				<div id="delete_condition_maximum_amount"> </div>
				<div id="delete_condition_winnings"> </div>
				<div id="delete_condition_winning_percentage"> </div>
				<div id="delete_condition_reset_every"> </div>
				<div id="delete_condition_status"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm_delete_condition">Delete</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script src="pages/assets/js/app.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
    $('#campaigns').addClass('active');

    $("#add_condition_form").validate({
        focusInvalid: true,
        rules: {
            condition_name: {
                required: true,
                minlength: 5
            },
            minimum_amount: {
                required: true,
                number: true,
                min: 1 
            },
            maximum_amount: {
                required: true,
                number: true,
                min: function () {
                    return parseFloat($("#minimum_amount").val()) + 1; // Ensure greater than min
                }
            },
            winnings: {
                required: true,
                number: true,
                min: 10
            },
            winning_percentage: {
                required: true,
                number: true,
                min: 1
            },
            reset_every: {
                required: true,
                number: true,
                min: 1
            }
        },
        messages: {
            condition_name: {
                required: "Please enter a condition name.",
                minlength: "Condition name must be at least 5 characters long."
            },
            minimum_amount: {
                required: "Please enter a minimum amount.",
                number: "Minimum amount must be a valid number."
            },
            maximum_amount: {
                required: "Please enter a maximum amount.",
                number: "Maximum amount must be a valid number.",
                min: "Maximum amount must be greater than the minimum amount."
            },
            winnings: {
                required: "Please enter a winnings amount.",
                number: "Winnings amount must be a valid number.",
                min: "Winnings amount must be greater than 10/=."
            },
            winning_percentage: {
                required: "Please enter a winning percentage without %.",
                number: "Winning percentage must be a valid number without %.",
                min: "Winning percentage must be greater than 1."
            },
            reset_every: {
                required: "Please enter a value for reset after every n transactions.",
                number: "Reset Every must be a valid number.",
                min: "Reset Every must be greater than 1."
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
            let _id = new URLSearchParams(window.location.search).get("_id"); // Get _id from URL

            formData += `&cronjob_id=${encodeURIComponent(_id)}`;
            console.log(formData);

            $.ajax({
                type: "GET",
                url: "pages/utils/winning_conditions/add_winning_condition.php", // Backend handler
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

                        $("#add_condition").modal("hide");
                        $("#add_condition_form")[0].reset();
                        $('#datatables-winning-conditions').DataTable().ajax.reload(null, false);
                        
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
        }
    });

    $(document).on("click", ".update-status", function (e) {
    	e.preventDefault();

		let id = $(this).attr("data-id"); 
		let status = $(this).attr("data-status"); 

    
		$.ajax({
			type: "GET",
			url: "pages/utils/winning_conditions/update_condition_status.php",
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

					$('#datatables-winning-conditions').DataTable().ajax.reload(null, false);
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

    $(document).on("click", ".condition-message", function () {
        $("#message_condition_id").val($(this).data("id"));
        $("#message_condition_name").val($(this).data("name"));
        $("#message_winning").val($(this).data("winning_message"));
        $("#message_losing").val($(this).data("losing_message"));
    });

    // message form
    $("#message_condition_form").on("submit", function (e) {
        e.preventDefault();

		let winningMessage = $("#message_winning").val().trim();
		let losingMessage = $("#message_losing").val().trim();
        

        if (winningMessage === "" || losingMessage === "" ) {
            toastr.error("All fields are required.", "Validation Error");
            return;
        }

        
        let _id = new URLSearchParams(window.location.search).get("_id"); // Get _id from URL
        let formData = {
            cronjob_id: _id,
            id: $("#message_condition_id").val(),
            winning_message: winningMessage,
            losing_message: losingMessage
        };

        console.log(formData);

        $.ajax({
            type: "GET",
            url: "pages/utils/winning_conditions/update_condition_messages.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#condition_message_modal").modal("hide");
                    $('#datatables-winning-conditions').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            },
            error: function (xhr) {
                toastr.error("An error occurred. Please try again.", "Error");
            }});
       

        
    });

    
    // Open Edit Modal and Load Data
	$(document).on("click", ".edit-condition", function () {
        $("#edit_condition_id").val($(this).data("id"));
        $("#edit_condition_name").val($(this).data("name"));
        $("#edit_minimum_amount").val($(this).data("amount-min"));
        $("#edit_maximum_amount").val($(this).data("amount-max"));
        $("#edit_condition_winnings").val($(this).data("winnings"));
        $("#edit_winning_percentage").val($(this).data("winning-percentage"));
        $("#edit_reset_every").val($(this).data("reset-every"));
		$("#edit_condtion_description").val($(this).data("description"));


        let isEnabled = $(this).data("enabled") == 1 ? true : false;
        $("#edit_enable_condition").prop("checked", isEnabled);

        $("#edit_condition_modal").modal("show");
    });

    // Validate and Submit Edit Form
    $("#edit_condition_form").on("submit", function (e) {
        e.preventDefault();

        let conditionName = $("#edit_condition_name").val().trim();
		let conditionDescription = $("#edit_condtion_description").val().trim();
        let minimumAmount = parseFloat($("#edit_minimum_amount").val().trim());
        let maximumAmount = parseFloat($("#edit_maximum_amount").val().trim())
        let winnings = $("#edit_condition_winnings").val().trim();
        let winningPercentage = $("#edit_winning_percentage").val().trim();
        let resetEvery = $("#edit_reset_every").val().trim();
        let enableCondition = $("#edit_enable_condition").is(":checked") ? 1 : 0;

        if (conditionName === "" || minimumAmount === "" || maximumAmount === "" || winnings === "" || winningPercentage === "" || resetEvery === "") {
            toastr.error("All fields are required.", "Validation Error");
            return;
        }

        if (isNaN(minimumAmount) || minimumAmount <= 0) {
            toastr.error("Minimum Amount must be a valid number 0.", "Validation Error");
            return;
        }

        if (isNaN(maximumAmount) || maximumAmount < minimumAmount) {
            toastr.error("Maximum Amount must be a valid number greater than the minimum amount.", "Validation Error");
            return;
        }

        if (isNaN(winnings) || winnings <= 0) {
            toastr.error("Winnings must be a valid number greater than 0.", "Validation Error");
            return;
        }

        if (isNaN(winningPercentage) || winningPercentage <= 0) {
            toastr.error("Winning Percentage must be a valid number greater than 0.", "Validation Error");
            return;
        }

        if (isNaN(resetEvery) || resetEvery <= 0) {  
            toastr.error("Reset Every must be a valid number greater than 0.", "Validation Error");
            return;
        }

        let _id = new URLSearchParams(window.location.search).get("_id"); // Get _id from URL
        let formData = {
            cronjob_id: _id,
            id: $("#edit_condition_id").val(),
            condition_name: conditionName,
            minimum_amount: minimumAmount,
            maximum_amount: maximumAmount,
            winnings: winnings,
            winning_percentage: winningPercentage,
            reset_every: resetEvery,
            enable_condition: enableCondition,
            description: conditionDescription,
        };

        console.log(formData);

        $.ajax({
            type: "GET",
            url: "pages/utils/winning_conditions/update_condition.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#edit_condition_modal").modal("hide");
                    $('#datatables-winning-conditions').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            },
            error: function (xhr) {
                toastr.error("An error occurred. Please try again.", "Error");
            }});
       

        
    });

    // Open Delete Confirmation Modal
    $(document).on("click", ".delete-condition", function () {
        let id = $(this).data("id");
        $("#delete_condition_id").val(id);
		document.getElementById("delete_condition_name").innerHTML = "Condition Name: <b>"+$(this).data("name")+"</b>";
        document.getElementById("delete_condition_minimum_amount").innerHTML = "Minimum Amount: <b>"+$(this).data("amount-min")+" /=</b>";
        document.getElementById("delete_condition_maximum_amount").innerHTML = "Maximum Amount: <b>"+$(this).data("amount-max")+" /=</b>";
        document.getElementById("delete_condition_winnings").innerHTML = "Winnings: <b>"+$(this).data("winnings")+" /=</b>";
        document.getElementById("delete_condition_winning_percentage").innerHTML = "Winning Percentage: <b>"+$(this).data("winning-percentage")+" %</b>";
        document.getElementById("delete_condition_reset_every").innerHTML = "Reset After Every: <b>"+$(this).data("reset-every")+" Transactions</b>";
        document.getElementById("delete_condition_status").innerHTML = "Condition Status: <b style='color:"+($(this).data("enabled") == 1 ? 'green' : 'red')+"'>"+($(this).data("enabled") == 1 ? 'Enabled' : 'Disabled')+"</b>";

        $("#delete_condition_modal").modal("show");
    });

    // Confirm Delete
    $("#confirm_delete_condition").on("click", function () {
        let id = $("#delete_condition_id").val();

        $.ajax({
            type: "GET",
            url: "pages/utils/winning_conditions/delete_condition.php",
            data: { id: id },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    toastr.success(response.message, "Success");
                    $("#delete_condition_modal").modal("hide");
                    $('#datatables-winning-conditions').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message, "Error");
                }
            }
        });
    });

    
    
    let _id = new URLSearchParams(window.location.search).get("_id"); // Get campaign ID

    let table = $("#datatables-winning-conditions").DataTable({
        processing: true,
        serverSide: true,
        scrollY: "400px",
        scrollX: true,
        paging: true,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, "desc"]],
        autoWidth: true,
        responsive: true, 
        dom: `<"row"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center custom-search">>` + 
             `<"row"<"col-md-12"p>>` + // Pagination at the top
             `<"row"<"col-12"tr>>` +
             `<"row"<"col-md-5"i><"col-md-7"p>>`, // Pagination at the bottom
        ajax: {
            url: "pages/utils/winning_conditions/fetch_winning_conditions.php",
            type: "GET",
            data: function (d) {
                d["cronjob_id"] = _id; // Pass campaign ID
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    d["column_" + columnIndex] = $(this).val(); // Pass column search values
                });
            },
            dataSrc: function (json) {
                $("#campaign_name1, #campaign_name2").text(json.campaign_name); // Set campaign name
                return json.data;
            }
        },
        columns: [
            { title: "Condition Name", data: 0 },
            { title: "Min Amount", data: 1, render: $.fn.dataTable.render.number(',', '.', 2) }, // Format as currency
            { title: "Max Amount", data: 2, render: $.fn.dataTable.render.number(',', '.', 2) },
            { title: "Winnings", data: 3, render: $.fn.dataTable.render.number(',', '.', 2) },
            { title: "Winning %", data: 4 },
            { title: "Reset After Every (n Transactions)", data: 5 },
            { title: "Status", data: 6 },
            { title: "Action", data: 7 }
        ],
        initComplete: function () {
            $(".custom-search").append(`
                <button id="export_excel" class="btn btn-sm btn-md btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#customSearch">
                    <i class="fas fa-search"></i> Search
                </button>
            `);

            $(document).on("click", "#export_excel", function () {
                let exportData = { cronjob_id: _id };
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    exportData["column_" + columnIndex] = $(this).val();
                });

                let queryParams = new URLSearchParams(exportData).toString();
                window.location.href = "pages/utils/winning_conditions/export_conditions.php?" + queryParams;
            });

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
