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
        <span style="float:left">List of Expenses Accounts</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Add, update & delete expenses accounts
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
                                    <th style="text-align: center">Code number</th>
                                    <th style="text-align: center">Account name</th>
                                    <th style="text-align: center">Description</th>                                    
                                </tr>
                            </thead>
                        </table>

                        <?= $tbl_expenses; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Print</button>
</div>
<div id="dt-extra-params"></div>

<!-- Modal -->
<div class="modal fade" id="md-expenses" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px">
            <div class="modal-header">
                Liability
                <input type="hidden" name="account_type" id="account_type" value="expenses" />
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="" />
                <div class="form-group">
                    <label>Code number:</label>
                    <input type="text" class="form-control" name="code_number" id="code_number" />
                </div>
                <div class="form-group">
                    <label>Account name:</label>
                    <input type="text" class="form-control" name="account_name" id="account_name" />
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save-asset">Save</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php echo form_open('accounting/ajax', 'id="frmAssetDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $("#tbl_accounts_filter").prepend("<a href='javascript:void(0)' class='btn btn-primary pull-left' id='btn-new-asset'>New expenses account</a>");
        $("#tbl_accounts_filter input[type='search']").attr("placeholder", "Type your search here");
        $("#tbl_accounts_filter input[type='search']").removeClass("input-sm");
        $("#tbl_accounts_filter").append($(".extra-filters").html());
        
        $("#btn-save-asset").click(function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = $("#md-expenses input, #md-expenses textarea").serialize();
            params += '&softtoken=' + $("input[name='softtoken']").val() + '&type=3';
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#md-expenses").modal("hide");
                    $("#tbl_accounts").DataTable().ajax.reload();
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
        });
        
        $(document).on("click", "#btn-new-asset", function(){            
            $("#md-expenses .modal-body input, #md-expenses .modal-body textarea").val("");
            $("#md-expenses #code_number").prop("disabled", false);
            $("#md-expenses").modal("show");
        });
        
        $(document).on("click", ".btn-edit-asset", function(){
            var url = '<?=site_url('accounting/ajax');?>';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type:4,
                id: $(this).data("id")
            };
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $.each(data.row, function(key, value){                        
                        $("#" + key).val(value);
                    });
                    
                    $("#md-expenses #code_number").prop("disabled", true);
                    $("#md-expenses").modal("show");
                }
                else
                {
                    alertify.alert(data.msg)
                }
            }, "json");
            
        });

        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_assets_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/assets.pdf');?>';
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
            alertify.confirm("Are you sure you wish to delete this expenses account?", function () {
                var url = $("#frmAssetDelete").attr("action");
                var params = $("#frmAssetDelete").serialize();
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