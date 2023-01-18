<?php

namespace Uniform\Guards;

use Exception;
use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * Guard that checks an encrypted honeytime form field.
 */
class HoneytimeGuard extends Guard
{
    /**
     * Default name for the honeytime form field.
     *
     * @var string
     */
    const FIELD_NAME = 'uniform-honeytime';

    /**
     * Encryption cipher.
     *
     * @var string
     */
    const CIPHER = 'AES-128-CBC';

    /**
     * Encrypt plaintext.
     *
     * @param string $key The base64 encoded encryption key
     * @param string $plaintext Text to encrypt
     *
     * @return string
     */
    public static function encrypt($key, $plaintext)
    {
        // Encryption based on PHP manual:
        // https://www.php.net/manual/en/function.openssl-encrypt.php
        $key = base64_decode($key);
        $ivlen = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
        $ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);

        return $ciphertext;
    }

    /**
     * {@inheritDoc}
     * Check if the value of the honeytime field indicates a form submission
     * that happened too fast.
     *
     * Remove the honeytime field from the form data if it was empty.
     */
    public function perform()
    {
        $field = $this->option('field', self::FIELD_NAME);
        $seconds = $this->option('seconds', 10);
        $key = $this->requireOption('key');

        $key = base64_decode($key);
        $value = App::instance()->request()->body()->get($field);

        // Decryption based on PHP manual:
        // https://www.php.net/manual/en/function.openssl-encrypt.php
        try {
            $c = base64_decode($value);
            $ivlen = openssl_cipher_iv_length(self::CIPHER);
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);

            $original_plaintext = openssl_decrypt($ciphertext_raw, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
        } catch (Exception $e) {
            $this->reject(I18n::translate('uniform-honeytime-invalid'));
        }

        if (!empty($hmac) && hash_equals($hmac, $calcmac)) {
            if ((time() - intval($original_plaintext)) <= $seconds) {
                $this->reject(I18n::translate('uniform-honeytime-reject'));
            }
        } else {
            $this->reject(I18n::translate('uniform-honeytime-invalid'));
        }

        $this->form->forget($field);
    }
}
