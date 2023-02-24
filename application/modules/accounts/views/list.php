<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap;
    }

    td:nth-child(3),
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
        <span style="float:left">Listado de cuentas</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Añadir, actualizar o borrar cuentas
    </p>
</div>


<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">

                    <div class="inqbox-content table-responsive">

                        <table class="table table-hover table-bordered" id="tbl_accounts">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 1%"></th>                            
                                    <th style="text-align: center">Denominación de la cuenta</th>
                                    <th style="text-align: center">Tipo de cuenta</th>
                                    <th style="text-align: center">Descripción</th>                                    
                                </tr>
                            </thead>
                        </table>

                        <?= $tbl_accounts; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Imprimir</button>
</div>
<div id="dt-extra-params">

</div>

<?php echo form_open('accounts/ajax', 'id="frmLoanAccountDelete"', ["type" => 4]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $("#tbl_accounts_filter").prepend("<a href='<?= site_url('accounts/view/-1') ?>' class='btn btn-primary pull-left'>Nueva cuenta</a>");
        $("#tbl_accounts_filter input[type='search']").attr("placeholder", "Buscar cuentas");
        $("#tbl_accounts_filter input[type='search']").removeClass("input-sm");
        $("#tbl_accounts_filter").append($(".extra-filters").html());

        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_accounts_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/accounts.pdf');?>';
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
            alertify.confirm("Esta seguro de eliminar esta cuenta de préstamo?", function () {
                var url = $("#frmLoanAccountDelete").attr("action");
                var params = $("#frmLoanAccountDelete").serialize();
                params += '&id=' + $this.attr("data-id");
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#tbl_accounts").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>