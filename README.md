# Mail → SMS (Atomix)

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
| Password | `password` | Netgsm API password |
| Msgheader | `msgheader` | Approved sender name |
| Encoding | `encoding` | `TR` (default), `TURKCE` or `UTF-8`, passed to Netgsm as is |
| Debug log | `log` | On by default. Writes to the PHP error log (`wp-content/debug.log` if `WP_DEBUG_LOG` is on) |

Usercode, password and msgheader are all required. If one of them is empty, no SMS is sent.

If your email template does not contain the customer's phone number, add it as an HTML comment so the plugin can find it:

```html
<!-- PHONE: {customer_phone} -->
```

### How it works

- The plugin hooks into the `wp_mail` filter with priority 99. It does not change or block the email.
- It joins the subject and body and looks for a number matching `(+90|90|0) 5XX XXX XX XX`, spaces optional. Only the first match is used.
- The number is normalized to `905XXXXXXXXX`.
- The SMS text is the subject, a dash, and the body with HTML tags removed and whitespace collapsed, cut at 900 bytes.
- It sends one POST request to `https://api.netgsm.com.tr/sms/rest/v2/send` with Basic auth. A response with HTTP 200 and `"code": "00"` counts as success.

Accepted number formats:

```
0 5XX XXX XX XX
5XX XXX XX XX
+90 5XX XXX XX XX
90 5XX XXX XX XX
```

### Known limitations

- Every outgoing email that contains a mobile number triggers an SMS, including password reset mails, admin notices and contact form copies. There is no filter by recipient, subject or sender.
- Numbers written with dashes, dots or parentheses (`0532-123-45-67`, `(532) 123 45 67`) are not matched.
- A long digit sequence that happens to contain `5XXXXXXXXX` can be picked up as a phone number.
- The 900 limit is applied with `substr`, so it counts bytes and can cut a Turkish character in half.
- The SMS request runs inside `wp_mail` with a 30 second timeout, so a slow Netgsm response also delays the email.
- With debug log on, phone numbers and full Netgsm responses are written to the log.
- The password field is a plain text input and the password is stored unencrypted in the options table.

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
| Password | `password` | Netgsm API şifresi |
| Msgheader | `msgheader` | Onaylı gönderici adı |
| Encoding | `encoding` | `TR` (varsayılan), `TURKCE` veya `UTF-8`; Netgsm'e olduğu gibi gönderilir |
| Debug log | `log` | Varsayılan olarak açık. PHP hata günlüğüne yazar (`WP_DEBUG_LOG` açıksa `wp-content/debug.log`) |

Usercode, password ve msgheader zorunludur. Biri boşsa SMS gönderilmez.

E-posta şablonunda müşterinin telefonu yoksa, eklentinin bulabilmesi için şablona HTML yorumu olarak ekleyin:

```html
<!-- PHONE: {customer_phone} -->
```

### Nasıl çalışır

- Eklenti `wp_mail` filtresine 99 önceliğiyle bağlanır. E-postayı değiştirmez, engellemez.
- Konu ile gövdeyi birleştirip `(+90|90|0) 5XX XXX XX XX` biçimine uyan bir numara arar, boşluklar isteğe bağlıdır. Yalnızca ilk eşleşme kullanılır.
- Numara `905XXXXXXXXX` biçimine çevrilir.
- SMS metni; konu, bir tire ve HTML etiketleri temizlenip boşlukları sadeleştirilmiş gövdeden oluşur, 900 bayttan sonrası kesilir.
- `https://api.netgsm.com.tr/sms/rest/v2/send` adresine Basic auth ile tek bir POST isteği atılır. HTTP 200 ve `"code": "00"` dönerse gönderim başarılı sayılır.

Tanınan numara biçimleri:

```
0 5XX XXX XX XX
5XX XXX XX XX
+90 5XX XXX XX XX
90 5XX XXX XX XX
```

### Bilinen kısıtlar

- İçinde cep numarası geçen her e-posta SMS tetikler: şifre sıfırlama mailleri, yönetici bildirimleri, iletişim formu kopyaları dahil. Alıcıya, konuya ya da gönderene göre süzme yok.
- Tire, nokta ya da parantezle yazılmış numaralar (`0532-123-45-67`, `(532) 123 45 67`) yakalanmaz.
- İçinde `5XXXXXXXXX` geçen uzun bir rakam dizisi telefon numarası sanılabilir.
- 900 sınırı `substr` ile uygulandığı için bayt sayar ve bir Türkçe karakteri ortadan bölebilir.
- SMS isteği `wp_mail` içinde 30 saniye zaman aşımıyla çalışır; Netgsm geç yanıt verirse e-posta da gecikir.
- Debug log açıkken telefon numaraları ve Netgsm yanıtlarının tamamı günlüğe yazılır.
- Şifre alanı düz metin kutusudur, şifre seçenekler tablosunda şifrelenmeden durur.

### Lisans

Atıf şartı eklenmiş MIT. Ayrıntılar için [LICENSE](LICENSE).

Atakan Özdal · atomiXbilişim · https://atakanozdal.com.tr
