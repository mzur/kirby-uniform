<?php

/**
 * The guard to check if a honeypot form field got filled in.
 * Removes the field if the check passes.
 */
uniform::$guards['honeypot'] = function (UniForm $form) {
    $field = $form->options('honeypot');

    if (!$field) {
        // default honeypot name is 'website'
        $field = 'website';
    }

    if ($form->value($field)) {
        return [
            'success' => false,
            'message' => l::get('uniform-filled-potty'),
        ];
    }
    // remove honeypot field from form data
    $form->removeField($field);

    return ['success' => true];
};
