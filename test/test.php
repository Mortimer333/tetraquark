<?php
require 'vendor/autoload.php';
use Tetraquark\Tetraquark as Tetraquark;
new Xeno\X('a');
$tetra = new Tetraquark();
$minified = $tetra->minify(__DIR__ . '/data/single-function.js');
echo PHP_EOL;
