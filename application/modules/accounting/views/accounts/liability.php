<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7) { text-align: center; }
    .dataTables_info { float:left; }
    .indent-0 { padding-left: 8px !important; }
    .indent-1 { padding-left: 30px !important; }
    .indent-2 { padding-left: 60px !important; }
    .indent-3 { padding-left: 70px !important; }
</style>

<div class="section">
    <div class="card" style="width:100%">
        <div class="card-block">
            <div class="inqbox-content table-responsive">
                <table class="table table-hover table-bordered" id="tbl_liability">
                    <thead>
                        <tr>
                            <th style="width: 1%"></th>                            
                            <th>Código</th>
                            <th>Nombre de cuenta</th>
                            <th>Descripción</th>                                    
                        </tr>
                    </thead>
                </table>
                <?= $tbl_liability; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md-liability" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cuenta de Activo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="liability_id" name="id" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nivel de la Cuenta:</label>
                            <select class="form-control" id="liability_account_level" name="account_level">
                                <option value="">Seleccionar nivel</option>
                                <option value="1">Nivel 1 - Clase Principal (2 dígitos)</option>
                                <option value="2">Nivel 2 - Subclase (4 dígitos)</option>
                                <option value="3">Nivel 3 - Cuenta específica (6 dígitos)</option>
                                <option value="4">Nivel 4 - Cuenta detallada (8 dígitos)</option>
                            </select>
                        </div>
                        <div class="form-group" id="liability_parent_group" style="display:none;">
                            <label>Cuenta Padre:</label>
                            <select class="form-control" id="liability_parent_code" name="parent_code"></select>
                            <small class="text-info" id="liability_parent_hint"></small>
                        </div>
                        <div class="form-group">
                            <label>Código Generado:</label>
                            <input type="text" class="form-control" id="liability_code_number" name="code_number" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" class="form-control" id="liability_account_name" name="account_name" />
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea class="form-control" id="liability_description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-save-liability">Guardar Cuenta</button>
            </div>
        </div>
    </div>
</div>

<script>
var isAdmin = <?php echo ($this->Employee->get_logged_in_employee_info()->role_id == 13 || 17) ? 'true' : 'false'; ?>;
function loadliabilityParents(level) {
var select = $("#liability_parent_code");
    select.empty().append('<option value="">Cargando...</option>');
    
    if (level == 1) {
        $("#liability_parent_group").hide();
        $("#liability_parent_code").val(""); 
        generateliabilityCode();
        return;
    }
    
    if (!level || level === "") {
        $("#liability_parent_group").hide();
        return;
    }
    
    $("#liability_parent_group").show();
    
    var lengths = [];
    if (level == 2) lengths = [2];
    else if (level == 3) {
        lengths = [2, 4];
    } 
    else if (level == 4) lengths = [2, 4, 6];

    var requests = lengths.map(function(len) {
        return $.post('<?=site_url("accounting/get_parent_accounts_by_level");?>', {
            softtoken: $("input[name='softtoken']").val(),
            account_type: 'liability',
            required_length: len
        }, null, "json");
    });

    $.when.apply($, requests).done(function() {
        select.empty().append('<option value="">Seleccionar cuenta padre</option>');
        var args = (lengths.length === 1) ? [arguments] : arguments;
        
        $.each(args, function(i, response) {
            var data = response[0];
            if(data && data.status == "OK") {
                $.each(data.accounts, function(j, a) {
                    var label = (a.code_number.length == 2) ? " " : " ";
                    if(a.code_number.length == 6) label = " ";
                    select.append('<option value="'+a.code_number+'">'+label + a.code_number+' - '+a.account_name+'</option>');
                });
            }
        });
    });
}

function generateliabilityCode() {
    var parentCode = $("#liability_parent_code").val();
    var targetLevel = $("#liability_account_level").val();

    $.post('<?=site_url("accounting/generate_hierarchical_code");?>', {
        softtoken: $("input[name='softtoken']").val(),
        account_type: 'liability',
        parent_code: parentCode,
        account_level: targetLevel
    }, function(res) {
        if(res.status == "OK") {
            $("#liability_code_number").val(res.code_number);
        }
    }, "json");
}

function applyliabilityIndentation() {
    $('#tbl_liability tbody tr').each(function() {
        var row = $(this);
        var codeNumber = row.find('td:eq(1)').text().trim();
        var accountNameCell = row.find('td:eq(2)');
        var level = (codeNumber.length <= 2) ? 0 : (codeNumber.length <= 4 ? 1 : (codeNumber.length <= 6 ? 2 : 3));
        accountNameCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4').addClass('indent-' + level);
    });
}

function resetliabilityForm() {
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

function validateliabilityForm() {
    var accountLevel = $("#liability_account_level").val();
    var parentCode = $("#liability_parent_code").val();
    var codeNumber = $("#liability_code_number").val();
    var accountName = $("#liability_account_name").val();
    
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
    
    if (accountLevel > 1 && !parentCode) {
        alertify.alert("Para nivel " + accountLevel + " debe seleccionar una cuenta padre");
        $("#liability_parent_code").focus();
        return false;
    }
    
    return true;
}

$(document).ready(function() {
    $('#tbl_liability').on('draw.dt', function () {
        $('#tbl_liability tbody tr').each(function() {
            var code = $(this).find('td:eq(1)').text().trim();
            var indent = (code.length <= 2) ? 0 : (code.length <= 4 ? 1 : (code.length <= 6 ? 2 : 3));
            $(this).find('td:eq(2)').addClass('indent-' + indent);
        });
    });

    $("#tbl_liability_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-liability' style='margin-right:10px'>Nueva cuenta</a>");
    
    $(document).on("change", "#liability_account_level", function() { loadliabilityParents($(this).val()); });
    $(document).on("change", "#liability_parent_code", function() { generateliabilityCode(); });
    
    $(document).on("click", "#btn-new-liability", function() {
        $("#md-liability input, #md-liability textarea").val("");
        $("#liability_account_level").val("").prop("disabled", false);
        $("#liability_parent_group").hide();
        $("#liability_parent_hint").text("");
        $("#md-liability").modal("show");
    });

    $(document).on("click", "#btn-save-liability", function () {
        var id = $("#liability_id").val();
        var code_number = $("#liability_code_number").val();
        var account_name = $("#liability_account_name").val();
        var description = $("#liability_description").val();
        var account_level = $("#liability_account_level").val();
        var parent_code = $("#liability_parent_code").val();
        var $btn = $(this);
        
        if ($btn.prop('disabled')) return; // EVITA EJECUCIÓN MÚLTIPLE
        $btn.prop('disabled', true);

        if (account_name == "" || code_number == "") {
            alertify.alert("Por favor complete los campos obligatorios (Nombre y Código)");
            return;
        }

        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

        $.ajax({
            url: '<?=site_url('accounting/ajax');?>',
            type: 'POST',
            data: {
                softtoken: $("input[name='softtoken']").val(),
                type: 3,
                id: id,
                code_number: code_number,
                account_name: account_name,
                description: description,
                account_type: 'liability',
                account_level: account_level,
                parent_code: parent_code,
                account_map: code_number
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == "OK") {
                    alertify.success("Cuenta de activo guardada correctamente");
                    $("#md-liability").modal('hide');
                    $("#tbl_liability").DataTable().ajax.reload(null, false);
                    
                    $("#liability_id").val("");
                    $("#liability_account_name").val("");
                    $("#liability_description").val("");
                    $("#liability_account_level").val("").trigger('change');
                } else {
                    alertify.alert("Error: " + (data.msg || "No se pudo guardar la cuenta"));
                }
            },
            error: function() {
                alertify.alert("Error de conexión con el servidor");
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    $(document).on("click", ".btn-edit-liability", function() {
        var id = $(this).data("id");
        $.post('<?=site_url('accounting/ajax');?>', {
            softtoken: $("input[name='softtoken']").val(), 
            type: 4, 
            id: id
        }, function(data) {
            if (data.status == "OK") {
                $("#liability_id").val(data.row.id);
                $("#liability_account_name").val(data.row.account_name);
                $("#liability_description").val(data.row.description);
                $("#liability_code_number").val(data.row.code_number);
                $("#liability_account_level").val(data.row.code_number.length / 2).prop("disabled", true);
                $("#liability_parent_code").val(data.row.parent_code).prop("disabled", true);
                $("#liability_parent_group").hide(); 
                $("#md-liability").modal("show");
            }
        }, "json");
        //if (isAdmin) { $("#liability_code_number").prop("readonly", false); }
    });

    $(document).on("click", ".btn-delete", function () {
        var accountId = $(this).data("id");
        var $deleteBtn = $(this);
        
        alertify.confirm("¿Está seguro que desea eliminar esta cuenta de activos?", function () {
            var originalHtml = $deleteBtn.html();
            $deleteBtn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
            
            $.ajax({
                url: '<?=site_url('accounting/ajax');?>',
                type: 'POST',
                data: {
                    softtoken: $("input[name='softtoken']").val(),
                    type: 2,
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
});
</script>