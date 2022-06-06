let a = 'sdsdf';
const b = {
    c : 'd'
}
console.log('asdas');
function testGlobal() {
    console.log('asdas2');
}
class testGlobablClass {
    constructor() {
        console.log('asd');
    }
}
testGlobal();
export { testGlobablClass, testGlobal, b as default};
