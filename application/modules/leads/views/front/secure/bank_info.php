<?php $this->load->view("partial/header"); ?>

<?= form_open('leads/ajax/' . $person_info->id, array('id' => 'frmPersonalInfo', 'class' => 'form-horizontal')); ?>

<input type="hidden" name="type" value="16" />

<div class="title-block">
    <h3 class="title">Bank Information</h3>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">
                    <div class="inqbox float-e-margins">

                        <div class="inqbox-content">
                            <div class="tabs-container">

                                
                                <div id="tab-info" class="tab-pane fade in active show">

                                    <div style="text-align: center">
                                        <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
                                        <ul id="error_message_box"></ul>
                                    </div>                                        
                                    
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Bank name:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?=$person_info->bank_name;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Account number:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="account_number" name="account_number" value="<?=$person_info->account_number;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Account holder name:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" value="<?=$person_info->account_holder_name;?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-12">
                    <div class="form-group">

                        <a href="<?=site_url('customers')?>" class="btn btn-default btn-secondary" data-dismiss="modal" id="btn-close"><?= $this->lang->line("common_close"); ?></a>
                            <?php
                            echo form_submit(
                                    array(
                                        'name' => 'submit',
                                        'id' => 'btn-save',
                                        'value' => $this->lang->line('common_save'),
                                        'class' => 'btn btn-primary'
                                    )
                            );
                            ?>
                    </div>
                </div>


            </div>


        </div>
    </div>    
</div>
<?= form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script src="<?php echo base_url(); ?>js/people.js?v=<?= time(); ?>"></script>

<script type="text/javascript">
    $(document).ready(function () {
        
        $("#frmPersonalInfo").submit(function(e){
            e.preventDefault();
            
            var url = $("#frmPersonalInfo").attr("action");
            var params = $("#frmPersonalInfo").serialize();
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    set_feedback("You have successfully saved changes!", 'success_message', false);
                }
            }, "json");
        });
    });
</script>