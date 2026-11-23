# Mail → SMS (Atomix)

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.24462802.svg)](https://doi.org/10.5281/zenodo.24462802)

[English](#english) · [Türkçe](#türkçe)

## English

A small WordPress plugin that watches every email WordPress sends through `wp_mail()`. If it finds a Turkish mobile number in the subject or body, it sends the email text to that number as an SMS through Netgsm. I wrote it for sites using the Amelia booking plugin, so customers get their booking notifications by SMS without touching Amelia itself, but it works with anything that sends mail through `wp_mail()`.

### Requirements

- WordPress 4.7 or later (uses `wp_mail`, `wp_remote_post`, `wp_json_encode`)
- PHP 7.0 or later (the code uses the `??` operator)
- A Netgsm account with API access and an approved SMS header (msgheader). The header must be enabled for web service use in the Netgsm panel.

### Installation

1. Download this repository as a ZIP, or copy `mail2sms-atomix.php` into `wp-content/plugins/mail2sms-atomix/`.
2. In WordPress go to Plugins and activate "Mail → SMS (Atomix)".
3. Open Settings → Mail → SMS and fill in your Netgsm details.

### Configuration

Settings → Mail → SMS has these fields. They are stored in the `atomix_m2s` option.

| Field | Option key | Notes |
|---|---|---|
| Usercode | `usercode` | Netgsm user code (usually your account number) |
| Password | `password` | Netgsm API password. The field stays empty after saving; leave it empty to keep the stored password |
| Msgheader | `msgheader` | Approved sender name |
| Encoding | `encoding` | `TR` (default), `TURKCE` or `UTF-8`, passed to Netgsm as is. Any other value falls back to `TR` |
| Debug log | `log` | On by default, but only writes when `WP_DEBUG` is true. Goes to the PHP error log (`wp-content/debug.log` if `WP_DEBUG_LOG` is on). Phone numbers are masked to the last 2 digits and only the Netgsm status code is logged |

Usercode, password and msgheader are all required. If one of them is empty, no SMS is sent.

If your email template does not contain the customer's phone number, add it as an HTML comment so the plugin can find it:

```html
<!-- PHONE: {customer_phone} -->
```

### How it works

- The plugin hooks into the `wp_mail` filter with priority 99. It does not change or block the email.
- Password reset and account security mails are skipped. These are the WordPress core mails for password reset, password changed, email changed and new user login details, and any mail whose body contains a reset link (`wp-login.php?action=rp`, `action=resetpass`) or a personal data request confirmation link (`action=confirmaction`).
- It joins the subject and body and looks for a number matching `(+90|90|0) 5XX XXX XX XX`. Spaces, dashes, dots and parentheses inside the number are allowed. The number has to stand on its own, so digits inside a longer digit run are ignored. Only the first match is used.
- The number is normalized to `905XXXXXXXXX`.
- The SMS text is the subject, a dash, and the body with HTML tags removed and whitespace collapsed, cut at 900 characters (not bytes, so Turkish letters are not split).
- It sends one POST request to `https://api.netgsm.com.tr/sms/rest/v2/send` with Basic auth. A response with HTTP 200 and `"code": "00"` counts as success.

Accepted number formats:

```
0 5XX XXX XX XX
5XX XXX XX XX
+90 5XX XXX XX XX
90 5XX XXX XX XX
0532-123-45-67
(532) 123 45 67
```

### Deciding which mails send an SMS

The `mail2sms_atomix_should_send` filter gets `true` (or `false` for a security mail) and the `wp_mail` arguments. Return `false` to skip the SMS:

```php
add_filter('mail2sms_atomix_should_send', function ($send, $args) {
    // only booking mails
    return $send && stripos($args['subject'], 'randevu') !== false;
}, 10, 2);
```

### Known limitations

- Apart from the security mails above, every outgoing email that contains a mobile number triggers an SMS, including admin notices and contact form copies. Use the `mail2sms_atomix_should_send` filter if you want to narrow it down.
- A standalone 10 digit number starting with 5 (an order number, for example) still looks like a phone number.
- The SMS request runs inside `wp_mail` with a 30 second timeout, so a slow Netgsm response also delays the email.
- The password is stored unencrypted in the options table.

### License

MIT with an attribution clause. See [LICENSE](LICENSE).

Atakan Özdal · atomiXbilişim · https://atakanozdal.com.tr

## Türkçe

WordPress'in `wp_mail()` ile gönderdiği her e-postayı dinleyen küçük bir eklenti. Konu ya da gövdede Türkiye'ye ait bir cep telefonu numarası bulursa, e-postanın metnini Netgsm üzerinden o numaraya SMS olarak gönderir. Amelia randevu eklentisini kullanan siteler için yazdım; Amelia'ya dokunmadan müşteriye giden bildirimler SMS olarak da gitsin diye. Ama `wp_mail()` ile mail atan her eklentiyle çalışır.

### Gereksinimler

- WordPress 4.7 veya üstü (`wp_mail`, `wp_remote_post`, `wp_json_encode` kullanılıyor)
- PHP 7.0 veya üstü (kodda `??` operatörü var)
- API erişimi olan bir Netgsm hesabı ve onaylı bir SMS başlığı (msgheader). Başlığın Netgsm panelinde web servis kullanımına açık olması gerekir.

### Kurulum

1. Depoyu ZIP olarak indirin ya da `mail2sms-atomix.php` dosyasını `wp-content/plugins/mail2sms-atomix/` klasörüne kopyalayın.
2. WordPress'te Eklentiler sayfasından "Mail → SMS (Atomix)" eklentisini etkinleştirin.
3. Ayarlar → Mail → SMS sayfasını açıp Netgsm bilgilerinizi girin.

### Ayarlar

Ayarlar → Mail → SMS sayfasındaki alanlar `atomix_m2s` seçeneğinde saklanır.

| Alan | Anahtar | Açıklama |
|---|---|---|
| Usercode | `usercode` | Netgsm kullanıcı kodu (genelde abone numaranız) |
| Password | `password` | Netgsm API şifresi. Kaydettikten sonra alan boş görünür; boş bırakırsanız kayıtlı şifre korunur |
| Msgheader | `msgheader` | Onaylı gönderici adı |
| Encoding | `encoding` | `TR` (varsayılan), `TURKCE` veya `UTF-8`; Netgsm'e olduğu gibi gönderilir. Başka bir değer gelirse `TR` kullanılır |
| Debug log | `log` | Varsayılan olarak açık ama yalnızca `WP_DEBUG` açıkken yazar. PHP hata günlüğüne gider (`WP_DEBUG_LOG` açıksa `wp-content/debug.log`). Telefonun yalnızca son 2 hanesi görünür, Netgsm'den sadece durum kodu yazılır |

Usercode, password ve msgheader zorunludur. Biri boşsa SMS gönderilmez.

E-posta şablonunda müşterinin telefonu yoksa, eklentinin bulabilmesi için şablona HTML yorumu olarak ekleyin:

```html
<!-- PHONE: {customer_phone} -->
```

### Nasıl çalışır

- Eklenti `wp_mail` filtresine 99 önceliğiyle bağlanır. E-postayı değiştirmez, engellemez.
- Şifre sıfırlama ve hesap güvenliği mailleri atlanır. Bunlar WordPress çekirdeğinin şifre sıfırlama, şifre değişti, e-posta değişti ve yeni kullanıcı giriş bilgisi mailleri ile gövdesinde sıfırlama bağlantısı (`wp-login.php?action=rp`, `action=resetpass`) ya da kişisel veri talebi onay bağlantısı (`action=confirmaction`) geçen her maildir.
- Konu ile gövdeyi birleştirip `(+90|90|0) 5XX XXX XX XX` biçimine uyan bir numara arar. Numaranın içinde boşluk, tire, nokta ve parantez olabilir. Numara tek başına durmalı; daha uzun bir rakam dizisinin içindeki rakamlar dikkate alınmaz. Yalnızca ilk eşleşme kullanılır.
- Numara `905XXXXXXXXX` biçimine çevrilir.
- SMS metni; konu, bir tire ve HTML etiketleri temizlenip boşlukları sadeleştirilmiş gövdeden oluşur, 900 karakterden sonrası kesilir (bayt değil karakter sayılır, Türkçe harf bölünmez).
- `https://api.netgsm.com.tr/sms/rest/v2/send` adresine Basic auth ile tek bir POST isteği atılır. HTTP 200 ve `"code": "00"` dönerse gönderim başarılı sayılır.

Tanınan numara biçimleri:

```
0 5XX XXX XX XX
5XX XXX XX XX
+90 5XX XXX XX XX
90 5XX XXX XX XX
0532-123-45-67
(532) 123 45 67
```

### Hangi maillerin SMS olacağına karar vermek

`mail2sms_atomix_should_send` filtresi `true` (güvenlik mailinde `false`) değerini ve `wp_mail` argümanlarını alır. SMS gitmesin istiyorsanız `false` döndürün:

```php
add_filter('mail2sms_atomix_should_send', function ($send, $args) {
    // yalnızca randevu mailleri
    return $send && stripos($args['subject'], 'randevu') !== false;
}, 10, 2);
```

### Bilinen kısıtlar

- Yukarıdaki güvenlik mailleri dışında, içinde cep numarası geçen her e-posta SMS tetikler: yönetici bildirimleri, iletişim formu kopyaları dahil. Daraltmak isterseniz `mail2sms_atomix_should_send` filtresini kullanın.
- 5 ile başlayan, tek başına duran 10 haneli bir sayı (örneğin sipariş numarası) yine telefon sanılabilir.
- SMS isteği `wp_mail` içinde 30 saniye zaman aşımıyla çalışır; Netgsm geç yanıt verirse e-posta da gecikir.
- Şifre seçenekler tablosunda şifrelenmeden durur.

### Lisans

Atıf şartı eklenmiş MIT. Ayrıntılar için [LICENSE](LICENSE).

Atakan Özdal · atomiXbilişim · https://atakanozdal.com.tr
