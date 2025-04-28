<?php $this->load->view("partial/header"); ?>
<link href="css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<?php echo form_open('branches/save/' . $branch_info->id, array('id' => 'frmBranches', 'class' => 'form-horizontal')); ?>

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

        <span style="float:left"><?php if ($branch_info->id > 0): ?>
                Actualizar sucursal
            <?php else: ?>
                Nueva sucursal
            <?php endif; ?></span>
    </h3>
    
    <div class="clearfix"></div>
    
    <p class="title-descriptions">
        Información básica de la sucursal
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
                                        Nombre:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="branch_name" name="branch_name" value="<?= $branch_info->branch_name; ?>" placeholder="Escriba el nombre de la sucursal aquí" autocomplete="not" />                            
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Teléfono:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="branch_phone" name="branch_phone" value="<?= $branch_info->branch_phone; ?>" placeholder="Ingrese el número de teléfono de la sucursal" autocomplete="not" />                            
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Dirección:
                                    </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="branch_address" name="branch_address" value="<?= $branch_info->branch_address; ?>" placeholder="Ingrese la dirección de la sucursal" autocomplete="not" />                            
                                    </div>
                                </div>

                                <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <label class="col-lg-2 text-xs-right control-label">
                                        Descripción:
                                    </label>
                                    <div class="col-lg-10">
                                        <textarea id="descriptions" name="descriptions" class="form-control" style="height: 120px;"><?= $branch_info->descriptions; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">

                        <div class="form-group">
                            <a class="btn btn-default btn-secondary" id="btn-close" href="<?= site_url("branches"); ?>"><?= $this->lang->line("common_close"); ?></a>

                            <?php if ($branch_info->id > 0): ?>
                                <?php if (check_access($user_info->role_id, "branches", 'edit')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar cambios" id="btn-save" />
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (check_access($user_info->role_id, "branches", 'add')): ?>
                                    <input type="button" class="btn btn-primary" value="Guardar registro" id="btn-save" />
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
            var url = $("#frmBranches").attr("action");
            var params = $("#frmBranches").serialize();

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