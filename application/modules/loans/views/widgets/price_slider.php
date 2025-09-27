<link rel="stylesheet" media="screen" href="<?=site_url('loans/')?>assets/slider/bootstrap-slider.css" />
<script src="<?=site_url('loans/')?>assets/slider/bootstrap-slider.js"></script>

<style>
    .slider-handle.min-slider-handle {
        position: absolute;
        top: -5px;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
        margin-left: -26px;
        width: 57px;
        height: 22px;
        background-image: -webkit-gradient(linear,left top,left bottom,from(#2ebde0),to(#04738e));
        background-image: linear-gradient(to bottom,#2ebde0,#04738e);
        cursor: pointer;
        border-radius: 11px;
        -ms-touch-action: none;
        touch-action: none;
    }
    
    .slider.slider-horizontal .slider-track {
        position: absolute;
        left: 0;
        width: 345px;
        height: 3px;
        background: #e7e7e7;
        cursor: pointer;
        border: 0;
        border-radius: 15px;
    }
    
    .slider-selection {
        position: absolute;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        background: #2ebde0;
        border-radius: 15px;
    }
    
    .slider.slider-horizontal {
        width: 300px;
        height: 20px;
    }
    
    .sliders__plus, .sliders__minus {
        position: absolute;
        top: 45px;
        display: block;
        width: 20px;
        height: 20px;
        background-repeat: no-repeat;
        background-position: center;
        cursor: pointer;
    }
    
    .sliders__minus {
        left: -6px;
        background-image: url(<?=site_url('loans/assets')?>/slider/minus.png);
        background-size: 9px 1px;
    }
    
    .sliders__plus {
        right: -20px;
        background-image: url(<?=site_url('loans/assets')?>/slider/plus.png);
        background-size: 9px 9px;
    }
</style>

<div class="hero_widget__calculation">
    
    <?php if ( isset($widget_title) && $widget_title != '' ): ?>
    
    <div class="hero_widget__title h1"><?=$widget_title; ?></div>
    
    <?php endif; ?>
    
    <div class="hero_widget__sliders">
        
        
        <input type="hidden" id="hid_apply_amount" value="<?=$default_apply_amount;?>" />
        <input type="hidden" id="hid_unpaid_amount" value="<?=$default_apply_amount + ( $default_apply_amount * ($default_interest_rate / 100) )?>" />
        <input type="hidden" id="hid_default_interest_rate" value="<?=$default_interest_rate;?>" />
        <input type="hidden" id="hid_first_payment_date" value="<?=$default_first_payment_date;?>" />
        
        <div class="sliders sliders-top">
            <span class="sliders__minus" id="sliderAmountMinus"></span>
            <span class="sliders__plus" id="sliderAmountPlus"></span>
            <span class="sliders__title medium semibold">I need <span class="sliders__value lightblue" id="needAmount">3,000 </span><span class="lightblue">PHP</span></span>
            
            <input type="text" class="span2" value="" data-slider-min="1000" data-slider-max="30000" data-slider-step="1000" data-slider-value="3000" id="need-amount" data-slider-tooltip="hide" data-slider-handle="square" />
            
        </div>
        <div class="credit_limitation small orange" role="creditLimitation">This amount is available to repeat borrowers only
            <div class="credit_limitation__close" role="creditLimitationClose"></div>
        </div>
        <div class="sliders">
            <span class="sliders__minus" id="sliderTermMinus"></span>
            <span class="sliders__plus" id="sliderTermPlus"></span>
            <span class="sliders__title medium semibold">for <span class="sliders__value lightblue" id="forTerms">15 </span> <span class="lightblue">days</span></span>
            <br/>
            <input type="text" class="span2" value="" data-slider-min="5" data-slider-max="30" data-slider-step="5" data-slider-value="15" id="slider-term" data-slider-tooltip="hide" data-slider-handle="square" />
            
            
            <input role="termSlider" data-slider-value="15" type="hidden" value="15" name="term" id="application_term" />
            <input role="termSliderMove" type="hidden" value="0" name="application[credit_term_slider_move]" id="application_credit_term_slider_move" />
            <div class="term_limitation small orange" role="termLimitation">This term is available to repeat borrowers only
                <div class="term_limitation__close" role="termLimitationClose"></div>
            </div>
        </div>
    </div>
    <div class="hero_widget__calc">
        <div class="hero_widget__item">
            <div class="hero_widget__value normal bold black"><span id="calcAmount">3,000 </span>PHP </div>
            <p class="hero_widget__name small">Loan amount</p>
        </div>
        <div class="hero_widget__item">
            <div class="hero_widget__value normal bold black"><span id="paymentDueDateLabel"><?=date('m.d.Y', strtotime("+15 days"))?></span></div>
            <p class="hero_widget__name small">First payment date</p>
        </div>
        <div class="hero_widget__item" role="totalValue">
            <div class="hero_widget__value normal bold black"><span id="calcTotal"><?=number_format(( 3000 + (3000*17/100) ), 0)?></span>PHP </div>
            <p class="hero_widget__name small">Total Repayment</p>
        </div>
    </div>
</div>

<script>
    $.fn.digits = function(){ 
        return this.each(function(){ 
            $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") ); 
        });
    };
    
    function pad (str, max) {
        str = str.toString();
        return str.length < max ? pad("0" + str, max) : str;
    }

    $(document).ready(function(){
        var AmountChange = function() {
            $('#needAmount').html(nd_amount.getValue()).digits();
            $('#calcAmount').html(nd_amount.getValue()).digits();
            
            $("#hid_apply_amount").val( nd_amount.getValue() );
            
            var calcTotal = nd_amount.getValue() + (nd_amount.getValue() * ($("#hid_default_interest_rate").val()/100));
            $("#hid_unpaid_amount").val(calcTotal);
            $("#calcTotal").html(calcTotal).digits();
        };
        
        var TermChange = function(){
            $("#forTerms").html(sl_term.getValue());
            $("#application_term").val(sl_term.getValue());
            
            $("#hid_default_interest_rate").val( sl_term.getValue() + 3 );
            AmountChange();
            
            var dueDate = new Date();
            var numberOfDaysToAdd = sl_term.getValue();
            dueDate.setDate(dueDate.getDate() + numberOfDaysToAdd);

            var dd = dueDate.getDate();
            var mm = dueDate.getMonth() + 1;
            var y = dueDate.getFullYear();

            var dueFormattedDate = pad(mm, 2) + '.'+ pad(dd, 2) + '.'+ y;
            $("#paymentDueDateLabel").html(dueFormattedDate);
            $("#hid_first_payment_date").val(y+'-'+mm+'-'+dd);
        };

        var nd_amount = $('#need-amount').slider()
                        .on('slide', AmountChange)
                        .data('slider');
        
        var sl_term = $('#slider-term').slider()
                        .on('slide', TermChange)
                        .data('slider');
                
        $("#sliderAmountPlus").click(function(){
            $('#need-amount').slider('setValue', nd_amount.getValue() + 1000);
            AmountChange();
        });
        $("#sliderAmountMinus").click(function(){
            $('#need-amount').slider('setValue', nd_amount.getValue() - 1000);
            AmountChange();
        });
        
        $("#sliderTermPlus").click(function(){
            $('#slider-term').slider('setValue', sl_term.getValue() + 5);
            TermChange();
        });
        $("#sliderTermMinus").click(function(){
            $('#slider-term').slider('setValue', sl_term.getValue() - 5);
            TermChange();
        });
    });
</script>