# TO DO
## 0. Add all key words so when object has property "typeof" it won't get replaced with its alias
## 1. Upgrade fixScript as it doesn't reamove all not needed space - do{ whileLooped--; console.log(whileLooped);}


## If script will be slow look into setting all values for functions in one iteration (instead of ~2,5)

## Setting to allow changing anonymous functions `function () {}` into array functions `() => {}`

## Next stage - check for any useless statements
- not resolved methemtical equasions - 2 +2 (or more realisticly 60\*60\*24)

## Handle
- [DONE] ?: conditions
- import, export
- array (used like object)
- [DONE] notes
- [DONE] chain linking with square brackets (if bracet is prefixed with `:` or `=` then its array)
- [DONE] Object,
- [DONE] if (removing brackets if it contains only one instruction),
- [DONE] for (replace vars in brackets),
- [DONE] while and do while,
- [DONE] Switch
- [DONE] passing anonymous functions,

# Known bugs

1. instructions like :
let a  = {
    b : 'v'
}
let c = a.
b

are not properly recognized because script thinks that instruction ends on the dot (a.)

2. Arrow Method inside parenthesis:
(x => x + 1)

3. When someone uses double spaces as in a value name:
let obj = {};
obj['a  b'] = 'v';
This: obj['a  b'] will become this obj['a b'] in process which might cause some problems with script.

# Problems:
- my alias replacer for globally scoped vars will not work for anything that accessed them dynamically, example:
```js
class Test1 {
    varOne = 'one';
    varTwo = 'two';
}

const newTest = new Test1();
['One', 'Two'].forEach(suffix => {
    console.log(newTest['var' + suffix]);
});
```
Its going to be translated into something like this:
```js
class a{b='one';c='two';}const d=new a;['One', 'Two'].forEach(e=>{console.log(d['var' + e]);}
``
Which will result in error `varOne doesn't exist` because varOne got replaced with `b`.
