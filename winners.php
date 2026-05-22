<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">

	<h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">Winners</li>
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
					<h5 class="card-title mb-0">Today's Winners</h5>
				</div>

				<div class="collapse p-3 bg-light" id="customSearch">
					<div class="row">
                        <div class="col-md-2">
                            <input type="hidden" name="campaign_id" id="campaign_id">
							<label>Campaign Name</label>
							<select id="campaignFilter" class="form-control form-control-sm column-search" data-column="0">
								<option value="">Loading...</option>
							</select>
						</div>

                        <div class="col-md-2">
							<label>Condition Name</label>
							<select id="conditionFilter" class="form-control form-control-sm column-search" data-column="2">
								<option value="">Select Campaign First</option>
							</select>
						</div>

                        <div class="col-md-2">
							<label>Shortcode</label>
							<select id="shortcodeFilter" class="form-control form-control-sm column-search" data-column="1">
								<option value="">Loading...</option>
							</select>
						</div>

                        <div class="col-md-3">
                            <label>Transaction Date</label>
                            <input class="form-control form-control-sm column-search" type="text" name="datetimes" data-column="3" />
                        </div>

                        
                        <!-- <div class="col-md-2">
							<label>Amount Won</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="3">
						</div> -->

						<!-- <div class="col-md-2">
							<label>Campaign Name</label>
							<select id="campaignFilter" class="form-control form-control-sm column-search" data-column="6">
								<option value="">Loading...</option>
							</select>
						</div> -->
						<!-- <div class="col-md-2">
							<label>Shortcode / Paybill</label>
							<select id="shortcodeFilter" class="form-control form-control-sm column-search" data-column="0">
								<option value="">Loading...</option>
							</select>
						</div> -->
						<!-- <div class="col-md-2">
							<label>Condition Name</label>
							<select id="conditionFilter" class="form-control form-control-sm column-search" data-column="7">
								<option value="">Loading...</option>
							</select>
						</div> -->
						<!-- <div class="col-md-2">
							<label>Amount Won</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="3">
						</div> -->
						<!-- <div class="col-md-2">
							<label>Amount User Transacted</label>
							<input type="text" class="form-control form-control-sm column-search" data-column="4">
						</div> -->
						
					</div>
                    <div class="row mt-3">

                    </div>
    			</div>

				<div class="card-body">
					<table id="datatables-winners" class="table table-striped" style="width:100%">
						<thead>
							<tr>
								<th>Shortcode</th>
								<th>Customer Name</th>
								<th>Telephone No</th>
								<th>Transaction ID</th>
								<th>Keyword</th>
								<th>Amount Transacted (ksh)</th>
								<th>Amount Won (ksh)</th>
								<th>Campaign Name</th>
								<th>Condition Name</th>
								<th>Time</th>
							</tr>
						</thead>

                        <tfoot>
                            <tr>
								<th>Shortcode</th>
								<th>Customer Name</th>
								<th>Telephone No</th>
								<th>Transaction ID</th>
								<th>Keyword</th>
								<th>Amount Transacted (ksh)</th>
								<th>Amount Won (ksh)</th>
								<th>Campaign Name</th>
								<th>Condition Name</th>
								<th>Time</th>
							</tr>
                        </tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>

<script src="pages/assets/js/app.js"></script>

<script>
$(document).ready(function () {
    $('#winners_nav').addClass('active');
    $('input[name="datetimes"]').daterangepicker({
								timePicker: true,
								opens: 'left',
								startDate: moment().startOf('day'),
								endDate: moment().endOf('day'),
								locale: {
									format: 'MMM DD hh:mm A'
								}
							}); 
    
    var table = $('#datatables-winners').DataTable({
        processing: true,
        serverSide: true,
        scrollY: "400px",
        scrollX: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [
            { width: "10%", targets: 0 },  // Shortcode
            { width: "15%", targets: 1 },  // Customer Name
            { width: "15%", targets: 2 },  // Telephone No
            { width: "10%", targets: 3 },  // Transaction ID
            { width: "10%", targets: 4 },  // Keyword
            { width: "10%", targets: 5 },  // Amount Transacted
            { width: "10%", targets: 6 },  // Campaign Name
            { width: "10%", targets: 7 },  // Condition Name
            { width: "10%", targets: 8 },  // Time
        ],
        ajax: {
            url: "pages/utils/winners/fetch_winners.php",
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
            { title: "Customer Name", data: 1 },
            { title: "Telephone No", data: 2 },
            { title: "Transaction ID", data: 3 },
            { title: "Keyword", data: 4 },
            { title: "Amount Transacted (ksh)", data: 5 },
            { title: "Amount Won (ksh)", data: 6 },
            { title: "Campaign Name", data: 7 },
            { title: "Condition Name", data: 8 },
            { title: "Time", data: 9 }
        ],
        order: [[8, 'desc']],
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

                let dateRangePicker = $('input[name="datetimes"]').data('daterangepicker');
                if (dateRangePicker) {
                    exportData["column_3_a"] = dateRangePicker.startDate.format('YYYY-MM-DD HH:mm:ss');
                    exportData["column_3_b"] = dateRangePicker.endDate.format('YYYY-MM-DD HH:mm:ss');
                }

                let queryParams = new URLSearchParams(exportData).toString();
                window.location.href = "pages/utils/winners/export_winners.php?" + queryParams;
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

    

    function fetchCondition(url, selectElement, campaignId, defaultText) {
        $.ajax({
            url: url,
            type: "GET",
            data: { campaign_id: campaignId }, 
            dataType: "json",
            success: function (response) {
                let options = `<option value="">${defaultText}</option>`;
                $.each(response, function (index, item) {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                $(selectElement).html(options);
            },
            error: function () {
                $(selectElement).html(`<option value="">Failed to load</option>`);
            }
        });
    }

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

    function fetchCampaign(url, selectElement, defaultText) {
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                let options = `<option value="">${defaultText}</option>`;
                $.each(response, function (index, item) {
                    options += `<option value="${item.name}" data-id="${item.id}">${item.name}</option>`;
                });
                $(selectElement).html(options);
            }
        });
    }

    // Populate the Campaign Name dropdown
    fetchCampaign("pages/utils/winners/fetch_campaigns.php", "#campaignFilter", "Select Campaign");

    // Populate the Shortcode dropdown
    fetchShortcodes("pages/utils/winners/fetch_shortcodes.php", "#shortcodeFilter", "Select Shortcode");

    // Fetch conditions based on the selected campaign
    $('#campaignFilter').on('change', function () {
        // let selectedCampaign = $(this).val();
        let selectedOption = $(this).find(':selected'); 
        let campaign_id = selectedOption.data('id'); 
        if (campaign_id) {
            fetchCondition("pages/utils/winners/fetch_conditions.php", "#conditionFilter", campaign_id, "Select Condition");
        } else {
            $('#conditionFilter').html('<option value="">Select Campaign First</option>'); 
        }
    });

});
</script>

<?php include 'pages/footer.php'; ?>
