<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap
    }
    
    #tbl_loans_transactions td:nth-child(3),
    #tbl_loans_transactions td:nth-child(4),
    #tbl_loans_transactions td:nth-child(5) {
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
    
    .dataTable td:nth-child(6),
    .dataTable td:nth-child(7), 
    .dataTable td:nth-child(8),
    .dataTable td:nth-child(9)
    {
        text-align: center !important;
    }
    
    .dataTables_scrollBody {
        max-height: fit-content !important;
        height: auto !important;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>


<div class="title-block">
    <h3 class="title">Applied Loans</h3>
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
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center">Loan ID#</th>
                                                <th style="text-align: center">Amount Applied</th>
                                                <th style="text-align: center">Payable</th>
                                                <th style="text-align: center">Balance</th>
                                                <th style="text-align: center">Date <br/>Applied</th>
                                                <th style="text-align: center">Date <br/>Approved</th>
                                                <th style="text-align: center">Estimated <br/>Payment<br/> Date</th>
                                                <th style="text-align: center">Status</th>                            
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

<!-- Modal -->
<div class="modal fade" id="md-apply-loan" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px">
            <div class="modal-header">
                Apply Loan
            </div>
            <div class="modal-body">
                
                <div class="alert alert-info" style="display:none;">
                    You currently have pending loan application. You can only apply new loan once you have settled the previous loan.
                </div>
                
                <div class="form-group">
                    <label>How much do you need?</label>
                    <input type="number" class="form-control" name="application_apply_amount" id="application_apply_amount" />
                </div>
                <div class="form-group">
                    <label>Choose the Loan Product</label>
                    <select class="form-control" name="application_loan_product" id="application_loan_product">
                        <?php foreach ( $loan_products as $loan_product ): ?>
                        <option value="<?=$loan_product->id;?>"><?=$loan_product->product_name;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-submit-apply-loan">Submit</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Print</button>
    <select id="filter_by" class="form-control hidden-xs">
        <option value="all">All</option>
        <option value="paid">Paid</option>
        <option value="unpaid">Un-Paid</option>
        <option value="overdue">Overdue</option>
    </select>
</div>

<div id="dt-extra-params">
    <input type="hidden" id="status" name="status" value="all" />
</div>

<?php echo form_open();?>
<?php echo form_close();?>

<script>
    $(document).ready(function () {
        $("#tbl_loans_transactions_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left btn-apply-loan'>Apply Loan</a>");
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
                else
                {
                    alertify.alert(data.msg);
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

<?php $this->load->view("partial/footer"); ?>