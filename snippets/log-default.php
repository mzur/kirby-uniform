<?php
    print('['.date('c').'] '.Visitor::ip().' '.Visitor::userAgent());
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', array_filter($value, function ($i) {
                return $i !== '';
            }));
        }
        print("\n{$key}: {$value}");
    }
    print("\n\n");
