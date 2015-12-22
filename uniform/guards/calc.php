<?php

/**
 * The guard to check if a captcha was filled in correctly.
 * Removes the captcha field if the check passes.
 */
uniform::$guards['calc'] = function (UniForm $form) {
    $result = s::get($form->id().'-captcha-result');
    $field = '_captcha';

    if (!empty($result) && $form->value($field) != $result) {
        return [
            'success' => false,
            'message' => l::get('uniform-fields-not-valid'),
            // mark the catcha field as erroneous
            'fields' => [$field],
            // don't clear all form fields
            'clear' => false,
        ];
    }

    // remove captcha field from form data
    $form->removeField($field);

    return ['success' => true];
};

/**
 * Generates a new calculate captcha result for a Uniform form.
 *
 * @param UniForm $form The form to generate the captcha for
 *
 * @return string A label like '4 plus 5'
 */
function uniform_captcha(UniForm $form)
{
    list($a, $b) = [rand(0, 9), rand(0, 9)];
    s::set($form->id().'-captcha-result', $a + $b);

    return str::encode($a.' '.l::get('uniform-calc-plus').' '.$b);
}
