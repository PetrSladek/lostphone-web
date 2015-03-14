
$(function(){

    //var mapOptions = mapOptions || {};
    var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);



    //function initialize() {
    //
    //}
    //google.maps.event.addDomListener(window, 'load', initialize);

    setInterval(function() {
        $.nette.ajax(urlRefresh);
    }, 1000*10);


    $.nette.ext('position', {
        success: function (payload) {
            if(payload.position && map && payload.position.find) {

                this.latLng = new google.maps.LatLng(payload.position.lat, payload.position.lng);
                this.marker = new google.maps.Marker({
                    position: this.latLng,
                    title: payload.device + " @ " + payload.position.find
                });
                this.marker.setMap(map);
                map.setCenter(this.latLng);

                return false;
            }
        }
    }, {
        marker: null,
        latLng: null
    });

    $.nette.init();
});
