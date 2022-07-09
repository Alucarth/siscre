<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap
    }

    #tbl_loans_transactions td:nth-child(6), 
    #tbl_loans_transactions td:nth-child(7), 
    #tbl_loans_transactions td:nth-child(8) 
    {
        text-align: right;
    }
    .dataTables_info {
        float:left;
    }

    .dataTable th:nth-child(2),
    .dataTable td:nth-child(2)    
    {
        text-align: center;
    }

    .dataTable th:nth-child(10),
    .dataTable td:nth-child(10), 
    .dataTable th:nth-child(11),
    .dataTable td:nth-child(11), 
    .dataTable th:nth-child(12),
    .dataTable td:nth-child(12) 
    {
        text-align: center;
    }
    .hidden {
        display: none;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="title-block">
    <h3 class="title"> 
        <?=ktranslate("loans_transactions");?>
    </h3>
    <a href="javascript:void(0)" class="pull-right toggle-search" data-status="show"><span class="fa fa-angle-double-right"></span> <?= ktranslate2("Show Advance Search")?></a>
    <p class="title-description">
        <?=ktranslate("module_loans_desc");?>
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">   
        <div class="hidden panel-search">
            <div class="card" style="width:100%">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block"><?=ktranslate("loans_advance_search")?></h5>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="" id="dt-extra-params" style="height: calc(61vh);overflow: auto">
                        <div class="col-lg-12">
                            <div class="">

                                <div class="form-group">
                                    <label><?=ktranslate("loans_due_date_from")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="due_from_date" value="" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><?=ktranslate("loans_due_date_to")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="due_to_date" value="" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><?=ktranslate("loans_applied_date_from")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="applied_from_date" value="" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><?=ktranslate("loans_applied_date_to")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="applied_to_date" value="" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><?= ktranslate("loans_approved_date_from")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="approved_from_date" value="" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><?= ktranslate("loans_approved_date_to")?>:</label>
                                    <div class="input-group date">
                                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                        <input type="text" class="form-control" name="approved_to_date" value="" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><?= ktranslate("loans_by_customer")?>:</label>
                                    <div>
                                        <select class="form-control" name="customer_id">
                                            <option value="0">Elegir</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <?php if (trim($customer->first_name) == '') continue; ?>
                                                <option value="<?= $customer->person_id ?>"><?= ucwords($customer->first_name . " " . $customer->last_name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label><?= ktranslate("loans_by_staff")?>:</label>
                                    <div>
                                        <select class="form-control hidden-xs" name="employee_id">
                                            <option value="0">Elegir</option>
                                            <?php foreach ($staffs as $staff): ?>
                                                <option value="<?= $staff->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $staff->person_id) ? 'selected="selected"' : ""; ?>><?= $staff->first_name . " " . $staff->last_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><?= ktranslate("loans_by_status")?>:</label>
                                    <div>
                                        <select name="status" class="form-control hidden-xs">
                                            <option value="all"><?= ktranslate("common_all")?></option>
                                            <option value="paid"><?= ktranslate("loans_paid")?></option>
                                            <option value="unpaid"><?= ktranslate("loans_unpaid")?></option>
                                            <option value="reject"><?= ktranslate2("Rejected")?></option>
                                            <option value="overdue"><?= ktranslate("loans_overdue")?></option>
                                            <option value="approved"><?= ktranslate("loans_approved")?></option>
                                            <option value="pending"><?= ktranslate("loans_pending")?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="card-footer card-footer-sm bordered">
                    <div class="footer-block" style="width:100%;text-align: center">
                        <button type="button" class="btn btn-primary" id="btn-search"><?= ktranslate("loans_search")?></button>
                        <button type="button" class="btn btn-warning" id="btn-clear-search"><?= ktranslate("loans_clear")?></button>
                        <button type="button" class="btn btn-default" id="btn-close"><?= ktranslate("common_close")?></button>
                    </div>                   
                </div>

            </div>
        </div>
        <div class="col-lg-12 panel-list">
            <div class="card" style="width:100%; min-height: calc(85vh - 160px);">

                <div class="card-block">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">

                                <div class="inqbox-content table-responsive">

                                    <table class="table table-hover table-bordered" id="tbl_loans_transactions">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center"><?= ktranslate("loans_trans_id")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_borrower")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_product")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_description")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_loan_amount")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_loan_proceeds")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_balance")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_agent")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_approved_by")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_approved_date")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_next_payment_date")?></th>
                                                <th style="text-align: center"><?=ktranslate("loans_status")?></th>                            
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" style="text-align:right" class="tf-label"><?=ktranslate("loans_total")?>:</th>
                                                <th colspan="1" style="text-align:right" class="tf-total-proceeds"></th>
                                                <th colspan="1" style="text-align:right" class="tf-total-net-proceeds"></th>
                                                <th colspan="1" style="text-align:right" class="tf-total-balance"></th>
                                                <th colspan="5"></th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <?= $tbl_loan_transactions; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button type="button" class="btn btn-success" id="btn-export-csv"><?=ktranslate2("Export CSV");?></button>
    <button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> <?=ktranslate("common_print")?></button>
</div>

<?php echo form_open('loans/ajax', 'id="frmLoansDelete"', ["ajax_type" => 4]); ?>
<?php echo form_close(); ?>

<script>
    function loansFooter(row, data, start, end, display, table)
    {
        var api = table.api(), data;
        var url = '<?= site_url('loans/ajax'); ?>';
        var params = {
            ajax_type: 6,
            softtoken: $("input[name='softtoken']").val()
        };
        $.post(url, params, function (data) {
            if (data.status == "OK")
            {
                $(api.column(6).footer()).html(data.total_net_proceeds);
                $(api.column(5).footer()).html(data.total_proceeds);
                $(api.column(7).footer()).html(data.total_balance);
            }
        }, "json");
    }

    $(document).ready(function () {
        $(document).on("click", "#btn-export-csv", function () {
            var url = '<?= site_url('loans/export_csv'); ?>';
            var params = $("#dt-extra-params input, #dt-extra-params select").serialize();
            params += '&softtoken=' + $("input[name='softtoken']").val();
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    window.location.href = data.url;
                }
            }, "json");
        });
        
        $("#btn-clear-search").click(function(){
            $("#dt-extra-params input").val("");
            $("#dt-extra-params select[name='employee_id']").val('0');
            $("#dt-extra-params select[name='customer_id']").val('0');
            $("#dt-extra-params select[name='status']").val("all");
            $("select").trigger("change.select2");
        });
        
        $("#btn-close").click(function(){
            $(".toggle-search").trigger("click");
        });
        
        $(".toggle-search").click(function () {
            if ($(this).attr("data-status") == "show")
            {
                $(".panel-search").addClass("col-lg-3");
                $(".panel-search").removeClass("hidden");
                $(".panel-list").removeClass("col-lg-12");
                $(".panel-list").addClass("col-lg-9");
                $(this).attr("data-status", "hide");
                $(this).html('<span class="fa fa-angle-double-left"></span> <?=ktranslate("loans_hide_advance_search")?>')
            } else
            {
                $(".panel-search").removeClass("col-lg-3");
                $(".panel-search").addClass("hidden");
                $(".panel-list").addClass("col-lg-12");
                $(".panel-list").removeClass("col-lg-9");
                $(this).attr("data-status", "show");
                $(this).html('<span class="fa fa-angle-double-right"></span> <?=ktranslate("loans_show_advance_search")?>')
            }
        });

        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });

        $("#btn-search").click(function () {
            $("#tbl_loans_transactions").DataTable().ajax.reload();
        });

        $("#tbl_loans_transactions_filter").prepend("<a href='<?= site_url('loans/view/-1') ?>' class='btn btn-primary pull-left'><?=ktranslate("loans_new")?></a>");
        $("#tbl_loans_transactions_filter input[type='search']").attr("placeholder", "<?=ktranslate("common_search")?>");
        $("#tbl_loans_transactions_filter input[type='search']").removeClass("input-sm");
        $("#tbl_loans_transactions_filter").append($(".extra-filters").html());

        $(document).on("click", "#btn-export-pdf", function () {
            var clone = $("#tbl_loans_transactions_wrapper .dataTables_scrollBody").clone();

            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();

            var total_proceeds = $(".dataTables_scrollFoot .tf-total-proceeds.dt-min-width").html();
            var total_net_proceeds = $(".dataTables_scrollFoot .tf-total-net-proceeds.dt-min-width").html();
            var total_balance = $(".dataTables_scrollFoot .tf-total-balance.dt-min-width").html();
            $(clone).find("tfoot").html('<tr><td colspan="4" style="text-align:right">Total:</td><td style="text-align:right">' + total_proceeds + '</td><td style="text-align:right">' + total_net_proceeds + '</td><td style="text-align:right">' + total_balance + '</td><td colspan="5"></td></tr>');

            var url = '<?= site_url('printing/print_list/transactions.pdf'); ?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                title: '<?= ktranslate2("Loan Transaction Report")?>',
                html: clone.html()
            };
            blockElement("#btn-export-pdf");
            $.post(url, params, function (data) {
                if (data.status == "OK")
                {
                    window.open(data.url, '_blank');
                }
                unblockElement("#btn-export-pdf");
            }, "json");
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("<?=ktranslate2("Are you sure you wish to delete this transaction")?>?", function () {
                var url = $("#frmLoansDelete").attr("action");
                var params = $("#frmLoansDelete").serialize();
                params += '&id=' + $this.attr("data-loan-id");
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#tbl_loans_transactions").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>