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
                <?= ktranslate("common_photo")?>:
            </label>
            <div class="col-sm-9">
                    <?php if (trim(trim($person_info->photo_url) !== "") && file_exists(FCPATH . "/uploads/profile-" . $person_info->person_id . "/" . $person_info->photo_url)): ?>
                        <img id="img-pic" style="width:80px" src="<?= base_url("uploads/profile-" . $person_info->person_id . "/" . $person_info->photo_url); ?>"/>
                    <?php else: ?>
                        <img id="img-pic" style="width:80px" src="http://via.placeholder.com/80x80"/>
                    <?php endif; ?>
                       
                    <div>
                        <input type="file" id="photo_url" name="photo_url" />
                    </div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>        

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?=ktranslate("common_first_name");?>:
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
        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?=ktranslate("common_last_name");?>:
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
                <?= ktranslate("common_bank_name"); ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?=$person_info->bank_name;?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_bank_account_number"); ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="bank_account_num" id="bank_account_num" value="<?=$person_info->bank_account_num;?>" />
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate('common_email'); ?>:
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
                <?= ktranslate("common_date_of_birth"); ?>:
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
                <?= ktranslate("common_phone_number"); ?>:
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
                <?= ktranslate("common_address_1"); ?>:
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
                <?= ktranslate("common_address_2"); ?>:
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
    </div>
    
    <div class="col-lg-6">
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_city"); ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="city" id="city" value="<?=$person_info->city?>" />
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_state"); ?>:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="state" id="state" value="<?=$person_info->state?>" />
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_zip"); ?>:
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
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_country"); ?>:
            </label>
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
        <div class="hr-line-dashed"></div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("common_comments"); ?>:
            </label>
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
            <label class="control-label col-sm-3 text-xs-right">
                <?= ktranslate("customers_account_number");?>:
            </label>
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
                    <?=ktranslate2("Branch")?>:
                </label>
                <div class="col-sm-9">
                    <select class="form-control" id="branch_id" name="branch_id">
                        <option value="-1"><?=ktranslate("common_please_select");?></option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?=$branch->id;?>" <?=$person_info->branch_id == $branch->id ? 'selected="selected"' : '';?>><?=$branch->branch_name;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
        <?php endif; ?>
        
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