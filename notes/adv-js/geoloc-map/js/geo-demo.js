// geo-demo.js 
// geolocation demo script 
// completed script: https://gist.github.com/drstearns/aa07f2b583e0335eefb8

//updateMesage()
// updates the message p tag above the map
// parameters:
//  - msg (string)  message to display
//
function updateMessage(msg) {
    $('.map-message').html(msg);
}

//onGeoSuccess()
// called with sucessfully-obtained geolocation
// parameters:
//  - position (Position object)
//      position.coords.latitude (number)
//      position.coords.longitude (number)
//      position.coords.accuracy (number of meters)
function onGeoSuccess(position) {
    //create a new Google LatLng object to represent
    //our current latitude and longitude
    var ll = new google.maps.LatLng(position.coords.latitude,
        position.coords.longitude);

    //create a simple object for the map options
    var mapOptions = {
        center: ll,     //center the map on our current lat/long
        zoom: 16,       //zoom level (0 = whole earth)
        mapTypeId: google.maps.MapTypeId.ROADMAP //roadmap type
    }

    //create a new Google Map object
    //passing a reference to the map container div
    //and our map options
    var map = new google.maps.Map($('.map-container')[0], mapOptions);

    //now add a marker on the map showing our lat/long
    var markerOptions = {
        map: map,
        position: ll,
        title: 'You are Here'
    }

    var marker = new google.maps.Marker(markerOptions);

    updateMessage("Here is where you are (accuracy within " 
        + position.coords.accuracy + " meters)");

}

//onGeoError()
// called when geolocation produces an error
// parameters:
//  - err (PositionError object)
//      err.code (number)
//      err.message (string)
function onGeoError(err) {
    updateMessage("Sorry, I was unable to obtain your current position " + 
        "for the following reason: " + err.message);
}

//docment ready function
$(function(){
    //first test if geolocation services are available
    if (navigator && navigator.geolocation) {
        updateMessage('I am now obtaining your current position...');

        //call getCurrentPosition, with high accuracy
        //(high accuracy ignored on devices that don't have GPS)
        navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoError, 
            {enableHighAccuracy: true});
    }
    else {
        //geolocation services not available
        updateMessage("Sorry, geolocation services are not available!");
    }
});
