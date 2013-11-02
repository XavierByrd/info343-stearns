
$(function(){
    $('.try-ajax-demo').click(function(){
        var tryButton = $(this);
        tryButton.html("Requesting Data...");
        tryButton.attr('disabled', true);

        $.getJSON("http://courses.washington.edu/info343/ajax/test.php", function(data){
            alert('The server responded with the message "' + data.message + '"');

            tryButton.html("Try Again!");
            tryButton.removeAttr('disabled');
        });
    });
})