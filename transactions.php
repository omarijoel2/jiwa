<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">

	<h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">B2C Transactions</li>
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

                    table.dataTable td {
                        white-space: nowrap;
                    }

                </style>
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Today's B2C Transactions</h5>
				</div>

				<div class="collapse p-3 bg-light" id="customSearch">
					<div class="row">
                        <!-- TODO: UPDATE INPUT SEARCH FIELDS -->
                        
                        <div class="col-md-2">
							<label>Shortcode</label>
							<select id="shortcodeFilter" class="form-control form-control-sm column-search" data-column="1">
								<option value="">Loading...</option>
							</select>
						</div>

                        <div class="col-md-2">
                            <label>Keyword</label>
                            <input type="text" class="form-control form-control-sm column-search" type="text" data-column="2">
                        </div>

                        <div class="col-md-3">
                            <label>Transaction Date</label>
                            <input class="form-control form-control-sm column-search" type="text" name="datetimes" data-column="3" />
                        </div>
						
					</div>
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <label>Customer Name</label>
                            <input type="text" class="form-control form-control-sm column-search" type="text" data-column="4">
                        </div>

                        <div class="col-md-2">
                            <label>Telephone No</label>
                            <input type="text" class="form-control form-control-sm column-search" type="text" data-column="5">
                        </div>
                        <div class="col-md-2">
                            <label>Transaction ID</label>
                            <input type="text" class="form-control form-control-sm column-search" type="text" data-column="6">
                        </div>
                    </div>
    			</div>

				<div class="card-body">
					<table id="datatables-transactions" class="table table-striped" style="width:100%">
						<thead>
							<tr>
								<th>Shortcode</th>
                                <th>Keyword</th>
                                <th>Transaction ID</th>
                                <th>Amount (ksh)</th>
								<th>Customer Name</th>
								<th>Telephone No</th>
                                <th>Time</th>
                                <th>Action</th>
							</tr>
						</thead>

                        <tfoot>
                            <tr>
                                <th>Shortcode</th>
                                <th>Keyword</th>
                                <th>Transaction ID</th>
                                <th>Amount (ksh)</th>
								<th>Customer Name</th>
								<th>Telephone No</th>
                                <th>Time</th>
                                <th>Action</th>
							</tr>
                        </tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>
<!-- view transactions modal -->
<div class="modal fade" id="view_transaction_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			<!-- <form id="edit_campaign_form"> -->
				<div class="modal-header">
					<h5 class="modal-title">Transaction Details</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
            	<div class="modal-body m-3">
					
					<!-- shortcode -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Shortcode</label>
						<div class="col-sm-10">
                            <input type="text" class="form-control" id="shortcode" name="shortcode"  readonly>
						</div>
					 </div>

					<!-- keyword -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Keyword</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="keyword" name="keyword" readonly>
						</div>
					</div>

                    <!-- transaction code -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Transaction ID</label>
						<div class="col-sm-10">
								<input type="text" class="form-control" id="transaction_code" name="transaction_code" readonly>
						</div>
                    </div>
                    

					<!-- amount won -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Amount Won (ksh)</label>
						<div class="col-sm-10">
								<input type="number" class="form-control" id="amount_won" name="amount_won" readonly>
						</div>
					</div>
                    

					<!-- customer name -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Customer Name</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="customer_name" name="customer_name" readonly>
						</div>
					</div>

                    <!-- phone number -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Telephone No</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="msisdn" name="msisdn" readonly>
						</div>
					</div>

                    <!-- Transaction Time -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Transaction Time</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="trans_time" name="trans_time" readonly>
						</div>
					</div>

                    <!-- Amount User Sent -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Amount User Sent (Ksh)</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="amount_user_transacted" name="amount_user_transacted" readonly>
						</div>
					</div>

                    <!-- Amount User Sent (Transaction ID) -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Amount User Sent (Transaction ID)</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="amount_transacted_code" name="amount_transacted_code" readonly>
						</div>
					</div>

                     <!-- Amount User Sent (Time) -->
					<div class="form-group row">
						<label class="col-form-label col-sm-2 text-sm-right">Amount User Sent (Time)</label>
						<div class="col-sm-10">
                        <input type="text" class="form-control" id="amount_transacted_time" name="amount_transacted_time" readonly>
						</div>
					</div>

            	</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			<!-- </form> -->
        </div>
    </div>
</div>

<script src="pages/assets/js/app.js"></script>

<script>
$(document).ready(function () {
    $('#transactions_nav').addClass('active');
    $('input[name="datetimes"]').daterangepicker({
								timePicker: true,
								opens: 'left',
								startDate: moment().startOf('day'),
								endDate: moment().endOf('day'),
								locale: {
									format: 'MMM DD hh:mm A'
								}
							}); 
    
    var table = $('#datatables-transactions').DataTable({
        processing: true,
        serverSide: true,
        scrollY: "400px",
        scrollX: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [
            { width: "10%", targets: 0 },  // Shortcode
            { width: "10%", targets: 1 },  // Keyword
            { width: "10%", targets: 2 },  // Transaction ID
            { width: "10%", targets: 3 },  // Amount
            { width: "10%", targets: 4 },  // Customer Name
            { width: "10%", targets: 5 },  // Telephone No
            { width: "10%", targets: 6 },  // Time
            { width: "10%", targets: 7 }  // Action

        ],
        ajax: {
            url: "pages/utils/transactions/fetch_transactions.php",
            type: "GET",
            data: function (d) {
                $('.column-search').each(function () {
                    let columnIndex = $(this).data('column');
                    d['column_' + columnIndex] = $(this).val();
                    d['column_3_a'] = $('input[name="datetimes"]').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d['column_3_b'] = $('input[name="datetimes"]').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                });

                // alert(d.column_3_b);
            }
        },
        columns: [
            { title: "Shortcode", data: 0 },
            { title: "Keyword", data: 1 },
            { title: "Transaction ID", data: 2 },
            { title: "Amount (ksh)", data: 3 },
            { title: "Customer Name", data: 4 },
            { title: "Telephone No", data: 5 },
            { title: "Time", data: 6 },
            { title: "Action", data: 7 }
        ],
        order: [[6, 'desc']],
        dom: `<"row"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center custom-search">>` + 
             `<"row"<"col-md-12"p>>` + 
             `<"row"<"col-12"tr>>` +
             `<"row"<"col-md-5"i><"col-md-7"p>>`, 
        initComplete: function () {
			
            $(".custom-search").append(`
			<button id="export_excel" class="btn btn-sm btn-md btn-success">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#customSearch" aria-expanded="false" aria-controls="customSearch">
                    <i class="fas fa-search"></i> Search
                </button>
            `);

            $(document).on("click", "#export_excel", function () {
                let exportData = {};
                $(".column-search").each(function () {
                    let columnIndex = $(this).data("column");
                    exportData["column_" + columnIndex] = $(this).val(); 
                });

                let dateRangePicker = $('input[name="datetimes"]').data('daterangepicker');
                if (dateRangePicker) {
                    exportData["column_3_a"] = dateRangePicker.startDate.format('YYYY-MM-DD HH:mm:ss');
                    exportData["column_3_b"] = dateRangePicker.endDate.format('YYYY-MM-DD HH:mm:ss');
                }


                let queryParams = new URLSearchParams(exportData).toString();
                window.location.href = "pages/utils/transactions/export_transactions.php?" + queryParams;
            });

            $(".column-search").on("keyup change", function () {
                table.ajax.reload();
            });
        },
		drawCallback: function () {
            table.columns.adjust().responsive.recalc(); 
        }
    });

    // Search filters
    $('.column-search').on('keyup change', function () {
        table.draw();
    });

    // Adjust table on sidebar toggle
    $(".sidebar-toggle").click(function () {
        setTimeout(function () {
            table.columns.adjust().responsive.recalc();
        }, 300);
    });

    // Adjust on window resize
    $(window).on("resize", function () {
        setTimeout(function () {
            table.columns.adjust().responsive.recalc();
        }, 300);
    });

    

   

    function fetchShortcodes(url, selectElement, defaultText){
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                let options = `<option value="">${defaultText}</option>`;
                $.each(response, function (index, item) {
                    options += `<option value="${item.shortcode_id}">${item.shortcode}</option>`;
                });
                $(selectElement).html(options);
            }
        });
    }

    // Populate the Shortcode dropdown
    fetchShortcodes("pages/utils/winners/fetch_shortcodes.php", "#shortcodeFilter", "Select Shortcode");


    // Open View Transaction Modal and Load Data
	$(document).on("click", ".view-transaction", function () {
		
		let shortcode = $(this).data("shortcode");
		let keyword = $(this).data("keyword");
		let transaction_code = $(this).data("transaction_code");
		let amount_won = $(this).data("amount");
		let customer_name = $(this).data("customer_name");
		let msisdn = $(this).data("msisdn");
		let trans_time = $(this).data("trans_time");
		let amount_user_transacted = $(this).data("amount_user_transacted");
		let amount_transacted_code = $(this).data("amount_transacted_code");
		let amount_transacted_time = $(this).data("amount_transacted_time");
		

		$("#shortcode").val(shortcode);
		$("#keyword").val(keyword);
		$("#transaction_code").val(transaction_code);
		$("#amount_won").val(amount_won);
		$("#customer_name").val(customer_name);
		$("#msisdn").val(msisdn);
		$("#trans_time").val(trans_time);
		$("#amount_user_transacted").val(amount_user_transacted);
		$("#amount_transacted_code").val(amount_transacted_code);
		$("#amount_transacted_time").val(amount_transacted_time);
		

		

		$("#view_transaction_modal").modal("show");
	});

});
</script>

<?php include 'pages/footer.php'; ?>
