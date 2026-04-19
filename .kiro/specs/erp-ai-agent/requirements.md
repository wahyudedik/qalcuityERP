# Requirements Document

## Introduction

Fitur ini mengevolusi AI Chat yang sudah ada pada Qalcuity ERP menjadi **ERP AI Agent** — sebuah agen cerdas yang tidak hanya menjawab pertanyaan, tetapi juga memahami konteks bisnis secara mendalam, mengeksekusi task multi-langkah secara otonom, menganalisis data lintas modul ERP (keuangan, inventory, HR/payroll, CRM, project management, dll), memberikan rekomendasi proaktif berbasis kondisi bisnis real-time, dan berinteraksi penuh dengan seluruh modul ERP yang ada.

Sistem ini dibangun di atas infrastruktur yang sudah ada: GeminiService (Google Gemini), ToolRegistry, ChatController, AiMemoryService, dan ERP Tools per modul — dengan evolusi signifikan pada lapisan orkestrasi, perencanaan, memori jangka panjang, dan eksekusi otonom.

---

## Glossary

- **ERP_Agent**: Sistem AI agent utama yang mengorkestrasi seluruh kemampuan agen dalam konteks ERP multi-tenant.
- **Agent_Planner**: Komponen yang memecah goal kompleks menjadi rencana langkah-langkah (plan) yang dapat dieksekusi.
- **Agent_Executor**: Komponen yang mengeksekusi setiap langkah dalam plan, memanggil tools ERP yang relevan.
- **Agent_Memory**: Sistem memori persisten yang menyimpan konteks bisnis, preferensi, dan pola penggunaan per tenant/user.
- **Tool_Registry**: Registri tools ERP yang sudah ada, diperluas dengan tools baru untuk kemampuan agent.
- **ERP_Context**: Snapshot kondisi bisnis tenant saat ini (KPI, anomali, status modul aktif, dll) yang diinjeksikan ke setiap sesi agent.
- **Proactive_Insight**: Rekomendasi atau peringatan yang diinisiasi oleh ERP_Agent tanpa permintaan eksplisit dari user.
- **Agent_Session**: Sesi percakapan agent yang menyimpan riwayat, plan aktif, dan state eksekusi.
- **Multi_Step_Task**: Task yang memerlukan lebih dari satu tool call atau langkah untuk diselesaikan.
- **Tenant**: Perusahaan/organisasi yang menggunakan Qalcuity ERP dalam lingkungan multi-tenant.
- **Approval_Gate**: Titik dalam eksekusi agent di mana user harus memberikan konfirmasi sebelum aksi write dijalankan.
- **Agent_Audit_Log**: Catatan lengkap setiap aksi yang dieksekusi oleh ERP_Agent beserta konteks dan hasilnya.
- **Cross_Module_Query**: Query yang mengambil dan mengkorelasikan data dari lebih dari satu modul ERP.
- **Skill**: Kemampuan spesifik ERP_Agent yang diimplementasikan sebagai kumpulan tools dan prompt khusus per domain bisnis.

---

## Requirements

### Requirement 1: Perencanaan dan Eksekusi Task Multi-Langkah

**User Story:** Sebagai pengguna ERP, saya ingin memberikan instruksi kompleks kepada AI Agent dalam bahasa natural, sehingga agent dapat merencanakan dan mengeksekusi serangkaian langkah secara otonom tanpa saya harus memandu setiap langkahnya.

#### Acceptance Criteria

1. WHEN seorang user mengirimkan instruksi yang memerlukan lebih dari satu langkah eksekusi, THE Agent_Planner SHALL menguraikan instruksi tersebut menjadi rencana terurut berisi maksimal 10 langkah yang dapat dieksekusi.
2. WHEN Agent_Planner menghasilkan rencana eksekusi, THE ERP_Agent SHALL menampilkan ringkasan rencana kepada user sebelum eksekusi dimulai.
3. WHEN sebuah langkah dalam rencana menghasilkan output, THE Agent_Executor SHALL menggunakan output tersebut sebagai input untuk langkah berikutnya dalam rencana yang sama.
4. WHEN eksekusi sebuah langkah gagal, THE Agent_Executor SHALL menghentikan eksekusi rencana, mencatat kegagalan pada Agent_Audit_Log, dan menyampaikan pesan kegagalan beserta langkah yang berhasil kepada user.
5. WHEN sebuah langkah dalam rencana adalah operasi write (create/update/delete), THE ERP_Agent SHALL menampilkan Approval_Gate kepada user dan menunggu konfirmasi eksplisit sebelum mengeksekusi langkah tersebut.
6. THE Agent_Planner SHALL mendukung instruksi dalam Bahasa Indonesia dan Bahasa Inggris.

---

### Requirement 2: Konteks Bisnis ERP Real-Time

**User Story:** Sebagai pengguna ERP, saya ingin AI Agent memahami kondisi bisnis saya saat ini secara otomatis, sehingga setiap respons dan rekomendasi yang diberikan relevan dengan situasi bisnis aktual tanpa saya harus menjelaskan konteks dari awal.

#### Acceptance Criteria

1. WHEN sebuah Agent_Session dimulai, THE ERP_Agent SHALL membangun ERP_Context yang mencakup: ringkasan KPI utama (pendapatan bulan ini, stok kritis, piutang jatuh tempo, jumlah karyawan aktif), daftar modul yang aktif untuk tenant tersebut, dan periode akuntansi yang sedang berjalan.
2. WHEN ERP_Context dibangun, THE ERP_Agent SHALL menyelesaikan proses pembangunan konteks dalam waktu tidak lebih dari 3 detik.
3. WHILE sebuah Agent_Session aktif, THE ERP_Agent SHALL menyertakan ERP_Context yang relevan pada setiap permintaan ke model AI.
4. WHEN data ERP_Context berubah secara signifikan selama sesi berlangsung (misalnya stok mencapai batas kritis), THE ERP_Agent SHALL memperbarui ERP_Context tanpa memerlukan restart sesi.
5. THE ERP_Agent SHALL mengisolasi ERP_Context sepenuhnya per Tenant sehingga data satu tenant tidak dapat diakses oleh tenant lain.

---

### Requirement 3: Analisis Data Lintas Modul (Cross-Module Analysis)

**User Story:** Sebagai manajer bisnis, saya ingin AI Agent dapat menganalisis dan mengkorelasikan data dari berbagai modul ERP sekaligus, sehingga saya mendapatkan insight bisnis yang holistik tanpa harus membuka banyak laporan secara manual.

#### Acceptance Criteria

1. WHEN user meminta analisis yang melibatkan data dari lebih dari satu modul ERP, THE ERP_Agent SHALL mengeksekusi Cross_Module_Query dengan mengambil data dari semua modul yang relevan secara paralel.
2. WHEN hasil Cross_Module_Query diterima, THE ERP_Agent SHALL mengkorelasikan data antar modul dan menyajikan insight terintegrasi dalam satu respons yang kohesif.
3. THE ERP_Agent SHALL mendukung Cross_Module_Query untuk kombinasi modul berikut: Akuntansi + Inventory, Akuntansi + HRM/Payroll, Penjualan + CRM + Inventory, HRM + Payroll + Absensi, dan Project + Keuangan.
4. WHEN Cross_Module_Query dieksekusi, THE ERP_Agent SHALL menyelesaikan pengambilan data dalam waktu tidak lebih dari 5 detik untuk query yang melibatkan hingga 3 modul.
5. IF data yang diperlukan untuk Cross_Module_Query tidak tersedia atau modul tidak aktif untuk tenant tersebut, THEN THE ERP_Agent SHALL menyampaikan informasi modul mana yang tidak tersedia dan menyajikan analisis parsial dari modul yang tersedia.

---

### Requirement 4: Rekomendasi Proaktif Berbasis Kondisi Bisnis

**User Story:** Sebagai pemilik bisnis, saya ingin AI Agent secara proaktif memberitahu saya tentang kondisi bisnis yang memerlukan perhatian, sehingga saya dapat mengambil tindakan sebelum masalah berkembang.

#### Acceptance Criteria

1. THE ERP_Agent SHALL menganalisis kondisi bisnis tenant secara terjadwal setiap 6 jam dan menghasilkan Proactive_Insight jika ditemukan kondisi yang memerlukan perhatian.
2. WHEN kondisi berikut terdeteksi, THE ERP_Agent SHALL menghasilkan Proactive_Insight: stok produk di bawah reorder point, piutang melewati jatuh tempo lebih dari 7 hari, anggaran yang terpakai lebih dari 90%, karyawan dengan kontrak yang berakhir dalam 30 hari, dan invoice yang belum dibayar lebih dari nilai threshold yang dikonfigurasi tenant.
3. WHEN Proactive_Insight dihasilkan, THE ERP_Agent SHALL menyertakan: deskripsi kondisi yang terdeteksi, dampak bisnis yang diperkirakan, dan minimal satu rekomendasi tindakan yang dapat langsung dieksekusi.
4. WHEN user membuka dashboard atau memulai Agent_Session baru, THE ERP_Agent SHALL menampilkan Proactive_Insight yang belum dibaca oleh user tersebut.
5. WHEN user menandai sebuah Proactive_Insight sebagai "diabaikan" atau "sudah ditangani", THE ERP_Agent SHALL tidak menampilkan insight yang sama untuk kondisi yang sama dalam 24 jam berikutnya.
6. WHERE fitur notifikasi push aktif untuk tenant, THE ERP_Agent SHALL mengirimkan Proactive_Insight melalui push notification untuk kondisi dengan tingkat urgensi tinggi.

---

### Requirement 5: Memori Jangka Panjang dan Pembelajaran Kontekstual

**User Story:** Sebagai pengguna ERP yang rutin, saya ingin AI Agent mengingat preferensi, pola kerja, dan konteks bisnis saya dari waktu ke waktu, sehingga interaksi menjadi semakin personal dan efisien.

#### Acceptance Criteria

1. THE Agent_Memory SHALL menyimpan preferensi user yang terdeteksi dari pola interaksi, termasuk: format laporan yang disukai, modul yang paling sering diakses, rentang tanggal yang sering digunakan, dan bahasa yang digunakan.
2. WHEN user memulai Agent_Session baru, THE ERP_Agent SHALL memuat konteks dari Agent_Memory dan menyertakannya dalam system prompt untuk personalisasi respons.
3. WHEN user berhasil menyelesaikan sebuah Multi_Step_Task, THE Agent_Memory SHALL menyimpan pola task tersebut sebagai template yang dapat direkomendasikan kembali di masa depan.
4. THE Agent_Memory SHALL mempertahankan data memori per kombinasi tenant_id dan user_id, sehingga memori satu user tidak mempengaruhi user lain dalam tenant yang sama.
5. WHEN data dalam Agent_Memory tidak diakses selama lebih dari 90 hari, THE Agent_Memory SHALL menurunkan confidence_score data tersebut sebesar 50% dan menghapus data dengan confidence_score di bawah 0.1.
6. THE ERP_Agent SHALL memungkinkan user untuk melihat dan menghapus data Agent_Memory miliknya melalui antarmuka pengaturan.

---

### Requirement 6: Eksekusi Aksi ERP dengan Validasi dan Audit

**User Story:** Sebagai pengguna ERP, saya ingin AI Agent dapat mengeksekusi aksi nyata di sistem ERP (membuat jurnal, update stok, dll) dengan jaminan bahwa setiap aksi tervalidasi dan tercatat, sehingga saya dapat mempercayai agent untuk menjalankan operasional bisnis.

#### Acceptance Criteria

1. WHEN ERP_Agent akan mengeksekusi operasi write pada modul ERP manapun, THE ERP_Agent SHALL terlebih dahulu memvalidasi parameter aksi menggunakan aturan validasi yang sama dengan yang digunakan pada antarmuka manual.
2. WHEN validasi parameter aksi gagal, THE ERP_Agent SHALL menampilkan pesan error yang spesifik dan actionable kepada user tanpa mengeksekusi aksi tersebut.
3. WHEN sebuah aksi write berhasil dieksekusi oleh ERP_Agent, THE Agent_Audit_Log SHALL mencatat: identitas user, tenant_id, nama aksi, parameter yang digunakan, timestamp, dan hasil eksekusi.
4. THE ERP_Agent SHALL mendukung eksekusi aksi pada modul berikut: pembuatan jurnal akuntansi, penyesuaian stok inventory, pembuatan invoice penjualan, pembuatan purchase order, pembaruan data karyawan, dan pembuatan task project.
5. IF user tidak memiliki permission untuk mengeksekusi aksi tertentu berdasarkan role yang ditetapkan, THEN THE ERP_Agent SHALL menolak eksekusi dan menginformasikan permission yang diperlukan kepada user.
6. THE ERP_Agent SHALL mendukung mekanisme undo untuk aksi write yang dieksekusi dalam 5 menit terakhir, selama aksi tersebut belum diproses lebih lanjut oleh sistem.

---

### Requirement 7: Antarmuka Agent yang Responsif dan Informatif

**User Story:** Sebagai pengguna ERP, saya ingin melihat progress eksekusi agent secara real-time, sehingga saya tahu apa yang sedang dilakukan agent dan dapat mengintervensi jika diperlukan.

#### Acceptance Criteria

1. WHEN ERP_Agent mengeksekusi Multi_Step_Task, THE ERP_Agent SHALL mengirimkan pembaruan status secara streaming untuk setiap langkah yang sedang dieksekusi, termasuk nama langkah dan status (sedang berjalan / selesai / gagal).
2. WHEN ERP_Agent sedang memproses permintaan, THE ERP_Agent SHALL menampilkan indikator "sedang berpikir" atau "sedang mengeksekusi" kepada user.
3. WHEN ERP_Agent menyelesaikan eksekusi Multi_Step_Task, THE ERP_Agent SHALL menyajikan ringkasan hasil yang mencakup: jumlah langkah yang berhasil, jumlah langkah yang gagal, dan daftar aksi yang telah dieksekusi beserta hasilnya.
4. THE ERP_Agent SHALL mendukung pembatalan eksekusi Multi_Step_Task yang sedang berjalan oleh user kapan saja sebelum langkah terakhir selesai.
5. WHEN user membatalkan eksekusi Multi_Step_Task, THE ERP_Agent SHALL menghentikan eksekusi langkah berikutnya dan menyajikan status langkah-langkah yang sudah selesai kepada user.
6. THE ERP_Agent SHALL merespons setiap pesan user dalam waktu tidak lebih dari 2 detik untuk memberikan acknowledgment awal, meskipun proses eksekusi penuh belum selesai.

---

### Requirement 8: Skill Domain Bisnis Spesifik

**User Story:** Sebagai pengguna modul ERP tertentu, saya ingin AI Agent memiliki pemahaman mendalam tentang domain bisnis modul tersebut, sehingga rekomendasi dan analisis yang diberikan relevan secara kontekstual dan menggunakan terminologi bisnis yang tepat.

#### Acceptance Criteria

1. THE ERP_Agent SHALL memiliki Skill khusus untuk domain berikut: Akuntansi & Keuangan, Inventory & Gudang, HRM & Payroll, Penjualan & CRM, dan Project Management.
2. WHEN user berinteraksi dalam konteks modul tertentu, THE ERP_Agent SHALL secara otomatis mengaktifkan Skill yang relevan berdasarkan deteksi intent dari pesan user.
3. WHEN Skill Akuntansi & Keuangan aktif, THE ERP_Agent SHALL memahami dan menggunakan terminologi akuntansi standar Indonesia (debit/kredit, neraca, laba rugi, arus kas, jurnal umum, buku besar) dalam respons.
4. WHEN Skill HRM & Payroll aktif, THE ERP_Agent SHALL memahami regulasi ketenagakerjaan Indonesia yang relevan (UMR, BPJS Ketenagakerjaan, BPJS Kesehatan, PPh 21) dalam memberikan rekomendasi.
5. WHEN Skill Inventory aktif, THE ERP_Agent SHALL memahami metode costing yang digunakan tenant (FIFO atau Average) dan menggunakannya sebagai dasar analisis nilai stok.
6. WHERE modul industri khusus (Healthcare, Manufaktur, Telecom, dll) aktif untuk tenant, THE ERP_Agent SHALL mengaktifkan Skill tambahan yang sesuai dengan domain industri tersebut.

---

### Requirement 9: Keamanan, Isolasi, dan Kepatuhan Multi-Tenant

**User Story:** Sebagai administrator sistem ERP multi-tenant, saya ingin memastikan bahwa ERP AI Agent beroperasi dalam batas keamanan yang ketat, sehingga data setiap tenant terlindungi dan aksi agent dapat diaudit sepenuhnya.

#### Acceptance Criteria

1. THE ERP_Agent SHALL memastikan setiap query data dan eksekusi aksi selalu disertai tenant_id yang valid dan terverifikasi, sehingga tidak ada akses lintas tenant yang dapat terjadi.
2. THE ERP_Agent SHALL menerapkan batas kuota penggunaan AI per tenant sesuai dengan subscription plan yang aktif, dan menolak permintaan yang melebihi kuota dengan pesan yang informatif.
3. WHEN ERP_Agent menerima instruksi yang berpotensi merusak data (bulk delete, modifikasi data historis yang sudah dikunci), THE ERP_Agent SHALL menolak eksekusi dan menjelaskan alasan penolakan kepada user.
4. THE Agent_Audit_Log SHALL disimpan dengan retensi minimal 1 tahun dan tidak dapat dimodifikasi atau dihapus oleh user biasa.
5. THE ERP_Agent SHALL tidak menyertakan data sensitif (password, API key, data pribadi karyawan di luar konteks yang diizinkan) dalam respons atau log yang dapat diakses oleh user yang tidak berwenang.
6. WHEN ERP_Agent mendeteksi pola penggunaan yang mencurigakan (misalnya eksekusi aksi write dalam jumlah besar dalam waktu singkat), THE ERP_Agent SHALL membatasi laju eksekusi dan mengirimkan notifikasi kepada administrator tenant.

---

### Requirement 10: Integrasi dengan Workflow dan Automation yang Ada

**User Story:** Sebagai pengguna ERP yang sudah menggunakan fitur Automation Builder, saya ingin ERP AI Agent dapat berinteraksi dengan workflow otomatis yang sudah ada, sehingga agent dapat memicu atau merespons workflow bisnis yang telah dikonfigurasi.

#### Acceptance Criteria

1. WHEN user meminta ERP_Agent untuk memicu sebuah workflow yang sudah dikonfigurasi di Automation Builder, THE ERP_Agent SHALL mengeksekusi trigger workflow tersebut dengan parameter yang sesuai.
2. WHEN sebuah workflow yang dipicu oleh ERP_Agent selesai dieksekusi, THE ERP_Agent SHALL menerima notifikasi hasil dan menyampaikannya kepada user dalam Agent_Session yang aktif.
3. THE ERP_Agent SHALL dapat membaca daftar workflow yang tersedia untuk tenant dan mendeskripsikannya kepada user ketika diminta.
4. WHEN ERP_Agent mengeksekusi aksi yang memerlukan approval berdasarkan konfigurasi Approval Workflow yang ada, THE ERP_Agent SHALL menginisiasi proses approval dan menginformasikan user bahwa aksi menunggu persetujuan dari approver yang berwenang.
5. IF sebuah workflow yang akan dipicu oleh ERP_Agent dalam kondisi nonaktif, THEN THE ERP_Agent SHALL menginformasikan status workflow kepada user dan menawarkan alternatif tindakan yang tersedia.
