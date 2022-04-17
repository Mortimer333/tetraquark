# TO DO

## Create proper structure
I want to have structure which informs about scope of variables, what type is it (function, object, array), name so we can rename it etc.
Draft:
```
[
    type: 'function',
    subtype:'normal',
    instruction: 'function test() {',
    scope: 'global',
    name: 'test',
    blocks: [
        [
            type:'variable',
            subtype:'const',
            instruction:'const test1 = "test1";'
        ]
    ]
]
```

Script is going and encouters `function`. Its new block - so create new TetraquarkBlock and get type from TetraqaurkType. Pass remaining content to Block and script goes... inside the block!
So I should start by creating new Block of type Script and it would go inside him! Then I can recursivly just call new Block and attach him to his parent. Content would be saved in `leftcontent` of block and passed to parent after `end` method would be called.

## Shared scope - how to do it?

So I want to have somewhat shared scope of variables:
function `func1` has `var1` and `var2` inside but also `func2` which has `var3` and `var4`. So here is how scopes should look like:
- `func2` should be able to access `var1` to `var4`
- `func1` should be able to access only `var1`, `var2` and `func2`
And if I was to add another function it should have access to all vars and their own. So there should be... passed down variables. Pretty simple, but creating aliases will require new iteration over whole script. They might be used not in order and us changing their names in one place might break use in another.

## Next stage - check for any useless statements

## If script will be slow look into setting all values for functions in one iteration (instead of ~2,5)

## Setting to allow changing anonymous functions `function () {}` into array functions `() => {}`

## Handle
- notes
- import, export
- [DONE] chain linking with square brackets (if bracet is prefixed with `:` or `=` then its array)
- [DONE] Object,
- [DONE] if (removing brackets if it contains only one instruction),
- [DONE] for (replace vars in brackets),
- [DONE] while and do while,
- [DONE] Switch
- passing anonymous functions,
- array (used like object)

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

After minifization:

class a{
    b(d,e){ // missing default value for parameter
        // Missing let timer;
        let c=>{ // It probably mixed `timer` and `return (...args)` into one `ArrowFunctionBlock`
            clearTimeout(g) // missing semicolon
            if(args[0]==="clear"){return;}
            // This is just trash - anonymous function has been merged into setTimeoutCall,
            // args didn't get minified, same with timeout even though it got alias `e` in function,
            g=setTimeout()=>{ d.apply(this,args); };, timeout);
        };
    }
}
export{a}; // This guy got changed into SoloObjectBlock but this will be fixed when I will add export/import blocks

class a{
    b(d,e){
        let c;
        return...argsg=>{
            clearTimeout(c)
            if(args[0]==="clear"){return;}
            c=setTimeout()=>{d.apply(this,args);};,timeout);
        };
    }
}
export{a};


Before mini:

class TabJF_Hidden {
  debounce (func, timeout = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      if ( args[0] === "clear" ) {
        return; // if passed `clear` then stop debouncing
      }

      timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
  }
}
export { TabJF_Hidden };
