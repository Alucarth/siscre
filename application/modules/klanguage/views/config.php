<?php
$list = glob(APPPATH . 'language/en/*_lang.php');
?>
<style>
    .btn-rem-category
    {
        display: inline-block !important;
    }
    .link-lang {
        display: inline-block !important;
        width: 300px;
    }
</style>
<div class="row">
    <div class="col-lg-3">
        <ul class="nav nav-pills flex-column">
            <?php $i = 0; ?>
            <?php foreach ($list as $file): ?>
            
            <?php if ($i==0) $initial_lang = str_replace("_lang.php", "", basename($file)); ?>
            
            <li class="nav-item"><a href="javascript:void(0)" data-lang-name="<?= str_replace("_lang.php", "", basename($file)); ?>" class="btn-rem-category"><span class="fa fa-trash"></span></a> <a class="nav-link link-lang <?=$i==0?'active':''?>" data-lang-name="<?= str_replace("_lang.php", "", basename($file)); ?>" data-counter="<?= $i; ?>" data-toggle="tab" href="#tab-lang"><?= ucwords(str_replace(["_lang.php", "_"], ["", " "], basename($file))); ?></a></li>
                <?php $i++; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="col-lg-9">
        <div class="alert alert-info">Please translate the text below.</div>

        <input type="hidden" id="hid-lang-name" value="" />

        <div class="clearfix">
            <button type="button" class="btn btn-primary pull-right" title="Add a Word" id="btn-add-lang-word"><span class="fa fa-plus"></span></button>
            <button type="button" class="btn btn-primary pull-right" title="Search" id="btn-search-lang-word" style="margin-right:4px;"><span class="fa fa-search"></span></button>
        </div>
        <div class="tab-content" style="height: 500px;overflow: auto;">
            <?php $i = 0; ?>
            <?php foreach ($list as $file): ?>
                <div id="tab-lang" class="tab-pane fade <?= $i == 0 ? 'show active' : '' ?> in">
                    <div id="div-lang"></div>
                </div>
                <?php $i++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="md-add-word-lang" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                New Word
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" id="sel-lang-category">
                        <option value="">New</option>
                        <optgroup label="Current">
                            <?php foreach ($list as $file): ?>
                                <option value="<?= str_replace("_lang.php", "", basename($file)); ?>"><?= ucwords(str_replace(["_lang.php", "_"], ["", " "], basename($file))); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>                    
                </div>
                <div class="form-group" id="div-new-lang-category" style="display:none;">
                    <input type="text" id="txt-new-lang-category" class="form-control" value="" placeholder="Type new category here..." />
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <div>
                        <div class="input-group">
                            <span class="input-group-addon input-group-append"><span class="input-group-text" id="sp-prefix-lang-key">common_</span></span>
                            <input type="text" class="form-control" id="txt-add-word-key" value="" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Text</label>
                    <textarea class="form-control" id="txt-add-word-text"></textarea>
                </div>
                
                <div id="div-new-word-lang" style="display:none;">
                    <div>
                        <div class="cont-77xz2"></div>
                        <div class="cont-77xz1">
                            <textarea class="form-control" style="min-height:50px;" name=""></textarea>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save-add-word">Add</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md-search-word-lang" role="dialog">
    <div class="modal-dialog" style="width: 750px; max-width: none;">
        <div class="modal-content">
            <div class="modal-header">
                Search Word
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Type your search here</label>
                    <input type="text" class="form-control" value="" id="txt-lang-search" name="txt-lang-search" />
                </div>
                
                <div class="form-group">
                    <label>Results:</label>
                    <div id="div-lang-search-result" style="max-height: 550px; overflow: auto;"></div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /.modal -->

<script>
    $(document).ready(function () {
        
        <?php if ( isset($new_add) && $new_add ): ?>
                
        <?php else:?>
            load_lang_content("<?=$initial_lang?>");
        <?php endif; ?>
            
        $("#btn-search-lang-word").click(function(){
            $("#md-search-word-lang").modal("show");
        });
        
        $(document).on("click", ".btn-search-goto", function(){
            $("#md-search-word-lang").modal("hide");
            var lang_name = $(this).attr("data-lang-name");
            $(".link-lang[data-lang-name='"+lang_name+"']").trigger("click");
        });
        
        $("#txt-lang-search").keyup(function(){
            var keywords = $(this).val();
            var url = '<?=site_url('klanguage/ajax')?>';
            var params = {
                type:5,
                keywords: keywords,
                softtoken:$("input[name='softtoken']").val()
            };
            $.post(url, params, function(data){
                $("#div-lang-search-result").html(data);
            });
        });
            
        $("#txt-new-lang-category").keyup(function(){
            $(this).val( $(this).val().replace(/ /g,"_") );
            $("#sp-prefix-lang-key").html($(this).val().toLowerCase() + "_");
        });
            
        $(".btn-rem-category").click(function(){
            var lang_name = $(this).attr("data-lang-name");
            alertify.confirm("This action cannot be undone, are you sure you wish to remove this category?", function(){
                var url = '<?=site_url('klanguage/ajax')?>';
                var params = {
                    type:4,
                    lang_name: lang_name,
                    softtoken:$("input[name='softtoken']").val()
                };
                $.post(url, params, function(data){
                    if ( data.status == "OK" )
                    {
                        load_lang_config();
                    }
                }, "json");
            });
        });
            
        $("#txt-add-word-key").keyup(function(){
            $(this).val( $(this).val().replace(/ /g,"_") );
        });
        
        $("#sel-lang-category").change(function(){
            if ( $(this).val() == '' )
            {
                $("#div-new-lang-category").show();
            }
            else
            {
                $("#div-new-lang-category").hide();
                $("a.link-lang[data-lang-name='"+$(this).val()+"']").trigger("click");
            }
        });
        
        $(".link-lang").click(function () {
            var lang_name = $(this).attr("data-lang-name");
            load_lang_content(lang_name);
            $("#hid-lang-name").val(lang_name);
        });        
        
        $(document).on("click", "#btn-add-lang-word", function(){
            $("#md-add-word-lang").modal("show");
        });
        
        $("#txt-add-word-key, #txt-add-word-text").keyup(function(){
            $("#div-new-word-lang .cont-77xz2").html( '<a href="javascript:void(0)" class="btn-remove-lang" style="color:#e80e0e"><span class="fa fa-trash"></span></a> ' + $("#hid-lang-name").val() + "_" + $("#txt-add-word-key").val());
            $("#div-new-word-lang .cont-77xz1 textarea").attr("name", "lang[" +$("#hid-lang-name").val() + "_" + $("#txt-add-word-key").val() + "]");
            $("#div-new-word-lang .cont-77xz1 textarea").val( $("#txt-add-word-text").val());
        });
        
        $("#btn-save-add-word").click(function(){
            
            if ( $("#sel-lang-category").val() == '' )
            {
                var lang_name = $("#txt-new-lang-category").val().toLowerCase();
                var url = '<?=site_url('klanguage/ajax');?>';
                var params = {
                    type:3,
                    lang_name:lang_name,
                    softtoken:$("input[name='softtoken']").val()                    
                };
                
                params["lang["+ $("#sp-prefix-lang-key").html() + $("#txt-add-word-key").val()+"]"] = $("#div-new-word-lang textarea").val();
                
                $.post(url, params, function(data){
                    if ( data.status == "OK" )
                    {
                        $("#md-add-word-lang").modal("hide");
                        
                        var url = '<?=site_url('klanguage/ajax')?>';
                        var params = {
                            type:1,
                            new_add:1,
                            softtoken:$("input[name='softtoken']").val()
                        };
                        blockElement("#div-language-container");
                        $.post(url, params, function(data){
                            $("#div-language-container").html(data);
                            unblockElement("#div-language-container");
                            $("a.link-lang[data-lang-name='"+lang_name+"']").trigger("click");
                            load_lang_content(lang_name.toLowerCase());
                        });
                    }
                }, "json");
            }
            else
            {
                $("#md-add-word-lang").modal("hide");
                $("#tbl-lang-form").prepend($("#div-new-word-lang").html());            
                var new_lang_name = "lang[" +$("#hid-lang-name").val() + "_" + $("#txt-add-word-key").val() + "]";
                $("textarea[name='"+new_lang_name+"']").val($("#txt-add-word-text").val());
            }
        });
        
        $(document).on("click", ".btn-remove-lang", function(){
            var $this = $(this);
            alertify.confirm("This action cannot be undone, are you sure you wish to remove this word?", function(){
                $this.parent().parent().remove();                
            });
        });
    });
    
    function load_lang_content(lang_name)
    {
        var url = '<?= site_url('klanguage/ajax'); ?>';
        var params = {
            type: 2,
            lang_name: lang_name,
            softtoken: $("input[name='softtoken']").val()
        };
        $.post(url, params, function (data) {
            $("#sp-prefix-lang-key").html(lang_name + "_");
            $("#div-lang").html(data);
            $("#sel-lang-category").val(lang_name);
            $("#hid-lang-name").val(lang_name);
        });
    }
</script>