/*let nem = {
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
tem['as' . meh]
tem[`as${meh}`].ga
return {
    a: 'asd',
    meh : 2
};
let set = group.sets[word[0]] || group.sets[word] || group.sets['default'] || { attrs : { class : 'mistake' } };
let oldOne = { 'subset' : { 'sets' : { [group.start] : group } } };

lineContent.push({
    attrs   : 123,
    content : ''
});*/

let obj = {
  192 : ( e, type ) => {
    if ( this.pressed.shift ) this.insert('~');
    else                      this.insert('`');
  },
  default : ( e, type ) => {
    throw new Error('Unknow special key', e.keyCode);
  }
};
