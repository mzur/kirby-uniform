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
    public function perform()
    {
        $url = $this->requireOption('url');
        $only = $this->option('only');
        $except = $this->option('except');

        if (is_array($only)) {
            $data = [];
            foreach ($only as $key) {
                $data[$key] = $this->form->data($key);
            }
        } else {
            $data = $this->form->data();
        }

        if (is_array($except)) {
            foreach ($except as $key) {
                unset($data[$key]);
            }
        }

        $params = $this->option('params', []);
        // merge the optional 'static' data from the action array with the form data
        $data = array_merge(A::get($params, 'data', []), $data);
        $params['data'] = $this->transformData($data);

        if ($this->option('json') === true) {
            $headers = ['Content-Type: application/json'];
            $params['data'] = json_encode($params['data'], JSON_UNESCAPED_SLASHES);
        } else {
            $headers = ['Content-Type: application/x-www-form-urlencoded'];
        }

        $params['headers'] = array_merge(A::get($params, 'headers', []), $headers);

        $response = $this->request($url, $params);

        if ($response->error !== 0) {
            $this->fail(L::get('uniform-webhook-error').$response->message);
        }
    }

    /**
     * Process the data to some other form than given by the webform.
     *
     * This can be done by custom actions extending from this class.
     *
     * @param array $data
     * @return array
     */
    protected function transformData(array $data)
    {
        return $data;
    }

    /**
     * Perform the request
     *
     * @param  string $url
     * @param  array $params
     * @return RemoteResponse
     */
    protected function request($url, $params)
    {
        return Remote::request($url, $params);
    }
}
