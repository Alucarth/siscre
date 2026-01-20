<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7) { text-align: center; }
    .dataTables_info { float:left; }
    .indent-0 { padding-left: 8px !important; font-weight: bold; }
    .indent-1 { padding-left: 30px !important; font-weight: 600; }
    .indent-2 { padding-left: 50px !important; }
    .indent-3 { padding-left: 70px !important; font-style: italic; }
    .indent-4 { padding-left: 90px !important; }
    #tbl_equity td:nth-child(2) { font-family: 'Courier New', monospace; }
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
                                    <th style="text-align: center">Código</th>
                                    <th style="text-align: center">Nombre de cuenta</th>
                                    <th style="text-align: center">Descripción</th>                                    
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

<div class="modal fade" id="md-equity" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="width:800px">
            <div class="modal-header">
                <h5 class="modal-title">Cuenta de Patrimonio</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <input type="hidden" id="equity_account_type" name="account_type" value="equity" />
            </div>
            <div class="modal-body">
                <input type="hidden" id="equity_id" name="id" value="" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nivel de la Cuenta:</label>
                            <select class="form-control" id="equity_account_level" name="account_level" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="1">Nivel 1 (2 dígitos)</option>
                                <option value="2">Nivel 2 (4 dígitos)</option>
                                <option value="3">Nivel 3 (6 dígitos)</option>
                                <option value="4">Nivel 4 (8 dígitos)</option>
                            </select>
                        </div>
                        <div class="form-group" id="equity_parent_group" style="display: none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" id="equity_parent_code" name="parent_code"></select>
                            <small class="form-text text-muted" id="equity_parent_help"></small>
                        </div>
                        <div class="form-group">
                            <label>Código de Cuenta:</label>
                            <input type="text" class="form-control" id="equity_code_number" name="code_number" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre de Cuenta:</label>
                            <input type="text" class="form-control" id="equity_account_name" name="account_name" required />
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="equity_description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-equity">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php echo form_open('accounting/ajax', 'id="frmEquityDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
    function loadEquityParents(selectedLevel) {
        if (selectedLevel > 1) {
            $("#equity_parent_group").show();
            var requiredParentLength = (selectedLevel == 2) ? 2 : (selectedLevel == 3 ? 4 : 6);
            $.ajax({
                url: '<?= site_url("accounting/get_parent_accounts_by_level"); ?>',
                type: 'POST',
                data: { softtoken: $("input[name='softtoken']").val(), account_type: 'equity', required_length: requiredParentLength },
                dataType: 'json',
                success: function(response) {
                    if (response.status == "OK") {
                        $("#equity_parent_code").empty().append('<option value="">-- Seleccionar --</option>');
                        $.each(response.accounts, function(i, a) {
                            $("#equity_parent_code").append('<option value="'+a.code_number+'">'+a.code_number+' - '+a.account_name+'</option>');
                        });
                    }
                }
            });
        } else {
            $("#equity_parent_group").hide();
            generateEquityCode();
        }
    }

    function generateEquityCode() {
        $.ajax({
            url: '<?= site_url("accounting/generate_hierarchical_code"); ?>',
            type: 'POST',
            data: {
                softtoken: $("input[name='softtoken']").val(),
                account_type: 'equity',
                parent_code: $("#equity_parent_code").val(),
                account_level: $("#equity_account_level").val()
            },
            dataType: 'json',
            success: function(res) { if (res.status == "OK") $("#equity_code_number").val(res.code_number); }
        });
    }

    function applyEquityIndentation() {
        $('#tbl_equity tbody tr').each(function() {
            var row = $(this);
            var codeNumber = row.find('td:eq(1)').text().trim();
            var accountNameCell = row.find('td:eq(2)');
            var codeCell = row.find('td:eq(1)');
            
            // Calcular nivel basado en longitud
            var level = 0;
            if (codeNumber.length <= 2) level = 0;
            else if (codeNumber.length <= 4) level = 1;
            else if (codeNumber.length <= 6) level = 2;
            else level = 3;
            
            // Aplicar sangría a nombre Y código
            accountNameCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4')
                           .addClass('indent-' + level);
            codeCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4')
                    .addClass('indent-' + level);
        });
    }

    function validateEquityForm() {
        var level = $("#equity_account_level").val();
        var parentCode = $("#equity_parent_code").val();
        var accountName = $("#equity_account_name").val().trim();
        
        if (!level) {
            alert("Por favor seleccione un nivel de cuenta");
            return false;
        }
        
        if (level > 1 && !parentCode) {
            alert("Por favor seleccione una cuenta padre");
            return false;
        }
        
        if (!accountName) {
            alert("Por favor ingrese el nombre de la cuenta");
            return false;
        }
        
        return true;
    }

    function resetEquityForm() {
        $("#md-equity input[type='text'], #md-equity textarea, #equity_id").val("");
        $("#equity_account_level").val("").prop("disabled", false);
        $("#equity_parent_group").hide();
        $("#equity_parent_code").empty().append('<option value="">-- Seleccionar --</option>');
    }

    $(document).ready(function () {
        // Ordenar por código (columna 1) ascendente por defecto
        var table = $('#tbl_equity').DataTable();
        table.order([1, 'asc']).draw();
        
        // Aplicar indentación después de cada draw
        $('#tbl_equity').on('draw.dt', function () { 
            applyEquityIndentation(); 
        });
        
        // Aplicar indentación inicial
        applyEquityIndentation();
        
        // Botón para nueva cuenta
        $("#tbl_equity_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-equity'>Nueva cuenta de patrimonio</a>");

        // Eventos
        $(document).on("change", "#equity_account_level", function() { 
            loadEquityParents($(this).val()); 
        });
        
        $(document).on("change", "#equity_parent_code", function() { 
            generateEquityCode(); 
        });

        $(document).on("click", "#btn-new-equity", function(){ 
            resetEquityForm();
            $("#md-equity").modal("show");
        });

        $(document).on("click", "#btn-save-equity", function(){
            if (!validateEquityForm()) return;
            
            var params = $("#md-equity input, #md-equity select, #md-equity textarea").serialize() + 
                         '&softtoken=' + $("input[name='softtoken']").val() + '&type=3';
            
            $.post('<?=site_url('accounting/ajax');?>', params, function(data){
                if (data.status == "OK") { 
                    $("#md-equity").modal("hide"); 
                    $("#tbl_equity").DataTable().ajax.reload(function() {
                        // Re-aplicar orden después de recargar
                        $('#tbl_equity').DataTable().order([1, 'asc']).draw();
                        applyEquityIndentation();
                    }, false);
                } else {
                    alert("Error: " + (data.message || "No se pudo guardar la cuenta"));
                }
            }, "json");
        });

        $(document).on("click", ".btn-edit-equity", function(){
            var id = $(this).data("id");
            $.post('<?=site_url('accounting/ajax');?>', {softtoken: $("input[name='softtoken']").val(), type: 4, id: id}, function(data){
                if (data.status == "OK") {
                    $("#equity_id").val(data.row.id);
                    $("#equity_account_name").val(data.row.account_name);
                    $("#equity_description").val(data.row.description);
                    $("#equity_code_number").val(data.row.code_number);
                    
                    var level = data.row.code_number.length / 2;
                    $("#equity_account_level").val(level).prop("disabled", true);
                    
                    // Cargar cuenta padre si es nivel > 1
                    if (level > 1) {
                        loadEquityParents(level);
                        setTimeout(function() {
                            var parentCode = data.row.code_number.substring(0, (level-1)*2);
                            $("#equity_parent_code").val(parentCode);
                        }, 500);
                    } else {
                        $("#equity_parent_group").hide();
                    }
                    
                    $("#md-equity").modal("show");
                }
            }, "json");
        });
    });
</script>