<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1){
        text-align: center !important;
    }
    #tbl_loans_transactions td:nth-child(5),
    #tbl_loans_transactions td:nth-child(6) {
        text-align: right;
    }
    .dataTables_info {
        float:left;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="title-block">
    <h3 class="title"> 
        <?= ktranslate2("Receivables");?>
    </h3>
    <p class="title-description">
        <?= ktranslate2("List of receivables");?>
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">
                                <div class="inqbox-title">
                                    <h5><i class="fa fa-filter"></i> <?= ktranslate2("Filters");?></h5>
                                </div>
                                <div class="inqbox-content">
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label><?= ktranslate2("Due Date From");?>:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_from_date" value="" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label><?= ktranslate2("Due Date To");?>:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_to_date" value="" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label><?= ktranslate2("By Customer");?>:</label>
                                                <div>
                                                    <select class="form-control" id="sel-customer-id">
                                                        <option value="0"><?= ktranslate2("Choose");?></option>
                                                        <?php foreach ( $customers as $customer ):?>
                                                            <option value="<?=$customer->person_id?>"><?=ucwords($customer->first_name . " " . $customer->last_name);?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label><?= ktranslate2("By Agent")?>:</label>
                                                <div>
                                                    <select class="form-control hidden-xs" id="sel-staff">
                                                        <option value="0"><?=ktranslate2("Choose")?></option>
                                                        <?php foreach ($staffs as $staff): ?>
                                                            <option value="<?= $staff->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $staff->person_id) ? 'selected="selected"' : ""; ?>><?= $staff->first_name . " " . $staff->last_name; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>&nbsp;
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-search"><?= ktranslate2("Search");?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%">
                <div class="card-block">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">            
                                <div class="inqbox-content table-responsive">

                                    <table class="table table-hover table-bordered" id="tbl_loans_transactions">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center"><?=ktranslate2("Trans. ID#");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Client /<br/>Borrower");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Description");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Proceeds");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Balance");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Agent");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Approved <br/>By");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Date <br/>Approved");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Next <br/>Payment<br/> Date");?></th>
                                                <th style="text-align: center"><?=ktranslate2("Status");?></th>                            
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" style="text-align:right" class="tf-label"><?= ktranslate2("Total");?>:</th>
                                                <th colspan="1" style="text-align:right" class="tf-total-proceeds"></th>
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
    <button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> <?= ktranslate2("Print");?></button>
</div>

<div id="dt-extra-params">
    <input type="hidden" id="status" name="status" value="overdue" />
    <input type="hidden" id="employee_id" name="employee_id" value="0" />
    <input type="hidden" id="customer_id" name="customer_id" value="0" />
    
    <input type="hidden" id="from_date" name="due_from_date" value="" />
    <input type="hidden" id="to_date" name="due_to_date" value="" />
    
    <input type="hidden" name="no_delete" value="1" />
</div>

<?php echo form_open('loans/ajax', 'id="frmLoansDelete"', ["ajax_type" => 4]); ?>
<?php echo form_close(); ?>

<script>
    function loansFooter( row, data, start, end, display, table )
    {
        var api = table.api(), data;
        var url = '<?=site_url('loans/ajax');?>';
        var params = {
            ajax_type: 6,
            softtoken:$("input[name='softtoken']").val()
        };
        $.post(url, params, function(data){
            if ( data.status == "OK" )
            {
                $( api.column( 4 ).footer() ).html(data.total_proceeds);
                $( api.column( 5 ).footer() ).html(data.total_balance);
            }
        }, "json");
    }
    
    $(document).ready(function () {
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });
        
        $("#filter_from_date").change(function(){
            $("#from_date").val($(this).val());
        });
        $("#filter_to_date").change(function(){
            $("#to_date").val($(this).val());
        });
        $("#sel-customer-id").change(function(){
            $("#customer_id").val($(this).val());
        });
        $("#sel-staff").change(function(){
            $("#employee_id").val($(this).val());
        });
        
        $("#btn-search").click(function(){
            $("#tbl_loans_transactions").DataTable().ajax.reload();
        });
        
        $("#tbl_loans_transactions_filter input[type='search']").attr("placeholder", "<?= ktranslate2("Type your search here");?>");
        $("#tbl_loans_transactions_filter input[type='search']").removeClass("input-sm");
        $("#tbl_loans_transactions_filter").append($(".extra-filters").html());
        
        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_loans_transactions_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var total_proceeds = $(".dataTables_scrollFoot .tf-total-proceeds.dt-min-width").html();
            var total_balance = $(".dataTables_scrollFoot .tf-total-balance.dt-min-width").html();
            $(clone).find("tfoot").html('<tr><td colspan="3" style="text-align:right">Total:</td><td style="text-align:right">' + total_proceeds + '</td><td style="text-align:right">' + total_balance + '</td><td colspan="5"></td></tr>');
            
            var url = '<?=site_url('printing/print_list/overdue.pdf');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                title: 'Cuentas por cobrar',
                html: clone.html()
            };
            blockElement("#btn-export-pdf");
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    window.open(data.url,'_blank');
                }
                unblockElement("#btn-export-pdf");
            }, "json");
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Are you sure you wish to delete this transaction?", function () {
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