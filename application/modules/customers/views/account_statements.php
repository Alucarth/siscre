<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%; min-height: calc(85vh - 160px);">

                <div class="card-block">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="inqbox float-e-margins">

                                <div class="inqbox-content table-responsive">

                                    <table class="table table-hover table-bordered" id="tbl_account_statements">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center">Descripción</th>                                                
                                            </tr>
                                        </thead>
                                    </table>

                                    <?= $tbl_account_statements; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<div id="dt-extra-params">
    <input type="hidden" id="customer_id" name="customer_id" value="<?=$person_info->person_id;?>" />    
</div>