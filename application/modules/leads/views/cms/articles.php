<?php $this->load->view("partial/header"); ?>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<?php echo form_open('leads/ajax', 'id="frmLeadsArticles"', ["type" => 10]); ?>
<?php echo form_close(); ?>


<div class="title-block">
    <h3 class="title"> 

        Leads - Articles

    </h3>
    <p class="title-description">
        Add, update & delete articles
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
                                    <div class="tabs-container">

                                    </div>

                                    <table class="table table-bordered" id="leads_articles">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; width: 1%"></th>
                                                <th style="text-align: center">Title</th>
                                                <th style="text-align: center">Content</th>
                                                <th style="text-align: center">Last Modified Date</th>                            
                                                <th style="text-align: center">Added By</th>                            
                                                <th style="text-align: center">Publish</th>                            
                                            </tr>
                                        </thead>        
                                    </table>

                                    <?= $tbl_leads_articles; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#leads_articles_filter").prepend('<div style="float: left;"><a href="<?= site_url('leads/view_article') ?>" class="btn btn-primary">New Article</a></div>')
        $(document).on("click", ".btn-publish", function () {
            var $this = $(this);

            var publish_msg = 'publish';
            if ($this.attr("data-published") == '0')
            {
                publish_msg = 'un-publish';
            }

            alertify.confirm("Are you sure you wish to " + publish_msg + " this article?", function () {
                var url = '<?= site_url('leads/ajax') ?>';
                var params = {
                    softtoken: $("input[name='softtoken']").val(),
                    article_id: $this.attr("data-article-id"),
                    published: $this.attr("data-published"),
                    type: 13
                };
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#leads_articles").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
        $(document).on("click", ".btn-delete", function () {
            var $this = $(this);
            alertify.confirm("Are you sure you wish to delete this article? This can't be undone", function () {
                var url = '<?= site_url('leads/ajax') ?>';
                var params = {
                    softtoken: $("input[name='softtoken']").val(),
                    article_id: $this.attr("data-article-id"),
                    type: 14
                };
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $("#leads_articles").DataTable().ajax.reload();
                    }
                }, "json");
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>

