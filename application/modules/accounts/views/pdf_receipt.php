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

        <div>

            <table width="100%">
                <tr>
                    <td align="center">
                        <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="height:99px" /><br/>
                        <?= ucwords($this->config->item('company')); ?><br/>
                        <?= ucwords($this->config->item('address')); ?><br/>
                        <?= $this->config->item('phone') . " " . $this->config->item('rnc'); ?><br/>
                    </td>
                </tr>
            </table>

            <table class="table">
                <tr>
                    <td colspan="2"><hr/></td>
                </tr>
                <tr>
                    <td>Nombre del cliente:</td>
                    <td style="text-align:right;"><?php echo ucwords($customer); ?></td>
                </tr>
                <tr>
                    <td>
                        <?=($trans_type == 'deposit' ? 'Monto depósito' : 'Monto retiro')?> :
                    </td>
                    <td style="text-align: right">
                        <?=to_currency($amount);?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr/></td>
                </tr>
            </table>

            <br/>
            <br/>
            <br/>

            <table width="100%">
                <tr>
                    <td align="center">
                        <h3>¡Gracias!</h3>
                    </td>
                </tr>
            </table>


        </div>

    </body>

</html>