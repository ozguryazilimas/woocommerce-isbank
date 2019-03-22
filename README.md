## WooComerce İşbank

Türkiye İş Bankası sanal pos üzerinden 3D destekli ödeme almayı sağlayan WooCommerce eklentisi.

### Kurulum
* Eklenti `/wp-content/plugins` dizinine eklenir.
* Admin panelinden eklenti aktifleştirilir.
* WooCommerce ayarlar sayfasından `Checkout` sekmesine tıklanır.
* Ödeme yöntemleri listesinden `Kredi Kartı` bulunur ve tıklanır.
* Ödeme yöntemini aktifleştirmek için `Türkiye İş Bankası Sanal Pos etkinleştir` seçeneği aktif edilir.
* `https://sanalpos.isbank.com.tr/` adresinden alınan bilgiler (Üye iş yeri numarası, 3D Anahtarı, API Kullanıcı Adı, API Kullanıcı Parolası) ayar sayfasındaki ilgili alanlara girilir.
* Eğer eklenti test modunda çalıştırılmak istenirse `Test ortamını aktif et` seçeneği aktif edilmeli ve aşağıda verilen test ortamı bilgileri kullanılmalıdır.

Test ortamı bilgileri: (Bu bilgiler banka tarafından sanal pos entegrasyon dokümanında verilmiştir)
```
client_id: 700655000100
storekey (işyeri anahatarı): TRPS1234

Test Kart bilgileri
Visa: 4508034508034509
Master: 5406675406675403

Son kullanma tarihi: 12/26
Güvenlik kodu: 000
3d secure parolası: a
```