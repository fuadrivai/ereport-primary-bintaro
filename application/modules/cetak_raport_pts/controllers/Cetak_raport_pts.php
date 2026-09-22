<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cetak_raport_pts extends CI_Controller
{
    // === CONFIG ===
    // Cache lives on DO volume but is bind-mounted into webroot:
    // mount --bind /mnt/volume_sgp1_02/document/rapor_cache/bintaro/primary \
    //              /www/wwwroot/report.mhis.link/bintaro/primary/storage_cache
    // private $CACHE_BASE = '/www/wwwroot/report.mhis.link/bintaro/primary/storage_cache/';

    // // Use a long random string and keep it secret
    // private $SECRET = 'sss';

    // // Path to Html2Pdf autoload (relative to FCPATH)
    // private $HTML2PDF_AUTOLOAD = 'aset/html2pdf/autoload.php';
    
    private $CACHE_BASE = '/mnt/volume_sgp1_02/document/rapor_cache/bintaro/primary/mid/';
    private $HTML2PDF_AUTOLOAD = FCPATH . 'aset/html2pdf/autoload.php';
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre . 'konid');
        $this->d['url'] = "cetak_raport_pts";

        $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($get_tasm['tahun'], 0, 4);
        $this->d['semester'] = substr($get_tasm['tahun'], 4, 1);

        $this->d['wk'] = $this->session->userdata('app_rapot_walikelas');
    }

    public function sampul1($id_siswa)
    {
        $d['ds'] = $this->db->query("SELECT nama, nis, nisn FROM m_siswa WHERE id = '$id_siswa'")->row_array();

        $this->load->view('cetak_sampul1', $d);
    }

    public function sampul2($id_siswa)
    {
        $d = null;

        $this->load->view('cetak_sampul2', $d);
    }

    public function sampul4($id_siswa)
    {
        $d['ds'] = $this->db->query("SELECT * FROM m_siswa WHERE id = '$id_siswa'")->row_array();
        $d['da'] = $this->db->query("SELECT * FROM tahun WHERE aktif = 'Y'")->row_array();

        $this->load->view('cetak_sampul4', $d);
    }

    public function prestasi_catatan($id_siswa, $tasm)
    {
        //$tasm = substr($tasm, 0, 4);
        $q_prestasi = $this->db->query("SELECT 
                                    a.*
                                    FROM t_prestasi a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();

        //echo $this->db->last_query();
        //exit;


        $q_catatan = $this->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();

        // echo $this->db->last_query();
        // exit;

        $d['prestasi'] = $q_prestasi;
        $d['catatan'] = $q_catatan;

        $this->load->view('cetak_prestasi', $d);
    }

    public function cetak($id_siswa, $tasm)
    {
        ob_start();
        $d = array();


        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);

        $siswa = $this->db->query("SELECT 
                                    a.nama, a.nis, a.nisn, c.tingkat, c.id idkelas
                                    FROM m_siswa a
                                    LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                    LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                    WHERE a.id = $id_siswa AND b.ta = '" . $d['ta'] . "'")->row_array();

        $d['det_siswa'] = $siswa;

        $d['wali_kelas'] = $this->db->query("SELECT 
                                a.*, b.nama nmguru, b.nip, 
                                c.tingkat, c.nama nmkelas
                                FROM t_walikelas a 
                                INNER JOIN m_guru b ON a.id_guru = b.id 
                                INNER JOIN m_kelas c ON a.id_kelas = c.id
                                WHERE a.id_kelas = '" . $d['det_siswa']['idkelas'] . "' AND a.tasm = '" . $this->d['ta'] . "'")->row_array();

        // Start NILAI PENGETAHUAN //
        $ambil_np = $this->db->query("SELECT 
                                    c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                    FROM t_nilai a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa
                                    AND d.mid_final=1
                                    AND a.tasm = '" . $tasm . "'")->result_array();


        $ambil_np_submp = $this->db->query("SELECT 
                                    b.id_mapel, c.kd_singkat
                                    FROM t_nilai a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                    GROUP BY b.id_mapel")->result_array();

        $array1 = array();

        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_mapel']] = array();
        }
        $array_kkm = array();
        foreach ($ambil_np as $a2) {
            $idx = $a2['idmapel'];
            $kkmx = $a2['kkm'];
            $array_kkm[] = $kkmx;
            $lang_mapel = $a2['lang'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }


        $kkm = array_unique($array_kkm);

        $d['kkm'] = '';
        foreach ($kkm as $kkmm) {
            $rentang = round(((100 - $kkmm) / 3), 0);
            $d['kkm'] .= '
            <tr>
                <td colspan="2">' . $kkmm . '</td>
                <td colspan="2">0 - ' . ($kkmm - 1) . '</td>
                <td colspan="2">' . $kkmm . ' - ' . ($kkmm + $rentang) . '</td>
                <td colspan="2">' . ($kkmm + ($rentang * 1) + 1) . ' - ' . ($kkmm + ($rentang * 2)) . '</td>
                <td colspan="2">' . ($kkmm + ($rentang * 2) + 1) . ' - 100</td>
            </tr>';
            $d['kkm'] = $d['kkm'];
        }
        //echo var_dump($array1);

        $bobot_h = $this->config->item('pnp_h');
        $bobot_t = $this->config->item('pnp_t');
        $bobot_a = $this->config->item('pnp_a');

        $jml_bobot = 100;

        //MULAI HITUNG..
        $nilai_pengetahuan = array();
        foreach ($array1 as $k => $v) {

            $jumlah_h = !empty($array1[$k]['h']) ? sizeof($array1[$k]['h']) : 0;
            $jumlah_n_h = 0;

            $desk = array();

            if (!empty($array1[$k]['h'])) {
                $arrayh = max($array1[$k]['h']);
                $arrayhmin = min($array1[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array1[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_n_h += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_n_h = 1;
                $jumlah_h = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }

            $__tengah = empty($array1[$k]['t']) ? 0 : $array1[$k]['t'];
            $__akhir = empty($array1[$k]['a']) ? 0 : $array1[$k]['a'];

            $_np = round((((100 / $jml_bobot) * ($jumlah_n_h / $jumlah_h))), 0);

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            }
        }
        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN

        // Start NILAI KETRAMPILAN //
        //ambil nilai untuk siswa ybs
        $ambil_nk = $this->db->query("SELECT 
                                    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                    FROM t_nilai_ket a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa AND d.mid_final=1
                                    AND a.tasm = '" . $tasm . "'")->result_array();
        $ambil_nicb = $this->db->query("SELECT 
        c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
        FROM t_nilai_icb a
        INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
        INNER JOIN m_mapel c ON b.id_mapel = c.id
        INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
        WHERE a.id_siswa = $id_siswa
        AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        $ambil_pss = $this->db->query("SELECT 
        c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
        FROM t_nilai_pss a
        INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
        INNER JOIN m_mapel c ON b.id_mapel = c.id
        INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
        WHERE a.id_siswa = $id_siswa
        AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();
        $ambil_la = $this->db->query("SELECT 
        c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
        FROM t_nilai_la a
        INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
        INNER JOIN m_mapel c ON b.id_mapel = c.id
        INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
        WHERE a.id_siswa = $id_siswa
        AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        //echo var_dump($ambil_nk);
        //ambil id mapel, kode singkat
        $ambil_nk_submk = $this->db->query("SELECT 
                                    b.id_mapel, c.kd_singkat
                                    FROM t_nilai_ket a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                    GROUP BY b.id_mapel")->result_array();
        //echo j($ambil_nk_submk);
        $ambil_nc = $this->db->query("SELECT 
                                    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai_mid as nilai
                                    FROM t_nilai_cat a
                                    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_mapel c ON b.id_mapel = c.id
                                    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                    WHERE a.id_siswa = $id_siswa
                                    AND a.tasm = '" . $tasm . "'")->result_array();
        $array2 = array();

        foreach ($ambil_nk_submk as $a11) {
            $array2[$a11['id_mapel']] = array();
        }

        //echo j($ambil_nk);

        foreach ($ambil_nk as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            if ($a22['jenis'] == "h") {
                $array2[$idx]['h'][] = $a22['nilai'];
            } else if ($a22['jenis'] == "p") {
                $array2[$idx]['p'] = $a22['nilai'];
            } else if ($a22['jenis'] == "t") {
                $array2[$idx]['t'] = $a22['nilai'];
            } else if ($a22['jenis'] == "a") {
                $array2[$idx]['a'] = $a22['nilai'];
            }
        }
        foreach ($ambil_nicb as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['icb'][] = $a22['nilai'];
        }

        foreach ($ambil_pss as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['pss'][] = $a22['nilai'];
        }
        foreach ($ambil_la as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['la'][] = $a22['nilai'];
        }
        foreach ($ambil_nc as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['c'] = $a22['nilai'];
        }

        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = 100;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
        $icb_aspect = array();
        $pss_aspect = array();
        $la_aspect = array();
        foreach ($array2 as $k => $v) {
            $jumlah_array_nilai = !empty($array2[$k]['h']) ? sizeof($array2[$k]['h']) : 0;
            $jumlah_nilai = 0;

            $desk = array();
            if (!empty($array2[$k]['h'])) {
                $arrayh = max($array2[$k]['h']);
                $arrayhmin = min($array2[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array2[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_nilai += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_array_nilai = 1;
                $jumlah_nilai = 1;
            }

            if (!empty($array2[$k]['icb'])) {
                foreach ($array2[$k]['icb'] as $icb) {
                    $icb_aspect[$k]['aspect'][] = $icb;
                }
            }
            if (!empty($array2[$k]['pss'])) {
                foreach ($array2[$k]['pss'] as $pss) {
                    $pss_aspect[$k]['aspect'][] = $pss;
                }
            }
            if (!empty($array2[$k]['la'])) {
                foreach ($array2[$k]['la'] as $la) {
                    $la_aspect[$k]['aspect'][] = $la;
                }
            }


            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            $nilai_keterampilan[$k]['catatan'] = empty($array2[$k]['c']) ? '-' : $array2[$k]['c'];
            $_nilai_keterampilan = round((((100 / $jml_bobot) * ($jumlah_nilai / $jumlah_array_nilai))), 0);

            $nilai_keterampilan[$k]['nilai'] = number_format($_nilai_keterampilan);
            $nilai_keterampilan[$k]['predikat'] = nilai_huruf($kkmx, $_nilai_keterampilan);
            $nilai_keterampilan[$k]['desk'] = implode("; ", $txt_desk);
        }
        //echo j($nilai_keterampilan);
        $d['nilai_keterampilan'] = $nilai_keterampilan;
        //j($nilai_keterampilan);
        //exit;
        // END Nilai PENGETAHUAN

        //===========================================================================
        //       START NIlai Sikap SPIRITUAL
        //===========================================================================

        $q_nilai_sikap_sp = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_sp WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();

        $q_kd_nilai_sikap_sp = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSp'")->result_array();

        $list_kd_sp = array();
        foreach ($q_kd_nilai_sikap_sp as $k) {
            $list_kd_sp[$k['id']] = $k['nama_kd'];
        }

        //jika belum ada nilai sikap sp yang diinputkan
        if (!empty($q_nilai_sikap_sp['selalu'])) {
            $pc_selalu = explode("-", $q_nilai_sikap_sp['selalu']);
            $sll_1 = $pc_selalu[0];
            $sll_2 = $pc_selalu[1];
            $mngkt = $q_nilai_sikap_sp['mulai_meningkat'];

            $selalu1 = $list_kd_sp[$sll_1];
            $selalu2 = $list_kd_sp[$sll_2];
            $mulai_meningkat = $list_kd_sp[$mngkt];


            $nilai_sikap_spiritual = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $selalu1 . ', ' . $selalu2 . ' dan Mulai meningkat pada sikap : ' . $mulai_meningkat;
        } else {
            $selalu1 = '';
            $selalu2 = '';
            $mulai_meningkat = '';

            $nilai_sikap_spiritual = 'Belum diinput';
        }


        $d['nilai_sikap_spiritual'] = $nilai_sikap_spiritual;
        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Sikap SOSIAL
        //===========================================================================

        $q_nilai_sikap_so = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_so WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();
        //echo $this->db->last_query();
        //exit;

        $q_kd_nilai_sikap_so = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSo'")->result_array();

        $so_text_selalu = "";
        $so_mulai_meningkat = "";

        $list_kd_so = array();
        foreach ($q_kd_nilai_sikap_so as $k) {
            $list_kd_so[$k['id']] = $k['nama_kd'];
        }

        $so_pc_selalu = explode(",", $q_nilai_sikap_so['selalu']);
        $so_mulai_meningkat = $q_nilai_sikap_so['mulai_meningkat'];

        if ($so_pc_selalu[0] == "") {
            $nilai_sikap_sosial = 'Belum diinput';
        } else if ($so_pc_selalu[0] != "" && sizeof($so_pc_selalu) > 0) {
            $so_teks_selalu = array();

            //echo var_dump($q_nilai_sikap_so);

            for ($i = 0; $i < sizeof($so_pc_selalu); $i++) {
                $idx = $so_pc_selalu[$i];
                $so_teks_selalu[] = $list_kd_so[$idx];
            }

            $so_text_selalu = implode(", ", $so_teks_selalu);

            $so_mulai_meningkat = $list_kd_so[$so_mulai_meningkat];

            $nilai_sikap_sosial = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $so_text_selalu . ' dan Mulai meningkat pada sikap : ' . $so_mulai_meningkat;
        } else {
            $nilai_sikap_sosial = 'Belum diinput';
        }


        $d['nilai_sikap_sosial'] = $nilai_sikap_sosial;

        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Ekstrakurikuler
        //===========================================================================
        $q_nilai_ekstra = $this->db->query("SELECT 
                                            b.nama, a.nilai, a.desk
                                            FROM t_nilai_ekstra a
                                            INNER JOIN m_ekstra b ON a.id_ekstra = b.id
                                            WHERE a.id_siswa = $id_siswa AND a.nilai != '0' AND a.tasm = '" . $tasm . "'")->result_array();
        //echo $this->db->last_query();

        $d['nilai_ekstra'] = $q_nilai_ekstra;

        //===========================================================================
        //              START NIlai Prestasi
        //===========================================================================
        $q_prestasi = $this->db->query("SELECT 
                                    a.*
                                    FROM t_prestasi a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
        //echo $this->db->last_query();

        $d['prestasi'] = $q_prestasi;

        //===========================================================================
        //              START NIlai Absensi
        //===========================================================================
        $q_nilai_absensi = $this->db->query("SELECT 
                                            s, i, a
                                            FROM t_nilai_absensi
                                            WHERE id_siswa = $id_siswa AND tasm = '" . $tasm . "'")->row_array();

        $d['nilai_absensi'] = $q_nilai_absensi;

        $d['nilai_utama'] = '';
        $d['nilai_keterampilan'] = '';
        $d['icb_aspect'] = '';
        $d['pss_aspect'] = '';
        $d['la_aspect'] = '';

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");


        $no = 0;
        $noket = 0;


        //foreach ($q_mapel as $m) {
        //PAI kelompok A
        if ($this->config->item('is_kemenag') == TRUE) {
            $d['nilai_utama'] .= '<tr><td class="ctr">' . $no . '</td><td colspan="9">Pendidikan Agama Islam</td></tr>';
            $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'PAI'")->result_array();

            foreach ($q_mapel as $i => $m) {
                $kkmx = $m['kkm'];

                $idx = $m['id'];
                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];
                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } else {
                    $predikatx1 = "belum tuntas";
                }

                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". " . $nilai_keterampilan[$idx]['desk'];

                $d['nilai_utama'] .= '
                                        <tr>
                                            <td class="ctr"></td>
                                            <td>' . $arr_huruf[$i] . '. ' . $m['nama'] . '</td>
                                            <td class="ctr">' . $m['kkm'] . '</td>
                                            <td class="ctr">' . $npa . '</td>
                                            <td class="ctr">' . $npp . '</td>
                                            <td class="font_kecil">' . $npd . '</td>
                                            <td class="ctr">' . $nka . '</td>
                                            <td class="ctr">' . $nkp . '</td>
                                            <td class="font_kecil">' . $nkd . '</td>
                                        </tr>';
            }
        }

        $no++;
        $noket++;
        //no ICB
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
             WHERE kelompok = 'ICB' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($icb_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);
                $d['icb_aspect'] .= '<tr>
                    <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['icb_aspect'] .= '
                            <td style="text-align:center;">V</td>
                            <td></td>
                            <td></td>
                            </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['icb_aspect'] .= '
                            <td></td>
                            <td style="text-align:center;">V</td>
                            <td></td>
                            </tr>';
                } else {
                    $d['icb_aspect'] .= '
                            <td></td>
                            <td></td>
                            <td style="text-align:center;">V</td>
                            </tr>';
                }
            }
        }
        //no PSS
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
             WHERE kelompok = 'PSS' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($pss_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['pss_aspect'] .= '<tr>
                    <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['pss_aspect'] .= '
                            <td style="text-align:center;">V</td>
                            <td></td>
                            <td></td>
                            </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['pss_aspect'] .= '
                            <td></td>
                            <td style="text-align:center;">V</td>
                            <td></td>
                            </tr>';
                } else {
                    $d['pss_aspect'] .= '
                            <td></td>
                            <td></td>
                            <td style="text-align:center;">V</td>
                            </tr>';
                }
            }
        }
        //no LA
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
             WHERE kelompok = 'LA' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($la_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['la_aspect'] .= '<tr>
                    <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['la_aspect'] .= '
                            <td style="text-align:center;">V</td>
                            <td></td>
                            <td></td>
                            </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['la_aspect'] .= '
                            <td></td>
                            <td style="text-align:center;">V</td>
                            <td></td>
                            </tr>';
                } else {
                    $d['la_aspect'] .= '
                            <td></td>
                            <td></td>
                            <td style="text-align:center;">V</td>
                            </tr>';
                }
            }
        }

        //no pai kelompok A
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                        INNER JOIN m_guru c ON b.id_guru = c.id
             WHERE kelompok = 'A' AND tambahan_sub = 'NO'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];


            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }
            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }
            $d['nilai_utama'] .= '
                                    <table>
                                    <tr>
                                        <td>
                                            <table style="font-weight: bold;">
                                            <tr>
                                                <td></td>
                                                <td style="width:550px;color:#0162b1;">' . $m['nama'] . '</td>
                                                <td>Teacher: ' . $m['namaguru'] . '</td>
                                            </tr>
                                            </table>
                                            <table class="table" style="text-align: center;">
                                            <tr style="color:#0162b1;font-weight: bold;">
                                                <td colspan="2" style="width:100px;padding: 20px 10px;">Passing Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Knowledge Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Skill Grade</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 20px 10px;">' . $m['kkm'] . '</td>
                                                <td style="padding: 20px 10px;">' . $npa . '</td>
                                                <td style="padding: 20px 10px;">' . $nka . '</td>     
                                            </tr>
                                            </table>
                                            <table>
                                            <tr>
                                                <td></td>
                                                <td>Comment:</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="width:710px;text-align: justify;text-justify: inter-word;">
                                                    ' . $catatan . '
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
        }

        //no pai kelompok B

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
            INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
            INNER JOIN m_guru c ON b.id_guru = c.id
            WHERE kelompok = 'B' AND tambahan_sub = 'NO'")->result_array();

        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }

            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];


            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];

            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }

            $d['nilai_utama'] .= '
                                    <table>
                                    <tr>
                                        <td>
                                            <table style="font-weight: bold;">
                                            <tr>
                                                <td></td>
                                                <td style="width:550px;color:#0162b1;">' . $m['nama'] . '</td>
                                                <td>Teacher: ' . $m['namaguru'] . '</td>
                                            </tr>
                                            </table>
                                            <table class="table" style="text-align: center;">
                                            <tr style="color:#0162b1;font-weight: bold;">
                                                <td colspan="2" style="width:100px;padding: 20px 10px;">Passing Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Knowledge Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Skill Grade</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 20px 10px;">' . $m['kkm'] . '</td>
                                                <td style="padding: 20px 10px;">' . $npa . '</td>
                                                <td style="padding: 20px 10px;">' . $nka . '</td>     
                                            </tr>
                                            </table>
                                            <table>
                                            <tr>
                                                <td></td>
                                                <td>Comment:</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="width:710px;text-align: justify;text-justify: inter-word;">
                                                    ' . $catatan . '
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
        }
        //no pai kelompok C
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
            INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
            INNER JOIN m_guru c ON b.id_guru = c.id
            WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {

            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }

                $d['nilai_utama'] .= '
                                    <table>
                                    <tr>
                                        <td>
                                            <table style="font-weight: bold;">
                                            <tr>
                                                <td></td>
                                                <td style="width:550px;color:#0162b1;">' . $m['nama'] . '</td>
                                                <td>Teacher: ' . $m['namaguru'] . '</td>
                                            </tr>
                                            </table>
                                            <table class="table" style="text-align: center;">
                                            <tr style="color:#0162b1;font-weight: bold;">
                                                <td colspan="2" style="width:100px;padding: 20px 10px;">Passing Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Knowledge Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Skill Grade</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 20px 10px;">' . $m['kkm'] . '</td>
                                                <td style="padding: 20px 10px;">' . $npa . '</td>
                                                <td style="padding: 20px 10px;">' . $nka . '</td>     
                                            </tr>
                                            </table>
                                            <table>
                                            <tr>
                                                <td></td>
                                                <td>Comment:</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="width:710px;text-align: justify;text-justify: inter-word;">
                                                    ' . $catatan . '
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
            }
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
            INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
            INNER JOIN m_guru c ON b.id_guru = c.id
            WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];

                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }

                $d['nilai_utama'] .= '
                                    <table>
                                    <tr>
                                        <td>
                                            <table style="font-weight: bold;">
                                            <tr>
                                                <td></td>
                                                <td style="width:550px;color:#0162b1;">' . $m['nama'] . '</td>
                                                <td>Teacher: ' . $m['namaguru'] . '</td>
                                            </tr>
                                            </table>
                                            <table class="table" style="text-align: center;">
                                            <tr style="color:#0162b1;font-weight: bold;">
                                                <td colspan="2" style="width:100px;padding: 20px 10px;">Passing Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Knowledge Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Skill Grade</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 20px 10px;">' . $m['kkm'] . '</td>
                                                <td style="padding: 20px 10px;">' . $npa . '</td>
                                                <td style="padding: 20px 10px;">' . $nka . '</td>     
                                            </tr>
                                            </table>
                                            <table>
                                            <tr>
                                                <td></td>
                                                <td>Comment:</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="width:710px;text-align: justify;text-justify: inter-word;">
                                                    ' . $catatan . '
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
            }
        }

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                INNER JOIN m_guru c ON b.id_guru = c.id
                WHERE kelompok = 'B' AND tambahan_sub = 'MULOK'")->result_array();

        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }

            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }

            $d['nilai_utama'] .= '
                                    <table>
                                    <tr>
                                        <td>
                                            <table style="font-weight: bold;">
                                            <tr>
                                                <td></td>
                                                <td style="width:550px;color:#0162b1;">' . $m['nama'] . '</td>
                                                <td>Teacher: ' . $m['namaguru'] . '</td>
                                            </tr>
                                            </table>
                                            <table class="table" style="text-align: center;">
                                            <tr style="color:#0162b1;font-weight: bold;">
                                                <td colspan="2" style="width:100px;padding: 20px 10px;">Passing Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Knowledge Grade</td>
                                                <td style="width:100px;padding: 20px 10px;">Skill Grade</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 20px 10px;">' . $m['kkm'] . '</td>
                                                <td style="padding: 20px 10px;">' . $npa . '</td>
                                                <td style="padding: 20px 10px;">' . $nka . '</td>     
                                            </tr>
                                            </table>
                                            <table>
                                            <tr>
                                                <td></td>
                                                <td>Comment:</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="width:710px;text-align: justify;text-justify: inter-word;">
                                                    ' . $catatan . '
                                                </td>
                                            </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>';
        }

        //}
        //}
        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();

        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;
        $q_catatan_homeroom = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_homeroom a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan_homeroom'] = $q_catatan_homeroom;
        $this->load->view('cetak_pts_mh', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', false, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);

        $pdf->WriteHTML($str);
        $pdf->Output('Data Siswa.pdf');
    }

    public function cetak_sd($id_siswa, $tasm)
    {
        ob_start();
        ini_set('display_errors', 0);
        $d = array();


        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);
        $ta = substr($tasm, 0, 4);

        $siswa = $this->db->query("SELECT 
                                a.nama, a.nis, a.nisn, c.tingkat, c.id idkelas
                                FROM m_siswa a
                                LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                WHERE a.id = $id_siswa AND b.ta = '" . $d['ta'] . "'")->row_array();

        $d['det_siswa'] = $siswa;
        $tingkat = $siswa['tingkat'];

        $d['wali_kelas'] = $this->db->query("SELECT 
                            a.*, b.nama nmguru, b.nip, 
                            c.tingkat, c.nama nmkelas
                            FROM t_walikelas a 
                            INNER JOIN m_guru b ON a.id_guru = b.id 
                            INNER JOIN m_kelas c ON a.id_kelas = c.id
                            WHERE a.id_kelas = '" . $d['det_siswa']['idkelas'] . "' AND a.tasm = '" . $ta . "'")->row_array();

        // Start NILAI PENGETAHUAN //
        $ambil_np = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "'
                                AND a.nilai != 0
                                ")->result_array();
        $ambil_uts = $this->db->query("SELECT 
                        c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                        FROM t_nilai a
                        INNER JOIN m_mapel c ON a.id_mapel_kd = c.id
                        WHERE 
                        a.jenis= 't'
                        AND a.id_siswa = $id_siswa
                        AND a.tasm = '" . $tasm . "'
        ")->result_array();


        $ambil_np_submp = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();

        $array1 = array();

        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_mapel']] = array();
        }
        $array_kkm = array();
        foreach ($ambil_np as $a2) {
            $idx = $a2['idmapel'];
            $kkmx = $a2['kkm'];
            $array_kkm[] = $kkmx;
            $lang_mapel = $a2['lang'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }
        foreach ($ambil_uts as $a2) {
            $idx = $a2['idmapel'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {

                $array1[$idx]['t'] = $a2['nilai'];
                $array1[$idx]['kd_singkat'] = $a2['kd_singkat'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }


        $kkm = array_unique($array_kkm);

        $d['kkm'] = '';
        foreach ($kkm as $kkmm) {
            $rentang = round(((100 - $kkmm) / 3), 0);
            $d['kkm'] .= '
        <tr>
            <td colspan="2">' . $kkmm . '</td>
            <td colspan="2">0 - ' . ($kkmm - 1) . '</td>
            <td colspan="2">' . $kkmm . ' - ' . ($kkmm + $rentang) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 1) + 1) . ' - ' . ($kkmm + ($rentang * 2)) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 2) + 1) . ' - 100</td>
        </tr>';
            $d['kkm'] = $d['kkm'];
        }
        //echo var_dump($array1);

        $bobot_h = $this->config->item('pnp_h');
        $bobot_t = $this->config->item('pnp_t');
        $bobot_a = $this->config->item('pnp_a');

        $jml_bobot = 100;

        //MULAI HITUNG..
        $nilai_pengetahuan = array();
        $nilai_pengetahuan_uts = array();
        foreach ($array1 as $k => $v) {

            $jumlah_h = !empty($array1[$k]['h']) ? sizeof($array1[$k]['h']) : 0;
            $jumlah_n_h = 0;

            $desk = array();

            if (!empty($array1[$k]['h'])) {
                $arrayh = max($array1[$k]['h']);
                $arrayhmin = min($array1[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array1[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_n_h += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_n_h = 1;
                $jumlah_h = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }

            $__tengah = empty($array1[$k]['t']) ? 0 : $array1[$k]['t'];
            $__akhir = empty($array1[$k]['a']) ? 0 : $array1[$k]['a'];
            $kd_singkat = empty($array1[$k]['kd_singkat']) ? 0 : $array1[$k]['kd_singkat'];
            if ($tingkat == 1 || $tingkat == 2) {
                $_np = round(($jumlah_n_h / $jumlah_h), 0);
            } else {
                $_np = round((((2 * ($jumlah_n_h / $jumlah_h)) + $__tengah) / 3), 0);
            }


            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['uts'] = $__tengah;
            $nilai_pengetahuan[$k]['kd_singkat'] = $kd_singkat;
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            }
        }
        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN

        // Start NILAI KETRAMPILAN //
        //ambil nilai untuk siswa ybs
        $ambil_nk = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "' AND a.nilai != 0")->result_array();
        $ambil_nkuts = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                                FROM t_nilai_ket a
                                INNER JOIN m_mapel c ON a.id_mapel_kd = c.id
                                WHERE 
                                a.jenis= 't'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                                ")->result_array();
        $ambil_nicb = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_icb a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();



        $ambil_pss = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_pss a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();
        $ambil_la = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_la a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        //echo var_dump($ambil_nk);
        //ambil id mapel, kode singkat
        $ambil_nk_submk = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();
        //echo j($ambil_nk_submk);
        $ambil_nc = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai_mid as nilai
                                FROM t_nilai_cat a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'")->result_array();
        $array2 = array();

        foreach ($ambil_nk_submk as $a11) {
            $array2[$a11['id_mapel']] = array();
        }

        //echo j($ambil_nk);

        foreach ($ambil_nk as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            if ($a22['jenis'] == "h") {
                $array2[$idx]['h'][] = $a22['nilai'];
            } else if ($a22['jenis'] == "p") {
                $array2[$idx]['p'] = $a22['nilai'];
            } else if ($a22['jenis'] == "t") {
                $array2[$idx]['t'] = $a22['nilai'];
            } else if ($a22['jenis'] == "a") {
                $array2[$idx]['a'] = $a22['nilai'];
            }
        }
        foreach ($ambil_nkuts as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            if ($a22['jenis'] == "h") {
                $array2[$idx]['h'][] = $a22['nilai'];
            } else if ($a22['jenis'] == "p") {
                $array2[$idx]['p'] = $a22['nilai'];
            } else if ($a22['jenis'] == "t") {
                $array2[$idx]['t'] = $a22['nilai'];
            } else if ($a22['jenis'] == "a") {
                $array2[$idx]['a'] = $a22['nilai'];
            }
        }
        foreach ($ambil_nicb as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['icb'][] = $a22['nilai'];
        }

        foreach ($ambil_pss as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['pss'][] = $a22['nilai'];
        }
        foreach ($ambil_la as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['la'][] = $a22['nilai'];
        }
        foreach ($ambil_nc as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['c'] = $a22['nilai'];
        }

        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = 100;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
        $icb_aspect = array();
        $pss_aspect = array();
        $la_aspect = array();
        foreach ($array2 as $k => $v) {
            $jumlah_array_nilai = !empty($array2[$k]['h']) ? sizeof($array2[$k]['h']) : 0;
            $jumlah_nilai = 0;

            $desk = array();
            if (!empty($array2[$k]['h'])) {
                $arrayh = max($array2[$k]['h']);
                $arrayhmin = min($array2[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array2[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_nilai += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_array_nilai = 1;
                $jumlah_nilai = 1;
            }

            if (!empty($array2[$k]['icb'])) {
                foreach ($array2[$k]['icb'] as $icb) {
                    $icb_aspect[$k]['aspect'][] = $icb;
                }
            }
            if (!empty($array2[$k]['pss'])) {
                foreach ($array2[$k]['pss'] as $pss) {
                    $pss_aspect[$k]['aspect'][] = $pss;
                }
            }
            if (!empty($array2[$k]['la'])) {
                foreach ($array2[$k]['la'] as $la) {
                    $la_aspect[$k]['aspect'][] = $la;
                }
            }


            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            $nilai_keterampilan[$k]['catatan'] = empty($array2[$k]['c']) ? '-' : $array2[$k]['c'];
            if ($tingkat == 1 || $tingkat == 2) {
                $_nilai_keterampilan = round(($jumlah_nilai / $jumlah_array_nilai), 0);
            } else {
                $_nilai_keterampilan = round((((2 * ($jumlah_nilai / $jumlah_array_nilai)) + $__tengah) / 3), 0);
            }



            $nilai_keterampilan[$k]['nilai'] = number_format($_nilai_keterampilan);
            $nilai_keterampilan[$k]['predikat'] = nilai_huruf($kkmx, $_nilai_keterampilan);
            $nilai_keterampilan[$k]['desk'] = implode("; ", $txt_desk);
        }
        //echo j($nilai_keterampilan);
        $d['nilai_keterampilan'] = $nilai_keterampilan;
        //j($nilai_keterampilan);
        //exit;
        // END Nilai PENGETAHUAN

        //===========================================================================
        //       START NIlai Sikap SPIRITUAL
        //===========================================================================

        $q_nilai_sikap_sp = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_sp WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();

        $q_kd_nilai_sikap_sp = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSp'")->result_array();

        $list_kd_sp = array();
        foreach ($q_kd_nilai_sikap_sp as $k) {
            $list_kd_sp[$k['id']] = $k['nama_kd'];
        }

        //jika belum ada nilai sikap sp yang diinputkan
        if (!empty($q_nilai_sikap_sp['selalu'])) {
            $pc_selalu = explode("-", $q_nilai_sikap_sp['selalu']);
            $sll_1 = $pc_selalu[0];
            $sll_2 = $pc_selalu[1];
            $mngkt = $q_nilai_sikap_sp['mulai_meningkat'];

            $selalu1 = $list_kd_sp[$sll_1];
            $selalu2 = $list_kd_sp[$sll_2];
            $mulai_meningkat = $list_kd_sp[$mngkt];


            $nilai_sikap_spiritual = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $selalu1 . ', ' . $selalu2 . ' dan Mulai meningkat pada sikap : ' . $mulai_meningkat;
        } else {
            $selalu1 = '';
            $selalu2 = '';
            $mulai_meningkat = '';

            $nilai_sikap_spiritual = 'Belum diinput';
        }


        $d['nilai_sikap_spiritual'] = $nilai_sikap_spiritual;
        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Sikap SOSIAL
        //===========================================================================

        // $q_nilai_sikap_so = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_so WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();
        // //echo $this->db->last_query();
        // //exit;

        // $q_kd_nilai_sikap_so = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSo'")->result_array();

        // $so_text_selalu = "";
        // $so_mulai_meningkat = "";

        // $list_kd_so = array();
        // foreach ($q_kd_nilai_sikap_so as $k) {
        //     $list_kd_so[$k['id']] = $k['nama_kd'];
        // }

        // $so_pc_selalu = explode(",", $q_nilai_sikap_so['selalu']);
        // $so_mulai_meningkat = $q_nilai_sikap_so['mulai_meningkat'];

        // if ($so_pc_selalu[0] == "") {
        //     $nilai_sikap_sosial = 'Belum diinput';
        // } else if ($so_pc_selalu[0] != "" && sizeof($so_pc_selalu) > 0) {
        //     $so_teks_selalu = array();

        //     //echo var_dump($q_nilai_sikap_so);

        //     for ($i = 0; $i < sizeof($so_pc_selalu); $i++) {
        //         $idx = $so_pc_selalu[$i];
        //         $so_teks_selalu[] = $list_kd_so[$idx];
        //     }

        //     $so_text_selalu = implode(", ", $so_teks_selalu);

        //     $so_mulai_meningkat = $list_kd_so[$so_mulai_meningkat];

        //     $nilai_sikap_sosial = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $so_text_selalu . ' dan Mulai meningkat pada sikap : ' . $so_mulai_meningkat;
        // } else {
        //     $nilai_sikap_sosial = 'Belum diinput';
        // }


        // $d['nilai_sikap_sosial'] = $nilai_sikap_sosial;

        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Ekstrakurikuler
        //===========================================================================
        $q_nilai_ekstra = $this->db->query("SELECT 
                                        b.nama, a.nilai, a.desk
                                        FROM t_nilai_ekstra a
                                        INNER JOIN m_ekstra b ON a.id_ekstra = b.id
                                        WHERE a.id_siswa = $id_siswa AND a.nilai != '0' AND a.tasm = '" . $tasm . "'")->result_array();
        //echo $this->db->last_query();

        $d['nilai_ekstra'] = $q_nilai_ekstra;

        //===========================================================================
        //              START NIlai Prestasi
        //===========================================================================
        $q_prestasi = $this->db->query("SELECT 
                                a.*
                                FROM t_prestasi a 
                                LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
        //echo $this->db->last_query();

        $d['prestasi'] = $q_prestasi;
        //===========================================================================
        //              START NIlai Kl-1
        //===========================================================================
        $q_kl1 = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_kl1 a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm' LIMIT 1")->result_array();
        //echo $this->db->last_query();

        $d['nilai_kl1'] = $q_kl1;
        //===========================================================================
        //              START NIlai Kl-2
        //===========================================================================
        $q_kl2 = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_kl2 a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm' LIMIT 1")->result_array();
        //echo $this->db->last_query();

        $d['nilai_kl2'] = $q_kl2;
        //===========================================================================
        //              START NIlai Absensi
        //===========================================================================
        $q_nilai_absensi = $this->db->query("SELECT 
                                        s, i, a
                                        FROM t_nilai_absensi
                                        WHERE id_siswa = $id_siswa AND tasm = '" . $tasm . "'")->row_array();

        $d['nilai_absensi'] = $q_nilai_absensi;

        $d['nilai_utama'] = '';
        $d['nilai_keterampilan'] = '';
        $d['icb_aspect'] = '';
        $d['pss_aspect'] = '';
        $d['la_aspect'] = '';

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");


        $no = 0;
        $noket = 0;


        //foreach ($q_mapel as $m) {
        //PAI kelompok A

        $no++;
        $noket++;


        //no pai kelompok A
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'A' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'
         ORDER BY a.id ASC")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];


            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }
            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }
            if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $nka . '</td>
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:200px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $nka . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }

        //no pai kelompok B

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'B' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'
        ORDER BY a.id ASC")->result_array();

        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            if ($nilai_pengetahuan[$idx]['kd_singkat'] == 'GP' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'SBK' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'DL' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'PE' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'Music') {
                $uts = "-";
            } else {
                $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            }
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }
            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }
            if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $nka . '</td>
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:200px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $nka . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }
        //no pai kelompok C
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'PUS' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO' ORDER BY id ASC")->num_rows();

        if ($count > 0) {
            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
                if ($nilai_pengetahuan[$idx]['kd_singkat'] == 'GP' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'SBK' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'DL' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'PE' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'Music') {
                    $uts = "-";
                } else {
                    $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
                }
                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }
                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }
                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }
                if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $nka . '</td>
                                    </tr>
                            ';
                } else {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:200px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $nka . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
                }
            }
        }

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
            INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
            INNER JOIN m_guru c ON b.id_guru = c.id
            WHERE kelompok = 'B' AND tambahan_sub = 'MULOK' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'
            ORDER BY a.id ASC")->result_array();

        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            if ($nilai_pengetahuan[$idx]['kd_singkat'] == 'GP' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'SBK' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'DL' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'PE' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'Music') {
                $uts = "-";
            } else {
                $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            }
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }
            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }
            if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $nka . '</td>
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:200px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $nka . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }
        if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
            $d['nilai_utama'] .= '<tr>
        <td class="ctr"></td>
        <td colspan="2" style="width:295px;">Muatan Lokal</td>
        <td colspan="2" class="ctr" style="width:80px;padding:10px;"></td>
        <td colspan="2" class="ctr" style="width:80px;padding:10px;"></td>
        <td colspan="2" class="ctr" style="width:80px;padding:10px;"></td> 
        </tr>';
        } else {
            $d['nilai_utama'] .= '<tr>
        <td class="ctr"></td>
        <td colspan="2" style="width:200px;">Muatan Lokal</td>
        <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
        <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
        <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td> 
        <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td> 
        </tr>';
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND a.kd_singkat != 'BTQ' AND b.tasm= '" . $tasm . "'
        ORDER BY a.id ASC")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {

            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];

                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
                if ($nilai_pengetahuan[$idx]['kd_singkat'] == 'GP' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'SBK' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'DL' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'PE' || $nilai_pengetahuan[$idx]['kd_singkat'] == 'Music') {
                    $uts = "-";
                } else {
                    $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
                }
                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }
                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }
                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }
                if ($d['det_siswa']['tingkat'] == 1 || $d['det_siswa']['tingkat'] == 2) {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $nka . '</td>
                                    </tr>
                            ';
                } else {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:200px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $nka . '</td>
                                    <td colspan="2" class="ctr" style="width:65px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
                }
            }
        }
        //}
        //}
        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();

        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                a.*
                                FROM t_naikkelas a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;
        $q_catatan_homeroom = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_homeroom a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan_homeroom'] = $q_catatan_homeroom;
        $this->load->view('cetak_pts_sd', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', false, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);
        $pdf->setTestTdInOnePage(false);
        $pdf->WriteHTML($str);
        $pdf->Output($d['det_siswa']['nama'] . '-' . $d['wali_kelas']['nmkelas'] . '-' . $tasm . '.pdf');
    }

    public function cetak_ikm($id_siswa, $tasm)
    {
        ob_start();
        $d = array();


        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);
        $ta = substr($tasm, 0, 4);

        $siswa = $this->db->query("SELECT 
                                a.nama, a.nis, a.nisn, c.tingkat, c.id idkelas
                                FROM m_siswa a
                                LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                WHERE a.id = $id_siswa AND b.ta = '" . $d['ta'] . "'")->row_array();

        $d['det_siswa'] = $siswa;
        $tingkat = $siswa['tingkat'];

        $d['wali_kelas'] = $this->db->query("SELECT 
                            a.*, b.nama nmguru, b.nip, 
                            c.tingkat, c.nama nmkelas
                            FROM t_walikelas a 
                            INNER JOIN m_guru b ON a.id_guru = b.id 
                            INNER JOIN m_kelas c ON a.id_kelas = c.id
                            WHERE a.id_kelas = '" . $d['det_siswa']['idkelas'] . "' AND a.tasm = '" . $ta . "'")->row_array();

        // Start NILAI PENGETAHUAN //
        $ambil_np = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "'
                                AND a.nilai != 0
                                ")->result_array();
        $ambil_uts = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                                FROM t_nilai a
                                INNER JOIN m_mapel c ON a.id_mapel_kd = c.id
                                WHERE 
                                a.jenis= 't'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                ")->result_array();

        $ambil_np_submp = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();

        $array1 = array();

        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_mapel']] = array();
        }
        $array_kkm = array();
        foreach ($ambil_np as $a2) {
            $idx = $a2['idmapel'];
            $kkmx = $a2['kkm'];
            $array_kkm[] = $kkmx;
            $lang_mapel = $a2['lang'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }
        foreach ($ambil_uts as $a2) {
            $idx = $a2['idmapel'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
                $array1[$idx]['kd_singkat'] = $a2['kd_singkat'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }


        $kkm = array_unique($array_kkm);

        $d['kkm'] = '';
        foreach ($kkm as $kkmm) {
            $rentang = round(((100 - $kkmm) / 3), 0);
            $d['kkm'] .= '
        <tr>
            <td colspan="2">' . $kkmm . '</td>
            <td colspan="2">0 - ' . ($kkmm - 1) . '</td>
            <td colspan="2">' . $kkmm . ' - ' . ($kkmm + $rentang) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 1) + 1) . ' - ' . ($kkmm + ($rentang * 2)) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 2) + 1) . ' - 100</td>
        </tr>';
            $d['kkm'] = $d['kkm'];
        }
        //echo var_dump($array1);

        $bobot_h = $this->config->item('pnp_h');
        $bobot_t = $this->config->item('pnp_t');
        $bobot_a = $this->config->item('pnp_a');

        $jml_bobot = 100;

        //MULAI HITUNG..
        $nilai_pengetahuan = array();
        foreach ($array1 as $k => $v) {

            $jumlah_h = !empty($array1[$k]['h']) ? sizeof($array1[$k]['h']) : 0;
            $jumlah_n_h = 0;

            $desk = array();

            if (!empty($array1[$k]['h'])) {
                $arrayh = max($array1[$k]['h']);
                $arrayhmin = min($array1[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array1[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_n_h += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_n_h = 0;
                $jumlah_h = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }

            $__tengah = empty($array1[$k]['t']) ? 0 : $array1[$k]['t'];
            $__akhir = empty($array1[$k]['a']) ? 0 : $array1[$k]['a'];
            $kd_singkat = empty($array1[$k]['kd_singkat']) ? 0 : $array1[$k]['kd_singkat'];
            if ($tingkat == 1 || $tingkat == 2) {
                $_np = round(($jumlah_n_h / $jumlah_h), 0);
            } else {
                $_np = round((((2 * ($jumlah_n_h / $jumlah_h)) + $__tengah) / 3), 0);
            }

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['uts'] = $__tengah;
            $nilai_pengetahuan[$k]['kd_singkat'] = $kd_singkat;
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            }
        }
        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN

        // Start NILAI KETRAMPILAN //
        //ambil nilai untuk siswa ybs
        $ambil_nk = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "'
                                OR a.jenis= 't'
                                AND a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'")->result_array();
        $ambil_nicb = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_icb a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        $ambil_pss = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_pss a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();
        $ambil_la = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_la a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        //echo var_dump($ambil_nk);
        //ambil id mapel, kode singkat
        $ambil_nk_submk = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();
        //echo j($ambil_nk_submk);
        $ambil_nc = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai_mid as nilai
                                FROM t_nilai_cat a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'")->result_array();
        $array2 = array();

        foreach ($ambil_nk_submk as $a11) {
            $array2[$a11['id_mapel']] = array();
        }

        //echo j($ambil_nk);

        foreach ($ambil_nk as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            if ($a22['jenis'] == "h") {
                $array2[$idx]['h'][] = $a22['nilai'];
            } else if ($a22['jenis'] == "p") {
                $array2[$idx]['p'] = $a22['nilai'];
            } else if ($a22['jenis'] == "t") {
                $array2[$idx]['t'] = $a22['nilai'];
            } else if ($a22['jenis'] == "a") {
                $array2[$idx]['a'] = $a22['nilai'];
            }
        }
        foreach ($ambil_nicb as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['icb'][] = $a22['nilai'];
        }

        foreach ($ambil_pss as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['pss'][] = $a22['nilai'];
        }
        foreach ($ambil_la as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['la'][] = $a22['nilai'];
        }
        foreach ($ambil_nc as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['c'] = $a22['nilai'];
        }

        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = 100;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
        $icb_aspect = array();
        $pss_aspect = array();
        $la_aspect = array();
        foreach ($array2 as $k => $v) {
            $jumlah_array_nilai = !empty($array2[$k]['h']) ? sizeof($array2[$k]['h']) : 0;
            $jumlah_nilai = 0;

            $desk = array();
            if (!empty($array2[$k]['h'])) {
                $arrayh = max($array2[$k]['h']);
                $arrayhmin = min($array2[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array2[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_nilai += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_array_nilai = 1;
                $jumlah_nilai = 1;
            }

            if (!empty($array2[$k]['icb'])) {
                foreach ($array2[$k]['icb'] as $icb) {
                    $icb_aspect[$k]['aspect'][] = $icb;
                }
            }
            if (!empty($array2[$k]['pss'])) {
                foreach ($array2[$k]['pss'] as $pss) {
                    $pss_aspect[$k]['aspect'][] = $pss;
                }
            }
            if (!empty($array2[$k]['la'])) {
                foreach ($array2[$k]['la'] as $la) {
                    $la_aspect[$k]['aspect'][] = $la;
                }
            }


            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            $nilai_keterampilan[$k]['catatan'] = empty($array2[$k]['c']) ? '-' : $array2[$k]['c'];
            $_nilai_keterampilan = round((((2 * ($jumlah_nilai / $jumlah_array_nilai)) + $__tengah) / 3), 0);


            $nilai_keterampilan[$k]['nilai'] = number_format($_nilai_keterampilan);
            $nilai_keterampilan[$k]['predikat'] = nilai_huruf($kkmx, $_nilai_keterampilan);
            $nilai_keterampilan[$k]['desk'] = implode("; ", $txt_desk);
        }
        //echo j($nilai_keterampilan);
        $d['nilai_keterampilan'] = $nilai_keterampilan;
        //j($nilai_keterampilan);
        //exit;
        // END Nilai PENGETAHUAN

        //===========================================================================
        //       START NIlai Sikap SPIRITUAL
        //===========================================================================

        $q_nilai_sikap_sp = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_sp WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();

        $q_kd_nilai_sikap_sp = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSp'")->result_array();

        $list_kd_sp = array();
        foreach ($q_kd_nilai_sikap_sp as $k) {
            $list_kd_sp[$k['id']] = $k['nama_kd'];
        }

        //jika belum ada nilai sikap sp yang diinputkan
        if (!empty($q_nilai_sikap_sp['selalu'])) {
            $pc_selalu = explode("-", $q_nilai_sikap_sp['selalu']);
            $sll_1 = $pc_selalu[0];
            $sll_2 = $pc_selalu[1];
            $mngkt = $q_nilai_sikap_sp['mulai_meningkat'];

            $selalu1 = $list_kd_sp[$sll_1];
            $selalu2 = $list_kd_sp[$sll_2];
            $mulai_meningkat = $list_kd_sp[$mngkt];


            $nilai_sikap_spiritual = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $selalu1 . ', ' . $selalu2 . ' dan Mulai meningkat pada sikap : ' . $mulai_meningkat;
        } else {
            $selalu1 = '';
            $selalu2 = '';
            $mulai_meningkat = '';

            $nilai_sikap_spiritual = 'Belum diinput';
        }


        $d['nilai_sikap_spiritual'] = $nilai_sikap_spiritual;
        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Sikap SOSIAL
        //===========================================================================

        // $q_nilai_sikap_so = $this->db->query("SELECT selalu, mulai_meningkat FROM t_nilai_sikap_so WHERE tasm = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row_array();
        // //echo $this->db->last_query();
        // //exit;

        // $q_kd_nilai_sikap_so = $this->db->query("SELECT id, nama_kd FROM t_mapel_kd WHERE jenis = 'SSo'")->result_array();

        // $so_text_selalu = "";
        // $so_mulai_meningkat = "";

        // $list_kd_so = array();
        // foreach ($q_kd_nilai_sikap_so as $k) {
        //     $list_kd_so[$k['id']] = $k['nama_kd'];
        // }

        // $so_pc_selalu = explode(",", $q_nilai_sikap_so['selalu']);
        // $so_mulai_meningkat = $q_nilai_sikap_so['mulai_meningkat'];

        // if ($so_pc_selalu[0] == "") {
        //     $nilai_sikap_sosial = 'Belum diinput';
        // } else if ($so_pc_selalu[0] != "" && sizeof($so_pc_selalu) > 0) {
        //     $so_teks_selalu = array();

        //     //echo var_dump($q_nilai_sikap_so);

        //     for ($i = 0; $i < sizeof($so_pc_selalu); $i++) {
        //         $idx = $so_pc_selalu[$i];
        //         $so_teks_selalu[] = $list_kd_so[$idx];
        //     }

        //     $so_text_selalu = implode(", ", $so_teks_selalu);

        //     $so_mulai_meningkat = $list_kd_so[$so_mulai_meningkat];

        //     $nilai_sikap_sosial = 'Ananda ' . $siswa['nama'] . ' Selalu melakukan sikap : ' . $so_text_selalu . ' dan Mulai meningkat pada sikap : ' . $so_mulai_meningkat;
        // } else {
        //     $nilai_sikap_sosial = 'Belum diinput';
        // }


        // $d['nilai_sikap_sosial'] = $nilai_sikap_sosial;

        //END NIlai Sikap SPIRITUAL

        //===========================================================================
        //              START NIlai Ekstrakurikuler
        //===========================================================================
        $q_nilai_ekstra = $this->db->query("SELECT 
                                        b.nama, a.nilai, a.desk
                                        FROM t_nilai_ekstra a
                                        INNER JOIN m_ekstra b ON a.id_ekstra = b.id
                                        WHERE a.id_siswa = $id_siswa AND a.nilai != '0' AND a.tasm = '" . $tasm . "'")->result_array();
        //echo $this->db->last_query();

        $d['nilai_ekstra'] = $q_nilai_ekstra;

        //===========================================================================
        //              START NIlai Prestasi
        //===========================================================================
        $q_prestasi = $this->db->query("SELECT 
                                a.*
                                FROM t_prestasi a 
                                LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
        //echo $this->db->last_query();

        $d['prestasi'] = $q_prestasi;
        //===========================================================================
        //              START NIlai Kl-1
        //===========================================================================
        $q_kl1 = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_kl1 a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
        //echo $this->db->last_query();

        $d['nilai_kl1'] = $q_kl1;
        //===========================================================================
        //              START NIlai Kl-2
        //===========================================================================
        $q_kl2 = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_kl2 a 
                                    LEFT JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->result_array();
        //echo $this->db->last_query();

        $d['nilai_kl2'] = $q_kl2;
        //===========================================================================
        //              START NIlai Absensi
        //===========================================================================
        $q_nilai_absensi = $this->db->query("SELECT 
                                        s, i, a
                                        FROM t_nilai_absensi
                                        WHERE id_siswa = $id_siswa AND tasm = '" . $tasm . "'")->row_array();

        $d['nilai_absensi'] = $q_nilai_absensi;

        $d['nilai_utama'] = '';
        $d['nilai_keterampilan'] = '';
        $d['icb_aspect'] = '';
        $d['pss_aspect'] = '';
        $d['la_aspect'] = '';

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");


        $no = 0;
        $noket = 0;


        //foreach ($q_mapel as $m) {
        //PAI kelompok A
        if ($this->config->item('is_kemenag') == TRUE) {
            $d['nilai_utama'] .= '<tr><td class="ctr">' . $no . '</td><td colspan="9">Pendidikan Agama Islam</td></tr>';
            $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'PAI' ORDER BY id ASC")->result_array();

            foreach ($q_mapel as $i => $m) {
                $kkmx = $m['kkm'];

                $idx = $m['id'];
                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];
                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } else {
                    $predikatx1 = "belum tuntas";
                }

                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". " . $nilai_keterampilan[$idx]['desk'];

                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr"></td>
                                        <td>' . $arr_huruf[$i] . '. ' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td class="ctr">' . $npa . '</td>
                                        <td class="ctr">' . $npp . '</td>
                                        <td class="font_kecil">' . $npd . '</td>
                                        <td class="ctr">' . $nka . '</td>
                                        <td class="ctr">' . $nkp . '</td>
                                        <td class="font_kecil">' . $nkd . '</td>
                                    </tr>';
            }
        }

        $no++;
        $noket++;
        //no ICB
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'ICB' AND tambahan_sub = 'mid' ORDER BY a.id ASC")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($icb_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);
                $d['icb_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['icb_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['icb_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['icb_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }
        //no PSS
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'PSS' AND tambahan_sub = 'mid' ORDER BY a.id ASC")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($pss_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['pss_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['pss_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['pss_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['pss_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }
        //no LA
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'LA' AND tambahan_sub = 'mid' ORDER BY a.id ASC")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($la_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['la_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['la_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['la_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['la_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }

        //no pai kelompok A
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'A' AND tambahan_sub = 'NO'  AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' ORDER BY a.id ASC")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];


            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
            $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }
            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }
            if ($d['det_siswa']['tingkat'] == 0) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:240px;padding:10px;">' . $npa . '</td>
                                     
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }

        //no pai kelompok B

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'B' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "' ORDER BY a.id ASC")->result_array();
        // $json =json_encode($nilai_pengetahuan);
        // print($json);
        // die();
        // die();
        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
            $kdSingkat = $nilai_pengetahuan[$idx]['kd_singkat'] ?? "";
            if ($kdSingkat == 'GP' || $kdSingkat == 'SBK' || $kdSingkat == 'DL' || $kdSingkat == 'PE' || $kdSingkat == 'Music') {
                $uts = "-";
            } else {
                $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            }

            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }

            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];


            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];

            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }

            if ($d['det_siswa']['tingkat'] == 0) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:240px;padding:10px;">' . $npa . '</td>
                                     
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }
        //no pai kelompok C
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'PUS' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "' ORDER BY a.id ASC")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];

                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];

                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }

                if ($d['det_siswa']['tingkat'] == 0) {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:240px;padding:10px;">' . $npa . '</td>
                                    </tr>
                            ';
                } else {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
                    
                }
            }
        }

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
            INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
            INNER JOIN m_guru c ON b.id_guru = c.id
            WHERE kelompok = 'B' AND tambahan_sub = 'MULOK' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "' ORDER BY a.id ASC")->result_array();

        foreach ($q_mapel as $i => $m) {
            $idx = $m['id'];
            $kkmx = $m['kkm'];

            $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
            $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
            $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
            if ($npa >= $kkmx) {
                $predikatx = "sudah tuntas";
            } else {
                $predikatx = "belum tuntas";
            }

            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];
            $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
            }

            if ($d['det_siswa']['tingkat'] == 0) {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:240px;padding:10px;">' . $npa . '</td>
                                     
                                    </tr>
                            ';
            } else {
                $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
            }
        }
        if ($d['det_siswa']['tingkat'] == 0) {
            $d['nilai_utama'] .= '<tr>
            <td class="ctr"></td>
            <td colspan="2" style="width:280px;">Muatan Lokal</td>
            <td colspan="2" class="ctr" style="width:240px;padding:10px;"></td>
            </tr>';
        } else {
            $d['nilai_utama'] .= '<tr>
            <td class="ctr"></td>
            <td colspan="2" style="width:280px;">Muatan Lokal</td>
            <td colspan="2" class="ctr" style="width:120px;padding:10px;"></td>
            <td colspan="2" class="ctr" style="width:120px;padding:10px;"></td>
            </tr>';
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
        INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
        INNER JOIN m_guru c ON b.id_guru = c.id
        WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND a.kd_singkat != 'BTQ' AND b.tasm= '" . $tasm . "' ORDER BY a.id ASC")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {

            foreach ($q_mapel as $i => $m) {
                $idx = $m['id'];
                $kkmx = $m['kkm'];

                $npa = empty($nilai_pengetahuan[$idx]['nilai']) ? "-" : $nilai_pengetahuan[$idx]['nilai'];
                $kdSingkat = $nilai_pengetahuan[$idx]['kd_singkat'] ?? "";
                if ($kdSingkat == 'GP' || $kdSingkat == 'SBK' || $kdSingkat == 'DL' || $kdSingkat == 'PE' || $kdSingkat == 'Music') {
                    $uts = "-";
                } else {
                    $uts = empty($nilai_pengetahuan[$idx]['uts']) ? "-" : $nilai_pengetahuan[$idx]['uts'];
                }
                $npp = empty($nilai_pengetahuan[$idx]['predikat']) ? "-" : $nilai_pengetahuan[$idx]['predikat'];
                $catatan = empty($nilai_keterampilan[$idx]['catatan']) ? "-" : $nilai_keterampilan[$idx]['catatan'];
                if ($npa >= $kkmx) {
                    $predikatx = "sudah tuntas";
                } else {
                    $predikatx = "belum tuntas";
                }

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx . " dengan predikat " . nilai_pre($kkmx, $npa, $lang_mapel) . ". " . $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Kamu telah " . $nilai_keterampilan[$idx]['desk'];
                }

                if ($d['det_siswa']['tingkat'] == 0) {
                    
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:240px;padding:10px;">' . $npa . '</td>
                                     
                                    </tr>
                            ';
                } else {
                    $d['nilai_utama'] .= '
                                <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td colspan="2" style="width:295px;">' . $m['nama'] . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $npa . '</td>
                                    <td colspan="2" class="ctr" style="width:80px;padding:10px;">' . $uts . '</td>
                                    </tr>
                            ';
                }
            }
        }
        //}
        //}
        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();

        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                a.*
                                FROM t_naikkelas a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;
        $q_catatan_homeroom = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_homeroom a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan_homeroom'] = $q_catatan_homeroom;
        $this->load->view('cetak_pts_ikm', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', false, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);
        $pdf->encoding = 'UTF-8';
        $pdf->setTestTdInOnePage(false);
        $pdf->WriteHTML($html);
        $namaSiswa = $d['det_siswa']['nama'] ?? "--";
        $waliKelas = $d['wali_kelas']['nmkelas'] ?? "";
        $pdf->Output($namaSiswa . '-' . $waliKelas . '-' . $tasm . '.pdf');
    }

    public function cetak_preschool($id_siswa, $tasm)
    {
        ob_start();
        $d = array();


        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);

        $siswa = $this->db->query("SELECT 
                                a.nama, a.nis, a.nisn, c.tingkat, c.id idkelas
                                FROM m_siswa a
                                LEFT JOIN t_kelas_siswa b ON a.id = b.id_siswa
                                LEFT JOIN m_kelas c ON b.id_kelas = c.id
                                WHERE a.id = $id_siswa AND b.ta = '" . $d['ta'] . "'")->row_array();

        $d['det_siswa'] = $siswa;

        $d['wali_kelas'] = $this->db->query("SELECT 
                            a.*, b.nama nmguru, b.nip, 
                            c.tingkat, c.nama nmkelas
                            FROM t_walikelas a 
                            INNER JOIN m_guru b ON a.id_guru = b.id 
                            INNER JOIN m_kelas c ON a.id_kelas = c.id
                            WHERE a.id_kelas = '" . $d['det_siswa']['idkelas'] . "' AND a.tasm = '" . $this->d['ta'] . "'")->row_array();

        // Start NILAI PENGETAHUAN //
        $ambil_np = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "'")->result_array();


        $ambil_np_submp = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();

        $array1 = array();

        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_mapel']] = array();
        }
        $array_kkm = array();
        foreach ($ambil_np as $a2) {
            $idx = $a2['idmapel'];
            $kkmx = $a2['kkm'];
            $array_kkm[] = $kkmx;
            $lang_mapel = $a2['lang'];

            //$pc_nilai = explode("//", $a2['nilai']);

            if ($a2['jenis'] == "h") {
                $array1[$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }


        $kkm = array_unique($array_kkm);

        $d['kkm'] = '';
        foreach ($kkm as $kkmm) {
            $rentang = round(((100 - $kkmm) / 3), 0);
            $d['kkm'] .= '
        <tr>
            <td colspan="2">' . $kkmm . '</td>
            <td colspan="2">0 - ' . ($kkmm - 1) . '</td>
            <td colspan="2">' . $kkmm . ' - ' . ($kkmm + $rentang) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 1) + 1) . ' - ' . ($kkmm + ($rentang * 2)) . '</td>
            <td colspan="2">' . ($kkmm + ($rentang * 2) + 1) . ' - 100</td>
        </tr>';
            $d['kkm'] = $d['kkm'];
        }
        //echo var_dump($array1);

        $bobot_h = $this->config->item('pnp_h');
        $bobot_t = $this->config->item('pnp_t');
        $bobot_a = $this->config->item('pnp_a');

        $jml_bobot = 100;

        //MULAI HITUNG..
        $nilai_pengetahuan = array();
        foreach ($array1 as $k => $v) {

            $jumlah_h = !empty($array1[$k]['h']) ? sizeof($array1[$k]['h']) : 0;
            $jumlah_n_h = 0;

            $desk = array();

            if (!empty($array1[$k]['h'])) {
                $arrayh = max($array1[$k]['h']);
                $arrayhmin = min($array1[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array1[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_n_h += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_n_h = 1;
                $jumlah_h = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }

            $__tengah = empty($array1[$k]['t']) ? 0 : $array1[$k]['t'];
            $__akhir = empty($array1[$k]['a']) ? 0 : $array1[$k]['a'];

            $_np = round((((100 / $jml_bobot) * ($jumlah_n_h / $jumlah_h))), 0);

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            }
        }
        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN

        // Start NILAI KETRAMPILAN //
        //ambil nilai untuk siswa ybs
        $ambil_nk = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa AND d.mid_final=1
                                AND a.tasm = '" . $tasm . "'")->result_array();
        $ambil_nicb = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_icb a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        $ambil_pss = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_pss a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();
        $ambil_la = $this->db->query("SELECT 
    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
    FROM t_nilai_la a
    INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
    INNER JOIN m_mapel c ON b.id_mapel = c.id
    INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
    WHERE a.id_siswa = $id_siswa
    AND a.tasm = '" . $tasm . "' AND a.jenis = 'h'")->result_array();

        //echo var_dump($ambil_nk);
        //ambil id mapel, kode singkat
        $ambil_nk_submk = $this->db->query("SELECT 
                                b.id_mapel, c.kd_singkat
                                FROM t_nilai_ket a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                GROUP BY b.id_mapel")->result_array();
        //echo j($ambil_nk_submk);
        $ambil_nc = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai_mid as nilai
                                FROM t_nilai_cat a
                                INNER JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                                INNER JOIN m_mapel c ON b.id_mapel = c.id
                                INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'")->result_array();
        $array2 = array();

        foreach ($ambil_nk_submk as $a11) {
            $array2[$a11['id_mapel']] = array();
        }

        //echo j($ambil_nk);

        foreach ($ambil_nk as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            if ($a22['jenis'] == "h") {
                $array2[$idx]['h'][] = $a22['nilai'];
            } else if ($a22['jenis'] == "p") {
                $array2[$idx]['p'] = $a22['nilai'];
            } else if ($a22['jenis'] == "t") {
                $array2[$idx]['t'] = $a22['nilai'];
            } else if ($a22['jenis'] == "a") {
                $array2[$idx]['a'] = $a22['nilai'];
            }
        }
        foreach ($ambil_nicb as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['icb'][] = $a22['nilai'];
        }

        foreach ($ambil_pss as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['pss'][] = $a22['nilai'];
        }
        foreach ($ambil_la as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['la'][] = $a22['nilai'];
        }
        foreach ($ambil_nc as $a22) {
            $idx = $a22['idmapel'];
            //$pc_nilai = explode("//", $a2['nilai']);
            $array2[$idx]['c'] = $a22['nilai'];
        }

        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = 100;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
        $icb_aspect = array();
        $pss_aspect = array();
        $la_aspect = array();
        foreach ($array2 as $k => $v) {
            $jumlah_array_nilai = !empty($array2[$k]['h']) ? sizeof($array2[$k]['h']) : 0;
            $jumlah_nilai = 0;

            $desk = array();
            if (!empty($array2[$k]['h'])) {
                $arrayh = max($array2[$k]['h']);
                $arrayhmin = min($array2[$k]['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($array2[$k]['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_nilai += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_array_nilai = 1;
                $jumlah_nilai = 1;
            }

            if (!empty($array2[$k]['icb'])) {
                foreach ($array2[$k]['icb'] as $icb) {
                    $icb_aspect[$k]['aspect'][] = $icb;
                }
            }
            if (!empty($array2[$k]['pss'])) {
                foreach ($array2[$k]['pss'] as $pss) {
                    $pss_aspect[$k]['aspect'][] = $pss;
                }
            }
            if (!empty($array2[$k]['la'])) {
                foreach ($array2[$k]['la'] as $la) {
                    $la_aspect[$k]['aspect'][] = $la;
                }
            }


            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            $nilai_keterampilan[$k]['catatan'] = empty($array2[$k]['c']) ? '-' : $array2[$k]['c'];
            $_nilai_keterampilan = round((((100 / $jml_bobot) * ($jumlah_nilai / $jumlah_array_nilai))), 0);

            $nilai_keterampilan[$k]['nilai'] = number_format($_nilai_keterampilan);
            $nilai_keterampilan[$k]['predikat'] = nilai_huruf($kkmx, $_nilai_keterampilan);
            $nilai_keterampilan[$k]['desk'] = implode("; ", $txt_desk);
        }
        //echo j($nilai_keterampilan);
        $d['nilai_keterampilan'] = $nilai_keterampilan;
        //j($nilai_keterampilan);
        //exit;
        // END Nilai PENGETAHUAN

        $d['nilai_utama'] = '';
        $d['nilai_keterampilan'] = '';
        $d['icb_aspect'] = '';
        $d['pss_aspect'] = '';
        $d['la_aspect'] = '';
        $d['ta_catatan_nna'] = '';
        $d['ta_catatan_sek'] = '';
        $d['ta_catatan_bi'] = '';
        $d['ta_catatan_kog'] = '';
        $d['ta_catatan_fimo'] = '';

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");


        $no = 0;
        $noket = 0;



        $no++;
        $noket++;
        //no ICB
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'ICB' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($icb_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);
                $d['icb_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['icb_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['icb_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['icb_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }
        //no PSS
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'PSS' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($pss_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['pss_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['pss_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['pss_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['pss_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }
        //no LA
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
         WHERE kelompok = 'LA' AND tambahan_sub = 'mid'")->result_array();
        foreach ($q_mapel as $i => $m) {

            $kkmx = $m['kkm'];

            $idx = $m['id'];
            foreach ($la_aspect[$idx]['aspect'] as $aspect) {
                $aspect_array = explode("//", $aspect);

                $d['la_aspect'] .= '<tr>
                <td>' . $aspect_array[1] . '</td>';
                if ($aspect_array[0] == 3) {
                    $d['la_aspect'] .= '
                        <td style="text-align:center;">V</td>
                        <td></td>
                        <td></td>
                        </tr>';
                } elseif ($aspect_array[0] == 2) {
                    $d['la_aspect'] .= '
                        <td></td>
                        <td style="text-align:center;">V</td>
                        <td></td>
                        </tr>';
                } else {
                    $d['la_aspect'] .= '
                        <td></td>
                        <td></td>
                        <td style="text-align:center;">V</td>
                        </tr>';
                }
            }
        }
        $q_catatan_nna = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_nna a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['ta_catatan_nna'] .= '
        <td style="font-family:freeserif; width:425px;padding: 10px 5px;">' . $q_catatan_nna['catatan_mid'] . '</td>
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_nna['catatan_final'] . '</td>';

        $q_catatan_sek = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_sek a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['ta_catatan_sek'] .= '
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_sek['catatan_mid'] . '</td>
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_sek['catatan_final'] . '</td>';

        $q_catatan_bi = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_bi a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['ta_catatan_bi'] .= '
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_bi['catatan_mid'] . '</td>
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_bi['catatan_final'] . '</td>';

        $q_catatan_kog = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_kog a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['ta_catatan_kog'] .= '
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_kog['catatan_mid'] . '</td>
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_kog['catatan_final'] . '</td>';

        $q_catatan_fimo = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_fimo a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['ta_catatan_fimo'] .= '
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_fimo['catatan_mid'] . '</td>
        <td style="font-family:freeserif;width:425px;padding: 10px 5px;">' . $q_catatan_fimo['catatan_final'] . '</td>';

        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();

        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                a.*
                                FROM t_naikkelas a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;
        $q_catatan_homeroom = $this->db->query("SELECT 
                                a.*
                                FROM t_catatan_homeroom a 
                                WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan_homeroom'] = $q_catatan_homeroom;

        $this->load->view('cetak_pts_preschool', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);
        $pdf->encoding = 'UTF-8';

        $pdf->WriteHTML($html);
        $pdf->Output('Data Siswa.pdf');
    }


    public function index()
    {

        $wali = $this->session->userdata($this->sespre . "walikelas");

        $this->d['siswa_kelas'] = $this->db->query("SELECT 
                                                a.id_siswa, b.nama, c.tingkat
                                                FROM t_kelas_siswa a
                                                INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                INNER JOIN m_kelas c ON a.id_kelas = c.id
                                                WHERE a.id_kelas = '" . $wali['id_walikelas'] . "' AND a.ta = '" . $this->d['ta'] . "'
                                                ORDER BY b.nama ASC")->result_array();
        $tahun = $this->db->query("SELECT * FROM tahun WHERE tahun = '" . $this->d['tasm'] . "'")->row();
        $jenis_rapor = getJenisRaport($tahun->id, $this->d['siswa_kelas'][0]['tingkat']);
        $this->d['jenis_rapor'] = ($jenis_rapor->nama ?? "") == "K13" ? 2 : 1; // 1. kurmer, 2. k13

        foreach ($this->d['siswa_kelas'] as &$s) {
            $rapor =  $this->db->query("SELECT * FROM t_rapor
                                WHERE id_siswa = '" . $s['id_siswa'] . "'
                                AND tingkat = '" . $s['tingkat'] . "'
                                AND tahun = '" . $this->d['ta'] . "'
                                AND semester = '" . $this->d['semester'] . "'
                                AND jenis_rapor = 1
                                ")->row();
            $s['terkunci'] = isset($rapor) ? TRUE : FALSE;
        }

        $this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

    function mapping_nilai($table, $id_siswa, $tasm)
    {
        $array1 = [];
        $ambil_np_submp = get_nilai_sub($table, $id_siswa, $tasm);
        $ambil_np = get_nilai_utama($table, $id_siswa, $tasm, true);
        $ambil_uts = get_nilai_uts($table, $id_siswa, $tasm, true);

        foreach ($ambil_np_submp as $a1) {
            $mapel = $this->db->get_where('m_mapel', ['id' => $a1['id_mapel']])->row();
            $data_nilai = [
                'id_mapel' => $a1['id_mapel'],
                'mapel' => $mapel->nama, //nama mapel
                'mapel_diknas' => $mapel->nama_diknas ?? "", //nama mapel
                'kelompok' => $mapel->kelompok, //nama mapel
                'kd_singkat' => $a1['kd_singkat'],
            ];
            foreach ($ambil_np as $a2) {
                $idx = $a2['idmapel'];
                if ($idx == $a1['id_mapel']) {
                    if ($a2['jenis'] == "a") {
                        $data_nilai['a'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "t") {
                        $data_nilai['t'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "c") {
                        $data_nilai['c'] = $a2['catatan'];
                    } else if ($a2['jenis'] == "h") {
                        $data_nilai['h'][] = $a2['nilai'];
                    }
                }
            }
            foreach ($ambil_uts as $a2) {
                $idx = $a2['idmapel'];
                if ($idx == $a1['id_mapel']) {
                    if ($a2['jenis'] == "a") {
                        $data_nilai['a'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "t") {
                        $data_nilai['t'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "c") {
                        $data_nilai['c'] = $a2['catatan'];
                    } else if ($a2['jenis'] == "h") {
                        $data_nilai['h'][] = $a2['nilai'];
                    }
                }
            }
            $array1[] = $data_nilai;
        }
        return $array1;
    }

    function mapping_nilai_keterampilan($table, $id_siswa, $tasm)
    {
        $array1 = [];
        $ambil_np_submp = get_nilai_sub($table, $id_siswa, $tasm);
        $ambil_np = get_nilai_utama($table, $id_siswa, $tasm, true);
        $ambil_uts = get_nilai_uts($table, $id_siswa, $tasm, true);

        foreach ($ambil_np_submp as $a1) {
            $mapel = $this->db->get_where('m_mapel', ['id' => $a1['id_mapel']])->row();
            $data_nilai = [
                'id_mapel' => $a1['id_mapel'],
                'mapel' => $mapel->nama, //nama mapel
                'mapel_diknas' => $mapel->nama_diknas ?? "", //nama mapel
                'kd_singkat' => $a1['kd_singkat'],
            ];
            foreach ($ambil_np as $a2) {
                $idx = $a2['idmapel'];
                if ($idx == $a1['id_mapel']) {
                    if ($a2['jenis'] == "a") {
                        $data_nilai['a'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "t") {
                        $data_nilai['t'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "c") {
                        $data_nilai['c'] = $a2['catatan'];
                    } else if ($a2['jenis'] == "p") {
                        $data_nilai['p'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "h") {
                        $data_nilai['h'][] = $a2['nilai'];
                    }
                }
            }
            foreach ($ambil_uts as $a2) {
                $idx = $a2['idmapel'];
                if ($idx == $a1['id_mapel']) {
                    if ($a2['jenis'] == "a") {
                        $data_nilai['a'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "t") {
                        $data_nilai['t'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "c") {
                        $data_nilai['c'] = $a2['catatan'];
                    } else if ($a2['jenis'] == "p") {
                        $data_nilai['p'] = $a2['nilai'];
                    } else if ($a2['jenis'] == "h") {
                        $data_nilai['h'][] = $a2['nilai'];
                    }
                }
            }
            $array1[] = $data_nilai;
        }
        return $array1;
    }


    function hitung_nilai_pengetahuan($table, $id_siswa, $tasm, $siswa)
    {
        $ambil_np = get_nilai_utama($table, $id_siswa, $tasm);
        $array1 = $this->mapping_nilai($table, $id_siswa, $tasm);
        $nilai_pengetahuan = [];
        foreach ($ambil_np as $a2) {
            $kkmx = $a2['kkm'];
            $lang_mapel = $a2['lang'];
        }
        foreach ($array1 as $k) {

            $jumlah_h = !empty($k['h']) ? sizeof($k['h']) : 0;
            $jumlah_n_h = 0;

            $desk = array();

            if (!empty($k['h'])) {
                $arrayh = max($k['h']);
                $arrayhmin = min($k['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {

                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($k['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_n_h += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_n_h = 0;
                $jumlah_h = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }

            $__tengah = empty($k['t']) ? 0 : $k['t'];

            if ($siswa->tingkat == 1 || $siswa->tingkat == 2) {
                $_np = round(($jumlah_n_h / $jumlah_h), 0);
            } else {
                $_np = round((((2 * ($jumlah_n_h / $jumlah_h)) + $__tengah) / 3), 0);
            }
            $guru_mapel = get_guru_mapel($k['id_mapel'], $siswa->id_kelas, $tasm);
            $pengetahuan = [
                'id_mapel' => $k['id_mapel'],
                'nama_guru' => $guru_mapel->namaguru,
                'mapel' => $k['mapel'],
                'mapel_diknas' => $k['mapel_diknas'],
                'kelompok' => $k['kelompok'],
                'kd_singkat' => $k['kd_singkat'],
                'uts' => $__tengah,
                'nilai' => number_format($_np),
                'predikat' => nilai_huruf($kkmx, $_np),
                'kkm' => $kkmx,
                "tipe" => 1, // 1. Pengetahuan 2. Keterampilan

            ];
            if ($lang_mapel == 'eng') {
                $pengetahuan['desk'] =  empty($k['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $k['c'];
            } else {
                $pengetahuan['desk'] =  empty($k['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $k['c'];
            }
            $nilai_pengetahuan[] = $pengetahuan;
        }

        // echo json_encode($nilai_pengetahuan);
        return $nilai_pengetahuan;
    }

    function hitung_nilai_keterampilan($table, $id_siswa, $tasm, $siswa)
    {
        $ambil_np = get_nilai_utama('t_nilai', $id_siswa, $tasm);
        foreach ($ambil_np as $a2) {
            $kkmx = $a2['kkm'];
            $lang_mapel = $a2['lang'];
        }
        $array2 = $this->mapping_nilai_keterampilan($table, $id_siswa, $tasm);
        $nilai_keterampilan = array();
        foreach ($array2 as $k) {
            $jumlah_array_nilai = !empty($k['h']) ? sizeof($k['h']) : 0;
            $jumlah_nilai = 0;

            $desk = array();
            if (!empty($k['h'])) {
                $arrayh = max($k['h']);
                $arrayhmin = min($k['h']);
                $pc_nilai_hmin = explode("//", $arrayhmin);
                $pc_nilai_h = explode("//", $arrayh);
                $_desk = nilai_pre($kkmx, $pc_nilai_h[0], $lang_mapel);
                $do = do_lang($kkmx, $pc_nilai_h[0]);
                if ($lang_mapel == "eng") {
                    $_desk1 = 'However, you ' . $do . ' continue to develop your comprehension on how to';
                    $desk[$_desk][] = "on how to " . $pc_nilai_h[1];
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                } else {
                    $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                    $desk[$pc_nilai_h[1]][] = "dengan " . $_desk;
                    $desk[$_desk1][] = $pc_nilai_hmin[1];
                }
                foreach ($k['h'] as $j) {
                    $pc_nilai_h = explode("//", $j);
                    $jumlah_nilai += $pc_nilai_h[0];
                }
            } else {
                //biar ndak division by zero
                $jumlah_array_nilai = 1;
                $jumlah_nilai = 1;
            }
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($k['t']) ? 0 : $k['t'];
            if ($siswa->tingkat == 1 || $siswa->tingkat == 2) {
                $_nilai_keterampilan = round(($jumlah_nilai / $jumlah_array_nilai), 0);
            } else {
                $_nilai_keterampilan = round((((2 * ($jumlah_nilai / $jumlah_array_nilai)) + $__tengah) / 3), 0);
            }


            $guru_mapel = get_guru_mapel($k['id_mapel'], $siswa->id_kelas, $tasm);
            $keterampilan = [
                'id_mapel' => $k['id_mapel'],
                'nama_guru' => $guru_mapel->namaguru,
                'mapel' => $k['mapel'],
                'mapel_diknas' => $k['mapel_diknas'],
                'kd_singkat' => $k['kd_singkat'],
                'nilai' => number_format($_nilai_keterampilan),
                'kkm' => $kkmx,
                'predikat' => nilai_huruf($kkmx, $_nilai_keterampilan),
                "tipe" => 2, // 1. pengetauan 2. keterampilan
            ];
            if ($lang_mapel == 'eng') {
                $keterampilan['desk'] = empty($k['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $k['c'];
            } else {
                $keterampilan['desk'] = empty($k['c']) ? 'Kamu telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $k['c'];
            }
            $nilai_keterampilan[] = $keterampilan;
        }

        // echo json_encode($nilai_keterampilan);
        return $nilai_keterampilan;
    }
    
    function kunci_rapor($id_siswa, $tasm)
    {
        $semester = substr($tasm, 4, 1);
        $ta = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);
        $tahun = substr($tasm, 0, 4);

        $rapor = $this->db->get_where(
            't_rapor',
            [
                'id_siswa' => $id_siswa,
                'tahun' => $tahun,
                'semester' => $semester,
                'jenis_rapor' => 1, //mid
            ]
        )->row();

        if ($rapor) {
            return false; //kunci
        }

        $siswa = siswa($id_siswa, $tahun); //get siswa
        $wali_kelas = wali_kelas($siswa->id_kelas, $tahun); // get walikelas
        $ta_active = ta_active($tasm); // get active kepsek
        $kl1 = get_capaianKl1($id_siswa, $tasm);
        $kl2 = get_capaianKl2($id_siswa, $tasm);
        $catatan_ht = catatan_homeroom($id_siswa, $tasm);
        $catatan_naik_kelas = catatan_naik_kelas($id_siswa, $tasm);

        $d['det_raport'] = $this->db->query("SELECT * FROM tahun WHERE tahun = '$tasm'")->row();
        $jenis_rapor = getJenisRaport($d['det_raport']->id, $siswa->tingkat);
        $r = ($jenis_rapor->nama ?? "") == "K13" ? 2 : 1; // 1. kurmer, 2. k13

        $d = [
            "nama" => $siswa->nama,
            "nis" => $siswa->nis,
            "nisn" => $siswa->nisn,
            "kelas" => $wali_kelas->nmkelas,
            "tingkat" => $siswa->tingkat,
            "semester" => $semester,
            "tasm" => $ta,
            "tahun" => $tahun,
            "wali_kelas" => $wali_kelas->nmguru,
            "kepala_sekolah" => $ta_active->nama_kepsek,
            "tgl_rapor" => $ta_active->tgl_raport,
            "jenis_rapor" => 1, // 1.mid, 2.final
            "tipe_rapor" => $r,
            "catatan_ht" => $catatan_ht->catatan_mid ?? "",
            "catatan_naik_kelas" => $catatan_naik_kelas->catatan_wali ?? "",
            "capaian_kl1" => $kl1->capaian_mid??"",
            "catatan_kl1" => $kl1->catatan_mid??"",
            "capaian_kl2" => $kl2->capaian_mid??"",
            "catatan_kl2" => $kl2->catatan_mid??"",
        ];

        $pengetahuan = $this->hitung_nilai_pengetahuan('t_nilai', $id_siswa, $tasm, $siswa);
        $keterampilan = $this->hitung_nilai_keterampilan('t_nilai_ket', $id_siswa, $tasm, $siswa);
        $catatan_mapel =  get_catatan_mapel($id_siswa, $tasm);

        $obj_nilai = [];
        foreach ($pengetahuan as $data) {
            $nilai = [
                "id_mapel" => $data['id_mapel'],
                "mapel" => $data['mapel'],
                "mapel_diknas" => $data['mapel_diknas'],
                "kd_singkat" => $data['kd_singkat'],
                "kelompok" => $data['kelompok'],
                "kkm" => $data['kkm'],
                "nama_guru" => $data['nama_guru'],
                "nilai_uts" => $data['uts'],
                "nilai_pengetahuan" => $data['nilai'],
                "predikat_pengetahuan" => $data['predikat'],
                "desk_pengetahuan" => $data['desk'],
            ];
            $filterKeterampilan = array_filter($keterampilan, function ($ket) use ($data) {
                return $ket['id_mapel'] == $data['id_mapel'];
            });
            $valueKeterampilan = array_values($filterKeterampilan);

            if (!empty($valueKeterampilan)) {
                $nilai['nilai_keterampilan'] = $valueKeterampilan[0]['nilai'];
                $nilai['predikat_keterampilan'] = $valueKeterampilan[0]['predikat'];
                $nilai['desk_keterampilan'] = $valueKeterampilan[0]['desk'];
            }
            $filterNilaiCatatan = array_filter($catatan_mapel, function ($cat) use ($data) {
                return $cat['idmapel'] == $data['id_mapel'];
            });
            $valueCatatan = array_values($filterNilaiCatatan);
            if (!empty($valueCatatan)) {
                $nilai['nilai_catatan'] = $valueCatatan[0]['nilai_mid'];
            }
            $obj_nilai[] = $nilai;
        }

        $e = $this->db->query("INSERT INTO t_rapor (id_siswa, nama,nis,nisn,kelas,tingkat,semester,tasm,tahun,wali_kelas,kepala_sekolah,
                            tgl_rapor,jenis_rapor,tipe_rapor,catatan_ht,capaian_kl1,catatan_kl1,capaian_kl2,catatan_kl2,catatan_naik_kelas) VALUES 
                            ('" . $id_siswa . "','" . $d["nama"] . "','" . $d["nis"] . "','" . $d["nisn"] . "','" . $d["kelas"] . "',
                            " . $d["tingkat"] . "," . $d["semester"] . ",'" . $d["tasm"] . "'," . $d["tahun"] . ",'" . $d["wali_kelas"] . "',
                            '" . $d["kepala_sekolah"] . "','" . $d["tgl_rapor"] . "','" . $d["jenis_rapor"] . "','" . $d["tipe_rapor"] . "',
                            '" . addslashes($d["catatan_ht"])  . "','" . addslashes($d["capaian_kl1"]) . "','" . addslashes($d["catatan_kl1"]) . "',
                            '" . addslashes($d["capaian_kl2"]) . "','" . addslashes($d["catatan_kl2"]) . "','" . addslashes($d["catatan_naik_kelas"]) . "')");

        if ($e) {
            $id = $this->db->insert_id(); // get the last insert id

            //insert nilai Pengetahuan/keterampilan/nilai catatan
            foreach ($obj_nilai as $data) {
                $_nilai_keterampilan = isset($data['nilai_keterampilan']) ? $data['nilai_keterampilan'] : 0;
                $_predikat_keterampilan = isset($data['predikat_keterampilan']) ? $data['predikat_keterampilan'] : "";
                $_desk_keterampilan = isset($data['desk_keterampilan']) ? $data['desk_keterampilan'] : "";

                $this->db->query("INSERT INTO t_rapor_detail (id_rapor,id_mapel,mapel,mapel_diknas,kd_singkat,kkm,
                    nama_guru,nilai_uts,nilai_pengetahuan,nilai_keterampilan,nilai_catatan,predikat_pengetahuan,
                    predikat_keterampilan,desk_pengetahuan,desk_keterampilan,kelompok) VALUES 
                    ($id,'" . $data['id_mapel'] . "','" . $data['mapel'] . "','" . $data['mapel_diknas'] . "',
                    '" . $data['kd_singkat'] . "','" . $data['kkm'] . "','" . $data['nama_guru'] . "','" . $data['nilai_uts'] . "',
                    '" . $data['nilai_pengetahuan'] . "','" . $_nilai_keterampilan . "','" . addslashes($data['nilai_catatan'] ?? "")  . "',
                    '" . $data['predikat_pengetahuan'] . "','" . $_predikat_keterampilan . "','" . addslashes($data['desk_pengetahuan']) . "'
                    ,'" . addslashes($_desk_keterampilan) . "','" . $data['kelompok'] . "')");
            }

            //inser icb,pss,la
            insert_icb_pss_la($id_siswa, $tasm, "t_nilai_icb", $id, "mid");
            insert_icb_pss_la($id_siswa, $tasm, "t_nilai_pss", $id, "mid");
            insert_icb_pss_la($id_siswa, $tasm, "t_nilai_la", $id, "mid");
            
            $d['details'] = $obj_nilai;
            $d['characters'] = $this->db->query("SELECT * FROM t_raport_character WHERE id_rapor = $id")->result_array();
            
            $raporData = ["rapor" => $d];
            $safeName  = $this->_safe($d['nama'] ?? 'SISWA');
            $safeKelas = $this->_safe($d['kelas'] ?? 'KELAS');
            $pdfPath   = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";
            
            ob_start();
            
            if ($r == 1) {
                $this->load->view('fix_cetak_pts_ikm', $raporData['rapor']);
            } else {
                $this->load->view('fix_cetak_pts_sd', $raporData['rapor']);
            }
            
            $html = ob_get_clean();
            
            require_once $this->HTML2PDF_AUTOLOAD;
    
            $pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
            $pdf->encoding = 'UTF-8';
            $pdf->setTestTdInOnePage(false);
            $pdf->writeHTML($html);
            
            if (file_exists($pdfPath)) {
@unlink($pdfPath);
            }
            // Simpan PDF baru
            $pdf->output($pdfPath, 'F');
        }
        // echo json_encode($obj_nilai);
        redirect('cetak_raport_pts');
    }

    function mhis($id_siswa, $tasm)
    {
        $tahun = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);
        $rapor = $this->db->query("SELECT * FROM t_rapor WHERE id_siswa = $id_siswa and tahun = $tahun and semester =$semester and jenis_rapor=1")->row();

        if (isset($rapor)) {
            $safeName  = $this->_safe($rapor->nama);
            $safeKelas = $this->_safe($rapor->kelas);
            $pdfFile = $this->CACHE_BASE ."{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";
            
            if (!file_exists($pdfFile)) {
                require_once $this->HTML2PDF_AUTOLOAD;
                $rapor_detail       = $this->db->query("SELECT * FROM t_rapor_detail WHERE id_rapor = $rapor->id")->result_array();
                $rapor_characters   = $this->db->query("SELECT * FROM t_raport_character WHERE id_rapor = $rapor->id")->result_array();
                $rapor->details     = $rapor_detail;
                $rapor->characters  = $rapor_characters;
                
                ob_start();
                if ($rapor->tipe_rapor == 1) {
                    $this->load->view('fix_cetak_pts_ikm', $rapor);
                } else {
                    $this->load->view('fix_cetak_pts_sd', $rapor);
                }
                $html = ob_get_clean();
                
                $pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
                $pdf->encoding = 'UTF-8';
                $pdf->setTestTdInOnePage(false);
                $pdf->writeHTML($html);
                if (file_exists($pdfFile)) {
@unlink($pdfFile);
                }
                $pdf->output($pdfFile, 'F');
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($pdfFile) . '"');
            header('Content-Length: ' . filesize($pdfFile));
            // Outputkan file PDF ke browser
            readfile($pdfFile);
            exit; 
        }else{
            show_error("file tidak tersedia");
            return;
        }
    }
//     public function mhis($id_siswa = null, $tasm = null)
// {
//     // Basic validation
//     if (!$id_siswa || !$tasm || !preg_match('/^\d{5}$/', (string)$tasm)) {
//         return $this->_end('Bad request', 400);
//     }

//     $tahun    = (int) substr($tasm, 0, 4);
//     $semester = (int) substr($tasm, -1);

//     // Get minimal info for filename (same source table/filters)
//     $rapor = $this->db->query(
//         "SELECT nama, kelas
//           FROM t_rapor
//           WHERE id_siswa = ? AND tahun = ? AND semester = ?
//           LIMIT 1",
//         [$id_siswa, $tahun, $semester]
//     )->row();

//     if (!$rapor) {
//         return $this->_end('No rapor found for this student/term.', 404);
//     }

//     // Build cached file path (same naming convention as pregen)
//     $safeName  = $this->_safe($rapor->nama ?? 'SISWA');
//     $safeKelas = $this->_safe($rapor->kelas ?? 'KELAS');

//     // IMPORTANT: same cache base as your bind mount target
//     $pdfPath = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";

//     if (is_file($pdfPath)) {
//         // Stream the cached PDF with minimal memory use
//         header('Content-Type: application/pdf');
//         header('Content-Length: ' . filesize($pdfPath));
//         header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
//         readfile($pdfPath);
//         return;
//     }

//     // If not found yet, tell the user to try again (keeps CPU low; no on-demand render)
//     return $this->_end('Rapor sedang disiapkan. Silakan coba lagi beberapa saat.', 503);
// }

    public function pregen_http($tasm = null, $token = null)
    {
        // 1) Security first
        if ($token !== $this->SECRET) {
            return $this->_end('Unauthorized', 401);
        }

        // 2) Validate input
        if (!$tasm || !preg_match('/^\d{5}$/', (string)$tasm)) {
            return $this->_end('Invalid tasm', 400);
        }
        $limit  = (int)($this->input->get('limit')  ?? 200);
        $offset = (int)($this->input->get('offset') ?? 0);
        $force  = (int)($this->input->get('force')  ?? 0);

        if ($limit <= 0 || $limit > 1000) $limit = 200;
        if ($offset < 0) $offset = 0;

        // 3) Per-chunk lock (avoid duplicate runs for same chunk)
        $lockFile = $this->_lockPath($tasm, $offset);
        if (file_exists($lockFile) && (time() - @filemtime($lockFile)) < 1800) { // 30 min TTL
            return $this->_end("Locked: tasm=$tasm offset=$offset", 429);
        }
@file_put_contents($lockFile, date('c'));

        // 4) Make sure cache dir exists (after auth ok)
        if (!is_dir($this->CACHE_BASE)) {
@mkdir($this->CACHE_BASE, 0775, true);
        }

        $tahun    = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);

        // 5) Fetch a chunk of students
        $rapors = $this->db->query("
            SELECT r.id, r.id_siswa, r.nama, r.kelas, r.tipe_rapor
            FROM t_rapor r
            WHERE r.tahun = ? AND r.semester = ?
            ORDER BY r.id
            LIMIT ? OFFSET ?
        ", [$tahun, $semester, $limit, $offset])->result();

        // 6) Generate
        $generated = 0; $skipped = 0; $failed = 0;
        foreach ($rapors as $r) {
            $ok = $this->_generate_single_pdf((int)$r->id_siswa, $tasm, (bool)$force);
            if ($ok === true)        $generated++;
            elseif ($ok === 'skip')  $skipped++;
            else                     $failed++;
            gc_collect_cycles();
        }

        // 7) Unlock
@unlink($lockFile);

        return $this->_end("OK tasm=$tasm offset=$offset limit=$limit generated=$generated skipped=$skipped failed=$failed", 200);
    }

    /**
     * Serve cached PDF to parents.
     * Example: /cetak_raport/mhis_cached/12345/20241
     */
    // public function mhis_cached($id_siswa = null, $tasm = null)
    // {
    //     if (!$id_siswa || !$tasm || !preg_match('/^\d{5}$/', (string)$tasm)) {
    //         return $this->_end('Bad request', 400);
    //     }
    //     $tahun    = substr($tasm, 0, 4);
    //     $semester = substr($tasm, -1);

    //     $rapor = $this->db->query("
    //         SELECT nama, kelas
    //         FROM t_rapor
    //         WHERE id_siswa = ? AND tahun = ? AND semester = ?
    //         LIMIT 1
    //     ", [$id_siswa, $tahun, $semester])->row();
    //     if (!$rapor) {
    //         return $this->_end('No rapor found', 404);
    //     }

    //     $safeName  = $this->_safe($rapor->nama ?? 'SISWA');
    //     $safeKelas = $this->_safe($rapor->kelas ?? 'KELAS');
    //     $pdfPath   = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";

    //     if (is_file($pdfPath)) {
    //         // Stream with minimal memory
    //         header('Content-Type: application/pdf');
    //         header('Content-Length: ' . filesize($pdfPath));
    //         header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
    //         readfile($pdfPath);
    //         return;
    //     }
    //     // Optional: trigger on-demand generation, or ask user to try again later
    //     return $this->_end('Rapor sedang disiapkan. Silakan coba lagi beberapa saat.', 503);
    // }

    // ===== Internals =====

    private function _generate_single_pdf(int $id_siswa, string $tasm, bool $force = false)
    {
        $tahun    = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);

        $rapor = $this->db->query("
            SELECT * FROM t_rapor
            WHERE id_siswa = ? AND tahun = ? AND semester = ?
            LIMIT 1
        ", [$id_siswa, $tahun, $semester])->row();
        if (!$rapor) return false;

        $rapor_detail     = $this->db->query("SELECT * FROM t_rapor_detail WHERE id_rapor = ?", [$rapor->id])->result_array();
        $rapor_characters = $this->db->query("SELECT * FROM t_raport_character WHERE id_rapor = ?", [$rapor->id])->result_array();

        // Checksum so we only regenerate if data changed
        $payload  = ['rapor' => $rapor, 'detail' => $rapor_detail, 'characters' => $rapor_characters];
        $checksum = md5(json_encode($payload));

        $safeName  = $this->_safe($rapor->nama ?? 'SISWA');
        $safeKelas = $this->_safe($rapor->kelas ?? 'KELAS');
        $pdfPath   = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";
        $metaPath  = $pdfPath . '.json';

        if (!$force && is_file($pdfPath) && is_file($metaPath)) {
            $meta = json_decode(@file_get_contents($metaPath), true);
            if (!empty($meta['checksum']) && $meta['checksum'] === $checksum) {
                return 'skip'; // already up-to-date
            }
        }

        // Render HTML from view (no direct output)
        ob_start();
        $data = (array)$rapor;
        $data['details']    = $rapor_detail;
        $data['characters'] = $rapor_characters;

        if ((int)$rapor->tipe_rapor === 1) {
            $this->load->view('fix_cetak_pts_ikm', $data);
        } else {
            $this->load->view('fix_cetak_pts_sd', $data);
        }
        $html = ob_get_clean();

        // Safety for heavy render
@ini_set('memory_limit', '512M');
@set_time_limit(0);

        // Generate PDF
        $autoload = FCPATH . $this->HTML2PDF_AUTOLOAD;
        if (!is_file($autoload)) {
            log_message('error', 'Html2Pdf autoload not found at: '.$autoload);
            return false;
        }
        require_once $autoload;

        try {
            $pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', ['7mm','7mm','10mm','10mm']);
            $pdf->setTestTdInOnePage(false);
            $pdf->writeHTML($html);

            // Ensure cache base exists
            if (!is_dir($this->CACHE_BASE)) {
@mkdir($this->CACHE_BASE, 0775, true);
            }

            $pdf->output($pdfPath, 'F');

            // Write meta
@file_put_contents($metaPath, json_encode([
                'checksum'  => $checksum,
                'generated' => date('c'),
                'id_siswa'  => $id_siswa,
                'tasm'      => $tasm,
            ], JSON_PRETTY_PRINT));

            unset($pdf);
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'PDF gen failed for id_siswa='.$id_siswa.' tasm='.$tasm.' : '.$e->getMessage());
            return false;
        }
    }

    private function _lockPath(string $tasm, int $offset): string
    {
        $dir = sys_get_temp_dir() . '/rapor_cache_locks';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        return $dir . "/pregen_{$tasm}_offset{$offset}.lock";
    }

    private function _safe(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }

    private function _end(string $msg, int $code = 200)
    {
        $this->output->set_status_header($code)
            ->set_content_type('text/plain')
            ->set_output($msg);
    }
}