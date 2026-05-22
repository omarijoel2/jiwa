<?php require 'pages/utils/auth/auth_check.php'; ?>

<?php include 'pages/head.php'; ?>
<?php include 'pages/sidebar.php'; ?>
<?php include 'pages/navbar.php'; ?>

<div class="container-fluid p-0">
    <h1 class="h3 mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="index">Home</a></li>
				<li class="breadcrumb-item active">Contacts</li>
			</ol>
		</nav>
	</h1>

    <!-- row -->
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
					<h5 class="card-title mb-0">Contacts</h5>
				</div>

                <div class="collapse p-3 bg-light" id="customSearch">
                    <div class="row mt-3">
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
                            <label>Date Joined</label>
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

                        <div class="col-md-6">
                            <label>Hashed Telephone No</label>
                            <input type="text" class="form-control form-control-sm column-search" type="text" data-column="6">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table id="datatables-contacts" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Telephone No</th>
                                <th>Customer Name</th>
                                <th>Hashed Telephone No</th>
                                <th>Shortcode</th>
                                <th>Keyword</th>
                                <th>Date Joined</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th>Telephone No</th>
                                <th>Customer Name</th>
                                <th>Hashed Telephone No</th>
                                <th>Shortcode</th>
                                <th>Keyword</th>
                                <th>Date Joined</th>
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
    $(document).ready(function(){
        $('#contacts_nav').addClass('active');

        $('input[name="datetimes"]').daterangepicker({
            timePicker: true,
            opens: 'left',
            startDate: moment().startOf('month'), 
            endDate: moment().endOf('month'), 
            locale: {
                format: 'MMM DD hh:mm A'
            }
        });

        var table = $('#datatables-contacts').DataTable({
            processing: true,
            serverSide: true,
            scrollY: "400px",
            scrollX: true,
            autoWidth: false,
            responsive: true,
            columnDefs: [
                { width: "10%", targets: 0 },  // Telephone No
                { width: "15%", targets: 1 },  // Hashed Telephone No
                { width: "15%", targets: 2 },  // Paybill
                { width: "10%", targets: 3 },  // Keyword
                { width: "10%", targets: 4 },  // Customer Name
                { width: "10%", targets: 5 }   // Date Joined
            ],
            ajax: {
                url: "pages/utils/contacts/fetch_contacts.php",
                type: "GET",
                data: function (d) {
                    $('.column-search').each(function () {
                        let columnIndex = $(this).data('column');
                        d['column_' + columnIndex] = $(this).val();
                        d['column_3_a'] = $('input[name="datetimes"]').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                        d['column_3_b'] = $('input[name="datetimes"]').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    });
                }
            },
            columns: [
                { title: "Telephone No", data: 0 },
                { title: "Customer Name", data: 4 },
                { title: "Hashed Telephone No", data: 1 },
                { title: "Shortcode", data: 2 },
                { title: "Keyword", data: 3 },
                { title: "Date Joined", data: 5 }
            ],
            order: [[5, 'desc']],
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
                    window.location.href = "pages/utils/contacts/export_contacts.php?" + queryParams;
                });

                $(".column-search").on("keyup change", function () {
                    table.ajax.reload();
                });
            },
            drawCallback: function () {
                table.columns.adjust().responsive.recalc(); 
            }
        });

        
        $('.column-search').on('keyup change', function () {
            table.draw();
        });

        
        $(".sidebar-toggle").click(function () {
            setTimeout(function () {
                table.columns.adjust().responsive.recalc();
            }, 300);
        });

        
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

        fetchShortcodes("pages/utils/winners/fetch_shortcodes.php", "#shortcodeFilter", "Select Shortcode");
    });
    
    
</script>

<?php include 'pages/footer.php'; ?>