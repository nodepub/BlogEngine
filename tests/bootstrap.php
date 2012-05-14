<?php

define('FIXTURES_DIR', __DIR__.'/fixtures');

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('NodePub\BlogEngine', __DIR__.'/../lib');