# Upgrade Guide v4->v5

There are only the following breaking changes between Uniform v4 and v5:

- Form data is now HTML-escaped by default in the Dump, Email, Log and Webhook actions. Set the new `escapeHtml` option to `false` to disable escaping.

- The `data()` method of the form now HTML-escapes form data by default. Set the new third argument to `false` to disable escaping.

That's it!
