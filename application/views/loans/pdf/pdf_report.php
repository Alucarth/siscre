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
            .custom_table td {
                border: 2px solid black;
                vertical-align: top;
                padding: 5px;
            }
            .loans_pdf_company_name, .loans_pdf_title{
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div>
            <table class="table">
                <tr>
                    <td width="30%">
                        <div class="loans_pdf_company_name">
                            <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("/uploads/logo/" . $this->config->item('logo')) : base_url("/uploads/common/no_img.png"); ?>" style="height:50px" />
                        </div>
                    </td>
                    <td width="70%">
                        <div class="loans_pdf_title">
                            <h4>COMPROBANTE DE DESEMBOLSO</h4>
                        </div>
                    </td>
                </tr>    
            </table>
        </div>

        <table class="table">
            <tr>
                <td><?= $this->lang->line("loans_apply_date").":"; ?></td>
                <td><?= date($this->config->item('date_format'), $loan->loan_applied_date); ?></td>
                <td width="25%"><?= $this->lang->line("common_full_name").":"; ?></td>
                <!--<td width="25%"><?= $customer_id; ?></td>-->
                <td width="25%"><?= $customer_name; ?></td>
                <!--<td width="25%"><?= $this->lang->line("common_address_present").":"; ?></td>
                <td colspan="3" width="25%"><?= $customer_address; ?></td>-->
            </tr>

            <tr>
                <td>Agencia:</td>
                <td>1</td>
                <td><?=ktranslate2("Loan Interest")?>:</td>
                <td style="text-align: left"><?=$loan->interest_rate . "%";?></td>
                <!--<td><?= $this->lang->line("loans_type").":"; ?></td>
                <td><?= str_replace("_", " ", $loan->interest_type); ?></td>-->
                <!--<td><?= $this->lang->line("loan_type_term").":"; ?></td>
                <td><?= $term . " " . $term_period; ?></td>-->
                <!--<td>Interest Rate</td>
                <td><?= $rate ?>%</td>-->
            </tr>
            <tr>
                <td>Asesor:</td>
                <td>1</td>
                <!--<td><?= $this->lang->line("loans_apply_date").":"; ?></td>
                <td><?= date($this->config->item('date_format'), $loan->loan_applied_date); ?></td>-->
                <!--<td><?= $this->lang->line("loans_payment_date").":"; ?></td>
                <td><?= date($this->config->item('date_format'), $loan->loan_payment_date); ?></td>-->
                <!--<td><?= $this->lang->line("loan_type_penalty"); ?></td>
                <td>__</td>-->
            </tr>
        </table>
        <!--
        <div>
            <label><?= strtoupper($this->lang->line("loan_type_payment_sched")); ?></label>
            <ul class="checkbox-grid">
                <?php foreach ($schedules as $key => $schedule): ?>
                    <?php if ($key === $term_period): ?>
                        <li>[x] <label for="text1"><?= $schedule; ?></label></li>
                    <?php else: ?>
                        <li>[ ] <label for="text1"><?= $schedule; ?></label></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
                    -->
        <div>
            <?= $this->lang->line("loan_type_acknowledgment"); ?>
        </div>
        <table class="custom_table" width="100%">
            <tr>
                <td width="50%" vertical-align="top"><strong>NOMBRE</strong></td>
                <td width="20%"><strong>CI</strong></td>
                <td width="30%"><strong>FIRMA(S) CLIENTE(S)</strong></td>
            </tr>
            <tr>
                <td rowspan="2" vertical-align="top"><?= $customer_name; ?></td>
                <td height="50"> <?= $document_number; ?></td>
                <td height="50"></td>
            </tr>
            <tr>
                <!--<td height="100">Garante</td>-->
                <td height="50"></td>
                <td height="50"></td>
            </tr>
            <tr>
                <td width="50%"><strong><?= ktranslate2("APPLIED AMOUNT")?>:</strong></td>
                <td width="20%"><strong><?= $loan_amount; ?></strong></td>
                <td width="30%"><strong>FIRMA(S) AUTORIZADA(S)</strong></td>        
            </tr>
            <tr>
                <td colspan="2" rowspan="2"></td>
                <!--<td height="50"></td>-->
                <td height="50"></td>
            </tr>
            <tr>
                <!--<td height="100">Garante</td>-->
                <!--<td height="50"></td>-->
                <td height="50"></td>
            </tr>
        </table>
    </body>
</html>