# 🧑‍🏫 Dashboard Les Private Bimbelku

Halo! 👋  
Proyek ini adalah **website dashboard untuk Les Private Bimbelku**, dibuat menggunakan **Laravel + Filament**.  
Tujuannya untuk membantu pengelolaan data yang sebelumnya masih dilakukan secara manual — seperti pencatatan jadwal, kurikulum, data murid, hingga pelaporan kelas.

---

## 🚀 Fitur Utama

### 👩‍🏫 Manajemen Teacher
- Tambah, ubah, dan hapus data pengajar.  
- Atur jadwal mengajar per teacher.  
- Lihat total kelas yang diampu.

### 📚 Kurikulum
- Kelola daftar kurikulum/materi per jenjang.  
- Hubungkan dengan kelas dan teacher terkait.  

### 🎓 Database Student
- Data lengkap siswa (nama, kontak, jenjang, paket kelas).  
- Tracking progres siswa: kelas keberapa dari total kuota.  
- Status kelas (aktif, selesai, atau habis kuota).

### 📅 Jadwal Kelas
- Tampilan jadwal per hari/minggu.  
- Filter berdasarkan teacher atau student.  
- Reminder otomatis (opsional, bisa ditambah via email/WhatsApp integration).

### 📊 Report Class Student
- Rekap jumlah kelas yang sudah diambil.  
- Hitung otomatis sisa kelas berdasarkan kuota.  
- Export laporan ke PDF atau Excel.

---

## 🧩 Sistem Kuota Kelas

Sistem pembelian kelas berdasarkan **paket kuota**, misalnya:
- Paket 10 kelas  
- Paket 20 kelas  
- Paket 30 kelas  
