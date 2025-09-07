<?php $this->load->view('leads/front/partial/header'); ?>

<script  src="<?=site_url('leads/assets/js/jquery-masked-input/jquery.maskedinput-ben.js')?>"></script>

<style>
    .error {
        border: 1px solid red;
    }
</style>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">
            
            
            <?php $this->load->view('leads/front/steps/nav'); ?>
            
            
            <div class="steps__fields">
                
                <?php echo form_open(site_url('leads/ajax'), 'id="frmStep1"', ['type' => 1, "leads_id" => $leads_id]); ?>
                    <div class="h2 step_page__title">Personal Information</div>
                    
                    <div role="formFloatGroup" class="form_float__group string optional application_first_name">
                        <input class="string optional form_float__input" placeholder="First name" type="text" name="application_first_name" id="application_first_name" value="<?=$leads_info->first_name?>" />
                    </div>
                    <div role="formFloatGroup" class="form_float__group string optional application_middle_name">
                        <input class="string optional form_float__input" placeholder="Middle name" type="text" name="application_middle_name" id="application_middle_name" value="<?=$leads_info->middle_name?>"/>
                    </div>
                    <div role="formFloatGroup" class="form_float__group string optional application_last_name">
                        <input class="string optional form_float__input" placeholder="Last name" type="text" name="application_last_name" id="application_last_name" value="<?=$leads_info->last_name?>"/>
                    </div>
                    <p class="form_float__help">Please provide correct first name & last name as it will be used to verify your identity</p>
                    
                    <div>
                        <div class="form__group string required application_password" aria-required="true">
                            <label class="string required form__label" for="application_password" aria-required="true"><abbr title="required">*</abbr> Password</label>
                            <input type="password" class="string required form__input form__input-select input-lg" value="" name="password" id="application_password" placeholder="Password" aria-required="true">
                        </div>
                    </div>
                    <div>
                        <div class="form__group string required application_confirm_password" aria-required="true">
                            <label class="string required form__label" for="application_confirm_password" aria-required="true"><abbr title="required">*</abbr> Confirm</label>
                            <input type="password" class="string required form__input form__input-select input-lg" value="" name="confirm_password" id="application_confirm_password" placeholder="Confirm" aria-required="true">
                        </div>
                    </div>
                    
                    <div class="form__group select required application_type_of_document" aria-required="true">
                        <label class="select required form__label" for="application_type_of_document" aria-required="true"><abbr title="required">*</abbr> Type of Verification Document to Submit</label>
                        
                        <select role="typeOfDocument select2" data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="type_of_document" id="application_type_of_document" tabindex="-1" aria-hidden="true" aria-required="true">
                            <option value=""></option>                            
                            <?php foreach ( get_type_of_documents() as $key => $name ): ?>
                                <option value="<?=$key; ?>" <?=$leads_info && $leads_info->id_type == $key ? 'selected="selected"' : '';?>><?=$name;?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <p class="form__hint">You might be asked to provide a photo of the chosen document later on the next steps of the application form</p>
                    </div>
                    <div>
                        <div class="form__group string required application_document_number" aria-required="true">
                            <label class="string required form__label" for="application_document_number" aria-required="true"><abbr title="required">*</abbr> Document ID Number</label>
                            <input type="text" class="string required form__input form__input-select input-lg" value="<?=$leads_info->id_no;?>" name="document_number" id="application_document_number" inputmode="numeric" placeholder="ID Number" aria-required="true">
                        </div>
                    </div>
                    <div class="form__group radio_buttons required application_gender" aria-required="true">
                        <label class="radio_buttons required form__label form__label-radio" aria-required="true"><abbr title="required">*</abbr> Gender</label>
                        <div class="form__boolean">
                            <span class="radio">
                                <input class="radio_buttons required pretty_radio" type="radio" <?=$leads_info->gender == 'male' ? 'checked="checked"' : '';?> value="male" name="gender" id="application_gender_0" aria-required="true">
                                <label class="collection_radio_buttons" for="application_gender_0">Male</label>
                            </span>
                            <span class="radio">
                                <input class="radio_buttons required pretty_radio" type="radio" <?=$leads_info->gender == 'female' ? 'checked="checked"' : '';?> value="female" name="gender" id="application_gender_1" aria-required="true">
                                <label class="collection_radio_buttons" for="application_gender_1">Female</label>
                            </span>
                        </div>
                    </div>
                    <div class="form__group string required application_living_nationality" aria-required="true">
                        <label class="string required form__label" for="application_living_nationality" aria-required="true"><abbr title="required">*</abbr> Nationality</label>
                        <input class="string required form__input form__input-select input-lg" type="text" name="nationality" value="<?=$leads_info->nationality;?>" placeholder="Nationality" id="application_nationality" aria-required="true">
                    </div>
                    <div class="form__group string required application_living_marital_status" aria-required="true">
                        <label class="select required form__label" for="application_living_marital_status" aria-required="true"><abbr title="required">*</abbr> Marital Status</label>
                        
                        <select role="maritalStatus select2" data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="marital_status" id="application_living_marital_status" tabindex="-1" aria-hidden="true" aria-required="true">
                            <option value=""></option>                            
                            <?php foreach ( get_marital_statuses() as $key => $name ): ?>
                                <option value="<?=$key; ?>" <?=$leads_info && $leads_info->marital_status == $key ? 'selected="selected"' : '';?>><?=$name;?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form__group string required application_date_of_birth" aria-required="true">
                        <label class="string required form__label" for="application_date_of_birth" aria-required="true"><abbr title="required">*</abbr> Date of birth</label>
                        <input type="tel" role="datePicker" class="string required form__input form__input-select input-lg" placeholder="MM-DD-YYYY" value="<?=($leads_info->birth_date != '0000-00-00' && $leads_info->birth_date != '') ? date("m-d-Y", strtotime($leads_info->birth_date)) : '';?>" name="date_of_birth" id="application_date_of_birth" aria-required="true">
                        <p class="form__hint">Please provide correct date of birth as it will be used to verify your identity</p>
                        
                        <script>
                            $(document).ready(function(){
                                $("#application_date_of_birth").mask( '99-99-9999' );
                            });
                        </script>
                        
                    </div>                    
                    <div class="form__group boolean optional application_receive_promo_notifications">
                        <div class="form__checkbox">
                            <input name="receive_promo_notifications" id="receive_promo_notifications" type="hidden" value="<?=$leads_info->receive_promo_notifications?>">
                            <input class="boolean optional pretty_checkbox" type="checkbox" <?=$leads_info->receive_promo_notifications == 1 ? 'checked="checed"' : '';?> value="1" name="chk_receive_promo_notifications" id="application_receive_promo_notifications">
                            <label class="boolean optional checkbox-pretty form__label form__label-checkbox" for="application_receive_promo_notifications">I want to receive promo notifications on this email</label>
                        </div>
                    </div>
                    
                    <div class="form__group form__group-actions">
                        <button name="button" type="button" class="btn btn-primary btn" id="btn-save-step1" data-disable-with="Please wait...">Continue</button>
                    </div>
                <?php echo form_close(); ?>
            </div>
            
            <div class="steps__calc steps__calc-top">
                <h3>A few clicks away from creating your account.</h3>
                    <h4>Borrow with Ease</h4>
                    <ul>
                        <li>Competitive interest rate</li>
                        <li>Quick loan application</li>
                        <li>No collateral</li>                            
                    </ul>
            </div>
            <div class="steps__calc steps__calc-bot"></div>
            
            <script>
                function validate( form )
                {
                    var has_error = 0;
                    
                    if ( $("#application_password").val() != $("#application_confirm_password").val() )
                    {
                        $("#application_confirm_password").parent().append( '<span class="static-error-msg" style="color:red">Password don\'t match</span>' );
                        
                        if ( has_error == 0 )
                        {
                            has_error = 1;
                        }
                    }
                    
                    form.find("input.required, select.required").each(function(){
                        
                        if ( $(this).val() == '' )
                        {
                            if ( $(this).parent().find('.static-error-msg').length > 0 )
                            {
                                
                            }
                            else
                            {
                                $(this).parent().append( '<span class="static-error-msg" style="color:red">' + $(this).attr("name").replace(/\_/g, ' ') + ' is a required field</span>' )
                            }
                            
                            if ( has_error == 0 )
                            {
                                has_error = 1;
                            }
                        }
                    });
                    
                    form.find("input.required, select.required").focus(function(){
                        $(".static-error-msg").remove();
                    });
                    
                    if ( has_error )
                    {
                        return false;
                    }
                    
                    return true;
                }
                $(document).ready(function(){
                    $("#application_receive_promo_notifications").click(function(){
                        $("#receive_promo_notifications").val( $(this).is(":checked") ? 1 : 0 );
                    });
                    
                    $("#btn-save-step1").click(function(){
                        if ( !validate( $("#frmStep1") ) ) return;
                        var url = $("#frmStep1").attr("action");
                        var params = $("#frmStep1").serialize();
                        
                        $("#btn-save-step1").html( $("#btn-save-step1").attr("data-disable-with") );
                        $.post(url, params, function(data){
                            if ( data.status == "OK" )
                            {
                                window.location.href = data.step;
                            }
                            else
                            {
                                alert(data.message)
                            }
                            
                            $("#btn-save-step1").html( "Continue" );
                        }, "json");
                    });
                });
            </script>
            
            
        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>