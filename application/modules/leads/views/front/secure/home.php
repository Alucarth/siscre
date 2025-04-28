
<?php $this->load->view("partial/header"); ?>

<section class="section">
    <div class="row sameheight-container">
        <div class="col col-12 col-sm-12 col-md-4 col-xl-4">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Oustanding Balance</h5>
                            <span class="label label-primary pull-right">Current</span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-title border-top-primary">

                        </div>
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?php echo to_currency($current_outstanding_balance, 1, 0); ?></h1>
                            <small>Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-4 col-xl-4">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block"># of Payments made</h5>
                            <span class="label label-primary pull-right">Current</span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $current_num_payments; ?></h1>
                            <small>Total No. Payments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-4 col-xl-4">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Approved loan amount</h5>
                            <span class="label label-primary pull-right">Current</span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?php echo to_currency($current_approve_loan_amount, 1, 0); ?></h1>
                            <small>Amount</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="section">
    <div class="row sameheight-container">
        <div class="col-xl-12">
            <div class="card sameheight-item items" data-exclude="xs,sm,lg" style="height: 400px;">
                <div class="card-header bordered">
                    <div class="header-block">
                        <h3 class="title"> Current Loan Transactions</h3>
                    </div>
                </div>
                <ul class="item-list striped">
                    <li class="item item-list-header">
                        <div class="item-row">

                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span>Loan <br/> Transaction #</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span>Loan <br/> product</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-stats" style="text-align:center">
                                <div class="no-overflow">
                                    <span>Applied <br/> amount</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Last <br/>applied <br/>date</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Status</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Action</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php foreach ( $current_loan_transactions as $loan ): ?>
                        <li class="item">
                            <div class="item-row">
                                <div class="item-col">
                                    <div style="text-align:center"> <?php echo $loan->loan_id; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?php echo $loan->loan_product_name; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?php echo $loan->applied_amount ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?php echo $loan->applied_date; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?php echo $loan->application_status; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center">
                                        <a href="<?= site_url("loans/fix_breakdown/$loan->loan_id"); ?>" target="_blank" title="Show payment schedule">
                                            <span class="fa fa-files-o"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>
        </div>        
    </div>
</section>

<?php $this->load->view("partial/footer"); ?>