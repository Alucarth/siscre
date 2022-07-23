<html>
    <head>
        <title>Vista previa de impresión</title>
        <link rel="stylesheet" rev="stylesheet" href="<?php echo base_url(); ?>bootstrap3/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>font-awesome-4.3.0/css/font-awesome.min.css" />
        <style>
            body {
                background: white!important;
                color: #000!important;
            }
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
                    font-size:35px !important;
                }
                #img-pic{
                    height: 200px !important;
                }
                hr {
                    border: none;
                    color:#000!important;
                }
            <?php else: ?>
                #img-pic{
                    height: 99px !important;
                }
            <?php endif; ?>
        </style>
    </head>
    <body>
       
           
            <div >
                <table>
                    <tbody>
                        <tr>
                        <td>
                            <table width="30%" align="center">
                                <tr>
                                    <td align="center"></td>>
                                        <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>"/><br/>
                                    </td>    
                                </tr>
                            </table>
                            <table width="80%" align="center">
                                <tr>
                                    <td align="center">
                                        <!--<?= ucwords($this->config->item('company')); ?><br/>-->
                                        <?= ucwords($this->config->item('address')); ?><br/>
                                        <?= $this->config->item('phone') . " " . $this->config->item('rnc'); ?><br/><br/>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%">
                                <tr>
                                    <td align="center">
                                        <H4><b>PAGO DE CRÉDITO</b></H4><br/>  
                                    </td>
                                </tr>
                            </table>
                            <table width="90%" align="center">
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td>Transacción No. <?= $count; ?> </td>
                                    </tr>
                                    <tr>
                                        <td align="left">Fecha: <?= $trans_date; ?></td>    
                                        <td>Nro. Cuota <?= $number; ?> de <?= $size; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td colspan="2">Cliente: <?= $client; ?></td>
                                        
                                    </tr>
                
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left" > <strong>Monto Desembolso:</strong> </td>
                                        <td align="left"><?= $loan; ?></td>
                                    </tr>
                
                                    
                
                                    <tr>
                                        <td align="left">Capital</td>
                                        <td align="right"><?= $capital; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left">Interes</td>
                                        <td align="right"><?= $interest; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left">G.O.G.C </td>
                                        <td align="right"><?= $operating_expenses_amount; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left"><b>Total </b></td>
                                        <td align="right" > <strong> <?= $total; ?> </strong> </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" colspan="2"> Son: <?= $literal; ?> </td>
                
                                    </tr>
                                    <?php if(isset($next_pay)){ ?>
                                    <tr>
                                        <td align="left" colspan="2"> Prox. Cuota <?= $next_pay; ?> </td>
                                    </tr>
                                    <?php }?>    
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table width="80%" align="center">
                                <tr>
                                    <td align="left">Firma cliente: ....................&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="left">CI: ....................&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="left">Nombre: <?= $client; ?></td>
                                </tr>
                            </table>
                            </td>
                            <td>
                            <table width="30%" align="center">
                                <tr>
                                    <td align="center">
                                        <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>"/><br/>
                                    </td>    
                                </tr>
                            </table>
                            <table width="80%" align="center">
                                <tr>
                                    <td align="center">
                                        <!--<img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" /><br/>
                                        <?= ucwords($this->config->item('company')); ?><br/>-->
                                        <?= ucwords($this->config->item('address')); ?><br/>
                                        <?= $this->config->item('phone') . " " . $this->config->item('rnc'); ?><br/><br/>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%">
                                <tr>
                                    <td align="center">
                                        <H4><b>PAGO DE CRÉDITO</b></H4><br/>  
                                    </td>
                                </tr>
                            </table>
                            <table width="90%" align="center">
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td>Transacción No. <?= $count; ?> </td>
                                    </tr>
                                    <tr>
                                        <td align="left">Fecha: <?= $trans_date; ?></td>    
                                        <td>Nro. Cuota <?= $number; ?> de <?= $size; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td colspan="2">Cliente: <?= $client; ?></td>
                                        
                                    </tr>
                
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left" > <strong>Monto Desemboloso:</strong> </td>
                                        <td align="left"><?= $loan; ?></td>
                                    </tr>
                
                                    
                
                                    <tr>
                                        <td align="left">Capital</td>
                                        <td align="right"><?= $capital; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left">Interes</td>
                                        <td align="right"><?= $interest; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left">G.O.G.C </td>
                                        <td align="right"><?= $operating_expenses_amount; ?></td>
                                    </tr>
                
                                    <tr>
                                        <td align="left"><b>Total </b></td>
                                        <td align="right" > <strong> <?= $total; ?> </strong> </td>
                                    </tr>
                                    <tr>
                                        <td> &nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" colspan="2"> Son: <?= $literal; ?> </td>
                
                                    </tr>
                                    <?php if(isset($next_pay)){ ?>
                                    <tr>
                                        <td align="left" colspan="2"> Prox. Cuota <?= $next_pay; ?> </td>
                                    </tr>
                                    <?php }?>    
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table width="80%" align="center">
                                <tr>
                                    <td align="left">Firma cliente: ....................&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="left">CI: ....................&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="left">Nombre: <?= $client; ?></td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>    
    </body>
</html>