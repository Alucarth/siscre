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
    
    /* ESTILOS PARA SANGRÍAS */
    .indent-0 { padding-left: 8px !important; }
    .indent-1 { padding-left: 30px !important; }
    .indent-2 { padding-left: 60px !important; }
    .indent-3 { padding-left: 70px !important; }
    .indent-4 { padding-left: 90px !important; }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%">
                <div class="card-block">
                    <div class="inqbox-content table-responsive">
                        <table class="table table-hover table-bordered" id="tbl_liability">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 1%"></th>                            
                                    <th style="text-align: center">Código</th>
                                    <th style="text-align: center">Nombre de cuenta</th>
                                    <th style="text-align: center">Descripción</th>                                    
                                </tr>
                            </thead>
                        </table>
                        <?= $tbl_liability; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md-liability" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="width:800px">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Cuenta de Pasivo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <input type="hidden" name="account_type" id="liability_account_type" value="liability" />
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="liability_id" value="" />
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nivel de la Cuenta:</label>
                            <select class="form-control" name="account_level" id="liability_account_level" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="1">Nivel 1 - Clase Principal (2 dígitos)</option>
                                <option value="2">Nivel 2 - Subclase (4 dígitos)</option>
                                <option value="3">Nivel 3 - Cuenta específica (6 dígitos)</option>
                                <option value="4">Nivel 4 - Cuenta detallada (8 dígitos)</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="liability-parent-group" style="display: none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" name="parent_code" id="liability_parent_code">
                                <option value="">Seleccionar cuenta padre</option>
                            </select>
                            <small class="form-text text-muted" id="liability-parent-help"></small>
                        </div>
                        
                        <div class="form-group">
                            <label>Código de Cuenta:</label>
                            <input type="text" class="form-control" name="code_number" id="liability_code_number" readonly />
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre de Cuenta:</label>
                            <input type="text" class="form-control" name="account_name" id="liability_account_name" required />
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="liability_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <!-- Panel de ayuda para edición -->
                        <div class="alert alert-info" id="liability-hierarchy-help" style="display: none;">
                            <strong>Información:</strong><br>
                            <small>En modo edición, el nivel y padre no pueden modificarse.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-liability">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php echo form_open('accounting/ajax', 'id="frmLiabilityDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
// =============================================
// FUNCIONES PARA LIABILITY
// =============================================

function loadLiabilityParentAccounts(selectedLevel) {
    console.log('loadLiabilityParentAccounts - Nivel:', selectedLevel);
    
    var parentGroup = $("#liability-parent-group");
    var parentSelect = $("#liability_parent_code");
    var parentHelp = $("#liability-parent-help");
    
    if (selectedLevel > 1) {
        parentGroup.show();
        
        var requiredParentLength = 0;
        var helpText = "";
        
        switch(parseInt(selectedLevel)) {
            case 2:
                requiredParentLength = 2;
                helpText = "Seleccione una cuenta de Nivel 1 (2 dígitos)";
                break;
            case 3:
                requiredParentLength = 4;
                helpText = "Seleccione una cuenta de Nivel 2 (4 dígitos)";
                break;
            case 4:
                requiredParentLength = 6;
                helpText = "Seleccione una cuenta de Nivel 3 (6 dígitos)";
                break;
        }
        
        parentHelp.text(helpText);
        parentSelect.empty().append('<option value="">Cargando cuentas padre...</option>');
        parentSelect.prop('disabled', true);
        
        $.ajax({
            url: '<?= site_url("accounting/get_parent_accounts_by_level"); ?>',
            type: 'POST',
            data: {
                softtoken: $("input[name='softtoken']").val(),
                account_type: 'liability',
                required_length: requiredParentLength
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta cuentas padre:', response);
                
                if (response.status == "OK") {
                    parentSelect.empty().append('<option value="">Seleccionar cuenta padre</option>');
                    
                    if (response.accounts && response.accounts.length > 0) {
                        $.each(response.accounts, function(index, account) {
                            parentSelect.append('<option value="' + account.code_number + '">' + 
                                               account.code_number + ' - ' + account.account_name + '</option>');
                        });
                        
                        // Si solo hay una cuenta, seleccionarla automáticamente
                        if (response.accounts.length === 1) {
                            parentSelect.val(response.accounts[0].code_number);
                            setTimeout(function() {
                                generateLiabilityHierarchicalCode();
                            }, 100);
                        }
                    } else {
                        parentSelect.append('<option value="">No hay cuentas padre disponibles</option>');
                    }
                } else {
                    alertify.alert("Error: " + (response.msg || "No se pudieron cargar las cuentas padre"));
                    parentSelect.empty().append('<option value="">Error al cargar</option>');
                }
                
                parentSelect.prop('disabled', false);
            },
            error: function() {
                alertify.alert("Error al conectar con el servidor");
                parentSelect.empty().append('<option value="">Error de conexión</option>');
                parentSelect.prop('disabled', false);
            }
        });
        
    } else {
        parentGroup.hide();
        parentSelect.val('');
        
        // Para nivel 1, generar código inmediatamente
        if (selectedLevel == 1) {
            generateLiabilityHierarchicalCode();
        }
    }
}

function generateLiabilityHierarchicalCode() {
    console.log('generateLiabilityHierarchicalCode llamado');
    
    var parentCode = $("#liability_parent_code").val();
    var accountLevel = $("#liability_account_level").val();
    
    if (!accountLevel) {
        console.log('No hay nivel seleccionado');
        $("#liability_code_number").val("");
        return;
    }
    
    if (accountLevel > 1 && !parentCode) {
        console.log('Falta cuenta padre para nivel', accountLevel);
        $("#liability_code_number").val("");
        return;
    }
    
    // Mostrar cargando
    $("#liability_code_number").val("Generando código...");
    
    $.ajax({
        url: '<?= site_url("accounting/generate_hierarchical_code"); ?>',
        type: 'POST',
        data: {
            softtoken: $("input[name='softtoken']").val(),
            account_type: 'liability',
            parent_code: parentCode,
            account_level: accountLevel
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta generate_hierarchical_code:', response);
            
            if (response.status == "OK") {
                $("#liability_code_number").val(response.code_number);
            } else {
                alertify.alert("Error al generar código: " + (response.msg || "Error desconocido"));
                $("#liability_code_number").val("");
            }
        },
        error: function() {
            alertify.alert("Error al conectar con el servidor");
            $("#liability_code_number").val("");
        }
    });
}

function validateLiabilityForm() {
    var accountLevel = $("#liability_account_level").val();
    var parentCode = $("#liability_parent_code").val();
    var codeNumber = $("#liability_code_number").val();
    var accountName = $("#liability_account_name").val();
    
    // Validaciones básicas
    if (!accountLevel) {
        alertify.alert("Debe seleccionar el nivel de la cuenta");
        return false;
    }
    
    if (!accountName.trim()) {
        alertify.alert("Debe ingresar el nombre de la cuenta");
        $("#liability_account_name").focus();
        return false;
    }
    
    if (!codeNumber) {
        alertify.alert("Debe generar un código primero. Seleccione el nivel y cuenta padre si aplica.");
        return false;
    }
    
    // Validar que para niveles 2-4 tenga cuenta padre seleccionada
    if (accountLevel > 1 && !parentCode) {
        alertify.alert("Para nivel " + accountLevel + " debe seleccionar una cuenta padre");
        $("#liability_parent_code").focus();
        return false;
    }
    
    return true;
}

function applyLiabilityIndentation() {
    $('#tbl_liability tbody tr').each(function() {
        var row = $(this);
        var codeNumber = row.find('td:eq(1)').text().trim();
        var accountNameCell = row.find('td:eq(2)');
        var level = (codeNumber.length <= 2) ? 0 : (codeNumber.length <= 4 ? 1 : (codeNumber.length <= 6 ? 2 : 3));
        accountNameCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4').addClass('indent-' + level);
    });
}

function resetLiabilityForm() {
    $("#liability_id").val("");
    $("#liability_account_name").val("");
    $("#liability_description").val("");
    $("#liability_code_number").val("");
    $("#liability_account_level").val("").prop("disabled", false);
    $("#liability_parent_code").val("").prop("disabled", false);
    $("#liability-parent-group").hide();
    $("#liability-hierarchy-help").hide();
    
    $("#md-liability input, #md-liability select, #md-liability textarea").prop("disabled", false).prop("readonly", function() {
        return $(this).attr('id') === 'liability_code_number';
    });
}

$(document).ready(function () {
    var liabilityTable = $('#tbl_liability').DataTable();
    
    liabilityTable.order([1, 'asc']).draw();
    
    $("#tbl_liability_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-liability'>Nueva cuenta de pasivo</a>");
    
    $('#tbl_liability').on('draw.dt', function () { 
        applyLiabilityIndentation(); 
    });
    
    setTimeout(function() {
        applyLiabilityIndentation();
    }, 500);

    $(document).on("change", "#liability_account_level", function() {
        console.log('Cambio en liability_account_level:', $(this).val());
        
        $("#liability_parent_code").val("");
        $("#liability_code_number").val("");
        
        var selectedLevel = $(this).val();
        if (selectedLevel) {
            loadLiabilityParentAccounts(selectedLevel);
        } else {
            $("#liability-parent-group").hide();
        }
    });
    
    // Cambio en cuenta padre
    $(document).on("change", "#liability_parent_code", function() {
        console.log('Cambio en liability_parent_code:', $(this).val());
        generateLiabilityHierarchicalCode();
    });

    // Nueva cuenta
    $(document).on("click", "#btn-new-liability", function(){ 
        console.log('Nueva cuenta de liability');
        resetLiabilityForm();
        $("#md-liability .modal-title").text("Nueva Cuenta de Pasivo");
        $("#md-liability").modal("show");
    });

    // Guardar cuenta
    $(document).on("click", "#btn-save-liability", function(){
        console.log('Guardar cuenta de liability');
        
        // Validar formulario
        if (!validateLiabilityForm()) {
            return;
        }
        
        // Preparar datos para enviar
        var formData = {
            id: $("#liability_id").val(),
            account_type: $("#liability_account_type").val(),
            account_level: $("#liability_account_level").val(),
            parent_code: $("#liability_parent_code").val(),
            code_number: $("#liability_code_number").val(),
            account_name: $("#liability_account_name").val(),
            description: $("#liability_description").val(),
            softtoken: $("input[name='softtoken']").val(),
            type: 3  // type=3 para save_account en el controlador
        };
        
        console.log('Datos a enviar:', formData);
        
        // Mostrar indicador de carga
        var $saveBtn = $(this);
        var originalText = $saveBtn.html();
        $saveBtn.html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);
        
        $.ajax({
            url: '<?=site_url('accounting/ajax');?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.status == "OK") {
                    alertify.success("Cuenta guardada correctamente");
                    $("#md-liability").modal("hide");
                    $("#tbl_liability").DataTable().ajax.reload(null, false); // false para mantener paginación
                } else {
                    alertify.alert("Error: " + (response.msg || "Error al guardar la cuenta"));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                alertify.alert("Error de conexión con el servidor");
            },
            complete: function() {
                // Restaurar botón
                $saveBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Editar cuenta
    $(document).on("click", ".btn-edit-liability", function(){
        var accountId = $(this).data("id");
        console.log('Editar cuenta liability ID:', accountId);
        
        // Mostrar cargando
        var $editBtn = $(this);
        var originalHtml = $editBtn.html();
        $editBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.ajax({
            url: '<?=site_url('accounting/ajax');?>',
            type: 'POST',
            data: {
                softtoken: $("input[name='softtoken']").val(),
                type: 4,  // type=4 para load_account en el controlador
                id: accountId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Datos de cuenta para editar:', response);
                
                if (response.status == "OK") {
                    resetLiabilityForm();
                    
                    // Llenar formulario con datos existentes
                    $("#liability_id").val(response.row.id);
                    $("#liability_account_name").val(response.row.account_name);
                    $("#liability_description").val(response.row.description);
                    $("#liability_code_number").val(response.row.code_number);
                    
                    // Calcular nivel basado en longitud del código
                    var codeLength = response.row.code_number.toString().length;
                    var accountLevel = codeLength / 2; // 2,4,6,8 dígitos -> 1,2,3,4 niveles
                    
                    $("#liability_account_level").val(accountLevel).prop("disabled", true);
                    $("#md-liability .modal-title").text("Editar Cuenta de Pasivo");
                    
                    // Si es nivel 2-4, cargar y seleccionar cuenta padre
                    if (accountLevel > 1) {
                        loadLiabilityParentAccounts(accountLevel);
                        
                        // Extraer código padre (primeros n-2 dígitos)
                        var parentCode = response.row.code_number.toString().substring(0, codeLength - 2);
                        
                        // Esperar a que se carguen las opciones y seleccionar padre
                        setTimeout(function() {
                            $("#liability_parent_code").val(parentCode).prop("disabled", true);
                        }, 800);
                    }
                    
                    // Mostrar ayuda para edición
                    $("#liability-hierarchy-help").show();
                    
                    $("#md-liability").modal("show");
                } else {
                    alertify.alert("Error: " + (response.msg || "No se pudo cargar la cuenta"));
                }
            },
            error: function() {
                alertify.alert("Error al conectar con el servidor");
            },
            complete: function() {
                // Restaurar botón de edición
                $editBtn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    // Eliminar cuenta
    $(document).on("click", ".btn-delete", function () {
        var accountId = $(this).data("id");
        var $deleteBtn = $(this);
        
        alertify.confirm("¿Está seguro que desea eliminar esta cuenta de pasivo?", function () {
            // Mostrar cargando
            var originalHtml = $deleteBtn.html();
            $deleteBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
            
            $.ajax({
                url: '<?=site_url('accounting/ajax');?>',
                type: 'POST',
                data: {
                    softtoken: $("input[name='softtoken']").val(),
                    type: 2,  // type=2 para delete_account en el controlador
                    id: accountId,
                    account_type: 'liability'
                },
                dataType: 'json',
                success: function (data) {
                    if (data.status == "OK") {
                        alertify.success("Cuenta eliminada correctamente");
                        $("#tbl_liability").DataTable().ajax.reload(null, false);
                    } else {
                        alertify.alert("Error: " + (data.msg || "No se pudo eliminar la cuenta"));
                    }
                },
                error: function() {
                    alertify.alert("Error al conectar con el servidor");
                },
                complete: function() {
                    $deleteBtn.html(originalHtml).prop('disabled', false);
                }
            });
        });
    });
    
    // Cerrar modal - limpiar formulario
    $('#md-liability').on('hidden.bs.modal', function () {
        resetLiabilityForm();
    });
});
</script>