<?php $this->load->view("partial/header"); ?>

<?= form_open('leads/ajax/' . $person_info->id, array('id' => 'frmPersonalInfo', 'class' => 'form-horizontal')); ?>

<input type="hidden" name="type" value="16" />

<div class="title-block">
    <h3 class="title">Address Details</h3>
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
                                            Living city/municipality:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="city" name="city" value="<?=$person_info->city;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Address:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="address1" name="address1" value="<?=$person_info->address1;?>" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="control-label col-sm-3 text-xs-right">
                                            Street, house number, apartment number:
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="street_no" name="street_no" value="<?=$person_info->street_no;?>" />
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
            
            if ( $("#password").val() != $("#confirm_password").val() )
            {
                set_feedback("Password don't match!", 'error_message', false);
                return false;
            }
            
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