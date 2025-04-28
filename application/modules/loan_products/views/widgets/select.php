<div class="form-group row">
    <label class="control-label col-sm-2 text-xs-right">
        Producto de préstamo:
    </label>
    <div class="col-sm-10">
        <select class="form-control" id="loan_product" name="loan_product" disabled="disabled">
            <option>Elegir</option>
        </select>
    </div>
</div>

<script>
    $(document).ready(function(){
        var url = '<?=site_url('loan_products/ajax')?>';
        var params = {
            softtoken: $("input[name='softtoken']").val(),
            type: 2,
            val: '<?=isset($val) ? $val : ''?>'
        };
        
        $.post(url, params, function(data){
            if ( data.status == "OK" )
            {
                $("#loan_product").html(data.options);
            }
            $("#loan_product").prop("disabled", false);
        }, "json");
        
        $("#loan_product").change(function(){
            var url = '<?=site_url('loan_products/ajax');?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type: 3,
                id: $(this).val()
            };
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#sp-interest-type-label").html(data.loan_product_info.interest_type_name);
                    $("#DTE_Field_interest_type").val(data.loan_product_info.interest_type);
                    $("#interest_rate").val(data.loan_product_info.interest_rate);
                    $("#term").val(data.loan_product_info.term);
                    $("#term_period").val(data.loan_product_info.term_period);
                    $("#penalty_value").val(data.loan_product_info.penalty_value);
                    $("#hid-penalty-type").val(data.loan_product_info.penalty_type);
                    
                    if ( data.loan_product_info.penalty_type == 'percentage' )
                    {
                        $("#btn-toggle-penalty-type").html("%");
                    }
                    else
                    {
                        $("#btn-toggle-penalty-type").html("Amt");                        
                    }
                    
                    $("#tbl-grace-periods tbody").html( data.loan_product_info.grace_periods_html );
                }
            }, "json");
        });
    });
</script>