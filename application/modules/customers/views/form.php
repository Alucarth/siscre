<?php $this->load->view("partial/header"); ?>

<script src="<?php echo base_url('js/documents.js?v=' . APP_VERSION)?>"></script>



<?= form_open_multipart('customers/save/' . $person_info->person_id, array('id' => 'customer_form', 'class' => 'form-horizontal')); ?>

<input type="hidden" id="customer_id" value="<?= $customer_id ?>" />

<input type="hidden" id="linker" value="<?= random_string('alnum', 16); ?>" />



<style>

    .list-inline li {

        display: inline-block;

        padding: 28px;

    }

</style>



<div class="title-block">

    <h3 class="title"> 



        <?php if ($person_info->person_id > 0): ?>

            Actualizar prestatario

        <?php else: ?>

            Nuevo prestatario

        <?php endif; ?>



    </h3>

    <p class="title-description">

        Información básica del prestatario

    </p>

</div>



<div class="section">

    <div class="row sameheight-container">

        <div class="col-lg-12">



            <div class="card">



                <div class="card-block">

                    <div class="inqbox float-e-margins">

                        

                        <div class="inqbox-content">

                            <div class="tabs-container">



                                <ul class="nav nav-tabs nav-tabs-bordered">

                                    <li class="nav-item">

                                        <a class="nav-link active" data-toggle="tab" href="#sectionA"><?= $this->lang->line("customers_personal_information"); ?></a>

                                    </li>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-company">Información de la empresa</a></li>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-documents">Verificación de documentos</a></li>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-bank">Métodos de pago</a></li>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sectionB"><?= $this->lang->line("customers_financial_information"); ?></a></li>

                                    <?php if ( $person_info->person_id > 0 ): ?>

                                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-customer-documents">Documentos</a></li>

                                    <?php endif; ?>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-password">Contraseña</a></li>

                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-acc-statement">Declaraciones de cuenta</a></li>

                                    

                                </ul>

                                <div class="tab-content">

                                    <div id="sectionA" class="tab-pane fade in active show">



                                        <div style="text-align: center">

                                            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

                                            <ul id="error_message_box"></ul>

                                        </div>                                        

                                        <?php $this->load->view("customers/form_basic_info"); ?>                                        

                                    </div>

                                    

                                    <div id="tab-company" class="tab-pane fade">

                                        <?php $this->load->view('customers/company_info');?>

                                    </div>

                                    

                                    <div id="tab-documents" class="tab-pane fade">

                                        <?php $this->load->view('customers/doc_info');?>

                                    </div>

                                    

                                    <div id="tab-bank" class="tab-pane fade">

                                        <?php $this->load->view('customers/bank_info');?>

                                    </div>

                                    

                                    <div id="sectionB" class="tab-pane fade">

                                        <input type="hidden" name="financial_status_id" value="<?= @$person_info->financial_status_id; ?>" />

                                        <table class="table table-bordered" id="tbl-income-sources">

                                            <thead>

                                                <tr>

                                                    <th style="text-align: center; width: 1%">

                                                        <input type="checkbox" class="select_all_" />

                                                    </th>

                                                    <th style="text-align: center; width: 80%"><?= $this->lang->line("customers_occupation"); ?></th>

                                                    <th style="text-align: center; width: 20%"><?= $this->lang->line("customers_monthly_income"); ?></th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php foreach ($financial_infos as $financial_info): ?>

                                                    <tr>

                                                        <td>

                                                            <input type="checkbox" class="select_" />

                                                        </td>

                                                        <td>

                                                            <input type="text" class="form-control" name="sources[]" value="<?= $financial_info[0]; ?>" />

                                                        </td>

                                                        <td>

                                                            <input type="number" class="form-control" step="any" name="values[]" value="<?= $financial_info[1]; ?>" />

                                                        </td>

                                                    </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>



                                        <button class="btn btn-primary" type="button" id="btn-add-row"><?= $this->lang->line('common_add_row'); ?></button>

                                        <button class="btn btn-danger" type="button" id="btn-del-row"><?= $this->lang->line('common_delete_row'); ?></button>



                                    </div>

                                    

                                    <div id="tab-customer-documents" class="tab-pane fade">

                                        <div id="div-documents">

                                            <button class="btn btn-primary tbl_documents_dt-custom-button" type="button" id="btn-upload-doc">Upload</button>

                                            <table class="table table-hover table-bordered" id="tbl_documents">

                                                <thead>

                                                    <tr>

                                                        <th style="text-align: center; width: 1%"></th>

                                                        <th style="text-align: center;">Nombre del documento</th>

                                                        <th style="text-align: center;">Descripción</th>

                                                        <th style="text-align: center;">Fecha de modificación</th>

                                                    </tr>

                                                </thead>

                                            </table>

                                            <?=$tbl_documents;?>

                                        </div>

                                        <script>

                                            $(document).ready(function(){

                                                $("a[href='#tab-customer-documents']").click(function(){

                                                    $("#tbl_documents").DataTable().ajax.reload();

                                                });

                                            });

                                        </script>

                                        <div class="clearfix"></div>

                                    </div>

                                    

                                    <div id="tab-password" class="tab-pane fade">

                                        <br/>

                                        <div id="div-password">

                                            <div class="form-group">

                                                <label>Contraseña:</label>

                                                <input type="password" class="form-control" id="password" name="password" />

                                            </div>

                                            <div class="form-group">

                                                <label>Confirmar contraseña:</label>

                                                <input type="password" class="form-control" id="repassword" name="repassword" />

                                            </div>

                                        </div>

                                        <?php if ($user_info->role_id != CUSTOMER_ROLE_ID):?>

                                        <div class="form-group">

                                            <div><label><input type="checkbox" id="chk-notify-customer" name="notify_customer" value="1" /> Notificar al cliente </label></div>

                                        </div>

                                        <?php endif;?>

                                        <div class="form-group" id="div-change-password" style="<?=$user_info->role_id == CUSTOMER_ROLE_ID ? '' : 'display:none;'?>">

                                            <button type="button" class="btn btn-primary" id="btn-change-password">Cambiar contraseña</button>

                                        </div>

                                    </div>

                                    

                                    <div id="tab-acc-statement" class="tab-pane fade">

                                        <?php $this->load->view('customers/account_statements');?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                </div>



                <div class="col-lg-12">

                    <div class="form-group">

                        <a href="<?=site_url('customers')?>" class="btn btn-default btn-secondary" data-dismiss="modal" id="btn-close"><?= $this->lang->line("common_close"); ?></a>

                        <?php if ((int) $customer_id > -1) : ?>

                            <?php if ( check_access($user_info->role_id, "customers", 'edit') ): ?>

                                <button type="button" class="btn btn-primary" id="btn-edit"><?= $this->lang->line("common_edit"); ?></button>    

                            <?php endif; ?>

                        <?php endif; ?>



                        <?php if ( check_access($user_info->role_id, "customers", 'add') ): ?>

                            <?php

                            $display = '';

                            if ($customer_id > -1)

                            {

                                $display = 'display: none';

                            }

                            echo form_submit(

                                    array(

                                        'name' => 'submit',

                                        'id' => 'btn-save',

                                        'value' => $this->lang->line('common_save'),

                                        'class' => 'btn btn-primary',

                                        'style' => $display

                                    )

                            );

                            ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>    

</div>

<?= form_close(); ?>



<!-- Modal -->

<div class="modal fade" id="md-upload-document" role="dialog">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                Subir documento

            </div>

            <div class="modal-body">

                <?php echo form_open_multipart(site_url('customers/upload'), ["id"=>"frmDocUpload"]); ?>

                    <input type="hidden" name="customer_id" value="<?=$person_info->person_id;?>" />

                    <div class="form-group">

                        <input type="file" name="file" />

                    </div>

                    <div class="form-group">

                        <label>Nombre del documento</label>

                        <input type="text" class="form-control" id="document_name" name="document_name" />

                    </div>

                    <div class="form-group">

                        <label>Descripción</label>

                        <textarea class="form-control" id="descriptions" name="descriptions"></textarea>

                    </div>

                <?php echo form_close();?>

                

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                <button type="button" class="btn btn-primary" id="btn-submit-upload">Subir</button>

            </div>

        </div>

        <!-- /.modal-content -->

    </div>

    <!-- /.modal-dialog -->

</div>

<!-- /.modal -->



<?php $this->load->view("partial/footer"); ?>



<script src="<?php echo base_url(); ?>js/people.js?v=<?= time(); ?>"></script>



<script type="text/javascript">

    $(document).ready(function () {

        

        $("#btn-change-password").click(function(){

            var $this = $(this);

            

            $this.prop("disabled", true);

            $this.html("Updating... Please wait..");

            

            var url = '<?=site_url('customers/ajax')?>';

            var params = $("#div-password input").serialize();

            params += "&type=5";

            params += "&customer_id=<?=$person_info->person_id?>";

            params += "&softtoken=" + $("input[name='softtoken']").val();

            

            $.post(url, params, function(data){

                if ( data.status == "OK" )

                {

                    toastr.success("Password has been successfully updated!");

                    window.location.reload();

                }

                else

                {

                    toastr.error(data.msg);

                }

                $this.prop("disabled", false);

                $this.html("Change Password");

            }, "json");

        });



        if ($("#customer_id").val() > -1)

        {

            $("#customer_form input, textarea, #customer_form select").prop("disabled", true);

            $("#customer_form input[type='hidden']").prop("disabled", false);



            $("#btn-edit").click(function () {

                $("#btn-save").show();

                $(this).hide();

                $("#customer_form input, textarea, #customer_form select").prop("disabled", false);

            });

            

            <?php if ($user_info->role_id == CUSTOMER_ROLE_ID): ?>

                $("#password, #repassword").prop("disabled", false);

            <?php endif; ?>

        }

        

        $("#photo_url").on("change", function () {

            kl_readURL(this, "img-pic");

        });



        $(document).on("click", "#btn-upload-doc", function () {

            $("#md-upload-document").modal("show");

        });



        $(document).on("click", ".remove-file", function () {

            var el = $(this);

            $.ajax({

                url: '<?= site_url('customers/remove_file'); ?>',

                data: {

                    file_id: el.data("file-id"),

                    softtoken: $("input[name='softtoken']").val()

                },

                type: 'post',

                dataType: 'json',

                success: function (data) {

                    $("input[name='softtoken']").val(data.token_hash);

                    el.parent().remove();

                },

                error: function () {

                    ;

                }

            });

        });



        $("#btn-add-row").on("click", function () {

            $(".select_all_").prop("checked", false);



            var rowCount = $('#tbl-income-sources tr').length;

            if (rowCount > 1)

            {

                $("#tbl-income-sources tbody").append("<tr>" + $('#tbl-income-sources tr:last').html() + "</tr>");

            } else

            {

                $("#tbl-income-sources tbody").append("<tr><td><input type='checkbox' class='select_' /></td><td><input type='text' class='form-control' name='sources[]' /></td><td><input type='number' class='form-control' name='values[]' /></td></tr>");

            }

        });



        $('#customer_form').on("submit", function (e) {

            e.preventDefault();



            var $this = $(this);

            var formData = new FormData( this );



            $.ajax({

                url: $this.attr("action"),

                type: 'POST',

                data: formData,

                success: function (data) {

                    var data = $.parseJSON(data);

                    if (!data.success)

                    {

                        set_feedback(data.message, 'error_message', true);

                    } else

                    {

                        set_feedback(data.message, 'success_message', false);

                        window.location.href = '<?=site_url('customers/view/')?>' + data.person_id;

                    }

                },

                cache: false,

                contentType: false,

                processData: false

            });

        });

    });

</script>