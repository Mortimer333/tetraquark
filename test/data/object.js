let nem = {
    objSoloTest,
    obj: 'boj',
}

let tem = {
    'as' : 'asd',
    'kek@' : [
        1, 2,3
    ],
    `as${meh}` : 2
}
return {
    a: 'asd',
    meh : 2
};
let set = group.sets[word[0]] || group.sets[word] || group.sets['default'] || { attrs : { class : 'mistake' } };
let oldOne = { 'subset' : { 'sets' : { [group.start] : group } } };

lineContent.push({
    attrs   : 123,
    content : ''
});

let obj = {
  192 : ( e, type ) => {
    if ( this.pressed.shift ) this.insert('~');
    else                      this.insert('`');
  },
  default : ( e, type ) => {
    throw new Error('Unknow special key', e.keyCode);
  }
};

let a = 33;

const prevent = {
  33 : true,
  34 : true,
  35 : true,
  36 : true,
  37 : true,
  38 : true,
  39 : true,
  40 : true,
  222 : true
};

console.log(prevent[33]); // wont work because 33 will be changed to `c` alias when obfusticating

// Possible solution:
// - check if this object is used with bracket chain and if so don't change its aliases
//  - we could also check if it contains only specific usage if so then don't change only this one but this seems very complicated
//  - this solution will also help with `Accessing object with generated names` problem

// What about nested:

const nested = {
  33 : {
    asd: {
        'cv' : 12
    }
  },
};

console.log(prevent[33].asd.cv)

// I would like to keep 33 but alias the rest... But how do I know? I have to create scope then but this will be extremly difficult



asd.fdg['asd' + asd].cxsds().asd
dasd.fdg.cxsds()

let a = onj.as.vc + 212;

class ClassName {
    while() {

    }
}


tem['as' + meh]
tem[`as${meh}`].ga
