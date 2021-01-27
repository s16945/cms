jQuery(document).ready(function($){
	$(function(){
    $('#btnUpload').click(function(){
        //Show select file dialog
        $('#uploadFile').click();

        //Wait for user to select a file
        var tmr = setInterval(function(){
            if($('#uploadFile').val() !== "") {
                clearInterval(tmr);
                $('#upload_form').submit();
            }
        }, 500);
    });
});
});
