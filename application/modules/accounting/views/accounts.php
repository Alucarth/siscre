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
    
    .tabs-container {
        position: relative;
    }
    
    .nav-sidebar {
        width: 40px;
        transition: width 0.3s ease;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
        border-right: 1px solid #dee2e6;
    }
    
    .nav-sidebar:hover {
        width: 200px;
    }
    
    .nav-sidebar .nav-link {
        white-space: nowrap;
        padding: 12px 8px;
        position: relative;
        color: #495057;
        border: none;
        border-radius: 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .nav-sidebar .nav-link:before {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        text-align: center;
        line-height: 20px;
        font-size: 14px;
        vertical-align: middle;
    }
    
    /* Iniciales para cada pestaña */
    .nav-sidebar .nav-link[href="#tab-asset"]:before {
        content: 'A';
        font-weight: bold;
        color: inherit;
    }
    .nav-sidebar .nav-link[href="#tab-liability"]:before {
        content: 'P';
        font-weight: bold;
        color: inherit;
    }
    .nav-sidebar .nav-link[href="#tab-equity"]:before {
        content: 'Pt';
        font-weight: bold;
        color: inherit;
    }
    .nav-sidebar .nav-link[href="#tab-income"]:before {
        content: 'I';
        font-weight: bold;
        color: inherit;
    }
    .nav-sidebar .nav-link[href="#tab-expenses"]:before {
        content: 'E';
        font-weight: bold;
        color: inherit;
    }
    
    .nav-sidebar .nav-link.active {
        background-color: #007bff;
        color: white;
    }
    
    .nav-sidebar .nav-text {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .nav-sidebar:hover .nav-text {
        opacity: 1;
    }
    
    .content-area {
        transition: margin-left 0.3s ease;
    }
    
    /* Estilos para sangrías de cuentas */
    .indent-0 { padding-left: 8px !important; }
    .indent-1 { padding-left: 30px !important; }
    .indent-2 { padding-left: 60px !important; }
    .indent-3 { padding-left: 70px !important; }
    .indent-4 { padding-left: 90px !important; }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="title-block">
    <h3 class="title"> 
        <span style="float:left">Listado de Cuentas</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Crear, actualizar y borrar cuentas
    </p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-12">
            <div class="card" style="width:100%">
                <div class="card-block">
                    <div class="inqbox-content table-responsive">
                        
                        <div class="tabs-container d-flex">
                            <div class="nav-sidebar">
                                <ul class="nav nav-pills flex-column h-100">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="pill" href="#tab-asset">
                                            <span class="nav-text">Activos</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="pill" href="#tab-liability">
                                            <span class="nav-text">Pasivos</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="pill" href="#tab-equity">
                                            <span class="nav-text">Patrimonio</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="pill" href="#tab-income">
                                            <span class="nav-text">Ingresos</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="pill" href="#tab-expenses">
                                            <span class="nav-text">Egresos</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="content-area flex-grow-1">
                                <div class="tab-content p-3" style="min-height:250px;">
                                    <div id="tab-asset" class="tab-pane fade in active show">
                                        <div id="div-asset"></div>
                                    </div>
                                    <div id="tab-liability" class="tab-pane fade">
                                        <div id="div-liability"></div>
                                    </div>
                                    <div id="tab-equity" class="tab-pane fade">
                                        <div id="div-equity"></div>
                                    </div>
                                    <div id="tab-income" class="tab-pane fade">
                                        <div id="div-income"></div>
                                    </div>
                                    <div id="tab-expenses" class="tab-pane fade">
                                        <div id="div-expenses"></div>
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
    function applyIndentation(tableId) {
        $('#' + tableId + ' tbody tr').each(function() {
            var row = $(this);
            var indentLevel = row.data('indent-level') || 0;
            var accountNameCell = row.find('td:eq(2)'); // Columna del nombre de cuenta
            
            // Aplicar clase de sangría
            accountNameCell.removeClass('indent-0 indent-1 indent-2 indent-3 indent-4');
            accountNameCell.addClass('indent-' + indentLevel);
            
            var codeNumber = row.find('td:eq(1)').text().trim();
            var codeLength = codeNumber.replace(/[^0-9]/g, '').length;
            var isParent = codeLength <= 4;
            
            accountNameCell.removeClass('has-children no-children');
            if (isParent && codeLength <= 4) {
                accountNameCell.addClass('has-children');
            } else {
                accountNameCell.addClass('no-children');
            }
        });
    }
    
    $(document).ready(function(){
        var url = '<?=site_url('accounting/ajax');?>';
        var params = {
            softtoken:$("input[name='softtoken']").val(),
            type:5
        };
        $.post(url, params, function(data){
            $("#div-asset").html(data);
            setTimeout(function() {
                applyIndentation('tbl_asset');
            }, 100);
        });
                
        $("a[href='#tab-asset']").click(function(){
            $("#div-asset").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:5
            };
            $.post(url, params, function(data){
                $("#div-asset").html(data);
                setTimeout(function() {
                    applyIndentation('tbl_asset');
                }, 100);
            });
        });
        
        $("a[href='#tab-liability']").click(function(){
            $("#div-liability").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:6
            };
            $.post(url, params, function(data){
                $("#div-liability").html(data);
                setTimeout(function() {
                    applyIndentation('tbl_liability');
                }, 100);
            });
        });
        
        $("a[href='#tab-equity']").click(function(){
            $("#div-equity").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:7
            };
            $.post(url, params, function(data){
                $("#div-equity").html(data);
                setTimeout(function() {
                    applyIndentation('tbl_equity');
                }, 100);
            });
        });
        
        $("a[href='#tab-income']").click(function(){
            $("#div-income").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:8
            };
            $.post(url, params, function(data){
                $("#div-income").html(data);
                setTimeout(function() {
                    applyIndentation('tbl_income');
                }, 100);
            });
        });
        
        $("a[href='#tab-expenses']").click(function(){
            $("#div-expenses").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:9
            };
            $.post(url, params, function(data){
                $("#div-expenses").html(data);
                setTimeout(function() {
                    applyIndentation('tbl_expenses');
                }, 100);
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>