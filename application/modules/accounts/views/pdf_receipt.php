<html>
    <head>
        <title>Comprobante <?=date("Y-m-d H:i:s");?></title>
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
        </style>
    </head>
    <body>
        <table width="100%">
            <tr>
                <td width="50%">
                    <div>
                        <table width="95%", style= "border: 1px solid #ccc; background-color: #d5d8dc; margin:auto">
                            <tr>
                                <td style="text-align: left; padding: 20px;">
                                    <h4><b><?= ucwords($this->config->item('company')); ?></b></h4>
                                </td>
                                <td style="text-align: right">
                                    <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="height:50px" /><br/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <p></p>
                    <div>
                        <table width="95%", style= "border: 1px solid #ccc; margin:auto">
                            <tr>
                                <td colspan="4", style="padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h4>
                                        <b><?=($trans_type == 'deposit' ? '*** Depósito en cuenta ***' : '*** Retiro de cuenta ***')?></b>
                                    </h4>
                                </td>
                            </tr>
                            <hr/>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Sucursal:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($branch_name); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cajero:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($person_name); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Fecha:</b></td>
                                <!--<td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ($trans_date); ?><br/></td>-->
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?= date($this->config->item("date_format")); ?><br/></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Transaccion:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ($trans_id); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cuenta:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ($account_id); ?></td>
                                <td width="2%"></td>
                            </tr>   
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cliente:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($customer); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <!--
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Por cuenta de:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($customer); ?></td>
                                <td width="2%"></td>
                            </tr>
                            -->
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;">
                                    <b><?=($trans_type == 'deposit' ? 'Monto depósito' : 'Monto retiro')?></b> :
                                </td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;">
                                    <?=to_currency($amount);?>
                                </td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="48%" height="150p" style="border-right: 1px solid #F2F3F4; color: #F2F3F4; padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h2><b>Sello autorizado</b></h2>
                                </td>
                                <td width="48%" height="150p" style="border-left: 1px solid #F2F3F4; color: #F2F3F4; padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h3><b>Firma <br>Documento Cliente</b></h3>
                                </td>
                                <td width="2%"></td>
                            </tr>
                        </table>
                        <table width="95%", style= "border: 1px solid #ccc; background-color: #d5d8dc; margin:auto">
                            <tr>
                                <td style="color: #7f8c8d; text-align: center; padding: 20px;">
                                    <h5><b>ORIGINAL</b></h5>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <!--<hr border-top="3px dashed #bbb">-->
                <td width="50%">
                    <div>
                        <table width="95%", style= "border: 1px solid #ccc; background-color: #d5d8dc; margin:auto">
                            <tr>
                                <td style="text-align: left; padding: 20px;">
                                    <h4><b><?= ucwords($this->config->item('company')); ?></b></h4>
                                </td>
                                <td style="text-align: right">
                                    <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="height:50px" /><br/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <p></p>
                    <div>
                        <table width="95%", style= "border: 1px solid #ccc; margin:auto">
                            <tr>
                                <td colspan="4", style="padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h4>
                                        <b><?=($trans_type == 'deposit' ? '*** Depósito en cuenta ***' : '*** Retiro de cuenta ***')?></b>
                                    </h4>
                                </td>
                            </tr>
                            <hr/>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Sucursal:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($branch_name); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cajero:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($person_name); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Fecha:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?= date($this->config->item("date_format")); ?><br/></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Transaccion:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ($trans_id); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cuenta:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ($account_id); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Cliente:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($customer); ?></td>
                                <td width="2%"></td>
                            </tr>
                            <!--
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;"><b>Por cuenta de:</b></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;"><?php echo ucwords($customer); ?></td>
                                <td width="2%"></td>
                            </tr>   
                            -->
                            <tr>
                                <td width="2%"></td>
                                <td style="border-bottom: 1px solid #ccc;text-align:left;">
                                    <b><?=($trans_type == 'deposit' ? 'Monto depósito' : 'Monto retiro')?></b> :
                                </td>
                                <td style="border-bottom: 1px solid #ccc;text-align:right;">
                                    <?=to_currency($amount);?>
                                </td>
                                <td width="2%"></td>
                            </tr>
                            <tr>
                                <td width="2%"></td>
                                <td width="48%" height="150p" style="border-right: 1px solid #F2F3F4; color: #F2F3F4; padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h2><b>Sello autorizado</b></h2>
                                </td>
                                <td width="48%" height="150p" style="border-left: 1px solid #F2F3F4; color: #F2F3F4; padding-left: 10px; padding-right: 10px; padding-top: 10px; text-align: center">
                                    <h3><b>Firma <br>Documento Cliente</b></h3>
                                </td>
                                <td width="2%"></td>
                            </tr>
                        </table>
                        <table width="95%", style= "border: 1px solid #ccc; background-color: #d5d8dc; margin:auto">
                            <tr>
                                <td style="color: #7f8c8d; text-align: center; padding: 20px;">
                                    <h5><b>COPIA CLIENTE</b></h5>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>