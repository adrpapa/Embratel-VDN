define([],
    function() {
        return (
            function (millis) {
                var hours = Math.floor(millis/1000/60/60);
                var seconds = "0" + Math.floor(millis/1000)%60;
                var minutes = "0" + Math.floor(millis / 1000 / 60 ) % 60;
                return( hours + ":" + minutes.substr(minutes.length -2) + ":" + seconds.substr(seconds.length -2));
            }
        );
    }
);
