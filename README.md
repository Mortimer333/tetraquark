# Tetraquark
Tetraquark allows you to analyze code with your custom schema in Trie fashion.

# Overview
The main purpose of this library is to provide basic functionality to help with code analysis. By code analysing I mean turning lines of code into objects that other scripts/libraries can understand.
It helps with tasks such as:
- minifying
- beautifying
- sniffing
- mapping
- etc.

# Real life example
Let's imagine that you were tasked to write beautifier for project but the language you are writing in doesn't have any available beautifier that follows rules set for your project. Now instead of wasting time on writing basic functionality to analyze code and transform it into something other scripts can understand you might want to pick up this library. It will provide you with basic functionallity for code analysing speeding up the process. Or, if the language in which your project is written doesn't have analyser you might use it core functionality to write it by yourself, still saving a lot of time and effort.

It resolves issues like:
- UTF-8 multibyte characters - thanks to `Content` library retrieving letters and string manipulation is quite easy, quick and will never return trash characters
- Movement on text - `Tetraquark\Str` class is already packed with method to help you move around code and find what you need
- Instructions Trie - prefix map done with UX in mind (check it out in `Write your own analyser` section)
- Flexibility - defining instruction and creating additional logic is as easy as creating new function. No additional handlers or resolvers - just find letter and set caret position.

# How to use
```php
use Tetraquark\Reader;                                    // Main component of the library
use Tetraquark\Analyzer\JavaScriptAnalyzerAbstract;       // Analysier class, provides instructions and settings on how to interpret code

$reader = new Reader(JavaScriptAnalyzerAbstract::class);  // Inialize new Reader with chosen analyser

$analysis = $reader->read(' let foo = "bar";');            // You can pass script in text
$analysis = $reader->read(__DIR__ . '/foo.js', true);     // Or pass it by path
```
Analysis (simplified):
```js
[
    {
        "blockStart": 5,
        "blockEnd": 16,
        "start": 0,
        "end": 16,
        "landmark": {
            "class": "LetVariableBlock"
        },
        "children": [
            {
                "blockStart": 10,
                "blockEnd": 16,
                "start": 4,
                "end": 16,
                "landmark": {
                    "class": "VariableInstanceBlock"
                },
                "data": {
                    "name": "foo",
                },
                "children": [
                    {
                        "start": 10,
                        "end": 15,
                        "landmark": {
                            "class": "StringBlock"
                        },
                        "data": {
                            "string": "bar"
                        }
                    }
                ]
            }
        ]
    }
]
```
Based on which analyser you use, your script will be categorized in blocks readable by other script and allowing you to automate any code related flow.
Currently, available analysers:
- JavaScript - `Tetraquark\Analyzer\JavaScriptAnalyzerAbstract`

You can also write your own analysers:

# Write your own analyser
Firstly your analyser class must implement `Tetraquark\Contract\AnalyzerInterface` and include those methods:
- `getSchema` - this method must return the schema/settings of your analyser
- `getName` - should return unique name of your analyser. It will be used as key for your compiled instructions, so in case of creation of new instance of `Reader` we can use cached map instead of generating it again.

```php
class SimpleAnalyser implements \Tetraquark\Contract\AnalyzerInterface
{
    [...]
}

```

## Recommended flow
It's recommended to extend `Tetraquark\Analyzer\BaseAnalyzerAbstract` class as it already implements said interface and provides simplified solution for creating settings.

By default, it doesn't require you to provide any settings at all and is ready from the start.
After extending it you may choose which methods you want to replace and which leave to their default values:
- `getName`
- `getRemoveAdditional`
- `getRemoveComments`
- `getCommentsMap`
- `getPrepareMissed`
- `getPrepareContent`
- `getSharedEnds`
- `getInstruction`
- `getMethods`

More on each setting in the next paragraph.
**Each method requires from you to allow passing array into first parameter:**
```php
public static function getInstruction(array $settings = []): array
{
    return [];
}
```

## Settings

Settings is the core of your analyser. It describes its behaviour and processes, its up to you to make it quick and precise. Example of settings with default values:
```php
    [
        "comments" => [],
        "prepare" => [
            "content" => null,
            "missed" => null
        ],
        "shared" => [
            "ends" => []
        ],
        "remove" => [
            "comments" => false,
            "additional" => false,
        ],
        "instructions" => [],
        // Those three methods will always be available unless overwritten
        "methods" => [
            "s" => \Closure,
            "find" => \Closure,
            "read" => \Closure,
        ],
    ];
```

### `comments`
This part holds the "[Trie](https://en.wikipedia.org/wiki/Trie) map" describing the comments. Each node ends with string contains the end of found comment:
```php
    "comments" =>  [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
```
This indicated that anything that looks like this `//` or `/*` is a comment and `//` ends when encountered `\n` (new line) and `/*` ends with `*/`. This will allow our analyser to identify comments and remove them/assign to block (depending on the other settings).

### `prepare.content`
Holds function that will change content before it is analysed:
```php
    public static function getPrepareContent(array $settings = []): \Closure
    {
        return fn(Content $content) => $content->prependArrayContent([' ']);
    }
```
Method above adds additional whitespace at the start of the script (just as an example).
Script is passed in the form of `Content\Utf8` instance about which you can read more [here](https://github.com/Mortimer333/Content) (to create your own analyser it's very crucial to know how `Content` works, but worry not it's pretty straight forward).
This setting is very helpful if you want to trim your script before the analysis or replace some part of it.

But be aware that there is other very similar setting (`remove.additional`) which allows you to iterate over your whole script and remove any part of it that you don't need. Think of this one (`prepare.content`) as place where you can remove all of the known problems that doesn't require identifing.

### `prepare.missed`
Holds function that prepares `missed` part of the script (missed in sense that no instruction described it and couldn't be catalogued). It is called on runtime (during script analysis) to check if found "missed" part of the script should be saved (for example space or new line can qualify as missed if you didn't describe them in instructions) If the returned value is empty `missed` content will be skipped and not added to the analysis:
```php
    public static function getPrepareMissed(array $settings = []): \Closure
    {
        // ` ; ` - empty, nothing was missed
        // ` a; ` - `a`, `a` was missed (and only `a` will be saved)
        return fn(string $missed) => trim(trim(trim($missed), ';'));
    }
```
Created function must accept `string` as its first argument.

### `shared.ends`
Some of your instructions might start and end on the same letter. To accommodate it script allows passing which letters should be taken into consideration twice if they appear on the end of instruction. For example `let foo = 'bar';let bar = 'foo'` the semicolon here is actually part of the two `let` instructions.
Symbols defining shared ends have to be set a keys of the array with value set to true (performance).

```php
    public static function getSharedEnds(array $settings = []): array
    {
        return [
            "\n" => true,
            ";" => true,
            "}" => true,
            "," => true,
            ")" => true,
        ];
    }
```

### `remove.comments`
Contains `bool` value and decides if comments should be present when analysing script or not. The absence of comments might impact the `start` and `end` values of the instructions in an inconvenient way for analysiers that try to be as precise as they can be. But if you only care about the structure and not placement of the code, you can rest easy and set this option to true (remember to define `comments` part of the settings otherwise nothing will get removed/analysed).
By default, this option is set to `false` and comments can be found in `comments` part of the block.
```php
    public static function getRemoveComments(array $settings = []): bool
    {
        return false;
    }
```
### `remove.additional`
Contains `bool|\Closure` value, allows removing not needed parts of the script before the analysis. If you care about speed in which tool analyzes the script you might want to remove additional spaces and obstacles which require heavier operations to identify properly.
```php
    public static function getRemoveAdditional(array $settings = []): \Closure|bool
    {
        return function (int &$i, Content\Utf8 $content, string $letter, string $nextLetter, array $schema): void {
            // Operations
        };
    }
```
The method is fired on each letter of the script but be aware that if `remove.comments` is set to true - found comments will be removed before this method is called.
It's called in `for` on the end of each iteration, so you can remove part of the script, set `$i` to proper value and wait for another letter. Think of it as a way to help yourself with clean _table and sheet_ before actual work.

#### Attributes
You are provided with following values that allow you to identify not needed parts of your script:
- `int $i` - position of caret
- `Content\Utf8 $content` - the actual script
- `string $letter` - current letter
- `string $nextLetter` - next letter
- `array $schema` - settings from your analyser

### `instructions`
Most important part of the settings - the actual definition of syntax. This value will hold all instructions for `Reader` to actually catalogue code into blocks which other scripts will understand. Just like with `comments` it uses Trie map but in more _"comfortable"_ way:
```php
    public static function getInstruction(array $settings = []): array
    {
        return [
            'break' => [
                "custom" => "stuff",
                "type" => "keyword",
                "class" => "BreakBlock",
            ]
        ];
    }
```
This map tells `Reader` - if encounters string of characters equal `break`, mark them and saved into `block` with those values: `custom: "stuff", type: "keyword", class: "BreakBlock"`.
For example:
```js
for (;;) {
    break;
}
```
Settings are empty and only contain `instructions` from `getInstruction` method.
Here is what `Reader` will output for us (representation simplified):
```
[
    {
        "_missed": true,
        "data": {
            "missed": "for (;;) {\n    "
        }
    },
    {
        "landmark": {
            "custom": "stuff",
            "type": "keyword",
            "class": "BreakBlock"
        }
    },
    {
        "_missed": true,
        "data": {
            "missed": ";\n}"
        }
    }
]
```
Not mapped parts of the file are saved into `missed` blocks that can be identified by their `_missed` values. But we can see that analysis didn't contain only missed parts: the only instruction that we gave him was found and saved in the next item (`BreakBlock`).

I hope that you already can see that this might actually be quite powerful tool.
Before I will show you helper methods and other syntaxes let's move to the last setting.

### `methods`
Array of functions which can be used in `instructions` to identify not obvious part of the code. The script provides four methods by default:
- `e` - whitespace checker
- `s` - whitespace checker
- `find` - finds passed strings
- `read` - converts string into analysed blocks

All of them are little more advanced than it seems, but I will describe them in details a little later.
As it was said, methods can be used to catalogue more demanding syntaxes (method is depth are described after this example):
Script:
```js
    for (;;) {
        break;
    }
```
Instruction:
```php
    [
        'for/s|e\(/find:")":"(":"condition">read:"condition"\\' => [
            "class" => "ForBlock"
        ],
        'break' => [
            "class" => "BreakBlock",
        ],
    ];
```
Analysis:
```
[
    {
        "landmark": {
            "class": "ForBlock"
        },
        "data": {
            "condition": [
                {
                    "_missed": true,
                    "data": {
                        "missed": ";;"
                    }
                }
            ]
        }
    },
    {
        "_missed": true,
        "data": {
            "missed": "{\n    "
        }
    },
    {
        "landmark": {
            "custom": "stuff",
            "type": "keyword",
            "class": "BreakBlock"
        }
    },
    {
        "_missed": true,
        "data": {
            "missed": ";\n}"
        }
    }
]
```
As we can see now we have two instructions and both were found in this example. One is symple text but other is more advanced as it uses methods to help yourself with more complex syntaxes.
Break down:
- `for/s|e\(` - `s` method checks if letter is whitespace and `e` method makes it optional, so whitespace might be there or not. This means that `for(` is valid, `for (` is valid or even `for \n\t (` is valid syntax.
- `/find:")":"(":"condition">read:"condition"\` - this tells script to search for `)` letter and if encounters `(` to skip another hit (which in this case would be when `)` is found). Find is able to save skipped content into variable in `data` value if we provide name for it (in our case it's `condition`) and thanks to this feature we can later analyse skipped part with `read` methods which we queued with `>` symbol. We also had to tell `read` what value in `data` it should analyse by passing to it `condition`

With this example in mind let's talk more in depth:

#### OR symbol
`Reader` uses pipe symbol `|` as OR indicator. So script understands that either any of those methods are fine (are valid):
`/s|e\` (so there can be whitespace or not). OR is pretty useful when combined with the next option.
  |
#### Literals
If needed be, multiple letters can be set as valid in the same place with OR symbol: `function/"*"|"(">isanonymous\{`.
 Syntax like that tells `Reader` that if he encounters an asterisk `*` or left parenthesis `(` he can continue with this path and check if the rest of the syntax is there. Notice that I've queued something which will later allow me to identify which letter was actually found.

##### Negation
You can also set negation on letter: `=/"!="\`.
This means that `Reader` will only proceed if the next letter after equal sign `=` is not another equal sign (`!=`).

#### Queue
You can queue some actions after the first one was successful: `=/"!=">decrease\`.
Here we are calling the custom decrease method (which decreases the pointer by one) after the current letter was not an equal sign, so we don't lose any symbols and can smoothly proceed with further identification.

#### Parameters
If your method allow passing additional parameters you can provide them by prefixing them with colon `:`: `/find:")"::"name"\`.
You could potentially pass attributes without closing them in string indicators, but script understands that anything between them is not a valid symbol which will then prevent `Reader` from unintentional actions.
You can also notice that I placed two colons next to each other. That means that I want to skip this parameter and `Reader` will put `null` in its place.

#### Default methods

##### `e`
As I said we have four methods but default `methods` setting have only three of them. That's because one of them `e` is not actually a method, its a special sign for the "compiler" the all methods in the same scope can be skipped. For example:
```php
    [
        "/s|e\function/"*"|e\/s|e\(" => [
            "anonymous" => true
        ]
    ]
```
After the compile, script will transform to something similar like this:
```php
[
    "/s\function/"*"\/s\(" => [
        "anonymous" => true
    ],
    "/s\function/"*"\(" => [
        "anonymous" => true
    ],
    "/s\function(" => [
        "anonymous" => true
    ],
    "function/"*"\/s\(" => [
        "anonymous" => true
    ],
    "function/"*"\(" => [
        "anonymous" => true
    ],
    "/s\function/s\(" => [
        "anonymous" => true
    ],
    "function(" => [
        "anonymous" => true
    ],
]
```
Thanks to the `e` - `empty` method we can easily tell script that some values are optional.

#### `s`

This method checks whitespaces and moves pointer to the last adjusted whitespace it encounters. So in this example:
```js
let foo = function()
  {

  }
```
Event though bracket `{` is three whitespaces away form parenthesis `)` single `/s\` will be enough to match all three of them and move to another letter.

#### `find`

Find is the most complicated method from all four. It allows you to find specific set of strings or single string while skipping any false matches. Example:
```php
    '[/find:"]":"["\\' => [
        "class" => "ArrayBlock"
    ]
```
This simple exmaple allows us to find the end of an array even though he might have arrays inside of him:
```js
[
    [1,2,3],
    'a'
]
```
But if you want to make use of all functionality of `find` you will have to create custom method for it as inline syntax doesn't support passing arrays into parameters:
```php
    "methods" => [
        "findanyparenthesisend" =>  function (CustomMethodEssentialsModel $essentials): void
        {
            $essentials->getMethods()['find']($essentials, ["]", "}", ")"], ["[", "(", "{"], "values_between_parenthesis");
        }
    ]
```

`find` accepts multiple ends, multiple skips and landmark of different length (`break` is valid end as well as `]`). It's quite useful when you need to find end of some block and it can save skipped part of the code into `data` if you provide name for it in third argument (`values_between_parenthesis`).

#### `read`

As name suggest this is an inline method for analysing skipped code (for example from `find` method). It required to pass where code to analyse is stored (it always searches inside `data` value) and as optional third argument it lets you name under what name it should be stored (if third qrgument is not passed it will save analysis under key from which it got code to analyse):
```php
    '[/find:"]":"[":"insidearray">read:"insidearray":"araychildren"\\' => [
        "class" => "ArrayBlock"
    ]
```

#### Custom method

Custom methods are a way to bring more logic to the analyser when its needed. If you have a syntax that can't be described in words or is flexible (like spaces between keywords), you can create methods for it. The custom method must:
- be named only using letter in lowercase
- its first parameter have to accept `Tetraquark\Model\CustomMethodEssentialsModel`
- have to return `bool` if is used as validator

Example:
```php
    "methods" => [
        "isprivate" =>  function (CustomMethodEssentialsModel $essentials): void
        {
            $essentials->appendData(true, 'private');
        },
        "isnewline" => function (CustomMethodEssentialsModel $essentials): bool
        {
            return $essentials->getLetter() === "\n" || $essentials->getLetter() === "\r";
        }
    ]
```
Methods that are checking syntax must return `bool` (`isnewline`) and methods which only mark `landmark` shouldn't return anything (`isprivate`).

##### CustomMethodEssentialsModel
This model consists of (`current` is an important keyword which we will better understand when we come to blocks):
- `Content\Utf8 content` - current content
- `int lmStart` - at what letter current landmark started
- `string letter` - current letter
- `int i` - current pointers position (on current content)
- `array data` - all the additional data which will be added to new block
- `Tetraquark\Model\Block\BlockModel|Tetraquark\Model\Block\ScriptBlockModel parent` - parent of the block
- `Tetraquark\Model\Block\BlockModel|null previous` -  previous sibling of the block
- `array methods` - default and custom methods
- `Reader reader` - the actual `Reader`

All of those values can be accessed without use of any functions (as the model is dynamically generated (partially)) but for convenience each value has its own `getter` and `setter`. Also, array have `append` and `prepend` methods for easier management (so `data` and `methods`):
```php
    $essentials->getData(); // data array
    $essentials->setData(['data']);
    $essentials->appendData(1, 'number'); // ["number" => 1];
    $essentials->appendData("number"); // [0 => "number"];
```
And those values are actually linked and will be updated in the main object:
- `content`
- `letter`
- `i`
- `data`

If you want to give option for additional parameters just define them in method:
```php
"isnewline" => function (CustomMethodEssentialsModel $essentials, bool $checkR = false): bool
{
    return $essentials->getLetter() === "\n" || ($checkR && $essentials->getLetter() === "\r");
}
```
Instruction:
```php
"[/isnewline:1\]" => [
    "class" => "NewLineArray"
]
```
### Advanced syntax
#### `_extend`
If your `instructions` contain a lot of repeating syntax you could try to extend it:
```php
    [
        'function' => [
            "function" => true
            "_extend" => [
                '*' => [
                    "generator" => true
                ],
                '/s|e\[' => [
                    "constant" => true
                ]
            ]
        ]
    ];
```
`Reader` will read this as:
```php
    [
        'function' => [
            "function" => true
        ],
        'function*' => [
            "generator" => true
        ],
        'function/s|e\[' => [
            "constant" => true
        ]
    ];
```
#### `_block`
In many cases you want to set proper `child <=> parent` relation and this can be easily achieved with blocks:
```php
[
    'if/s|e\(/find:")":"(":"condition">read:"condition"\/s|e\{' => [
        "class" => "IfBlock",
        "_block" => [
            "end" => "}",
            "nested" => "{",
        ]
    ],
];
```
This will result in mapping anything between brackets of this if as its children.

##### End and Nested Trie
`end` and `nested` actually work in the same way as `insturctions` but you don't set them in keys but as values of array or just as string.
```php
        "_block" => [
            "end" => ["}", "]", "/end\", "end"],
            "nested" => ["{", "]", "/start\", "start"],
        ]
```
This will make block end on any of matches found in this array and skip the next match if anything from `nested` is found.
###### `include_end`
If your syntaxes end should be treated as a block (ends are skipped by default) you can indicate it by setting `include_end` value:
```php
        "_block" => [
            "end" => "break",
            "include_end" => true,
        ]
```
Now `break` even though ended the block will be analysed and marked as `missed` or as `landmark`.
