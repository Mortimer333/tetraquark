<?php
require 'vendor/autoload.php';
use Tetraquark\Tetraquark as Tetraquark;
$tetra = new Tetraquark();
// $minified = $tetra->minify(__DIR__ . '/data/array.js');
// $minified = $tetra->minify(__DIR__ . '/data/arrowFunction.js');
// $minified = $tetra->minify(__DIR__ . '/data/attribute.js');
// $minified = $tetra->minify(__DIR__ . '/data/chain.js');
// $minified = $tetra->minify(__DIR__ . '/data/class.js');
// $minified = $tetra->minify(__DIR__ . '/data/comment.js');
// $minified = $tetra->minify(__DIR__ . '/data/caller.js');
// $minified = $tetra->minify(__DIR__ . '/data/dowhile.js');
// $minified = $tetra->minify(__DIR__ . '/data/export.js');
// $minified = $tetra->minify(__DIR__ . '/data/for.js');
// $minified = $tetra->minify(__DIR__ . '/data/function.js');
// $minified = $tetra->minify(__DIR__ . '/data/ifelse.js');
// $minified = $tetra->minify(__DIR__ . '/data/import.js');
// $minified = $tetra->minify(__DIR__ . '/data/object.js');
// $minified = $tetra->minify(__DIR__ . '/data/shortIf.js');
// $minified = $tetra->minify(__DIR__ . '/data/spread.js');
// $minified = $tetra->minify(__DIR__ . '/data/switch.js');
// $minified = $tetra->minify(__DIR__ . '/data/variable.js');
// $minified = $tetra->minify(__DIR__ . '/data/while.js');

// $minified = $tetra->minify(__DIR__ . '/data/single-function.js');
$minified = $tetra->minify('/var/www/html/tab_jf/main.js');
// $minified = $tetra->minify('E:\xampp\htdocs\tab_jf\main.js');
echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// echo PHP_EOL . PHP_EOL . str_replace(';', ";\n", $minified ?? '') . PHP_EOL;
