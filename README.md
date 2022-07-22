# tetraquark
PHP Library for minifying javascript

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

php ./vendor/bin/phpunit test
php test/test.php > test/test.log 2>&1
