
$(function(){

    //var mapOptions = mapOptions || {};
    var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);


    function seconds(s) {
        return s * 1000;
    }

    //function initialize() {
    //
    //}
    //google.maps.event.addDomListener(window, 'load', initialize);

    setInterval(function() {
        $.nette.ajax(urlRefresh);
    }, seconds(10));


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

                //return false;
            }
        }
    }, {
        marker: null,
        latLng: null
    });

    $.nette.ext('command', {
        success: function (payload) {
            if(payload.command) {

                $.growl({
                    title: payload.command.text,
                    message: "příkaz odeslán do telefonu",
                    duration: seconds(10),
                    style: 'warning command-'+payload.command.id
                });
                //$.growl.error({ message: "The kitten is attacking!" });
                //$.growl.notice({ message: "The kitten is cute!" });
                //$.growl.warning({ message: "The kitten is ugly!" });

            }

            if(payload.ackeds) {
                for(ack in payload.ackeds) {
                    $.growl({
                        title: payload.ackeds[ack].text,
                        message: "příkaz dorazil do telefonu",
                        duration: seconds(10),
                        style: 'notice command-'+payload.ackeds[ack].id
                    });
                }
            }
        }
    }, {

    });





    $.nette.init();
});
