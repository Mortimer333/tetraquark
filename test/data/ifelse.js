/*if (obj.objSoloTest[0] == 'asd') {
    console.log('found')
    console.log('found2')
}
if (a === b)
    console.log('found3')

    if (a === b) console.log('found4'); if (a === b)
        console.log('found5')

if (true) {
    // test1
} else {
    // test
}

if (false) {

} else if (true) {

}

if (true) console.log('as')
else console.log('vc')

if (false) console.log('as')
else if(false)console.log('vc')
else if ('asd')
    console.log('cxasd')

if ( char !== -1 ) {
    return char;
}*/

if ( first  && line?.nodeName != "P" ) throw new Error("Parent has wrong tag, can't find proper lines");
if ( !first && line?.nodeName == "P" ) return line;
