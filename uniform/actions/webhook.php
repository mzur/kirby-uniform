<?php

/*
 * Action to log in to the Kirby frontend
 */
uniform::$actions['webhook'] = function ($form, $actionOptions) {
    $url = a::get($actionOptions, 'url', false);

    if ($url === false) {
        throw new Exception('Uniform webhook action: No url specified!');
    }

    $data = [];
    $only = a::get($actionOptions, 'only');

    // 'only' has higher priority than 'except'
    if (is_array($only)) {
        // take only the fields specified in 'only'
        foreach ($only as $key) {
            $data[$key] = $form[$key];
        }
    } else {
        $data = $form;
        // remove those fields specified in 'except'
        foreach (a::get($actionOptions, 'except', []) as $key) {
            unset($data[$key]);
        }
    }

    $params = a::get($actionOptions, 'params', []);

    // merge the optional 'static' data from the action array with the form data
    $params['data'] = array_merge(a::get($params, 'data', []), $data);

    if (a::get($actionOptions, 'json', false)) {
        $headers = ['Content-Type: application/json'];
        $params['data'] = json_encode($params['data'], JSON_UNESCAPED_SLASHES);
    } else {
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
    }

    $params['headers'] = array_merge(
        a::get($params, 'headers', []),
        $headers
    );

    $response = remote::request($url, $params);

    if ($response->error === 0) {
        return [
            'success' => true,
            'message' => l::get('uniform-webhook-success'),
        ];
    } else {
        return [
            'success' => false,
            'message' => l::get('uniform-webhook-error').$response->message,
        ];
    }
};
