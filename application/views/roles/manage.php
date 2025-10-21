<?php $this->load->view("partial/header"); ?>
<style>
    table#datatable td:nth-child(6) {
        width: 15%;
        text-align: center;
        white-space: nowrap;
    }       
    table#datatable td:nth-child(7) {
        width: 5%;
        white-space: nowrap;
    } 
</style>

<div class="title-block">
    <h3 class="title"><?=$this->lang->line("roles_title");?></h3>
    <p class="title-description">
        <?=$this->lang->line("roles_sub_title");?>
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
                                    
                                    <div class="inqbox-content table-responsive">
                                        <table id="datatable" class="table table-hover table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center; width: 1%"><input type="checkbox" class="select_all_" /></th>
                                                    <th style="text-align: center"><?=ktranslate2("Name");?></th>
                                                    <th style="text-align: center"><?=ktranslate2("Added by");?></th>

                                                    <th style="text-align: center; width: 1%"><?= $this->lang->line("common_action"); ?></th>
                                                </tr>
                                            </thead>
                                        </table>
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

<div class="hide-staff" style="display: none;">    
    <select class="form-control input-sm hidden-xs" id="sel-staff">
        <option value="0">Select staff</option>
        <?php foreach ($staffs as $staff): ?>
            <option value="<?= $staff->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $staff->person_id) ? 'selected="selected"' : ""; ?>><?= $staff->first_name . " " . $staff->last_name; ?></option>
        <?php endforeach; ?>
    </select>
    
    &nbsp;<?= anchor("$controller_name/delete", $this->lang->line("common_delete"), array('id' => 'delete', 'class' => 'btn btn-primary', 'style' => 'color:white')); ?>&nbsp;
    <?= anchor("$controller_name/view/-1", "<div class='btn btn-primary' style='float: left; margin-right:10px;'><span>" . $this->lang->line($controller_name . '_new') . "</span></div>"); ?>
</div>

<div id="feedback_bar"></div>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
    $(document).ready(function ()
    {
        $('body').on('hidden.bs.modal', '.modal', function () {
            $(this).removeData('bs.modal');
        });

        $("#datatable").dataTable({
            "aoColumnDefs": [
                {'bSortable': false, 'aTargets': [0, 3]}
            ],
            "language": {
                "url": "<?php echo base_url($this->config->item('language') . ".json"); ?>"
            },
            "processing": true,
            "serverSide": true,
            "aLengthMenu": [[50, 100, 200, 100000], [50, 100, 200, "<?= $this->lang->line("common_all"); ?>"]],
            "iDisplayLength": 50,
            "order": [1, "desc"],
            "ajax": {
                "url": "<?php echo site_url("$controller_name/data") ?>",
                data: {employee_id: '<?= isset($_GET['employee_id']) ? $_GET['employee_id'] : false; ?>'},
                type: 'get'
            },
            "initComplete": function (settings, json) {
                $("#datatable_filter").find("input[type='search']").attr("placeholder", "<?= $this->lang->line("common_search") ?>");
                var el = $(".dataTables_filter").find('label');
                    el.append("&nbsp;");                    
                    el.append($(".hide-staff").html());
            }
        });

        $(document).on("change", "#sel-staff", function(){
            location.href = "<?=site_url($this->uri->segment(1))?>?employee_id=" + $(this).val();
        });
        
        enable_delete('<?php echo $this->lang->line($controller_name . "_confirm_delete") ?>', '<?php echo $this->lang->line($controller_name . "_none_selected") ?>');

        $(".select_all_").click(function () {
            if ($(this).is(":checked"))
            {
                $(".select_").prop("checked", true);
            } else
            {
                $(".select_").prop("checked", false);
            }
        });

    });

</script>