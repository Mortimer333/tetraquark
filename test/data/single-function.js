function test() { var longName = 20;
    console.log( longName);
    const testa = (arg1) => {
        const   testScoped = function (adas, test5   ,  test4,
        fgdfg) {

        }
    };
    const test2 = e => 1 + 2;
    const test3 = (e2) =>
    1 + e2; let longNameLet = `asd ${testInline} asdasd`
    console.log(test3('s'));
    var testInline = 20; let testInline2 = {'a' : 'sd'}
    const testAddAliases = longNameLet + testInline
}

testa='b'

e3=> {

}

class ClassName {
    property = 'y';
    constructor(arg1, arg2) {

    }

    func() {
        console.log(this['property']);
        console.log(this['propert' + this.property]);
        console.log(this[`prop${this.property}`]);
        console.log(this[`property`]);
        console.log(this["property"]);
        console.log(this["property" + this.fun2('a')]);
        console.log(this[this.fun2('a') + 'propert']);
    }

    fun2 (arg1) {
        return arg1;
    }
}

const classNameInst = new ClassName('test', 2);
classNameInst.func();
