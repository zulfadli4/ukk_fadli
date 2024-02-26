<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$database_name = "ukk_fadli";
$connection = mysqli_connect($host, $username, $password, $database_name);

/* SIGN UP Member */
function signUp($data) {
  global $connection;
  
  $nisn = htmlspecialchars($data["nisn"]);
  $nama = htmlspecialchars(strtolower($data["nama"]));
  $password = htmlspecialchars($data["password"]);
  $kelas = htmlspecialchars($data["kelas"]);
  $jurusan = htmlspecialchars($data["jurusan"]);
  $alamat = htmlspecialchars($data["alamat"]);
  
    // cek nisn sudah ada / belum 
  $nisnResult = mysqli_query($connection, "SELECT nisn FROM member WHERE nisn = $nisn");
  if(mysqli_fetch_assoc($nisnResult)) {
    echo "<script>
    alert('Nisn sudah terdaftar, silahkan gunakan nisn lain!');
    </script>";
    return 0;
  }
  
  // Enkripsi password

  
  $querySignUp = "INSERT INTO member VALUES($nisn, '$nama', '$password', '$kelas', '$jurusan', '$alamat')";
  mysqli_query($connection, $querySignUp);
  return mysqli_affected_rows($connection);
  
}

// === FUNCTION KHUSUS ADMIN START ===

// MENAMPILKAN DATA KATEGORI BUKU
function queryReadData($dataKategori) {
  global $connection;
  $result = mysqli_query($connection, $dataKategori);
  $items = [];
  while($item = mysqli_fetch_assoc($result)) {
    $items[] = $item;
  }     
  return $items;
}

function tambahBuku($dataBuku)
{
  global $connection;

  $cover = upload();
  $idBuku = htmlspecialchars($dataBuku["id_buku"]);
  $kategoriBuku = $dataBuku["kategori"];
  $judulBuku = htmlspecialchars($dataBuku["judul"]);
  $pengarangBuku = htmlspecialchars($dataBuku["pengarang"]);
  $penerbitBuku = htmlspecialchars($dataBuku["penerbit"]);
  $tahunTerbit = date('Y-m-d', strtotime($dataBuku["thn_terbit"])); // Format date as needed
  $jumlahHalaman = $dataBuku["jml_halaman"];
  $deskripsiBuku = htmlspecialchars($dataBuku["deskripsi"]);
  $isi_buku = upload_isi();

  if (!$cover || !$isi_buku) {
    return 0;
  }

  $queryInsertDataBuku = "INSERT INTO buku (cover, id_buku, kategori, judul, pengarang, penerbit, thn_terbit, jml_halaman, deskripsi, isi_buku) 
                          VALUES('$cover', '$idBuku', '$kategoriBuku', '$judulBuku', '$pengarangBuku', '$penerbitBuku', '$tahunTerbit', $jumlahHalaman, '$deskripsiBuku', '$isi_buku')";

  mysqli_query($connection, $queryInsertDataBuku);
  return mysqli_affected_rows($connection);
}


// Function upload gambar 
function upload()
{
  $namaFile = $_FILES["cover"]["name"];
  $ukuranFile = $_FILES["cover"]["size"];
  $error = $_FILES["cover"]["error"];
  $tmpName = $_FILES["cover"]["tmp_name"];

  // cek apakah ada gambar yg diupload
  if ($error === 4) {
    echo "<script>
    alert('Silahkan upload cover buku terlebih dahulu!')
    </script>";
    return 0;
  }

  // cek kesesuaian format gambar
  $jpg = "jpg";
  $jpeg = "jpeg";
  $png = "png";
  $svg = "svg";
  $bmp = "bmp";
  $psd = "psd";
  $tiff = "tiff";
  $formatGambarValid = [$jpg, $jpeg, $png, $svg, $bmp, $psd, $tiff];
  $ekstensiGambar = explode('.', $namaFile);
  $ekstensiGambar = strtolower(end($ekstensiGambar));

  if (!in_array($ekstensiGambar, $formatGambarValid)) {
    echo "<script>
    alert('Format file tidak sesuai');
    </script>";
    return 0;
  }

  // batas ukuran file
  if ($ukuranFile > 2000000) {
    echo "<script>
    alert('Ukuran file terlalu besar!');
    </script>";
    return 0;
  }

  //generate nama file baru, agar nama file tdk ada yg sama
  $namaFileBaru = uniqid();
  $namaFileBaru .= ".";
  $namaFileBaru .= $ekstensiGambar;

  move_uploaded_file($tmpName, '../../imgDB/' . $namaFileBaru);
  return $namaFileBaru;
}

//upload isi buku dengan format pdf
function upload_isi()
{
  $namaFile = $_FILES['isi_buku']['name'];
  $x = explode('.', $namaFile);
  $ekstensiFile = strtolower(end($x));
  $ukuranFile = $_FILES['isi_buku']['size'];
  $file_tmp = $_FILES['isi_buku']['tmp_name'];

  // Lokasi Penempatan file
  $dirUpload = "../../isi-buku/";
  $linkBerkas = $dirUpload . $namaFile;

  // Validasi Format File (contoh: hanya menerima format PDF)
  if ($ekstensiFile !== 'pdf') {
    echo "<script>
      alert('Format file tidak sesuai. Hanya file PDF yang diperbolehkan.');
      </script>";
    return 0;
  }

  // Kontrol Ukuran File (contoh: maksimum 2MB)
  if ($ukuranFile > 20000000000) {
    echo "<script>
      alert('Ukuran file terlalu besar. Maksimum 2MB.');
      </script>";
    return 0;
  }

  // Menyimpan file
  if (move_uploaded_file($file_tmp, $linkBerkas)) {
    return $namaFile;
  } else {
    echo "<script>
      alert('Gagal mengunggah file. Silakan coba lagi.');
      </script>";
    return 0;
  }
}

// DELETE DATA Kategori
function deleteKategori($kategori)
{
  global $connection;
  
  $queryDeleteBuku = "DELETE FROM buku WHERE kategori = '$kategori'
  ";
  mysqli_query($connection, $queryDeleteBuku);
  
  $queryDeleteKategori = "DELETE FROM kategori_buku WHERE kategori = '$kategori'
  ";
  mysqli_query($connection, $queryDeleteKategori);

  return mysqli_affected_rows($connection);
}

function tambahadmin()
{
  global $connection;

  $nama = $_POST['nama'];
  $username = $_POST['username'];
  $password = $_POST['password'];
  $sebagai = $_POST['sebagai'];


  //cek dulu jika ada foto produk jalankan coding ini
  if ($username != "") {
    $query = "INSERT INTO user ( nama, username, password, sebagai) VALUES ( '$nama', '$username', '$password', '$sebagai')";
    $result = mysqli_query($connection, $query);
    // periska query apakah ada error
    if (!$result) {
      die("Query gagal dijalankan: " . mysqli_errno($connection) .
        " - " . mysqli_error($connection));
    } else {
      //tampil alert dan akan redirect ke halaman index.php
      //silahkan ganti index.php sesuai halaman yang akan dituju
      echo "<script>alert('Data berhasil ditambah.');window.location='pengguna.php';</script>";
    }
  }
}

// MENAMPILKAN SESUATU SESUAI DENGAN INPUTAN USER PADA * SEARCH ENGINE *
function search($keyword)
{
  // search data buku
  $querySearch = "SELECT * FROM buku 
  WHERE
  judul LIKE '%$keyword%' OR
  kategori LIKE '%$keyword%'
  ";
  return queryReadData($querySearch);
}
function searchMember ($keyword) {
     // search member terdaftar || admin
   $searchMember = "SELECT * FROM member WHERE 
   nisn LIKE '%$keyword%' OR 
   nama LIKE '%$keyword%' OR 
   jurusan LIKE '%$keyword%'
   ";
   return queryReadData($searchMember);
}
function searchAdmin ($keyword) {
  // search admin terdaftar || admin
$searchMember = "SELECT * FROM user WHERE 
username LIKE '%$keyword%'
";
return queryReadData($searchMember);
}

// DELETE DATA Buku
function deleteBuku($bukuId) {
  global $connection;
  $queryDeletePeminjaman = "DELETE FROM peminjaman WHERE id_buku = '$bukuId'
  ";
  mysqli_query($connection, $queryDeletePeminjaman);
  
  $queryDeleteBuku = "DELETE FROM buku WHERE id_buku = '$bukuId'
  ";
  mysqli_query($connection, $queryDeleteBuku);
  
  return mysqli_affected_rows($connection);
}

// UPDATE || EDIT DATA BUKU 
function updateBuku($dataBuku) {
  global $connection;

  $gambarLama = htmlspecialchars($dataBuku["coverLama"]);
  $idBuku = htmlspecialchars($dataBuku["id_buku"]);
  $kategoriBuku = $dataBuku["kategori"];
  $judulBuku = htmlspecialchars($dataBuku["judul"]);
  $pengarangBuku = htmlspecialchars($dataBuku["pengarang"]);
  $penerbitBuku = htmlspecialchars($dataBuku["penerbit"]);
  $tahunTerbit = $dataBuku["thn_terbit"];
  $jumlahHalaman = $dataBuku["jml_halaman"];
  $deskripsiBuku = htmlspecialchars($dataBuku["deskripsi"]);
  
  
  // pengecekan mengganti gambar || tidak
  if($_FILES["cover"]["error"] === 4) {
    $cover = $gambarLama;
  }else {
    $cover = upload();
  }
  // 4 === gagal upload gambar
  // 0 === berhasil upload gambar
  
  $queryUpdate = "UPDATE buku SET 
  cover = '$cover',
  id_buku = '$idBuku',
  kategori = '$kategoriBuku',
  judul = '$judulBuku',
  pengarang = '$pengarangBuku',
  penerbit = '$penerbitBuku',
  thn_terbit = '$tahunTerbit',
  jml_halaman = $jumlahHalaman,
  deskripsi = '$deskripsiBuku'
  WHERE id_buku = '$idBuku'
  ";
  
  mysqli_query($connection, $queryUpdate);
  return mysqli_affected_rows($connection);
}

// Hapus member yang terdaftar
function deleteMember($nisnMember) {
  global $connection;
  
  $deleteMember = "DELETE FROM member WHERE nisn = $nisnMember";
  mysqli_query($connection, $deleteMember);
  return mysqli_affected_rows($connection);
}
function deleteAdmin($id) {
  global $connection;
  
  $deleteAdmin = "DELETE FROM user WHERE id = $id";
  mysqli_query($connection, $deleteAdmin);
  return mysqli_affected_rows($connection);
}

// === FUNCTION KHUSUS ADMIN END ===


// === FUNCTION KHUSUS MEMBER START ===// Peminjaman BUKU
function pinjamBuku($dataBuku) {
  global $connection;
  
  $idBuku = $dataBuku["id_buku"];
  $nisn = $dataBuku["nisn"];
  $idAdmin = $dataBuku["id_user"];
  $tglPinjam = $dataBuku["tgl_pinjam"];
  $tglKembali = $dataBuku["tgl_kembali"];
  $harga = $dataBuku["harga"];
  $status = 0;

  $queryCekPeminjaman = "SELECT * FROM peminjaman WHERE id_buku = '$idBuku' AND nisn = '$nisn' AND (status = '0' OR status = '1')";
  $resultCekPeminjaman = mysqli_query($connection, $queryCekPeminjaman);
  
    if(mysqli_num_rows($resultCekPeminjaman) > 0) {
      echo "<script>alert('Anda sudah meminjam buku ini');</script>";
      return 0; 
    }
  
  $queryPinjam = "INSERT INTO peminjaman (id, id_buku, nisn, id_user, tgl_pinjam, tgl_kembali, harga, status) VALUES(null, '$idBuku', $nisn, $idAdmin, '$tglPinjam', '$tglKembali', '$harga', '$status')";
  mysqli_query($connection, $queryPinjam);
  return mysqli_affected_rows($connection);
}

// === FUNCTION KHUSUS MEMBER END ===


// Logika untuk mengubah status menjadi "Waktu habis" saat tanggal pengembalian telah lewat
function pengembalian() {
  global $connection;

  // Ambil waktu saat ini
  $waktuSekarang = date("Y-m-d");

  // Query untuk mendapatkan peminjaman yang waktu pengembaliannya sudah berakhir
  $queryPeminjamanBerakhir = "SELECT * FROM peminjaman WHERE tgl_kembali < '$waktuSekarang'";
  $resultPeminjamanBerakhir = mysqli_query($connection, $queryPeminjamanBerakhir);

  // Jika ada peminjaman yang sudah berakhir, simpan ke dalam tabel pengembalian dan hapus dari tabel peminjaman
  while ($row = mysqli_fetch_assoc($resultPeminjamanBerakhir)) {
      $idPeminjaman = $row['id'];

      // Update status peminjaman menjadi "Waktu habis"
      $queryUpdateStatus = "UPDATE peminjaman SET status = '3' WHERE id = '$idPeminjaman'";
      mysqli_query($connection, $queryUpdateStatus);
  }
}

// Batal Pinjam
function batalPinjam($Id)
{
  global $connection;

  $queryBatalPinjam = "DELETE FROM peminjaman WHERE id = '$Id'
  ";
  mysqli_query($connection, $queryBatalPinjam);

  return mysqli_affected_rows($connection);
}
