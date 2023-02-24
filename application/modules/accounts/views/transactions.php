<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap;
    }
    td:nth-child(2) {
        text-align: right;
    }

    td:nth-child(4),
    td:nth-child(5),
    td:nth-child(6), 
    td:nth-child(7) {
        text-align: center;
    }
    .dataTables_info {
        float:left;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>



<div class="title-block">
    <h3 class="title"> 
        <span style="float:left">Transacciones</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Listado de transacciones
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
                                    <!--<h5><i class="fa fa-filter"></i> Filters - Advance</h5>//-->
                                </div>
                                <div class="inqbox-content">
                                    <div class="row" id="dt-extra-params">
                                        
                                        <input type="hidden" id="account_id" name="account_id" value="" />
                                        <input type="hidden" id="employee_id" name="employee_id" value="0" />

                                        
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Fecha de transacciones desde:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_from_date" name="filter_from_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Fecha de transacciones hasta:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_to_date" name="filter_to_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-search">Buscar</button>
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

                    <div class="inqbox-content table-responsive">

                        <table class="table table-hover table-bordered" id="tbl_account_transactions">
                            <thead>
                                <tr>
                                    <th style="text-align: center">Cuenta</th>
                                    <th style="text-align: center">Monto</th>
                                    <th style="text-align: center">Descripción</th>                                    
                                    <th style="text-align: center">Tipo</th>                                    
                                    <th style="text-align: center">Fecha</th>                                    
                                    <th style="text-align: center">Cliente</th>                                    
                                    <th style="text-align: center">Creado por</th>                                    
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th style="text-align:right">Total:</th>
                                    <th style="text-align:right"></th>
                                    <th colspan="5"></th>
                                </tr>
                            </tfoot>
                        </table>

                        <?= $tbl_account_transactions; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span>Imprimir</button>
    <select class="form-control hidden-xs" id="sel-staff">
        <option value="0">Seleccionar cliente</option>
        <?php foreach ($customers as $customer): ?>
            <option value="<?= $customer->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $customer->person_id) ? 'selected="selected"' : ""; ?>><?= $customer->first_name . " " . $customer->last_name; ?></option>
        <?php endforeach; ?>
    </select>&nbsp;
    <select class="form-control" name="sel_account_id" id="sel_account_id" style="margin-right:6px">
        <option value="">Escoger cuenta</option>
        <?php if ( $accounts ): ?>
            <?php foreach ( $accounts->result() as $account ): ?>
                <option value="<?=$account->id;?>"><?=$account->account_name?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>&nbsp;
</div>


<?php echo form_open('accounts/ajax', 'id="frmLoanAccountDelete"', ["type" => 4]); ?>
<?php echo form_close(); ?>

<script>
    function accountsFooter( row, data, start, end, display, table )
    {
        var api = table.api(), data;
        var url = '<?=site_url('accounts/ajax');?>';
        var params = $("#dt-extra-params input, #dt-extra-params select").serialize();
        params += "&type=8&softtoken=" + $("input[name='softtoken']").val() + "&search=" + $("#tbl_account_transactions_wrapper input[type='search']").val();        
        $.post(url, params, function(data){
            if ( data.status == "OK" )
            {
                $( api.column( 1 ).footer() ).html(data.total_balance);
            }
        }, "json");
    }
    
    $(document).ready(function () {
        
        $("#btn-search").click(function(){
            $("#tbl_account_transactions").DataTable().ajax.reload();
        });
        
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });
        
        $(document).on("change", "#sel-staff, #sel_account_id", function () {
            $("#account_id").val($("#sel_account_id").val());
            $("#employee_id").val($("#sel-staff").val());
            $("#tbl_account_transactions").DataTable().ajax.reload();
        });
        
        $("#tbl_account_transactions_filter input[type='search']").attr("placeholder", "Buscar transacciones");
        $("#tbl_account_transactions_filter input[type='search']").removeClass("input-sm");
        $("#tbl_account_transactions_filter").append($(".extra-filters").html());

        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_account_transactions_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/accounts.pdf');?>';
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

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Esta seguro que desea eliminiar esta cuenta?", function () {
                var url = $("#frmLoanAccountDelete").attr("action");
                var params = $("#frmLoanAccountDelete").serialize();
                params += '&id=' + $this.attr("data-id");
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#tbl_account_transactions").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>