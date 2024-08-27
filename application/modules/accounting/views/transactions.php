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
        <span style="float:left">Listado de Transacciones</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Crear, actualizar y borrar transacciones
    </p>
</div>


<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">

                    <div class="inqbox-content table-responsive">

                        <ul class="nav nav-tabs nav-tabs-bordered">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-asset">Activos</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-liability">Pasivos</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-equity">Patrimonio</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-income">Ingresos</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-expenses">Egresos</a></li>
                        </ul>
                        <div class="tab-content" style="min-height:250px;">
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

<?php echo form_open();?>
<?php echo form_close();?>

<script>
    $(document).ready(function(){
        var url = '<?=site_url('accounting/ajax');?>';
        var params = {
            softtoken:$("input[name='softtoken']").val(),
            type:14
        };
        $.post(url, params, function(data){
            $("#div-asset").html(data);
        });
                
        $("a[href='#tab-asset']").click(function(){
            $("#div-asset").html('');
                var url = '<?=site_url('accounting/ajax');?>';
                var params = {
                    softtoken:$("input[name='softtoken']").val(),
                    type:14
                };
                $.post(url, params, function(data){
                    $("#div-asset").html(data);
                });
        });
        
        $("a[href='#tab-liability']").click(function(){
            $("#div-liability").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:15
            };
            $.post(url, params, function(data){
                $("#div-liability").html(data);
            });
        });
        
        $("a[href='#tab-equity']").click(function(){
            $("#div-equity").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:16
            };
            $.post(url, params, function(data){
                $("#div-equity").html(data);
            });
        });
        
        $("a[href='#tab-income']").click(function(){
            $("#div-income").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:17
            };
            $.post(url, params, function(data){
                $("#div-income").html(data);
            });
        });
        
        $("a[href='#tab-expenses']").click(function(){
            $("#div-expenses").html('');
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                type:18
            };
            $.post(url, params, function(data){
                $("#div-expenses").html(data);
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>