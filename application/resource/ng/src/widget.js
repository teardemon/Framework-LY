/**
 * Author: LyonWong
 * Date: 2014-09-26
 */

(function widget() {
    var widget = angular.module('widget', ['core']);
    widget.controller('ctlNavbar', ['$scope', '$http', function ($scope, $http) {
        $http.get(resource.makeURL('data/navlist.json'))
            .success(function (data) {
                $scope.navList = data;
            });
        $scope.navActive = location.pathname;
    }]);

    widget.directive('widgetNavbar', function () {
        return {
            restrict: 'E',
            templateUrl: resource.makeURL('ng/tpl/widget/navbar.html')
        }
    });

    widget.controller('ctlDatepicker', ['$scope', function($scope){
        $scope.format = 'yyyy-MM-dd';
        $scope.isOpen = false;
        $scope.open = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.isOpen = true;
        };

    }]);
    widget.directive('widgetDatepicker', function () {
        return {
            link: function(scope, element, attrs){
            }
        }
    })
})();
