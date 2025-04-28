<?php $this->load->view("leads/front/secure/partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap
    }
    
    #tbl_loans_transactions td:nth-child(3) {
        text-align: right;
    }
    .dataTables_info {
        float:left;
    }
    
    .dataTable th:nth-child(4),
    .dataTable td:nth-child(4),
    .dataTable th:nth-child(5),
    .dataTable td:nth-child(5)    
    {
        text-align: center;
    }
    
    .dataTables_scrollBody {
        max-height: fit-content !important;
        height: auto !important;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>


<div class="title-block">
    <h3 class="title">My Transactions</h3>
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
                                    <h5><i class="fa fa-filter"></i> Filters - Advance</h5>
                                </div>
                                <div class="inqbox-content">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Transaction Date From:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_from_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Transaction Date To:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_to_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-search">Search</button>
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
            <div class="card" style="width:100%; min-height: calc(85vh - 160px);">

                <div class="card-block">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">

                                <div class="inqbox-content table-responsive">

                                    <table class="table table-hover table-bordered" id="tbl_loans_transactions">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%">TRX. ID#</th>                                                
                                                <th style="text-align: center">Description</th>
                                                <th style="text-align: center">Amount</th>
                                                <th style="text-align: center">Type</th>
                                                <th style="text-align: center">Trans. Date</th>
                                            </tr>
                                        </thead>
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
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Print</button>
</div>

<div id="dt-extra-params">
    <input type="hidden" id="from_date" name="from_date" value="<?= date($this->config->item("date_format")); ?>" />
    <input type="hidden" id="to_date" name="to_date" value="<?= date($this->config->item("date_format")); ?>" />
</div>

<?php echo form_open();?>
<?php echo form_close();?>

<script>
    $(document).ready(function () {
        
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });
        
        $("#filter_from_date").change(function () {
            $("#from_date").val($(this).val());
        });
        $("#filter_to_date").change(function () {
            $("#to_date").val($(this).val());
        });

        $("#btn-search").click(function () {
            $("#tbl_loans_transactions").DataTable().ajax.reload();
        });
        
        $("#tbl_loans_transactions_filter input[type='search']").attr("placeholder", "Type your search here");
        $("#tbl_loans_transactions_filter input[type='search']").removeClass("input-sm");
        $("#tbl_loans_transactions_filter").append($(".extra-filters").html());
        
        $("#btn-submit-apply-loan").click(function(){
            var url = '<?=site_url('leads/ajax');?>';
            var params = {
                type: 18,
                softtoken:$("input[name='softtoken']").val(),
                loan_product_id: $("#application_loan_product").val(),
                apply_amount: $("#application_apply_amount").val()
            };
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#md-apply-loan").modal("hide");
                    $("#tbl_loans_transactions").DataTable().ajax.reload();
                }
            }, "json");
        });
        
        $(document).on("click", ".btn-apply-loan", function(){
            $("#md-apply-loan").modal("show");
        });
        
        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_loans_transactions_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/transactions.pdf');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
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
        
        $(document).on("change", "#filter_by", function () {
            $("#status").val($(this).val());
            $("#tbl_loans_transactions").DataTable().ajax.reload();
        });
    });
</script>

<?php $this->load->view("leads/front/secure/partial/footer"); ?>