<?php $this->load->view("partial/header"); ?>

<div class="title-block">
    <h3 class="title"> 
        Comprobante - <?php echo $user_info->first_name . " " . $user_info->last_name; ?>
    </h3>

    <div class="clearfix"></div>

    <p class="title-description">
        
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">
                    
                    <div class="form-group">
                        <div style="width: 40%;    border: 1px solid #ccc;    margin: 0 auto; padding: 15px;" id="div-receipt-container">

                            <div style="text-align: center;">
                                <img id="img-pic" src="<?= (trim($this->config->item("logo")) !== "") ? base_url("/uploads/logo/" . $this->config->item('logo')) : base_url("/uploads/common/no_img.png"); ?>" style="height:99px" />
                                <h5><?= $this->config->item("company"); ?></h5>
                                <h6>
                                    <?= $this->config->item("address"); ?><br/>
                                    <?= "Tel. No. " . $this->config->item("phone") . " Fax " . $this->config->item("fax") . " Email " . $this->config->item("email"); ?><br/>
                                    <?= "Fecha: " . date($this->config->item("date_format")); ?><br/>
                                </h6>
                            </div>
                            
                            <div>
                                <table class="table">
                                    <tr>
                                        <td>Cliente:</td>
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
                                </table>

                            </div>
                            <div>
                                <table class="table">
                                    <tr>
                                        <td>Sucursal:</td>
                                        <td style="text-align:right;"><?php echo ucwords($branch->branch_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Cajero :
                                        </td>
                                        <td style="text-align:right;"><?php echo ucwords($person->first_name." ".$person->last_name); ?></td>
                                    </tr>
                                </table>

                            </div>
                            <hr/>
                            <div style="text-align: center">
                                ¡GRACIAS!
                            </div>

                        </div>
                    </div>
                    
                    <div class="col-lg-12" style="text-align: center;">
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="btn-print-receipt"><span class="fa fa-print"></span> Imprimir</button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_open(); echo form_close(); ?>

<script>
    $(document).ready(function(){
        $(document).on("click", "#btn-print-receipt", function(){
            var clone = $("#div-receipt-container").clone();
            
            var url = '<?=site_url('accounts/print_receipt');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                customer: '<?=$customer;?>',
                trans_type: '<?=$trans_type;?>',
                amount: '<?=$amount;?>',
                trans_id: '<?=$trans_id;?>',
                account_id: '<?=$account_id;?>',
                trans_date: '<?=$trans_date;?>',
                branch_name: '<?=$branch->branch_name;?>',
                person_name: '<?=$person->first_name." ".$person->last_name;?>'
                
            };
            blockElement("#btn-print-receipt");
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    window.open(data.url,'_blank');
                }
                unblockElement("#btn-print-receipt");
            }, "json");
        });
    });
</script>

<!--<?php $this->load->view("partial/footer"); ?>-->