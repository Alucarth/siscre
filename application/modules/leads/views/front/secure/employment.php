<?php $this->load->view("partial/header"); ?>

<?= form_open('leads/ajax/' . $person_info->id, array('id' => 'frmPersonalInfo', 'class' => 'form-horizontal')); ?>

<input type="hidden" name="type" value="16" />

<div class="title-block">
    <h3 class="title">Company Details</h3>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">
                    <div class="inqbox float-e-margins">

                        <div class="inqbox-content">
                            <div class="tabs-container">

                                
                                <div id="tab-info" class="tab-pane fade in active show">

                                    <div style="text-align: center">
                                        <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
                                        <ul id="error_message_box"></ul>
                                    </div>                                        
                                    
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Employer:
                                        </label>
                                        <div class="col-sm-9">
                                            <select class="form-control" disabled="disabled">
                                                <option value="0">I am an employer</option>
                                                <?php foreach ( $employers as $employer ): ?>
                                                    <option value="<?=$employer->person_id?>" <?=$person_info->employer_id == $employer->person_id ? 'selected="selected"' : '';?> ><?=$employer->first_name . " " . $employer->last_name . " ( " . $employer->company_name . " )";?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Occupation:
                                        </label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="occupation" name="occupation">
                                                <option value="">Choose</option>
                                                <?php foreach ( get_occupations() as $key => $name ): ?>
                                                    <option value="<?=$key?>" <?=$person_info->occupation == $key ? 'selected="selected"' : '';?> ><?=$name;?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Company name:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?=$person_info->company_name;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            How long have you been working at your current company?
                                        </label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="work_term" name="work_term">
                                                <option value="">Choose</option>
                                                <?php foreach ( get_work_terms() as $key => $name ): ?>
                                                <option value="<?=$key?>" <?=$person_info->work_term == $key ? 'selected="selected"' : '';?> ><?=$name;?></option>
                                                <?php endforeach; ?>  
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Monthly income (<?=$this->config->item("currency_symbol")?>):
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="net_monthly_income" name="net_monthly_income" value="<?=$person_info->net_monthly_income;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Work/office phone:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?=$person_info->company_phone;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Your personal phone number:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="guarantor_phone" name="guarantor_phone" value="<?=$person_info->guarantor_phone;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            UBO (Ultimate Beneficial Owner):
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="ubo" name="ubo" value="<?=$person_info->ubo;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Chamber of Commerce number:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="cc_number" name="cc_number" value="<?=$person_info->cc_number;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            VAT number:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="vat_number" name="vat_number" value="<?=$person_info->vat_number;?>" />
                                        </div>
                                    </div>
                                    
                                </div>
                                    
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">

                        <a href="<?=site_url('customers')?>" class="btn btn-default btn-secondary" data-dismiss="modal" id="btn-close"><?= $this->lang->line("common_close"); ?></a>
                            <?php
                            echo form_submit(
                                    array(
                                        'name' => 'submit',
                                        'id' => 'btn-save',
                                        'value' => $this->lang->line('common_save'),
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
<?= form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script src="<?php echo base_url(); ?>js/people.js?v=<?= time(); ?>"></script>

<script type="text/javascript">
    $(document).ready(function () {
        
        $("#frmPersonalInfo").submit(function(e){
            e.preventDefault();
            
            var url = $("#frmPersonalInfo").attr("action");
            var params = $("#frmPersonalInfo").serialize();
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    set_feedback("You have successfully saved changes!", 'success_message', false);
                }
            }, "json");
        });
    });
</script>