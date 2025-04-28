<?php $this->load->view("partial/header"); ?>
<link href="css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open('accounts/save/' . $account_info->id, array('id' => 'frmLoanAccounts', 'class' => 'form-horizontal')); ?>

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

        <?php if ($account_info->id > 0): ?>
            Actualizar cuenta de préstamo
        <?php else: ?>
            Nueva cuenta de préstamo
        <?php endif; ?></span>
    </h3>

    <div class="clearfix"></div>

    <p class="title-description">
        Información básica de cuentas de préstamo
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
                                        Denominación de la cuenta:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="account_name" name="account_name" value="<?= $account_info->account_name; ?>" placeholder="Nombre o denominación de cuenta" autocomplete="not" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Tipo de cuenta:
                                    </label>
                                    <div class="col-lg-10">
                                        <select class="form-control" id="account_type" name="account_type">
                                            <option value="">Escoger</option>
                                            <?php foreach( $account_types as $type => $label ):?>
                                                <option value="<?=$type?>" <?=$account_info->account_type==$type?'selected="selected"':'';?>><?=ucwords($label);?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Descripción:
                                    </label>
                                    <div class="col-lg-10">
                                        <textarea id="description" name="description" class="form-control" style="height: 120px;"><?= $account_info->description; ?></textarea>
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

                            <?php if ($account_info->id > 0): ?>
                                <?php if (check_access($user_info->role_id, "accounts", 'edit')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar" id="btn-save" />
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (check_access($user_info->role_id, "accounts", 'add')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar" id="btn-save" />
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
            var url = $("#frmLoanAccounts").attr("action");
            var params = $("#frmLoanAccounts").serialize();

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