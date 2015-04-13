/**
 * Author: LyonWong
 * Date: 2014-09-09
 */

var config = config || {};
config.getVersion = function () {
    if (!config.version || config.environment == 'DEV') {
        return (new Date).getTime();
    } else {
        return config.version;
    }
};

var resource = {
    makeURL: function(URI) {
        var splitor = (URI.indexOf('?') == -1) ? '?' : '&';
        var suffix = splitor+'v='+config.getVersion();
        return config.resourcePath+URI+suffix;
    }
};
