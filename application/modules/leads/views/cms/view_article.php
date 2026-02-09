<?php $this->load->view("partial/header"); ?>

<style>
    @font-face {
        font-family: "summernote";
        font-style: normal;
        font-weight: 400;
        font-display: auto;
        src: url(<?=base_url()?>fonts/summernote.eot);
        src: url(<?=base_url()?>fonts/summernote.eot?#iefix) format("embedded-opentype"), 
            url(<?=base_url()?>fonts/summernote.woff2) format("woff2"), 
            url(<?=base_url()?>fonts/summernote.woff) format("woff"), 
            url(<?=base_url()?>fonts/summernote.ttf) format("truetype");
    }

    .gallery-box {
        padding: 4px;
        text-align: center;
        float: left;
        border: 1px solid #e6e6e6;
        margin-right: 5px;
        margin-bottom: 8px;
    }
</style>

<link rel="stylesheet" href="<?= site_url('leads/assets/summernote/summernote-lite.css') ?>">  

<style>
    .hr-line-dashed, .form-group {
        clear: both;
    }
</style>


<div class="title-block">
    <h3 class="title"> 

        New Article

    </h3>
    <p class="title-description">
        Article basic information
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-block">

                    <div id="leadsForm">


                        <div class="row">
                            <div class="col-lg-12">
                                <?php echo form_open('leads/ajax', 'id="frmSaveArticle"', ["type" => 12, "article_id" => $article_id]); ?>
                                <div class="inqbox float-e-margins">

                                    <div class="inqbox-content clearfix">

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-lg-2 control-label">Title:</label>
                                                <div class="col-lg-10">
                                                    <input type="text" name="title" id="title" class="form-control" value="<?= $article_info->title; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hr-line-dashed"></div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-lg-2 control-label">Content:</label>
                                                <div class="col-lg-10">
                                                    <textarea id="content" name="content"><?= $article_info->content; ?></textarea>
                                                </div>
                                            </div>
                                            <script type="text/javascript">
                                                $(document).ready(function () {
                                                    $('#content').summernote({
                                                        height: 180,
                                                        tabsize: 2
                                                    });
                                                });
                                            </script>
                                        </div>
                                        <div class="hr-line-dashed"></div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-lg-2 control-label">Publish:</label>
                                                <div class="col-lg-10">
                                                    <input type="checkbox" id="published" name="published" value="1" <?= ( $article_info->published ? 'checked="checked"' : "" ); ?> />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hr-line-dashed"></div>

                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>    

                        <script>
                            $(document).ready(function () {
                                $("#btn-save").click(function () {
                                    var url = $("#frmSaveArticle").attr("action");
                                    var params = $("#frmSaveArticle").serialize();
                                    $.post(url, params, function (data) {
                                        if (data.status == "OK")
                                        {
                                            toastr.success(data.message);
                                            // reload the page
                                            window.location.href = data.path;
                                        } else
                                        {
                                            toastr.error(data.message);
                                        }
                                    }, "json");
                                });
                            });
                        </script>

                    </div>

                </div>

                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <a class="btn btn-default btn-secondary" href="<?= site_url('leads/articles') ?>"><?= $this->lang->line("common_close"); ?></a>
                        <button type="submit" class="btn btn-primary" id="btn-save"><?= $this->lang->line("common_save"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>    

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" src="<?= site_url('leads/assets/summernote/summernote-lite.js') ?>"></script>