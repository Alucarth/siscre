<html>
    <head>
        <title>Print Preview</title>
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
            
            <?php if ( $is_mobile ): ?>
                table td{
                    font-size:30px !important;
                }
                #img-pic{
                    height: 200px !important;
                }
            <?php else: ?>
                #img-pic{
                    height: 99px !important;
                }
            <?php endif; ?>
        </style>
    </head>
    <body>

        <div>

            <table width="100%">
                <tr>
                    <td align="center">
                        <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" /><br/>
                        <?= ucwords($this->config->item('company')); ?><br/>
                        <?= ucwords($this->config->item('address')); ?><br/>
                        <?= $this->config->item('phone') . " " . $this->config->item('rnc'); ?><br/>
                    </td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td>Date:</td>
                    <td align="right"><?= $trans_date; ?></td>
                </tr>
                <tr>
                    <td>Account:</td>
                    <td align="right"><?= $account; ?></td>
                </tr>
                <tr>
                    <td>Borrower:</td>
                    <td align="right"><?= $client; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td>Address 1:</td>
                    <td align="right"><?= $customer->address_1; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td>Address 2:</td>
                    <td align="right"><?= $customer->address_2; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td>Phone Number:</td>
                    <td align="right"><?= $customer->phone_number; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td>Loan:</td>
                    <td align="right"><?= $loan; ?></td>
                </tr>
                <tr>
                    <td>Paid:</td>
                    <td align="right"><?= $paid; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td>Balance:</td>
                    <td align="right"><?= $balance; ?></td>
                </tr>
                <tr>
                    <td>Transaction No.</td>
                    <td align="right"><?= $count; ?></td>
                </tr>
            </table>

            <br/>
            <br/>
            <br/>

            <table width="100%">
                <tr>
                    <td align="center">
                        Thank you!
                    </td>
                </tr>
            </table>


        </div>

    </body>

</html>