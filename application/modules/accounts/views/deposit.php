<?php $this->load->view("partial/header"); ?>
<link href="css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open('accounts/save_transaction/' . $deposit_info->id, array('id' => 'frmAccountDeposit', 'class' => 'form-horizontal')); ?>

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

        <?php if ($deposit_info->id > 0): ?>
            Actualizar depósito
        <?php else: ?>
            Nuevo depósito
        <?php endif; ?></span>
    </h3>

    <div class="clearfix"></div>

    <p class="title-description">
        Información básica de depósitos
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
                                
                                <input type="hidden" id="trans_type" name="trans_type" value="deposit" />
                                
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Seleccionar cuenta:
                                    </label>
                                    <div class="col-lg-10">
                                        <select class="form-control" name="account_id" id="account_id">
                                            <?php if ( $accounts ): ?>
                                                <?php foreach ( $accounts->result() as $account ): ?>
                                                    <option value="<?=$account->id;?>" <?=( $deposit_info->account_id == $account->id ? "selected='selected'" : "" );?>><?=$account->account_name?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Seleccionar cliente:
                                    </label>
                                    <div class="col-lg-10">
                                        
                                        <?php
                                        echo form_input(
                                                array(
                                                    'name' => 'inp-customer',
                                                    'id' => 'inp-customer',
                                                    'value' => $deposit_info->customer_name,
                                                    'class' => 'form-control',
                                                    'placeholder' => $this->lang->line('common_start_typing'),
                                                    'style' => 'display:' . ($deposit_info->added_by <= 0 ? "" : "none")
                                                )
                                        );
                                        ?>
                                        
                                        <span id="sp-customer" style="display: <?= ($deposit_info->added_by > 0 ? "" : "none") ?>">
                                            <?= $deposit_info->customer_name; ?>
                                            <span><a href="javascript:void(0)" title="Remove Customer" class="btn-remove-row"><i class="fa fa-times"></i></a></span>
                                        </span>
                                        <input type="hidden" id="client_id" name="client_id" value="<?= $deposit_info->added_by; ?>" />
                                        
                                        <script>
                                            $(document).ready(function(){
                                                $('#inp-customer').autocomplete({
                                                    serviceUrl: '<?php echo site_url("accounts/customer_search"); ?>',
                                                    onSelect: function (suggestion) {
                                                        $("#client_id").val(suggestion.data);
                                                        $("#sp-customer").html(suggestion.value + ' <span><a href="javascript:void(0)" title="Remover cliente" class="btn-remove-row"><i class="fa fa-times"></i></a></span>');
                                                        $("#sp-customer").show();
                                                        $("#inp-customer").hide();
                                                    }
                                                });
                                                
                                                $(document).on("click", ".btn-remove-row", function () {
                                                    $("#sp-customer").hide();
                                                    $("#sp-customer").html("");
                                                    $("#inp-customer").val("");
                                                    $("#inp-customer").show();
                                                    $("#client_id").val("");
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Monto:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="number" class="form-control" id="amount" name="amount" value="<?= $deposit_info->amount; ?>" placeholder="Ingrese el monto en bolivianos aquí" autocomplete="not" />
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Descripción:
                                    </label>
                                    <div class="col-lg-10">
                                        <textarea id="description" name="description" class="form-control" style="height: 120px;"><?= $deposit_info->description; ?></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">

                        <div class="form-group">
                            <a class="btn btn-default btn-secondary" id="btn-close" href="<?= site_url("accounts"); ?>"><?= $this->lang->line("common_close"); ?></a>

                            <?php if ($deposit_info->id > 0): ?>
                                <?php if (check_access($user_info->role_id, "accounts", 'edit')): ?>
                                    <input type="button" class="btn btn-primary" value="Depositar" id="btn-save" />
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (check_access($user_info->role_id, "accounts", 'add')): ?>
                                    <input type="button" class="btn btn-primary" value="Depositar" id="btn-save" />
                                <?php endif; ?>
                            <?php endif; ?>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<?php
echo form_close();
?>

<?php $this->load->view("partial/footer"); ?>


<script type='text/javascript'>
    $(document).ready(function () {
        $("#btn-save").click(function () {
            var url = $("#frmAccountDeposit").attr("action");
            var params = $("#frmAccountDeposit").serialize();

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
    });
</script>