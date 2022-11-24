<style>
    #tbl_cities_wrapper td:nth-child(1),
    #tbl_cities_wrapper th:nth-child(1), 
    #tbl_cities_wrapper td:nth-child(2),
    #tbl_cities_wrapper th:nth-child(2) 
    {
        width:40px;
        min-width:40px;
    }
</style>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_photo') . ':', 'photo_url'); ?>
            </label>
            <div class="col-sm-9">
                    <?php if (trim(trim($person_info->photo_url) !== "") && file_exists(FCPATH . "/uploads/profile-" . $person_info->person_id . "/" . $person_info->photo_url)): ?>
                        <img id="img-pic" style="width:80px;" src="<?= base_url("uploads/profile-" . $person_info->person_id . "/" . $person_info->photo_url); ?>"/>
                    <?php else: ?>
                        <img id="img-pic" style="width:80px;" src="http://via.placeholder.com/80x80"/>
                    <?php endif; ?>
                    <div>
                        <input type="file" id="photo_url" name="photo_url" />
                    </div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>        
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_first_name') . ':', 'first_name', array('class' => 'required')); ?>
            </label>
            <div class="col-sm-9">
                <?php
                    echo form_input(
                        array(
                            'name' => 'first_name',
                            'id' => 'first_name',
                            'value' => $person_info->first_name,
                            'class' => 'form-control'
                        )
                    );
                ?>
            </div>
        </div>
        <!--  <div class="hr-line-dashed"></div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo ktranslate2('Middle Name'); ?>:
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'middle_name',
                            'id' => 'middle_name',
                            'value' => $person_info->middle_name,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>
            -->
        <div class="hr-line-dashed"></div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_last_name') . ':', 'last_name', array('class' => 'required')); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'last_name',
                            'id' => 'last_name',
                            'value' => $person_info->last_name,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
            Documento 
            </label>
            <div class="col-sm-9">
                <?php $id_types = get_type_of_documents(); ?>
                <select class="form-control" id="id_type" name="id_type">
                    <?php foreach ( $id_types as $key => $id_type ): ?>
                        <option value="<?=$key?>" <?=$key==$person_info->id_type?'selected="selected"':''?> ><?=$id_type;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Número de documento
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="id_no" id="id_no" value="<?=$person_info->id_no;?>" />
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Género
            </label>
            <div class="col-sm-9">
                <select class="form-control" name="gender" id="gender">
                    <option value="male" <?=$person_info->gender=='male'?'selected="selected"':'';?>>Hombre</option>
                    <option value="female" <?=$person_info->gender=='female'?'selected="selected"':'';?>>Mujer</option>
                </select>
            </div>
        </div>
        <!--
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Estado civil
            </label>
            <div class="col-sm-9">
                <?php $m_statuses = get_marital_statuses(); ?>
                <select class="form-control" id="marital_status" name="marital_status">
                    <?php foreach ( $m_statuses as $key => $name ): ?>
                       <option value="<?=$key?>" <?=$key==$person_info->marital_status?'selected="selected"':''?> ><?=$name;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
                    -->
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_email') . ':', 'email'); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'email',
                            'id' => 'email',
                            'value' => $person_info->email,
                            'class' => 'form-control',
                            'autocomplete' => 'new-password'
                        )
                );
                ?>
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?=ktranslate2("Birth Date")?>:
            </label>
            <div class="col-sm-9">
                <div class="input-group date">
                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                    <input type="text" id="date_of_birth" name="date_of_birth" class="form-control" value="<?=$person_info->date_of_birth > 0 ? date($this->config->item('date_format'), $person_info->date_of_birth) : ''?>" />
                </div>

            </div>

        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_phone_number') . ':', 'phone_number'); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'phone_number',
                            'id' => 'phone_number',
                            'value' => $person_info->phone_number,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>
        <div class="hr-line-dashed"></div>
    
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_address_1') . ':', 'address_1'); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'address_1',
                            'id' => 'address_1',
                            'value' => $person_info->address_1,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_address_2') . ':', 'address_2'); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'address_2',
                            'id' => 'address_2',
                            'value' => $person_info->address_2,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>

        <div class="hr-line-dashed"></div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_city') . ':', 'city'); ?></a>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="city" id="city" value="<?=$person_info->city?>" />
            </div>
        </div>
        <div class="hr-line-dashed"></div>
        
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Calle/Puerta/Dpto. No:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="street_no" name="street_no" value="<?=$person_info->street_no;?>" />
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_state') . ':', 'state'); ?></a>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="state" id="state" value="<?=$person_info->state?>" />
            </div>
        </div>

        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?php echo form_label($this->lang->line('common_zip') . ':', 'zip'); ?>
            </label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'zip',
                            'id' => 'zip',
                            'value' => $person_info->zip,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>

        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right"><?php echo form_label($this->lang->line('common_country') . ':', 'country'); ?></label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'country',
                            'id' => 'country',
                            'value' => $person_info->country,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>
            
    </div>

    <div class="col-lg-6">
        <div class="hr-line-dashed"></div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right"><?php echo form_label($this->lang->line('common_comments') . ':', 'comments'); ?></label>
            <div class="col-sm-9">
                <?php
                echo form_textarea(
                        array(
                            'name' => 'comments',
                            'id' => 'comments',
                            'value' => $person_info->comments,
                            'rows' => '5',
                            'cols' => '17',
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>

        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right"><?php echo form_label($this->lang->line('customers_account_number') . ':', 'account_number'); ?></label>
            <div class="col-sm-9">
                <?php
                echo form_input(
                        array(
                            'name' => 'account_number',
                            'id' => 'account_number',
                            'value' => $person_info->account_number,
                            'class' => 'form-control'
                        )
                );
                ?>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <?php if ( is_plugin_active('branches') ): ?>
            <div class="form-group row">
                <label class="control-label col-sm-3 text-xs-right">
                    Agencia:
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="branch_id" name="branch_id">
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?=$branch->id;?>" <?=$person_info->branch_id == $branch->id ? 'selected="selected"' : '';?>><?=$branch->branch_name;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
        <?php endif; ?>
        <label class="control-label text-xs-right">
            <b>Campos Adicionales</b>
        </label>
        <?php foreach ( $extra_fields as $field ): ?>

            <div class="form-group row">

                <label class="control-label col-sm-3 text-xs-right"><?php echo $field->label; ?></label>

                <div class="col-sm-9">

                    <?php $new_field = $field->name;?>

                    <input type="text" class="form-control" name="<?=$field->name;?>" id="<?=$field->name;?>" value="<?=$person_info->$new_field;?>" />

                </div>

            </div>

        <?php endforeach; ?>

        

    </div>

</div>



<script>

    $(document).ready(function(){

        $('.input-group.date').datepicker({

            format: '<?= calendar_date_format(); ?>',

            todayBtn: "linked",

            keyboardNavigation: false,

            forceParse: false,

            calendarWeeks: true,

            autoclose: true

        });

    });

</script>