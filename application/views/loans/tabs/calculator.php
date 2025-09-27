<script src="http://momentjs.com/downloads/moment.js"></script>


<div style="text-align: center">
    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
    <ul id="error_message_box"></ul>
</div>

<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
    Apply Amount:
    </label>
    <div class="col-sm-2">
        <input type="hidden" id="amount" name="amount" value="<?=$loan_info->loan_amount;?>" />
        <?php
        echo form_input(
                array(
                    'name' => 'apply_amount',
                    'id' => 'apply_amount',
                    'value' => $loan_info->apply_amount,
                    'class' => 'form-control',
                    'type' => 'number',
                    'step' => 'any',
                )
        );
        ?>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Interest Rate:
    </label>
    <div class='col-sm-2'>
        <div class="input-group">
            <input type="text" class="form-control" name="interest_rate" id="interest_rate" value="<?= $loan_info->interest_rate; ?>" />
            <span class="input-group-addon input-group-append"><span class="input-group-text">%</span></span>
        </div>
        <input type="hidden" id="DTE_Field_interest_type" name="interest_type" value="fixed" />
    </div>
</div>

<div id="div_terms">
    <div class="hr-line-dashed"></div>
    <div class="form-group row">
        <label class="col-sm-2 control-label text-xs-right" style="color:red">
            <?= $this->lang->line('loan_type_term'); ?>:
        </label>
        <div class="col-sm-2">
            <input type="text" name="term" id="term" class="form-control" value="<?= $loan_info->payment_term; ?>" />
        </div>
        <div class="col-sm-2">
            <select class="form-control" name="term_period" id="term_period">
                <option value="day" <?= $loan_info->term_period === "day" ? 'selected="selected"' : ''; ?>>Day</option>
                <option value="week" <?= $loan_info->term_period === "week" ? 'selected="selected"' : ''; ?>>Week</option>
                <option value="month" <?= $loan_info->term_period === "month" ? 'selected="selected"' : ''; ?>>Month</option>
                <option value="biweekly" <?= $loan_info->term_period === "biweekly" ? 'selected="selected"' : ''; ?>>Month (Biweekly)</option>
                <option value="month_weekly" <?= $loan_info->term_period === "month_weekly" ? 'selected="selected"' : ''; ?>>Month (weekly)</option>
                <option value="year" <?= $loan_info->term_period === "year" ? 'selected="selected"' : ''; ?>>Year</option>
            </select>
        </div>          
    </div>
    
    <script>
        $(document).ready(function(){
            $("#term_period").change(function(){
                if ( $(this).val() == 'biweekly' )
                {
                    $("#sp-term-description").html("The interest rate is applied every month but the customer is required to pay twice in a month");
                    $("#div_explain").slideDown();
                }
                else if ( $(this).val() == 'month_weekly' )
                {
                    $("#sp-term-description").html("The interest rate is applied every month but the customer is required to pay every week");
                    $("#div_explain").slideDown();
                }
                else
                {
                    $("#div_explain").slideUp();                    
                }
                    
            });
        });
    </script>
    
</div>

<div id="div_explain" style="display:none;">
    <div class="hr-line-dashed"></div>
    <div class="form-group row">
        <label class="col-lg-2 control-label text-xs-right">
            Term description:
        </label>
        <div class="col-lg-10">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <span id="sp-term-description"></span>
            </div>
        </div>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right" style="color:red">
        First Payment Date:
    </label>
    <div class='col-sm-2'>
        <div class="input-group date">
            <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>                                        
            <input type="text" class="form-control" autocomplete="nope" name="start_date" id="start_date" value="<?=($loan_info->payment_start_date != '') ? date($this->config->item('date_format'), $loan_info->payment_start_date) : ''?>" />
        </div>
    </div>
</div>

<?php if (is_plugin_active("holidays") ): ?>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Exclude (Schedule):
    </label>
    <div class='col-sm-2'>
        <select class="form-control" multiple="multiple" name="loan[exclude_schedule][]">
            <option value="monday" <?=in_array("monday", $exclude_schedules) ? 'selected="selected"' : '';?>>Monday</option>
            <option value="tuesday" <?=in_array("tuesday", $exclude_schedules) ? 'selected="selected"' : '';?>>Tuesday</option>
            <option value="wednesday" <?=in_array("wednesday", $exclude_schedules) ? 'selected="selected"' : '';?>>Wednesday</option>
            <option value="thursday" <?=in_array("thursday", $exclude_schedules) ? 'selected="selected"' : '';?>>Thursday</option>
            <option value="friday" <?=in_array("friday", $exclude_schedules) ? 'selected="selected"' : '';?>>Friday</option>
            <option value="saturday" <?=in_array("saturday", $exclude_schedules) ? 'selected="selected"' : '';?>>Saturday</option>
            <option value="sunday" <?=in_array("sunday", $exclude_schedules) ? 'selected="selected"' : '';?>>Sunday</option>
            <option disabled="">------------</option>
            <option value="holidays" <?=in_array("holidays", $exclude_schedules) ? 'selected="selected"' : '';?>>Holidays</option>
        </select>
    </div>
</div>
<?php endif; ?>

<div class="form-group row">
    <label class="col-lg-2 control-label text-xs-right">
        Late payment penalties:
    </label>
    <div class="col-lg-2">
        <div class="input-group">
            <input type="text" name="penalty_value" id="penalty_value" class="form-control" value="<?= $loan_info->penalty_value > 0 ? $loan_info->penalty_value : 0?>" />
            <?php if ( $loan_info->penalty_type == 'amount' ): ?>
            <span class="input-group-addon input-group-append" title="Click to toggle type"><span class="input-group-text" id="btn-toggle-penalty-type">Amt</span></span>
            <?php else: ?>
            <span class="input-group-addon input-group-append" title="Click to toggle type"><span class="input-group-text" id="btn-toggle-penalty-type">%</span></span>
            <?php endif; ?>
            <input type="hidden" id="hid-penalty-type" name="penalty_type" value="<?=$loan_info->penalty_type != '' ? $loan_info->penalty_type : 'percentage';?>" />
        </div>
    </div>
    
    <div class="col-lg-2">
        <label>
            <a href="javascript:void(0)" id="btn-add-grace-period">Add grace period</a>
        </label>
    </div>
    
</div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
    Total Additional Fees:
    </label>
    <div class="col-sm-5">
        <span id="sp-total-additional-fees">0.00</span>
        <input type="hidden" id="hid_total_additiona_fees" value="0" />
        
        <label style="display: inline-block; margin-left: 20px"><input type="checkbox" id="exclude_additional_fees" name="exclude_additional_fees" <?=$loan_info->exclude_additional_fees ? 'checked="checked"' : ''?> /> Mark as Paid</label>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        Payable Amount:
    </label>
    <div class="col-sm-2">
        <div id="loan-total-amount"><?=$loan_info->loan_amount - $loan_info->exclude_additional_fees > 0 ? $loan_info->loan_amount - $loan_info->exclude_additional_fees: '0.00'?></div>
    </div>
</div>


<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right">
        &nbsp;
    </label>
    <div class="col-sm-4">
        <button class="btn btn-primary" type="button" id="btn-loan-calculator">Calculate</button>
    </div>
</div>
<div class="hr-line-dashed"></div>

<div class="form-group row">
    <label class="col-sm-2 control-label text-xs-right"> &nbsp; </label>
    <div class="col-sm-10">
        <div id="div-payment-scheds" style="overflow: auto"></div>
    </div>
</div>

<script>
    
    $(document).ready(function(){    
        $("#exclude_additional_fees").click(function(){
            calculate_amount();
        });
    });
    
    function check_term_field()
    {
        if ( $("#DTE_Field_interest_type").val() == "outstanding_interest" ||                 
                $("#DTE_Field_interest_type").val() == "one_time")
        {
            $("#term").val("1");
            $("#term").prop("disabled", true);
            $("#term_period").prop("disabled", true);
            $("#div_terms").slideUp();
        }
        else
        {
            $("#term").prop("disabled", false);
            $("#div_terms").slideDown();
        }
    }
    
    function calculate_amount()
    {
        compute_additional_fees();
        
        var url = '<?= site_url('loans/ajax'); ?>';
        var params = {
            softtoken: $("input[name='softtoken']").val(),
            InterestType: $("#DTE_Field_interest_type").val(),
            NoOfPayments: $("#term").val(),
            ApplyAmt: parseFloat($("#apply_amount").val()),
            TotIntRate: $('#interest_rate').val(),
            InstallmentStarted: $('#start_date').val(),
            PayTerm: $("#term_period").val(),
            ajax_type:1,
            exclude_sundays: $("#exclude_sundays").is(":checked") ? 1 : 0,
            penalty_value: $("#penalty_value").val(),
            penalty_type: $("#hid-penalty-type").val(),
            loan_id: '<?=$loan_info->loan_id;?>',
	    additional_fees: parseFloat($("#hid_total_additiona_fees").val()),
            exclude_schedules: $("select[name='loan[exclude_schedule][]']").val(),
            exclude_additional_fees: $("#exclude_additional_fees").is(":checked") ? 1 : 0
        };
        $.post(url, params, function(data){
            if ( data.status == "OK" )
            {
                $("#loan-total-amount").html( data.formatted_total_amount );
                $("#amount").val( data.total_amount );
                $("#div-payment-scheds").html( data.table_scheds );                
            }
        }, "json");
    }
    
    $(document).ready(function(){
        
        check_term_field();
        
        $(document).on('change', "#DTE_Field_interest_type", function(){
            $("#DTE_Field_InterestType").val($(this).val());
            check_term_field();
        });
        
        <?php if ( $loan_info->loan_id > 0 ): ?>
            calculate_amount();
        <?php endif; ?>
        
        $(document).on('click', '#btn-loan-calculator', function () {

            if ($("#start_date").val() == '')
            {
                alertify.alert("Start Date is a required field");
                return false;
            }
            
            if ($("#term").val() == '')
            {
                alertify.alert("Term is a required field");
                return false;
            }            
            
            calculate_amount();            
        });
    });
    
    function addCommas(nStr)
    {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }
</script>