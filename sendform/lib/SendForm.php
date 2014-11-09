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
	private $sent_successful;

	/**
	 * Feedback success/error message.
	 */
	private $sent_message;

	/**
	 * id: The unique ID of this form.
	 *
	 * recipient: e-mail adress the form content should be sent to.
	 *
	 * subject (optional): The subject of the e-mail.
	 */
	function __construct($id, $recipient, $subject='') {

		if (str::length($id) === 0) {
			throw new Error('No SendForm ID was given.');
		}

		if (str::length($recipient) === 0) {
			throw new Error('No SendForm recipient was given.');
		}

		$this->id = $id;

		// the token is stored as session variable until the form is sent
		// successfully
		$this->token = s::get($this->id);

		if (!$this->token) {
			$this->generate_token();
		}

		// get the data to be sent (if there is any)
		$this->data = get();
		$this->data['_to'] = $recipient;
		$this->data['_subject'] = $subject;

		$this->sent_successful = false;
		$this->sent_message = '';

		if ($this->data_valid()) {
			$this->send_form();
		}
	}

	/**
	 * Generates a new token for this form and session.
	 */
	private function generate_token() {
		$this->token = str::random(SendForm::TOKEN_LENGTH);
		s::set($this->id, $this->token);
	}

	/**
	 * Destroys the current token of this form.
	 */
	private function destroy_token() {
		s::remove($this->id);
	}

	/**
	 * Checks if all required data is present to send the form. This includes
	 * the session token, the sender's e-mail address and the hney pot still
	 * being empty.
	 */
	private function data_valid() {

		if (a::get($this->data, '_submit') === $this->token) {
			if (a::get($this->data, '_from')) {
				if (!a::get($this->data, '_potty')) {
					return true;
				} else {
					$this->sent_message = l::get('sendform-filled-potty');
				}
			} else {
				$this->sent_message = l::get('sendform-no-email');
			}
		}

		return false;
	}

	/**
	 * Bundles the form data to an e-mail body and sends it.
	 */
	private function send_form() {
		$mail_body = "";

		foreach ($this->data as $key => $value) {
			if (str::startsWith($key, '_')) {
				continue;
			}

			$mail_body .= ucfirst($key).': '.$value."\n\n";
		}

		$email = email(array(
			'to'			=> a::get($this->data, '_to'),
			'from'		=> a::get($this->data, 'name', '') . ' <' .
				$this->data['_from'] . '>',
			'subject'	=> a::get($this->data, '_subject',
				l::get('sendform-default-subject')),
			'body'		=> $mail_body
		));

		if($email->send()) {
			$this->sent_message = l::get('sendform-send-success');
			$this->sent_successful = true;
			// now this form send session is over, so destroy the token
			$this->destroy_token();
		} else {
			$this->sent_message = l::get('sendform-send-error') . " " .
				$email->error();
		}
	}

	/**
	 * Returns the value of a form field. The value is empty if the form was
	 * sent successful.
	 *
	 * key: The "name" attribute of the form field.
	 */
	public function value($key) {
		return ($this->sent_successful) ? '' : a::get($this->data, $key, '');
	}

	/**
	 * Echos the value of a form field directly as a HTML-safe string.
	 *
	 * key: The "name" attribute of the form field.
	 */
	public function echo_value($key) {
		echo str::html($this->value($key));
	}

	/**
	 * Checks if a form field has a certain value.
	 *
	 * key: The "name" attribute of the form field.
	 *
	 * value: The value tested against the actual content of the form field.
	 *
	 * returns: True if the value equals the content of the form field. false
	 * 	otherwise
	 */
	public function is_value($key, $value) {
		return $this->value($key) === $value;
	}

	/**
	 * Returns true if the form was sent successfully. false otherwise.
	 */
	public function successful() {
		return $this->sent_successful;
	}

	/**
	 * Returns the success/error feedback message.
	 */
	public function message() {
		return $this->sent_message;
	}

	/**
	 * Echos the success/error feedback message directly as a HTML-safe string.
	 */
	public function echo_message() {
		echo str::html($this->message());
	}

	/**
	 * Returns true if there is a success/error feedback message.
	 */
	public function has_message() {
		return str::length($this->sent_message) !== 0;
	}

	/**
	 * Returns the current session token of this form.
	 */
	public function token() {
		return $this->token;
	}
}