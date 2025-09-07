<?php $this->load->view('leads/front/partial/header'); ?>

<script  src="<?=site_url('leads/assets/js/jquery-masked-input/jquery.maskedinput-ben.js')?>"></script>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">
            
             <?php $this->load->view('leads/front/steps/nav'); ?>
            
            <div class="steps__fields">
                <?php echo form_open('leads/ajax', 'id="frmStep3"', ["type" => 3, "leads_id" => $leads_id]); ?>
                    
                    <div class="h2 step_page__title">Employment Details</div>
                    
                    <div class="form__group select required application_social_status_id mainInformation" aria-required="true">
                        <label class="select required form__label" for="application_social_status_id" aria-required="true"><abbr title="required">*</abbr> Occupation</label>
                        <select data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="occupation" id="application_occupation" tabindex="-1" aria-hidden="true" aria-required="true">
                            <option value=""></option>
                            <?php foreach ( get_occupations() as $key => $name ): ?>
                                <option value="<?=$key?>" <?=$leads_info->occupation == $key ? 'selected="selected"' : '';?> ><?=$name;?></option>
                            <?php endforeach; ?>
                        </select> 
                        
                        <script>
                            
                            $(document).ready(function(){
                                $("#application_occupation").change(function(){
                                    if ( $(this).val() == 12 || $(this).val() == 13 || $(this).val() == 14 || $(this).val() == 15 )
                                    {
                                        $("#workingInformation").hide();
                                    }
                                    else
                                    {
                                        $("#workingInformation").show();
                                    }
                                });
                            });
                            
                        </script>
                    </div>
                    
                    <div id="workingInformation" style="<?=(in_array($leads_info->occupation, [12,13,14,15]) || $leads_info->occupation == '') ? 'display:none' : ''?>">
                        <div class="form__group string required application_company_name" aria-required="true">
                            <label class="string required form__label" for="application_company_name" aria-required="true">
                                <abbr title="required">*</abbr>Company name
                            </label>
                            <input role="companyName" class="string required form__input form__input-select input-lg" value="<?=$leads_info->company_name;?>" placeholder="Company Name" type="text" name="company_name" id="application_company_name" aria-required="true">
                        </div>
                        <div class="form__group select required application_work_term" aria-required="true">
                            <label class="select required form__label" for="application_work_term" aria-required="true">
                                <abbr title="required">*</abbr> How long have you been working at your current company?
                            </label>
                            <select data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="work_term" id="application_work_term" tabindex="-1" aria-hidden="true" aria-required="true">
                                <option value=""></option>
                                <?php foreach ( get_work_terms() as $key => $name ): ?>
                                <option value="<?=$key?>" <?=$leads_info->work_term == $key ? 'selected="selected"' : '';?> ><?=$name;?></option>
                                <?php endforeach; ?>                                
                            </select>                            
                        </div>
                        <div class="form__group new_currency required application_salary" aria-required="true">
                            <label class="new_currency required form__label" for="application_salary" aria-required="true"><abbr title="required">*</abbr> Monthly income</label>
                            <input class="form__input" type="tel" placeholder="25 000 <?= strtoupper($this->config->item('currency_symbol'));?>" value="<?=$leads_info->net_monthly_income;?>" name="salary" id="application_salary">
                        </div>
                        <div class="form__group tel required application_company_phone" aria-required="true">
                            <label class="tel required form__label" for="application_company_phone" aria-required="true">
                                <abbr title="required">*</abbr>Work/office phone
                            </label>
                            <input placeholder="0900-000-0000" type="tel" class="string tel required form__input form__input-select input-lg" value="<?=$leads_info->company_phone;?>" name="company_phone" id="application_company_phone" aria-required="true">
                            <p class="form__hint">Please provide valid mobile phone as it might be used to verify your employment</p>
                            <script>
                                $(document).ready(function(){
                                    $("#application_company_phone").mask( '9999-999-9999' );
                                });
                            </script>
                        </div>
                    </div>                    
                    
                    <div class="form__group tel required application_guarantor_phone mainInformation" aria-required="true">
                        <label class="tel required form__label" for="application_guarantor_phone" aria-required="true">
                            <abbr title="required">*</abbr>Your Personal Phone Number
                        </label>
                        <input role="phoneMobile guarantorPhone" placeholder="0900-000-0000" type="tel" class="string tel required form__input form__input-select input-lg" value="<?=$leads_info->guarantor_phone;?>" name="guarantor_phone" id="application_guarantor_phone" aria-required="true">
                        <p class="form__hint">Please provide a valid mobile phone as it might be used to verify your identity</p>
                        
                        <script>
                            $(document).ready(function(){
                                $("#application_guarantor_phone").mask( '9999-999-9999' );
                            });
                        </script>
                    </div>
                    
                    <div class="form__group form__group-actions">
                        <button name="button" type="button" class="btn btn-primary btn" id="btn-save-step3" data-disable-with="Please wait...">Continue</button> 
                        <a class="link link-back" href="<?=site_url('leads/steps/2/' . $leads_id);?>">Back to Address details</a>
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
                    
                    if ( $("#application_occupation").val() == 12 || $("#application_occupation").val() == 13 || $("#application_occupation").val() == 14 || $("#application_occupation").val() == 15 )
                    {
                        form.find(".mainInformation input.required, .mainInformation select.required").each(function(){
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
                    }
                    else
                    {
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
                    }
                    
                    
                    
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
                    $("#btn-save-step3").click(function(){
                        
                        if ( !validate( $("#frmStep3") ) ) return;
                        
                        var url = $("#frmStep3").attr("action");
                        var params = $("#frmStep3").serialize();
                        
                        $("#btn-save-step3").html( $("#btn-save-step3").attr("data-disable-with") );
                        $.post(url, params, function(data){
                            if ( data.status == "OK" )
                            {
                                window.location.href = data.step;
                            }
                            else
                            {
                                alert(data.message);
                            }
                            $("#btn-save-step3").html( "Continue" );
                        }, "json");
                    });
                });
            </script>
            
        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>