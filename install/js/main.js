var interv = null;

$(document).ready(function () {
    "use strict";
    
    $("#submit").on("click", function (e) {
        e.preventDefault();
        
        $("#install_form").hide();
        
        dep_download();
        
    });
});

function dep_download()
{
    var i = 0;
    var text = "Downloading Dependencies ";
    
    $("#div_install_dependencies").show();
    
    interv = setInterval(function() {
        $("#loading").html(text+Array((++i % 10)+1).join("&#46;"));
    }, 500);
    
    var url = 'download.php';
    var params = {};
    $.post(url, params, function(data){
        if ( data.status == "OK" )
        {
            $("#loading").html("Downloading Dependencies ... Done <br/><span id='sp_dep_extract'></span>");
            clearInterval(interv);
            dep_extract();
        }
        else
        {
            alert(data.message);
        }
    }, "json");
}

function dep_extract()
{
    var i = 0;
    var text = "Extracting Dependencies ";
    
    interv = setInterval(function() {
        $("#sp_dep_extract").html(text+Array((++i % 10)+1).join("&#46;"));
    }, 500);
    
    var url = 'extract.php';
    var params = {};
    $.post(url, params, function(data){
        if ( data.status == "OK" )
        {
            $("#sp_dep_extract").html("Extracting Dependencies ... Done <br/><span id='sp_db_install'></span>");
            clearInterval(interv);
            db_install();
        }
        else
        {
            alert(data.message);
        }
    }, "json");
}

function db_install()
{
    var i = 0;
    var text = "Importing Database Tables ";
    
    interv = setInterval(function() {
        $("#sp_db_install").html(text+Array((++i % 10)+1).join("&#46;"));
    }, 500);
    
    $("#submit").prop("disabled", true);
    $("#submit").html("Installing, Please wait...");

    var url = $("#install_form").attr("action");
    var params = $("#install_form").serialize();

    $.post(url, params, function (data) {
        if (data.status == "OK")
        {
            window.location.href = data.href;
        } 
        else
        {
            $(".error").html(data.message);
            $(".error").show();
        }

        $("#submit").prop("disabled", false);
        $("#submit").html("Install");

    }, "json");
}