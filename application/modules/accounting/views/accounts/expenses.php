<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7) { text-align: center; }
    .dataTables_info { float:left; }
    .indent-0 { padding-left: 8px !important; font-weight: bold; }
    .indent-1 { padding-left: 30px !important; font-weight: 600; }
    .indent-2 { padding-left: 50px !important; }
    .indent-3 { padding-left: 70px !important; font-style: italic; }
    .indent-4 { padding-left: 90px !important; }
    #tbl_expenses td:nth-child(2) { font-family: 'Courier New', monospace; }
    #tbl_expenses tr td.indent-0 { background-color: #f8f9fa !important; }
</style>

<div class="section">
    <div class="card" style="width:100%">
        <div class="card-block">
            <div class="inqbox-content table-responsive">
                <table class="table table-hover table-bordered" id="tbl_expenses">
                    <thead>
                        <tr>
                            <th style="width: 1%"></th>                            
                            <th>Código</th>
                            <th>Nombre de cuenta</th>
                            <th>Descripción</th>                                    
                        </tr>
                    </thead>
                </table>
                <?= $tbl_expenses; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md-expenses" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cuenta de Gastos</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="expenses_id" name="id" value="" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nivel:</label>
                            <select class="form-control" id="expenses_account_level" name="account_level">
                                <option value="">Seleccionar nivel</option>
                                <option value="1">Nivel 1</option><option value="2">Nivel 2</option>
                                <option value="3">Nivel 3</option><option value="4">Nivel 4</option>
                            </select>
                        </div>
                        <div class="form-group" id="expenses_parent_group" style="display:none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" id="expenses_parent_code" name="parent_code"></select>
                        </div>
                        <div class="form-group">
                            <label>Código:</label>
                            <input type="text" class="form-control" id="expenses_code_number" name="code_number" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" class="form-control" id="expenses_account_name" name="account_name" />
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="expenses_description" name="description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-expenses">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function loadExpensesParents(level) {
        if (level > 1) {
            $("#expenses_parent_group").show();
            var reqLen = (level == 2) ? 2 : (level == 3 ? 4 : 6);
            $.post('<?= site_url("accounting/get_parent_accounts_by_level"); ?>', {
                softtoken: $("input[name='softtoken']").val(), 
                account_type: 'expenses', 
                required_length: reqLen
            }, function(res) {
                if(res.status == "OK") {
                    $("#expenses_parent_code").empty().append('<option value="">Seleccionar...</option>');
                    $.each(res.accounts, function(i, a) {
                        $("#expenses_parent_code").append('<option value="'+a.code_number+'">'+a.code_number+' - '+a.account_name+'</option>');
                    });
                }
            }, "json");
        } else {
            $("#expenses_parent_group").hide();
            generateExpensesCode();
        }
    }

    function generateExpensesCode() {
        $.post('<?= site_url("accounting/generate_hierarchical_code"); ?>', {
            softtoken: $("input[name='softtoken']").val(), 
            account_type: 'expenses',
            parent_code: $("#expenses_parent_code").val(), 
            account_level: $("#expenses_account_level").val()
        }, function(res) { 
            if(res.status == "OK") {
                $("#expenses_code_number").val(res.code_number); 
            }
        }, "json");
    }

    function applyExpensesIndentation() {
        $('#tbl_expenses tbody tr').each(function() {
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

    function validateExpensesForm() {
        var level = $("#expenses_account_level").val();
        var parentCode = $("#expenses_parent_code").val();
        var accountName = $("#expenses_account_name").val().trim();
        
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

    function resetExpensesForm() {
        $("#md-expenses input[type='text'], #md-expenses textarea").val("");
        $("#expenses_id").val("");
        $("#expenses_account_level").val("").prop("disabled", false);
        $("#expenses_parent_group").hide();
        $("#expenses_parent_code").empty().append('<option value="">Seleccionar...</option>');
    }

    $(document).ready(function () {
        // Ordenar por código (columna 1) ascendente por defecto
        var table = $('#tbl_expenses').DataTable();
        table.order([1, 'asc']).draw();
        
        // Aplicar indentación después de cada draw
        $('#tbl_expenses').on('draw.dt', function () { 
            applyExpensesIndentation(); 
        });
        
        // Aplicar indentación inicial
        applyExpensesIndentation();
        
        // Botón para nueva cuenta
        $("#tbl_expenses_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-expenses'>Nueva cuenta de gasto</a>");
        
        // Eventos
        $(document).on("change", "#expenses_account_level", function() { 
            loadExpensesParents($(this).val()); 
        });
        
        $(document).on("change", "#expenses_parent_code", function() { 
            generateExpensesCode(); 
        });

        $(document).on("click", "#btn-new-expenses", function(){ 
            resetExpensesForm();
            $("#md-expenses").modal("show");
        });

        $(document).on("click", "#btn-save-expenses", function(){
            if (!validateExpensesForm()) return;
            
            var params = $("#md-expenses input, #md-expenses select, #md-expenses textarea").serialize() + 
                         '&account_type=expenses&type=3&softtoken=' + $("input[name='softtoken']").val();
            
            $.post('<?=site_url('accounting/ajax');?>', params, function(data){
                if (data.status == "OK") { 
                    $("#md-expenses").modal("hide"); 
                    $("#tbl_expenses").DataTable().ajax.reload(function() {
                        // Re-aplicar orden después de recargar
                        $('#tbl_expenses').DataTable().order([1, 'asc']).draw();
                        applyExpensesIndentation();
                    }, false);
                } else {
                    alert("Error: " + (data.message || "No se pudo guardar la cuenta"));
                }
            }, "json");
        });

        $(document).on("click", ".btn-edit-expenses", function(){
            var id = $(this).data("id");
            $.post('<?=site_url('accounting/ajax');?>', {
                softtoken: $("input[name='softtoken']").val(), 
                type: 4, 
                id: id,
                account_type: 'expenses'
            }, function(data){
                if (data.status == "OK") {
                    $("#expenses_id").val(data.row.id);
                    $("#expenses_account_name").val(data.row.account_name);
                    $("#expenses_description").val(data.row.description);
                    $("#expenses_code_number").val(data.row.code_number);
                    
                    var level = data.row.code_number.length / 2;
                    $("#expenses_account_level").val(level).prop("disabled", true);
                    
                    // Cargar cuenta padre si es nivel > 1
                    if (level > 1) {
                        loadExpensesParents(level);
                        setTimeout(function() {
                            var parentCode = data.row.code_number.substring(0, (level-1)*2);
                            $("#expenses_parent_code").val(parentCode);
                        }, 500);
                    } else {
                        $("#expenses_parent_group").hide();
                    }
                    
                    $("#md-expenses").modal("show");
                }
            }, "json");
        });
    });
</script>