<div id="div-language-container"></div>

<script>
    function load_lang_config()
    {
        var url = '<?= site_url('klanguage/ajax') ?>';
        var params = {
            type: 1,
            softtoken: $("input[name='softtoken']").val()
        };
        blockElement("#div-language-container");
        $.post(url, params, function (data) {
            $("#div-language-container").html(data);
            unblockElement("#div-language-container");
        });
    }

    $(document).ready(function () {
        $("a[href='#tab-language']").click(function () {
            $("#btn-submit").hide();
            $("#btn-save-lang").show();
            load_lang_config();
        });

        $("a").not("[href='#tab-language']").click(function () {
            $("#btn-submit").show();
            $("#btn-save-lang").hide();
        });

        $("#btn-save-lang").click(function () {
            var url = '<?= site_url('klanguage/ajax'); ?>';
            var params = $("#tbl-lang-form textarea").serialize();
            params += '&type=3';
            params += '&lang_name=' + $("#hid-lang-name").val();
            params += '&softtoken=' + $("input[name='softtoken']").val();
            $.post(url, params, function (data) {
                if (data.status == "OK")
                {
                    set_feedback("Language Translation has been successfully!", 'success_message', false);
                }
            }, "json");
        });
    });
</script>