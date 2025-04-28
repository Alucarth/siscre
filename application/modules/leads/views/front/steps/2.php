<?php $this->load->view('leads/front/partial/header'); ?>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">

            <?php $this->load->view('leads/front/steps/nav'); ?>



            <div class="steps__fields">
                <?php echo form_open('leads/ajax', 'id="frmStep2"', ["type" => 2, 'leads_id' => $leads_id]); ?>
                
                    <div class="h2 step_page__title">Current (Present) Address</div>
                    
                    <div class="form__group select optional application_living_city_id">
                        <label class="select optional form__label" for="application_living_city_id"><abbr title="required">*</abbr> Living city/municipality</label>
                        <input minlength="3" class="string optional form__input form__input-select input-lg" placeholder="City/Municipality" value="<?=$leads_info->city;?>" type="text" name="city" id="application_city">
                    </div>
                    
                    <div class="form__group string required application_address" aria-required="true">
                        <label class="string required form__label" for="application_address" aria-required="true"><abbr title="required">*</abbr> Address</label>
                        <input class="string required form__input form__input-select input-lg" placeholder="Address" type="text" value="<?=$leads_info->address1?>" name="address1" id="application_address" aria-required="true">
                    </div>
                    <div class="form__group string required application_living_street_house_apt" aria-required="true">
                        <label class="string required form__label" for="application_living_street_house_apt" aria-required="true"><abbr title="required">*</abbr> Street, house number, apartment number</label>
                        <input class="string required form__input form__input-select input-lg" type="text" name="street_no" value="<?=$leads_info->street_no;?>" placeholder="Street, house number, apartment number" id="application_street" aria-required="true">
                    </div>
                    <div class="form__group string required application_living_country" aria-required="true">
                        <label class="string required form__label" for="application_living_country" aria-required="true"><abbr title="required">*</abbr> Country</label>
                        <input class="string required form__input form__input-select input-lg" type="text" name="country" value="<?=$leads_info->country;?>" placeholder="Country" id="application_country" aria-required="true">
                    </div>
                    
                    <div class="form__group form__group-actions">
                        <button name="button" type="button" class="btn btn-primary btn" id="btn-save-step2" data-disable-with="Please wait...">Continue</button> 
                        <a class="link link-back" href="<?=site_url('leads/steps/1/' . $leads_id); ?>">Back to Personal Information</a>
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
                    $("#btn-save-step2").click(function(){
                        
                        if ( !validate( $("#frmStep2") ) ) return;
                        
                        var url = $("#frmStep2").attr("action");
                        var params = $("#frmStep2").serialize();
                        
                        $("#btn-save-step2").html( $("#btn-save-step2").attr("data-disable-with") );
                        $.post(url, params, function(data){
                            if ( data.status == "OK" )
                            {
                                window.location.href = data.step;
                            }
                            else
                            {
                                alert(data.message);
                            }
                            $("#btn-save-step2").html( "Continue" );
                        }, "json");
                    });
                });
            </script>
            
        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>