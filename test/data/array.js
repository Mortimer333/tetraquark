const var1 = 'as';
function caller(arg1) {
    console.log(arg1);
    return arg1;
}
['a',var1]
[function () { return 'a' }, 'a']
[
    'c',
    () => {
        console.log('a')
    },
    caller('a'),
]
[
    [
        [
            'a'
        ],
        'b',
        'v'
    ]
]
