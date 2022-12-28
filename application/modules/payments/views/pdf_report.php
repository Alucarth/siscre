<?php require_once (APPPATH . "controllers/phpqrcode/qrlib.php"); ?>

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
        <table width="100%">
            <tr>
                <td width="75%" rowspan="4"><img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="height:50px"/></td>
                <!--<td align="right">Transacción No. <?= $count; ?> </td>-->
                <td></td>
                <td></td>
            </tr>
            <tr>
                <!--<td>Nro. Cuota <?= $number; ?> de <?= $size; ?></td>-->
                <td>Fecha: <?= $trans_date; ?> </td>    
            </tr>
            <tr>
                <td>Hora: <?= $time_date; ?> </td>    
            </tr>
            <tr>
                <td>Usuario: <?= $teller; ?> </td>
            </tr>
            <tr>
                <td colspan="2"><hr></td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td align="center">
                    <H4><b>LIQUIDACIÓN DE AMORTIZACIÓN DE CREDITO</b></H4>  
                </td>
            </tr>
        </table>
        <table width="100%" align="center">
            <tbody>
                <tr>
                    <td width="25%" align="left"><strong>Transacción No.:</strong></td>
                    <td width="25%" align="left"><?= $count; ?></td>
                    <td width="25%" align="left"><strong>Cliente:</strong></td>
                    <td width="25%" align="left"><?= $client; ?></td>
                </tr>
                <tr>
                    <td align="left" > <strong>Monto de Desembolso:</strong> </td>
                    <td align="left"><?= $loan; ?></td>
                    <td align="left"><strong>Asesor:</strong></td>
                    <td align="left"><?= $loan_agent; ?></td>
                </tr>
                <tr>
                    <td align="left" > <strong>Fecha de Desembolso:</strong> </td>
                    <td align="left"><?= $loan_approved_date; ?></td>
                    <td align="left"><strong>Moneda:</strong></td>
                    <td align="left">Bolivianos</td>
                </tr>
                <tr>
                    <td align="left"></td>
                    <td align="left"></td>
                    <td align="left"><strong>Nro. Cuota:</strong></td>
                    <td align="left"><?= $number; ?> de <?= $size; ?></td>
                </tr>
                <tr>
                    <td colspan="4"><hr></td>
                </tr>
                <tr>
                    <td align="left"><strong>Concepto de liquidación.</strong></td>
                    <td align="left"></td>
                    <td align="left">Monto:</td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Capital:</td>
                    <td align="left"></td>
                    <td align="left"><?= $capital; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Interes:</td>
                    <td align="left"></td>
                    <td align="left"><?= $interest; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Sanción por pago tardío:</td>
                    <td align="left"></td>
                    <td align="left"><?= $lpp; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Gastos operativos:</td>
                    <td align="left"></td>
                    <td align="left"><?= $operating_expenses_amount; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left"><b>Total </b></td>
                    <td align="left"></td>
                    <td align="left" > <strong> <?= $total; ?> </strong> </td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left"></td>
                    <td align="left"></td>
                    <td align="left" colspan="2"> Son: <?= $literal; ?> </td>
                    <td align="left"></td>
                </tr>
        <!--        <?php if(isset($next_pay)){ ?>
                <tr>
                    <td align="left" colspan="2"> Prox. Cuota <?= $next_pay; ?> </td>
                </tr>
                <?php }?>    
                <tr>
                    <td colspan="2"><hr></td>
                </tr>-->
                <tr>
                    <td colspan="4"><hr></td>
                </tr>
            </tbody>
        </table>
        <table width="100%">
            <tr>
                <td>&nbsp;</td>
                <td align="right" width="75%" rowspan="5">
                <?php
            	    //Declaramos una carpeta temporal para guardar la imágenes generadas
	                $dir = 'temp/';
	
	                //Si no existe la carpeta la creamos
	                if (!file_exists($dir))
                        mkdir($dir);
	
                    //Declaramos la ruta y nombre del archivo a generar
	                $filename = $dir.'test.png';

                    //Parámetros de Configuración
	
	                $tamaño = 3; //Tamaño de Pixel
	                $level = 'M'; //Precisión L = Baja, M = Mediana, Q = Alta, H= Máxima
	                $framSize = 1; //Tamaño en blanco
	                $contenido = " ID Tran: " . $count . "\n Cliente: " . $account . "\n Cuota: " . $number . " de " . $size . "\n Total: " . $total; //Texto
	
                    //Enviamos los parámetros a la Función para generar código QR 
	                QRcode::png($contenido, $filename, $level, $tamaño, $framSize); 
	
                    //Mostramos la imagen generada
	                echo '<img src="'.$dir.basename($filename).'" />';  
                ?>
                </td>
            </tr>
            <tr>
                <td align="left">&nbsp;</td>
            </tr>
            <tr>
                <td align="left">Firma cliente: ....................&nbsp;</td>
            </tr>
            <tr>
                <td align="left">CI: <?= $document_number; ?>&nbsp;</td>
            </tr>
            <tr>
                <td align="left">Nombre: <?= $client; ?></td>
            </tr>
        </table>
        <br><br>
        <strong>NOTA: Este documento no tiene valor sin la firma y sello del cajero.</strong>
    </body>
</html>