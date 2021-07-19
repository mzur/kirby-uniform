<?php

foreach ($data as $field => $value) {
    if (in_array($field, ['_data', '_options'])) {
        continue;
    }

    if (is_array($value)) {
        $value = implode(', ', array_filter($value, function ($i) {
            return $i !== '';
        }));
    }

    echo ucfirst($field).': '.$value."\n";
}
