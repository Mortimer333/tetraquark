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
