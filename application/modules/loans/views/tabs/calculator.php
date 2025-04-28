<script src="http://momentjs.com/downloads/moment.js"></script>


<div style="text-align: center">
    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
    <ul id="error_message_box"></ul>
</div>

<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
    Aplicar cantidad:
    </label>
    <div class="col-sm-2">
        <input type="hidden" id="amount" name="amount" value="<?=$loan_info->loan_amount;?>" />
        <?php
        echo form_input(
                array(
                    'name' => 'apply_amount',
                    'id' => 'apply_amount',
                    'value' => $loan_info->apply_amount,
                    'class' => 'form-control',
                    'type' => 'number',
                    'step' => 'any',
                )
        );
        ?>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Tasa de interés:
    </label>
    <div class='col-sm-2'>
        <div class="input-group">
            <input type="text" class="form-control" name="interest_rate" id="interest_rate" value="<?= $loan_info->interest_rate; ?>" />
            <span class="input-group-addon input-group-append"><span class="input-group-text">%</span></span>
        </div>
    </div>
    <div class="col-sm-2">
        
        <div id="div_ineterest_type_label">
            <span id="sp-interest-type-label">
            <?php echo isset($interest_types[$loan_info->interest_type]) ? $interest_types[$loan_info->interest_type] : 'Elija una opción'; ?>
            </span>
            (<a href="javascript:void(0)" id="btn-change-interest-type">Cambiar</a>)
        </div>
        <div style="display:none;" id="div_interest_type">
            <select name="interest_type" id="DTE_Field_interest_type" class="form-control">
                <?php foreach( $interest_types as $key => $name ): ?>
                    <option value="<?=$key;?>" <?=$loan_info->interest_type==$key ? 'selected="selected"' : ''?>><?=$name;?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <script>
            $(document).ready(function(){
                $("#btn-change-interest-type").click(function(){
                    $("#div_interest_type").show();
                    $("#div_ineterest_type_label").hide();                    
                });
            });
        </script>
        
    </div>
</div>

<div id="div_terms">
    <div class="hr-line-dashed"></div>
    <div class="form-group row">
        <label class="col-sm-2 control-label text-xs-right" style="color:red">
            <?= $this->lang->line('loan_type_term'); ?>:
        </label>
        <div class="col-sm-2">
            <input type="text" name="term" id="term" class="form-control" value="<?= $loan_info->payment_term; ?>" />
        </div>
        <div class="col-sm-2">
            <select class="form-control" name="term_period" id="term_period">
                <option value="day" <?= $loan_info->term_period === "day" ? 'selected="selected"' : ''; ?>>Día</option>
                <option value="week" <?= $loan_info->term_period === "week" ? 'selected="selected"' : ''; ?>>Semana</option>
                <option value="month" <?= $loan_info->term_period === "month" ? 'selected="selected"' : ''; ?>>Mes</option>
                <option value="biweekly" <?= $loan_info->term_period === "biweekly" ? 'selected="selected"' : ''; ?>>Mes (Quincenal)</option>
                <option value="month_weekly" <?= $loan_info->term_period === "month_weekly" ? 'selected="selected"' : ''; ?>>Mes (Semanal)</option>
                <option value="year" <?= $loan_info->term_period === "year" ? 'selected="selected"' : ''; ?>>Año</option>
            </select>
        </div>          
    </div>
    
    <script>
        $(document).ready(function(){
            $("#term_period").change(function(){
                if ( $(this).val() == 'biweekly' )
                {
                    $("#sp-term-description").html("La tasa de interés se aplica todos los meses, pero el cliente debe pagar dos veces en un mes");
                    $("#div_explain").slideDown();
                }
                else if ( $(this).val() == 'month_weekly' )
                {
                    $("#sp-term-description").html("La tasa de interés se aplica todos los meses, pero el cliente debe pagar todas las semanas.");
                    $("#div_explain").slideDown();
                }
                else
                {
                    $("#div_explain").slideUp();                    
                }
                    
            });
        });
    </script>
    
</div>

<div id="div_explain" style="display:none;">
    <div class="hr-line-dashed"></div>
    <div class="form-group row">
        <label class="col-lg-2 control-label text-xs-right">
            Descripción del término:
        </label>
        <div class="col-lg-10">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <span id="sp-term-description"></span>
            </div>
        </div>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right" style="color:red">
        Primera fecha de pago:
    </label>
    <div class='col-sm-2'>
        <div class="input-group date">
            <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>                                        
            <input type="text" class="form-control" autocomplete="nope" name="start_date" id="start_date" value="<?=($loan_info->payment_start_date != '') ? date($this->config->item('date_format'), $loan_info->payment_start_date) : ''?>" />
        </div>
    </div>
    <?php if (is_plugin_active("holidays")): ?>
    <?php else: ?>
    <div class="col-lg-2">
        <label>
            <input type="checkbox" id="exclude_sundays" name="exclude_sundays" <?=$loan_info->exclude_sundays ? 'checked="checked"' : ''?> />
            No incluir domingos
        </label>
    </div>
    <?php endif;?>
</div>

<?php if (is_plugin_active("holidays")): ?>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Excluir (Programar):
    </label>
    <div class='col-sm-2'>
        <select class="form-control" multiple="multiple" name="loan[exclude_schedule][]">
            <option value="monday" <?=in_array("monday", $exclude_schedules) ? 'selected="selected"' : '';?>>Lunes</option>
            <option value="tuesday" <?=in_array("tuesday", $exclude_schedules) ? 'selected="selected"' : '';?>>Martes</option>
            <option value="wednesday" <?=in_array("wednesday", $exclude_schedules) ? 'selected="selected"' : '';?>>Miércoles</option>
            <option value="thursday" <?=in_array("thursday", $exclude_schedules) ? 'selected="selected"' : '';?>>Jueves</option>
            <option value="friday" <?=in_array("friday", $exclude_schedules) ? 'selected="selected"' : '';?>>Viernes</option>
            <option value="saturday" <?=in_array("saturday", $exclude_schedules) ? 'selected="selected"' : '';?>>Sábado</option>
            <option value="sunday" <?=in_array("sunday", $exclude_schedules) ? 'selected="selected"' : '';?>>Domingo</option>
            <option disabled="">------------</option>
            <option value="holidays" <?=in_array("holidays", $exclude_schedules) ? 'selected="selected"' : '';?>>Días festivos</option>
        </select>
    </div>
</div>
<?php endif; ?>

<!--<//?php if ( $loan_info->interest_type == 'pago_excel' ): ?>-->
<div class="form-group row">
    <label class="col-lg-2 control-label text-xs-right">
        Ahorro:
    </label>
    <div class="col-lg-2">
        <div class="input-group">
            
            <input type="text" name="operating_expenses" id="operating_expenses" class="form-control" value="<?= $operative_expenses > 0 ? $operative_expenses : 0?>" />
            <span class="input-group-addon input-group-append"><span class="input-group-text">Bs.</span></span>
        </div>
    </div>
</div>
<!--<//?php endif; ?>-->

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-lg-2 control-label text-xs-right">
        Sanciones por pago atrasado:
    </label>
    <div class="col-lg-2">
        <div class="input-group">
            <input type="text" name="penalty_value" id="penalty_value" class="form-control" value="<?= $loan_info->penalty_value > 0 ? $loan_info->penalty_value : 0?>" />
            <?php if ( $loan_info->penalty_type == 'amount' ): ?>
            <span class="input-group-addon input-group-append" title="Click to toggle type"><span class="input-group-text" id="btn-toggle-penalty-type">Amt</span></span>
            <?php else: ?>
            <span class="input-group-addon input-group-append" title="Click to toggle type"><span class="input-group-text" id="btn-toggle-penalty-type">%</span></span>
            <?php endif; ?>
            <input type="hidden" id="hid-penalty-type" name="penalty_type" value="<?=$loan_info->penalty_type != '' ? $loan_info->penalty_type : 'percentage';?>" />
        </div>
    </div>
    
    <div class="col-lg-2">
        <label>
            <a href="javascript:void(0)" id="btn-add-grace-period">Agregar período de gracia</a>
        </label>
    </div>
    
</div>

<script>
    $(document).ready(function(){
        $("#btn-add-grace-period").click(function(){
            $("#md-grace-periods").modal("show");
        });
        
        $("#btn-toggle-penalty-type").click(function(){
            if ( $(this).html() == '%' )
            {
                $(this).html("Amt");
                $("#hid-penalty-type").val("amount");
            }
            else
            {
                $(this).html("%");
                $("#hid-penalty-type").val("percentage");
            }
        });
    });
</script>

<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Tarifas adicionales totales:
    </label>
    <div class="col-sm-5">
        <span id="sp-total-additional-fees">0.00</span>
        <input type="hidden" id="hid_total_additiona_fees" value="0" />
        
        <label style="display: inline-block; margin-left: 20px"><input type="checkbox" id="exclude_additional_fees" name="exclude_additional_fees" <?=$loan_info->exclude_additional_fees ? 'checked="checked"' : ''?> /> Marcar como pagado</label>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Cantidad a pagar:
    </label>
    <div class="col-sm-2">
        <div id="loan-total-amount"><?=$loan_info->apply_amount > 0 ? $loan_info->apply_amount: '0.00'?></div>
    </div>
</div>


<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        &nbsp;
    </label>
    <div class="col-sm-4">
        <button class="btn btn-primary" type="button" id="btn-loan-calculator">Calcular</button>
    </div>
</div>
<div class="hr-line-dashed"></div>

<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right"> &nbsp; </label>
    <div class="col-sm-10">
        <div id="div-payment-scheds" style="overflow: auto"></div>
    </div>
</div>

<div class="modal in fade" role="modal" tabindex="-1" id="md-grace-periods">
    <div class="modal-dialog">
        <div class="modal-content" role="modalContent">
            <div class="modal-header">
                Periodo de gracia
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>                  
            </div>
            <div class="modal-body" role="modalAcc">
                
                <div class="alert alert-info" style="font-size: 10px">
                    <span class="fa fa-info-circle"></span>
                    Definición de términos<br/>
                    Periodo también conocido como fecha de recolección. Por ejemplo, selecciona el período 1 y el período de gracia se aplicará a la primera fecha de pago.<br/>
                    Cant. o cantidad: se utilizará para ampliar la fecha de recogida.<br/>
                    Unidad - se basa en días, semanas, meses o años<br/>
                </div>
                
                <button class="btn btn-primary" id="btn-add-grace-period-row" type="button">Añadir fila</button>
                <button class="btn btn-warning" id="btn-delete-grace-period-row" type="button" disabled="disabled">Eliminar fila</button>
                
                <table class="table table-bordered" id="tbl-grace-periods">
                    <thead>
                        <tr>
                            <th style="text-align: center">&nbsp;</th>
                            <th style="text-align: center">Periodo</th>
                            <th style="text-align: center">Cant</th>
                            <th style="text-align: center">Unidad</th>
                        </tr>
                        <tr id="tr-new-row" style="display:none">
                            <td style="text-align:center"><input type="checkbox" class="chk-delete" /></td>
                            <td style="text-align: center;">
                                <select class="form-control no-select2" name="period[]">
                                    <?php if ( $period_cnt > 0 ): ?>
                                        <?php for( $i=1; $i <= $period_cnt; $i++ ): ?>
                                        <option><?=$i;?></option>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <?php for( $i=1; $i <= 30; $i++ ): ?>
                                        <option><?=$i;?></option>
                                        <?php endfor; ?>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td style="text-align: center; width: 150px">
                                <input type="text" style="text-align: center" class="form-control" name="qty[]" />
                            </td>
                            <td style="text-align: center">
                                <select class="form-control no-select2" name="unit[]">
                                    <option>Días</option>
                                    <option>Semanas</option>
                                    <option>Meses</option>
                                    <option>Años</option>
                                </select>
                            </td>
                        </tr>
                    </thead>
                    <tbody>                        
                        <?php foreach ( $grace_periods as $grace_period ): ?>
                            <tr>
                                <td style="text-align:center"><input type="checkbox" class="chk-delete"/></td>
                                <td style="text-align: center">
                                    <?=$grace_period["period"];?>
                                    <input type="hidden" name="period[]" value="<?=$grace_period["period"];?>">
                                </td>
                                <td style="text-align: center">
                                    <?=$grace_period["qty"];?>
                                    <input type="hidden" name="qty[]" value="<?=$grace_period["qty"];?>">
                                </td>
                                <td style="text-align: center">
                                    <?=$grace_period["unit"];?>
                                    <input type="hidden" name="unit[]" value="<?=$grace_period["unit"];?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="btn-close"><?= $this->lang->line("common_close"); ?></button>
                <button type="button" class="btn btn-primary" id="btn-save-grace_period">Aplicar</button>
            </div>
        </div>
    </div>
</div>

<textarea id="grace_period_json" name="grace_periods_json" style="display:none;"></textarea>

<script>
    
    $(document).ready(function(){
        
        $("#btn-delete-grace-period-row").click(function(){
            $(".chk-delete").each(function(){
                if ( $(this).is(":checked") )
                {
                    $(this).parent().parent().remove();
                }
            });
        });
        
        $(document).on("click", ".chk-delete", function(){
            var has_check = false;
            $(".chk-delete").each(function(){
                if ( $(this).is(":checked") && !has_check )
                {
                    has_check = true;
                }
            });
            
            if ( has_check )
            {
                $("#btn-delete-grace-period-row").prop("disabled", false);
            }
            else
            {
                $("#btn-delete-grace-period-row").prop("disabled", true);                
            }
        });
        
        $("#btn-save-grace_period").click(function(e){
            e.preventDefault();
            
            var url = '<?=site_url('loans/ajax');?>';
            var params = $("#tbl-grace-periods tbody input, #tbl-grace-periods tbody select").serialize();
            
            params += '&softtoken=' + $("input[name='softtoken']").val();
            params += '&ajax_type=5';
            params += '&loan_id=<?=$loan_info->loan_id;?>';
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    if ( data.ret_grace_periods != '' )
                    {
                        $("#grace_period_json").val( data.ret_grace_periods );
                    }
                    alertify.alert("¡El período de gracia se ha guardado correctamente!", function(){
                        $("#md-grace-periods").modal("hide");
                        $("#btn-loan-calculator").trigger("click");
                    });
                }
            }, "json");
        });
        
        $("#btn-add-grace-period-row").click(function(){
           $("#tbl-grace-periods tbody").append( "<tr>" + $("#tr-new-row").html() + "</tr>" );
           $("#tbl-grace-periods tbody input, #tbl-grace-periods tbody select").prop("disabled", false);
           $("#tbl-grace-periods tbody input").prop("readonly", false);
        });
        
        $("#exclude_additional_fees").click(function(){
            calculate_amount();
        });
    });
    
    function check_term_field()
    {
        if ( $("#DTE_Field_interest_type").val() == "outstanding_interest" ||                 
                $("#DTE_Field_interest_type").val() == "one_time")
        {
            $("#term").val("1");
            $("#term").prop("disabled", true);
            $("#term_period").prop("disabled", true);
            $("#div_terms").slideUp();
        }
        else
        {
            $("#term").prop("disabled", false);
            $("#div_terms").slideDown();
        }
    }
    
    function calculate_amount()
    {
        compute_additional_fees();
        
        var url = '<?= site_url('loans/ajax'); ?>';
        var params = {
            softtoken: $("input[name='softtoken']").val(),
            InterestType: $("#DTE_Field_interest_type").val(),
            NoOfPayments: $("#term").val(),
            ApplyAmt: parseFloat($("#apply_amount").val()),
            TotIntRate: $('#interest_rate').val(),
            InstallmentStarted: $('#start_date').val(),
            PayTerm: $("#term_period").val(),
            ajax_type:1,
            exclude_sundays: $("#exclude_sundays").is(":checked") ? 1 : 0,
            operating_expenses: $("#operating_expenses").val(),
            penalty_value: $("#penalty_value").val(),
            penalty_type: $("#hid-penalty-type").val(),
            loan_id: '<?=$loan_info->loan_id;?>',
            grace_period_json: $("#grace_period_json").val(),
	        additional_fees: parseFloat($("#hid_total_additiona_fees").val()),
            exclude_schedules: $("select[name='loan[exclude_schedule][]']").val(),
            exclude_additional_fees: $("#exclude_additional_fees").is(":checked") ? 1 : 0
        };
        $.post(url, params, function(data){
            if ( data.status == "OK" )
            {
                $("#loan-total-amount").html( data.formatted_total_amount );
                $("#amount").val( data.total_amount);
                $("#div-payment-scheds").html( data.table_scheds );                
            }
        }, "json");
    }
    
    $(document).ready(function(){
        
        check_term_field();
        
        $(document).on('change', "#DTE_Field_interest_type", function(){
            $("#DTE_Field_InterestType").val($(this).val());
            check_term_field();
        });
        
        <?php if ( $loan_info->loan_id > 0 ): ?>
            calculate_amount();
        <?php endif; ?>
        
        $(document).on('click', '#btn-loan-calculator', function () {

            if ($("#start_date").val() == '')
            {
                alertify.alert("La fecha de inicio es un campo obligatorio");
                return false;
            }
            
            if ($("#term").val() == '')
            {
                alertify.alert("El término es un campo obligatorio");
                return false;
            }            
            
            calculate_amount();            
        });
    });
    
    function addCommas(nStr)
    {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }
</script>