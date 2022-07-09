<?php $this->load->view('leads/front/partial/header'); ?>

<script src="<?php echo site_url('leads/assets/js/drag-drop-upload/simpleUpload.min.js') ?>"></script>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">

            <?php $this->load->view('leads/front/steps/nav'); ?>

            <div class="steps__fields">
                <?php echo form_open('leads/ajax', 'id="frmStep4"', ["type" => 4, "leads_id" => $leads_id]); ?>

                <div class="h2 step_page__title">Documents</div>
                <div class="h2 step_page__title">Take pictures of your <?= get_type_of_documents()[$leads_info->id_type]; ?> as per instruction below</div>


                <div class="fileinput__block">
                    <div class="fileinput" role="fileinput">
                        <div class="fileinput__upload">
                            <label class="string form__label" for="application_documents_attributes_0_file_attach"><abbr title="required">*</abbr>Front side image of <?= get_type_of_documents()[$leads_info->id_type]; ?></label>
                            <input type="file" name="file" id="cmnd_front" requared="false" class="progress_meter fileinput__field required" role="fileinputFile" accept="image/*" capture="camera">                            
                            <div class="fileinput__text" role="upload" id="div_container_front">
                                <?php if ( $leads_info->front_side_img != '' ): ?>
                                    <img src="<?=site_url('leads/documents/' . $leads_id . '/' . $leads_info->front_side_img); ?>" style="width:100%" />
                                <?php else: ?>
                                    click here
                                    <br> to upload photo
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar hidden-bar" id="progress-bar-cmnd_front">
                                <div class="progress-percentage" id="progress-percentage-cmnd_front" style="width:80%">80%</div>
                            </div>
                        </div>                        
                    </div>
                </div>
                
                <div class="fileinput__block">
                    <div class="fileinput" role="fileinput">
                        <div class="fileinput__upload">
                            <label class="string form__label" for="application_documents_attributes_0_file_attach"><abbr title="required">*</abbr>Back side image of <?= get_type_of_documents()[$leads_info->id_type]; ?></label>
                            <input type="file" name="file" id="cmnd_back" requared="false" class="progress_meter fileinput__field required" role="fileinputFile" accept="image/*" capture="camera">                            
                            <div class="fileinput__text" role="upload" id="div_container_back">
                                <?php if ( $leads_info->back_side_img != '' ): ?>
                                    <img src="<?=site_url('leads/documents/' . $leads_id . '/' . $leads_info->back_side_img); ?>" style="width:100%" />
                                <?php else: ?>
                                    click here
                                    <br> to upload photo
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar hidden-bar" id="progress-bar-cmnd_back">
                                <div class="progress-percentage" id="progress-percentage-cmnd_back" style="width:80%">80%</div>
                            </div>
                        </div>                        
                    </div>
                </div>

                <div class="fileinput__block">
                    <div class="fileinput" role="fileinput">
                        <div class="fileinput__upload">
                            <label class="string form__label" for="application_documents_attributes_1_file_attach"><abbr title="required">*</abbr>Selfie image with <?= get_type_of_documents()[$leads_info->id_type]; ?></label>
                            <input type="file" name="file" id="cmnd_selfie" requared="false" class="progress_meter fileinput__field required" role="fileinputFile" accept="image/*" capture="camera">
                            <div class="fileinput__text" role="upload" id="div_container_selfie">
                                <?php if ( $leads_info->selfie_with_img != '' ): ?>
                                    <img src="<?=site_url('leads/documents/' . $leads_id . '/' . $leads_info->selfie_with_img); ?>" style="width:100%" />
                                <?php else: ?>
                                    click here
                                    <br> to upload photo
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar hidden-bar" id="progress-bar-cmnd_selfie">
                                <div class="progress-percentage" id="progress-percentage-cmnd_selfie">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="fileinput__block">
                    <div class="fileinput" role="fileinput">
                        <div class="fileinput__upload">
                            <label class="string form__label" for="application_documents_attributes_0_file_attach"><abbr title="required">*</abbr>Utility</label>
                            <input type="file" name="file" id="cmnd_cc" requared="false" class="progress_meter fileinput__field required" role="fileinputFile" accept="image/*" capture="camera">                            
                            <div class="fileinput__text" role="upload" id="div_container_cc">
                                <?php if ( $leads_info->cc_img != '' ): ?>
                                    <img src="<?=site_url('leads/documents/' . $leads_id . '/' . $leads_info->cc_img); ?>" style="width:100%" />
                                <?php else: ?>
                                    click here
                                    <br> to upload photo
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar hidden-bar" id="progress-bar-cmnd_cc">
                                <div class="progress-percentage" id="progress-percentage-cmnd_cc" style="width:80%">80%</div>
                            </div>
                        </div>                        
                    </div>
                </div>

                <div class="form__group form__group-actions">
                    <button name="button" type="button" class="btn btn-primary btn" id="btn-save-step4" data-disable-with="Please wait...">Continue</button> 
                    <a class="link link-back" href="<?= site_url('leads/steps/3/' . $leads_id); ?>">Back to Company details</a>
                </div>                    
                <?php echo form_close(); ?>
            </div>

            <div class="steps__calc steps__calc-top">
                <h3>A few clicks away from creating your account.</h3>
                    <h4>Borrow with Ease</h4>
                    <ul>
                        <li>Competitive interest rate</li>
                        <li>Quick loan application</li>
                        <li>No collateral</li>                            
                    </ul>
            </div>
            <div class="steps__calc steps__calc-bot"></div>

            <script>
                
                $(document).ready(function () {
                    $('#cmnd_front').change(function(){
                        $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {
                            allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],
                            data: {
                                softtoken: $("input[name='softtoken']").val(),
                                id: '<?=$leads_id;?>',
                                is_front_img: 1,
                                is_back_img: 0,
                                is_cc_img: 0
                            },
                            start: function(file){                                    
                            },
                            progress: function(progress){
                                $("#progress-bar-cmnd_front").removeClass("hidden-bar");
                                $("#progress-percentage-cmnd_front").width(Math.round(progress) + '%');
                                $("#progress-percentage-cmnd_front").html(Math.round(progress) + '%');     
                            },
                            success: function(data){
                                //upload successful
                                var data = $.parseJSON(data);
                                $("#div_container_front").html( "<img src='" + data.path + "' style='width:100%' />" );
                            },
                            error: function(error){
                            }
                        });
                    });
                    
                    $('#cmnd_back').change(function(){

                        $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {
                            allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],
                            data: {
                                softtoken: $("input[name='softtoken']").val(),
                                id: '<?=$leads_id;?>',
                                is_back_img: 1,
                                is_front_img: 0,
                                is_cc_img: 0
                            },
                            start: function(file){                                    
                            },
                            progress: function(progress){
                                $("#progress-bar-cmnd_back").removeClass("hidden-bar");
                                $("#progress-percentage-cmnd_back").width(Math.round(progress) + '%');
                                $("#progress-percentage-cmnd_back").html(Math.round(progress) + '%');     
                            },
                            success: function(data){
                                //upload successful
                                var data = $.parseJSON(data);
                                $("#div_container_back").html( "<img src='" + data.path + "' style='width:100%' />" );
                                    
                            },
                            error: function(error){
                            }
                        });
                    });
                    
                    $('#cmnd_cc').change(function(){

                        $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {
                            allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],
                            data: {
                                softtoken: $("input[name='softtoken']").val(),
                                id: '<?=$leads_id;?>',
                                is_back_img: 0,
                                is_front_img: 0,
                                is_cc_img: 1
                            },
                            start: function(file){                                    
                            },
                            progress: function(progress){
                                $("#progress-bar-cmnd_cc").removeClass("hidden-bar");
                                $("#progress-percentage-cmnd_cc").width(Math.round(progress) + '%');
                                $("#progress-percentage-cmnd_cc").html(Math.round(progress) + '%');     
                            },
                            success: function(data){
                                //upload successful
                                var data = $.parseJSON(data);
                                $("#div_container_cc").html( "<img src='" + data.path + "' style='width:100%' />" );
                                    
                            },
                            error: function(error){
                            }
                        });
                    });
                    
                    $('#cmnd_selfie').change(function(){

                        $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {
                            allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],
                            data: {
                                softtoken: $("input[name='softtoken']").val(),
                                id: '<?=$leads_id;?>',
                                is_front_img: 0
                            },
                            start: function(file){                                    
                            },
                            progress: function(progress){
                                $("#progress-bar-cmnd_selfie").removeClass("hidden-bar");
                                $("#progress-percentage-cmnd_selfie").width(Math.round(progress) + '%');
                                $("#progress-percentage-cmnd_selfie").html(Math.round(progress) + '%');     
                            },
                            success: function(data){
                                //upload successful
                                var data = $.parseJSON(data);
                                $("#div_container_selfie").html( "<img src='" + data.path + "' style='width:100%' />" );
                                    
                            },
                            error: function(error){
                            }
                        });
                    });
                    
                
                    $("#btn-save-step4").click(function () {

                        if ( $("#div_container_front img").length == 0 )
                        {
                            alert("Front side image of the document is a required field");
                            return;
                        }
                        
                        if ( $("#div_container_selfie img").length == 0 )
                        {
                            alert("Selfie with image of the document is a required field");
                            return;
                        }

                        var url = $("#frmStep4").attr("action");
                        var params = $("#frmStep4").serialize();

                        $("#btn-save-step4").html($("#btn-save-step4").attr("data-disable-with"));
                        $.post(url, params, function (data) {
                            if (data.status == "OK")
                            {
                                window.location.href = data.step;
                            } else
                            {
                                alert(data.message);
                            }
                            $("#btn-save-step4").html("Continue");
                        }, "json");
                    });
                });
            </script>

        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>