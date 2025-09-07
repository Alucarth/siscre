</article>
                
                <footer class="footer">
                    <div class="footer-block buttons">
                       
                    </div>
                    <div class="footer-block author">
                        <ul>
                            <li> &copy; 2015 - <?=date("Y")?> <a href="<?=$this->config->item("app_brand_url") != '' ? $this->config->item("app_brand_url") : 'https://softreliance.com'; ?>"><?= ( $this->config->item("app_brand_name") != '' ? $this->config->item("app_brand_name") : COMPANY_NAME ) ?></a>
                            </li>
                        </ul>
                    </div>
                </footer>
                
                
            </div>
        </div>
        
        
        
        <!-- Reference block for JS -->
        <div class="ref" id="ref">
            <div class="color-primary"></div>
            <div class="chart">
                <div class="color-primary"></div>
                <div class="color-secondary"></div>
            </div>
        </div>
    </body>
    
    <!-- Modal -->
<div class="modal fade" id="config_modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Modal -->
<div class="modal fade" id="print_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

</html>




<!-- Custom and plugin javascript -->
<script src="<?php echo base_url(); ?>js/plugins/pace/pace.min.js"></script>

<script src="<?php echo base_url('modular-admin/js/app.js')?>"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.niftymodals/js/jquery.modalEffects.js"></script>
<!-- Date picker -->
<script src="<?php echo base_url('js/plugins/datapicker/bootstrap-datepicker.js')?>"></script>
<!-- Toastr script -->
<script src="<?php echo base_url(); ?>js/plugins/toastr/toastr.min.js"></script>
<script src="<?php echo base_url(); ?>js/jquery.autocomplete/dist/jquery.autocomplete.js" type="text/javascript" language="javascript" charset="UTF-8"></script>

<script src="<?php echo base_url(); ?>js/plupload/plupload.full.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
<script src="<?php echo base_url(); ?>js/alertifyjs/alertify.js"></script>
<script src="<?php echo base_url(); ?>js/jquery-migrate-1.2.1.js"></script>
<script src="<?php echo base_url(); ?>js/jquery.blockUI.js"></script>
<script src="<?php echo base_url(); ?>js/common.js?v=<?=APP_VERSION; ?>" type="text/javascript" language="javascript" charset="UTF-8"></script>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">
<link href="//select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js"></script>

<script>
    $(document).ready(function () {
        $('.md-trigger').modalEffects();
        $("select").select2({
            theme: "bootstrap",
        });
        $(".no-select2").select2("destroy");
    });
</script>

<style>
    <?php if ( $write_modules ): ?>
        <?php if ( (is_array($write_modules) && in_array( $this->router->fetch_class(), $write_modules)) ): ?>

        <?php else: ?>
/*            input[type='submit'],
            input[type='button'],
            button[type='button'],
            button, 
            button.btn,
            .btn-primary,
            .btn-danger {
                display:none;
            }*/
        <?php endif; ?>
    <?php endif; ?>
</style>