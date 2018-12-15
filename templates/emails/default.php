<?php

foreach ($data as $field => $value) {
	if (is_array($value)) {
		$value = implode(', ', array_filter($value, function ($i) {
			return $i !== '';
		}));
	}

	echo ucfirst($field).': '.$value."\n";
}
