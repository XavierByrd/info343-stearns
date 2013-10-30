
$(function(){
    $('.btn-geoloc').click(function(){
        if (navigator && navigator.geolocation) {
            $(this).html('Getting your location...');
            navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoError, 
                {enableHighAccuracy: true});
        }
        else
            alert("Your browser doesn't appear to support geolocation!");
    });
});

function onGeoSuccess(position) {
    $('.btn-geoloc').html("Try It Again!");
    alert("Your current coordinates are latitude=" 
        + position.coords.latitude 
        + ", longitude="
        + position.coords.longitude);
}

function onGeoError(err) {
    $('.btn-geoloc').html("Sorry, Geolocation Unavailable");
    alert("Sorry, I was unabe to obtain your coordinates for the following reason: " + err.message);
}