-- ============================================================
-- Green-Fund — menyelaraskan data donasi dengan akun user
-- Jalan di SQLite (app) maupun MySQL/MariaDB
-- Jalankan berurutan, lalu cek bagian VERIFIKASI di akhir
-- ============================================================


-- ------------------------------------------------------------
-- 1. Isi user_id yang masih NULL lewat email yang sama
-- ------------------------------------------------------------
UPDATE donasi
SET user_id = (SELECT u.id FROM users u WHERE u.email = donasi.email)
WHERE user_id IS NULL
  AND EXISTS (SELECT 1 FROM users u WHERE u.email = donasi.email);


-- ------------------------------------------------------------
-- 2. Perbaiki user_id yang tidak cocok (mis. donatur ganti email)
-- ------------------------------------------------------------
UPDATE donasi
SET user_id = (SELECT u.id FROM users u WHERE u.email = donasi.email)
WHERE user_id IS NOT NULL
  AND EXISTS (SELECT 1 FROM users u WHERE u.email = donasi.email)
  AND user_id <> (SELECT u.id FROM users u WHERE u.email = donasi.email);


-- ------------------------------------------------------------
-- 3. Samakan nama_donatur dengan nama akun terbaru
-- ------------------------------------------------------------
UPDATE donasi
SET nama_donatur = (SELECT u.name FROM users u WHERE u.id = donasi.user_id)
WHERE user_id IS NOT NULL
  AND EXISTS (SELECT 1 FROM users u
              WHERE u.id = donasi.user_id AND u.name <> donasi.nama_donatur);


-- ------------------------------------------------------------
-- 4. Samakan email donasi dengan email akun
-- ------------------------------------------------------------
UPDATE donasi
SET email = (SELECT u.email FROM users u WHERE u.id = donasi.user_id)
WHERE user_id IS NOT NULL
  AND EXISTS (SELECT 1 FROM users u
              WHERE u.id = donasi.user_id AND u.email <> donasi.email);


-- ------------------------------------------------------------
-- 5. Hitung ulang subtotal & total dari harga kampanye
--    total = (jumlah_pohon * harga_per_pohon) + biaya_admin + kode_unik
-- ------------------------------------------------------------
UPDATE donasi
SET subtotal = jumlah_pohon * (SELECT k.harga_per_pohon FROM kampanye k WHERE k.id = donasi.kampanye_id),
    total    = jumlah_pohon * (SELECT k.harga_per_pohon FROM kampanye k WHERE k.id = donasi.kampanye_id)
               + biaya_admin + kode_unik
WHERE EXISTS (SELECT 1 FROM kampanye k WHERE k.id = donasi.kampanye_id);


-- ============================================================
-- VERIFIKASI A — ringkasan donasi per user
-- ============================================================
SELECT
    u.id                                                        AS user_id,
    u.name                                                      AS nama,
    u.email,
    u.role,
    COUNT(d.id)                                                 AS total_transaksi,
    SUM(CASE WHEN d.status = 'terverifikasi' THEN 1 ELSE 0 END) AS transaksi_lunas,
    SUM(CASE WHEN d.status = 'pending'       THEN 1 ELSE 0 END) AS transaksi_pending,
    SUM(CASE WHEN d.status = 'ditolak'       THEN 1 ELSE 0 END) AS transaksi_ditolak,
    COALESCE(SUM(CASE WHEN d.status = 'terverifikasi' THEN d.jumlah_pohon ELSE 0 END), 0) AS pohon_lunas,
    COALESCE(SUM(CASE WHEN d.status = 'terverifikasi' THEN d.total       ELSE 0 END), 0) AS rupiah_lunas,
    COUNT(DISTINCT d.kampanye_id)                               AS kampanye_didukung,
    MAX(d.created_at)                                           AS donasi_terakhir
FROM users u
LEFT JOIN donasi d ON d.user_id = u.id
GROUP BY u.id, u.name, u.email, u.role
ORDER BY rupiah_lunas DESC;


-- ============================================================
-- VERIFIKASI B — donasi tanpa akun (guest / email tak terdaftar)
-- NORMAL untuk donasi sebelum aturan wajib-login.
-- ============================================================
SELECT d.id, d.kode, d.email, d.nama_donatur, d.status
FROM donasi d
WHERE d.user_id IS NULL;


-- ============================================================
-- VERIFIKASI C — akun duplikat (nama sama, email beda)
-- Gabungkan manual dulu kalau ada, lalu ulangi query di atas.
-- ============================================================
SELECT LOWER(name) AS nama, COUNT(*) AS jumlah_akun,
       GROUP_CONCAT(email) AS daftar_email
FROM users
GROUP BY LOWER(name)
HAVING COUNT(*) > 1;


-- ============================================================
-- VERIFIKASI D — total tidak konsisten (harus kosong)
-- ============================================================
SELECT d.id, d.kode, d.subtotal, d.total, d.biaya_admin, d.kode_unik,
       d.jumlah_pohon * k.harga_per_pohon AS subtotal_seharusnya
FROM donasi d
JOIN kampanye k ON k.id = d.kampanye_id
WHERE d.subtotal <> d.jumlah_pohon * k.harga_per_pohon
   OR d.total <> (d.jumlah_pohon * k.harga_per_pohon) + d.biaya_admin + d.kode_unik;
