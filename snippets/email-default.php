<?php

foreach ($form as $field => $value) {
	if (str::startsWith($field, '_')) {
		continue;
	}

	if (is_array($value)) {
		$value = implode(', ', array_filter($value, function ($i) {
			return $i !== '';
		}));
	}

	echo ucfirst($field).': '.$value."\n";
}
