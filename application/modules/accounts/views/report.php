<?php $this->load->view("partial/header"); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap;
    }
    
    
    
    <?php
    $count_accounts = 0;
    if ($accounts)
    {
        foreach ( $accounts->result() as $row )
        {
            $count_accounts++;
        }
    }
    
    $y=2;
    for ($i=0; $i<($count_accounts*3); $i++)
    {
        $tmp[] = "td:nth-child($y)";
        $y++;
    }

    $str = implode(",", $tmp);
    echo $str . "{
        text-align:center;
    }";
    
    ?>
    
    .dataTables_info {
        float:left;
    }
</style>


<div class="title-block">
    <h3 class="title"> 
        <span style="float:left">Libro mayor de clientes</span>
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
                                <div class="inqbox-title">
                                    <!--<h5><i class="fa fa-filter"></i> Filters - Advance</h5>//-->
                                </div>
                                <div class="inqbox-content">
                                    <div class="row" id="dt-extra-params">
                                        
                                        <input type="hidden" id="account_id" name="account_id" value="" />
                                        <input type="hidden" id="employee_id" name="employee_id" value="0" />

                                        
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Fecha desde:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_from_date" name="filter_from_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Fecha hasta:</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                                                    <input type="text" class="form-control" id="filter_to_date" name="filter_to_date" value="<?= date($this->config->item("date_format")); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Cliente:</label>
                                                <select class="form-control" name="customer" id="customer">
                                                    <?php foreach ( $customers as $customer ): ?>
                                                    <option value="<?=$customer->person_id?>"><?=$customer->first_name . " " . $customer->last_name;?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" class="btn btn-primary" id="btn-search">Buscar</button>
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

<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">

                    <div class="inqbox-content table-responsive">
                        <table class="table table-hover table-bordered" id="tbl_account_report">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <?php if ($accounts): ?>
                                        <?php foreach ($accounts->result() as $row): ?>
                                            <th colspan="3" style="text-align:center"><?= $row->account_name; ?></th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                                <tr>
                                    <th style="text-align: center">Cliente</th>
                                    <th style="text-align: center">Transacción</br> Fecha</th>
                                    <?php if ($accounts): ?>
                                        <?php foreach ($accounts->result() as $row): ?>
                                            <th style="text-align: center">Pagado en</th>
                                            <th style="text-align: center">Retiro</th>
                                            <th style="text-align: center">Balance</th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <?= $tbl_account_report; ?>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="extra-filters" style="display: none;">
    &nbsp;<button class="btn btn-primary" id="btn-export-pdf"><span class="fa fa-print"></span> Imprimir</button>
    <select class="form-control hidden-xs" id="sel-staff">
        <option value="0">Seleccionar cliente</option>
        <?php foreach ($staffs as $staff): ?>
            <option value="<?= $staff->person_id; ?>" <?= ((isset($_GET['employee_id'])) && $_GET['employee_id'] === $staff->person_id) ? 'selected="selected"' : ""; ?>><?= $staff->first_name . " " . $staff->last_name; ?></option>
        <?php endforeach; ?>
    </select>&nbsp;
    <select class="form-control" name="sel_account_id" id="sel_account_id" style="margin-right:6px">
        <option value="">Escoger cuenta</option>
        <?php if ( $accounts ): ?>
            <?php foreach ( $accounts->result() as $account ): ?>
                <option value="<?=$account->id;?>"><?=$account->account_name?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>&nbsp;
</div>


<?php echo form_open('accounts/ajax', 'id="frmLoanAccountDelete"', ["type" => 4]); ?>
<?php echo form_close(); ?>

<script>
    $(document).ready(function () {
        
        $("#btn-search").click(function(){
            $("#tbl_account_report").DataTable().ajax.reload();
        });
        
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });
        
        $(document).on("change", "#sel-staff, #sel_account_id", function () {
            $("#account_id").val($("#sel_account_id").val());
            $("#employee_id").val($("#sel-staff").val());
            $("#tbl_account_transactions").DataTable().ajax.reload();
        });
        
        $("#tbl_account_report_filter input[type='search']").attr("placeholder", "Buscar aquí");
        $("#tbl_account_report_filter input[type='search']").removeClass("input-sm");
        $("#tbl_account_report_filter").append($(".extra-filters").html());

        $(document).on("click", "#btn-export-pdf", function(){
            var clone = $("#tbl_account_report").clone();
            
            var url = '<?=site_url('printing/print_list/accounts.pdf');?>';
            var params = {
                softtoken:$("input[name='softtoken']").val(),
                html: "<table border='1' width='100%' cellpadding='5' cellspacing='0'>" + clone.html() + "</table>"
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
    });
</script>

<?php $this->load->view("partial/footer"); ?>