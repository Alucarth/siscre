<div class="steps__calc steps__calc-top">
    <div class="loan_information">
        <div class="loan_information__block">
            <div class="loan_information__item loan_information__item-mih">
                <p class="loan_information__name small">Net proceed (10% handling fee deducted upfront)</p>
                <div class="loan_information__value bold black"><span role="calcAmount"><?= number_format($proceed_amount, 2); ?> </span>PHP </div>
            </div>
            <div class="loan_information__item">
                <p class="loan_information__name small">First payment date</p>
                <div class="loan_information__value bold black"><span><?=date('m.d.y', strtotime($first_payment_date)); ?></span></div>
            </div>
            <div class="loan_information__item">
                <p class="loan_information__name small">Total Repayment</p>
                <div class="loan_information__value bold black"><span><?=  number_format($total_repayment, 2); ?> </span>PHP </div>
            </div>
        </div>
    </div>
</div>
<div class="steps__calc steps__calc-bot">
    <div class="loan_information">
        <hr class="hr">
        <div class="loan_information__block">
            <div class="loan_information__item loan_information__item-mih">
                <p class="loan_information__name small">Total Charges Applied</p>
                <div class="loan_information__value bold black"><span role="totalInterestAmount"><?=$total_interest_amount;?> </span>PHP </div>
            </div>
        </div>
    </div>
</div>