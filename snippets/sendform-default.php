<?php

foreach ($data as $field => $value) {
	if (str::startsWith($field, '_')) {
		continue;
	}

	echo ucfirst($field).': '.$value."\n";
}