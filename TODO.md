# TO DO

## Aliasing

This is supposed to be a minifier and obfuscator in one. But due to project with higher priority the obfuscator functionality won't be finished and this library will be only joining and minifing files. There is about 50% work done on obfuscator but this is still not usable.

# Fixes

## Fix chain, import
## Extend classes:
    - interface
    - extends
    - implements
    - protected
    - public
    - private
    - static

## Add try catch finally
## Add the rest of taken keywords
## Add to variables Deconstructed assignment - https://medium.com/swlh/javascript-best-practices-renaming-imports-and-proper-variable-declaration-aa405c191bee
## If CallerBlock try to get caller name
## Add all key words so when object has property "typeof" it won't get replaced with its alias
## Upgrade fixScript as it doesn't reamove all not needed space example: do{ whileLooped--; console.log(whileLooped);}

# Improvments

## If script will be slow look into setting all values for functions in one iteration (instead of ~2,5)

## Setting to allow changing anonymous functions `function () {}` into array functions `() => {}`

## Next stage - check for any useless statements
- not resolved methemtical equasions - 2 +2 (or more realisticly 60\*60\*24)
- variables only containing strings and number - 'asd' + 'vxc' + 2 (but look out for those - 'asd' + (2+3))
- if `IfBlock` and with `return` but the next one is else then remove else completely
  - maybe if if ends with empty return it might be better to remove `return;` and extend if with `else{}`? this might be less symbols in the end (7 to 6 (or 4 if its single instruction))
- remove not used variables (scope required)

## Handle
- [DONE] import, export
- [DONE] ?: conditions
- [DONE] array (used like object)
- [DONE] notes
- [DONE] chain linking with square brackets (if bracet is prefixed with `:` or `=` then its array)
- [DONE] Object,
- [DONE] if (removing brackets if it contains only one instruction),
- [DONE] for (replace vars in brackets),
- [DONE] while and do while,
- [DONE] Switch
- [DONE] passing anonymous functions,

# Known bugs

1. Arrow Method inside parenthesis:
(x => x + 1)

2. When someone uses double spaces as in a value name:
let obj = {};
obj['a  b'] = 'v';
This: obj['a  b'] will become this obj['a b'] in process which might cause some problems with script.

# Problems:

## 0. If
```js
let obj = {
    'asd' :2,
    33 : 'sad'
};

function fuc() {
    return {
        'asd' : 123
    };
}
```

## Accessing object with generated names

My alias replacer for globally scoped vars will not work for anything that accessed them dynamically, example:
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
