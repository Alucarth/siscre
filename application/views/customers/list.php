<?php $this->load->view("partial/header"); ?>

<style>
    #tbl_loans_transactions td:nth-child(5),
    #tbl_loans_transactions td:nth-child(6) {
        text-align: right;
    }
    .dataTables_info {
        float:left;
    }
    
    .dataTables_scrollBody {
        max-height: fit-content !important;
        height: auto !important;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="title-block">
    <h3 class="title"> 

       <?= ktranslate("customers_list");?>

    </h3>
    <p class="title-description">
        <?= ktranslate("customers_list_desc")?>
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
                                <div class="inqbox-content table-responsive">

                                    <table class="table table-hover table-bordered" id="tbl_borrowers">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center"><?=ktranslate("common_last_name")?></th>
                                                <th style="text-align: center"><?= ktranslate("common_first_name")?></th>
                                                <th style="text-align: center"><?=ktranslate("common_address_1")?></th>
                                                <th style="text-align: center"><?= ktranslate("common_address_2")?></th>
                                                <th style="text-align: center"><?=ktranslate("common_phone_number")?></th>
                                                <?php if ( is_plugin_active('branches') ): ?>
                                                    <th style="text-align: center"><?= ktranslate("branch_branch_label");?></th>
                                                <?php endif; ?>
                                                
                                                <?php foreach ( $extra_fields as $field ): ?>
                                                    <?php if ( $field->show_to_list ): ?>
                                                        <th style="text-align: center"><?=$field->label;?></th>   
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                
                                            </tr>
                                        </thead>
                                    </table>

                                    <?= $tbl_borrowers; ?>

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
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> <?= ktranslate("common_print");?></button>
    &nbsp;<button class="btn btn-primary" id="btn-add-field"><span class="fa fa-table"></span> <?= ktranslate("common_add_custom_field")?></button>
    <select class="form-control hidden-xs" id="sel-staff">
        <option value="0"><?=ktranslate("employees_select");?></option>
        <?php foreach ($staffs as $staff): ?>
            <option value="<?= $staff->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $staff->person_id) ? 'selected="selected"' : ""; ?>><?= $staff->first_name . " " . $staff->last_name; ?></option>
        <?php endforeach; ?>
    </select>&nbsp;
</div>

<!-- Modal -->
<div class="modal fade" id="md-extra-fields" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px">
            <div class="modal-header">
                <?= ktranslate2("Extra fields")?>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-info" style="font-size: 10px">
                    <span class="fa fa-info-circle"></span>
                    <?=ktranslate("custom_field_terms")?>
                </div>
                
                <button class="btn btn-primary" id="btn-add-extra-field-row" type="button"><?=ktranslate("common_add_row");?></button>
                <button class="btn btn-primary" id="btn-remove-extra-field-row" type="button"><?=ktranslate("common_remove_row");?></button>
                
                <table class="table table-bordered" id="tbl-extra-fields">
                    <thead>
                        <tr>
                            <th style="text-align: center"></th>
                            <th style="text-align: center"><?= ktranslate("common_name");?></th>
                            <th style="text-align: center"><?= ktranslate("common_label");?></th>
                            <th style="text-align: center"><?= ktranslate("common_show_to_list");?></th>
                            <th style="text-align: center; width: 20%"><?= ktranslate("common_data_type");?></th>
                        </tr>
                        <tr id="tr-new-row" style="display:none">   
                            <td style="text-align: center">
                                <input type="checkbox" name="_remove_field[]" value="" />
                            </td>
                            <td style="text-align: center; width: 150px">
                                <input type="text" class="form-control" name="field_names[]" placeholder="Enter field name" />
                            </td>
                            <td style="text-align: center; width: 150px">
                                <input type="text" class="form-control" name="label[]" placeholder="Enter label" />
                            </td>
                            <td style="text-align: center; width: 150px">
                                <select class="form-control" name="show_to_list[]">
                                    <option value="0"><?= ktranslate("common_no");?></option>
                                    <option value="1"><?= ktranslate("common_yes");?></option>
                                </select>
                            </td>
                            <td style="text-align: center">
                                <?= ktranslate("common_text");?>
                            </td>
                        </tr>
                    </thead>
                    <tbody>                        
                        <?php foreach ( $extra_fields as $field ): ?>
                            <tr>
                                <td style="text-align: center">
                                    <input type="checkbox" name="_remove_field[]" value="<?=$field->id;?>" />
                                </td>
                                <td style="text-align: center">
                                    <?=$field->name;?>
                                    <input type="hidden" name="field_names[]" value="<?=$field->name;?>">
                                </td>
                                <td style="text-align: center">
                                    <?=$field->label;?>
                                    <input type="hidden" name="label[]" value="<?=$field->label;?>">
                                </td>
                                <td style="text-align: center">
                                    <?=$field->show_to_list ? 'Yes' : 'No';?>
                                    <input type="hidden" name="show_to_list[]" value="<?=$field->show_to_list;?>">
                                </td>
                                <td style="text-align: center">
                                    Text
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= ktranslate("common_close");?></button>
                <button type="button" class="btn btn-primary" id="btn-save-extra-field"><?= ktranslate("common_save");?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div id="dt-extra-params">
    <input type="hidden" id="employee_id" name="employee_id" value="0" />
</div>

<?php echo form_open('customers/ajax', 'id="frmCustomerDelete"', ["type" => 2]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        $(document).on("click", "#btn-remove-extra-field-row", function(){            
            
            var ids = [];
            $("input[name='_remove_field[]']").each(function(){                    
                if ( $(this).is(":checked") )
                {
                    ids.push( $(this).val() );
                }
            });

            if ( ids.length == 0 )
            {
                alertify.alert("Please select row");
                return false;
            }
            
            alertify.confirm("<?= ktranslate2("Are you sure you wish to remove the selected fields and it's data? This can't be undone")?>", function(){
                var url = '<?=site_url('customers/ajax');?>';
                var params = {
                    softtoken: $("input[name='softtoken']").val(),
                    type: 4,
                    ids: ids
                };
                $.post(url, params, function(data){
                    if ( data.status == "OK" )
                    {
                        $("input[name='_remove_field[]']").each(function(){                    
                            if ( $(this).is(":checked") )
                            {
                                $(this).parent().parent().remove();
                            }
                        });
                    }
                }, "json");
            });
        });
        
        $(document).on("click", "#btn-save-extra-field", function(){
            var url = '<?=site_url('customers/ajax');?>';
            var params = $("#tbl-extra-fields tbody input, #tbl-extra-fields tbody select").serialize();
            
            if ( params.length == 0 )
            {
                alertify.alert("Please add a row");
                return false;
            }
            
            var has_error = false;
            $("#tbl-extra-fields tbody input[name='field_names[]']").each(function(){
                if ( $(this).val() == '' )
                {
                    has_error = true;
                }
            });
            
            $("#tbl-extra-fields tbody input[name='label[]']").each(function(){
                if ( $(this).val() == '' )
                {
                    has_error = true;
                }
            });
            
            if ( has_error )
            {
                alertify.alert("Input box can't be empty. Please enter a value");
                return false;
            }
            
            params += '&softtoken=' + $("input[name='softtoken']").val();
            params += '&type=3';
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    alertify.alert("Extra fields has been save successfully!", function(){
                        $("#md-extra-fields").modal("hide");
                        window.location.reload();
                    });
                }
            }, "json");
        });
        
        $(document).on("click", "#btn-add-field", function(){
            $("#md-extra-fields").modal("show");
        });
        
        $("#btn-add-extra-field-row").click(function(){
           $("#tbl-extra-fields tbody").append( "<tr>" + $("#tr-new-row").html() + "</tr>" );
        });
        
        $("#tbl_borrowers_filter").prepend("<a href='<?= site_url('customers/view/-1') ?>' class='btn btn-primary pull-left'><?=ktranslate2("New Borrower")?></a>");
        $("#tbl_borrowers_filter input[type='search']").attr("placeholder", "Type your search here");
        $("#tbl_borrowers_filter input[type='search']").removeClass("input-sm");
        $("#tbl_borrowers_filter").append($(".extra-filters").html());

        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_borrowers_wrapper .dataTables_scrollBody").clone();
            
            $(clone).find("table").attr("border", 1);
            $(clone).find("table").attr("cellpadding", 5);
            $(clone).find("table").attr("cellspacing", 1);
            $(clone).find("table").attr("width", "100%");
            $(clone).find("table th:nth-child(1)").remove();
            $(clone).find("table td:nth-child(1)").remove();
            
            var url = '<?=site_url('printing/print_list/customers.pdf');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                title: 'List of Borrowers',
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
        
        $(document).on("change", "#filter_by", function () {
            $("#status").val($(this).val());
            $("#tbl_borrowers").DataTable().ajax.reload();
        });

        $(document).on("change", "#sel-staff", function () {
            $("#employee_id").val($(this).val());
            $("#tbl_borrowers").DataTable().ajax.reload();
        });

        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Are you sure you wish to delete this customer?", function () {
                var url = $("#frmCustomerDelete").attr("action");
                var params = $("#frmCustomerDelete").serialize();
                params += '&ids=' + $this.attr("data-customer-id");
                $.post(url, params, function (data) {
                    if (data.success)
                    {
                        $("#tbl_borrowers").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>