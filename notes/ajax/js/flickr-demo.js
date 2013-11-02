
var flickrFeedUrl = "http://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=processJSON&format=json&tagmode=any";
var container = $('.photos-container');

//document ready function
$(function(){
    getPhotos();

    $('.search-tags').keypress(function(evt){
        //if enter key is pressed, treat that as a search
        //char code for enter is 13
        if (13 == evt.keyCode) {
            getPhotos($(this).val());
        }
    });
});

function getPhotos(tags) {
    container.empty();
    container.addClass('loading');

    var scriptElem = $(document.createElement('script'));
    scriptElem.attr('src', flickrFeedUrl + (tags ? '&tags=' + tags : ''));
    $('body').append(scriptElem);
}

function processJSON(data) {
    var img;
    var a;

    container.removeClass('loading');

    $.each(data.items, function(){
        img = $(document.createElement('img'));
        img.attr({
            src: this.media.m,
            alt: htmlEncode(this.title),
            title: htmlEncode(this.title)
        });

        a = $(document.createElement('a'));
        a.attr({
            href: this.link,
            target: '_blank'
        });

        a.append(img);
        container.append(a);
    });

} //processJSON()

//htmlEncode()
// encodes the passed string of HTML so that it can
// be safely added to a page without being interpreted
// as HTML markup (with potentially harmful effects)
// source: http://stackoverflow.com/questions/1219860/html-encoding-in-javascript-jquery
// parameters:
//  - s (string) value to html-encode
//  - return value (string) encoded HTML value
//
function htmlEncode(s) {
    //create an in-memory div element
    var div = document.createElement('div');
    //append the string to encode as a text node
    div.appendChild(document.createTextNode(s));
    //return the innerHTML property (which will be encoded)
    return div.innerHTML;
} //htmlEncode()