<?php $this->load->view('leads/front/partial/header'); ?>

<script src="<?php echo site_url('leads/assets/js/drag-drop-upload/simpleUpload.min.js') ?>"></script>

<style>
    .alert {
        position: relative;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 15px;
    }
</style>

<div class="steps" role="steps">
    <div class="wrapper wrapper-steps">
        <div class="steps__wrap">

            <div style="height: calc(85vh - 200px)">
                <div class="h2 step_page__title">Confirmation</div>                
                <div class="alert alert-success">
                    Thank you for submitting your application with us! An email has been sent for your login details.
                </div>

                <div class="form__group form__group-actions">
                    <a href="<?=site_url("leads")?>" class="btn btn-primary btn">Go to main page</a> 
                </div>
            </div>

        </div>
    </div>
</div>

<?php $this->load->view('leads/front/partial/footer'); ?>