
<div class="hero_widget__calculation">

    <?php if (isset($widget_title) && $widget_title != ''): ?>

        <div class="hero_widget__title h1"><?= $widget_title; ?></div>

    <?php endif; ?>

    <div class="form__group string optional application_apply_amount form-group">
        <label class="required form__label"><abbr title="required">*</abbr> How much do you need?</label>
        <input class="string optional form_float__input" placeholder="0.00" type="text" name="application_apply_amount" id="application_apply_amount">
    </div>
    <div class="form__group select required application_type_of_document" aria-required="true">
        <label class="select required form__label" for="application_loan_product" aria-required="true"><abbr title="required">*</abbr> Select Loan Product</label>

        <select data-placeholder="Please Select" class="select required form__input form__input-select input-lg" name="application_loan_product" id="application_loan_product" tabindex="-1" aria-hidden="true" aria-required="true">
            <option value=""></option>
            <?php foreach ( $loan_products as $loan_product ): ?>
            <option value="<?=$loan_product->id;?>"><?=$loan_product->product_name;?></option>
            <?php endforeach; ?>
        </select>

    </div>


</div>

<script>
    $.fn.digits = function () {
        return this.each(function () {
            $(this).text($(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
        });
    };

    function pad(str, max) {
        str = str.toString();
        return str.length < max ? pad("0" + str, max) : str;
    }

    $(document).ready(function () {
        
        var AmountChange = function () {
            $('#needAmount').html(nd_amount.getValue()).digits();
            $('#calcAmount').html(nd_amount.getValue()).digits();

            $("#hid_apply_amount").val(nd_amount.getValue());

            var calcTotal = nd_amount.getValue() + (nd_amount.getValue() * ($("#hid_default_interest_rate").val() / 100));
            $("#hid_unpaid_amount").val(calcTotal);
            $("#calcTotal").html(calcTotal).digits();
        };

        var TermChange = function () {
            $("#forTerms").html(sl_term.getValue());
            $("#application_term").val(sl_term.getValue());

            $("#hid_default_interest_rate").val(sl_term.getValue() + 3);
            AmountChange();

            var dueDate = new Date();
            var numberOfDaysToAdd = sl_term.getValue();
            dueDate.setDate(dueDate.getDate() + numberOfDaysToAdd);

            var dd = dueDate.getDate();
            var mm = dueDate.getMonth() + 1;
            var y = dueDate.getFullYear();

            var dueFormattedDate = pad(mm, 2) + '.' + pad(dd, 2) + '.' + y;
            $("#paymentDueDateLabel").html(dueFormattedDate);
            $("#hid_first_payment_date").val(y + '-' + mm + '-' + dd);
        };

        var nd_amount = $('#need-amount').slider()
                .on('slide', AmountChange)
                .data('slider');

        var sl_term = $('#slider-term').slider()
                .on('slide', TermChange)
                .data('slider');

        $("#sliderAmountPlus").click(function () {
            $('#need-amount').slider('setValue', nd_amount.getValue() + 1000);
            AmountChange();
        });
        $("#sliderAmountMinus").click(function () {
            $('#need-amount').slider('setValue', nd_amount.getValue() - 1000);
            AmountChange();
        });

        $("#sliderTermPlus").click(function () {
            $('#slider-term').slider('setValue', sl_term.getValue() + 5);
            TermChange();
        });
        $("#sliderTermMinus").click(function () {
            $('#slider-term').slider('setValue', sl_term.getValue() - 5);
            TermChange();
        });
    });
</script>