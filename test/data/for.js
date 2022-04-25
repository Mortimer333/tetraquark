let objSoloTest = ['a'];
let start = 1;
let reverse = false;
let value = 'asd';
let modifier = 1;
function strlen(str) {
    return str.length;
}

for (var to = 0; to < objSoloTest.length; to++) {
    console.log(objSoloTest[to]);
}
for (let i=start; (!reverse && i < strlen(value)) || (reverse && i >= 0); i += modifier) {
    console.log('test')
}
