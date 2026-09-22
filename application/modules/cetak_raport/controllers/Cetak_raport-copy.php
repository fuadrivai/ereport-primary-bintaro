<?php
/*
-- ---------------------------------------------------------------
-- MYRAPORT K13
-- CREATED BY : NGODING PINTAR
-- COPYRIGHT  : Copyright (c) 2019 - 2020, (youtube.com/ngodingpintar)
-- CREATED ON : 2019-11-26
-- UPDATED ON : 2020-02-10
-- ---------------------------------------------------------------
*/
defined('BASEPATH') or exit('No direct script access allowed');

class Cetak_raport extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre . 'konid');
        $this->d['url'] = "cetak_raport";

        $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($get_tasm['tahun'], 0, 4);

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

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a;

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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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

            $_np = round(((($bobot_h / $jml_bobot) * ($jumlah_n_h / $jumlah_h)) +
                (($bobot_t / $jml_bobot) * $__tengah) +
                (($bobot_a / $jml_bobot) * $__akhir)), 0);

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Ananda telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
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
                                    WHERE a.id_siswa = $id_siswa
                                    AND a.tasm = '" . $tasm . "'")->result_array();

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

        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a + $bobot_p;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            $_nilai_keterampilan = round(((($bobot_h / $jml_bobot) * ($jumlah_nilai / $jumlah_array_nilai)) +
                (($bobot_t / $jml_bobot) * $__tengah) +
                (($bobot_a / $jml_bobot) * $__akhir) +
                (($bobot_p / $jml_bobot) * $__praktik)), 0);

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
                                            WHERE a.id_siswa = $id_siswa AND a.nilai != '-' AND a.tasm = '" . $tasm . "'")->result_array();
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

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");

        $d['nilai_utama'] .= '<tr><td colspan="9"><b>KELOMPOK A</b></td></tr>';
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

        //no pai kelompok A
        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'NO'")->result_array();
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

            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
            }

            $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">' . $no++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $npa . '</td>
                                        <td colspan="2" class="ctr">' . $npp . '</td>
                                        <td colspan="2" style="width:265px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
            $d['nilai_keterampilan'] .= '
                                    <tr>
                                        <td class="ctr">' . $noket++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $nka . '</td>
                                        <td colspan="2" class="ctr">' . $nkp . '</td>
                                        <td colspan="2" style="width:265px; padding: 20px 10px;">' . $nkd . '</td>
                                      
                                    </tr>';
        }

        //no pai kelompok B
        $d['nilai_utama'] .= '<tr><td colspan="9"><b>KELOMPOK B</b></td></tr>';
        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'B' AND tambahan_sub = 'NO'")->result_array();

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


            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
            }

            $d['nilai_utama'] .= '
                                    <tr>
                                    <td class="ctr">' . $no++ . '</td>
                                    <td style="width:250px;">' . $m['nama'] . '</td>
                                    <td class="ctr">' . $m['kkm'] . '</td>
                                    <td colspan="2" class="ctr">' . $npa . '</td>
                                    <td colspan="2" class="ctr">' . $npp . '</td>
                                     <td colspan="2" style="width:265px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
            $d['nilai_keterampilan'] .= '
                                    <tr>
                                        <td class="ctr">' . $noket++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $nka . '</td>
                                        <td colspan="2" class="ctr">' . $nkp . '</td>
                                         <td colspan="2" style="width:265px; padding: 20px 10px;">' . $nkd . '</td>
                                      
                                    </tr>';
        }
        //no pai kelompok C

        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            $d['nilai_utama'] .= '<tr><td colspan="9"><b>MUATAN LOKAL</b></td></tr>';
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


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
                }

                $d['nilai_utama'] .= '
                                            <tr>
                                            <td class="ctr">' . $no++ . '</td>
                                            <td style="width:250px;">' . $m['nama'] . '</td>
                                            <td class="ctr">' . $m['kkm'] . '</td>
                                            <td colspan="2" class="ctr">' . $npa . '</td>
                                            <td colspan="2" class="ctr">' . $npp . '</td>
                                             <td colspan="2" style="width:265px; padding: 20px 10px;">' . $npd . '</td>
                                            
                                            </tr>';
                $d['nilai_keterampilan'] .= '
                                    <tr>
                                        <td class="ctr">' . $noket++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $nka . '</td>
                                        <td colspan="2" class="ctr">' . $nkp . '</td>
                                         <td colspan="2" style="width:265px; padding: 20px 10px;">' . $nkd . '</td>
                                      
                                    </tr>';
            }
        }
        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            $d['nilai_utama'] .= '<tr><td colspan="9"><b>PROGRAM UNGGULAN SEKOLAH</b></td></tr>';
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


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                if ($lang_mapel == "eng") {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
                } else {
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
                }

                $d['nilai_utama'] .= '
                                            <tr>
                                            <td class="ctr">' . $no++ . '</td>
                                            <td style="width:250px;">' . $m['nama'] . '</td>
                                            <td class="ctr">' . $m['kkm'] . '</td>
                                            <td colspan="2" class="ctr">' . $npa . '</td>
                                            <td colspan="2" class="ctr">' . $npp . '</td>
                                             <td colspan="2" style="width:265px; padding: 20px 10px;">' . $npd . '</td>
                                            
                                            </tr>';
                $d['nilai_keterampilan'] .= '
                                    <tr>
                                        <td class="ctr">' . $noket++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $nka . '</td>
                                        <td colspan="2" class="ctr">' . $nkp . '</td>
                                         <td colspan="2" style="width:265px; padding: 20px 10px;">' . $nkd . '</td>
                                      
                                    </tr>';
            }
        }
        $d['nilai_utama'] .= '<tr><td class="ctr">' . $no . '</td><td colspan="8">Muatan Lokal</td></tr>';
        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'B' AND tambahan_sub = 'MULOK'")->result_array();

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
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            if ($lang_mapel == "eng") {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". You did " . $nilai_keterampilan[$idx]['desk'];
            } else {
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
            }

            $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr"></td>
                                        <td>' . $arr_huruf[$i] . '. ' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td class="ctr">' . $npa . '</td>
                                        <td class="ctr">' . $npp . '</td>
                                        <td class="font_kecil">' . $npd . '</td>
                                        
                                    </tr>';
            $d['nilai_keterampilan'] .= '
                                    <tr>
                                        <td class="ctr">' . $noket++ . '</td>
                                        <td style="width:250px;">' . $m['nama'] . '</td>
                                        <td class="ctr">' . $m['kkm'] . '</td>
                                        <td colspan="2" class="ctr">' . $nka . '</td>
                                        <td colspan="2" class="ctr">' . $nkp . '</td>
                                         <td colspan="2" style="width:265px; padding: 20px 10px;">' . $nkd . '</td>
                                      
                                    </tr>';
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

        $this->load->view('cetak_rapot', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));

        $pdf->WriteHTML($html);
        $pdf->Output('Data Siswa.pdf');
    }
    public function cetak_sd($id_siswa, $tasm)
    {
        ob_start();
        $d = array();
        ini_set('display_errors', 0);

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
                        OR a.jenis= 'a'
                        AND a.id_siswa = $id_siswa
                        AND a.tasm = '" . $tasm . "'
                        OR a.jenis= 'c'
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

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a;

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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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

            if ($tingkat == 1 || $tingkat == 2) {
                $_np = round(($jumlah_n_h / $jumlah_h), 0);
            } else {
            $_np = round((((2 * ($jumlah_n_h / $jumlah_h)) + $__tengah + $__akhir) / 4), 0);
            }

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Ananda telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
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
                                WHERE a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "' AND a.nilai != 0")->result_array();
        $ambil_nkuts = $this->db->query("SELECT 
                                c.id idmapel, c.kkm, c.lang, a.tasm, c.kd_singkat, a.jenis, a.catatan, a.nilai as nilai
                                FROM t_nilai_ket a
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
            } else if ($a22['jenis'] == "c") {
                $array2[$idx]['c'] = $a22['catatan'];
            }
        }
        //echo j($array2);
        $bobot_h = $this->config->item('pnk_h');
        $bobot_t = $this->config->item('pnk_t');
        $bobot_a = $this->config->item('pnk_a');
        $bobot_p = $this->config->item('pnk_p');

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a + $bobot_p;
        //MULAI HITUNG..

        $nilai_keterampilan = array();
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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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
            $txt_desk = array();
            foreach ($desk as $r => $s) {
                $txt_desk[] = $r . " " . implode(", ", $s);
            }
            $__tengah = empty($array2[$k]['t']) ? 0 : $array2[$k]['t'];
            $__akhir = empty($array2[$k]['a']) ? 0 : $array2[$k]['a'];
            $__praktik = empty($array2[$k]['p']) ? 0 : $array2[$k]['p'];
            if ($tingkat == 1 || $tingkat == 2) {
                $_nilai_keterampilan = round(($jumlah_nilai / $jumlah_array_nilai), 0);
            } else {
            $jumlah_array_nilai += 1;
            $_nilai_keterampilan = round((((2 * (($__praktik + $jumlah_nilai) / $jumlah_array_nilai)) + $__tengah + $__akhir) / 4), 0);
            }

            $nilai_keterampilan[$k]['nilai'] = number_format($_nilai_keterampilan);
            $nilai_keterampilan[$k]['predikat'] = nilai_huruf($kkmx, $_nilai_keterampilan);
            if ($lang_mapel == 'eng') {
                $nilai_keterampilan[$k]['desk'] = empty($array2[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array2[$k]['c'];
            } else {
                $nilai_keterampilan[$k]['desk'] = empty($array2[$k]['c']) ? 'Ananda telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array2[$k]['c'];
            }
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
                                            WHERE a.id_siswa = $id_siswa AND a.nilai != '-' AND a.tasm = '" . $tasm . "'")->result_array();
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
        //=============nilai_ekstra==============================================================
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

        $kelompok = array("A", "B");

        //foreach ($kelompok as $k) {
        //$q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = '$k'")->result_array();


        $arr_huruf = array("a", "b", "c", "d", "e");

        $d['nilai_utama'] .= '';
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

        //no pai kelompok A
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'A' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
        if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
        $d['nilai_utama'] .= '';
        }else{
          $d['nilai_utama'] .= '<tr><td colspan="9"><b>KELOMPOK A</b></td></tr>';  
        }
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
                                    WHERE kelompok = 'A' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'")->result_array();
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

            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }
            $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : $nilai_keterampilan[$idx]['desk'];

             if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">' . $no++ . '</td>
                                        <td style="width:170px;">' . $m['nama'] . '</td>
                                        <td colspan="2" class="ctr">' . $npa . '</td>
                                        <td colspan="2" style="width:330px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
            }else{
                $d['nilai_utama'] .= '
                                    <tr>
                                    <td valign="middle">' . $no++ . '</td>
                                    <td valign="middle" colspan="2" style="width:100px;">' . $m['nama'] . '</td>
                                    <td valign="middle">' . $npa . '</td>
                                    <td valign="middle">' . $npp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($npd, 100, "<br />\n") . '</td>
                                    <td valign="middle">' . $nka . '</td>
                                    <td valign="middle">' . $nkp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($nkd, 100, "<br />\n") . '</td>
                                      
                                    </tr>';
            }
        }

        //no pai kelompok B
        if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
        $d['nilai_utama'] .= '';
        }else{
          $d['nilai_utama'] .= '<tr><td colspan="9"><b>KELOMPOK B</b></td></tr>';  
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
                                    WHERE kelompok = 'B' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'")->result_array();

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

            $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];


            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];


            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : $nilai_keterampilan[$idx]['desk'];
            if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
                if($npa != 1){
                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">' . $no++ . '</td>
                                        <td style="width:170px;">' . $m['nama'] . '</td>
                                        <td colspan="2" class="ctr">' . $npa . '</td>
                                        <td colspan="2" style="width:330px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
                }
            }else{
            $d['nilai_utama'] .= '
                                    <tr>
                                    <td valign="middle">' . $no++ . '</td>
                                    <td valign="middle" colspan="2" style="width:100px;">' . $m['nama'] . '</td>
                                    <td valign="middle">' . $npa . '</td>
                                    <td valign="middle">' . $npp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($npd, 100, "<br />\n") . '</td>
                                    <td valign="middle">' . $nka . '</td>
                                    <td valign="middle">' . $nkp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($nkd, 100, "<br />\n") . '</td>
                                      
                                    </tr>';
            }
        }
        //no pai kelompok C

        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
                                    WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'MULOK' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
            $d['nilai_utama'] .= '';
            }else{
            $d['nilai_utama'] .= '<tr><td colspan="9"><b>MUATAN LOKAL</b></td></tr>';    
            }
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

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : $nilai_keterampilan[$idx]['desk'];
                if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
               if($npa != 1){
                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">' . $no++ . '</td>
                                        <td style="width:170px;">' . $m['nama'] . '</td>
                                        <td colspan="2" class="ctr">' . $npa . '</td>
                                        <td colspan="2" style="width:330px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
                }
            }else{
                $d['nilai_utama'] .= '
                                    <tr>
                                    <td valign="middle">' . $no++ . '</td>
                                    <td valign="middle" colspan="2" style="width:100px;">' . $m['nama'] . '</td>
                                    <td valign="middle">' . $npa . '</td>
                                    <td valign="middle">' . $npp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($npd, 100, "<br />\n") . '</td>
                                    <td valign="middle">' . $nka . '</td>
                                    <td valign="middle">' . $nkp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($nkd, 100, "<br />\n") . '</td>
                                      
                                    </tr>';
            }
            }
        }
        $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_mapel a
                                    INNER JOIN t_guru_mapel b ON a.id = b.id_mapel
                                    INNER JOIN m_guru c ON b.id_guru = c.id
                                    WHERE kelompok = 'PUS' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $d['det_siswa']['idkelas'] . "' AND b.tasm= '" . $tasm . "'")->result_array();
        $count = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'PUS' AND tambahan_sub = 'NO'")->num_rows();
        if ($count > 0) {
            if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
                $d['nilai_utama'] .= '';
            }else{
                $d['nilai_utama'] .= '<tr><td colspan="9"><b>PROGRAM UNGGULAN SEKOLAH</b></td></tr>';
            }
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

                $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];

                $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
                $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];


                if ($nka >= $kkmx) {
                    $predikatx1 = "sudah tuntas";
                } elseif ($nka < $kkmx) {
                    $predikatx1 = "belum tuntas";
                }

                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : $nilai_keterampilan[$idx]['desk'];
                if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
                if ($npa !== "-"){
                $d['nilai_utama'] .= '
                                    <tr>
                                        <td class="ctr">' . $no++ . '</td>
                                        <td style="width:170px;">' . $m['nama'] . '</td>
                                        <td colspan="2" class="ctr">' . $npa . '</td>
                                        <td colspan="2" style="width:330px; padding: 20px 10px;">' . $npd . '</td>
                                      
                                    </tr>';
                }
            }else{
                if ($npa !== "-"){
                $d['nilai_utama'] .= '
                                    <tr>
                                    <td valign="middle">' . $no++ . '</td>
                                    <td valign="middle" colspan="2" style="width:100px;">' . $m['nama'] . '</td>
                                    <td valign="middle">' . $npa . '</td>
                                    <td valign="middle">' . $npp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($npd, 100, "<br />\n") . '</td>
                                    <td valign="middle">' . $nka . '</td>
                                    <td valign="middle">' . $nkp . '</td>
                                    <td style="width:130px;font-size:11px; padding: 20px 10px;">' . wordwrap($nkd, 100, "<br />\n") . '</td>
                                      
                                    </tr>';
                }
            }
            }
        }
        // if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4'){
        // $d['nilai_utama'] .= '<tr><td colspan="6"><b>MUATAN LOKAL</b></td></tr>';
        // }else{
        // $d['nilai_utama'] .= '<tr><td class="ctr">' . $no . '</td><td colspan="8">Muatan Lokal</td></tr>';    
        // }
        $q_mapel = $this->db->query("SELECT * FROM m_mapel WHERE kelompok = 'B' AND tambahan_sub = 'MULOK'")->result_array();

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

           $npd = empty($nilai_pengetahuan[$idx]['desk']) ? "-" : $nilai_pengetahuan[$idx]['desk'];

            $nka = empty($nilai_keterampilan[$idx]['nilai']) ? "-" : $nilai_keterampilan[$idx]['nilai'];
            $nkp = empty($nilai_keterampilan[$idx]['predikat']) ? "-" : $nilai_keterampilan[$idx]['predikat'];
            if ($nka >= $kkmx) {
                $predikatx1 = "sudah tuntas";
            } elseif ($nka < $kkmx) {
                $predikatx1 = "belum tuntas";
            }

            $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : $nilai_keterampilan[$idx]['desk'];

            $d['nilai_utama'] .= '
                                    <tr>
                                    <td valign="middle">' . $no++ . '</td>
                                    <td valign="middle" colspan="2" style="width:100px;">' . $m['nama'] . '</td>
                                    <td valign="middle">' . $npa . '</td>
                                    <td valign="middle">' . $npp . '</td>
                                    <td style="width:130px;font-size:12px; padding: 20px 10px;">' . wordwrap($npd, 100, "<br />\n") . '</td>
                                    <td valign="middle">' . $nka . '</td>
                                    <td valign="middle">' . $nkp . '</td>
                                    <td style="width:130px;font-size:12px; padding: 20px 10px;">' . wordwrap($nkd, 100, "<br />\n") . '</td>
                                      
                                    </tr>';
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
        if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
        $this->load->view('cetak_ikm', $d);
        }else{
        $this->load->view('cetak_rapot_sd', $d);    
        }
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);
        $pdf->encoding = 'UTF-8';
        $pdf->setTestTdInOnePage(false);
        $pdf->WriteHTML($html);
        $pdf->Output($d['det_siswa']['nama'].'-'.$d['wali_kelas']['nmkelas'].'-'.$tasm.'.pdf');
    }
    public function cetak_projek($id_siswa, $tasm)
    {
        ob_start();
        $d = array();
        ini_set('display_errors', 0);

        $d['semester'] = substr($tasm, 4, 1);
        $d['ta'] = (substr($tasm, 0, 4)) . "/" . (substr($tasm, 0, 4) + 1);
        $ta = substr($tasm, 0, 4);

        $siswa = $this->db->query("SELECT 
                                    a.nama, a.id as idsiswa, a.nis, a.nisn, c.tingkat, c.id idkelas
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
                                c.id idmapel, c.kelompok, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',d.nama_kd),a.nilai) nilai
                                FROM t_nilai_kelompok a
                                INNER JOIN t_guru_kelompok b ON a.id_guru_mapel = b.id
                                INNER JOIN m_kelompok c ON b.id_kelompok = c.id
                                INNER JOIN t_kelompok_kd d ON a.id_kelompok_kd = d.id
                                WHERE a.id_siswa = $id_siswa
                                AND a.tasm = '" . $tasm . "'
                                AND a.nilai != 0
                                ")->result_array();
        $ambil_np_submp = $this->db->query("SELECT 
                                    b.id_kelompok, c.kd_singkat
                                    FROM t_nilai_kelompok a
                                    INNER JOIN t_guru_kelompok b ON a.id_guru_mapel = b.id
                                    INNER JOIN m_kelompok c ON b.id_kelompok = c.id
                                    WHERE a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                    GROUP BY b.id_kelompok")->result_array();

        $ambil_np_projek = $this->db->query("SELECT DISTINCT
                                                d.kelompok, d.nama, d.description, d.p_singkat
                                            FROM 
                                                t_nilai_kelompok a
                                                INNER JOIN t_guru_kelompok b ON a.id_guru_mapel = b.id
                                                INNER JOIN m_kelompok c ON b.id_kelompok = c.id
                                                INNER JOIN m_projek d ON c.kelompok = d.p_singkat
                                            WHERE
                                                a.id_siswa = $id_siswa AND a.tasm = '" . $tasm . "'
                                    GROUP BY b.id_kelompok")->result_array();
        $noprojek = 1;
        $d['nama_projek'] = $ambil_np_projek;
        foreach ($ambil_np_projek as $i) {
        $kelompokDescriptions = [
                                    "GHB"=>"Gaya Hidup Berkelanjutan","KL"=>"Kearifan Lokal","BTI"=>"Bhineka Tunggal Ika","BJR"=>"Bangunlah Jiwa dan Raganya","SD"=>"Suara Demokrasi","BBMN"=>"Berekayasa dan Berteknologi untuk Membangun NKRI","KW"=>"Kewirausahaan"
                                ];
        $kelompok = $kelompokDescriptions[$i['kelompok']] ?? 'Unknown Kelompok';
        
        $d['projek'] .= '
                                    <tr>
                                        <td style="width:610px; padding: 10px 10px;"><b> Projek ' . $noprojek++ . ' | ' . $kelompok . '<br>' . $i['nama'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width:610px; padding: 15px 10px;text-align: justify;">' . nl2br($i['description']) . '</td>
                                    </tr>   ';
        }
        $array1 = array();
        $d['nilai_projek'] = array();
        foreach ($ambil_np_submp as $a1) {
            $array1[$a1['id_kelompok']] = array();
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
                $d['nilai_projek'][$idx]['h'][] = $a2['nilai'];
            } else if ($a2['jenis'] == "t") {
                $array1[$idx]['t'] = $a2['nilai'];
            } else if ($a2['jenis'] == "a") {
                $array1[$idx]['a'] = $a2['nilai'];
            } else if ($a2['jenis'] == "c") {
                $array1[$idx]['c'] = $a2['catatan'];
            }
        }

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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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

            if ($tingkat == 1 || $tingkat == 2) {
                $_np = round(($jumlah_n_h / $jumlah_h), 0);
            } else {
            $_np = round((((2 * ($jumlah_n_h / $jumlah_h)) + $__tengah + $__akhir) / 4), 0);
            }

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Ananda telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            }

        }
        //echo j($nilai_pengetahuan);
        $d['nilai_pengetahuan'] = $nilai_pengetahuan;
        // END Nilai PENGETAHUAN

        
        // END Nilai PENGETAHUAN

       

        //===========================================================================
        //              START NIlai Ekstrakurikuler
        //===========================================================================
        $q_nilai_ekstra = $this->db->query("SELECT 
                                            b.nama, a.nilai, a.desk
                                            FROM t_nilai_ekstra a
                                            INNER JOIN m_ekstra b ON a.id_ekstra = b.id
                                            WHERE a.id_siswa = $id_siswa AND a.nilai != '-' AND a.tasm = '" . $tasm . "'")->result_array();
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
        //=============nilai_ekstra==============================================================
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

        $kelompok = array("A", "B");


        $arr_huruf = array("a", "b", "c", "d", "e");

        $d['nilai_utama'] .= '';
        $no = 0;
        $noket = 0;

        $no++;
        $noket++;
        $d['tasm'] = $tasm;
        $d['det_raport'] = $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport, tgl_raport_kelas3 FROM tahun WHERE tahun = '$tasm'")->row_array();

        //utk naik kelas atau tidak
        $q_catatan = $this->db->query("SELECT 
                                    a.*
                                    FROM t_naikkelas a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan'] = $q_catatan;
        if ($siswa['tingkat'] == '1' || $siswa['tingkat'] == '4' || $siswa['tingkat'] == '2' || $siswa['tingkat'] == '5'){
        $this->load->view('cetak_projek', $d);
        }else{
        $this->load->view('cetak_projek', $d);    
        }
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);
        $pdf->encoding = 'UTF-8';
        $pdf->setTestTdInOnePage(false);
        $pdf->WriteHTML($html);
        $pdf->Output($d['det_siswa']['nama'].'-'.$d['wali_kelas']['nmkelas'].'-'.$tasm.'.pdf');
    }
    public function cetak_mh($id_siswa, $tasm)
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

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a;

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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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

            $_np = round(((($bobot_h / $jml_bobot) * ($jumlah_n_h / $jumlah_h)) +
                (($bobot_t / $jml_bobot) * $__tengah) +
                (($bobot_a / $jml_bobot) * $__akhir)), 0);

            $nilai_pengetahuan[$k]['nilai'] = number_format($_np);
            $nilai_pengetahuan[$k]['predikat'] = nilai_huruf($kkmx, $_np);
            if ($lang_mapel == 'eng') {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'You did ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
            } else {
                $nilai_pengetahuan[$k]['desk'] = empty($array1[$k]['c']) ? 'Ananda telah ' . str_replace('; ', '. ', implode("; ", $txt_desk)) : $array1[$k]['c'];
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
                                    WHERE a.id_siswa = $id_siswa
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
                                    c.id idmapel, c.kkm, a.tasm, c.kd_singkat, a.jenis, a.nilai as nilai
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

        $jml_bobot = $bobot_h + $bobot_t + $bobot_a + $bobot_p;
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
                    $_desk1 = 'Akan tetapi, ananda harus tetap belajar dan banyak latihan di rumah untuk';
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
            $_nilai_keterampilan = round(((($bobot_h / $jml_bobot) * ($jumlah_nilai / $jumlah_array_nilai)) +
                (($bobot_t / $jml_bobot) * $__tengah) +
                (($bobot_a / $jml_bobot) * $__akhir) +
                (($bobot_p / $jml_bobot) * $__praktik)), 0);

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
                                            WHERE a.id_siswa = $id_siswa AND a.nilai != '-' AND a.tasm = '" . $tasm . "'")->result_array();
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
             WHERE kelompok = 'ICB' AND tambahan_sub = 'final'")->result_array();
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
             WHERE kelompok = 'PSS' AND tambahan_sub = 'final'")->result_array();
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
             WHERE kelompok = 'LA' AND tambahan_sub = 'final'")->result_array();
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
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
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
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
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
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
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
                    $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
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
                $nkd = empty($nilai_keterampilan[$idx]['desk']) ? "-" : "Capaian kompetensi Ananda " . $siswa['nama'] . " " . $predikatx1 . " dengan predikat " . nilai_pre($kkmx, $nka, $lang_mapel) . ". Ananda telah " . $nilai_keterampilan[$idx]['desk'];
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
        //utk naik kelas atau tidak
        $q_catatan_homeroom = $this->db->query("SELECT 
                                    a.*
                                    FROM t_catatan_homeroom a 
                                    WHERE a.id_siswa = $id_siswa AND a.ta = '$tasm'")->row_array();
        $d['catatan_homeroom'] = $q_catatan_homeroom;
        $this->load->view('cetak_mh', $d);
        $html = ob_get_contents();
        ob_end_clean();

        require './aset/html2pdf/autoload.php';

        $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', false, 'UTF-8', array('7mm', '7mm', '10mm', '10mm'));
        $str = utf8_decode($html);

        $pdf->WriteHTML($str);
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
                                                WHERE a.id_kelas = '" . $wali['id_walikelas'] . "' AND a.ta = '" . $this->d['ta'] . "' ORDER BY b.nama ASC")->result_array();

        $this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

}