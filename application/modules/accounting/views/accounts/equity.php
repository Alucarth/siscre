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

                        <table class="table table-hover table-bordered" id="tbl_equity">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 1%"></th>                            
                                    <th style="text-align: center">Codigo numero</th>
                                    <th style="text-align: center">Nombre de cuenta</th>
                                    <th style="text-align: center">Descripcion</th>                                    
                                </tr>
                            </thead>
                        </table>

                        <?= $tbl_equity; ?>

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
<div class="modal fade" id="md-equity" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px">
            <div class="modal-header">
                Patrimonio
                <input type="hidden" name="account_type" id="account_type" value="equity" />
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="" />
                <div class="form-group">
                    <label>Codigo Numero:</label>
                    <input type="text" class="form-control" name="code_number" id="code_number" />
                </div>
                <div class="form-group">
                    <label>Numero de Cuenta:</label>
                    <input type="text" class="form-control" name="account_name" id="account_name" />
                </div>
                <div class="form-group">
                    <label>Descripcion:</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-equity">Guardar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php echo form_open('accounting/ajax', 'id="frmAssetDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $("#tbl_equity_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-equity'>Nueva cuenta de patrimonio</a>");
        $("#tbl_equity_filter input[type='search']").attr("placeholder", "Escriba su busqueda");
        $("#tbl_equity_filter input[type='search']").removeClass("input-sm");
        
        $("#btn-save-equity").click(function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = $("#md-equity input, #md-equity textarea").serialize();
            params += '&softtoken=' + $("input[name='softtoken']").val() + '&type=3';
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#md-equity").modal("hide");
                    $("#tbl_equity").DataTable().ajax.reload();
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
        });
        
        $(document).on("click", "#btn-new-equity", function(){            
            $("#md-equity .modal-body input, #md-equity .modal-body textarea").val("");
            $("#md-equity #code_number").prop("disabled", false);
            $("#md-equity").modal("show");
        });
        
        $(document).on("click", ".btn-edit-equity", function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type:4,
                id: $(this).data("id")
            };
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $.each(data.row, function(key, value){                        
                        $("#md-equity #" + key).val(value);
                    });
                    
//                    $("#md-equity #code_number").prop("disabled", true);
                    $("#md-equity").modal("show");
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
            
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Esta seguro que desea eliminar este patrimonio?", function () {
                var url = $("#frmAssetDelete").attr("action");/
                var params = $("#frmAssetDelete").serialize();
                params += '&id=' + $this.attr("data-id");
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#tbl_equity").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>