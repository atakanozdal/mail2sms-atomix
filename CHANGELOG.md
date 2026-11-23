# Changelog

## 1.0.1 - 2026-11-23

- Password reset and account security mails no longer trigger an SMS. The plugin skips the WordPress core reset, password changed, email changed and new user mails, and any mail with a reset link (`action=rp`, `action=resetpass`) or a data request confirmation link (`action=confirmaction`) in the body.
- New filter `mail2sms_atomix_should_send` (bool `$send`, array `$args`) so a site can decide which mails send an SMS.
- The phone number is only matched when it stands on its own, so digits inside a longer number are ignored. Spaces, dashes, dots and parentheses inside the number are accepted.
- The 900 limit now counts characters instead of bytes, so Turkish letters are not cut in half. Works without mbstring too.
- Settings are unslashed before saving, the encoding is checked against the three allowed values, and the form uses its own nonce name.
- The password field is a password input. It is not shown again after saving, and leaving it empty keeps the stored password.
- The log no longer contains full phone numbers or Netgsm responses. It shows the last 2 digits of the number and the Netgsm status code, and only when `WP_DEBUG` is on.

## 1.0.0 - 2025-10-27

First release.

- Hooks into `wp_mail` and looks for a Turkish mobile number in the subject and body.
- Sends the mail text as SMS through the Netgsm REST v2 API.
- Settings page under Settings → Mail → SMS: usercode, password, msgheader, encoding, debug log.
