# Methods

These are the mothods of the `$form` object.

## value($key)

Returns the value of a form field in case the submission of the form has failed. The value is empty if the form was submitted successfully. This will not work if the page was simply refreshed without submitting the form!

`$key`: The `name` attribute of the form field.

## echoValue($key)

Echos [`value($key)`](#valuekey) directly as a HTML-safe string.

`$key`: The `name` attribute of the form field.

## isValue($key, $value)

Checks if a form field has a certain value.

`$key`: The `name` attribute of the form field.

`$value`: The value tested against the actual content of the form field.

Returns `true` if the value equals the content of the form field, `false` otherwise

## hasError($key)

`$key`: (optional) The `name` attribute of the form field.

Retruns `true` if there are erroneous fields. If a key is given, returns `true` if this field is erroneous. Returns `false` otherwise.

## isRequired($key)

`$key`: The `name` attribute of the form field.

Returns `true` if the field was in the list of required fields. Returns `false` otherwise.

## token()

Returns the current session token of this form.

## id()

Returns the ID of the form

## options($key = null)

`$key`: (optional) Key of a specific option to return. If null, all options are returned.

Returns the options (array) of the form.

## removeField($key)

`$key`: Form field name

Remove a form field from the form.

## successful($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns `true` if the action was performed successfully, `false` otherwise. If `'_uniform'` is used, `true` if the form data was successfully validated, `false` otherwise. If no action was specified, `true` if the form data was valid and all actions performed successfully, `false` otherwise.

## message($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns the success/error feedback message of a specific action or Uniform. If no action was specified, all messages will be returned.

## echoMessage($action = false)

Echos [`message($action)`](#messageaction-false) directly as a HTML-safe string.

## hasMessage($action = false)

`$action`: (optional) the index of the action in the actions array or `'_uniform'`.

Returns `true` if there is a success/error feedback message for the specified action or Uniform, `false` otherwise. If no action was specified, returns `true` if there is *any* message, `false` otherwise.
