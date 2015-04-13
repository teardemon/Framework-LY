/**
 * Author: LyonWong
 * Date: 2014-09-24
 */

(function web() {
    var web = angular.module('web', ['core', 'widget']);

    web.directive('zhiling', function () {
        return {
            restrict: 'E',
            link: function (scope, elem, attr) {
                console.log(attr['src']);
            }
        }
    })
})();