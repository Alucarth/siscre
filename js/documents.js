$(document).ready(function(){
    "use strict";
    
    $("#btn-submit-upload").on("click", function(){
        $("#frmDocUpload").submit();
    });
    
    $(document).on("click", ".btn-delete", function(){
        var $this = $(this);
        alertify.confirm("Are you sure you wish to delete this document?", function(){
            var url = SITE_URL + 'documents/ajax';
            var params = {
                softtoken: $("input[name='softtoken']").val(),
                type: 1,
                id: $this.attr("data-document-id")
            };
            $.post(url, params, function(data){
                if ( data.status == "OK" )
                {
                    $("#tbl_documents").DataTable().ajax.reload();
                }                
            }, "json");
        });
    });
    
    $("input[name='file']").on("change", function(){
        $("#document_name").val($(this).val().replace(/C:\\fakepath\\/i, ''));
    });
    
    $("#frmDocUpload").on("submit", function(e){
        e.preventDefault();
        var $this = $(this);
        var formData = new FormData( this );
        
        $("#btn-submit-upload").prop("disabled", true);
        $.ajax({
            url: $this.attr("action"),
            type: 'POST',
            data: formData,
            success: function (data) {
                var data = $.parseJSON(data);
                if ( data.status == "OK" )
                {
                    $("#md-upload-document").modal("hide");
                    $("#tbl_documents").DataTable().ajax.reload();
                }
                else
                {
                    alertify.alert("File extension is not allowed!");
                }
                
                $("#btn-submit-upload").prop("disabled", false);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });
});