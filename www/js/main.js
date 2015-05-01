
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

    //if (!!window.EventSource) {
    //    // Reading Ecents
    //    var source = new EventSource(urlEventsTemplate.replace("DEVICEID", deviceId));
    //    source.onmessage = function (e) {
    //        console.log(e);
    //    };
    //
    //} else {
        // Ajax Refresh by Interval
        setInterval(function () {
            var urlRefresh = urlRefreshTemplate.replace("DEVICEID", deviceId);
            $.nette.ajax(urlRefresh);
        }, seconds(10));
    //}


    if ("WebSocket" in window) {
        try {

            socket = new WebSocket(websocketUrl);
            socket.onopen = function () {
                console.log('connection is opened');
                socket.send(JSON.stringify({type: 'newDeviceListening', 'data': deviceId}));
                return;
            };
            socket.onmessage = function (msg) {

                if (msg.data == 'newMessage') {
                    var urlRefresh = urlRefreshTemplate.replace("DEVICEID", deviceId);
                    $.nette.ajax(urlRefresh);
                }
                console.log(msg.data);

                return;
            };
            socket.onclose = function () {
                console.log('connection is closed');
                return;
            };
        }
        catch (e) {

            console.log(e);
        }
    }


    function changeMapPosition(position, device) {
        var latLng = new google.maps.LatLng(position.lat, position.lng);
        var marker = new google.maps.Marker({
            position: latLng,
            title: device + " @ " + position.find
        });
        marker.setMap(map);
        map.setCenter(latLng);
    }


    $(document).on('click','[data-position-lat][data-position-lng]',function() {
        var position = {
            lat: $(this).data('position-lat'),
            lng: $(this).data('position-lng')
        }
        var device = $(this).data('device-name') ? $(this).data('device-name') : null;

        changeMapPosition(position, device);
    });


    $.nette.ext('position', {
        success: function (payload) {
            if(payload.position && map && payload.position.find) {
                changeMapPosition(payload.position, payload.device.name);
            }
            if(payload.state.deviceId) {
                deviceId = payload.state.deviceId;
            }
            if(payload.device.locked) {
                $('#device-locked').removeClass('hidden');
            } else {
                $('#device-locked').addClass('hidden');
            }
        }
    }, {
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
