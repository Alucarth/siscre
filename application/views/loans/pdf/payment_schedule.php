<html>
    <head>
        <link rel="stylesheet" rev="stylesheet" href="<?php echo base_url(); ?>bootstrap3/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>font-awesome-4.3.0/css/font-awesome.min.css" />
        <style>
            ul.checkbox-grid li {
                display: block;
                float: left;
                width: 40%;
                text-decoration: none;
            }

            .loans_pdf_company_name, .loans_pdf_title{
                text-align: center;
            }
            .custom_table td {
                border: 2px solid black;
                padding: 5px;
            }
            hr.new2 {
                border-top: 1px dashed black;
            }
        </style>
    </head>
    <body>
        <div>
            <table class="table">
                <tr>
                    <td width="35%">
                        <div class="loans_pdf_company_name">
                            <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("/uploads/logo/" . $this->config->item('logo')) : base_url("/uploads/common/no_img.png"); ?>" style="height:50px" />
                        </div>
                    </td>
                    <td width="65%">
                        <div class="loans_pdf_title">
                            <h4><?= $this->lang->line("loans_schedule_title"); ?></h4>
                        </div>
                    </td>
                </tr>    
            </table>
        </div>
        <div height="50%">
            <table class="table">
                <tr>
                    <td width="18%"></td>
                    <td width="40%"></td>
                    <td width="22%"><h4>SUCURSAL:</h4></td>
                    <td width="20%"><h4>Puente Vela</h4></td>
                </tr>
                <tr>
                    <td>Tipo de operación: </td>
                    <td>INDIVIDUAL</td>
                    <td><?= $this->lang->line("loans_apply_date").":"; ?></td>
                    <td><?= date($this->config->item('date_format'), $loan->loan_applied_date); ?></td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td><?=$loan->customer_id?>&nbsp;-&nbsp;<?= $customer_name; ?></td>
                    <td><strong><?= ktranslate2("APPLIED AMOUNT").":";?></strong></td>
                    <td><strong><?= $loan_amount; ?></strong></td>
                </tr>
                <tr>
                    <td>Moneda:</td>
                    <td>BOLIVIANOS</td>
                    <td>Taza de interés:</td>
                    <td><?= $rate; ?>%</td>
                </tr>
                <tr>
                    <td>Plazo:</td>
                    <td>
                        <?= $term . " " . $term_schedule; ?>
                        <?php foreach ($term_schedules as $key => $term_schedule): ?>
                            <?php if ($key === $term_period): ?>
                                <label for="text1"><?= $term_schedule; ?></label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                    <td>Cantidad de cuotas:</td>
                    <td><?=sizeof($schedules);?></td>
                </tr>
            </table>
            <div>
                <table width="100%" class="custom_table">
                    <tr>
                        <td width="16%" align="center"><strong>#</strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Payment Date");?></strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Principal<br/> Amount");?></strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Interest");?> (<?=$this->config->item("currency_symbol");?>)</strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Ahorro");?></strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Amount to Pay");?></strong></td>
                        <td width="17%" align="center"><strong><?= ktranslate2("Balance");?></strong></td>
                    </tr>
                <?php foreach ( $schedules as $key => $schedule ):?>
                    <tr>
                        <td>&nbsp;&nbsp;<?=$key +1;?></td>
                        <td>&nbsp;&nbsp;<?=$schedule->payment_date;?></td>
                        <td align="right"><?= to_currency($schedule->payment_amount_capital, 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->interest, 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->operating_expenses_amount, 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->payment_amount, 1, 2);?>&nbsp;&nbsp;</td>
                        <td align="right"><?= to_currency($schedule->payment_balance, 1, 2);?>&nbsp;&nbsp;</td>
                    </tr>
                <?php endforeach;?>
                </table>
                <hr>
            </div>
        </div>
    </body>
</html>