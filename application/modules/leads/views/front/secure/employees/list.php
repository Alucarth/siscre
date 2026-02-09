<?php $this->load->view("leads/front/secure/partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap
    }
    
    .dataTables_info {
        float:left;
    }
    
    .dataTable th,
    .dataTable td
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
    <h3 class="title">My Employees</h3>
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

                                    <table class="table table-hover table-bordered" id="tbl_employees">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center">Last Name</th>
                                                <th style="text-align: center">First Name</th>
                                                <th style="text-align: center">Email</th>                                                
                                            </tr>
                                        </thead>
                                    </table>

                                    <?= $tbl_employees; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>
    </div>
</div>


<div id="dt-extra-params">
    <input type="hidden" id="status" name="status" value="all" />
</div>

<?php echo form_open();?>
<?php echo form_close();?>

<script>
    $(document).ready(function () {
        $("#tbl_employees_filter").prepend("<a href='<?=site_url('leads/employee/-1')?>' class='btn btn-primary pull-left btn-apply-loan'>Add Employee</a>");
        $("#tbl_employees_filter input[type='search']").attr("placeholder", "Type your search here");
        $("#tbl_employees_filter input[type='search']").removeClass("input-sm");
        $("#tbl_employees_filter").append($(".extra-filters").html());
        
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
                    $("#tbl_employees").DataTable().ajax.reload();
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
            var clone = $("#tbl_employees_wrapper .dataTables_scrollBody").clone();
            
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
            $("#tbl_employees").DataTable().ajax.reload();
        });
    });
</script>

<?php $this->load->view("leads/front/secure/partial/footer"); ?>