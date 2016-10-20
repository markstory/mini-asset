/*!
 this comment will stay
*/
//= require "local_script"
// this comment should be removed
function test(thing) {
    /* this comment will be removed */
    // I'm gone
    thing.doStuff(); //I get to stay
    return thing;
}
