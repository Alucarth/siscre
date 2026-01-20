<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7) { text-align: center; }
    .dataTables_info { float:left; }
    .indent-0 { padding-left: 8px !important; font-weight: bold; }
    .indent-1 { padding-left: 30px !important; font-weight: 600; }
    .indent-2 { padding-left: 50px !important; }
    .indent-3 { padding-left: 70px !important; font-style: italic; }
    .indent-4 { padding-left: 90px !important; }
    #tbl_income td:nth-child(2) { font-family: 'Courier New', monospace; }
    #tbl_income tr td.indent-0 { background-color: #f8f9fa !important; }
</style>

<div class="section">
    <div class="card" style="width:100%">
        <div class="card-block">
            <div class="inqbox-content table-responsive">
                <table class="table table-hover table-bordered" id="tbl_income">
                    <thead>
                        <tr>
                            <th style="width: 1%"></th>                            
                            <th>Código</th>
                            <th>Nombre de cuenta</th>
                            <th>Descripción</th>                                    
                        </tr>
                    </thead>
                </table>
                <?= $tbl_income; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md-income" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cuenta de Ingresos</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="income_id" name="id" value="" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nivel:</label>
                            <select class="form-control" id="income_account_level" name="account_level">
                                <option value="">Seleccionar nivel</option>
                                <option value="1">Nivel 1</option><option value="2">Nivel 2</option>
                                <option value="3">Nivel 3</option><option value="4">Nivel 4</option>
                            </select>
                        </div>
                        <div class="form-group" id="income_parent_group" style="display:none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" id="income_parent_code" name="parent_code"></select>
                        </div>
                        <div class="form-group">
                            <label>Código:</label>
                            <input type="text" class="form-control" id="income_code_number" name="code_number" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" class="form-control" id="income_account_name" name="account_name" />
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="income_description" name="description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-income">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function loadIncomeParents(level) {
        if (level > 1) {
            $("#income_parent_group").show();
            var reqLen = (level == 2) ? 2 : (level == 3 ? 4 : 6);
            $.post('<?= site_url("accounting/get_parent_accounts_by_level"); ?>', {
                softtoken: $("input[name='softtoken']").val(), 
                account_type: 'income', 
                required_length: reqLen
            }, function(res) {
                if(res.status == "OK") {
                    $("#income_parent_code").empty().append('<option value="">Seleccionar</option>');
                    $.each(res.accounts, function(i, a) {
                        $("#income_parent_code").append('<option value="'+a.code_number+'">'+a.code_number+' - '+a.account_name+'</option>');
                    });
                }
            }, "json");
        } else {
            $("#income_parent_group").hide();
            generateIncomeCode();
        }
    }

    function generateIncomeCode() {
        $.post('<?= site_url("accounting/generate_hierarchical_code"); ?>', {
            softtoken: $("input[name='softtoken']").val(), 
            account_type: 'income',
            parent_code: $("#income_parent_code").val(), 
            account_level: $("#income_account_level").val()
        }, function(res) { 
            if(res.status == "OK") {
                $("#income_code_number").val(res.code_number); 
            }
        }, "json");
    }

    function applyIncomeIndentation() {
        $('#tbl_income tbody tr').each(function() {
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

    function validateIncomeForm() {
        var level = $("#income_account_level").val();
        var parentCode = $("#income_parent_code").val();
        var accountName = $("#income_account_name").val().trim();
        
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

    function resetIncomeForm() {
        $("#md-income input[type='text'], #md-income textarea").val("");
        $("#income_id").val("");
        $("#income_account_level").val("").prop("disabled", false);
        $("#income_parent_group").hide();
        $("#income_parent_code").empty().append('<option value="">Seleccionar</option>');
    }

    $(document).ready(function () {
        // Ordenar por código (columna 1) ascendente por defecto
        var table = $('#tbl_income').DataTable();
        table.order([1, 'asc']).draw();
        
        // Aplicar indentación después de cada draw
        $('#tbl_income').on('draw.dt', function () { 
            applyIncomeIndentation(); 
        });
        
        // Aplicar indentación inicial
        applyIncomeIndentation();
        
        // Botón para nueva cuenta
        $("#tbl_income_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-income'>Nueva cuenta de ingreso</a>");
        
        // Eventos
        $(document).on("change", "#income_account_level", function() { 
            loadIncomeParents($(this).val()); 
        });
        
        $(document).on("change", "#income_parent_code", function() { 
            generateIncomeCode(); 
        });

        $(document).on("click", "#btn-new-income", function(){ 
            resetIncomeForm();
            $("#md-income").modal("show");
        });

        $(document).on("click", "#btn-save-income", function(){
            if (!validateIncomeForm()) return;
            
            var params = $("#md-income input, #md-income select, #md-income textarea").serialize() + 
                         '&account_type=income&type=3&softtoken=' + $("input[name='softtoken']").val();
            
            $.post('<?=site_url('accounting/ajax');?>', params, function(data){
                if (data.status == "OK") { 
                    $("#md-income").modal("hide"); 
                    $("#tbl_income").DataTable().ajax.reload(function() {
                        // Re-aplicar orden después de recargar
                        $('#tbl_income').DataTable().order([1, 'asc']).draw();
                        applyIncomeIndentation();
                    }, false);
                } else {
                    alert("Error: " + (data.message || "No se pudo guardar la cuenta"));
                }
            }, "json");
        });

        $(document).on("click", ".btn-edit-income", function(){
            var id = $(this).data("id");
            $.post('<?=site_url('accounting/ajax');?>', {
                softtoken: $("input[name='softtoken']").val(), 
                type: 4, 
                id: id,
                account_type: 'income'
            }, function(data){
                if (data.status == "OK") {
                    $("#income_id").val(data.row.id);
                    $("#income_account_name").val(data.row.account_name);
                    $("#income_description").val(data.row.description);
                    $("#income_code_number").val(data.row.code_number);
                    
                    var level = data.row.code_number.length / 2;
                    $("#income_account_level").val(level).prop("disabled", true);
                    
                    // Cargar cuenta padre si es nivel > 1
                    if (level > 1) {
                        loadIncomeParents(level);
                        setTimeout(function() {
                            var parentCode = data.row.code_number.substring(0, (level-1)*2);
                            $("#income_parent_code").val(parentCode);
                        }, 500);
                    } else {
                        $("#income_parent_group").hide();
                    }
                    
                    $("#md-income").modal("show");
                }
            }, "json");
        });
    });
</script>