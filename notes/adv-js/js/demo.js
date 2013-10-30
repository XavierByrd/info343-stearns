
$(function(){
    $('.btn-geoloc').click(function(){
        if (navigator && navigator.geolocation)
            navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoError, 
                {enableHighAccuracy: true});
        else
            alert("Your browser doesn't appear to support geolocation!");
    });
});

function onGeoSuccess(position) {
    alert("Your current coordinates are latitude=" 
        + position.coords.latitude 
        + ", longitude="
        + position.coords.longitude);
}

function onGeoError(err) {
    alert("Sorry, I was unabe to obtain your coordinates for the following reason: " + err.message);
}