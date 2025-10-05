<?php $this->load->view("partial/header"); ?>

<style>
    .account-row { margin-bottom: 10px; }
    .totals-box { 
        background-color: #f8f9fa; 
        padding: 15px; 
        border-radius: 5px; 
        margin-top: 20px;
    }
    .debit-credit-box {
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
    }
</style>

<div class="title-block">
    <h3 class="title"> 
        Crear Nuevo Comprobante Contable
    </h3>
    <p class="title-description">
        Registrar un nuevo comprobante contable
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-block">
                    <form id="voucher-form">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Número de Comprobante</label>
                                    <input type="text" class="form-control" name="voucher_id" 
                                           value="<?php echo $next_voucher_id; ?>" 
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="text" class="form-control datepicker" name="voucher_date" 
                                           value="<?php echo date($this->config->item('date_format')); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" class="form-control" name="description" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Método de Pago</label>
                                <select class="form-control" name="payment_methods" required>
                                    <option value="">Seleccionar</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="QR">QR</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="deposito">Deposito</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h4>Detalles del Comprobante</h4>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success btn-sm" id="add-row">
                                    <i class="fa fa-plus"></i> Agregar Cuenta
                                </button>
                            </div>
                        </div>
                        
                        <div id="account-rows">
                            <div class="account-row">
                                <div class="debit-credit-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Cuenta Contable</label>
                                                <select class="form-control account-select" name="accounts[]" required>
                                                    <option value="">Seleccionar cuenta</option>
                                                    <?php foreach ($accounts as $account): ?>
                                                    <option value="<?php echo $account->id; ?>">
                                                        <?php echo $account->code_number . ' - ' . $account->account_name; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Debe</label>
                                                <input type="number" class="form-control debit-amount" name="debits[]" 
                                                       step="0.01" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Haber</label>
                                                <input type="number" class="form-control credit-amount" name="credits[]" 
                                                       step="0.01" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <input type="text" class="form-control" name="descriptions[]">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm remove-row" style="margin-top: 8px;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="totals-box">
                            <div class="row">
                                <div class="col-md-2 offset-md-8">
                                    <div class="form-group">
                                        <label>Total Debe</label>
                                        <input type="text" class="form-control" name="total_debit" id="total-debit" 
                                               value="0.00" readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Total Haber</label>
                                        <input type="text" class="form-control" name="total_credit" id="total-credit" 
                                               value="0.00" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="balance-alert" class="alert alert-danger" style="display: none;">
                                        Los totales de debe y haber no coinciden
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="save-voucher">
                                <i class="fa fa-save"></i> <span class="button-text">Guardar Comprobante</span>
                            </button>
                            <a href="<?php echo site_url('accounting/transactions'); ?>" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar datepicker
    $('.datepicker').datepicker({
        format: '<?php echo $this->config->item('date_format') == 'd/m/Y' ? 'dd/mm/yyyy' : 'yyyy-mm-dd'; ?>',
        autoclose: true
    });
    
    // Plantilla para nuevas filas
    var rowTemplate = `
    <div class="account-row">
        <div class="debit-credit-box">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cuenta Contable</label>
                        <select class="form-control account-select" name="accounts[]" required>
                            <option value="">Seleccionar cuenta</option>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account->id; ?>">
                                <?php echo $account->code_number . ' - ' . $account->account_name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Debe</label>
                        <input type="number" class="form-control debit-amount" name="debits[]" 
                               step="0.01" min="0" value="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Haber</label>
                        <input type="number" class="form-control credit-amount" name="credits[]" 
                               step="0.01" min="0" value="0">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" class="form-control" name="descriptions[]">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm remove-row" style="margin-top: 8px;">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    
    // Agregar nueva fila
    $('#add-row').click(function() {
        $('#account-rows').append(rowTemplate);
        initRowEvents($('#account-rows .account-row').last());
    });
    
    // Eliminar fila
    $(document).on('click', '.remove-row', function() {
        if ($('.account-row').length > 1) {
            $(this).closest('.account-row').remove();
            calculateTotals();
        } else {
            alert('Debe haber al menos una cuenta en el comprobante.');
        }
    });
    
    // Calcular totales cuando cambian los valores
    $(document).on('input', '.debit-amount, .credit-amount', function() {
        calculateTotals();
    });
    
    // Inicializar eventos para la fila inicial
    initRowEvents($('.account-row'));
    
    // Validar y enviar formulario
    $('#voucher-form').submit(function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            var formData = $(this).serialize();
            
            $.ajax({
                url: '<?php echo site_url("accounting/voucher_save"); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function(xhr) {
                    // Agregar headers CSRF para la solicitud AJAX
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val());
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                },
                success: function(response) {
                    if (response.status == 'OK') {
                        alert('Comprobante guardado correctamente.');
                        window.location.href = '<?php echo site_url("accounting/transactions"); ?>';
                    } else {
                        alert('Error: ' + response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 403) {
                        alert('Error de seguridad: Token CSRF inválido. Por favor, recarga la página e intenta nuevamente.');
                    } else {
                        alert('Error al guardar el comprobante: ' + error);
                    }
                    console.error('Error details:', xhr.responseText);
                }
            });
        }
    });
    
    function initRowEvents(row) {
        // Asegurar que solo un campo (debe o haber) tenga valor
        row.find('.debit-amount, .credit-amount').on('input', function() {
            var debit = $(this).closest('.account-row').find('.debit-amount');
            var credit = $(this).closest('.account-row').find('.credit-amount');
            
            if ($(this).hasClass('debit-amount') && $(this).val() > 0) {
                credit.val(0);
            } else if ($(this).hasClass('credit-amount') && $(this).val() > 0) {
                debit.val(0);
            }
        });
    }
    
    function calculateTotals() {
        var totalDebit = 0;
        var totalCredit = 0;
        
        $('.debit-amount').each(function() {
            totalDebit += parseFloat($(this).val()) || 0;
        });
        
        $('.credit-amount').each(function() {
            totalCredit += parseFloat($(this).val()) || 0;
        });
        
        $('#total-debit').val(totalDebit.toFixed(2));
        $('#total-credit').val(totalCredit.toFixed(2));
        
        // Mostrar alerta si no hay balance
        if (totalDebit.toFixed(2) !== totalCredit.toFixed(2)) {
            $('#balance-alert').show();
        } else {
            $('#balance-alert').hide();
        }
    }
    
    function validateForm() {
        // Verificar que al menos una cuenta tenga monto
        var hasAmount = false;
        $('.account-row').each(function() {
            var debit = parseFloat($(this).find('.debit-amount').val()) || 0;
            var credit = parseFloat($(this).find('.credit-amount').val()) || 0;
            
            if (debit > 0 || credit > 0) {
                hasAmount = true;
            }
        });
        
        if (!hasAmount) {
            alert('Al menos una cuenta debe tener un monto en debe o haber.');
            return false;
        }
        
        // Verificar que los totales coincidan
        var totalDebit = parseFloat($('#total-debit').val()) || 0;
        var totalCredit = parseFloat($('#total-credit').val()) || 0;
        
        if (totalDebit.toFixed(2) !== totalCredit.toFixed(2)) {
            alert('Los totales de debe y haber deben ser iguales.');
            return false;
        }
        
        return true;
    }
});
</script>

<?php $this->load->view("partial/footer"); ?>