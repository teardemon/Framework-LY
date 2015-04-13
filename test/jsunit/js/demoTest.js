/**
 * Author: LyonWong
 * Date: 2014-09-22
 */


function foo(input) {
    return 'foo:'+input;
}

describe("A suite of foo functions", function() {
    it("foo word",function(){
        expect("foo:a").toEqual(foo("a"));
    });
});
