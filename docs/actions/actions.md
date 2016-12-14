# Actions

Once all required fields are present and validated, the actions are performed. These can be completely arbitrary functions that receive the form data and action options as arguments. An example is the builtin `email` action. You can create your own action, too, of course!

To add custom actions, create a `site/plugins/uniform-actions/uniform-actions.php` file and implement all your custom actions there. Take a look at the [`email` action](https://github.com/mzur/kirby-uniform/blob/v2.2.1/uniform/actions/email.php) to see how to implement one.
