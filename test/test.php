<?php
require 'vendor/autoload.php';
use Tetraquark\Tetraquark as Tetraquark;
new Xeno\X('a');
$tetra = new Tetraquark();
// $minified = $tetra->minify(__DIR__ . '/data/single-function.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/array.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/arrowFunction.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify(__DIR__ . '/data/class.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
// $minified = $tetra->minify('/var/www/html/tab_jf/module/_hidden.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
$minified = $tetra->minify('E:\xampp\htdocs\tab_jf\module\event.js'); echo PHP_EOL . PHP_EOL . $minified . PHP_EOL;
