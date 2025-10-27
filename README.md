
```md
# Mail → SMS Bridge (for Amelia)
**Developer:** Atakan Özdal  
**License:** MIT  

WordPress üzerinden gönderilen e-postaları dinler.  
Eğer mesaj içinde **telefon numarası** bulunursa → Netgsm SMS gönderir.  
Amelia’nın mail bildirimlerini SMS’e dönüştüren köprü çözümüdür ✅

---

## 🇹🇷 Özellikler
✅ wp_mail() filtresi ile otomatik tetikleme  
✅ Telefon no ayrıştırma (Regex)  
✅ HTML → Plain text dönüşümü  
✅ 1000 karakter limit  
✅ Debug log desteği  

---

## ⚙️ Kurulum (TR)

1. ZIP yükleyin → Etkinleştirin  
2. Ayarlar → Mail → SMS Bridge  
3. Netgsm bilgilerini girin  
4. Amelia’dan test randevusu oluşturun  
🟢 Mail gönderiliyorsa → SMS de gönderilir

---

## 📞 Desteklenen telefon numarası formatları
0 5XX XXX XX XX
5XX XXX XX XX
+90 5XX XXX XX XX
90 5XX XXX XX XX


---

## 🇬🇧 English Quick Guide
1. Install & activate plugin  
2. Configure:

Settings → Mail → SMS Bridge

3. Any outgoing email containing a Turkish phone number → SMS sent ✅

---

## 🔍 Debug Log

/wp-content/debug.log


---

## 📌 Changelog
| Version | Notes |
|--------|------|
| 1.0.0 | First release |
| 1.1.0 | Regex optimization & debug clarity |

---

## 📄 License
MIT — © 2025 Atakan Özdal
