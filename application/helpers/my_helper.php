<?php
defined('BASEPATH') or exit('No direct script access allowed');
function cek_aktif()
{
	$CI = &get_instance();
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
	$CI = &get_instance();
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
	$CI = &get_instance();
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
	$CI = &get_instance();
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
		$menu = '<style>
		@keyframes fly {
			0% { transform: translateY(0) rotate(-5deg); }
			50% { transform: translateY(-3px) rotate(-5deg); }
			100% { transform: translateY(0) rotate(-5deg); }
		}
		.flying-badge {
			position: absolute;
			top: -8px;
			right: 15px;
			background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
			color: white;
			padding: 2px 6px;
			border-radius: 20px;
			font-size: 9px;
			font-weight: 900;
			box-shadow: 0 4px 12px rgba(244, 63, 94, 0.4);
			letter-spacing: 0.5px;
			animation: fly 2s ease-in-out infinite;
			z-index: 10;
			line-height: 1;
			text-transform: uppercase;
		}
	</style>';
	$menu .= '<li><a href="' . base_url() . '"><i class="pe-7s-home"></i><p>Home</p></a></li>';
	if ($level == "admin") {
		$menu .= '
					<li><a href="' . base_url() . 'home/buzz_report"><i class="pe-7s-notebook"></i><p>LMS Reports<span class="flying-badge">NEW</span></p></a></li>
					<li><a href="' . base_url() . 'lesson_plan"><i class="pe-7s-magic-wand"></i><p>Lesson Plan Generator <span class="flying-badge">NEW</span></p></a></li>
					<li><a href="' . base_url() . 'data_guru"><i class="pe-7s-users"></i><p>Master Teachers</p></a></li>
					<li><a href="' . base_url() . 'data_siswa"><i class="pe-7s-smile"></i><p>Master Students</p></a></li>
					<li><a href="' . base_url() . 'data_kelas"><i class="pe-7s-global"></i><p>Master Classes</p></a></li>
					<li class="dropdown">
                       <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-notebook"></i><p>Master P5</p></a>
                        <ul class="dropdown-menu" role="menu" style="
                        background-color: #1F77D0;
                    ">
                    <li><a href="' . base_url() . 'data_projek"><i class="pe-7s-display2"></i>SET PROJECT</a></li>
                    <li><a href="' . base_url() . 'data_kelompok"><i class="pe-7s-display2"></i>SET GROUP</a></li>
                    <li><a href="' . base_url() . 'set_kelompok"><i class="pe-7s-display2"></i>SET CLASS</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                       <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-notebook"></i><p>Master TAHFIZ</p></a>
                        <ul class="dropdown-menu" role="menu" style="
                        background-color: #1F77D0;
                    ">
                    <li><a href="' . base_url() . 'data_kelas_tahfiz"><i class="pe-7s-display2"></i>MASTER KELAS</a></li>
                    <li><a href="' . base_url() . 'set_kelas_tahfiz"><i class="pe-7s-display2"></i>SET KELAS</a></li>
                    <li><a href="' . base_url() . 'set_walikelas_tahfiz"><i class="pe-7s-display2"></i>SET GURU</a></li>
                        </ul>
                    </li>
					<li><a href="' . base_url() . 'data_mapel"><i class="pe-7s-notebook"></i><p>Master Subjects</p></a></li>
					<li><a href="' . base_url() . 'data_ekstra"><i class="pe-7s-ball"></i><p>Master Extracurriculars</p></a></li>
					<li><a href="' . base_url() . 'view_ledger"><i class="pe-7s-bookmarks"></i><p>View Ledger</p></a></li>
					<li><a href="' . base_url() . 'backup_db"><i class="pe-7s-server"></i><p>Backup Data</p></a></li>
					<li><a href="' . base_url() . 'kehadiran/kelas"><i class="pe-7s-bookmarks"></i><p>Attendance</p></a></li>
					<li><a href="' . base_url() . 'tahun"><i class="pe-7s-date"></i><p>Set Academic Year</p></a></li>
					<li><a href="' . base_url() . 'set_kelas"><i class="pe-7s-add-user"></i><p>Set Class</p></a></li>
					<li><a href="' . base_url() . 'set_mapel"><i class="pe-7s-display2"></i><p>Set Subject</p></a></li>
					<li><a href="' . base_url() . 'set_walikelas"><i class="pe-7s-portfolio"></i><p>Set Homeroom Teacher</p></a></li>
					<li><a href="' . base_url() . 'pengumuman"><i class="pe-7s-bell"></i><p>Announcements</p></a></li>
					<li class="dropdown">
                       <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-star"></i><p>CORE Program</p></a>
                        <ul class="dropdown-menu" role="menu" style="background-color: #1F77D0;">
                            <li><a href="' . base_url() . 'core_target"><i class="pe-7s-target"></i>CORE Targets</a></li>
                            <li><a href="' . base_url() . 'core_tracker"><i class="pe-7s-graph"></i>CORE Tracker Dashboard</a></li>
                        </ul>
                    </li>
					<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Change Password</p></a></li>
					';
	} else if ($level == "guru") {
		$menu .= '
		<li><a href="' . base_url() . 'lesson_plan"><i class="pe-7s-magic-wand"></i><p>Lesson Plan Generator <span class="flying-badge">NEW</span></p></a></li>
		<li><a href="' . base_url() . 'view_mapel"><i class="pe-7s-note2"></i><p>Subjects</p></a></li>
		<li><a href="' . base_url() . 'riwayat_mengajar"><i class="pe-7s-file"></i><p>Teaching History</p></a></li>
		<li><a href="' . base_url() . 'view_ledger"><i class="pe-7s-bookmarks"></i><p>View Ledger</p></a></li>
					';
		$menu .= $is_wali == TRUE ? '<li class="devider"></li>
		                            <li><a href="' . base_url() . 'pengumuman"><i class="pe-7s-bell"></i><p>Pengumuman</p></a></li>
		                            <li><a href="' . base_url() . 'view_kelompok"><i class="pe-7s-date"></i><p>Projek P5</p></a></li>
		                            <li><a href="' . base_url() . 'n_absensi"><i class="pe-7s-date"></i><p>Absensi</p></a></li>
		                            <li><a href="' . base_url() . 'n_ekstra"><i class="pe-7s-ball"></i><p>Ekstrakurikuler</p></a></li>
		                            <li><a href="' . base_url() . 'n_prestasi"><i class="pe-7s-ball"></i><p>Prestasi</p></a></li>
		                            <li><a href="' . base_url() . 'kehadiran"><i class="pe-7s-user"></i><p>Attendance</p></a></li>
                                    <li class="dropdown">
                                       <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-star"></i><p>CORE Program</p></a>
                                        <ul class="dropdown-menu" role="menu" style="background-color: #1F77D0;">
                                            <li><a href="' . base_url() . 'core_target"><i class="pe-7s-target"></i>CORE Targets</a></li>
                                            <li><a href="' . base_url() . 'core_submission"><i class="pe-7s-check"></i>CORE Submissions</a></li>
                                            <li><a href="' . base_url() . 'core_tracker"><i class="pe-7s-graph"></i>CORE Tracker Dashboard</a></li>
                                        </ul>
                                    </li>
									<li class="dropdown">
   <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="pe-7s-file"></i><p>Catatan</p></a>
    <ul class="dropdown-menu" role="menu" style="
    background-color: #1F77D0;
">
    <li><a href="' . base_url() . 'n_projek"><i class="pe-7s-ball"></i>Catatan Projek P5</a></li>
    <li><a href="' . base_url() . 'n_catatan_homeroom"><i class="pe-7s-ball"></i>Catatan Homeroom</a></li>
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

function siswa($id_siswa, $ta)
{
	$CI2 = &get_instance();
	$siswa = $CI2->db->query("SELECT 
								a.nama, a.nis, a.nisn, c.tingkat, c.id as id_kelas,b.ta
								FROM m_siswa a
								LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
								LEFT JOIN m_kelas c ON b.id_kelas = c.id
								WHERE a.id = $id_siswa AND b.ta = $ta")->row();
	return $siswa;
}

function wali_kelas($id_kelas, $tahun)
{
	$CI2 = &get_instance();
	$wali_kelas = $CI2->db->query("SELECT 
	a.*, b.nama nmguru, b.nip, 
	c.tingkat, c.nama nmkelas
	FROM t_walikelas a 
	INNER JOIN m_guru b ON a.id_guru = b.id 
	INNER JOIN m_kelas c ON a.id_kelas = c.id
	WHERE a.id_kelas = $id_kelas AND a.tasm = $tahun")->row();
	return $wali_kelas;
}

function get_guru_mapel($id_mapel, $id_kelas, $tasm)
{
	$CI2 = &get_instance();
	$guru_mapel = $CI2->db->query("SELECT a.id id,kkm,a.nama nama, c.nama namaguru FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
										WHERE  a.id= $id_mapel AND b.id_kelas= $id_kelas AND b.tasm= $tasm")->row();
	return $guru_mapel;
}

function ta_active($tasm)
{
	$CI2 = &get_instance();
	$ta_active = $CI2->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun
	WHERE tahun = '$tasm'")->row();

	return $ta_active;
}

function get_absent($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_nilai_absensi = $CI2->db->query("SELECT 
										s, i, a
										FROM t_nilai_absensi
										WHERE id_siswa = $id_siswa AND tasm = '" . $tasm . "'")->row();
	return $q_nilai_absensi;
}
function get_catatan_naik_kelas($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_catatan = $CI2->db->query("SELECT 
								a.*
								FROM t_naikkelas a 
								WHERE a.id_siswa = $id_siswa AND a.ta = $tasm")->row();
	return $q_catatan;
}
function get_capaianKl1($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_kl1 = $CI2->db->query("SELECT 
								a.*
								FROM t_catatan_kl1 a 
								LEFT JOIN m_siswa c ON a.id_siswa = c.id
								WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row();
	return $q_kl1;
}
function get_capaianKl2($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_kl2 = $CI2->db->query("SELECT 
								a.*
								FROM t_catatan_kl2 a 
								LEFT JOIN m_siswa c ON a.id_siswa = c.id
								WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row();
	return $q_kl2;
}

function get_nilai_utama($table, $id_siswa, $tasm, $mid = false)
{
	$CI2 = &get_instance();
	$query = $mid ? "AND d.mid_final=1" : '';
	$ambil_np = $CI2->db->query("SELECT 
                                    c.id idmapel,b.subject_kkm as subject_kkm, c.kkm as kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                    FROM $table a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa
									$query
                                    AND a.tasm = '" . $tasm . "'
                                    AND a.nilai != 0
                                    AND c.kkm != 4")->result_array();
	// echo json_encode($ambil_np);
	return $ambil_np;
}
function get_nilai_uts($table, $id_siswa, $tasm, $mid = false)
{
	$CI2 = &get_instance();
	$ambil_uts = $CI2->db->query("SELECT 
                        c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                        FROM $table a
                        INNER JOIN m_mapel c ON a.id_mapel_kd = c.id
                        WHERE 
                        a.jenis IN ('t', 'a', 'p', 'b', 'c')
                        AND a.id_siswa = ?
                        AND a.tasm = ?
                        ", [$id_siswa, $tasm])->result_array();
	return $ambil_uts;
}

function get_nilai_sub($table, $id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$ambil_np_submp = $CI2->db->query("SELECT 
                                    b.id_mapel, c.kd_singkat
                                    FROM $table a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
									AND c.kkm !=4 AND a.nilai != 0
                                    GROUP BY b.id_mapel")->result_array();
	return $ambil_np_submp;
}

function get_exschool($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_nilai_ekstra = $CI2->db->query("SELECT 
										b.nama, a.nilai, a.desk
										FROM t_nilai_ekstra a
										INNER JOIN m_ekstra b ON a.id_ekstra = b.id
										WHERE a.id_siswa = $id_siswa AND a.nilai != '-' AND a.tasm = '" . $tasm . "'")->result_array();
	//echo $CI2->db->last_query();

	return $q_nilai_ekstra;
}

function get_prestasi($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$prestasi = $CI2->db->query("SELECT 
								a.*
								FROM t_prestasi a 
								LEFT JOIN m_siswa c ON a.id_siswa = c.id
								WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
	//echo $this->db->last_query();
	return $prestasi;
}

function catatan_homeroom($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_catatan_homeroom = $CI2->db->query("SELECT a.* FROM t_catatan_homeroom a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row();
	//echo $this->db->last_query();
	return $q_catatan_homeroom;
}

function get_ICB_PSS_LA($id_siswa, $tasm, $periode, $table)
{
	$CI2 = &get_instance();
	$nilai = $CI2->db->query("SELECT 
		c.id idmapel, c.kkm, a.tasm, c.kd_singkat,c.nama, a.jenis, a.nilai nilai, d.nama_kd
		FROM $table a
		INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
		INNER JOIN m_mapel c ON b.id_mapel = c.id
		INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
		WHERE a.id_siswa = $id_siswa
		AND c.tambahan_sub = '" . $periode . "'
		AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();
	return $nilai;
}
function get_catatan_mapel($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$ambil_nc = $CI2->db->query("SELECT 
        c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai as nilai, a.nilai_mid as nilai_mid
        FROM t_nilai_cat a
        INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
        INNER JOIN m_mapel c ON b.id_mapel = c.id
        WHERE a.id_siswa = $id_siswa
        AND a.tasm = '" . $tasm . "' AND c.kkm !=4")->result_array();
	return $ambil_nc;
}


function get_kkm($table, $id_siswa, $tasm)
{
	$ambil_np = get_nilai_utama($table, $id_siswa, $tasm);
	$array_kkm = array();
	foreach ($ambil_np as $a2) {
		$kkmx = $a2['kkm'];
		$array_kkm[] = $kkmx;
	}
	$kkm = array_unique($array_kkm);
	return $kkm;
}

function getJenisRaport($id_rapor, $tingkat)
{
	$CI2 = &get_instance();
	$jenis_rapor = $CI2->db->query("SELECT * FROM t_tahun_jenis_rapor
                        WHERE id_tahun = '" . $id_rapor . "'
                        AND tingkat = '" . $tingkat . "'
                        ")->row();
	return $jenis_rapor;
}

function insert_icb_pss_la($id_siswa, $tasm, $table, $id_rapor, $periode)
{
	$CI2 = &get_instance();
	$data = get_ICB_PSS_LA($id_siswa, $tasm, $periode, $table); //get data ICB PSS LA
	foreach ($data as $val) {
		$CI2->db->query("INSERT INTO t_raport_character (id_rapor,id_mapel,mapel,kd_singkat,kkm,tasm,jenis,nilai,nama_kd,periode) 
                            VALUES ($id_rapor,'" . $val["idmapel"] . "','" . $val["nama"] . "','" . $val["kd_singkat"] . "','" . $val['kkm'] . "','" . $val["tasm"] . "'
                            ,'" . $val["jenis"] . "','" . $val['nilai'] . "','" . addslashes($val["nama_kd"]) . "','" . $periode . "')");
	}
}

/**
 * Mendapatkan kategori penilaian mata pelajaran berdasarkan SOP MHIS (Section 6 & 7):
 * - 'without_written'    : Kategori 1 (Tanpa Ujian Tertulis): PE, Music, Art, PKWU/Makerspace, BTQ/Tahfiz, Project
 * - 'with_performance'   : Kategori 2 (Dengan Praktik/Kinerja): Science, Physics, Chemistry, Biology, CS, Languages (Arabic/Mandarin, English), GP
 * - 'without_performance': Kategori 3 (Teori Murni): Mathematics, Bahasa Indonesia, PKN, History, Geography, Accounting, Sociology, dsb.
 *
 * @param int|string $id_or_code_or_name ID mata pelajaran, kode singkat (kd_singkat), atau nama mapel
 * @return string 'without_written' | 'with_performance' | 'without_performance'
 */
function get_kategori_penilaian_mapel($id_or_code_or_name, $force_refresh = false)
{
	static $db_mapel_categories = null;

	if ($force_refresh) {
		$db_mapel_categories = null;
	}

	// 1. Muat kategori dinamis dari database (m_mapel) jika belum ada di memory cache
	if ($db_mapel_categories === null) {
		$db_mapel_categories = [
			'by_id' => [],
			'by_code' => [],
			'by_name' => []
		];

		if (function_exists('get_instance')) {
			$CI = &get_instance();
			if (!empty($CI->db)) {
				try {
					$query = $CI->db->query("SELECT id, kd_singkat, nama, kategori_penilaian FROM m_mapel");
					if ($query && method_exists($query, 'result_array')) {
						$rows = $query->result_array();
						if (is_array($rows)) {
							foreach ($rows as $r) {
								if (!empty($r['kategori_penilaian'])) {
									$kat = trim($r['kategori_penilaian']);
									$db_mapel_categories['by_id'][(int)$r['id']] = $kat;
									if (!empty($r['kd_singkat'])) {
										$db_mapel_categories['by_code'][strtoupper(trim($r['kd_singkat']))] = $kat;
									}
									if (!empty($r['nama'])) {
										$db_mapel_categories['by_name'][strtoupper(trim($r['nama']))] = $kat;
									}
								}
							}
						}
					}
				} catch (\Throwable $e) {
					// Fallback ke pemetaan SOP statis jika DB error
				}
			}
		}
	}

	$raw_category = strtolower(trim((string)$id_or_code_or_name));
	if (in_array($raw_category, ['without_written', 'with_performance', 'without_performance'], true)) {
		return $raw_category;
	}

	$id = is_numeric($id_or_code_or_name) ? (int)$id_or_code_or_name : 0;
	$str = strtoupper(trim((string)$id_or_code_or_name));

	// 2. Prioritas Utama: Konfigurasi Dinamis Admin dari Database (m_mapel)
	if ($id > 0 && isset($db_mapel_categories['by_id'][$id])) {
		return $db_mapel_categories['by_id'][$id];
	}
	if ($str !== '') {
		if (isset($db_mapel_categories['by_code'][$str])) {
			return $db_mapel_categories['by_code'][$str];
		}
		if (isset($db_mapel_categories['by_name'][$str])) {
			return $db_mapel_categories['by_name'][$str];
		}
	}

	// 3. Fallback: Pemetaan SOP Statis (untuk standalone unit test atau mapel baru sebelum disetting di DB)
	// 3.1 Kategori 1: Without Written Examination (Section 7 SOP)
	$ids_without_written = [9, 10, 19, 23, 38, 39]; // 9=Music, 10=PE, 19=PKWU, 23=BTQ, 38=TH, 39=HMT
	$codes_without_written = ['MUS', 'PE', 'PKWU', 'BTQ', 'TH', 'HMT', 'ART'];

	if (in_array($id, $ids_without_written, true) || in_array($str, $codes_without_written, true)) {
		return 'without_written';
	}

	if (
		strpos($str, 'PHYSICAL EDUCATION') !== false ||
		strpos($str, 'PJKES') !== false ||
		strpos($str, 'MUSIC') !== false ||
		strpos($str, 'SENI MUSIK') !== false ||
		strpos($str, 'SENI BUDAYA') !== false ||
		strpos($str, 'ART') !== false ||
		strpos($str, 'MAKERSPACE') !== false ||
		strpos($str, 'PRAKARYA') !== false ||
		strpos($str, 'PKWU') !== false ||
		strpos($str, 'BACA TULIS QURAN') !== false ||
		strpos($str, 'BTQ') !== false ||
		strpos($str, 'TAHFIZ') !== false ||
		strpos($str, 'HAFALAN') !== false ||
		strpos($str, 'QURAN') !== false ||
		strpos($str, 'PROJECT') !== false
	) {
		return 'without_written';
	}

	// 3.2 Kategori 2: With Performance-Based Assessment (Section 6 SOP)
	$ids_with_perf = [6, 8, 12, 13, 14, 21, 22, 24, 25, 26, 27, 33, 34, 35, 43];
	$codes_with_perf = ['SCIE', 'PHY', 'CHEM', 'BIO', 'CS', 'MAND', 'ARAB', 'GP', 'IPA', 'ENG'];

	if (in_array($id, $ids_with_perf, true) || in_array($str, $codes_with_perf, true)) {
		return 'with_performance';
	}

	if (
		strpos($str, 'SCIENCE') !== false ||
		strpos($str, 'PHYSICS') !== false ||
		strpos($str, 'CHEMISTRY') !== false ||
		strpos($str, 'BIOLOGY') !== false ||
		strpos($str, 'ILMU PENGETAHUAN ALAM') !== false ||
		strpos($str, 'COMPUTER SCIENCE') !== false ||
		strpos($str, 'INFORMATIKA') !== false ||
		strpos($str, 'MANDARIN') !== false ||
		strpos($str, 'ARABIC') !== false ||
		strpos($str, 'BAHASA ARAB') !== false ||
		strpos($str, 'GLOBAL PERSPECTIVE') !== false
	) {
		return 'with_performance';
	}

	// 3.3 Kategori 3: Without Performance-Based Assessment (Teori Murni)
	return 'without_performance';
}

/**
 * Mendapatkan komposisi persentase bobot penilaian berdasarkan kategori SOP, semester, dan tingkat/jenjang.
 *
 * @param string $kategori 'without_written' | 'with_performance' | 'without_performance'
 * @param int $semester Semester 1 atau Semester 2
 * @param bool $is_pts Apakah untuk penilaian Mid-Term (PTS)
 * @param int|string|null $tingkat Jenjang kelas (1, 2, 3, 4, 5, 6, dll)
 * @return array Bobot ['ut' => .., 'buzz' => .., 'mid' => .., 'final' => .., 'perf' => ..]
 */
function get_bobot_penilaian_sop($kategori, $semester = 1, $is_pts = false, $tingkat = null)
{
	// 1. Kasus Khusus Grade 1 dan Grade 2 (tidak ada Mid dan Final Exam untuk semua kategori mapel)
	// Sesuai Assessment Policy MHIS Section 3:
	// "For grade levels that do not administer Mid-Semester and Final/End-of-Year Examinations
	// (e.g., Grades 1–2 in Bintaro and Grade 1 in Semarang), the assessment composition is as follows:
	// Unit Tests: 60%, Performance-Based Assessment: 30%, Buzz Assignment: 10%"
	if ($tingkat !== null && in_array((string)$tingkat, ['1', '2'], true)) {
		return ['ut' => 60, 'buzz' => 10, 'mid' => 0, 'final' => 0, 'perf' => 30];
	}

	$is_secondary = ($tingkat !== null && (int)$tingkat >= 7);

	if ($is_secondary) {
		// Secondary School (Grade 7 - 12) - Section 4 Assessment Policy
		if ((int)$semester === 2) {
			if ($kategori === 'without_written') {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 0, 'final' => 0, 'perf' => 55];
			} elseif ($kategori === 'with_performance') {
				return ['ut' => 20, 'buzz' => 10, 'mid' => 20, 'final' => 40, 'perf' => 10];
			} else {
				return ['ut' => 30, 'buzz' => 10, 'mid' => 20, 'final' => 40, 'perf' => 0];
			}
		} else {
			if ($kategori === 'without_written') {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 0, 'final' => 0, 'perf' => 55];
			} elseif ($kategori === 'with_performance') {
				return ['ut' => 25, 'buzz' => 10, 'mid' => 25, 'final' => 30, 'perf' => 10];
			} else {
				return ['ut' => 30, 'buzz' => 10, 'mid' => 25, 'final' => 35, 'perf' => 0];
			}
		}
	} else {
		// Primary School (Grade 3 - 6, default repo Primary) - Section 3 Assessment Policy
		if ((int)$semester === 2) {
			if ($kategori === 'without_written') {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 0, 'final' => 0, 'perf' => 55];
			} elseif ($kategori === 'with_performance') {
				return ['ut' => 25, 'buzz' => 10, 'mid' => 20, 'final' => 35, 'perf' => 10];
			} else {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 20, 'final' => 35, 'perf' => 0];
			}
		} else {
			if ($kategori === 'without_written') {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 0, 'final' => 0, 'perf' => 55];
			} elseif ($kategori === 'with_performance') {
				return ['ut' => 30, 'buzz' => 10, 'mid' => 20, 'final' => 30, 'perf' => 10];
			} else {
				return ['ut' => 35, 'buzz' => 10, 'mid' => 20, 'final' => 35, 'perf' => 0];
			}
		}
	}
}

/**
 * Menghitung nilai akhir mata pelajaran berdasarkan SOP Assessment Policy.
 *
 * Mendukung 3 kategori SOP:
 * 1. without_written (PE, Musik, Art, Makerspace, BTQ, dll)
 * 2. with_performance (Science, CS, Bahasa Asing, English, dll)
 * 3. without_performance (Matematika, B. Indo, PKN, IPS, dll)
 *
 * Fitur Khusus:
 * - Zero Re-input Guarantee: Untuk mapel without_written, nilai PTS yang terlanjur
 *   diinput di kolom Midterm ('t') otomatis dikonversi menjadi Alternative Assessment ('p').
 * - Normalisasi Dinamis / Fallback Buzz Assignment: Jika Buzz Assignment ('b') belum ada
 *   atau belum disinkronkan, kalkulasi dinormalisasi dari komponen yang aktif sehingga
 *   siswa tidak kehilangan persentase nilai.
 * - Dukungan Cambridge Grade 8, 11, dan Grade 12 (Pre-University Thesis).
 *
 * @param array $components Komponen nilai: ['h'=>..., 'b'=>..., 't'=>..., 'a'=>..., 'p'=>..., 'thesis'=>...]
 * @param int|string $id_or_code_or_name ID Mapel, kode singkat, atau nama mapel
 * @param int $semester Semester 1 atau Semester 2 (default: 1)
 * @param bool $is_pts Apakah untuk penilaian Mid-Term (PTS) (default: false)
 * @param int|string|null $tingkat Jenjang kelas (misal: 7, 8, 9, 10, 11, 12)
 * @param bool $return_details Jika true, mengembalikan array breakdown detail
 * @return int|float|array Nilai akhir bulat (atau array detail jika $return_details = true)
 */
function hitung_nilai_sop($components, $id_or_code_or_name, $semester = 1, $is_pts = false, $tingkat = null, $return_details = false)
{
	// 1. Ekstrak nilai Unit Tests (Harian / KD)
	$raw_h = $components['h'] ?? $components['ut'] ?? 0;
	$val_h = 0;
	if (is_array($raw_h)) {
		$sum_h = 0;
		$cnt_h = 0;
		foreach ($raw_h as $item) {
			if (is_numeric($item)) {
				$sum_h += (float)$item;
				$cnt_h++;
			} elseif (is_string($item) && strpos($item, '//') !== false) {
				$p = explode('//', $item);
				if (is_numeric($p[0])) {
					$sum_h += (float)$p[0];
					$cnt_h++;
				}
			}
		}
		$val_h = ($cnt_h > 0) ? ($sum_h / $cnt_h) : 0;
	} elseif (is_numeric($raw_h)) {
		$val_h = (float)$raw_h;
	}

	// 2. Ekstrak nilai komponen lainnya
	$val_b = (float)($components['b'] ?? $components['buzz'] ?? 0);
	$val_t = (float)($components['t'] ?? $components['mid'] ?? 0);
	$val_a = (float)($components['a'] ?? $components['final'] ?? 0);
	$val_p = (float)($components['p'] ?? $components['perf'] ?? 0);
	$has_buzz = isset($components['has_buzz']) ? (bool)$components['has_buzz'] : ($val_b > 0);

	// 3. Tentukan kategori mapel berdasarkan SOP
	$kategori = get_kategori_penilaian_mapel($id_or_code_or_name);

	// 4. Zero Re-input Guarantee untuk mapel without_written & Penanganan Grade 1 & 2
	$is_grade_1_2 = ($tingkat !== null && in_array((string)$tingkat, ['1', '2'], true));
	if ($is_grade_1_2) {
		// Grade 1 & 2 tidak menyelenggarakan Mid dan Final Exam
		// Jika guru terlanjur input di kolom Mid (t) atau Final (a) sedangkan kolom Performance (p) kosong,
		// konversikan nilai tersebut ke Performance agar siswa tidak kehilangan nilai praktiknya.
		if ($val_p <= 0) {
			if ($val_t > 0) {
				$val_p = $val_t;
			} elseif ($val_a > 0) {
				$val_p = $val_a;
			}
		}
		// Pastikan nilai ujian tertulis mid & final tidak dihitung
		$val_t = 0;
		$val_a = 0;
	} elseif ($kategori === 'without_written') {
		if ($val_p <= 0) {
			if ($is_pts) {
				// Pada PTS, jika guru input di kolom Midterm (t), konversi ke Alternative Assessment (p)
				if ($val_t > 0) {
					$val_p = $val_t;
				}
			} else {
				// Pada Final, jika ada input di kolom Final (a) atau Mid (t), gunakan sebagai Alternative Assessment
				if ($val_a > 0) {
					$val_p = $val_a;
				} elseif ($val_t > 0) {
					$val_p = $val_t;
				}
			}
		}
		// Mapel non-written tidak memiliki ujian tertulis mid & final
		$val_t = 0;
		$val_a = 0;
	}

	// 5. Ambil matriks bobot resmi SOP
	$bobot = get_bobot_penilaian_sop($kategori, $semester, $is_pts, $tingkat);

	// 6. Penanganan khusus Grade 12 Semester 2 (Pre-University Thesis)
	$val_thesis = (float)($components['thesis'] ?? 0);
	if ((string)$tingkat === '12' && (int)$semester === 2 && !$is_pts && $val_thesis > 0) {
		// SOP: Internal Assessment 40%, Thesis 20%, End-of-Year (Mock 3) 40%
		$bobot_internal = $bobot['ut'] + $bobot['mid'];
		$skor_internal = ($bobot['ut'] * $val_h) + ($bobot['mid'] * $val_t);
		if ($bobot['perf'] > 0 && ($kategori === 'without_written' || $val_p > 0)) {
			$bobot_internal += $bobot['perf'];
			$skor_internal += ($bobot['perf'] * $val_p);
		}
		if ($has_buzz || $val_b > 0) {
			$bobot_internal += $bobot['buzz'];
			$skor_internal += ($bobot['buzz'] * $val_b);
		}
		$skor_internal_scaled = ($bobot_internal > 0) ? ($skor_internal / $bobot_internal) * 40 : 0;

		$total_skor = $skor_internal_scaled + (20 * $val_thesis) + (40 * $val_a);
		$nilai_akhir = round($total_skor / 100);

		if ($return_details) {
			$kategori_labels = [
				'without_written' => 'Without Written Exam (Alternative Assessment)',
				'with_performance' => 'With Performance Assessment',
				'without_performance' => 'Without Performance Assessment'
			];
			$kat_label = $kategori_labels[$kategori] ?? 'Cambridge Pre-University Thesis (Grade 12)';
			$internal_val = ($bobot_internal > 0) ? round($skor_internal / $bobot_internal, 1) : 0;
			$langkah = "(Internal 40% × {$internal_val}) + (Thesis 20% × " . round($val_thesis, 1) . ") + (Final/Mock 40% × " . round($val_a, 1) . ") = " . round($total_skor / 100, 2) . " ≈ {$nilai_akhir}";
			return [
				'nilai_akhir' => (int)$nilai_akhir,
				'kategori' => $kategori,
				'kategori_label' => $kat_label,
				'bobot' => ['internal' => 40, 'thesis' => 20, 'final' => 40],
				'total_bobot_aktif' => 100,
				'komponen' => ['ut' => round($val_h, 1), 'buzz' => round($val_b, 1), 'mid' => round($val_t, 1), 'final' => round($val_a, 1), 'perf' => round($val_p, 1), 'thesis' => round($val_thesis, 1)],
				'raw' => $total_skor / 100,
				'langkah_hitung' => $langkah,
				'rumus_dasar' => "Internal: 40% | Thesis: 20% | Mock 3 / Final: 40%",
				'penjelasan' => "Cambridge Grade 12 SOP Rule: Internal Assessment 40%, Thesis 20%, Mock 3 / Final Exam 40%."
			];
		}
		return (int)$nilai_akhir;
	}

	// 7. Hitung nilai akhir dengan normalisasi dinamis
	$skor_tertimbang = 0;
	$total_bobot = 0;

	if ($is_pts) {
		// Pada penilaian Mid-Term (PTS):
		// Bobot dasar SOP tetap digunakan tanpa merubah persentase matriks resmi.
		// Sesuai aturan: komponen yang nilainya 0 atau tidak ada tidak dihitung ke dalam bobot pembagi.
		// Ujian Akhir (Final / End-of-Year) belum ada / tidak dihitung pada PTS.

		// UT (Harian)
		if ($bobot['ut'] > 0 && $val_h > 0) {
			$skor_tertimbang += ($bobot['ut'] * $val_h);
			$total_bobot += $bobot['ut'];
		}

		// Mid-Term Exam (PTS)
		if ($bobot['mid'] > 0 && $val_t > 0) {
			$skor_tertimbang += ($bobot['mid'] * $val_t);
			$total_bobot += $bobot['mid'];
		}

		// Buzz Assignment (LMS)
		if ($bobot['buzz'] > 0 && $val_b > 0) {
			$skor_tertimbang += ($bobot['buzz'] * $val_b);
			$total_bobot += $bobot['buzz'];
		}

		// Performance / Alternative Assessment
		if ($bobot['perf'] > 0 && $val_p > 0) {
			$skor_tertimbang += ($bobot['perf'] * $val_p);
			$total_bobot += $bobot['perf'];
		}

		// Catatan: $bobot['final'] tidak pernah dihitung pada periode PTS.
	} else {
		// Pada penilaian Akhir Semester (Final-Term / End-of-Year / PAS):
		// UT
		if ($bobot['ut'] > 0) {
			$skor_tertimbang += ($bobot['ut'] * $val_h);
			$total_bobot += $bobot['ut'];
		}

		// Mid
		if ($bobot['mid'] > 0) {
			$skor_tertimbang += ($bobot['mid'] * $val_t);
			$total_bobot += $bobot['mid'];
		}

		// Final / End-of-Year
		if ($bobot['final'] > 0) {
			$skor_tertimbang += ($bobot['final'] * $val_a);
			$total_bobot += $bobot['final'];
		}

		// Performance / Alternative
		if ($bobot['perf'] > 0) {
			if ($kategori === 'without_written' || $val_p > 0) {
				$skor_tertimbang += ($bobot['perf'] * $val_p);
				$total_bobot += $bobot['perf'];
			}
		}

		// Buzz Assignment: jika ada atau sudah disinkronkan, masukkan bobot Buzz
		if ($bobot['buzz'] > 0) {
			if ($has_buzz || $val_b > 0) {
				$skor_tertimbang += ($bobot['buzz'] * $val_b);
				$total_bobot += $bobot['buzz'];
			} else {
				// Jika belum disinkronkan, jangan kurangi nilai siswa (normalisasi over active weights)
				// Total bobot tetap menggunakan komponen aktif yang ada
			}
		}
	}

	if ($total_bobot <= 0) {
		$total_bobot = 100;
	}

	$raw_nilai = $skor_tertimbang / $total_bobot;
	$nilai_akhir = round($raw_nilai);

	if ($return_details) {
		$kategori_labels = [
			'without_written' => 'Without Written Exam (Alternative Assessment)',
			'with_performance' => 'With Performance Assessment',
			'without_performance' => 'Without Performance Assessment'
		];
		if ($is_grade_1_2) {
			$kat_label = 'Grade 1 & 2 No Mid/Final Exam (UT 60%, Performance 30%, Buzz 10%)';
		} else {
			$kat_label = $kategori_labels[$kategori] ?? $kategori;
		}

		$terms = [];
		if ($is_pts) {
			if ($bobot['ut'] > 0 && $val_h > 0) {
				$terms[] = "({$bobot['ut']}% × " . round($val_h, 1) . ")";
			}
			if ($bobot['buzz'] > 0 && $val_b > 0) {
				$terms[] = "({$bobot['buzz']}% × " . round($val_b, 1) . ")";
			}
			if ($bobot['mid'] > 0 && $val_t > 0) {
				$terms[] = "({$bobot['mid']}% × " . round($val_t, 1) . ")";
			}
			if ($bobot['perf'] > 0 && $val_p > 0) {
				$terms[] = "({$bobot['perf']}% × " . round($val_p, 1) . ")";
			}
		} else {
			if ($bobot['ut'] > 0) {
				$terms[] = "({$bobot['ut']}% × " . round($val_h, 1) . ")";
			}
			if ($bobot['buzz'] > 0 && ($has_buzz || $val_b > 0)) {
				$terms[] = "({$bobot['buzz']}% × " . round($val_b, 1) . ")";
			}
			if ($bobot['mid'] > 0) {
				$terms[] = "({$bobot['mid']}% × " . round($val_t, 1) . ")";
			}
			if ($bobot['final'] > 0) {
				$terms[] = "({$bobot['final']}% × " . round($val_a, 1) . ")";
			}
			if ($bobot['perf'] > 0 && ($kategori === 'without_written' || $is_grade_1_2 || $val_p > 0)) {
				$terms[] = "({$bobot['perf']}% × " . round($val_p, 1) . ")";
			}
		}

		if (empty($terms)) {
			$langkah_hitung = "No assessment scores recorded (Score: 0)";
			$penjelasan = "Assessment components not yet entered.";
		} else {
			$terms_str = implode(" + ", $terms);
			if ($is_grade_1_2) {
				if ($total_bobot < 100) {
					$langkah_hitung = "[ " . $terms_str . " ] / {$total_bobot}% = " . round($skor_tertimbang / 100, 2) . " / " . ($total_bobot / 100) . " = " . round($raw_nilai, 2) . " ≈ {$nilai_akhir}";
					$penjelasan = "Grade 1 & 2 dynamic weighting: No Mid/Final Exam. Active weight total is {$total_bobot}%.";
				} else {
					$langkah_hitung = $terms_str . " = " . round($raw_nilai, 2) . " ≈ {$nilai_akhir}";
					$penjelasan = "Grade 1 & 2 standard calculation (UT: 60%, Performance: 30%, Buzz: 10%). No Mid/Final Exam.";
				}
			} elseif ($total_bobot < 100) {
				$langkah_hitung = "[ " . $terms_str . " ] / {$total_bobot}% = " . round($skor_tertimbang / 100, 2) . " / " . ($total_bobot / 100) . " = " . round($raw_nilai, 2) . " ≈ {$nilai_akhir}";
				$penjelasan = $is_pts
					? "Mid-Term (PTS) dynamic weighting: Total bobot pembagi aktif adalah {$total_bobot}%. Komponen bernilai 0 atau tidak ada tidak dihitung ke pembagi."
					: "Dynamic weighting active: Active weight total is {$total_bobot}%. Student score is not penalized for unentered/unsynced components.";
			} else {
				$langkah_hitung = $terms_str . " = " . round($raw_nilai, 2) . " ≈ {$nilai_akhir}";
				$penjelasan = "Standard calculation with full 100% SOP weighting.";
			}
		}

		$rumus_dasar = [];
		if ($bobot['ut'] > 0) $rumus_dasar[] = "UT: {$bobot['ut']}%";
		if ($bobot['buzz'] > 0) $rumus_dasar[] = "Buzz: {$bobot['buzz']}%";
		if ($bobot['mid'] > 0) $rumus_dasar[] = "Mid: {$bobot['mid']}%";
		if ($bobot['final'] > 0) $rumus_dasar[] = "Final: {$bobot['final']}%";
		if ($bobot['perf'] > 0) {
			if ($is_grade_1_2) {
				$rumus_dasar[] = "Performance: {$bobot['perf']}%";
			} else {
				$rumus_dasar[] = ($kategori === 'without_written') ? "Alternative: {$bobot['perf']}%" : "Practical: {$bobot['perf']}%";
			}
		}
		$rumus_dasar_str = implode(" | ", $rumus_dasar);

		return [
			'nilai_akhir' => (int)$nilai_akhir,
			'kategori' => $kategori,
			'kategori_label' => $kat_label,
			'bobot' => $bobot,
			'total_bobot_aktif' => $total_bobot,
			'komponen' => [
				'ut' => round($val_h, 1),
				'buzz' => round($val_b, 1),
				'mid' => round($val_t, 1),
				'final' => round($val_a, 1),
				'perf' => round($val_p, 1)
			],
			'raw' => $raw_nilai,
			'langkah_hitung' => $langkah_hitung,
			'rumus_dasar' => $rumus_dasar_str,
			'penjelasan' => $penjelasan
		];
	}

	return (int)$nilai_akhir;
}

/**
 * Menghitung nilai akhir Keterampilan (Skill) berdasarkan SOP.
 *
 * @param array $components Komponen nilai: ['h' => ..., 'p' => ...]
 * @param int|string $id_or_code_or_name ID Mapel atau kode
 * @param bool $return_details True untuk mengembalikan array rincian audit
 * @return int|array Nilai keterampilan bulat atau array detail
 */
function hitung_nilai_keterampilan_sop($components, $id_or_code_or_name, $return_details = false)
{
	$raw_h = $components['h'] ?? 0;
	$val_h = 0;
	if (is_array($raw_h)) {
		$sum_h = 0;
		$cnt_h = 0;
		foreach ($raw_h as $item) {
			if (is_numeric($item)) {
				$sum_h += (float)$item;
				$cnt_h++;
			} elseif (is_string($item) && strpos($item, '//') !== false) {
				$p = explode('//', $item);
				if (is_numeric($p[0])) {
					$sum_h += (float)$p[0];
					$cnt_h++;
				}
			}
		}
		$val_h = ($cnt_h > 0) ? ($sum_h / $cnt_h) : 0;
	} elseif (is_numeric($raw_h)) {
		$val_h = (float)$raw_h;
	}

	$val_p = (float)($components['p'] ?? $components['perf'] ?? 0);
	$kategori = get_kategori_penilaian_mapel($id_or_code_or_name);

	if ($kategori === 'with_performance') {
		if ($val_p > 0 && $val_h > 0) {
			// Gabungan KD Keterampilan (50%) dan Practical Test (50%)
			$nilai_akhir = (int)round((0.5 * $val_h) + (0.5 * $val_p));
			$langkah = "(50% × " . round($val_h, 1) . ") + (50% × " . round($val_p, 1) . ") = " . round((0.5 * $val_h) + (0.5 * $val_p), 2) . " ≈ {$nilai_akhir}";
		} elseif ($val_p > 0) {
			$nilai_akhir = (int)round($val_p);
			$langkah = "100% Practical Test (" . round($val_p, 1) . ") = {$nilai_akhir}";
		} else {
			$nilai_akhir = (int)round($val_h);
			$langkah = "100% Skills Assessment (KD) (" . round($val_h, 1) . ") = {$nilai_akhir}";
		}
	} else {
		// Mapel without_performance atau without_written: nilai keterampilan murni dari KD Keterampilan
		if ($val_h > 0) {
			$nilai_akhir = (int)round($val_h);
			$langkah = "100% Skills Assessment (KD) (" . round($val_h, 1) . ") = {$nilai_akhir}";
		} elseif ($val_p > 0) {
			$nilai_akhir = (int)round($val_p);
			$langkah = "100% Practical / Alternative (" . round($val_p, 1) . ") = {$nilai_akhir}";
		} else {
			$nilai_akhir = 0;
			$langkah = "No skills assessment scores recorded (Score: 0)";
		}
	}

	if ($return_details) {
		return [
			'nilai_akhir' => (int)$nilai_akhir,
			'kategori' => $kategori,
			'komponen' => [
				'kd' => round($val_h, 1),
				'perf' => round($val_p, 1)
			],
			'langkah_hitung' => $langkah
		];
	}

	return (int)$nilai_akhir;
}

/**
 * Menghitung nilai ledger live untuk satu siswa per mata pelajaran sesuai SOP.
 * Digunakan sebagai fallback kalkulasi pada modul cetak_leger dan view_ledger jika rapor belum dikunci.
 *
 * @param int $id_siswa ID Siswa
 * @param string $tasm Kode TASM (contoh: '20251')
 * @param bool $is_pts True untuk Midterm (PTS), False untuk Final-Term / End-of-Year
 * @param int|string|null $tingkat Jenjang kelas siswa (misal: 7, 8, 9, 10, 11, 12)
 * @param bool $return_details True untuk menyertakan array rincian audit komponen & rumus
 * @return array ['np' => [id_mapel => nilai, ...], 'nk' => [id_mapel => nilai, ...]]
 */
function get_live_ledger_scores_siswa($id_siswa, $tasm, $is_pts = false, $tingkat = null, $return_details = false)
{
	$sem = (int)substr($tasm, 4, 1);
	if ($sem === 0) { $sem = 1; }

	// 1. Ambil data nilai pengetahuan
	$ambil_np = get_nilai_utama('t_nilai', $id_siswa, $tasm, $is_pts);
	$ambil_uts = get_nilai_uts('t_nilai', $id_siswa, $tasm, $is_pts);

	$mapel_comp_p = [];
	foreach ($ambil_np as $row) {
		$mid = (int)$row['idmapel'];
		if (!$mid) continue;
		if (!isset($mapel_comp_p[$mid])) {
			$mapel_comp_p[$mid] = ['h' => [], 't' => 0, 'a' => 0, 'p' => 0, 'b' => 0];
		}
		if ($row['jenis'] === 'h') {
			$mapel_comp_p[$mid]['h'][] = $row['nilai'];
		}
	}
	foreach ($ambil_uts as $row) {
		$mid = (int)$row['idmapel'];
		if (!$mid) continue;
		if (!isset($mapel_comp_p[$mid])) {
			$mapel_comp_p[$mid] = ['h' => [], 't' => 0, 'a' => 0, 'p' => 0, 'b' => 0];
		}
		if (in_array($row['jenis'], ['t', 'a', 'p', 'b'], true)) {
			$mapel_comp_p[$mid][$row['jenis']] = (float)$row['nilai'];
		}
	}

	$scores_p = [];
	$details_p = [];
	foreach ($mapel_comp_p as $mid => $comp) {
		if ($return_details) {
			$det = hitung_nilai_sop($comp, $mid, $sem, $is_pts, $tingkat, true);
			$scores_p[$mid] = $det['nilai_akhir'];
			$details_p[$mid] = $det;
		} else {
			$scores_p[$mid] = hitung_nilai_sop($comp, $mid, $sem, $is_pts, $tingkat);
		}
	}

	// 2. Ambil data nilai keterampilan
	$ambil_nk = get_nilai_utama('t_nilai_ket', $id_siswa, $tasm, $is_pts);
	$ambil_nkuts = get_nilai_uts('t_nilai_ket', $id_siswa, $tasm, $is_pts);

	$mapel_comp_k = [];
	foreach ($ambil_nk as $row) {
		$mid = (int)$row['idmapel'];
		if (!$mid) continue;
		if (!isset($mapel_comp_k[$mid])) {
			$mapel_comp_k[$mid] = ['h' => [], 'p' => 0];
		}
		if ($row['jenis'] === 'h') {
			$mapel_comp_k[$mid]['h'][] = $row['nilai'];
		}
	}
	foreach ($ambil_nkuts as $row) {
		$mid = (int)$row['idmapel'];
		if (!$mid) continue;
		if (!isset($mapel_comp_k[$mid])) {
			$mapel_comp_k[$mid] = ['h' => [], 'p' => 0];
		}
		if ($row['jenis'] === 'p') {
			$mapel_comp_k[$mid]['p'] = (float)$row['nilai'];
		}
	}

	foreach ($mapel_comp_k as $mid => &$comp) {
		if (empty($comp['p']) && !empty($mapel_comp_p[$mid]['p'])) {
			$comp['p'] = (float)$mapel_comp_p[$mid]['p'];
		}
	}
	unset($comp);

	$scores_k = [];
	$details_k = [];
	foreach ($mapel_comp_k as $mid => $comp) {
		if ($return_details) {
			$det_k = hitung_nilai_keterampilan_sop($comp, $mid, true);
			$scores_k[$mid] = $det_k['nilai_akhir'];
			$details_k[$mid] = $det_k;
		} else {
			$scores_k[$mid] = hitung_nilai_keterampilan_sop($comp, $mid);
		}
	}

	if ($return_details) {
		return [
			'np' => $scores_p,
			'nk' => $scores_k,
			'details_p' => $details_p,
			'details_k' => $details_k
		];
	}

	return [
		'np' => $scores_p,
		'nk' => $scores_k
	];
}
