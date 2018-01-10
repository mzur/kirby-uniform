<?php 
$header = array(
	'timestamp' => date('c'),
	'ip' => Visitor::ip(),
	'userAgent' => Visitor::userAgent(),
);

print(json_encode(array_merge($header, $data)) . "\n");
