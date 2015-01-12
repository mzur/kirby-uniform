<?php

/**
 * A class to handle sending a contact form by e-mail.
 */

class SendForm {
	/**
	 * Length of the token string unique for a session until the form is sent.
	 */
	const TOKEN_LENGTH = 20;

	/**
	 * Unique ID/Key of this form.
	 */
	private $id;

	/**
	 * POST data that this form should send.
	 */
	private $data;

	/**
	 * Token string unique for a session until the form is sent. It is used to
	 * prevent arbitrary (scripted) post requests to be able to send e-mails
	 * through this form. It shuld only be possible to submit the form from the
	 * actual website containing it.
	 */
	private $token;

	/**
	 * Was this form sent succesfully?
	 */
	private $sentSuccessful;

	/**
	 * Feedback success/error message.
	 */
	private $message;

	/**
	 * Array of keys of form fields that were required and not given or failed
	 * their validation.
	 */
	private $erroneousFields;

	/**
	 * The options array.
	 */
	private $options;

	/**
	 * @param string $id The unique ID of this form.
	 *
	 * @param string $recipient e-mail adress the form content should be sent to.
	 *
	 * @param array $options Array of sendform options.
	 */
	public function __construct($id, $recipient, $options) {

		if (empty($id)) {
			throw new Error('No SendForm ID was given.');
		}

		if (empty($recipient)) {
			throw new Error('No SendForm recipient was given.');
		}

		$this->id = $id;

		$this->erroneousFields = array();

		// the token is stored as session variable until the form is sent
		// successfully
		$this->token = s::get($this->id);

		if (!$this->token) {
			$this->generateToken();
		}

		// get the data to be sent (if there is any)
		$this->data = get();

		if ($this->requestValid()) {
			$this->options = array(
				'subject' 			=>
					// apply the dynamic subject (insert form data)
					str::template(a::get($options, 'subject',
						l::get('sendform-default-subject')), $this->data),
				'snippet'			=> a::get($options, 'snippet', false),
				'copy'				=> a::get($options, 'copy', array()),
				'required'			=> a::get($options, 'required', array()),
				'validate'			=> a::get($options, 'validate', array()),
				'to'					=> $recipient,
				'service'			=> a::get($options, 'service', 'mail'),
				'service-options'	=> a::get($options, 'service-options', array())
			);

			// remove newlines to prevent malicious modifications of the email
			// header
			$this->options['subject'] = str_replace("\n", '', $this->options['subject']);

			// extend the data array so email snippets get these fields, too
			$this->data['_subject'] = $this->options['subject'];
			$this->data['_to'] 		= $this->options['to'];

			if (array_key_exists('_receive_copy', $this->data)) {
				array_unshift($this->options['copy'], $this->data['_from']);
			}

			$this->sentSuccessful = false;
			$this->message = '';

			$requiredFields = a::merge(
				$this->options['required'],
				// default required fields overwrite the fields of the options
				array('_from' => 'email')
			);

			$validateFields = a::merge(
				$this->options['validate'],
				// required fields will also be validated by default
				$requiredFields
			);

			if ($this->dataValid($requiredFields, $validateFields)) {
				$this->sendForm();
			}
		}
	}

	/**
	 * Generates a new token for this form and session.
	 */
	private function generateToken() {
		$this->token = str::random(SendForm::TOKEN_LENGTH);
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

		if (v::required('_potty', $this->data)) {
			$this->message = l::get('sendform-filled-potty');
			return false;
		}

		return true;
	}

	/**
	 * Checks if all required data is present to send the form.
	 */
	private function dataValid($requiredFields, $validateFields) {

		// check if all required fields are there
		$this->erroneousFields = a::missing(
			$this->data,
			array_keys($requiredFields)
		);

		if (!empty($this->erroneousFields)) {
			$this->message = l::get('sendform-fields-required');
			return false;
		}

		// perform validation for all fields with a given validation method
		foreach ($validateFields as $field => $method) {
			$value = a::get($this->data, $field);
			// validate only if a method is given and the field contains data
			if (!empty($method) && !empty($value) && !call('v::' . $method, $value)) {
				array_push($this->erroneousFields, $field);
			}
		}

		if (!empty($this->erroneousFields)) {
			$this->message = l::get('sendform-fields-not-valid');
			return false;
		}

		return true;
	}

	/**
	 * Bundles the form data to an e-mail body and sends it.
	 */
	private function sendForm() {
		$mailBody = "";
		$snippet = $this->options['snippet'];

		if (empty($snippet)) {
			foreach ($this->data as $key => $value) {
				if (str::startsWith($key, '_')) {
					continue;
				}

				$mailBody .= ucfirst($key).': '.$value."\n\n";
			}
		} else {
			$mailBody = snippet($snippet, array('data' => $this->data), true);
			if ($mailBody === false) {
				throw new Exception("The email snippet '" . $snippet . "' does not exist!");
			}
		}

		$params = array(
			'service'	=> $this->options['service'],
			'options'	=> $this->options['service-options'],
			'to'			=> $this->options['to'],
			'from'		=> a::get($this->data, 'name', '') . ' <' .
				a::get($this->data, '_from') . '>',
			'subject'	=> $this->options['subject'],
			'body'		=> $mailBody
		);

		$email = email($params);

		if($email->send()) {
			$params['subject'] = l::get('sendform-email-copy') . ' ' . $params['subject'];

			// if everything was ok, send the copies
			foreach ($this->options['copy'] as $address) {
				$params['to'] = $address;
				email($params)->send();
			}

			$this->message = l::get('sendform-send-success');
			$this->sentSuccessful = true;
			// now this form send session is over, so destroy the token
			$this->destroyToken();
		} else {
			$this->message = l::get('sendform-send-error') . " " .
				$email->error();
		}
	}

	/**
	 * Returns the value of a form field. The value is empty if the form was
	 * sent successful.
	 *
	 * @param string $key The "name" attribute of the form field.
	 */
	public function value($key) {
		return ($this->sentSuccessful) ? '' : a::get($this->data, $key, '');
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
	 * @return true if the form was sent successfully. false otherwise.
	 */
	public function successful() {
		return $this->sentSuccessful;
	}

	/**
	 * @return the success/error feedback message.
	 */
	public function message() {
		return $this->message;
	}

	/**
	 * Echos the success/error feedback message directly as a HTML-safe string.
	 */
	public function echoMessage() {
		echo str::html($this->message());
	}

	/**
	 * @return true if there is a success/error feedback message.
	 */
	public function hasMessage() {
		return !empty($this->message);
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
}
