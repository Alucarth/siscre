<style>
    .form-group {
        clear: both;
    }
    
    .hr-line-dashed {
        clear: both;
    }
</style>

<div class="tabs-container">
    <ul class="nav nav-tabs nav-tabs-bordered">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-basic-info">Basic Info</a></li>        
        <!--<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-loan-details">Loan details</a></li>-->
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-employment-details">Employment details</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-bank-details">Bank details</a></li>                        
        <!--<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-documents">Documents</a></li>-->                  
        
    </ul>
    <div class="tab-content">
        <div id="tab-basic-info" class="tab-pane fade in active show">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Full name:</label>
                        <div>
                            <?=$leads->full_name;?>
                        </div>
                    </div>            

                    <div class="form-group">
                        <label>Email:</label>
                        <div>
                            <?=$leads->email;?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gender:</label>
                        <div>
                            <?=ucwords($leads->gender);?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>City:</label>
                        <div>
                            <?=ucwords($leads->city);?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address:</label>
                        <div>
                            <?=ucwords($leads->address1);?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Street Number:</label>
                        <div>
                            <?=ucwords($leads->street_no);?>
                        </div>
                    </div>
                    <div class="hr-line-dashed clearfix"></div>
                </div>
                <div class="col-lg-4">
                    <?php if(is_plugin_active("branches")):?>
                    <div class="form-group">
                        <label>Assign Branch:</label>
                        <select class="form-control" id="branch_id">
                            <option value="">Please select</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch->id; ?>" <?= ($branch->id == $leads->branch_id) ? "selected='selected'" : ""; ?>><?= $branch->branch_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <script>
                            $(document).ready(function(){
                                $("#branch_id").change(function(){
                                    assign_branch();
                                });
                            });
                            
                            function assign_branch()
                            {
                                var url = '<?=site_url('leads/ajax');?>';
                                var params = {
                                    type:22,
                                    customer_id:'<?=$leads->customer_id?>',
                                    branch_id: $("#branch_id").val(),
                                    softtoken:$("input[name='softtoken']").val()
                                };
                                blockElement($("#branch_id").parent());
                                $.post(url, params, function(data){
                                    if ( data.status == "OK" )
                                    {
                                        unblockElement($("#branch_id").parent());
                                    }
                                }, "json");
                            }
                        </script>
                        
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            

        </div>
        
        <div id="tab-loan-details" class="tab-pane fade">

            <div class="form-group">
                <label class="col-lg-3">Apply Amount:</label>
                <div class="col-lg-9">
                    <?= to_currency($loan_info->apply_amount);?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3">Payable:</label>
                <div class="col-lg-9">
                    <?= to_currency($loan_info->loan_amount);?>
                </div>
            </div>
            
            <div class="form-group">
                <a href="<?=site_url("loans/view/" . $loan_info->loan_id)?>" target="_blank" class="btn btn-primary">Go to Loan details</a>
            </div>
            
            
        </div>

        <div id="tab-employment-details" class="tab-pane fade">

            <div class="form-group">
                <label class="col-lg-3">Occupation:</label>
                <div class="col-lg-9">
                    <?= ( $leads->occupation > 0 ) ? get_occupations()[$leads->occupation] : '';?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>

            <div class="form-group">
                <label class="col-lg-3">Company name:</label>
                <div class="col-lg-9">
                    <?= $leads->company_name;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Length of work:</label>
                <div class="col-lg-9">
                    <?= $leads->work_term > 0 ? get_work_terms()[$leads->work_term] : '';?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Net monthly income:</label>
                <div class="col-lg-9">
                    <?= to_currency($leads->net_monthly_income);?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Company phone:</label>
                <div class="col-lg-9">
                    <?= $leads->company_phone;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Guarantor phone:</label>
                <div class="col-lg-9">
                    <?= $leads->guarantor_phone;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            
        </div>

        <div id="tab-bank-details" class="tab-pane fade">

            <div class="form-group">
                <label class="col-lg-3">Bank name:</label>
                <div class="col-lg-9">
                    <?= $leads->bank_name;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Account holder name:</label>
                <div class="col-lg-9">
                    <?= $leads->account_holder_name;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>

            <div class="form-group">
                <label class="col-lg-3">Account number:</label>
                <div class="col-lg-9">
                    <?= $leads->account_number;?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
        </div>

        <div id="tab-documents" class="tab-pane fade">
            
            <div class="form-group">
                <label class="col-lg-3">Front side:</label>
                <div class="col-lg-9">
                    <?php if($leads->front_side_img != ''):?>
                    <a href="<?=site_url('leads/documents/' . $leads->id . '/' . $leads->front_side_img);?>" target="_blank"><img src="<?=site_url('leads/documents/' . $leads->id . '/' . $leads->front_side_img);?>" width="80" /></a>
                    <?php else: ?>
                    <div class="alert alert-danger">No uploaded</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
            <div class="form-group">
                <label class="col-lg-3">Selfie with image:</label>
                <div class="col-lg-9">
                    <?php if ( $leads->selfie_with_img ):?>
                    <a href="<?=site_url('leads/documents/' . $leads->id . '/' . $leads->selfie_with_img);?>" target="_blank"><img src="<?=site_url('leads/documents/' . $leads->id . '/' . $leads->selfie_with_img);?>" width="80" /></a>
                    <?php else: ?>
                    <div class="alert alert-danger">No uploaded</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hr-line-dashed clearfix"></div>
            
        </div>

    </div>
</div>