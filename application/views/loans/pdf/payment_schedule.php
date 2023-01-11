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
                            <!--<h3><?= $company_name; ?></h3>
                            <h4>
                                <?= $company_address; ?><br/>
                                <?= "Tel. No. " . $phone . " Fax " . $fax . " Email " . $email; ?>
                            </h4>-->
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
                    <td width="30%"></td>
                    <td width="30%"></td>
                    <td width="20%"><h4>SUCURSAL:</h4></td>
                    <td width="20%"><h4>La Paz</h4></td>
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
                    <!--<td><?= $this->lang->line("loan_type_term").":"; ?></td>-->
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
                    <!--<td><?= $this->lang->line("loan_type_payment_sched").":"; ?></td>-->
                    <!--<td>Cuota:</td>
                    <td></td>-->
                </tr>
                <tr>
                    <!--<td><?= $this->lang->line("loans_type").":"; ?></td>
                    <td><?=$loan->interest_type?></td>-->
                    <td>CI:</td>
                    <td><?= $document_number; ?></td>
                </tr>
                <!--<tr>
                    <td><?= $this->lang->line("loans_payment_date").":"; ?></td>
                    <td><?= $loan->loan_payment_date > 0 ? date($this->config->item('date_format'), $loan->loan_payment_date) : ''; ?></td>
                </tr>
                <tr>
                    <td><?= ktranslate2("PAYABLE AMOUNT")?>:</td>
                    <td><?= $payable; ?></td>
                </tr>-->
            </table>
            <!--
            <table class="table loans_pdf_loan_amount">
                <tr>
                    <td><strong><?= ktranslate2("APPLIED AMOUNT").":";?></strong></td>
                    <td style="text-align: right"><strong><?= $loan_amount; ?></strong></td>
                </tr>
                <tr>
                    <td><?= ktranslate2("PAYABLE AMOUNT")?>:</td>
                    <td style="text-align: right"><?= $payable; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><?= $this->lang->line("loan_type_less_charge") ?>:</td>
                </tr>

                <?php foreach ($misc_fees as $misc_fee): ?>
                    <tr>
                        <td><?= $misc_fee[0]; ?></td>
                        <td style="text-align: right"><?= $misc_fee[1]; ?></td>
                    </tr>
                <?php endforeach; ?>
                    
                <?php if ($loan->interest_type == 'loan_deduction'): ?>
                <tr>
                    <td><?= ktranslate2("Loan Interest")?>:</td>
                    <td style="text-align: right"><?=$loan_deduction_interest;?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><?= ktranslate2("TOTAL DEDUCTIONS") ?></td>
                    <td style="text-align: right"><?= $total_deductions; ?></td>
                </tr>
                <tr>
                    <td><?= strtoupper($this->lang->line("loan_type_net_proceed")) ?></td>
                    <td style="text-align: right"><?= $net_loan; ?></td>
                </tr>
                <?php if ( count($add_fees) > 0 ): ?>
                <tr>
                    <td><strong><?= ktranslate2("Additional Fees")?></strong></td>
                </tr>
                <?php endif ?>
                    
                <?php foreach( $add_fees as $desc => $amount ): ?>
                    <tr>
                        <td><?=$desc?></td>
                        <td style="text-align: right"><?= to_currency($amount); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
                -->
            <!--
            <div>
                <label><?= strtoupper($this->lang->line("loan_type_payment_sched")); ?></label>
                <ul class="checkbox-grid">
                    <?php foreach ($term_schedules as $key => $term_schedule): ?>
                        <?php if ($key === $term_period): ?>
                            <li>[x] <label for="text1"><?= $term_schedule; ?></label></li>
                        <?php else: ?>
                            <li>[ ] <label for="text1"><?= $term_schedule; ?></label></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
                        -->

            <div>
                <table width="100%" class="custom_table">
                    <tr>
                        <td width="16%" align="center"><strong>#</strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Payment Date");?></strong></td>
                        <!--<td align="center"><strong><?= ktranslate2("Grace Period");?></strong></td>-->
                        <td width="16%" align="center"><strong><?= ktranslate2("Principal<br/> Amount");?></strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Interest");?> (<?=$this->config->item("currency_symbol");?>)</strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Gastos Operativos");?></strong></td>
                        <td width="16%" align="center"><strong><?= ktranslate2("Amount to Pay");?></strong></td>
                        <!--<td align="center"><strong><?= ktranslate2("Penalty");?></strong></td>-->
                        <td width="17%" align="center"><strong><?= ktranslate2("Balance");?></strong></td>
                    </tr>
                <?php foreach ( $schedules as $key => $schedule ):?>
                    <tr>
                        <td>&nbsp;&nbsp;<?=$key +1;?></td>
                        <td>&nbsp;&nbsp;<?=$schedule->payment_date;?></td>
                        <!--<td style="text-align: center;"><?=isset($schedule->grace_period) ? $schedule->grace_period : '';?></td>-->
                        <!--<td align="right"><?= to_currency(($schedule->payment_amount - $schedule->interest), 1, 2);?></td>-->
                        <td align="right"><?= to_currency(($schedule->payment_amount - $schedule->interest - $schedule->operating_expenses_amount), 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->interest, 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->operating_expenses_amount, 1, 2);?></td>
                        <td align="right"><?= to_currency($schedule->payment_amount, 1, 2);?>&nbsp;&nbsp;</td>
                        <!--<td align="right"><?= to_currency($schedule->penalty_amount, 1, 2);?>&nbsp;&nbsp;</td>-->
                        <td align="right"><?= to_currency($schedule->payment_balance, 1, 2);?>&nbsp;&nbsp;</td>
                    </tr>
                <?php endforeach;?>
                </table>
                <hr>
            </div>
        </div>
    </body>
</html>