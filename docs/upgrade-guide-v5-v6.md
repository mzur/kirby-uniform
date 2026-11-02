# Upgrade Guide v5->v6

Uniform v6 requires Kirby 6 and PHP 8.3 or newer. There are only the following breaking changes between Uniform v5 and v6:

- The [SessionStore action](actions/session-store) now stores the form data as an array instead of the form, because Kirby 6 does not restore objects from the session. Replace `kirby()->session()->get('user-form')->data('email')` with `kirby()->session()->get('user-form')['email']`.

- Custom [email templates](actions/email#template) that loop over `$data` to print all form fields have to loop over `$_data` instead. In Kirby 6, `$data` also contains the global template variables of Kirby.

- Form fields with the name `slot` or `slots` cannot be used with email templates, as Kirby 6 reserves these variable names in templates.

That's it!
