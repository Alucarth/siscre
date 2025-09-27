<?php $this->load->view("partial/header"); ?>

<style>
    #role_manager_wrapper th:nth-child(1) {
        width:46px;
        min-width:46px;
    }
</style>

<div class="title-block">
    <h3 class="title">Permisos de tipo de usuario</h3>
    <p class="title-description">
        Agregar, actualizar y eliminar tipos de usuarios
    </p>
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
                                    <table class="table table-bordered" id="role_manager">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Nombre</th>
                                                <th>Añadido por</th>
                                            </tr>
                                        </thead>        
                                    </table>

                                    <?= $role_manager_table; ?>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    <button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Imprimir</button>
</div>

<input type="file" name="file" id="basic" style="display: none;">

<?php echo form_open('roles/ajax', 'id="frmRoles"', ["type" => 1]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $("#role_manager_filter input[type='search']").attr("placeholder", "Escriba su búsqueda aquí");
        $("#role_manager_filter").append($(".extra-filters").html());
        
        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#role_manager_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/permissions.pdf');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                html: clone.html()
            };
            blockElement("#btn-export-pdf");
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    window.open(data.url,'_blank');
                }
                unblockElement("#btn-export-pdf");
            }, "json");
        });
        
        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("¿Está seguro de que desea eliminar este permiso?", function () {
                var params = $("#frmRoles").serialize();
                params += '&role_id=' + $this.data('role-id');
                $.post($("#frmRoles").attr("action"), params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#role_manager").DataTable().ajax.reload();
                    }
                }, 'json');
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>