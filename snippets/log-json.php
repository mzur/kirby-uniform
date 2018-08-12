<?php

use Kirby\Cms\App;

$header = [
	'timestamp' => date('c'),
	'ip' => App::instance()->visitor()->ip(),
	'userAgent' => App::instance()->visitor()->userAgent(),
];

echo json_encode(array_merge($header, $data));
