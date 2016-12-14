# Webhook Action

This action can call a remote webhook url and send the form data to it.

Example controller:

```php
<?php
return function($site, $pages, $page) {

   $form = uniform(
      'webhook-form',
      array(
         'required' => array(
            'name'      => '',
            'message' => '',
            '_from'     => 'email'
         ),
         'actions' => array(
            array(
               '_action' => 'webhook',
               'only' => array(
                  'name'
               ),
               'url' => 'http://example.com/myhook',
               'params' => array(
                  'method' => 'post',
                  'data' => array(
                     'token' => 'my_access_token'
                  )
               )
            )
         )
      )
   );

   return compact('form');
};
```

The template looks [as usual](examples/basic). In this case it has a `name`, `message` and `_from` field. The example above takes only the content of the `name` field, adds the given `token` and sends both to `http://example.com/myhook` as a `POST` request.

## Options

### url (required)

The url, the request should be sent to.

### params

The parameter array of the request. Can contain the HTTP `'method'` or additional `'data'`. The data will be merged with the form data.

For the defaults and the possible options, see the [remote class](https://github.com/getkirby/toolkit/blob/e712a46bffc5957044dbf71d9a5b735fdd9540db/lib/remote.php#L18-27) of the Kirby Toolkit.

### only

An array of form field names that should be sent to the webhook. Any form field not specified in this array will be discarded. This option takes precedence over the `except` option. This does not affect the data fields manually given in the `data` parameter.

### except

An array of form field names that should *not* be sent to the webhook. Any form field *not* present in this array will be sent to the webhook. This does not affect the data fields manually given in the `data` parameter. If the `only` array is specified, this option will be ignored.

### json

Set to `true` to send the data as JSON. Default is `false`, which will result in the data being sent as `application/x-www-form-urlencoded`.
