<?php $this->load->view('leads/front/partial/header'); ?>

<link href="<?=base_url('fonts/font-awesome/css/font-awesome.css')?>" rel="stylesheet"></link>

<link rel="stylesheet" href="<?php echo site_url('leads/assets/bootstrap/css/tabs.css') ?>" />
<link rel="stylesheet" href="<?php echo site_url('leads/assets/bootstrap/css/cols.css') ?>" />
<link rel="stylesheet" href="<?php echo site_url('leads/assets/bootstrap/css/tables.css') ?>" />
<link rel="stylesheet" href="<?php echo site_url('leads/assets/bootstrap/css/modals.css') ?>" />
<link rel="stylesheet" href="<?php echo site_url('leads/assets/bootstrap/css/buttons.css') ?>" />

<link href="<?=base_url('js/alertifyjs/css/alertify.css')?>" rel="stylesheet"></link>
<script src="<?=base_url('js/alertifyjs/alertify.js')?>"></script>

<style>
    .form_float__input.error {
        border: 1px solid red;
    }

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

    .repeat_app_widget__body {
        align-items: baseline !important; 
    }
    
    .clearfix {
        clear: both;
    }
</style>

<?php echo form_open(); ?>
<?php echo form_close(); ?>

<div class="loan_info" style="">
    <div class="wrapper wrapper-steps" style="">


        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-item nav-link active" data-toggle="tab" href="#nav-home-tab" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
            </li>
<!--            <li class="nav-item">
                <a class="nav-item nav-link" data-toggle="tab" href="#nav-downloads" role="tab" aria-controls="nav-downloads" aria-selected="false">Downloads</a>                
            </li>            -->
            <li class="nav-item">
                <a class="nav-item nav-link" data-toggle="tab" href="#nav-support-tab" role="tab" aria-controls="nav-support" aria-selected="false">Support</a>
            </li>

        </ul>

        <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
            <div class="tab-pane fade active in" id="nav-home-tab">
                <h1 class="loan_info__title">Hello, <?= $leads_first_name; ?></h1>

                <div class="repeat_app_widget__body">
                    <div class="repeat_app_widget__calculation">

                        <?php if ($leads_info->application_status == 'paid'): ?>
                            <div class="loan_info__subtitle medium">Your loan limit has been upgraded. You can now apply for loans up to <span class="green">5,000 PHP </span>for <span class="green">30 days</span></div>
                            <?php $this->load->view('loans/widgets/price_slider'); ?>                    
                        <?php elseif ($leads_info->application_status == 'approved'): ?>                    
                            <div class="alert alert-success">You application has been approved</div>

                            <h3>Unpaid Amount: <?= number_format($leads_info->unpaid_amount, 2); ?></h3>
                            <h3>Due On: <?= date('m.d.Y', strtotime($leads_info->first_payment_date)); ?></h3>

                        <?php else: ?>
                            <div class="alert alert-success">You application has been reviewed</div>
                        <?php endif; ?>

                    </div>
                    <div class="repeat_app_widget__form">
                        <div class="repeat_app" role="resetBlockHidden">
                            <div class="repeat_app__title medium">Your bank details to receive the loan</div>
                            <div class="repeat_app__inputs">

                                <?php echo form_open('leads/ajax', 'id="frmBankDetails"', ["type" => 9, "leads_id" => $leads_info->id]); ?>

                                <div class="repeat_app__input">
                                    <div class="repeat_app__input__name small gray">Account holder name</div>
                                    <input readonly="readonly" class="string required form__input form__input-select input-lg" autocomplete="no" 
                                           value="<?= $leads_info->account_holder_name; ?>" 
                                           placeholder="Account Holder Name" type="text" 
                                           name="account_holder_name" id="application_account_holder_name" aria-required="true">
                                </div>
                                <div class="repeat_app__input">
                                    <div class="repeat_app__input__name small gray">Bank name</div>
                                    <input readonly="readonly" class="string required form__input form__input-select input-lg" autocomplete="no" 
                                           value="<?= $leads_info->bank_name; ?>" placeholder="Bank name" type="text" name="bank_name" id="application_bank_name" aria-required="true">
                                </div>
                                <div class="repeat_app__input">
                                    <div class="repeat_app__input__name small gray">Account Number</div>
                                    <input readonly="readonly" class="string required form__input form__input-select input-lg" autocomplete="no" 
                                           value="<?= $leads_info->account_number; ?>" placeholder="Account Number" type="text" name="account_number" id="application_account_number" aria-required="true">
                                </div>

                                <?php echo form_close(); ?>

                            </div>
                            <div class="repeat_app__reset">
                                <a id="resetLink" href="javascript:void(0)">I have different details</a>
                            </div>
                        </div>
                        <div id="div-reset-bank-details" style="display:none;">
                            <a class="btn block-center" id="saveLink" href="javascript:void(0)">Save</a>
                            <div class="repeat_app__cancel center">
                                <a id="cancelLink" class="link link-cancel" href="javascript:void(0)">Cancel</a>
                            </div>
                        </div>

                        <script>
                            $(document).ready(function () {
                                $("#resetLink").click(function () {
                                    $(this).hide();

                                    $("#application_account_holder_name").prop("readonly", false);
                                    $("#application_bank_name").prop("readonly", false);
                                    $("#application_account_number").prop("readonly", false);
                                    $("#div-reset-bank-details").show();
                                });

                                $("#cancelLink").click(function () {
                                    $("#resetLink").show();

                                    $("#application_account_holder_name").prop("readonly", true);
                                    $("#application_bank_name").prop("readonly", true);
                                    $("#application_account_number").prop("readonly", true);
                                    $("#div-reset-bank-details").hide();
                                });

                                $("#saveLink").click(function () {
                                    var url = $("#frmBankDetails").attr("action");
                                    var params = $("#frmBankDetails").serialize();
                                    $.post(url, params, function (data) {
                                        if (data.status == "OK")
                                        {
                                            $("#resetLink").show();

                                            $("#application_account_holder_name").prop("readonly", true);
                                            $("#application_bank_name").prop("readonly", true);
                                            $("#application_account_number").prop("readonly", true);
                                            $("#div-reset-bank-details").hide();
                                        }
                                    }, "json");
                                });
                            });
                        </script>
                    </div>
                </div>
                <?php if ($leads_info->application_status == 'paid'): ?>
                    <div class="repeat_app_widget__bottom">
                        <div class="form_float__btn repeat_app_widget__btn">
                            <input type="submit" name="commit" value="Get money now" class="btn block-center" role="repeatConfirm" data-disable-with="Please wait...">
                        </div>
                        <p class="repeat_app_widget__hint center small center">I have read understood and accepted the above statements, <a target="_blank" href="javascript:void(0)">Terms&amp;Conditions</a> and <a target="_blank" href="javascript:void(0)">Privacy Policy</a>.</p>
                    </div>
                <?php endif; ?>       
            </div>
            
            <div class="tab-pane fade in" id="nav-support-tab">
                <?php $this->load->view('support/tabs/support'); ?>
            </div>
        </div>



    </div>
</div>


<?php $this->load->view('leads/front/partial/footer'); ?>            


