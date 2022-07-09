<?php $this->load->view('leads/front/partial/header'); ?>

<?php echo form_open();?>
<?php echo form_close();?>

<div class="loan_info" style="">
    <div class="wrapper wrapper-steps" style="">
        <h1 class="loan_info__title"><?=ucwords($title)?></h1>

        <div class="repeat_app_widget__body">
            <?=$content;?>
        </div>
            
    </div>
</div>


<?php $this->load->view('leads/front/partial/footer'); ?>            


