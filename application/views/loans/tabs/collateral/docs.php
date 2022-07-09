<?php foreach ($supporting_docs as $doc): ?>
    <div style="position:relative" class="gal-box2">
        <img src="<?= check_file_icon($doc->document_path); ?>" />
        <?= $doc->document_name; ?>

        <div class="sel-cover" style="display:none;">
            <div style="
                 width: 100%;
                 position: absolute;
                 top: 0px;
                 left: 0;
                 text-align: center;
                 background: #fff;
                 height: 100%;
                 border-radius: 3px;
                 opacity: 0.8;
                 ">

            </div>
            <div style="
                 width: 100%;
                 position: absolute;
                 top: 0px;
                 left: 0;
                 text-align: center;
                 height: 100%;
                 border-radius: 3px;
                 ">
                <a href="<?= base_url($doc->document_path); ?>" target="_blank" title="View" style="color:blue"><span class="fa fa-eye"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="javascript:void(0)" title="Delete" class="btn-delete-proof-file" data-id="<?= $doc->document_id; ?>" style="color:red"><span class="fa fa-trash"></span></a>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    $(document).ready(function () {
        $(".gal-box2").hover(function () {
            $(this).find(".sel-cover").show();
        }, function () {
            $(this).find(".sel-cover").hide();
        });

        $(".btn-delete-proof-file").click(function () {
            var $this = $(this);
            alertify.confirm("Are you sure you wish to delete this file?", function () {
                var url = '<?= site_url('loans/ajax') ?>';
                var params = {
                    ajax_type: 9,
                    loan_id: '<?= $loan_id ?>',
                    document_id: $this.data("id"),
                    document_type: 'images',
                    softtoken: $("input[name='softtoken']").val()
                };
                $.post(url, params, function (data) {
                    if (data.status == "OK")
                    {
                        $this.parent().parent().parent().remove();
                    }
                }, "json");
            });
        });
    });
</script>
