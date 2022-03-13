# TO DO

## Create proper stucture
I want to have structure which informs about scope of variables, what type is it (function, object, array), name so we can rename it etc.
Draft:
```
[
    type: 'function',
    subtype:'normal',
    instruction: 'function test() {',
    scope: 'global',
    name: 'test',
    contents: [
        [
            type:'variable',
            subtype:'const',
            instruction:'const test1 = "test1";'
        ]
    ]
]
```
