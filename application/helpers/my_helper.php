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
					<li><a href="' . base_url() . 'home/koobits_report"><i class="pe-7s-notebook"></i><p>KooBits Reports<span class="flying-badge">NEW</span></p></a></li>
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
					<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Change Password</p></a></li>
					';
	} else if ($level == "guru") {
		$menu .= '
		<li><a href="' . base_url() . 'lesson_plan"><i class="pe-7s-magic-wand"></i><p>Lesson Plan Generator <span class="flying-badge">NEW</span></p></a></li>
		<li><a href="' . base_url() . 'view_mapel"><i class="pe-7s-note2"></i><p>Subjects</p></a></li>
		            <li><a href="' . base_url() . 'riwayat_mengajar"><i class="pe-7s-file"></i><p>Teaching History</p></a></li>
					';
		$menu .= $is_wali == TRUE ? '<li class="devider"></li>
		                            <li><a href="' . base_url() . 'pengumuman"><i class="pe-7s-bell"></i><p>Pengumuman</p></a></li>
		                            <li><a href="' . base_url() . 'view_kelompok"><i class="pe-7s-date"></i><p>Projek P5</p></a></li>
		                            <li><a href="' . base_url() . 'n_absensi"><i class="pe-7s-date"></i><p>Absensi</p></a></li>
		                            <li><a href="' . base_url() . 'n_ekstra"><i class="pe-7s-ball"></i><p>Ekstrakurikuler</p></a></li>
		                            <li><a href="' . base_url() . 'n_prestasi"><i class="pe-7s-ball"></i><p>Prestasi</p></a></li>
		                            <li><a href="' . base_url() . 'kehadiran"><i class="pe-7s-user"></i><p>Attendance</p></a></li>
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
									
		                            
		                            <li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Change Password</p></a></li>' :
			'<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Change Password</p></a></li>';
	} else if ($level == "siswa") {
		$menu .= '
					<li><a href="' . base_url() . 'lihat_raport"><i class="pe-7s-notebook"></i><p>Lihat Raport</p></a></li>
					<li><a href="' . base_url() . 'home/ubah_password"><i class="pe-7s-unlock"></i><p>Change Password</p></a></li>
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

function getJenisRaport($id_rapor, $tingkat)
{
	$CI2 = &get_instance();
	$jenis_rapor = $CI2->db->query("SELECT * FROM t_tahun_jenis_rapor
                        WHERE id_tahun = '" . $id_rapor . "'
                        AND tingkat = '" . $tingkat . "'
                        ")->row();
	return $jenis_rapor;
}

function siswa($id_siswa, $ta)
{
	$CI2 = &get_instance();
	$siswa = $CI2->db->query("SELECT 
                                    a.nama, a.nis, a.nisn, c.tingkat, c.id as id_kelas,b.ta
                                    FROM m_siswa a
                                    LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                    LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                    WHERE a.id = $id_siswa AND b.ta = '" . $ta . "'")->row();
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
                                WHERE a.id_kelas = '" . $id_kelas . "' AND a.tasm = '" . $tahun . "'")->row();
	return $wali_kelas;
}

function ta_active($tasm)
{
	$CI2 = &get_instance();
	$ta_active = $CI2->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun
	WHERE tahun = '$tasm'")->row();

	return $ta_active;
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

function catatan_homeroom($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_catatan_homeroom = $CI2->db->query("SELECT a.* FROM t_catatan_homeroom a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row();
	//echo $this->db->last_query();
	return $q_catatan_homeroom;
}
function catatan_naik_kelas($id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$q_catatan = $CI2->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row();
	//echo $this->db->last_query();
	return $q_catatan;
}

function get_nilai_utama($table, $id_siswa, $tasm, $mid = false)
{
	$CI2 = &get_instance();
	$query = $mid ? "AND d.mid_final=1" : '';
	$ambil_np = $CI2->db->query("SELECT 
                                    c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                    FROM $table a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa
									$query
                                    AND a.tasm = '" . $tasm . "'
                                    AND a.nilai != 0")->result_array();
	// echo json_encode($ambil_np);
	return $ambil_np;
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
function get_catatan_primary($id_siswa, $tasm, $id_guru_mapel)
{
	$CI2 = &get_instance();
	$ambil_nc = $CI2->db->query("SELECT * FROM `t_nilai` WHERE jenis = 'c' AND tasm = $tasm AND id_siswa = $id_siswa AND id_guru_mapel = $id_guru_mapel")->row();
	return $ambil_nc;
}
function get_catatan_keterampilan_primary($id_siswa, $tasm, $id_guru_mapel)
{
	$CI2 = &get_instance();
	$ambil_nc = $CI2->db->query("SELECT * FROM `t_nilai_ket` WHERE jenis = 'c' AND tasm = $tasm AND id_siswa = $id_siswa AND id_guru_mapel = $id_guru_mapel")->row();
	return $ambil_nc;
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

function get_nilai_sub($table, $id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$ambil_np_submp = $CI2->db->query("SELECT 
                                    b.id_mapel, c.kd_singkat
                                    FROM $table a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                    GROUP BY b.id_mapel")->result_array();
	return $ambil_np_submp;
}

function get_nilai_uts($table, $id_siswa, $tasm)
{
	$CI2 = &get_instance();
	$ambil_uts = $CI2->db->query("SELECT 
                        c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                        FROM $table a
                        INNER JOIN m_mapel c ON a.id_mapel_kd = c.id
                        WHERE 
                        a.jenis= 't'
                        AND a.id_siswa = $id_siswa
                        AND a.tasm = '" . $tasm . "'
						OR
                                a.jenis= 'a'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                                OR
                                a.jenis= 'c'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                                OR
                                a.jenis= 'p'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                        ")->result_array();
	return $ambil_uts;
}

function get_guru_mapel($id_mapel, $id_kelas, $tasm)
{
	$CI2 = &get_instance();
	$guru_mapel = $CI2->db->query("SELECT a.id id,kkm,a.nama nama, c.nama namaguru,b.id id_guru_mapel
										FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
										WHERE  a.id= $id_mapel AND b.id_kelas= $id_kelas AND b.tasm= $tasm")->row();
	return $guru_mapel;
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

function clean_text($text){
    $search = ["’","‘","“","”"];
    $replace = ["'","'","\"","\""];
    return str_replace($search,$replace,$text);
}
