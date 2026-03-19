## Autentikasi

4. Setup Laravel Breeze untuk auth user
5. Buat role/permission (Admin, Staff, Manager)

---

## Modul ERP (Database + API)

6. Modul Inventory — tabel `products`, `warehouses`, `stock_movements`
7. Modul Sales/CRM — tabel `customers`, `quotations`, `sales_orders`
8. Modul Purchasing — tabel `suppliers`, `purchase_orders`
9. Modul HRM — tabel `employees`, `attendance`, `reports`
10. Modul Finance — tabel `transactions`, `expense_categories`

---

## Integrasi Gemini (Inti Aplikasi)

11. Buat `GeminiService` — wrapper untuk komunikasi ke Gemini API
12. Definisikan **Function Calling tools** untuk setiap modul (check_inventory, create_po, get_finance_summary, dll)
13. Buat middleware validasi — intercept sebelum Gemini write ke DB
14. Implementasi context/session management per user (token efficiency)
15. Buat `ChatController` — handle percakapan masuk & routing ke fungsi yang tepat

---

## UI Chat

16. Buat halaman chat (blade/livewire/vue)
17. Tampilkan history percakapan per sesi
18. Render response Gemini dengan format tabel/list jika ada data

---

## Fitur Tambahan

19. Notifikasi/reminder otomatis (stok di bawah limit, laporan belum masuk)
20. Dashboard ringkasan (sales, stok, keuangan)
21. Export laporan (PDF/Excel)

---

Mau mulai dari mana? Saran saya mulai dari task 1-5 dulu (setup + auth), lalu lanjut ke modul DB, baru ke integrasi Gemini.