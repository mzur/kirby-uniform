<?php

/**
 * A class to handle performing actions with form data.
 */

class UniForm {
	/**
	 * Length of the token string unique for a session until the form is sent.
	 */
	const TOKEN_LENGTH = 20;

	/*
	 * The array of all action callback functions.
	 */
	public static $actions = array();

	/**
	 * Unique ID/Key of this form.
	 */
	private $id;

	/**
	 * Array of uniform options, including the actions to be performed.
	 */
	private $options;

	/**
	 * POST data of the form.
	 */
	private $data;

	/**
	 * Token string unique for a session until the form is sent. It is used to
	 * prevent arbitrary (scripted) post requests to be able to use the form.
	 * It shuld only be possible to submit the form from the actual website 
	 * containing it.
	 */
	private $token;

	/**
	 * Contains the returned values of the performed action callbacks as well as
	 * 'success' and 'message' of the form plugin itself.
	 */
	private $actionOutput;

	/**
	 * Array of keys of form fields that were required and not given or failed
	 * their validation.
	 */
	private $erroneousFields;

	/**
	 * @param string $id The unique ID of this form.
	 *
	 * @param array $options Array of uniform options, including the actions.
	 */
	public function __construct($id, $options) {

		if (empty($id)) {
			throw new Error('No Uniform ID was given.');
		}

		$this->id = $id;

		$this->erroneousFields = array();

		$this->options = array(
			// honeypot field name, default is 'website'
			'honeypot' => a::get($options, 'honeypot', 'website'),
			// required field names
			'required' => a::get($options, 'required', array()),
			// field names to be validated
			'validate' => a::get($options, 'validate', array()),
			// action arrays
			'actions'  => a::get($options, 'actions', array()),
		);

		// required fields will also be validated by default
		$this->options['validate'] = a::merge(
			$this->options['validate'],
			$this->options['required']
		);

		// initialize output array with the output of the plugin itself
		$this->actionOutput = array(
			'_uniform' => array(
				'success' => false,
				'message' => ''
			)
		);

		// the token is stored as session variable until the form is sent
		// successfully
		$this->token = s::get($this->id);

		if (!$this->token) $this->generateToken();

		// get the data to be sent (if there is any)
		$this->data = get();

		if ($this->requestValid()) {
			// remove uniform specific fields from form data
			unset($this->data['_submit']);
			unset($this->data[$this->options['honeypot']]);

			if (empty($this->options['actions'])) {
				throw new Error('No Uniform actions were given.');
			}

			if ($this->dataValid()) {
				// uniform is done, now it's the actions turn
				$this->actionOutput['_uniform']['success'] = true;
			}
		} else {
			// generate new token to spite the bots }:-)
			$this->generateToken();
		}
	}

	/**
	 * Custom implementation of a::missing(). Only works with associative arrays
	 * and string values.
	 *
	 * see: https://github.com/getkirby/toolkit/issues/47
	 *
	 * @param   array  $array The source array
	 * @param   array  $required An array of required keys
	 * @return  array  An array of missing fields. If this is empty, nothing is
	 *                 missing.
	 */
	private static function missing($array, $required = array()) {
		$missing = array();
		foreach($required as $r) {
			if(!array_key_exists($r, $array) || ($array[$r]==='')) {
				$missing[] = $r;
			}
		}
		return $missing;
	}

	/**
	 * Generates a new token for this form and session.
	 */
	private function generateToken() {
		$this->token = str::random(static::TOKEN_LENGTH);
		s::set($this->id, $this->token);
	}

	/**
	 * Destroys the current token of this form.
	 */
	private function destroyToken() {
		s::remove($this->id);
	}

	/**
	 * Quickly decides if the request is valid so the server is minimally
	 * stressed by scripted attacks.
	 */
	private function requestValid() {
		if (a::get($this->data, '_submit') !== $this->token) {
			return false;
		}

		if (!array_key_exists($this->options['honeypot'], $this->data)) {
			throw new Error('Uniform honeypot "'.$this->options['honeypot'].
				'" is missing.');
		}

		if (v::required($this->options['honeypot'], $this->data)) {
			$this->actionOutput['_uniform']['message'] =
				l::get('uniform-filled-potty');
			return false;
		}

		return true;
	}

	/**
	 * Checks if all required data is present to send the form.
	 */
	private function dataValid() {

		// check if all required fields are there
		$this->erroneousFields = static::missing(
			$this->data,
			array_keys($this->options['required'])
		);

		if (!empty($this->erroneousFields)) {
			$this->actionOutput['_uniform']['message'] =
				l::get('uniform-fields-required');
			return false;
		}

		// perform validation for all fields with a given validation method
		foreach ($this->options['validate'] as $field => $method) {
			$value = a::get($this->data, $field);
			// validate only if a method is given and the field contains data
			if (!empty($method) && !empty($value) && !call('v::'.$method, $value)) {
				array_push($this->erroneousFields, $field);
			}
		}

		if (!empty($this->erroneousFields)) {
			$this->actionOutput['_uniform']['message'] =
				l::get('uniform-fields-not-valid');
			return false;
		}

		return true;
	}

	/**
	 * Executes the form actions.
	 *
	 * @return true if all actions were performed successfully, false otherwise.
	 */
	public function execute() {
		// don't execute if there were validation errors
		if (!$this->actionOutput['_uniform']['success']) return false;

		foreach ($this->options['actions'] as $index => $action) {
			// skip this array if it doesn't contain an action name
			if (!($key = a::get($action, '_action'))) continue;

			if (!isset(static::$actions[$key])) {
				throw new Error('The uniform action "'.$key.'" does not exist.');
			}

			$this->actionOutput[$index] = call_user_func(
				static::$actions[$key],
				$this->data,
				$action
			);
		}

		// if all actions performed successfully, the session is over
		if ($this->successful()) $this->destroyToken();

		return $this->successful();
	}

	/**
	 * Returns the value of a form field. The value is empty if the form was
	 * sent successful.
	 *
	 * @param string $key The "name" attribute of the form field.
	 */
	public function value($key) {
		return ($this->successful()) ? '' : a::get($this->data, $key, '');
	}

	/**
	 * Echos the value of a form field directly as a HTML-safe string.
	 *
	 * @param string $key The "name" attribute of the form field.
	 */
	public function echoValue($key) {
		echo str::html($this->value($key));
	}

	/**
	 * Checks if a form field has a certain value.
	 *
	 * @param string $key The "name" attribute of the form field.
	 *
	 * @param string $value The value tested against the actual content of the form field.
	 *
	 * @return True if the value equals the content of the form field. false
	 * 	otherwise
	 */
	public function isValue($key, $value) {
		return $this->value($key) === $value;
	}

	/**
	 * @param string $key (optional) the key of the form field to check.
	 *
	 * @return true if there are erroneous fields. If a key is given, returns
	 * true if this field is erroneous. Returns false otherwise.
	 *
	 */
	public function hasError($key) {
		return ($key)
			? v::in($key, $this->erroneousFields)
			: !empty($this->erroneousFields);
	}

	/**
	 * @return the current session token of this form.
	 */
	public function token() {
		return $this->token;
	}

	/**
	 * @param string $action (optional) the index of the action to perform a
	 * successful check
	 * @return if an <code>$action></code> was given, <code>true</code> if the
	 * action was performed successfully, <code>false</code> otherwise. if no
	 * <code>$action></code> was given, <code>true</code> if all actions
	 * performed successfully, <code>false</code> otherwise.
	 */
	public function successful($action = false) {
		if (!is_int($action) && !is_string($action)) {
			foreach ($this->actionOutput as $output) {
				if (!a::get($output, 'success')) return false;
			}
			return true;
		} elseif (array_key_exists($action, $this->actionOutput)) {
			return a::get($this->actionOutput[$action], 'success');
		} else {
			return false;
		}
	}

	/**
	 * @param string $action (optional) the index of the action to get the
	 * feedback message from
	 * @return if an <code>$action></code> was given, the success/error
	 * feedback message of the action. if no <code>$action></code> was given, 
	 * the feedback messages of all actions; one per line.
	 */
	public function message($action = false) {
		$message = '';
		if (!is_int($action) && !is_string($action)) {
			foreach ($this->actionOutput as $output) {
				$message .= a::get($output, 'message', '') . "\n";
			}
		} elseif (array_key_exists($action, $this->actionOutput)) {
			$message = a::get($this->actionOutput[$action], 'message', '');
		}

		return $message;
	}

	/**
	 * @param string $action (optional) the index of the action to get the
	 * feedback message from
	 *
	 * Echos the success/error feedback message directly as a HTML-safe string.
	 * Either from one specified action or from all actions.
	 */
	public function echoMessage($action = false) {
		echo str::html($this->message($action));
	}

	/**
	 * @param string $action (optional) the index of the action to check for the
	 * presence of a feedback message.
	 * @return true if there is a success/error feedback message for the 
	 * specified action. of no action was specified, true if there is any 
	 * message from any action, false otherwise.
	 */
	public function hasMessage($action = false) {
		if (!is_int($action) && !is_string($action)) {
			foreach ($this->actionOutput as $output) {
				if (a::get($output, 'message')) return true;
			}
			return false;
		} elseif (array_key_exists($action, $this->actionOutput)) {
			return (bool) a::get($this->actionOutput[$action], 'message');
		} else {
			return false;
		}
	}
}

/* DEFAULT ACTIONS */

uniform::$actions['email'] = function($form, $actionOptions) {

	$options = array(
		// apply the dynamic subject (insert form data)
		'subject'         => str::template(
			a::get($actionOptions, 'subject', l::get('uniform-email-subject')),
			$form
		),
		'snippet'         => a::get($actionOptions, 'snippet', false),
		'to'              => a::get($actionOptions, 'to'),
		'sender'          => a::get($actionOptions, 'sender'),
		'service'         => a::get($actionOptions, 'service', 'mail'),
		'service-options' => a::get($actionOptions, 'service-options', array())
	);

	// remove newlines to prevent malicious modifications of the email
	// header
	$options['subject'] = str_replace("\n", '', $options['subject']);

	$mailBody = "";
	$snippet = $options['snippet'];

	if (empty($snippet)) {
		foreach ($form as $key => $value) {
			if (str::startsWith($key, '_')) {
				continue;
			}

			$mailBody .= ucfirst($key).': '.$value."\n\n";
		}
	} else {
		$mailBody = snippet($snippet, compact('form', 'options'), true);
		if ($mailBody === false) {
			throw new Exception('Uniform email action: The email snippet "'.
				$snippet.'" does not exist!');
		}
	}

	$params = array(
		'service' => $options['service'],
		'options' => $options['service-options'],
		'to'      => $options['to'],
		'from'    => $options['sender'],
		'replyTo' => a::get($form, 'name', '').' <'.a::get($form, '_from').'>',
		'subject' => $options['subject'],
		'body'    => $mailBody
	);

	$email = email($params);

	if (array_key_exists('_receive_copy', $form)) {
		$params['subject'] = l::get('uniform-email-copy').' '.$params['subject'];
		$params['to'] = $params['from'];
		email($params)->send();
	}

	if($email->send()) {
        return array(
            'success' => true,
            'message' => l::get('uniform-email-success')
        );
    } else {
        return array(
            'success' => false,
            'message' => l::get('uniform-email-error').' '.$email->error()
        );
    }
};