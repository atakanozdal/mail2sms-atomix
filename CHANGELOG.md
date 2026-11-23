# Changelog

## 1.0.0 - 2025-10-27

First release.

- Hooks into `wp_mail` and looks for a Turkish mobile number in the subject and body.
- Sends the mail text as SMS through the Netgsm REST v2 API.
- Settings page under Settings → Mail → SMS: usercode, password, msgheader, encoding, debug log.
