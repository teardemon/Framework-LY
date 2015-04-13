/**
 * Author: LyonWong
 * Date: 2014-09-22
 */
//var app = angular.module('myApp', []);
//
//app.controller('appCtrl', function($scope) {
//    $scope.foo = 'foo';
//});
var appCtrl = function($scope) {
    $scope.foo = 'foo';
};

describe("controller test", function(){
    it ("foo test", function(){
        var scopeMock = {};
        var cntl = new appCtrl(scopeMock);
        expect(scopeMock.foo).toBe("foo");
    })
});


