<script src="<?=site_url('leads/assets/js/drag-drop-upload/simpleUpload.min.js')?>"></script>

<style>

    .hidden-bar {

        display: none;

    }

</style>



<div style="text-align: center">

    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

    <ul id="error_message_box"></ul>

</div>                                        



<div id="div-employee">

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Ocupación:

        </label>

        <div class="col-sm-9">

            <select class="form-control" id="occupation" name="occupation">

                <option value="">Elija</option>

                <?php foreach (get_occupations() as $key => $name): ?>

                    <option value="<?= $key ?>" <?= $person_info->occupation == $key ? 'selected="selected"' : ''; ?> ><?= $name; ?></option>

                <?php endforeach; ?>

            </select>

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Nombre de la empresa:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" id="company_name" name="company_name" value="<?= $person_info->company_name; ?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            ¿Cuánto tiempo lleva trabajando en su empresa actual?

        </label>

        <div class="col-sm-9">

            <select class="form-control" id="work_term" name="work_term">

                <option value="">Elija</option>

                <?php foreach (get_work_terms() as $key => $name): ?>

                    <option value="<?= $key ?>" <?= $person_info->work_term == $key ? 'selected="selected"' : ''; ?> ><?= $name; ?></option>

                <?php endforeach; ?>  

            </select>

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Ingreso mensual (<?= $this->config->item("currency_symbol") ?>):

        </label>

        <div class="col-sm-9">

            <input type="number" class="form-control" id="net_monthly_income" name="net_monthly_income" value="<?= $person_info->net_monthly_income; ?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Teléfono del trabajo/oficina:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?= $person_info->company_phone; ?>" />

        </div>

    </div>
<!-- corregir -->
    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Dirección del trabajo/oficina:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" id="guarantor_phone" name="guarantor_phone" value="<?= $person_info->guarantor_phone; ?>" />

        </div>

    </div>
<!-- corregir -->
</div>



<!--

<div id="div-employer">



    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Business name:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" id="business_name" name="business_name" value="<?= $person_info->business_name; ?>" />

        </div>

    </div>



    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Business Address:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_address" id="business_address" value="<?=$person_info->business_address;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Type of Business:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_type" id="business_type" value="<?=$person_info->business_type;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Business NIF:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_nif" id="business_nif" value="<?=$person_info->business_nif;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Legal Structure of Business:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_legal_structure" id="business_legal_structure" value="<?=$person_info->business_legal_structure;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Financial Institution Used:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_financial_institution" id="business_financial_institution" value="<?=$person_info->business_financial_institution;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Account Name:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_account_name" id="business_account_name" value="<?=$person_info->business_account_name;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Phone Number:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_phone" id="business_phone" value="<?=$person_info->business_phone;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Payroll Date:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_payroll_date" id="business_payroll_date" value="<?=$person_info->business_payroll_date;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Number of Employees:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_total_employees" id="business_total_employees" value="<?=$person_info->business_total_employees;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Agent of Record:

        </label>

        <div class="col-sm-9">

            <input type="text" class="form-control" name="business_agent_record" id="business_agent_record" value="<?=$person_info->business_agent_record;?>" />

        </div>

    </div>

    <div class="form-group row">

        <label class="control-label col-sm-3 text-xs-right">

            Business Patent:

        </label>

        <div class="col-sm-9">



            <div class="fileinput__text" role="upload" id="div_container_patent" style="width:250px;">

                <?php if ($person_info->business_patent_file != ''): ?>

                    <img src="<?= site_url('leads/documents/' . $person_info->id . '/' . $person_info->business_patent_file); ?>" style="width:100%" />

                <?php else: ?>

                    Sin fotografía

                <?php endif; ?>

            </div>

            <input type="file" name="file" id="cmnd_patent" requared="false" class="progress_meter fileinput__field" role="fileinputFile" accept="image/*" capture="camera">                            

            <div class="progress-bar hidden-bar" id="progress-bar-cmnd_patent" style="width:250px;">

                <div class="progress-percentage" id="progress-percentage-cmnd_patent" style="width:80%;">80%</div>

            </div>



            <script>

                $(document).ready(function () {



                    $('#cmnd_patent').change(function () {

                        $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {

                            allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif", "pdf"],

                            data: {

                                softtoken: $("input[name='softtoken']").val(),

                                id: '<?= $person_info->id; ?>',

                                is_front_img: 0,

                                is_back_img: 0,

                                is_cc_img: 0,

                                is_patent_img: 1

                            },

                            start: function (file) {

                            },

                            progress: function (progress) {

                                $("#progress-bar-cmnd_patent").removeClass("hidden-bar");

                                $("#progress-percentage-cmnd_patent").width(Math.round(progress) + '%');

                                $("#progress-percentage-cmnd_patent").html(Math.round(progress) + '%');

                            },

                            success: function (data) {

                                //upload successful

                                var data = $.parseJSON(data);

                                $("#div_container_patent").html("<img src='" + data.path + "' style='width:100%' />");



                            },

                            error: function (error) {

                            }

                        });

                    });



                });

            </script>



        </div>

    </div>



</div>

-->

