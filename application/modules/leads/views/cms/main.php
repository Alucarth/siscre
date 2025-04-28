<?php $this->load->view("partial/header"); ?>

<?php echo form_open('leads/ajax', 'id="frmLeadsDetails"', ["type" => 10]); ?>
<?php echo form_close(); ?>

<?php echo form_open('leads/ajax', 'id="frmLeadsApproval"', ["type" => 11]); ?>
<?php echo form_close(); ?>


<div class="title-block">
    <h3 class="title"> 

        Leads - Registered

    </h3>
    <p class="title-description">
        Leads registration list
    </p>
</div>


<div class="section">
    <div class="row sameheight-container">

        <div class="col-lg-12">
            <div class="card" style="width:100%">

                <div class="card-block">
                    <?php $this->load->view('leads/cms/members'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="md-leads-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title"><span id="sp-leads-name"></span> - Preview</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body clearfix">
                
                <input type="hidden" id="hid-leads-id" name="hid-leads-id" value="" />
                <div id="div-leads-info"></div>
                
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="btn-close">Close</button>
            </div>
            
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script>
    $(document).ready(function(){
        $(document).on("click", "#btn-approve", function(){
            var $this = $(this);
            alertify.confirm("Are you sure you wish to approve this application? This will create a loan entry", function(){
                var url = $("#frmLeadsApproval").attr("action");
                var params = $("#frmLeadsApproval").serialize();
                params += '&leads_id=' + $("#hid-leads-id").val();
                $this.prop("disabled", true);
                $this.html("Please wait...");
                $.post(url, params, function(data){
                    if ( data.status == "OK" )
                    {
                       alertify.alert("The application has been approved successfully!", function(){
                           $("#md-leads-detail").modal("hide");
                           $("#leads_orders").DataTable().ajax.reload();
                       });
                    }
                   
                    $this.prop("disabled", false);
                    $this.html('<i class="fa fa-thumbs-up"></i> Approve');
               }, "json");
            });
        });
        
        $(document).on("click", ".btn-approve", function(){
            var $this = $(this);
            alertify.confirm("Are you sure you wish to approve this application? This will create a loan entry", function(){
                var url = $("#frmLeadsApproval").attr("action");
                var params = $("#frmLeadsApproval").serialize();
                params += '&leads_id=' + $this.attr("data-leads-id");
                $.post(url, params, function(data){
                   if ( data.status == "OK" )
                   {
                       alertify.alert("The application has been approved successfully!");
                       $("#leads_orders").DataTable().ajax.reload();
                   }
               }, "json");
            });
        });
        
        $(document).on("click", ".btn-view-details", function(){
            var leads_id = $(this).attr("data-leads-id");
            var url = $("#frmLeadsDetails").attr("action");
            var params = $("#frmLeadsDetails").serialize();
            params += '&leads_id=' + leads_id;
            
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#sp-leads-name").html(data.leads_full_name);
                    $("#div-leads-info").html(data.leads_info);
                    $("#hid-leads-id").val(data.leads_id);
                    
                    
                    if ( data.application_status == 'approved' )
                    {
                        $("#btn-approve").hide();
                    }
                    else
                    {
                        $("#btn-approve").show();
                    }
                    
                    $("#md-leads-detail").modal("show");
                }
                else
                {
                    alertify.alert("Something went wrong in getting leads information!");
                }
            }, "json");
        });
        
        $(document).on("click", ".btn-delete", function(){
            var $this = $(this);            
            alertify.confirm("Are you sure you wish to remove this item? (This can't be undone)", function(){
                var url = "<?=site_url('leads/ajax')?>";
                var params = {
                    softtoken: $("input[name='softtoken']").val(),
                    type: 15,
                    leads_id: $this.attr("data-leads-id")
                };
                $.post(url, params, function(data){
                    if ( data.status == "OK" )
                    {
                        $("#leads_orders").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>

