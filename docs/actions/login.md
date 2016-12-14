# Login Action

This action provides a simple way of logging in to the Kirby frontend. The form only requires a `username` and a `password` field (a spam protection guard is not necessary).

Example controller:

```php
<?php
return function($site, $pages, $page) {
   // go to this url if login was successful
   $redirect = $site->url();

   // redirect immediately if user is already logged in
   if ($site->user()) go($redirect);

   $form = uniform(
      'login-form',
      array(
         'guard' => '',
         'required' => array(
            'username' => '',
            'password' => '',
         ),
         'actions' => array(
            array(
               '_action'  => 'login',
               'redirect' => $redirect,
            )
         )
      )
   );

   return compact('form');
};
```

Example template:

```php+html
<form action="<?php echo $page->url()?>#form" method="post">
      <label for="username">Username</label>
      <input<?php e($form->hasError('username'), ' class="erroneous"')?> type="text" name="username" id="username" value="<?php $form->echoValue('username') ?>" required/>

      <label for="password">Password</label>
      <input<?php e($form->hasError('password'), ' class="erroneous"')?> type="password" name="password" id="password" value="<?php $form->echoValue('password') ?>" required/>

   <a name="form"></a>
<?php if ($form->hasMessage()): ?>
   <p class="message <?php e($form->successful(), 'success' , 'error')?>">
      <?php $form->echoMessage() ?>
   </p>
<?php endif ?>

      <button type="submit" name="_submit" value="<?php echo $form->token() ?>"<?php e($form->successful(), " disabled")?>>Login</button>
</form>
```

## Options

### redirect

Redirect URL for redirecting the user after a successful login. If none is given, the user stays on the login page with a success message.
