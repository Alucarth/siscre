<?php $this->load->view('leads/front/partial/header'); ?>

<script src="<?php echo site_url('leads/assets/js/drag-drop-upload/simpleUpload.min.js') ?>"></script>

<style>
    .alert {
        position: relative;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 15px;
    }
</style>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">

            <?php $this->load->view('leads/front/steps/nav'); ?>

            <div class="steps__fields">

                <?php echo form_open('leads/ajax', 'id="frmStep5"', ["type" => 5, "leads_id" => $leads_id]); ?>
                <div class="h2 step_page__title">Choose the payment method to receive the loan</div>
                
                <div class="form__group select required application_payment_method" aria-required="true">
                    <label class="select required form__label" for="application_payment_method" aria-required="true"><abbr title="required">*</abbr>Payment Method</label>
                    <select role="paymentMethod select2" data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="payment_method" id="application_payment_method" tabindex="-1" aria-hidden="true" aria-required="true">
                        <option value="bank" <?=$leads_info->payment_method=='bank' ? 'selected="selected"' : '';?>>Bank Details</option>
                        <option value="gcash" <?=$leads_info->payment_method=='gcash' ? 'selected="selected"' : '';?>>Gcash</option>
                    </select>
                </div>
                
                <div id="div-bank-details">
                    <div class="form__group string required application_bank_name" aria-required="true">
                        <label class="string required form__label" for="application_bank_name" aria-required="true">
                            <abbr title="required">*</abbr>Bank name
                        </label>
                        <input class="string required form__input form__input-select input-lg" autocomplete="no" value="<?=$leads_info->bank_name;?>" placeholder="Bank name" type="text" name="bank_name" id="application_bank_name" aria-required="true">
                    </div>
                    <div class="form__group string required application_account_number" aria-required="true">
                        <label class="string required form__label" for="application_account_number" aria-required="true">
                            <abbr title="required">*</abbr>Account Number
                        </label>
                        <input class="string required form__input form__input-select input-lg" autocomplete="no" value="<?=$leads_info->account_number;?>" placeholder="Account Number" type="text" name="account_number" id="application_account_number" aria-required="true">
                    </div>
                    <div class="form__group string required application_account_name" aria-required="true">
                        <label class="string required form__label" for="application_account_name" aria-required="true">
                            <abbr title="required">*</abbr>Account holder name
                        </label>
                        <input class="string required form__input form__input-select input-lg" autocomplete="no" value="<?=$leads_info->account_holder_name;?>" placeholder="Account Holder Name" type="text" name="account_holder_name" id="application_account_holder_name" aria-required="true">
                    </div>
                </div>
                <div id="div-gcash">
                    <div class="form__group string required application_gcash_num" aria-required="true">
                        <label class="string required form__label" for="application_gcash_num" aria-required="true">
                            <abbr title="required">*</abbr>GCASH #:
                        </label>
                        <input class="string required form__input form__input-select input-lg" autocomplete="no" value="<?=$leads_info->gcash_num;?>" placeholder="0905 XXX XXXX" type="text" name="gcash_num" id="application_gcash_num" aria-required="true">
                    </div>
                </div>
                

                <div class="form__group form__group-actions">
                    <button name="button" type="button" class="btn btn-primary btn" id="btn-save-step5" data-disable-with="Please wait...">Finish</button> 
                    <a class="link link-back" href="<?= site_url('leads/steps/4/' . $leads_id); ?>">Back to Address details</a>
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
                
                function toggle_payment_method()
                {
                    if ( $("#application_payment_method").val() == 'gcash' )
                    {
                        $("#div-gcash").show();
                        $("#div-bank-details").hide();
                        $("#div-gcash input").addClass("required");
                        $("#div-bank-details input").removeClass("required");
                    }
                    else
                    {
                        $("#div-gcash").hide();
                        $("#div-bank-details").show();
                        $("#div-gcash input").removeClass("required");
                        $("#div-bank-details input").addClass("required");
                    }
                }
                
                $(document).ready(function () {
                    
                    toggle_payment_method();
                
                    $("#application_payment_method").change(function(){
                        toggle_payment_method();
                    });
                
                    $("#btn-save-step5").click(function () {
                        
                        if ( !validate( $("#frmStep5") ) ) return;

                        var url = $("#frmStep5").attr("action");
                        var params = $("#frmStep5").serialize();

                        $("#btn-save-step5").html($("#btn-save-step5").attr("data-disable-with"));
                        $.post(url, params, function (data) {
                            if (data.status == "OK")
                            {
                                window.location.href = data.step;
                            } else
                            {
                                alert(data.message);
                            }
                            $("#btn-save-step5").html("Finish");
                        }, "json");
                    });
                });
            </script>

        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>