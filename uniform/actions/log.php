<?php

/*
 * Action to log the form data to a file
 */
uniform::$actions['log'] = function ($form, $actionOptions) {
    $file = a::get($actionOptions, 'file', false);
    if ($file === false) {
        throw new Exception('Uniform log action: No logfile specified!');
    }

    $snippet = a::get($actionOptions, 'snippet', '');

    if (empty($snippet)) {
        $data = '['.date('c').'] '.visitor::ip().' '.visitor::userAgent();

        foreach ($form as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter($value, function ($i) {
                    return $i !== '';
                }));
            }
            $data .= "\n{$key}: {$value}";
        }
        $data .= "\n\n";
    } else {
        $data = snippet($snippet, compact('form', 'actionOptions'), true);
    }

    $success = file_put_contents($file, $data, FILE_APPEND | LOCK_EX);

    if ($success === false) {
        return [
            'success' => false,
            'message' => l::get('uniform-log-error'),
        ];
    } else {
        return [
            'success' => true,
            'message' => l::get('uniform-log-success'),
        ];
    }
};
