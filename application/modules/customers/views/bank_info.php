<div style="text-align: center">

    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

    <ul id="error_message_box"></ul>

</div>                                        



<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Banco:

    </label>

    <div class="col-sm-9">

        <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?= $person_info->bank_name; ?>" />

    </div>

</div>

<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Número de cuenta:

    </label>

    <div class="col-sm-9">

        <input type="text" class="form-control" id="account_number" name="account_number" value="<?= $person_info->account_number; ?>" />

    </div>

</div>

<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Nombre del titular de la cuenta:

    </label>

    <div class="col-sm-9">

        <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" value="<?= $person_info->account_holder_name; ?>" />

    </div>

</div>



<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        GCASH:

    </label>

    <div class="col-sm-9">

        <input type="text" class="form-control" id="gcash_num" name="gcash_num" value="<?= $person_info->gcash_num; ?>" />

    </div>

</div>