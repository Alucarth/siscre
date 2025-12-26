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
    
    /* ESTILOS NUEVOS PARA SANGRÍAS */
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
                        <table class="table table-hover table-bordered" id="tbl_asset">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 1%"></th>                            
                                    <th style="text-align: center">Código</th>
                                    <th style="text-align: center">Nombre de cuenta</th>
                                    <th style="text-align: center">Descripción</th>                                    
                                </tr>
                            </thead>
                        </table>
                        <?= $tbl_assets; ?>
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
<div class="modal fade" id="md-asset" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="width:800px">
            <div class="modal-header">
                <h5 class="modal-title">Activo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <input type="hidden" name="account_type" id="account_type" value="asset" />
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="" />
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- PRIMERO: Seleccionar nivel -->
                        <div class="form-group">
                            <label>Nivel de la Cuenta:</label>
                            <select class="form-control" name="account_level" id="account_level" required>
                                <option value="">-- Seleccionar nivel --</option>
                                <option value="1">Nivel 1 - Clase Principal (2 dígitos)</option>
                                <option value="2">Nivel 2 - Subclase (4 dígitos)</option>
                                <option value="3">Nivel 3 - Grupo específico (6 dígitos)</option>
                                <option value="4">Nivel 4 - Cuenta detallada (8 dígitos)</option>
                            </select>
                            <small class="form-text text-muted">Seleccione el nivel jerárquico primero</small>
                        </div>
                        
                        <!-- SEGUNDO: Seleccionar cuenta padre (solo para niveles 2-4) -->
                        <div class="form-group" id="parent-group" style="display: none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" name="parent_code" id="parent_code">
                                <option value="">-- Seleccionar cuenta padre --</option>
                            </select>
                            <small class="form-text text-muted" id="parent-help"></small>
                        </div>
                        
                        <!-- TERCERO: Mostrar código generado -->
                        <div class="form-group">
                            <label>Código de Cuenta (Generado automáticamente):</label>
                            <input type="text" class="form-control" name="code_number" id="code_number" readonly />
                            <small class="form-text text-muted">El código se generará después de completar los campos anteriores</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre de Cuenta:</label>
                            <input type="text" class="form-control" name="account_name" id="account_name" required />
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        
                        <!-- Panel de ayuda jerárquico -->
                        <!-- <div class="alert alert-info" id="hierarchy-help">
                            <strong>Guía de niveles:</strong><br>
                            <small>• <strong>Nivel 1</strong>: Cuentas principales (2 dígitos) - No requiere padre<br>
                            • <strong>Nivel 2</strong>: Subclases (4 dígitos) - Requiere padre de Nivel 1<br>
                            • <strong>Nivel 3</strong>: Grupos (6 dígitos) - Requiere padre de Nivel 2<br>
                            • <strong>Nivel 4</strong>: Subcuentas (8 dígitos) - Requiere padre de Nivel 3</small>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-save-asset">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php echo form_open('accounting/ajax', 'id="frmAssetDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
    // Función para cargar cuentas padre según el nivel seleccionado
    function loadParentAccountsByLevel(selectedLevel) {
        var parentGroup = $("#parent-group");
        var parentSelect = $("#parent_code");
        var parentHelp = $("#parent-help");
        var hierarchyHelp = $("#hierarchy-help");
        
        // Mostrar/ocultar grupo de padre según nivel
        if (selectedLevel > 1) {
            parentGroup.show();
            
            // Determinar la longitud requerida del código padre
            var requiredParentLength = 0;
            var helpText = "";
            
            switch(parseInt(selectedLevel)) {
                case 2:
                    requiredParentLength = 2; // Nivel 2 requiere padre de 2 dígitos
                    helpText = "Seleccione una cuenta de Nivel 1 (2 dígitos)";
                    break;
                case 3:
                    requiredParentLength = 4; // Nivel 3 requiere padre de 4 dígitos
                    helpText = "Seleccione una cuenta de Nivel 2 (4 dígitos)";
                    break;
                case 4:
                    requiredParentLength = 6; // Nivel 4 requiere padre de 6 dígitos
                    helpText = "Seleccione una cuenta de Nivel 3 (6 dígitos)";
                    break;
            }
            
            parentHelp.text(helpText);
            parentSelect.prop('required', true);
            
            // Limpiar opciones anteriores
            parentSelect.empty();
            parentSelect.append('<option value="">-- Seleccionar cuenta padre --</option>');
            
            // Actualizar texto de ayuda jerárquica
            hierarchyHelp.html('<strong>Validación jerárquica:</strong><br>' +
                '<small>• <span class="text-success">✓ Nivel seleccionado: ' + selectedLevel + '</span><br>' +
                '• <span class="text-warning">→ Requiere cuenta padre de ' + requiredParentLength + ' dígitos</span></small>');
            
            // Cargar cuentas padre del nivel anterior
            $.ajax({
                url: '<?= site_url("accounting/get_parent_accounts_by_level"); ?>',
                type: 'POST',
                data: {
                    softtoken: $("input[name='softtoken']").val(),
                    account_type: 'asset',
                    required_length: requiredParentLength
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status == "OK") {
                        $.each(response.accounts, function(index, account) {
                            parentSelect.append('<option value="' + account.code_number + '">' + 
                                               account.code_number + ' - ' + account.account_name + '</option>');
                        });
                        
                        // Si solo hay una opción, seleccionarla automáticamente
                        if (response.accounts.length === 1) {
                            parentSelect.val(response.accounts[0].code_number);
                            generateHierarchicalCode();
                        }
                    } else {
                        alertify.alert("Error: " + (response.msg || "No se pudieron cargar las cuentas padre"));
                    }
                },
                error: function() {
                    alertify.alert("Error al conectar con el servidor");
                }
            });
            
        } else {
            // Para nivel 1, ocultar selector de padre
            parentGroup.hide();
            parentSelect.prop('required', false);
            parentSelect.val('');
            
            // Actualizar ayuda jerárquica
            hierarchyHelp.html('<strong>Validación jerárquica:</strong><br>' +
                '<small>• <span class="text-success">✓ Nivel seleccionado: ' + selectedLevel + '</span><br>' +
                '• <span class="text-success">✓ No requiere cuenta padre (nivel raíz)</span></small>');
            
            // Generar código automáticamente para nivel 1
            generateHierarchicalCode();
        }
    }
    
    // Función para generar el código según jerarquía
    function generateHierarchicalCode() {
        var parentCode = $("#parent_code").val();
        var accountLevel = $("#account_level").val();
        var accountType = 'asset';
        
        if (!accountLevel) {
            $("#code_number").val("");
            return;
        }
        
        // Validar que para niveles 2-4 haya una cuenta padre seleccionada
        if (accountLevel > 1 && !parentCode) {
            $("#code_number").val("");
            $("#hierarchy-help").html('<strong class="text-danger">¡Atención!</strong><br>' +
                '<small>• Debe seleccionar una cuenta padre para el nivel ' + accountLevel + '</small>');
            return;
        }
        
        $.ajax({
            url: '<?= site_url("accounting/generate_hierarchical_code"); ?>',
            type: 'POST',
            data: {
                softtoken: $("input[name='softtoken']").val(),
                account_type: accountType,
                parent_code: parentCode,
                account_level: accountLevel
            },
            dataType: 'json',
            success: function(response) {
                if (response.status == "OK") {
                    $("#code_number").val(response.code_number);
                    
                    // Actualizar ayuda con éxito
                    var currentHelp = $("#hierarchy-help").html();
                    $("#hierarchy-help").html(currentHelp + 
                        '<br><span class="text-success">✓ Código generado: ' + response.code_number + '</span>');
                } else {
                    alertify.alert("Error al generar código: " + (response.msg || "Error desconocido"));
                    $("#code_number").val("");
                }
            },
            error: function() {
                alertify.alert("Error al conectar con el servidor");
            }
        });
    }

    function applyAssetIndentation() {
        $('#tbl_asset tbody tr').each(function() {
            var row = $(this);
            var codeCell = row.find('td:eq(1)');
            var accountNameCell = row.find('td:eq(2)');
            var codeNumber = codeCell.text().trim();
            
            // Calcular nivel de sangría basado en la longitud del código
            var codeLength = codeNumber.length;
            var indentLevel = 0;
            
            if (codeLength <= 2) {
                indentLevel = 0; // Nivel 1: 2 dígitos
            } else if (codeLength <= 4) {
                indentLevel = 1; // Nivel 2: 4 dígitos
            } else if (codeLength <= 6) {
                indentLevel = 2; // Nivel 3: 6 dígitos
            } else {
                indentLevel = 3; // Nivel 4+: 8+ dígitos
            }
            
            accountNameCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4');
            accountNameCell.addClass('indent-' + indentLevel);
        });
    }

    $(document).ready(function () {
        $("#tbl_asset_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-asset'>Nueva cuenta de activo</a>");
        $("#tbl_asset_filter input[type='search']").attr("placeholder", "Escriba su búsqueda");
        $("#tbl_asset_filter input[type='search']").removeClass("input-sm");
        
        $('#tbl_asset').on('draw.dt', function () {
            setTimeout(function() {
                applyAssetIndentation();
            }, 100);
        });
        
        setTimeout(function() {
            applyAssetIndentation();
        }, 1000);
        
        // Evento cuando cambia el nivel
        $("#account_level").change(function() {
            var selectedLevel = $(this).val();
            
            // Limpiar campos dependientes
            $("#parent_code").val("");
            $("#code_number").val("");
            
            if (selectedLevel) {
                loadParentAccountsByLevel(selectedLevel);
            } else {
                $("#parent-group").hide();
                $("#hierarchy-help").html('<strong>Guía de niveles:</strong><br>' +
                    '<small>• <strong>Nivel 1</strong>: Cuentas principales (2 dígitos) - No requiere padre<br>' +
                    '• <strong>Nivel 2</strong>: Subclases (4 dígitos) - Requiere padre de Nivel 1<br>' +
                    '• <strong>Nivel 3</strong>: Grupos (6 dígitos) - Requiere padre de Nivel 2<br>' +
                    '• <strong>Nivel 4</strong>: Subcuentas (8 dígitos) - Requiere padre de Nivel 3</small>');
            }
        });
        
        // Evento cuando cambia la cuenta padre
        $("#parent_code").change(function() {
            if ($("#account_level").val() > 1) {
                generateHierarchicalCode();
            }
        });
        
        $("#btn-save-asset").click(function(){
            // Validar campos requeridos
            if (!$("#account_level").val()) {
                alertify.alert("Debe seleccionar el nivel de la cuenta");
                return;
            }
            
            if (!$("#account_name").val()) {
                alertify.alert("Debe ingresar el nombre de la cuenta");
                return;
            }
            
            if (!$("#code_number").val()) {
                alertify.alert("Debe generar un código primero");
                return;
            }
            
            var url = '<?=site_url('accounting/ajax');?>';
            var params = $("#md-asset input, #md-asset select, #md-asset textarea").serialize();
            params += '&softtoken=' + $("input[name='softtoken']").val() + '&type=3';
            
            $.post(url, params, function(data){
                if (data.status == "OK") {
                    $("#md-asset").modal("hide");
                    $("#tbl_asset").DataTable().ajax.reload();
                } else {
                    alertify.alert(data.msg || "Error al guardar");
                }
            }, "json");
        });
        
        $(document).on("click", "#btn-new-asset", function(){            
            // Limpiar formulario
            $("#md-asset .modal-body input[type='text'], #md-asset .modal-body input[type='hidden'], #md-asset .modal-body textarea").val("");
            $("#md-asset #account_level").val("");
            $("#md-asset #parent_code").val("");
            $("#md-asset #code_number").val("");
            $("#parent-group").hide();
            
            // Restablecer ayuda
            $("#hierarchy-help").html('<strong>Guía de niveles:</strong><br>' +
                '<small>• <strong>Nivel 1</strong>: Cuentas principales (2 dígitos) - No requiere padre<br>' +
                '• <strong>Nivel 2</strong>: Subclases (4 dígitos) - Requiere padre de Nivel 1<br>' +
                '• <strong>Nivel 3</strong>: Grupos (6 dígitos) - Requiere padre de Nivel 2<br>' +
                '• <strong>Nivel 4</strong>: Subcuentas (8 dígitos) - Requiere padre de Nivel 3</small>');
            
            $("#md-asset").modal("show");
        });
        
        $(document).on("click", ".btn-edit-asset", function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type: 4,
                id: $(this).data("id")
            };
            
            $.post(url, params, function(data){
                if (data.status == "OK") {
                    // Limpiar formulario primero
                    $("#md-asset .modal-body input[type='text'], #md-asset .modal-body input[type='hidden'], #md-asset .modal-body textarea").val("");
                    
                    // Determinar nivel y padre basado en el código
                    var codeStr = data.row.code_number.toString();
                    var codeLength = codeStr.length;
                    
                    var level = 1;
                    if (codeLength <= 2) level = 1;
                    else if (codeLength <= 4) level = 2;
                    else if (codeLength <= 6) level = 3;
                    else level = 4;
                    
                    // Determinar código padre
                    var parentCode = "";
                    if (codeLength > 2) {
                        parentCode = codeStr.substring(0, codeLength - 2);
                    }
                    
                    // Establecer valores
                    $("#account_level").val(level);
                    $("#id").val(data.row.id);
                    $("#account_name").val(data.row.account_name);
                    $("#description").val(data.row.description);
                    $("#code_number").val(data.row.code_number);
                    
                    // Manejar la lógica según el nivel
                    if (level > 1) {
                        // Cargar cuentas padre apropiadas
                        loadParentAccountsByLevel(level);
                        
                        // Una vez cargadas, seleccionar el padre correcto
                        setTimeout(function() {
                            $("#parent_code").val(parentCode);
                        }, 500);
                    } else {
                        // Para nivel 1, ocultar selector de padre
                        $("#parent-group").hide();
                        $("#hierarchy-help").html('<strong>Validación jerárquica:</strong><br>' +
                            '<small>• <span class="text-success">✓ Nivel seleccionado: ' + level + '</span><br>' +
                            '• <span class="text-success">✓ No requiere cuenta padre (nivel raíz)</span></small>');
                    }
                    
                    // Deshabilitar campos en modo edición
                    $("#code_number").prop("readonly", true);
                    $("#account_level").prop("disabled", true);
                    $("#parent_code").prop("disabled", true);
                    
                    $("#md-asset").modal("show");
                    
                } else {
                    alertify.alert(data.msg || "Error al cargar datos");
                }
            }, "json");
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("¿Está seguro que desea eliminar esta cuenta de activo?", function () {
                var url = $("#frmAssetDelete").attr("action");
                var params = $("#frmAssetDelete").serialize();
                params += '&id=' + $this.attr("data-id") + "&account_type=asset";
                $.post(url, params, function (data) {
                    if (data.status == "OK") {
                        $("#tbl_asset").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>