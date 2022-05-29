export let name1, name2, nameN;
export let name1 = '1', name2 = 'v', nameN;
export function functionName(){}
export class ClassName {}

export { name1, name2, nameN };

export { variable1 as name1, variable2 as name2, nameN };

export const { name1, name2: bar } = o;
export const [ name1, name2 ] = array;

export default expression;
export default function () {  }
export default function name1() {}
export { name1 as default, name2};

export * from 'path';
export * as name1 from 'path';
export { name1, name2, nameN } from 'path';
export { import1 as name1, import2 as name2, nameN } from 'path';
export { default, name2 } from 'path';
