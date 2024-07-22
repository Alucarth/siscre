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
        <table width="73%">
            <tr>
                <td width="75%" rowspan="5"></td>
                <!--<td width="75%" rowspan="4"><img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="height:50px"/></td>-->
                <!--<td align="right">Transacción No. <?= $count; ?> </td>-->
                <td></td>
                <td></td>
            </tr>
            <tr>
                <!--<td>Nro. Cuota <?= $number; ?> de <?= $size; ?></td>-->
                <td width="25%"><small>Fecha: <?= $trans_date; ?></small></td>    
            </tr>
            <tr>
                <td><small>Hora: <?= $time_date; ?></small></td>    
            </tr>
            <tr>
                <td><small>Usuario: <?= $teller; ?></small></td>
            </tr>
            <tr>
                <td><small>Sucursal: <?= $branch_name; ?></small></td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
        </table>
        <table width="73%">
            <tr>
                <td align="center" style="line-height: 0.5">
                    <H6><b>LIQUIDACIÓN DE AMORTIZACIÓN DE CREDITO</b></H6>  
                </td>
            </tr>
        </table>
        <br>
        <table width="73%" style="font-size: 12px;">
            <tbody>
                <tr>
                    <td width="35%" align="left"><strong>Transacción No.:</strong></td>
                    <td width="20%" align="left"><?= $count; ?></td>
                    <td width="20%" align="left"><strong>Cliente:</strong></td>
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
                    <td align="left"> <strong>Nro de Crédito:</strong> </td>
                    <td align="left"><?= $loan_id; ?></td>
                    <td align="left"><strong>Nro. Cuota:</strong></td>
                    <td align="left"><?= $number; ?> de <?= $size; ?></td>
                </tr>
                <tr>
                    <td colspan="4"><hr></td>
                </tr>
                <tr>
                    <th align="left">Concepto de liquidación.</th>
                    <th align="left"></th>
                    <th align="left">Monto:</th>
                    <th align="left"></th>
                </tr>
                <tr>
                    <td align="left">Capital:</td>
                    <td align="left"></td>
                    <td align="left">&nbsp;<?= $capital; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Interes:</td>
                    <td align="left"></td>
                    <td align="left">&nbsp;<?= $interest; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Sanción por pago tardío:</td>
                    <td align="left"></td>
                    <td align="left">&nbsp;<?= $lpp; ?></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="left">Ahorro:</td>
                    <td align="left"></td>
                    <td align="left">&nbsp;<?= $operating_expenses_amount; ?></td>
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
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
        <table width="73%" style="font-size: 12px;">
            <tr>
                <td>&nbsp;</td>
                <td align="right" width="65%" rowspan="5">
                <?php
            	    //Declaramos una carpeta temporal para guardar la imágenes generadas
	               $dir = 'temp/';
	
	                //Si no existe la carpeta la creamos
			if (!file_exists($dir))
                        mkdir($dir);
	
                    //Declaramos la ruta y nombre del archivo a generar
	                $filename = $dir.'test.png';
//			echo  $filename; 

                    //Parámetros de Configuración
	
	                $tamaño = 2; //Tamaño de Pixel
	                $level = 'M'; //Precisión L = Baja, M = Mediana, Q = Alta, H= Máxima
	                $framSize = 1; //Tamaño en blanco
	                $contenido = " ID Tran: " . $count . "\n Cliente: " . $account . "\n Cuota: " . $number . " de " . $size . "\n Total: " . $total; //Texto
	
                    //Enviamos los parámetros a la Función para generar código QR 
          		QRcode::png($contenido, $filename, $level, $tamaño, $framSize); 
			//echo '<img src= "'QRcode::png('test asd ')'">';
	
                    //Mostramos la imagen generada
                    // echo '<img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=http%3A%2F%2Fwww.google.com%2F&choe=UTF-8" title="'.$contenido.'" />';
	               echo '<img src="'.$dir.basename($filename).'" />';
                ?>
                </td>
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
            <tr>
                <td align="left">&nbsp;</td>
            </tr>
        </table>
        <br>
        <small><strong>NOTA: Este documento no tiene valor sin la firma y sello del cajero.</strong></small>
    </body>
</html>
