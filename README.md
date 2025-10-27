# mail2sms-atomix
mail2sms-atomix, WordPress’ten giden e-postaları (WP Mail / SMTP üzerinden de olsa) filtreler, içerikteki telefon numarasını bulursa mesajı sadeleştirip Netgsm üzerinden SMS olarak iletir. Böylece Amelia gibi eklentilerin e-posta bildirimleri otomatik SMS’e dönüştürülebilir.
🇹🇷 Amaç

mail2sms-atomix, WordPress’ten giden e-postaları (WP Mail / SMTP üzerinden de olsa) filtreler, içerikteki telefon numarasını bulursa mesajı sadeleştirip Netgsm üzerinden SMS olarak iletir. Böylece Amelia gibi eklentilerin e-posta bildirimleri otomatik SMS’e dönüştürülebilir.

🇬🇧 Purpose

mail2sms-atomix filters outgoing WordPress emails (even with SMTP plugins). If a Turkish phone number is detected in subject/body, it sends a compacted text as SMS via Netgsm. Perfect to mirror Amelia’s email notifications as SMS.

✨ Özellikler / Features

wp_mail filtrelemesi (SMTP ile uyumlu)

TR telefon deseni ayrıştırma + normalize (90XXXXXXXXXX)

Uzun HTML mail → sade metne indirgeme

Admin’de Netgsm ayarları + deneme gönderim

Ayrıntılı debug.log

🚀 Kurulum / Installation

ZIP olarak kur: mail2sms-atomix.zip → Etkinleştir

Ayarlar → Mail → SMS Bridge sayfasından Netgsm bilgilerini gir

Amelia (veya başka eklenti) e-postası telefon numarasını içeriyorsa SMS tetiklenir

İpuçu: Mail şablonuna şunu ekleyebilirsin:
<!-- PHONE: 05XXXXXXXXX -->
(Telefon görünmüyorsa bile eklenti bu ipucunu yakalar.)

🔎 Telefon Ayrıştırma / Phone Extraction

Regex: (\+?90|0)?\s*5\d{2}\s*\d{3}\s*\d{2}\s*\d{2}

Bulunan değerler 90XXXXXXXXXX formatına normalize edilir

Mesaj HTML ise strip_tags ile metne indirgenir, uzunluk sınırlandırılır

🧪 Test / Testing

Ayarlar sayfasındaki Hızlı Test ile SMS gönder

Kendine test maili atıp konu/gövdeye telefon ekle

⚠️ Dikkat / Caveats

Eğer e-postada telefon yoksa SMS tetiklenmez

Bazı SMTP/antispam eklentileri wp_mail filtresini farklı sırada çalıştırabilir; bu durumda priority artırılabilir

Çok uzun HTML e-postalar kısaltılır

🧾 Sürüm Notları / Changelog

v1.0.0: İlk sürüm — Email→SMS köprüsü, panel ayarları, test, debug

v1.1.0: Regex ve normalize iyileştirmeleri, karakter seti seçenekleri

📄 Lisans / License

MIT — ayrıntı için LICENSE dosyasına bakın.
