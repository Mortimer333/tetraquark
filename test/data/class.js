class ClassName {
    constructor() {
        console.log('new');
    }

    func1 () {

    }

    funcr2() {}

    meth2
    ()
    {
        console.log('asdas');
    }

    attrb = 'asd' + 'asd'
    att = [
        'asd'
    ]

    obj = {
        'as' : 'asd',
        'kek@' : [
            1, 2,3
        ]
    }
}

const objSoloTest = [ 'asd', 3, 2 + 1]
let obj = {
    objSoloTest
}

const classTest = new ClassName();
const classTest2 = new ClassName()
const classTes3 = new ClassName
()
const classTest4 = new ClassName;
const classTest6 = new ClassName
const classTest5 = new
ClassName

if (obj.objSoloTest[0] == 'asd') {
    console.log('found')
    console.log('found2')
}

for (var to = 0; to < objSoloTest.length; to++) {
    console.log(objSoloTest[to]);
}

while (objSoloTest.length) {
    let size = objSoloTest.length;
    console.log(size, objSoloTest[0]);
    objSoloTest.shift();
}

switch (objSoloTest.length) {
    case 1:
        console.log('Obj is one')
        break;
    default:
        console.log('Obj is bigger then one')
}
