<?php

use Tetraquark\{Log, Content};

$content = new Content(' trim ');
$trimmed = $content->trim();
Log::log('Trimmed: `' . $trimmed . '`');
$string = ' asddsa';
Log::log('(' . gettype(preg_match('/\s/', $string)) . ') ' . preg_match('/\s/', $string));
