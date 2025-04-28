<script src="<?=site_url('leads/assets/js/drag-drop-upload/simpleUpload.min.js')?>"></script>



<style>

    .hidden-bar {

        display: none;

    }

</style>



<div style="text-align: center">

    <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

    <ul id="error_message_box"></ul>

</div>                                        



<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Imagen frontal:

    </label>

    <div class="col-sm-9">



        <div class="fileinput__text" role="upload" id="div_container_front" style="width:250px;">

            <?php if ($person_info->front_side_img != ''): ?>

                <img src="<?= site_url('leads/documents/' . $person_info->id . '/' . $person_info->front_side_img); ?>" style="width:100%" />

            <?php else: ?>

                Sin fotografía

            <?php endif; ?>

        </div>

        

        <?php if ($user_info->role_id != CUSTOMER_ROLE_ID):?>

        <input type="file" name="file" id="cmnd_front" requared="false" class="progress_meter fileinput__field" role="fileinputFile" accept="image/*" capture="camera">                            

        <div class="progress-bar hidden-bar" id="progress-bar-cmnd_front" style="width:250px;">

            <div class="progress-percentage" id="progress-percentage-cmnd_front" style="width:80%;">80%</div>

        </div>



        <script>

            $(document).ready(function () {



                $('#cmnd_front').change(function () {

                    $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {

                        allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],

                        data: {

                            softtoken: $("input[name='softtoken']").val(),

                            id: '<?= $person_info->id; ?>',

                            is_front_img: 1,

                            is_back_img: 0,

                            is_cc_img: 0

                        },

                        start: function (file) {

                        },

                        progress: function (progress) {

                            $("#progress-bar-cmnd_front").removeClass("hidden-bar");

                            $("#progress-percentage-cmnd_front").width(Math.round(progress) + '%');

                            $("#progress-percentage-cmnd_front").html(Math.round(progress) + '%');

                        },

                        success: function (data) {

                            //upload successful

                            var data = $.parseJSON(data);

                            $("#div_container_front").html("<img src='" + data.path + "' style='width:100%' />");



                        },

                        error: function (error) {

                        }

                    });

                });



            });

        </script>

        <?php endif;?>



    </div>

</div>





<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Imagen trasera:

    </label>

    <div class="col-sm-9">



        <div class="fileinput__text" role="upload" id="div_container_back" style="width:250px;">

            <?php if ($person_info->back_side_img != ''): ?>

                <img src="<?= site_url('leads/documents/' . $person_info->id . '/' . $person_info->back_side_img); ?>" style="width:100%" />

            <?php else: ?>

                Sin fotografía

            <?php endif; ?>

        </div>

        

        <?php if ($user_info->role_id != CUSTOMER_ROLE_ID):?>

        <input type="file" name="file" id="cmnd_back" requared="false" class="progress_meter fileinput__field" role="fileinputFile" accept="image/*" capture="camera">                            

        <div class="progress-bar hidden-bar" id="progress-bar-cmnd_back" style="width:250px;">

            <div class="progress-percentage" id="progress-percentage-cmnd_back" style="width:80%">80%</div>

        </div>



        <script>

            $(document).ready(function () {



                $('#cmnd_back').change(function () {

                    $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {

                        allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],

                        data: {

                            softtoken: $("input[name='softtoken']").val(),

                            id: '<?= $person_info->id; ?>',

                            is_front_img: 0,

                            is_back_img: 1,

                            is_cc_img: 0

                        },

                        start: function (file) {

                        },

                        progress: function (progress) {

                            $("#progress-bar-cmnd_back").removeClass("hidden-bar");

                            $("#progress-percentage-cmnd_back").width(Math.round(progress) + '%');

                            $("#progress-percentage-cmnd_back").html(Math.round(progress) + '%');

                        },

                        success: function (data) {

                            //upload successful

                            var data = $.parseJSON(data);

                            $("#div_container_back").html("<img src='" + data.path + "' style='width:100%' />");



                        },

                        error: function (error) {

                        }

                    });

                });



            });

        </script>

        <?php endif; ?>



    </div>

</div>





<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Selfie con/Imagen:

    </label>

    <div class="col-sm-9">



        <div class="fileinput__text" role="upload" id="div_container_selfie" style="width:250px;">

            <?php if ($person_info->selfie_with_img != ''): ?>

                <img src="<?= site_url('leads/documents/' . $person_info->id . '/' . $person_info->selfie_with_img); ?>" style="width:100%" />

            <?php else: ?>

                Sin fotografía

            <?php endif; ?>

        </div>

        

        <?php if ($user_info->role_id != CUSTOMER_ROLE_ID):?>

        <input type="file" name="file" id="cmnd_selfie" requared="false" class="progress_meter fileinput__field" role="fileinputFile" accept="image/*" capture="camera">

        <div class="progress-bar hidden-bar" id="progress-bar-cmnd_selfie" style="width:250px;">

            <div class="progress-percentage" id="progress-percentage-cmnd_selfie">0%</div>

        </div>



        <script>

            $(document).ready(function () {

                $('#cmnd_selfie').change(function () {



                    $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {

                        allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],

                        data: {

                            softtoken: $("input[name='softtoken']").val(),

                            id: '<?= $person_info->id; ?>',

                            is_front_img: 0

                        },

                        start: function (file) {

                        },

                        progress: function (progress) {

                            $("#progress-bar-cmnd_selfie").removeClass("hidden-bar");

                            $("#progress-percentage-cmnd_selfie").width(Math.round(progress) + '%');

                            $("#progress-percentage-cmnd_selfie").html(Math.round(progress) + '%');

                        },

                        success: function (data) {

                            //upload successful

                            var data = $.parseJSON(data);

                            $("#div_container_selfie").html("<img src='" + data.path + "' style='width:100%' />");



                        },

                        error: function (error) {

                            // upload failed

                            // console.log("upload error: " + error.name + ": " + error.message);

                        }

                    });

                });

            });

        </script>

        <?php endif; ?>

    </div>

</div>





<div class="form-group row">

    <label class="control-label col-sm-3 text-xs-right">

        Factura de servicios públicos:

    </label>

    <div class="col-sm-9">



        <div class="fileinput__text" role="upload" id="div_container_cc" style="width:250px;">

            <?php if ($person_info->cc_img != ''): ?>

                <img src="<?= site_url('leads/documents/' . $person_info->id . '/' . $person_info->cc_img); ?>" style="width:100%" />

            <?php else: ?>

                Sin fotografía

            <?php endif; ?>

        </div>

        

        <?php if ($user_info->role_id != CUSTOMER_ROLE_ID):?>

        <input type="file" name="file" id="cmnd_cc" requared="false" class="progress_meter fileinput__field" role="fileinputFile" accept="image/*" capture="camera">

        <div class="progress-bar hidden-bar" id="progress-bar-cmnd_cc" style="width:250px;">

            <div class="progress-percentage" id="progress-percentage-cmnd_cc" style="width:80%">80%</div>

        </div>



        <script>

            $(document).ready(function () {



                $('#cmnd_cc').change(function () {



                    $(this).simpleUpload("<?php echo site_url('leads/upload'); ?>", {

                        allowedExts: ["jpg", "jpeg", "jpe", "jif", "jfif", "jfi", "png", "gif"],

                        data: {

                            softtoken: $("input[name='softtoken']").val(),

                            id: '<?= $person_info->id; ?>',

                            is_front_img: 0,

                            is_back_img: 0,

                            is_cc_img: 1

                        },

                        start: function (file) {

                        },

                        progress: function (progress) {

                            $("#progress-bar-cmnd_cc").removeClass("hidden-bar");

                            $("#progress-percentage-cmnd_cc").width(Math.round(progress) + '%');

                            $("#progress-percentage-cmnd_cc").html(Math.round(progress) + '%');

                        },

                        success: function (data) {

                            //upload successful

                            var data = $.parseJSON(data);

                            $("#div_container_cc").html("<img src='" + data.path + "' style='width:100%' />");



                        },

                        error: function (error) {

                        }

                    });

                });



            });

        </script>

        <?php endif; ?>

    </div>

</div>