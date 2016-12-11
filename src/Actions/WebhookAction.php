<?php

namespace Uniform\Actions;

use A;
use L;
use Remote;

/**
 * Action to call a webhook with the form data.
 */
class WebhookAction extends Action
{
    /**
     * Call a webhook
     */
    public function execute()
    {
        $url = $this->requireOption('url');
        $data = [];
        $only = $this->option('only');

        // 'only' has higher priority than 'except'
        if (is_array($only)) {
            foreach ($only as $key) {
                $data[$key] = $this->data[$key];
            }
        } else {
            $data = $this->data;
            foreach ($this->option('except', []) as $key) {
                unset($data[$key]);
            }
        }

        $params = $this->option('params', []);
        // merge the optional 'static' data from the action array with the form data
        $params['data'] = array_merge(A::get($params, 'data', []), $data);

        if ($this->option('json') === true) {
            $headers = ['Content-Type: application/json'];
            $params['data'] = json_encode($params['data'], JSON_UNESCAPED_SLASHES);
        } else {
            $headers = ['Content-Type: application/x-www-form-urlencoded'];
        }

        $params['headers'] = array_merge(A::get($params, 'headers', []), $headers);

        $response = Remote::request($url, $params);

        if ($response->error !== 0) {
            $this->fail(L::get('uniform-webhook-error').$response->message);
        }
    }
}
