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
        <span style="float:left">Libro Mayor</span>
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
                                    <?php echo form_open('general_book/generate', array('id'=>'filter_form')); ?>
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
                                                    <input type="text" class="form-control" id="filter_to_date" name="date_to" value="<?= date($this->config->item("date_format"), strtotime('+1 day')); ?>" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-search"><span class="fa fa-search"></span> Buscar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%; min-height: calc(85vh - 160px);">
                <div class="card-block">
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="div-show-data">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Seleccione un rango de fechas y haga clic en Buscar para generar el reporte del Libro Mayor.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            language: 'es'
        });
        
        $(document).on("click", "#btn-search", function () {
            load_ledger_data();
        });
    });
    
    function load_ledger_data()
    {
        var url = '<?=site_url('general_book/generate')?>';
        var params = {
            softtoken: $("input[name='softtoken']").val(),
            date_from: $("#filter_from_date").val(),
            date_to: $("#filter_to_date").val(),
        };
        
        blockElement("#div-show-data");
        $.post(url, params, function(html){
            $("#div-show-data").html(html);
            unblockElement("#div-show-data");
        }).fail(function() {
            unblockElement("#div-show-data");
            alert("Error al cargar los datos. Intente nuevamente.");
        });
    }
</script>

<?php $this->load->view("partial/footer"); ?>