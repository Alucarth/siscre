
<?php $this->load->view("partial/header"); ?>

<section class="section">
    <div class="row sameheight-container">
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Filter</h5>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-title border-top-primary">

                        </div>
                        <div class="inqbox-content">
                            <div class="input-group">
                                <select class="form-control" id="sel-period">
                                    <option value="ytd">YTD</option>
                                    <option value="mtd" <?=isset($_GET["p"]) && $_GET["p"] == 'mtd' ? 'selected="selected"' : ''?>>MTD</option>
                                    <option value="lm" <?=isset($_GET["p"]) && $_GET["p"] == 'lm' ? 'selected="selected"' : ''?>>Last Month</option>
                                </select>
                                <span class="input-group-addon input-group-prepend"><a href="javascript:void(0)" class="input-group-text" id="btn-search"><i class="fa fa-search"></i></a></span>
                            </div>
                            <small>Choose period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block"><?= $this->lang->line('common_my_wallet'); ?></h5>
                            <span class="label label-primary pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-title border-top-primary">

                        </div>
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?php echo to_currency($my_wallet, 1, 0); ?></h1>
                            <small><?= $this->lang->line('common_total_wallet'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block"><?= $this->lang->line('common_loans'); ?></h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $total_loans; ?></h1>
                            <small><?= $this->lang->line('common_total_loans'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Interest</h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $total_interest; ?></h1>
                            <small>Total Interest</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
        
<section class="section">
    <div class="row sameheight-container">
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Fees</h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $total_fees; ?></h1>
                            <small>Total Fees</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Collections</h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-title border-top-primary">

                        </div>
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?php echo $total_payments; ?></h1>
                            <small>Total Payments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block"># Payments</h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-title border-top-primary">

                        </div>
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $total_num_payments; ?></h1>
                            <small>Total # of Payments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-12 col-sm-12 col-md-3 col-xl-3">
            <div class="card sameheight-item" data-exclude="xs">
                <div class="card-header card-header-sm bordered">
                    <div class="header-block" style="width:100%">
                        <h3 class="title">
                            <h5 style="display:inline-block">Customers</h5>
                            <span class="label label-info pull-right"><?=$period_label?></span>
                        </h3>
                    </div>                   
                </div>
                <div class="card-block">
                    <div class="inqbox float-e-margins">
                        <div class="inqbox-content">
                            <h1 class="no-margins"><?= $total_borrowers; ?></h1>
                            <small>Total Customers</small>
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
                        <h3 class="title"> Latest loans</h3>
                        <a href="<?= site_url('loans/view/-1') ?>" class="btn btn-primary btn-sm" target="_blank"> Add new </a>
                    </div>
                </div>
                <ul class="item-list striped">
                    <li class="item item-list-header">
                        <div class="item-row">
                            <div class="item-col item-col-header fixed item-col-img xs"></div>
                            <div class="item-col item-col-header item-col-title">
                                <div>
                                    <span>Customer name</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span>Approved amount</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-stats" style="text-align:center">
                                <div class="no-overflow">
                                    <span>Balance</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Approved <br/>date</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php foreach ($latest_loans as $customer): ?>
                        <li class="item">
                            <div class="item-row">
                                <div class="item-col fixed item-col-img xs">
                                    <a href="<?= site_url('loans/view/' . $customer->loan_id) ?>" target="_blank">
                                        <div class="item-img xs rounded" style="background-image: url(<?= base_url('uploads/profile-' . $customer->person_id . '/' . $customer->photo_url) ?>)"></div>
                                    </a>
                                </div>
                                <div class="item-col no-overflow">
                                    <div>
                                        <a href="<?= site_url('loans/view/' . $customer->loan_id); ?>" target="_blank">
                                            <h4 class="no-wrap"><?= $customer->full_name; ?></h4>
                                        </a>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?= $customer->loan_approved > 0 ? to_currency($customer->loan_approved) : ''; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center">
                                        <?= ( $customer->loan_balance > 0 ) ? to_currency($customer->loan_balance) : ''; ?>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?= ( $customer->approved_date > strtotime(date("1970-01-01 00:00:00")) ) ? date($this->config->item("date_format"), $customer->approved_date) : ''; ?> </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>
        </div>
        
    </div>
</section>

<section class="section">
    <div class="row sameheight-container">
        <div class="col-xl-8">
            <div class="card sameheight-item items" data-exclude="xs,sm,lg" style="height: 400px;">
                <div class="card-header bordered">
                    <div class="header-block">
                        <h3 class="title"> Top customers</h3>
                        <a href="<?= site_url('loans/view/-1') ?>" class="btn btn-primary btn-sm" target="_blank"> Add new </a>
                    </div>
                </div>
                <ul class="item-list striped">
                    <li class="item item-list-header">
                        <div class="item-row">
                            <div class="item-col item-col-header fixed item-col-img xs"></div>
                            <div class="item-col item-col-header item-col-title">
                                <div>
                                    <span>Customer name</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span># Loans <br/> approved</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-stats" style="text-align:center">
                                <div class="no-overflow">
                                    <span># payments <br/> made</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Last <br/>applied <br/>date</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php foreach ($top_customers as $customer): ?>
                        <li class="item">
                            <div class="item-row">
                                <div class="item-col fixed item-col-img xs">
                                    <a href="<?= site_url('customers/view/' . $customer->person_id) ?>" target="_blank">
                                        <div class="item-img xs rounded" style="background-image: url(<?= base_url('uploads/profile-' . $customer->person_id . '/' . $customer->photo_url) ?>)"></div>
                                    </a>
                                </div>
                                <div class="item-col no-overflow">
                                    <div>
                                        <a href="<?= site_url('customers/view/' . $customer->person_id); ?>" target="_blank">
                                            <h4 class="no-wrap"><?= $customer->full_name; ?></h4>
                                        </a>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?= $customer->total_loan_approved > 0 ? $customer->total_loan_approved : ''; ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center">
                                        <?= ( $customer->total_payments_made > 0 ) ? $customer->total_payments_made : ''; ?>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?= ( $customer->last_applied_date > strtotime(date("1970-01-01 00:00:00")) ) ? date($this->config->item("date_format"), $customer->last_applied_date) : ''; ?> </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card sameheight-item sales-breakdown" data-exclude="xs,sm,lg" style="height: 400px;">
                <div class="card-header">
                    <div class="header-block">
                        <h3 class="title">Sales breakdown by product</h3>
                    </div>
                </div>
                <div class="card-block">
                    <div class="dashboard-sales-breakdown-chart" id="dashboard-loans-breakdown-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            var e = $("#dashboard-loans-breakdown-chart");
            if (!e.length)
                return !1;

            function sales_graph() {
                e.empty(),
                        Morris.Donut({
                            element: "dashboard-loans-breakdown-chart",
                            data: <?= $sales_chart_data ?>,
                            resize: !0,
                            colors: [
                                tinycolor(config.chart.colorPrimary.toString()).lighten(10).toString(),
                                tinycolor(config.chart.colorPrimary.toString()).darken(8).toString(),
                                config.chart.colorPrimary.toString()
                            ]
                        });
            }
            ;
            sales_graph();
        });
    </script>

</section>

<section class="section">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-block">
                    <div class="card-title-block">
                        <h3 class="title" style="display:inline-block"> No. of transactions per month</h3>
                    </div>
                    <section>
                        <div id="div-transaction-chart"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            if (!$("#div-transaction-chart").length)
                return !1;

            function transaction_chart() {
                $("#div-transaction-chart").empty(),
                        Morris.Line({
                            element: "div-transaction-chart",
                            data: <?= $transaction_chart_data; ?>,
                            xkey: "month",
                            ykeys: ["value"],
                            resize: !0,
                            lineWidth: 4,
                            labels: ["Loans"],
                            lineColors: [config.chart.colorPrimary.toString()],
                            pointSize: 5,
                            dateFormat: function (y) {
                                var d = new Date(y);
                                var month = new Intl.DateTimeFormat('en', {month: 'short'}).format(d);
                                var day = new Intl.DateTimeFormat('en', {day: '2-digit'}).format(d);

                                return month + " " + day;
                            },
                            xLabelFormat: function (y) {
                                var d = new Date(y);
                                // var year = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
                                var month = new Intl.DateTimeFormat('en', {month: 'short'}).format(d);
                                var day = new Intl.DateTimeFormat('en', {day: '2-digit'}).format(d);

                                return month + " " + day;
                            }
                        });
            }
            transaction_chart();
        })
    </script>

</section>

<section class="section">
    <div class="row sameheight-container">
        <div class="col-xl-12">
            <div class="card sameheight-item items" data-exclude="xs,sm,lg" style="height: 400px;">
                <div class="card-header bordered">
                    <div class="header-block">
                        <h3 class="title"> Receivables</h3>
                        <a href="<?= site_url('overdues') ?>" class="btn btn-primary btn-sm" target="_blank"> View all</a>

                    </div>
                </div>
                <ul class="item-list striped">
                    <li class="item item-list-header">
                        <div class="item-row">
                            <div class="item-col item-col-header fixed item-col-img xs"></div>
                            <div class="item-col item-col-header item-col-title">
                                <div>
                                    <span>Customer name</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span>Balance</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-stats" style="text-align:center">
                                <div class="no-overflow">
                                    <span># payments <br/> made</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Due <br/>date</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php foreach ($overdue_customers as $customer): ?>
                        <li class="item">
                            <div class="item-row">
                                <div class="item-col fixed item-col-img xs">
                                    <a href="<?= site_url('customers/view/' . $customer->person_id) ?>" target="_blank">
                                        <div class="item-img xs rounded" style="background-image: url(<?= base_url('uploads/profile-' . $customer->person_id . '/' . $customer->photo_url) ?>)"></div>
                                    </a>
                                </div>
                                <div class="item-col no-overflow">
                                    <div>
                                        <a href="<?= site_url('customers/view/' . $customer->person_id); ?>" target="_blank">
                                            <h4 class="no-wrap"><?= $customer->full_name; ?></h4>
                                        </a>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> <?= to_currency($customer->loan_balance, 1); ?> </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center">
                                        <?= $customer->total_payments_made; ?>
                                    </div>
                                </div>
                                <div class="item-col">
                                    <div style="text-align:center"> 
                                        <?php
                                        $scheds = json_decode($customer->periodic_loan_table);
                                        $next_payment_date = sync_payment_date($customer->due_paid, $scheds);

                                        echo date($this->config->item("date_format"), $next_payment_date);
                                        ?> </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ul>
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
                        <h3 class="title"> Due today</h3>
                        <a href="<?=site_url('overdues')?>" class="btn btn-primary btn-sm" target="_blank"> View all</a>
                        
                    </div>
                </div>
                
                <?php if ( count($overdue_today_customers) > 0 ): ?>
                
                <ul class="item-list striped">
                    <li class="item item-list-header">
                        <div class="item-row">
                            <div class="item-col item-col-header fixed item-col-img xs"></div>
                            <div class="item-col item-col-header item-col-title">
                                <div>
                                    <span>Customer name</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-sales" style="text-align:center">
                                <div>
                                    <span>Balance</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-stats" style="text-align:center">
                                <div class="no-overflow">
                                    <span># payments <br/> made</span>
                                </div>
                            </div>
                            <div class="item-col item-col-header item-col-date" style="text-align:center">
                                <div>
                                    <span>Due <br/>date</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    
                    <?php foreach ( $overdue_today_customers as $customer ): ?>
                    <li class="item">
                        <div class="item-row">
                            <div class="item-col fixed item-col-img xs">
                                <a href="<?=site_url('customers/view/' . $customer->person_id)?>" target="_blank">
                                    <div class="item-img xs rounded" style="background-image: url(<?=base_url('uploads/profile-' . $customer->person_id . '/' . $customer->photo_url)?>)"></div>
                                </a>
                            </div>
                            <div class="item-col no-overflow">
                                <div>
                                    <a href="<?=site_url('customers/view/' . $customer->person_id);?>" target="_blank">
                                        <h4 class="no-wrap"><?=$customer->full_name;?></h4>
                                    </a>
                                </div>
                            </div>
                            <div class="item-col">
                                <div style="text-align: center"> <?= to_currency($customer->loan_balance, 1);?> </div>
                            </div>
                            <div class="item-col">
                                <div style="text-align: center">
                                    <?=$customer->total_payments_made?>
                                </div>
                            </div>
                            <div class="item-col">
                                <div style="text-align:center"> <?=date($this->config->item("date_format"), $customer->loan_payment_date);?> </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    
                </ul>
                
                <?php else: ?>
                
                <div style="padding: 15px; text-align:center">No records found.</div>
                
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</section>

<?php echo form_open();?>
<?php echo form_close();?>

<?php $this->load->view("partial/footer"); ?>
<script>
    $(document).ready(function(){
        $("#sel-period").select2('destroy');
        $("#btn-search").click(function(){
            window.location.href = '<?=site_url('home')?>?p=' + $("#sel-period").val();
        });
    });
</script>