# Tetraquark
Tool for mapping out scripts
# How to use
```php
use Tetraquark;
$reader = new Tetraquark\Reader(Tetraquark\Analyzer\JavaScriptAnalyzerAbstract::class);
// Pass script
$analysis = $reader->read('let foo = "bar";');
// Or pass it by path
$analysis = $reader->read(__DIR__ . '/foo.js', true);
```
Based on which analyser you use, your script will be categorized in blocks readable by other script and allowing you to automate any code related flow (like minifying).
Currently, available analysers:
- JavaScript - `Tetraquark\Analyzer\JavaScriptAnalyzerAbstract`

You can also write your own analysers in quite easy way (that's why it's called a tool).

# Write your own analyser
Firstly your analyser must implement `Tetraquark\Contract\AnalyzerInterface` and must include those methods:
- `getSchema` - this method must return the schema/settings of your analyser
- `getName` - should return unique name of your analyser. It will be used as key for your compiled instructions, so in case of creation of new instance of `Reader` we can use cached map instead of generating it again.

## Recommendation
It's recommended to extend `Tetraquark\Analyzer\BaseAnalyzerAbstract` as it already implements said interface and provides simplified solution for creating settings. By default, it doesn't require you to provide any setting with all of them set to default values (even name defaults to the name of the class `static::class`).
After extending it you may choose which methods you want to replace and which leave to their default:
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
_Taken from `BaseAnalyzerAbstract::getSchema` :_
```php
    [
        "comments" => static::getCommentsMap(),
        "prepare" => [
            "content" => static::getPrepareContent(),
            "missed" => static::getPrepareMissed(),
        ],
        "shared" => [
            "ends" => static::getSharedEnds(),
        ],
        "remove" => [
            "comments" => static::getRemoveComments(),
            "additional" => static::getRemoveAdditional(),
        ],
        "instructions" => static::getInstruction(),
        "methods" => static::getMethods(),
    ];
```
### Settings defaults
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
        // Those two methods will always be available unless overwritten
        "methods" => [
            "s" => \Closure,
            "find" => \Closure,
        ],
    ];
```
Above you can see the array that holds the settings:
### `comments`
This part holds the trie map describing the comments. Each node ends with string contains the end of found comment:
```php
    "comments" =>  [
        "/" => [
            "/" => "\n",
            "*" => "*/"
        ],
    ],
```
This indicated that anything that looks like this `//` or `/*` is a comment and `//` ends when encountered `\n` (new line) and `/*` ends with `*/`. This will allow our analyser to identify comments and remove them/assign to block (depending on the settings).

### `prepare.content`
Holds function that will allow changing content before it is analysed:
```php
    public static function getPrepareContent(array $settings = []): \Closure
    {
        return fn(Content $content) => $content->prependArrayContent([' ']);
    }
```
Method above add additional whitespace at the start of the script.
Script is passed in the form of `Content\Utf8` instance about which you can read more [here](https://github.com/Mortimer333/Content) (to create your own analyser it's very crucial how `Content` works, but worry not it's pretty straight forward).
### `prepare.missed`
Holds function that prepares `missed` part of the script (missed in sense that no instruction described it and couldn't be catalogued). If the returned value is empty `missed` content will be skipped and not added to the analysis:
```php
    public static function getPrepareMissed(array $settings = []): \Closure
    {
        // ` ; ` - empty, nothing was missed
        // ` a; ` - `a`, `a` was missed
        return fn(string $missed) => trim(trim(trim($missed), ';'));
    }
```
Created method must accept `string` as its first argument.
### `shared.ends`
Some of your instructions might start and end on the same letter. To accommodate it script allows passing which letters should be taken into consideration twice if they appear on the end of instruction. For example `let foo = 'bar';let bar = 'foo'` the semicolon here is actually part of the two of the instructions here:
- it defines where `foo` ends
- it tells us that there is a space between next `let` and other words and allows us to catalogue it as definition of variable.

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
A `bool` value, decides if comments should be present when analysing script or not. The absence of comments might impact the `start` and `end` values of the instructions in an inconvenient way for scripts that rely on precision. But if you only care about the structure and not placement you can rest easy and set this option to true (remember to define `comments` part of the settings otherwise nothing will get removed/analysed).
By default, this option is set to `false` and comments can be found in `comments` part of the block.
```php
    public static function getRemoveComments(array $settings = []): bool
    {
        return false;
    }
```
### `remove.additional`
A `bool|\Closure` value, allows removing not needed parts of the script before the analysis. If you care about speed in which tool analysis the script you might want to remove additional spaces and obstacles which require heavier operations to identify properly.
```php
    public static function getRemoveAdditional(array $settings = []): \Closure|bool
    {
        return function (int &$i, Content\Utf8 $content, string $letter, string $nextLetter, array $schema): void {
            // Operations
        };
    }
```
The method is fired on each letter of the script but if `remove.comments` is set to true - found comments will be removed before this method is called.
It's called in `for` so you can remove part of the script, set `$i` to proper value and wait for another letter. Think of it as a way to help yourself with clean _table and sheet_ before actual work.

#### Attributes
You are provided with following values to allow you identifying not needed part of the script:
- `int $i` - position of caret
- `Content\Utf8 $content` - the actual script
- `string $letter` - current letter
- `string $nextLetter` - next letter (just to make this easier for you)
- `array $schema` - settings from your analyser

### `instructions`
Most important part of the settings, actual definition of syntax. This value will hold all instructions for `Reader` to actually catalogue code into blocks which other scripts will understand. Just like with `comments` it uses trie map but in more _comfortable_ way:
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
With this we have defined that if `Reader` encounters string of characters equal `break` they will be marked and saved into `block`.
For example:
```js
for (;;) {
    break;
}
```
with settings only containing `instructions` analysis will become (representation simplified):
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
        'for/s|e\(/find:")":"(":"condition">read:"condition"\' => [
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
Break down:
- `for/s|e\(` - `s` method checks if letter is whitespace and `e` method makes it optional, so whitespace might be there or not. This means that `for(` is valid, `for (` is valid or even `for \n\t (` is valid.
- `/find:")":"(":"condition">read:"condition"\` - this tells script to search for `)` letter and if encounters `(` to skip another hit (which in this case would be `)`). Find can save skipped content into variable in `data` if we provide name for it (in our case it's `condition`) and thanks to this feature we can later analyse skipped part with `read` methods which we queued with `>` symbol. We also had to tell `read` what it should analyse by passing to it `condition`

With this example in mind let's talk more in depth:

#### OR symbol
`Reader` uses pipe symbol `|` as OR indicator. So script understands that either if those methods is fine:
`/s|e\` (so there can be whitespace or not). It's pretty useful with the next option.
#### Literals
If needed to be multiple letters can be placed in the same place with OR symbol: `function/"*"|"(">isanonymous\{`.
 Syntax like that tells `Reader` that if he encounters an asterisk `*` or left parenthesis `(` he can continue with this path and check if the rest of the syntax is there. Notice that I've queued something which will later allow me to identify which letter was actually found.
##### Negation
You can also set negation on letter: `=/"!="\`.
This means that `Reader` will only proceed if the next letter after equal sign `=` is not another equal sign `!=`.
#### Queue
You can queue some actions after the first one was successful: `=/"!=">decrease\`.
Here we are calling the custom decrease method (which decreases the pointer by one) after the current letter was not an equal sign, so we don't lose any symbols and can smoothly proceed with further identification.
#### Parameters
If your method allow passing additional parameters you can provide them by prefixing them with colon `:`: `/find:")"::"name"\`.
You could potentially pass attributes without closing them in string indicators, but script understands that anything between them is not a valid symbol with will prevent `Reader` from unintentional actions.
You can also notice that I placed two colons next to each other. That means that I skip this parameter and `Reader` will put `null` in its place.
#### Custom method
Custom method must be:
- named only using letter in lowercase
- first parameter accepts `Tetraquark\Model\CustomMethodEssentialsModel`

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
