# Tetraquark
PHP <del>Library</del>Tool for <del>minifying javascript</del>mapping out scripts

# Draft
1. You can point main file and minifier will minify it and try to make it a single file and smallest possible. Might want to get functions or whole files from imports to make this independed file.
2. It will iterate over code and try to:
  - Replace all variables with single letters (if letters are all used start again but with two for example: last variable was `z` next will be `aa`)
  - Try to replace methods with single letters (same rule as above)
  - Remove all whitespace added for readability and add semicolomns instead of end line char (if there isn't already semicolomn present)
  - Replace `if` statments with only one line in them with braceless - if (true) { console.log('One liner'); } if (true) console.log('One liner');
    - Check if it won't be better to try and replace them with `?:` or `??`
  - Create its own variables for strings used multiple times or big numbers
  - Create variables for same calls of the function (for example `Object.keys` saved into `as` and would be called like this: `as(object)`)
3. You can:
  - Indicate if script should change the name of upper functions and global attrbiutes:
    - Which means if class `Foo` has attrbitue `name` (`Foo.name`) it won't get changed into `a`, same goes for method `getName` (`Foo.getName`) but all variables inside `getName` will be changed into letters. TL;TR; All functions and attrbiutes available for usage won't get changed, anything scoped inside function (which can't be accessed) will be.
  - Designate some methods and attributes for minifying or designate some methods and attributes which can't be minified (one or anothe; can't use both)
  - Choose to not include (copy the content) of its imports
  - Choose to not include one or more of imports or choose to include one or more of imports (try to allow for choosing specific functions, like copy all other stuff into this file but leave this method call like it was (so copy method `render.content()` into `ab()` but leave `render.clear()` without changes))

PHPunit

php ./vendor/bin/phpunit tests
XDEBUG_MODE=coverage php ./vendor/bin/phpunit tests --coverage-text XDEBUG_MODE=coverage
php test/test.php > test/test.log 2>&1

Plan:
0. [DONE] Merge it to master and create new branch
1. [DONE] Save somewhere how I figured out Import and Export (ImportBlock and ExportBlock - `let Ī = {};Ī.y = Ī => {};`) - we are moving JS handle
   to `schemats/` as part of transforming this to being a tool not dedicated solution.
2. [DONE] Clean old code:
   - Block
   - Trait
   - Fundation
   - Few root classes I don't use
3. [DONE] Move Log class to seperate project and explain the future of it with Attributes
4. [DONE] Maybe move Content class out (too) as it is surprisingly useful
5. [DONE] Recreate Validate as something used for JS sytax schema `/schemats/javascript` and copy used methods by Reader to it or in Str (probably in Str)
   - Quick test it
6. [DONE] Create unit tests (`test/Unit`) for each public method (probably all of them) for:
   - [DONE] Str
   - [DONE] Content (if not moved)
   - [DONE] Reader
   - [DONE] *Model
7. Create integration tests - one for each syntax (probably generate map and use it as reference)
   - each test should have seperate file with test data
   - each test have to check:
     - with formal syntax - `const a = 'a';`
     - with lazy syntax - `const a='a'`
     - with weird syntax - `const a\n\t=\n\t\t'a'`
   - all of them should be inside tests/Integration folder
8. Figure out better schema handling and extending (as current setup have to be upgraded a lot)

TODO:
1. [DONE] Some problem with missed in caller block -word.funcion = 12 + func(1 , 23);
2. [DONE] Add method which will create children for item (varend>read/objectify)
3. [DONE] https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Nullish_coalescing_operator - SymbolBlock
4. [DONE] https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Optional_chaining
5. [DONE] Object with array acces (object['asd'])
6. [DONE] yeld* - https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/yield*
7. [DONE] class - extends
8. [DONE] private class attrbitues and method - https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes/Private_class_fields
9. [DONE] https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring_assignment
10. [DONE] Object name in brackets: `{[key] : 'as'}`
11. [DONE] Skip comments between definition: `let a = /* wel whooops */ 2;`
12. [DONE] Figure out how to analize this: `${this.getDate()}-${months[this.getMonth()]}-${this.getFullYear()}`
13. [DONE] Add NumberBlock
14. [DONE] For in
15. [DONE] For of
16. [DONE] This weird shit - At some point. https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Iteration_protocols:
17. [DONE] Is it possible to have multiple call ont he same method - `caller(1)(2)(3)`

List of blocks which I might not implement:
~ SemicolonBlock - not needed
~ UndefinedBlock - automatic missed data
~ ScriptBlock - its implemented automatically

List of implemented blocks:
+ Import:
    + ImportPromiseBlock
    + ImportObjectBlock
    + ImportObjectItemBlock
    + ImportAsBlock
    + ImportAllBlock
    + ImportFromBlock
    + ImportItemSeperatorBlock
    + ImportBlock

+ Export:
    + ExportDefaultBlock
    + ExportAsBlock
    + ExportAllBlock
    + ExportFromBlock
    + ExportObjectItemBlock
    + ExportObjectBlock

+ VariableItemBlock - VariableInstanceBlock
+ WhileBlock
+ DebuggerBlock - KeywordBlock:debugger
+ ObjectSoloValueBlock - VariableInstanceBlock (parent ObjectBlock)
+ UndefinedValueBlock - KeywordBlock:undefined
+ NullBlock - KeywordBlock:null
+ InfinityBlock - KeywordBlock:Inifinity
+ OperatorBlock - VariableInstanceBlock:addition|subtraction
+ StringBlock
+ NewClassBlock - NewClassInstanceBlock
+ VoidBlock - KeywordBlock:void
+ ObjectValueBlock - ObjectValueBlock
+ ObjectBlock
+ SwitchCaseBlock - SwitchCaseBlock
+ TripleEqualBlock - ExactBlock
+ DoWhileBlock
+ CatchBlock
+ YeldBlock - YieldBlock
+ AttributeBlock - VariableInstanceBlock
+ StaticInitializationBlock
+ InstanceofBlock - KeywordBlock:instanceof
+ FinallyBlock
+ SpreadBlock - ObjectBlock:spread, VariableInstanceBlock:spread, ArrayBlock:spread
+ DoubleEqualBlock
+ ExportBlock - EqualBlock
+ CallerBlock - CallerBlock
+ FunctionBlock
+ TryBlock
+ SymbolBlock
+ ArrayItemSeperatorBlock - CommaBlock
+/- ScopeBlock - ObjectBlock
+ BreakBlock -  KeywordBlock:break
+ ElseBlock
+ EmptyAttributeBlock - VariableBlock:empty
+ IfBlock
+ FalseBlock
+ VariableBlock
+ TrueBlock
+ ClassBlock
+ TypeofBlock - KeywordBlock:typeof
+ SwitchBlock
+ ForBlock
+ ClassMethodBlock
+ NanBlock - KeywordBlock:NaN
+ ReturnBlock
+ ContinueBlock - KeywordBlock:continue
+ ArrowFunctionBlock - ArrowMethodBlock
+ ChainLinkBlock - ChainBlock
+ ArrayBlock
