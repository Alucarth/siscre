<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap;
    }

    td:nth-child(4),
    td:nth-child(5),
    td:nth-child(6), 
    td:nth-child(7) {
        text-align: center;
    }
    .dataTables_info {
        float:left;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>



<div class="title-block">
    <h3 class="title"> 
        <span style="float:left">Reportes</span>
    </h3>

    <div style="clear:both;"></div>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">
                                <div class="inqbox-content">
                                    <div class="row" id="div-filters">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Desde fecha:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_from_date" name="date_from" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Hasta fecha:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_to_date" name="date_to" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Elija su reporte</label>
                                                <select class="form-control" name="report_type">
                                                    <option value="trial_balance">Balance de Comprobacion</option>
                                                    <option value="financial_income">Ingresos Financieros</option>
                                                    <option value="balance_sheet">Hoja de Balance</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Imprimir Reporte (PDF)</button>
                                                    <button type="button" class="btn btn-primary" id="btn-export-csv"><span class="fa fa-print"></span> Imprimir Reporte (CSV)</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<?php echo form_open();?>
<?php echo form_close();?>

<script>
    $(document).ready(function(){
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            language: 'es'
        });
        
        $(document).on("click", "#btn-export-pdf", function () {
            var url = '<?= site_url('accounting/report_export'); ?>';
            var params = $("#div-filters input, #div-filters select").serialize();

            window.open(url + "?" + params, '_blank');
        });
        $(document).on("click", "#btn-export-csv", function () {
            var url = '<?= site_url('accounting/report_csv'); ?>';
            var params = $("#div-filters input, #div-filters select").serialize();

            window.open(url + "?" + params, '_blank');
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>