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
    .btn-actions {
        margin-bottom: 15px;
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
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Cuenta Contable:</label>
                                                <select class="form-control" name="account_id" id="account_id">
                                                    <option value="">Todas las cuentas</option>
                                                    <?php foreach ($accounts as $account): ?>
                                                    <option value="<?php echo $account->id; ?>">
                                                        <?php echo $account->code_number . ' - ' . $account->account_name; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
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
                            <div id="div-actions" class="btn-actions" style="display: none;">
                                <button type="button" class="btn btn-success" id="btn-print-pdf" style="background-color:#84ce36; border-color:#84ce36; color:#ffffff;">
                                    <span class="fa fa-print"></span> Imprimir PDF
                                </button>
                                <button type="button" class="btn btn-info" id="btn-export-csv" style="background-color:#4bcf99; border-color:#4bcf99; color:#ffffff;">
                                    <span class="fa fa-download"></span> Exportar CSV
                                </button>
                            </div>
                            
                            <div id="div-show-data">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Seleccione un rango de fechas y opcionalmente una cuenta contable, luego haga clic en Buscar para generar el reporte del Libro Mayor.
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
        
        $(document).on("click", "#btn-print-pdf", function () {
            print_pdf();
        });
        
        $(document).on("click", "#btn-export-csv", function () {
            export_csv();
        });
    });
    
    function load_ledger_data()
    {
        var url = '<?=site_url('general_book/generate')?>';
        var params = {
            softtoken: $("input[name='softtoken']").val(),
            date_from: $("#filter_from_date").val(),
            date_to: $("#filter_to_date").val(),
            account_id: $("#account_id").val()
        };
        
        blockElement("#div-show-data");
        $.post(url, params, function(html){
            $("#div-show-data").html(html);
            $("#div-actions").show();
            unblockElement("#div-show-data");
        }).fail(function() {
            unblockElement("#div-show-data");
            alert("Error al cargar los datos. Intente nuevamente.");
        });
    }
    
    function print_pdf()
    {
        var date_from = $("#filter_from_date").val();
        var date_to = $("#filter_to_date").val();
        var account_id = $("#account_id").val();
        
        var url = '<?=site_url('general_book/print_pdf')?>?date_from=' + encodeURIComponent(date_from) + 
                  '&date_to=' + encodeURIComponent(date_to) + 
                  '&account_id=' + encodeURIComponent(account_id);
        
        window.open(url, '_blank');
    }
    
    function export_csv()
    {
        var date_from = $("#filter_from_date").val();
        var date_to = $("#filter_to_date").val();
        var account_id = $("#account_id").val();
        
        var url = '<?=site_url('general_book/export_csv')?>?date_from=' + encodeURIComponent(date_from) + 
                  '&date_to=' + encodeURIComponent(date_to) + 
                  '&account_id=' + encodeURIComponent(account_id);
        
        window.location.href = url;
    }
</script>

<?php $this->load->view("partial/footer"); ?>