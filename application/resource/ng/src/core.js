/**
 * Created by LyonWong
 */

(function core() {

    var coreService = angular.module('coreService', ['ngResource']);
    coreService
        .factory('config', ['$http', function($http){}]

        )
        .factory('cookie', function () {
        return {
            read: function (key) {
                var c = (('; ' + document.cookie).split('; ' + key + '=')[1] || '') + ';';
                return decodeURIComponent(c.substring(0, c.indexOf(';')));
            },
            write: function (key, value, expire, scope) {
                var cookie = '';

                if (expire) {
                    var dt = new Date();
                    dt.setTime(dt.getTime() + expire);
                    cookie += "; expires=" + dt.toGMTString();
                }
                if (typeof (scope) == 'object') {
                    if (scope.domain) {
                        cookie += "; domain=" + scope.domain;
                    }
                    if (scope.path) {
                        cookie += "; path=" + scope.path;
                    }
                }
                document.cookie = key + "=" + encodeURIComponent(value) + cookie;
            }
        };

    })
    ;

    var coreDirective = angular.module('coreDirective', ['ngSanitize']);
    coreDirective.directive('coreClassHover', function () {
        return {
            link: function (scope, elem, attr) {
                var className = attr['coreClassHover'];
                elem.bind('mouseover', function () {
                    elem.addClass(className);
                });
                elem.bind('mouseleave', function () {
                    elem.removeClass(className);
                })
            }
        }
    });

    var coreFilter = angular.module('coreFilter', []);

    angular.module('coreBase', ['coreService', 'coreDirective', 'coreFilter', 'ui.bootstrap']);

    angular.module('core', ['coreBase', 'ngLocale']);

})();
