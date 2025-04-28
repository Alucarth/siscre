<?php $this->load->view("partial/header"); ?>

<?php echo form_open($controller_name . '/save/' . $id, array('id' => $controller_name . '_form', 'class' => 'form-horizontal')); ?>

<div class="title-block">
    <h3 class="title"> 

        <?php if ($id > 0): ?>
            Editar permiso de tipo de usuario
        <?php else: ?>
            Nuevo permiso de tipo de usuario
        <?php endif; ?>

    </h3>
    <p class="title-description">
        Información básica de permisos de tipo de usuario
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">

                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">

                            <div style="text-align: center">

                                <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

                                <ul id="error_message_box"></ul>

                            </div>

                            <div class="form-group row">

                                <label class="col-sm-2 control-label text-xs-right">

                                    <?php echo form_label('Tipo de usuario' . ':', 'role_name', array('class' => 'required', 'style' => 'color:red')); ?>

                                </label>

                                <div class="col-sm-10">

                                    <input type="text" name="role_name" placeholder="Escriba aquí para nombrar el grupo de usuarios" value="<?= $info->name; ?>" id="role_name" class="form-control">

                                </div>

                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="form-group row">

                                <label class="col-sm-2 control-label text-xs-right">

                                    Permisos:

                                </label>

                                <div class="col-sm-10">

                                    <div class="tabs-container">
                                        <ul class="nav nav-tabs nav-tabs-bordered">
                                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-general">Modulos</a></li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-loan">Préstamos</a></li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-views">Vistas</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="tab-general" class="tab-pane fade in active show">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align: center">Nombre del módulo</th>
                                                            <th style="text-align: center">Leer</th>
                                                            <th style="text-align: center">Añadir</th>
                                                            <th style="text-align: center">Editar </th>
                                                            <th style="text-align: center">Eliminar</th>
                                                        </tr>
                                                    </thead>
                                                    
                                                    <tbody>
                                                        <?php foreach ($all_modules->result() as $module) : ?>
                                                            <tr>
                                                                <td><?= ktranslate2(ucwords(str_replace("_", " ", $module->module_id))); ?></td>
                                                                <td style="text-align: center">
                                                                    <input name="rights[]" value="<?= $module->module_id; ?>" <?= in_array($module->module_id, $module_ids) ? "checked='checked'" : ""; ?> type="checkbox" />
                                                                </td>
                                                                <td style="text-align: center">
                                                                    <input name="write_access[]" value="<?= $module->module_id; ?>" <?= in_array($module->module_id, $write_module_ids) ? "checked='checked'" : ""; ?> type="checkbox" />
                                                                </td>
                                                                <td style="text-align: center">
                                                                    <input name="edit_access[]" value="<?= $module->module_id; ?>" <?= in_array($module->module_id, $edit_module_ids) ? "checked='checked'" : ""; ?> type="checkbox" />
                                                                </td>
                                                                <td style="text-align: center">
                                                                    <input name="delete_access[]" value="<?= $module->module_id; ?>" <?= in_array($module->module_id, $delete_module_ids) ? "checked='checked'" : ""; ?> type="checkbox" />
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="tab-loan" class="tab-pane fade">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align: center">Descripción</th>
                                                            <th style="text-align: center">Habilitar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                               Puede aprobar un préstamo? 
                                                            </td>
                                                            <td style="text-align: center">
                                                                <input type="checkbox" name="approve_loan" value="1" <?=$info->approve_loan ? 'checked="checked"' : ''; ?> />
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="tab-views" class="tab-pane fade">
                                                
                                                <div class="alert alert-info">
                                                    <span class="fa fa-info-circle"></span> Marque a continuación para ver las transacciones realizadas por esos <b>tipos de usuario</b>
                                                </div>
                                                
                                                <ul style="padding: 0">

                                                    <?php foreach ($roles as $role): ?>

                                                        <li style="list-style: none;">

                                                            <label style="width: 100%; line-height: 1">                                

                                                                <input type="checkbox" name="low_level[]" value="<?= $role->role_id; ?>" <?= in_array($role->role_id, $low_levels) ? "checked='checked'" : ""; ?> />

                                                                <span><?= $role->name; ?></span>

                                                            </label>

                                                        </li>

                                                    <?php endforeach; ?>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <button type="button" class="btn btn-default btn-secondary" data-dismiss="modal" id="btn-close"><?= $this->lang->line("common_close"); ?></button>

                        <?php
                        echo form_submit(
                                array(
                                    'name' => 'submit',
                                    'id' => 'submit',
                                    'value' => $this->lang->line('common_submit'),
                                    'class' => 'btn btn-primary'
                                )
                        );
                        ?>
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



    //validation and submit handling

    $(document).ready(function () {
        $("#div-form").height($(window).height() - 250);
        $('#<?= $controller_name; ?>_form').validate({
            submitHandler: function (form) {

                $(form).ajaxSubmit({
                    success: function (response) {
                        if (!response.success)
                        {
                            set_feedback(response.message, 'error_message', true);

                        } else
                        {
                            set_feedback(response.message, 'success_message', false);
                        }

                        $("#roles_form").attr("action", "<?= site_url(); ?>loan_types/save/" + response.role_id);
                    },
                    dataType: 'json'
                });
            },
            errorLabelContainer: "#error_message_box",
            wrapper: "li",
            rules:
                    {
                        role_name: "requerido"

                    },
            messages:
                    {
                        role_name: "El tipo de usuario es un campo obligatorio",
                    }
        });

    });

</script>











