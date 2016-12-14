# Email Select Action

This action makes switching the recipient email adresses possible, e.g. if the user should be able to choose to send the form to "marketing", "sales" or "feedback" and you have different email addresses for these.

Example controller:

```php
<?php

return function($site, $pages, $page) {

   $form = uniform(
      'contact-form',
      array(
         'required' => array(
            'name'      => '',
            'message'   => '',
            '_from'     => 'email'
         ),
         'actions' => array(
            array(
               '_action' => 'email-select',
               'sender'  => 'info@example.com',
               'allowed-recipients' => array(
                  'sales'     => 'sales@example.com',
                  'marketing' => 'marketing@example.com',
                  'feedback'  => 'feedback@example.com'
               )
            )
         )
      )
   );
   return compact('form');
};
```

The [form in the template](examples/basic) then has to contain a `_recipient` field that can look like this:

```php+html
<select name="_recipient" id="_recipient">
   <?php $value = $form->value('_recipient') ?>
   <option value="sales"<?php e($value=='sales', ' selected')?>>Contact sales</option>
   <option value="marketing"<?php e($value=='marketing', ' selected')?>>Contact marketing</option>
   <option value="feedback"<?php e($value=='feedback', ' selected')?>>Give feedback</option>
</select>
```

Besides a select field, radio buttons could be used as well.

## Options

### allowed-recipients (required)

An array of possible recipients. It contains key-value pairs of a recipient ID and the email address. The ID is used in the form HTML so the actual email addresses remain undisclosed. When the form is submitted, it will be sent to the email address belonging to the recipient ID given by the `_recipient` form field.

### other options

In addition to the options above, all [options of the email action](#options) can be used as well (especially the required ones). The single exception is the `to` option, which will be ignored by this action. The recipient is not specified by `to` but chosen from `allowed-recipients`.
