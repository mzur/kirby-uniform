<?php

// Dirty hack to run tests even if s::start() of the Kirby Toolkit is called
// see: http://stackoverflow.com/a/4059399/1796523
ob_start();

require __DIR__.'/../vendor/autoload.php';
