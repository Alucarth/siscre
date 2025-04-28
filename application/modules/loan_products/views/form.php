<?php $this->load->view("partial/header"); ?>
<link href="css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open('loan_products/save/' . $loan_product_info->id, array('id' => 'frmLoanProducts', 'class' => 'form-horizontal')); ?>

<style>
    #drop-target {
        border: 10px dashed #999;
        text-align: center;
        color: #999;
        font-size: 20px;
        width: 600px;
        height: 300px;
        line-height: 300px;
        cursor: pointer;
    }

    #drop-target.dragover {
        background: rgba(255, 255, 255, 0.4);
        border-color: green;
    }

    .kl-plugin {
        display: inline-block;
        padding: 2px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background-color: #f3e798;
    }

    .autocomplete-suggestions {
        overflow: auto;
    }
</style>


<div class="title-block">
    <h3 class="title"> 

        <span style="float:left"><?php if ($loan_product_info->id > 0): ?>
                Actualizar producto de préstamo
            <?php else: ?>
                Nuevo producto de préstamo
            <?php endif; ?></span>
    </h3>
    
    <div class="clearfix"></div>
    
    <p class="title-description">
        Información básica del producto de préstamo
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">


                    <div class="inqbox float-e-margins">            
                        <div class="inqbox-content">
                            <div>

                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Nombre del producto:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="product_name" name="product_name" value="<?= $loan_product_info->product_name; ?>" placeholder="Escriba aquí el nombre del producto" autocomplete="not" />                            
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Descripción:
                                    </label>
                                    <div class="col-lg-10">
                                        <textarea id="description" name="description" class="form-control" style="height: 120px;"><?= $loan_product_info->description; ?></textarea>
                                    </div>
                                </div>


                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-sm-2 text-xs-right control-label">
                                        Tasa de interés:
                                    </label>
                                    <div class='col-sm-2'>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="interest_rate" id="interest_rate" value="<?= $loan_product_info->interest_rate; ?>" autocomplete="not" />
                                            <span class="input-group-addon input-group-append">
                                                <span class="input-group-text">%</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">

                                        <div id="div_ineterest_type_label">
                                            <?php echo isset($interest_types[$loan_product_info->interest_type]) ? $interest_types[$loan_product_info->interest_type] : 'Pago_excel'; ?>
                                            (<a href="javascript:void(0)" id="btn-change-interest-type">Cambiar</a>)
                                        </div>
                                        <div style="display:none;" id="div_interest_type">
                                            <select name="interest_type" id="DTE_Field_interest_type" class="form-control">
                                                <?php foreach ($interest_types as $key => $name): ?>
                                                    <option value="<?= $key; ?>" <?= $loan_product_info->interest_type == $key ? 'selected="selected"' : '' ?>><?= $name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <script>
                                            $(document).ready(function () {
                                                $("#btn-change-interest-type").click(function () {
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
                                        <label class="col-sm-2 text-xs-right control-label">
                                            <?= $this->lang->line('loan_type_term'); ?>:
                                        </label>
                                        <div class="col-sm-2">
                                            <input type="text" name="term" id="term" class="form-control" value="<?= $loan_product_info->term; ?>" autocomplete="not" />
                                        </div>
                                        <div class="col-sm-2">
                                            <select class="form-control" name="term_period" id="term_period">
                                                <option value="day" <?= $loan_product_info->term_period === "day" ? 'selected="selected"' : ''; ?>>Día</option>
                                                <option value="week" <?= $loan_product_info->term_period === "week" ? 'selected="selected"' : ''; ?>>Semana</option>
                                                <option value="month" <?= $loan_product_info->term_period === "month" ? 'selected="selected"' : ''; ?>>Mes</option>
                                                <option value="biweekly" <?= $loan_product_info->term_period === "biweekly" ? 'selected="selected"' : ''; ?>>Mes (Quincenal)</option>
                                                <option value="month_weekly" <?= $loan_product_info->term_period === "month_weekly" ? 'selected="selected"' : ''; ?>>Mes (Semanal)</option>
                                                <option value="year" <?= $loan_product_info->term_period === "year" ? 'selected="selected"' : ''; ?>>Año</option>
                                            </select>
                                        </div>          
                                    </div>

                                    <script>
                                        $(document).ready(function () {
                                            $("#term_period").change(function () {
                                                if ($(this).val() == 'biweekly')
                                                {
                                                    $("#sp-term-description").html("La tasa de interés se aplica todos los meses, pero el cliente debe pagar dos veces en un mes");
                                                    $("#div_explain").slideDown();
                                                } else if ($(this).val() == 'month_weekly')
                                                {
                                                    $("#sp-term-description").html("La tasa de interés se aplica todos los meses, pero el cliente debe pagar todas las semanas.");
                                                    $("#div_explain").slideDown();
                                                } else
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
                                        <label class="col-lg-2 text-xs-right control-label">
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
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Sanciones por pago atrasado:
                                    </label>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <input type="text" name="penalty_value" id="penalty_value" class="form-control" value="<?= $loan_product_info->penalty_value > 0 ? $loan_product_info->penalty_value : 0 ?>" />
                                            <?php if ($loan_product_info->penalty_type == 'amount'): ?>
                                                <span class="input-group-addon input-group-append" title="Click to toggle type">
                                                    <span class="input-group-text" id="btn-toggle-penalty-type">Amt</span>                                                    
                                                </span>
                                            <?php else: ?>
                                                <span class="input-group-addon input-group-append" title="Click to toggle type"><span class="input-group-text" id="btn-toggle-penalty-type">%</span></span>
                                            <?php endif; ?>
                                            <input type="hidden" id="hid-penalty-type" name="penalty_type" value="<?= $loan_product_info->penalty_type != '' ? $loan_product_info->penalty_type : 'percentage'; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label>
                                            <a href="javascript:void(0)" id="btn-add-grace-period">Añadir periodo de gracia</a>
                                        </label>
                                    </div>
                                </div>

                                <script>
                                    $(document).ready(function () {
                                        $("#btn-add-grace-period").click(function () {
                                            $("#md-grace-periods").modal("show");
                                        });

                                        $("#btn-toggle-penalty-type").click(function () {
                                            if ($(this).html() == '%')
                                            {
                                                $(this).html("Amt");
                                                $("#hid-penalty-type").val("amount");
                                            } else
                                            {
                                                $(this).html("%");
                                                $("#hid-penalty-type").val("percentage");
                                            }
                                        });
                                    });
                                </script>

                                <div class="hr-line-dashed"></div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">

                        <div class="form-group">
                            <a class="btn btn-default btn-secondary" id="btn-close" href="<?= site_url("loan_products"); ?>"><?= $this->lang->line("common_close"); ?></a>

                            <?php if ($loan_product_info->id > 0): ?>
                                <?php if (check_access($user_info->role_id, "loan_products", 'edit')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar cambios" id="btn-save" />
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (check_access($user_info->role_id, "loan_products", 'add')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar cambios" id="btn-save" />
                                <?php endif; ?>
                            <?php endif; ?>


                        </div>

                    </div>
                </div>
            </div>
        </div>
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
                                    <?php if ($period_cnt > 0): ?>
                                        <?php for ($i = 1; $i <= $period_cnt; $i++): ?>
                                            <option><?= $i; ?></option>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <?php for ($i = 1; $i <= 30; $i++): ?>
                                            <option><?= $i; ?></option>
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
                        <?php foreach ($grace_periods as $grace_period): ?>
                            <tr>
                                <td style="text-align:center"><input type="checkbox" class="chk-delete"/></td>
                                <td style="text-align: center">
                                    <?= $grace_period["period"]; ?>
                                    <input type="hidden" name="period[]" value="<?= $grace_period["period"]; ?>">
                                </td>
                                <td style="text-align: center">
                                    <?= $grace_period["qty"]; ?>
                                    <input type="hidden" name="qty[]" value="<?= $grace_period["qty"]; ?>">
                                </td>
                                <td style="text-align: center">
                                    <?= $grace_period["unit"]; ?>
                                    <input type="hidden" name="unit[]" value="<?= $grace_period["unit"]; ?>">
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

<?php
echo form_close();
?>

<?php $this->load->view("partial/footer"); ?>

<script src="<?php echo base_url(); ?>js/loan.js?v=<?= time(); ?>"></script>

<script type='text/javascript'>
    $(document).ready(function () {
        
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
        
        $("#btn-save-grace_period").click(function(){
            
            var url = '<?=site_url('loan_products/ajax');?>';
            var params = $("#tbl-grace-periods tbody input, #tbl-grace-periods tbody select").serialize();
            
            params += '&softtoken=' + $("input[name='softtoken']").val();
            params += '&type=5';
            params += '&loan_product_id=<?=$loan_product_info->id;?>';
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    if ( data.ret_grace_periods != '' )
                    {
                        $("#grace_period_json").val( data.ret_grace_periods );
                    }
                    alertify.alert("¡El período de gracia se ha guardado correctamente!", function(){
                        $("#md-grace-periods").modal("hide");
                    });
                }
            }, "json");
        });
        
        $("#btn-add-grace-period-row").click(function(){
           $("#tbl-grace-periods tbody").append( "<tr>" + $("#tr-new-row").html() + "</tr>" );
           $("#tbl-grace-periods tbody input, #tbl-grace-periods tbody select").prop("disabled", false);
           $("#tbl-grace-periods tbody input").prop("readonly", false);
        });
        
        $("#btn-save").click(function () {
            var url = $("#frmLoanProducts").attr("action");
            var params = $("#frmLoanProducts").serialize();

            $.post(url, params, function (data) {
                if (data.status == "OK")
                {
                    window.location.href = data.url;
                } else
                {
                    alertify.alert(data.message);
                }
            }, "json");
        });
        
        $(".no-select2").select2("destroy");
    });
</script>