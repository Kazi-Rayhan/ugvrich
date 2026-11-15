# November 2026 idea submission notice

The personalised email thanks each submitter, includes their idea title and reference, and announces the primary selection result by 12:00 PM (Bangladesh time) on 16 November 2026 and the Final Round on 20 November 2026. It says updates will be communicated by email and SMS; this command sends email only. It does not change selection status.

## Run on the deployed server

Configure the existing delivery mailer (for example SMTP) and the correct sender address in the server environment. Then run:

```sh
php artisan migrate
php artisan ideas:send-selection-notices --dry-run
php artisan ideas:send-selection-notices
```

The command sends synchronously in submission ID order, with a one-second delay between emails. Each successful send is recorded in `selection_notice_sent_at`, also visible in the admin table. Rerunning skips recorded submissions and retries failures. Each submission receives its own message, including when an email address has submitted multiple ideas.

```sh
# Send just one pending submission per invocation:
php artisan ideas:send-selection-notices --limit=1

# Send a specific submission:
php artisan ideas:send-selection-notices --id=12

# Send up to 20 pending submissions, two seconds apart:
php artisan ideas:send-selection-notices --limit=20 --delay=2
```

A send means the configured mail transport accepted the message; it does not confirm inbox delivery. If the process stops after a transport accepts a message but before its database timestamp is saved, check the provider logs before retrying that submission. Use a shared persistent cache for command locking across servers. Log and array mailers are rejected for actual sends.

SMS notification still requires a configured SMS provider and a separate send workflow.
