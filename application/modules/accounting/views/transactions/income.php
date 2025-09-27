<style>
    td:nth-child(1) {
        white-space: nowrap;
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

<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">

                    <div class="inqbox-content table-responsive">

                        <table class="table table-hover table-bordered" id="tbl_income">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 1%"></th>                            
                                    <th style="text-align: center">Cuenta</th>
                                    <th style="text-align: center">Importe recibido</th>
                                    <th style="text-align: center">Forma de pago</th>
                                    <th style="text-align: center">Fecha</th>
                                    <th style="text-align: center">Descripcion</th>                                    
                                </tr>
                            </thead>
                        </table>

                        <?= $tbl_income; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Imprimir</button>
</div>
<div id="dt-extra-params"></div>

<!-- Modal -->
<div class="modal fade" id="md-income" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px">
            <div class="modal-header">
                 Ingresos
                <input type="hidden" name="transaction_type" id="transaction_type" value="income" />
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="" />
                <div class="form-group">
                    <label>Cuenta:</label>
                    <select class="form-control" id="account_id" name="account_id">
                        <option value="">Seleccione por favor</option>
                        <?php foreach( $asset_accounts as $account ):?>
                            <option value="<?=$account->id?>"><?=$account->account_name;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Importe recibido:</label>
                    <input type="text" class="form-control" name="amount" id="amount" />
                </div>
                <div class="form-group">
                    <label>Forma de pago:</label>
                    <input type="text" class="form-control" name="payment_methods" id="payment_methods" />
                </div>
                <div class="form-group">
                    <label>Fecha:</label>
                    <div class="input-group date">
                        <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                        <input type="text" id="added_date" name="added_date" class="form-control" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripcion:</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-income">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php echo form_open('accounting/ajax', 'id="frmAssetDelete"', ["type" => 11]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $("#tbl_income_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-income'>Crear</a>");
        $("#tbl_income_filter input[type='search']").attr("placeholder", "Escriba su busqueda");
        $("#tbl_income_filter input[type='search']").removeClass("input-sm");
        
        $("#btn-save-income").click(function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = $("#md-income input, #md-income textarea, #md-income select").serialize();
            params += '&softtoken=' + $("input[name='softtoken']").val() + '&type=12';
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#md-income").modal("hide");
                    $("#tbl_income").DataTable().ajax.reload();
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
        });
        
        $(document).on("click", "#btn-new-income", function(){            
            $("#md-income .modal-body input, #md-income .modal-body textarea").val("");
            $("#md-income #code_number").prop("disabled", false);
            $("#md-income").modal("show");
        });
        
        $(document).on("click", ".btn-edit-income", function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type:13,
                id: $(this).data("id")
            };
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $.each(data.row, function(key, value){                        
                        $("#md-income #" + key).val(value);
                    });
                    
                    $("#md-income #code_number").prop("disabled", true);
                    $("#md-income").modal("show");
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
            
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Esta seguro que desea eliminar este ingreso?", function () {
                var url = $("#frmAssetDelete").attr("action");
                var params = $("#frmAssetDelete").serialize();
                params += '&id=' + $this.attr("data-id");
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#tbl_income").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
        
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            language: 'es'
        });
    });
</script>