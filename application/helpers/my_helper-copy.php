<?php
defined('BASEPATH') or exit('No direct script access allowed');
function cek_aktif()
{
	$CI =& get_instance();
	$sesspre = $CI->config->item('session_name_prefix');
	$aktif = $CI->session->userdata($sesspre . "valid");
	$username = $CI->session->userdata($sesspre . "user");
	if ($aktif != TRUE || $username == "") {
		redirect('login');
	}
}
function cek_hak_akses($user_level, $list_hak_akses)
{
	if (!in_array($user_level, $list_hak_akses)) {
		return false;
	} else {
		return true;
	}
}
function nilai_huruf($kkm, $nilai)
{
	$CI =& get_instance();
	//$kkm 	= intval($CI->config->item('kkm'));
	$kkm = $kkm;

	$rentang = round(((100 - $kkm) / 3), 0);

	$d_min = 0;
	$d_max = round(($kkm - 1), 0);
	$c_min = $kkm;
	$c_max = round(($kkm + $rentang), 0);
	$b_min = round(($kkm + ($rentang * 1) + 1), 0);
	$b_max = round(($kkm + ($rentang * 2)));
	$a_min = round(($kkm + ($rentang * 2) + 1), 0);
	$a_max = 100;


	$ret = "";
	if ($nilai >= $d_min && $nilai <= $d_max) {
		$ret = "D";
	} else if ($nilai >= $c_min && $nilai <= $c_max) {
		$ret = "C";
	} else if ($nilai >= $b_min && $nilai <= $b_max) {
		$ret = "B";
	} else if ($nilai >= $a_min && $nilai <= $a_max) {
		$ret = "A";
	} else {
		$ret = "-";
	}
	return $ret;
}
function do_lang($kkm, $nilai)
{
	$CI =& get_instance();
	//$kkm 	= intval($CI->config->item('kkm'));
	$kkm = $kkm;

	$rentang = round(((100 - $kkm) / 3), 0);

	$d_min = 0;
	$d_max = round(($kkm - 1), 0);
	$c_min = $kkm;
	$c_max = round(($kkm + $rentang), 0);
	$b_min = round(($kkm + ($rentang * 1) + 1), 0);
	$b_max = round(($kkm + ($rentang * 2)));
	$a_min = round(($kkm + ($rentang * 2) + 1), 0);
	$a_max = 100;


	$ret = "";
	if ($nilai >= $d_min && $nilai <= $d_max) {
		$ret = "need";
	} else if ($nilai >= $c_min && $nilai <= $c_max) {
		$ret = "need";
	} else if ($nilai >= $b_min && $nilai <= $b_max) {
		$ret = "must";
	} else if ($nilai >= $a_min && $nilai <= $a_max) {
		$ret = "should";
	} else {
		$ret = "-";
	}
	return $ret;
}
function nilai_pre($kkm, $nilai, $lang)
{
	$CI =& get_instance();
	$kkm = $kkm;
	$lang = $lang;
	//$kkm 	= intval($CI->config->item('kkm'));

	$rentang = round(((100 - $kkm) / 3), 0);

	$d_min = 0;
	$d_max = round(($kkm - 1), 0);
	$c_min = $kkm;
	$c_max = round(($kkm + $rentang), 0);
	$b_min = round(($kkm + ($rentang * 1) + 1), 0);
	$b_max = round(($kkm + ($rentang * 2)), 0);
	$a_min = round(($kkm + ($rentang * 2) + 1), 0);
	$a_max = 100;

	$ret = "";
	if ($nilai >= $d_min && $nilai <= $d_max) {
		$ret = $lang == "eng" ? "satisfactorily" : "kurang";
	} else if ($nilai >= $c_min && $nilai <= $c_max) {
		$ret = $lang == "eng" ? "satisfactorily" : "kurang";
	} else if ($nilai >= $b_min && $nilai <= $b_max) {
		$ret = $lang == "eng" ? "well" : "baik";
	} else if ($nilai >= $a_min && $nilai <= $a_max) {
		$ret = $lang == "eng" ? "excellently" : "sangat baik";
	} else {
		$ret = "Undefined";
	}
	return $ret;
}
function generate_menu($level, $is_wali = false)
{
	$menu = '<li><a href="' . base_url() . '"><i class="pe-7s-home"></i><p>Home</p></a></li>';
	if ($level == "admin") {
		$menu .= '
					<li><a href="' . base_url() . 'data_guru"><i class="pe-7s-users"></i><p>Master Guru</p></a></li>
					<li><a href="' . base_url() . 'data_siswa"><i class="pe-7s-smile"></i><p>Master Siswa</p></a></li>
					<li><a href="' . base_url() . 'data_kelas"><i class="pe-7s-global"></i><p>Master Kelas</p></a></li>
					<li class="dropdown">
                       <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-notebook"></i><p>Master P5</p></a>
                        <ul class="dropdown-menu" role="menu" style="
                        background-color: #1F77D0;
                    ">
                    <li><a href="' . base_url() . 'data_projek"><i class="pe-7s-display2"></i>SET PROJEK</a></li>
                    <li><a href="' . base_url() . 'data_kelompok"><i class="pe-7s-display2"></i>SET KELOMPOK</a></li>
                    <li><a href="' . base_url() . 'set_kelompok"><i class="pe-7s-display2"></i>SET KELAS</a></li>
                        </ul>
                    </li>
					<li><a href="' . base_url() . 'data_mapel"><i class="pe-7s-notebook"></i><p>Master Mapel</p></a></li>
					<li><a href="' . base_url() . 'data_ekstra"><i class="pe-7s-ball"></i><p>Master Ekstra</p></a></li>
					<li><a href="' . base_url() . 'backup_db"><i class="pe-7s-server"></i><p>Backup Data</p></a></li>
					<li><a href="' . base_url() . 'tahun"><i class="pe-7s-date"></i><p>Set Tahun Aktif</p></a></li>
					<li><a href="' . base_url() . 'set_kelas"><i class="pe-7s-add-user"></i><p>Set Kelas</p></a></li>
					<li><a href="' . base_url() . 'set_mapel"><i class="pe-7s-display2"></i><p>Set Mapel</p></a></li>
					<li><a href="' . base_url() . 'set_walikelas"><i class="pe-7s-portfolio"></i><p>Set Wali Kelas</p></a></li>
					<li><a href="' . base_url() . 'pengumuman"><i class="pe-7s-bell"></i><p>Pengumuman</p></a></li>
					<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Ubah Password</p></a></li>
					';
	} else if ($level == "guru") {
		$menu .= '<li><a href="' . base_url() . 'view_mapel"><i class="pe-7s-note2"></i><p>Subjects</p></a></li>
		            <li><a href="' . base_url() . 'riwayat_mengajar"><i class="pe-7s-file"></i><p>Teaching History</p></a></li>
					';
		$menu .= $is_wali == TRUE ? '<li class="devider"></li>
		                            <li><a href="' . base_url() . 'pengumuman"><i class="pe-7s-bell"></i><p>Pengumuman</p></a></li>
		                            <li><a href="' . base_url() . 'view_kelompok"><i class="pe-7s-date"></i><p>Projek P5</p></a></li>
		                            <li><a href="' . base_url() . 'n_absensi"><i class="pe-7s-date"></i><p>Absensi</p></a></li>
		                            <li><a href="' . base_url() . 'n_ekstra"><i class="pe-7s-ball"></i><p>Ekstrakurikuler</p></a></li>
		                            <li><a href="' . base_url() . 'n_prestasi"><i class="pe-7s-ball"></i><p>Prestasi</p></a></li>
									<li class="dropdown">
   <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-file"></i><p>Catatan</p></a>
    <ul class="dropdown-menu" role="menu" style="
    background-color: #1F77D0;
">
    <li><a href="' . base_url() . 'n_projek"><i class="pe-7s-ball"></i>Catatan Projek P5</a></li>
	<li><a href="' . base_url() . 'n_catatan"><i class="pe-7s-ball"></i>Catatan Diknas</a></li>
	<li><a href="' . base_url() . 'n_catatan_kl1"><i class="pe-7s-ball"></i>Catatan Kl-1</a></li>
	<li><a href="' . base_url() . 'n_catatan_kl2"><i class="pe-7s-ball"></i>Catatan Kl-2</a></li>
    </ul>
</li>
<li class="dropdown">
   <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-print"></i><p>Cetak</p></a>
    <ul class="dropdown-menu" role="menu" style="
    background-color: #1F77D0;
">
<li><a href="' . base_url() . 'cetak_raport_pts"><i class="pe-7s-print"></i>Cetak Raport Midterm</a></li>
<li><a href="' . base_url() . 'cetak_raport"><i class="pe-7s-print"></i>Cetak Raport Final</a></li>
<li><a href="' . base_url() . 'cetak_leger"><i class="pe-7s-print"></i>Cetak Leger</a></li>
    </ul>
</li>
									
		                            
		                            <li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Ubah Password</p></a></li>' :
			'<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Ubah Password</p></a></li>';
	} else if ($level == "siswa") {
		$menu .= '
					<li><a href="' . base_url() . 'lihat_raport"><i class="pe-7s-notebook"></i><p>Lihat Raport</p></a></li>
					<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Ubah Password</p></a></li>
					';
	} else {
		$menu .= '<li><a href="' . base_url() . 'login"><i class="pe-7s-unlock"></i><p>Status Belum Login</p></a></li>';
	}

	return $menu;
}
function j($data)
{
	header('Content-Type: application/json');
	echo json_encode($data);
}
function tjs($tgl, $tipe)
{
	$pc_satu = explode(" ", $tgl);
	if (count($pc_satu) < 2) {
		$tgl1 = $pc_satu[0];
		$jam1 = "";
	} else {
		$jam1 = $pc_satu[1];
		$tgl1 = $pc_satu[0];
	}

	$pc_dua = explode("-", $tgl1);
	$tgl = $pc_dua[2];
	$bln = $pc_dua[1];
	$thn = $pc_dua[0];

	$bln_pendek = array("Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
	$bln_panjang = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

	$bln_angka = intval($bln) - 1;

	if ($tipe == "l") {
		$bln_txt = $bln_panjang[$bln_angka];
	} else if ($tipe == "s") {
		$bln_txt = $bln_pendek[$bln_angka];
	}

	return $tgl . " " . $bln_txt . " " . $thn . "  " . $jam1;
}
function jk($jk)
{
	if ($jk == "P") {
		return "Perempuan";
	} else {
		return "Laki-laki";
	}
}
function status_anak($data)
{
	if ($data == "AK") {
		return "Anak Kandung";
	} else if ($data == "AT") {
		return "Anak Tiri";
	} else if ($data == "AA") {
		return "Anak Angkat";
	} else {
		return "";
	}
}

function kekata($x)
{
	$x = abs($x);
	$angka = array(
		"",
		"satu",
		"dua",
		"tiga",
		"empat",
		"lima",
		"enam",
		"tujuh",
		"delapan",
		"sembilan",
		"sepuluh",
		"sebelas"
	);
	$temp = "";
	if ($x < 12) {
		$temp = " " . $angka[$x];
	} else if ($x < 20) {
		$temp = kekata($x - 10) . " belas";
	} else if ($x < 100) {
		$temp = kekata($x / 10) . " puluh" . kekata($x % 10);
	} else if ($x < 200) {
		$temp = " seratus" . kekata($x - 100);
	} else if ($x < 1000) {
		$temp = kekata($x / 100) . " ratus" . kekata($x % 100);
	} else if ($x < 2000) {
		$temp = " seribu" . kekata($x - 1000);
	} else if ($x < 1000000) {
		$temp = kekata($x / 1000) . " ribu" . kekata($x % 1000);
	} else if ($x < 1000000000) {
		$temp = kekata($x / 1000000) . " juta" . kekata($x % 1000000);
	} else if ($x < 1000000000000) {
		$temp = kekata($x / 1000000000) . " milyar" . kekata(fmod($x, 1000000000));
	} else if ($x < 1000000000000000) {
		$temp = kekata($x / 1000000000000) . " trilyun" . kekata(fmod($x, 1000000000000));
	}
	return $temp;
}


function terbilang($x, $style = 4)
{
	if ($x < 0) {
		$hasil = "minus " . trim(kekata($x));
	} else {
		$hasil = trim(kekata($x));
	}
	switch ($style) {
		case 1:
			$hasil = strtoupper($hasil);
			break;
		case 2:
			$hasil = strtolower($hasil);
			break;
		case 3:
			$hasil = ucwords($hasil);
			break;
		default:
			$hasil = ucfirst($hasil);
			break;
	}
	return $hasil;
}

function potong($teks)
{
	$pc_spasi = explode(" ", $teks);

	$terakhir = $pc_spasi[(sizeof($pc_spasi) - 1)];
	$huruf_pertama_terakhir = substr($terakhir, 0, 1) . ".";

	$tekss = '';
	if ((strlen($teks)) > 30) {

		$tekss = '';
		for ($i = 0; $i < (sizeof($pc_spasi) - 1); $i++) {
			$tekss .= $pc_spasi[$i] . " ";
		}

		$tekss .= " " . $huruf_pertama_terakhir;
	} else {
		$tekss = $teks;
	}

	return $tekss;
}