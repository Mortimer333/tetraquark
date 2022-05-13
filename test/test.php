<?php
require 'vendor/autoload.php';
use Tetraquark\Tetraquark as Tetraquark;
$tetra = new Tetraquark();
// $minified = $tetra->minify(__DIR__ . '/data/array.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/arrowFunction.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/attribute.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/chain.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
$minified = $tetra->minify(__DIR__ . '/data/class.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/comment.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/dowhile.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/for.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/function.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/ifelse.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/object.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/shortIf.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/spread.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/switch.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/variable.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/while.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;

// $minified = $tetra->minify(__DIR__ . '/data/single-function.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify('/var/www/html/tab_jf/module/_hidden.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify('E:\xampp\htdocs\tab_jf\module\get.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
