/**
 * Author: LyonWong
 * Date: 2014-09-24
 */

var debug = {
    'autoRefresh': function (urls) {
        var loadTime = (new Date).toUTCString();
        setInterval(function(){
            $.each(urls, function(i,url) {
                $.ajax(
                    url,
                    {
                        headers: {
                            'If-Modified-Since': loadTime
                        },
                        statusCode: {
                            200: function(){
                                location.reload();
                            }
                        }
                    }
                )
            })
        }, 1000);
    }
};