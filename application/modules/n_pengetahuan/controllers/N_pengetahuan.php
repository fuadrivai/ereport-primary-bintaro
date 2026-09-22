<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

defined('BASEPATH') OR exit('No direct script access allowed');
class N_pengetahuan extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');
        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['url'] = "n_pengetahuan";
        $this->d['idnya'] = "setmapel";
        $this->d['nama_form'] = "f_setmapel";
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['semester'] = substr($this->d['tasm'], -1, 1);
        $this->load->helper('buzz');

        $this->d['tahun'] = substr($this->d['tasm'], 0, 4);

        $this->kolom_xl = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");
        $this->d['id_guru_mapel'] = 0;
    }

     public function cetak($bawa,$tasm=0) {
        $tasm = $tasm == 0 ? $this->d['tasm'] : $tasm;
        $semester = substr($tasm,4,1);
        
        $pc_bawa = explode("-", $bawa);

        $html = '';

        $detil_guru = $this->db->query("SELECT 
                                b.nama nmmapel, b.lang, b.kkm, c.nama nmkelas, d.nama nmguru
                                FROM t_guru_mapel a
                                INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                INNER JOIN m_kelas c ON a.id_kelas = c.id
                                INNER JOIN m_guru d ON a.id_guru = d.id 
                                WHERE b.id = ".$pc_bawa[0]." AND c.id = ".$pc_bawa[1]." 
                                AND a.tasm = '".$tasm."'")->row_array();
        //j($detil_guru);

        $q_nilai_harian = $this->db->query("SELECT 
                                d.nama nmsiswa, a.id_mapel_kd, a.id_siswa, a.catatan, if(a.jenis='h',CONCAT(a.nilai,'//',b.nama_kd),a.nilai) nilai
                                FROM t_nilai a
                                LEFT JOIN t_mapel_kd b ON a.id_mapel_kd = b.id
                                LEFT JOIN t_kelas_siswa c ON CONCAT(a.id_siswa,LEFT(a.tasm,4)) = CONCAT(c.id_siswa,c.ta)
                                LEFT JOIN m_siswa d ON c.id_siswa = d.id
                                WHERE c.id_kelas = ".$pc_bawa[1]." AND b.id_mapel = ".$pc_bawa[0]."
                                AND a.tasm = '".$tasm."'
                                ORDER BY d.nama ASC")->result_array(); 
        //echo $this->db->last_query();

        $q_nilai_ta = $this->db->query("SELECT 
                                d.nama nmsiswa, a.jenis, a.id_siswa, a.nilai
                                FROM t_nilai a
                                LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id 
                                LEFT JOIN t_kelas_siswa c ON CONCAT(a.id_siswa,LEFT(a.tasm,4)) = CONCAT(c.id_siswa,c.ta)
                                LEFT JOIN m_siswa d ON c.id_siswa = d.id
                                WHERE c.id_kelas = ".$pc_bawa[1]." AND b.id_mapel = ".$pc_bawa[0]." AND a.jenis != 'h'
                                AND a.tasm = '".$this->d['tasm']."'
                                ORDER BY d.nama ASC")->result_array();

        $q_kd_guru_ini = $this->db->query("SELECT a.* 
                                    FROM t_mapel_kd a
                                    LEFT JOIN m_kelas b ON a.tingkat = b.tingkat
                                    WHERE a.id_guru = '".$this->d['admkonid']."'
                                    AND a.id_mapel = '".$pc_bawa[0]."'
                                    AND b.id = ".$pc_bawa[1]." 
                                    AND a.semester = '".$semester."'
                                    AND a.jenis = 'P' order by a.id asc")->result_array();

        $d_kd = array();

        if (!empty($q_kd_guru_ini)) {
            foreach ($q_kd_guru_ini as $v) {
                $idx = $v['id'];
                $d_kd[$idx]['kode'] = $v['no_kd'];
                $d_kd[$idx]['nama_kd'] = $v['nama_kd'];
            }
        }


        $d_nilai = array();

        if (!empty($q_nilai_harian)) {
            foreach ($q_nilai_harian as $d) {
                $pc_nilai_h = explode("//", $d['nilai']);
                $_desk = nilai_pre($detil_guru['kkm'],$pc_nilai_h[0],$detil_guru['lang']);
                $do = do_lang($detil_guru['kkm'], $pc_nilai_h[0]);
                $idx1 = $d['id_siswa'];
                $idx2 = $d['id_mapel_kd'];
                $catatan = $d['catatan'];
                $d_nilai[$idx1]['nama'] = $d['nmsiswa'];
                $d_nilai[$idx1]['h'][$idx2]['nilai_huruf'] = nilai_huruf($detil_guru['kkm'], $pc_nilai_h[0]);
                $d_nilai[$idx1]['h'][$idx2]['nilai_pre'] = nilai_pre($detil_guru['kkm'], $pc_nilai_h[0],$detil_guru['lang']);
                $d_nilai[$idx1]['h'][$idx2]['nilai_angka'] = $pc_nilai_h[0];
                $d_nilai[$idx1]['h'][$idx2]['nilai'] = $d['nilai'];
                
            }
        } 

        if (!empty($q_nilai_ta)) {
            foreach ($q_nilai_ta as $e) {
                $idx1 = $e['id_siswa'];
                
                if ($e['jenis'] == "t") {
                    $d_nilai[$idx1]['uts'] = $e['nilai'];
                } else if ($e['jenis'] == "a") {
                    $d_nilai[$idx1]['uas'] = $e['nilai'];
                }
            }
        } else {
            foreach ($q_siswa_kelas as $dk) {
                $idx1 = $dk['id_siswa'];
                
                $d_nilai[$idx1]['uts'] = 0;
                $d_nilai[$idx1]['uas'] = 0;
            }
        }

       // var_dump($d_nilai);die();
        //j($d_kd);

        $jml_kd = sizeof($d_kd);

        $html = '<p align="left"><b>REKAP NILAI PENGETAHUAN</b>
                <br>
                Mata Pelajaran : '.$detil_guru['nmmapel'].', Kelas : '.$detil_guru['nmkelas'].', Guru : '.$detil_guru['nmguru'].', Tahun Pelajaran: '.$tasm.'<hr style="border: solid 1px #000; margin-top: -10px"></p>
                <table class="table"><thead><tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama</th>
                <th colspan="'.$jml_kd.'">Kode KD</th>
                <th rowspan="2">Rata-rata UH</th>
                <th rowspan="2">UTS</th>
                <th rowspan="2">UAS</th>
                <th colspan="4">Nilai Akhir</th>
                </tr>
                <tr>';

        if (!empty($d_kd)) {
            foreach ($d_kd as $kd) {
                $html .= '<th>'.$kd['kode'].'</th>';
            }
        }

        //j($d_nilai);

        $html .= '<th>Nilai</th><th>Predikat</th><th width="80">KKM <br>'.$detil_guru['kkm'].'</th><th>Deskripsi</th></tr></thead><tbody>';

        if (!empty($d_nilai)) {
            $no = 1;
            foreach ($d_nilai as $ke => $dn) {
                
               

                $html .= '<tr><td class="ctr">'.$no.'</td><td>'.$dn['nama'].'</td>';

                $jml_nilai_tugas = 0;

                
                $array_undefined = array();
                $undefined = "";
                $array1 = array();
                $desk = array();
                if (!empty($d_kd)) {
                    foreach ($d_kd as $k => $v) {
                        $id_siswa = $ke;
                        $id_kd = $k;
                        
                        
                        $nil_huruf = empty($dn["h"][$id_kd]['nilai_huruf']) ? "" : $dn["h"][$id_kd]['nilai_huruf'];
                        $nilai1 = empty($dn["h"][$id_kd]['nilai']) ? "" : $dn["h"][$id_kd]['nilai'];
                        $array1[] = $nilai1;
                        


                        $nilai_uh = empty($dn["h"][$id_kd]["nilai_angka"]) ? '' : $dn["h"][$id_kd]["nilai_angka"];

                        //$dn[119]["h"][45]["nilai_angka"]

                        $html .= '<td class="ctr">'.$nilai_uh.'</td>';
                        $jml_nilai_tugas += floatval($nilai_uh);

                        //$html .= '<td>'.var_dump($dn).'</td>';
                    }
                    $arrayh = max($array1);
                    $arrayhmin = min($array1);
                    $pc_nilai_hmin = explode("//", $arrayhmin);
                    $pc_nilai_h = explode("//", $arrayh);
                    $lang_mapel = $detil_guru['lang'];
                    $_desk = nilai_pre($detil_guru['kkm'],$pc_nilai_h[0],$detil_guru['lang']);
                    $do = do_lang($detil_guru['kkm'], $pc_nilai_h[0]);
                    if ($lang_mapel == "eng"){
                    
                        $_desk1 = 'However, you '.$do.' continue to develop your comprehension on how to';
                        $desk[$_desk][] = "on how to ".$pc_nilai_h[1];
                        $desk[$_desk1][] = $pc_nilai_hmin[1];
                    }else{
                        $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                        $desk[$pc_nilai_h[1]][] = "dengan ".$_desk;
                        $desk[$_desk1][] = $pc_nilai_hmin[1];
                    }
                }
                $txt_desk = array();
                foreach ($desk as $r => $s) {
                    $txt_desk[] = $r." ".implode(", ", $s);
                }
                $n_h    = number_format($jml_nilai_tugas / $jml_kd);
                $nilai_akhir_kd = number_format($n_h);

                 //$ke = id siswa
                $idsx = $ke;
                $nilai_pengetahuan = implode("; ", $txt_desk); 
                $nilai_uts = !empty($d_nilai[$idsx]['uts']) ? number_format($d_nilai[$idsx]['uts']) : 0;
                $nilai_uas = !empty($d_nilai[$idsx]['uas']) ? number_format($d_nilai[$idsx]['uas']) : 0;

                 $p_h = $this->config->item('pnp_h');
                $p_t = $this->config->item('pnp_t');
                $p_a = $this->config->item('pnp_a');
                $jml = $p_h+$p_t+$p_a;

                $p_h = ($p_h / $jml) * 100; 
                $p_t = ($p_t / $jml) * 100; 
                $p_a = ($p_a / $jml) * 100; 
                
                $na_np = number_format((($nilai_akhir_kd * $p_h) + ($nilai_uts * $p_t) + ($nilai_uas * $p_a)) / 100);
                
                //pengujian kkm
                if($na_np < $detil_guru['kkm']){
                    $kkm = "Tidak Tuntas";
                }else{
                    $kkm = "Tuntas";
                }

                $html .= '<td class="ctr">'.$nilai_akhir_kd.'</td>
                    <td class="ctr">'.$nilai_uts.'</td>
                    <td class="ctr">'.$nilai_uas.'</td>
                    <td class="ctr">'.$na_np.'</td>'.
                    '<td class="ctr">'.nilai_huruf($detil_guru['kkm'], $na_np).'</td><td class="ctr">'.$kkm.'</td><td> Ananda '.strtoupper($dn['nama']).', '.$nilai_pengetahuan.'</td>';

                $no++;
            }
        } else {
            $html .= '<tr><td colspan="'.($jml_kd+6).'">Belum ada data</td></tr>';
        }

        $this->d['html'] = $html;
        $this->load->view('cetak', $this->d);
    }
    public function import($bawa) {
        $this->load->library('excel');
        
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Nur Akhwan")
                             ->setLastModifiedBy("Aplikasi Rapor Kurikulum 2013")
                             ->setTitle("Office 2007 XLSX Test Document")
                             ->setSubject("Office 2007 XLSX Test Document")
                             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Test result file");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Hanya isi pada cell dengan background HIJAU');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Cukup isikan di isian nilai Per KD, UTS dan UAS');

        $objPHPExcel->getActiveSheet()->getRowDimension('7')->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);

        //query utama import
        $pc_bawa = explode("-", $bawa);
        $detil_guru = $this->db->query("SELECT 
                                         a.id_guru, b.nama nmmapel, b.lang, b.kkm, c.nama nmkelas, d.nama nmguru
                                         FROM t_guru_mapel a
                                         INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                         INNER JOIN m_kelas c ON a.id_kelas = c.id
                                         INNER JOIN m_guru d ON a.id_guru = d.id 
                                         WHERE b.id = ".$pc_bawa[0]." AND c.id = ".$pc_bawa[1]." AND a.tasm = '".$this->d['tasm']."'")->row_array();
        $q_nilai_harian = $this->db->query("SELECT 
                                d.nama nmsiswa, a.jenis, a.id_mapel_kd, a.id_siswa, if(a.jenis='h',CONCAT(a.nilai,'//',b.nama_kd),a.nilai) nilai
                                FROM t_nilai a
                                LEFT JOIN t_mapel_kd b ON a.id_mapel_kd = b.id
                                LEFT JOIN t_kelas_siswa c ON CONCAT(a.id_siswa,LEFT(a.tasm,4)) = CONCAT(c.id_siswa,c.ta)
                                LEFT JOIN m_siswa d ON c.id_siswa = d.id
                                WHERE c.id_kelas = ".$pc_bawa[1]." AND b.id_mapel = ".$pc_bawa[0]."
                                AND a.tasm = '".$this->d['tasm']."'
                                ORDER BY d.nama ASC")->result_array();

        $q_nilai_ta = $this->db->query("SELECT 
                                d.nama nmsiswa, a.jenis, a.id_siswa, a.nilai, a.catatan, a.catatan_mid
                                FROM t_nilai a
                                LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id 
                                LEFT JOIN t_kelas_siswa c ON CONCAT(a.id_siswa,LEFT(a.tasm,4)) = CONCAT(c.id_siswa,c.ta)
                                LEFT JOIN m_siswa d ON c.id_siswa = d.id
                                WHERE c.id_kelas = ".$pc_bawa[1]." AND b.id_mapel = ".$pc_bawa[0]." AND a.jenis != 'h'
                                AND a.tasm = '".$this->d['tasm']."'
                                ORDER BY d.nama ASC")->result_array();
        
        $q_kd_guru_ini = $this->db->query("SELECT a.* 
                                    FROM t_mapel_kd a
                                    LEFT JOIN m_kelas b ON a.tingkat = b.tingkat
                                    WHERE a.id_mapel = '".$pc_bawa[0]."'
                                    AND b.id = ".$pc_bawa[1]." 
                                    AND a.semester = '".$this->d['semester']."'
                                    AND a.jenis = 'P'")->result_array();
        $q_siswa_kelas = $this->db->query("SELECT a.id_siswa, b.nama nmsiswa FROM t_kelas_siswa a LEFT JOIN m_siswa b ON a.id_siswa = b.id WHERE a.id_kelas = '".$pc_bawa[1]."' AND a.ta = '".$this->d['tahun']."' order by b.nama ASC")->result_array();

        $d_kd = array();
        if (!empty($q_kd_guru_ini)) {
            foreach ($q_kd_guru_ini as $v) {
                $idx = $v['id'];
                $d_kd[$idx]['kode'] = $v['no_kd'];
            }
        }
        $d_nilai = array();
        if (!empty($q_nilai_harian)) {
            foreach ($q_nilai_harian as $d) {
                $pc_nilai_h = explode("//", $d['nilai']);
                $idx1 = $d['id_siswa'];
                $idx2 = $d['id_mapel_kd'];
                $d_nilai[$idx1]['nama'] = $d['nmsiswa'];
                $d_nilai[$idx1]['h'][$idx2]['nilai_angka'] = $pc_nilai_h[0];
                $d_nilai[$idx1]['h'][$idx2]['nilai'] = $d['nilai'];
            }
        } else {
            foreach ($q_siswa_kelas as $dk) {
                $idx1 = $dk['id_siswa'];

                foreach ($q_kd_guru_ini as $kg) {
                    $idx2 = $kg['id'];
                    $d_nilai[$idx1]['nama'] = $dk['nmsiswa'];
                    $d_nilai[$idx1]['h'][$idx2]['nilai_angka'] = 0;
                }
            }
        }

        if (!empty($q_nilai_ta)) {
            foreach ($q_nilai_ta as $e) {
                $idx1 = $e['id_siswa'];
                
                if ($e['jenis'] == "t") {
                    $d_nilai[$idx1]['uts'] = $e['nilai'];
                } else if ($e['jenis'] == "a") {
                    $d_nilai[$idx1]['uas'] = $e['nilai'];
                } else if ($e['jenis'] == "c") {
                    $d_nilai[$idx1]['catatan'] = $e['catatan'];
                    $d_nilai[$idx1]['catatan_mid'] = $e['catatan_mid'];
                }
            }
        } else {
            foreach ($q_siswa_kelas as $dk) {
                $idx1 = $dk['id_siswa'];
                
                $d_nilai[$idx1]['uts'] = 0;
                $d_nilai[$idx1]['uas'] = 0;
            }
        }

        $jml_kd = sizeof($d_kd);
        //akhir ambil variabel db
        /// mulai
        $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ID Mapel');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $pc_bawa[0]);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', ": ".$detil_guru['nmmapel']);
        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'ID Guru');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $detil_guru['id_guru']);
        $objPHPExcel->getActiveSheet()->setCellValue('C5', ": ".$detil_guru['nmguru']);
        $objPHPExcel->getActiveSheet()->setCellValue('A6', 'ID Kelas');
        $objPHPExcel->getActiveSheet()->setCellValue('B6', $pc_bawa[1]);
        $objPHPExcel->getActiveSheet()->setCellValue('C6', ": ".$detil_guru['nmkelas']);

        $objPHPExcel->getActiveSheet()->getStyle('C4:C6')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B6')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('B4:B6')->getFont()->getColor()->setARGB('ffffff');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getStyle('7')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('7')->getFont()->setSize(11);

        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'No');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'Nama');

        $kolom_awal = 2;
        $kolom = $kolom_awal;
        foreach($d_kd as $k) {
            $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', $k['kode']);
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$kolom])->setWidth(10);
            $kolom++;
        }

        $kolom_akhir_kd = ($kolom-1);
        $kolom_uts = $kolom;
        $kolom_uas = ($kolom+1);
        
        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', 'UTS');
        $kolom++;
        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', 'UAS');
        $kolom++;
        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', 'Catatan Diknas Mid');
        $kolom++;
        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', 'Catatan Diknas Final');
        $kolom++;

        $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$kolom])->setVisible(FALSE);
        $kolom++;            
        foreach($d_kd as $k) {        
            //$objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$kolom].'7', $kolom);
            if (!empty($this->kolom_xl[$kolom])) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$kolom])->setVisible(FALSE);
                $kolom++;
            }
        }
        
        //bds = baris mulai data nilai
        $bds = 8;


        if (!empty($d_nilai)) {
            $no = 1;
            foreach ($d_nilai as $ke => $dn) {
                $ke = empty($ke) ? "" : $ke;
                $nama = empty($dn['nama']) ? "" : $dn['nama'];
                
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$bds, $no);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$bds, $nama);
                $array1 = array();
                $desk = array();
                $klm = $kolom_awal;
                if (!empty($d_kd)) {
                    foreach ($d_kd as $k => $v) {
                        $id_siswa = $ke;
                        $id_kd = $k;
                        $nilai1 = empty($dn["h"][$id_kd]['nilai']) ? "" : $dn["h"][$id_kd]['nilai'];
                        $array1[] = $nilai1;
                        $nilai_uh = empty($dn["h"][$id_kd]["nilai_angka"]) ? '' : $dn["h"][$id_kd]["nilai_angka"];
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $nilai_uh);
                        $klm++;
                    }
                    $arrayh = max($array1);
                    $arrayhmin = min($array1);
                    if (empty($arrayhmin)) {
                        
                        $pc_nilai_hmin = explode("//", $arrayh);
                    } else {
                        $pc_nilai_hmin = explode("//", $arrayhmin);
                    }
                    $pc_nilai_h = explode("//", $arrayh);
                    $lang_mapel = $detil_guru['lang'];
                    $_desk = nilai_pre($detil_guru['kkm'],$pc_nilai_h[0],$detil_guru['lang']);
                    $do = do_lang($detil_guru['kkm'], $pc_nilai_h[0]);
                    if (!empty($arrayh) && !empty($arrayh)){
                        if ($lang_mapel == "eng"){
                    
                            $_desk1 = 'However, you '.$do.' continue to develop your comprehension on how to';
                            $desk[$_desk][] = "on how to ".$pc_nilai_h[1];
                            $desk[$_desk1][] = $pc_nilai_hmin[1];
                        }else{
                            $_desk1 = 'Akan tetapi, kamu harus tetap belajar dan banyak latihan di rumah untuk';
                            $desk[$pc_nilai_h[1]][] = "dengan ".$_desk;
                            $desk[$_desk1][] = $pc_nilai_hmin[1];
                        }

                    }
                    
                } else {
                    exit("KD masih kosong. Silakan diisi");
                }
                $txt_desk = array();
                foreach ($desk as $r => $s) {
                    $txt_desk[] = $r." ".implode(", ", $s);
                    
                }
                $catatan_pengetahuan = str_replace('; ', '. ', implode("; ", $txt_desk)); 
                $n_uts  = empty($dn["uts"]) ? '' : number_format($dn["uts"]);
                $n_uas  = empty($dn["uas"]) ? '' : number_format($dn["uas"]);
                $n_catatan  = empty($dn["catatan"]) ? $catatan_pengetahuan : $dn["catatan"];
                $n_catatan_mid  = empty($dn["catatan_mid"]) ? $catatan_pengetahuan : $dn["catatan_mid"];
                $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_uts);
                $klm++;
                $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_uas);
                $klm++;
                if ($lang_mapel == 'eng'){
                    if (!empty($txt_desk)){
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, 'You did '.$n_catatan_mid);
                        $klm++;
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, 'You did '.$n_catatan);
                        $klm++;
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_catatan_mid);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$klm])->setWidth(50);
                        $klm++;
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_catatan);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$klm])->setWidth(50);
                        $klm++;
                    }
                    
                }else{
                    if (!empty($txt_desk)){
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, 'Kamu telah '.$n_catatan_mid);
                        $klm++;
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, 'Kamu telah '.$n_catatan);
                        $klm++;
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_catatan_mid);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$klm])->setWidth(50);
                        $klm++;
                        $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $n_catatan);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->kolom_xl[$klm])->setWidth(50);
                        $klm++;
                    }
                }
                
                
                $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $id_siswa);
                $klm++;
                
                foreach ($d_kd as $k => $v) {
                    $id_siswa = $ke;
                    $id_kd = $k;
                    
                    $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, "h-".$id_kd);                        
                    $klm++;
                }

                $c_nh = $this->config->item('pnp_h');
                $c_nt = $this->config->item('pnp_t');
                $c_na = $this->config->item('pnp_a');
                $jml_pn = ($c_nh+$c_nt+$c_na);

                $rumus = "=round(((SUM(".$this->kolom_xl[$kolom_awal].$bds.":".$this->kolom_xl[$kolom_akhir_kd].$bds.")/".$jml_kd."*".($c_nh/$jml_pn).")+(".$this->kolom_xl[$kolom_uts].$bds."*".($c_nt/$jml_pn).")+(".$this->kolom_xl[$kolom_uas].$bds."*".($c_na/$jml_pn).")),0)";
                //echo $rumus."<br>";

                $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[$klm].$bds, $rumus);
                $klm++;

                $bds++;
                $no++;
                
            }
            $objPHPExcel->getActiveSheet()->setCellValue($this->kolom_xl[($klm-1)].'7', 'NP Akhir');
        }

        //exit;

        $koordinat_awal = "C8";
        $koordinat_akhir = $this->kolom_xl[(($kolom-2)-$jml_kd)].($bds-1);

        $objPHPExcel->getActiveSheet()->getStyle($koordinat_awal.':'.$koordinat_akhir)->applyFromArray(
        array('fill'    => array(
                                    'type'      => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color'     => array('argb' => '01ff80')
                                )
             )
        );    
        //proteksi cell
        $objPHPExcel->getActiveSheet()->getProtection()->setPassword('super90');
        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setInsertColumns(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
        //proteksi kecuali untuk sheet
        $objPHPExcel->getActiveSheet()->getStyle($koordinat_awal.":".$koordinat_akhir)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

        $nama_file = "NP_".str_replace(" ","",$detil_guru['nmkelas'])."_".str_replace(" ", "_", $detil_guru['nmmapel']).'_'.str_replace(" ", "_", $detil_guru['nmguru']).'.xlsx';
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$nama_file.'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die();
    }

    public function upload($bawa) {
        $pc_bawa = explode("-", $bawa);
        
        $detil_guru = $this->db->query("SELECT 
                                         a.id, a.id_guru, b.nama nmmapel, c.nama nmkelas, d.nama nmguru
                                         FROM t_guru_mapel a
                                         INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                         INNER JOIN m_kelas c ON a.id_kelas = c.id
                                         INNER JOIN m_guru d ON a.id_guru = d.id 
                                         WHERE b.id = ".$pc_bawa[0]." AND c.id = ".$pc_bawa[1]."
                                         AND a.tasm = '".$this->d['tasm']."'")->row_array();
        

        $this->d['bawa'] = $detil_guru;
        $this->d['id_kelas'] = $pc_bawa[1];
        $this->d['p'] = "form";
        $this->load->view("template_utama", $this->d);
    }

    public function import_nilai() {
        //queri init
        $id_guru_mapel = $this->input->post('id_guru_mapel');
        $id_kelas = $this->input->post('id_kelas');

        $detil_mp = $this->db->query("SELECT 
                                        a.*, b.nama nmmapel, c.nama nmkelas, c.tingkat tingkat
                                        FROM t_guru_mapel a
                                        INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                        INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                        WHERE a.id  = '$id_guru_mapel'")->row_array();
        $list_kd = $this->db->query("SELECT * FROM t_mapel_kd 
                                    WHERE id_mapel = '".$detil_mp['id_mapel']."'
                                    AND tingkat = '".$detil_mp['tingkat']."'
                                    AND jenis = 'P'
                                    AND semester = '".$this->d['semester']."'");
        $d_list_kd = $list_kd->result_array();
        $j_list_kd = $list_kd->num_rows();

        $q_siswa_kelas = $this->db->query("SELECT a.id_siswa FROM t_kelas_siswa a WHERE a.id_kelas = '".$id_kelas."' AND a.ta = '".$this->d['tahun']."'");
        $d_list_siswa = $q_siswa_kelas->result_array();        
        $j_list_siswa = $q_siswa_kelas->num_rows();        


        
        $idx_kolom_mulai = 2;
        $idx_kolom_selesai = ($idx_kolom_mulai + ((2*$j_list_kd) + 2)) - 1;
        $idx_baris_mulai = 8;
        $idx_baris_selesai = $idx_baris_mulai + $j_list_siswa;

        $idx_kolom_hide = $idx_kolom_mulai + $j_list_kd;
        //echo $idx_kolom_hide;


        $target_file = './upload/temp/';
        move_uploaded_file($_FILES["import_excel"]["tmp_name"], $target_file.$_FILES['import_excel']['name']);

        $file   = explode('.',$_FILES['import_excel']['name']);
        $length = count($file);

        if($file[$length -1] == 'xlsx' || $file[$length -1] == 'xls') {
            //jagain barangkali uploadnya selain file excel
            $tmp    = './upload/temp/'.$_FILES['import_excel']['name'];
            //Baca dari tmp folder jadi file ga perlu jadi sampah di server :-p
            
            $this->load->library('excel');//Load library excelnya
            $read   = PHPExcel_IOFactory::createReaderForFile($tmp);
            $read->setReadDataOnly(true);
            $excel  = $read->load($tmp);

            //echo $tmp;
    
            $_sheet = $excel->setActiveSheetIndexByName('Worksheet');//Kunci sheetnye biar kagak lepas :-p
            

            $x_id_mapel = $_sheet->getCell('B4')->getCalculatedValue();
            $x_id_guru = $_sheet->getCell('B5')->getCalculatedValue();
            $x_id_kelas = $_sheet->getCell('B6')->getCalculatedValue();

            //echo $x_id_mapel."/".$detil_mp['id_mapel']."-".$x_id_guru."/".$detil_mp['id_guru']."-".$x_id_kelas."/".$detil_mp['id_kelas'];

            if ($x_id_mapel != $detil_mp['id_mapel'] || $x_id_guru != $detil_mp['id_guru'] || $x_id_kelas != $detil_mp['id_kelas']) {
                echo "File Excel SALAH";
                exit;
            }

            $data = array();

            //ambil id_siwa mumet
            
            //var tetap
            $tasm = $this->d['tasm'];
            $id_guru_mapel = $id_guru_mapel;
            $id_mapel = $detil_mp['id_mapel'];

            for ($b = $idx_baris_mulai; $b < $idx_baris_selesai; $b++) {
                $idx_klm = $j_list_kd + 6;
                $va_xy_id_siswa = $_sheet->getCell($this->kolom_xl[$idx_klm].$b)->getCalculatedValue();
                $id_siswa = $va_xy_id_siswa;

                //ngitung mulai sik uas
                $idx_mulai_uts = $idx_kolom_mulai+($j_list_kd);

                $xy_nilai_uts = $_sheet->getCell($this->kolom_xl[($idx_mulai_uts)].$b)->getCalculatedValue();
                $xy_nilai_uas = $_sheet->getCell($this->kolom_xl[($idx_mulai_uts+1)].$b)->getCalculatedValue();
                $xy_nilai_catatan = $_sheet->getCell($this->kolom_xl[($idx_mulai_uts+3)].$b)->getCalculatedValue();
                $xy_nilai_catatan_mid = $_sheet->getCell($this->kolom_xl[($idx_mulai_uts+2)].$b)->getCalculatedValue();
                $data[] = array("tasm"=>$tasm, "jenis"=>"t", "id_guru_mapel"=>$id_guru_mapel, "id_mapel_kd"=>$id_mapel, "id_siswa"=>$id_siswa, "nilai"=>$xy_nilai_uts, "catatan"=>'', "catatan_mid"=>'');
                $data[] = array("tasm"=>$tasm, "jenis"=>"a", "id_guru_mapel"=>$id_guru_mapel, "id_mapel_kd"=>$id_mapel, "id_siswa"=>$id_siswa, "nilai"=>$xy_nilai_uas, "catatan"=>'', "catatan_mid"=>'');
                $data[] = array("tasm"=>$tasm, "jenis"=>"c", "id_guru_mapel"=>$id_guru_mapel, "id_mapel_kd"=>$id_mapel, "id_siswa"=>$id_siswa, "nilai"=>'0', "catatan"=>$xy_nilai_catatan, "catatan_mid"=>$xy_nilai_catatan_mid);
                
                //nilai kd
                $tmb = $j_list_kd + 5;

                for ($k = $idx_kolom_mulai; $k < ($idx_kolom_hide); $k++) {
                    $nilai = $_sheet->getCell($this->kolom_xl[$k].$b)->getCalculatedValue();
                    $hide = $_sheet->getCell($this->kolom_xl[($k+$tmb)].$b)->getCalculatedValue();
                    
                    $pc_hide = explode("-", $hide);
                    $id_mapel_kd = !empty($pc_hide[1]) ? $pc_hide[1] : 0;
                    
                    //echo $pc_hide[1];
                    /*
                    echo "Id_Siswa : ".$id_siswa.", ";
                    echo "NIlai : ".$nilai.", ";
                    echo "Hide : ".$id_mapel_kd;
                    */

                    $data[] = array("tasm"=>$tasm, "jenis"=>"h", "id_guru_mapel"=>$id_guru_mapel, "id_mapel_kd"=>$id_mapel_kd, "id_siswa"=>$id_siswa, "nilai"=>$nilai, "catatan"=>'', "catatan_mid"=>'');
                    //echo "/";
                } 
                //echo "<br>";
            }

            //exit;

            $strq = "REPLACE INTO t_nilai (tasm, jenis, id_guru_mapel, id_mapel_kd, nilai, catatan, catatan_mid, id_siswa) VALUES ";
            $arr_perdata = array();
            foreach ($data as $d) {
                $arr_perdata[] = "(
                    '".$d['tasm']."',
                    '".$d['jenis']."',
                    '".$d['id_guru_mapel']."',
                    '".$d['id_mapel_kd']."',
                    '".$d['nilai']."',
                    ".$this->db->escape($d['catatan']).",
                    ".$this->db->escape($d['catatan_mid']).",
                    '".$d['id_siswa']."'
                )";
            }
            $strq .= implode(",", $arr_perdata).";";

            //j($arr_perdata);
            //exit;
            
            //echo $strq;
            //exit;

            $this->db->query($strq);

            //echo $strq;
            //exit;

            @unlink('./upload/temp/'.$tmp);
            
            $this->session->set_flashdata('k', '<div class="alert alert-success">Nilai berhasil diupload..</div>');
            redirect('n_pengetahuan/index/'.$id_guru_mapel);

        } else {
            exit('Buka File Excel...');//pesan error tipe file tidak tepat
        }
        redirect('n_pengetahuan/index/'.$id_guru_mapel);
    }
    public function import_from_agilix_batch($limit = 25, $offset = 0) {
        header('Content-Type: application/json');
    
        $tasm = 20251;
    
        // ambil data batch dari t_guru_mapel
        $rows = $this->db->query("
            SELECT id_mapel, id_kelas 
            FROM t_guru_mapel 
            WHERE tasm = ?
            LIMIT ? OFFSET ?
        ", [$tasm, (int)$limit, (int)$offset])->result();
    
        if (empty($rows)) {
            echo json_encode([
                'status' => 'done',
                'data'   => "No more records at offset {$offset}"
            ]);
            return;
        }
    
        $results = [];
    
        foreach ($rows as $row) {
            $bawa = $row->id_mapel . '-' . $row->id_kelas;
    
            ob_start();
            $this->import_from_agilix($bawa);
            $response = ob_get_clean();
    
            $results[] = [
                'mapel'  => $row->id_mapel,
                'kelas'  => $row->id_kelas,
                'result' => json_decode($response, true) ?? $response
            ];
        }
    
        echo json_encode([
            'status'  => 'sukses',
            'offset'  => (int)$offset,
            'next'    => (int)$offset + (int)$limit,
            'count'   => count($results),
            'results' => $results
        ]);
    }


public function import_from_agilix($bawa){
        header('Content-Type: application/json');
        $this->load->helper('buzz');
        $pc_bawa = explode("-", $bawa);
        $detil_guru = $this->db->query("SELECT 
                                         a.id, a.id_guru, b.nama nmmapel, c.nama nmkelas, d.nama nmguru
                                         FROM t_guru_mapel a
                                         INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                         INNER JOIN m_kelas c ON a.id_kelas = c.id
                                         INNER JOIN m_guru d ON a.id_guru = d.id 
                                         WHERE b.id = ".$pc_bawa[0]." AND c.id = ".$pc_bawa[1]."
                                         AND a.tasm = '".$this->d['tasm']."'")->row_array();
        // 3. CONFIG
        $id_guru_mapel = $this->session->userdata('id_guru_mapel');
        $tasm = $this->d['tasm'];
        $jenis = 'h';
        $semester = $this->d['semester'];
        $detil_mp = $this->db->query("SELECT 
                                        a.*, b.nama nmmapel, c.nama nmkelas, c.tingkat tingkat
                                        FROM t_guru_mapel a
                                        INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                        INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                        WHERE a.id  = '$id_guru_mapel'")->row_array();
        $tingkat = $detil_mp['tingkat'];
        $id_mapel = $detil_mp['id_mapel'];
        $name_mapel = $detil_mp['nmmapel']." - ".$detil_mp['nmkelas']." - ".$this->d['tahun'];
        $list_kd = $this->db->query("SELECT * FROM t_mapel_kd 
                                    WHERE id_mapel = '".$detil_mp['id_mapel']."'
                                    AND tingkat = '".$detil_mp['tingkat']."'
                                    AND jenis = 'P'
                                    AND semester = '".$this->d['semester']."'");
        $q_kd_guru_ini = $this->db->query("SELECT a.* 
                                    FROM t_mapel_kd a
                                    LEFT JOIN m_kelas b ON a.tingkat = b.tingkat
                                    WHERE a.id_mapel = '".$pc_bawa[0]."'
                                    AND b.id = ".$pc_bawa[1]." 
                                    AND a.semester = '".$this->d['semester']."'
                                    AND a.jenis = 'P'")->result_array();
        $q_siswa_kelas = $this->db->query("SELECT a.id_siswa, b.nama nmsiswa FROM t_kelas_siswa a LEFT JOIN m_siswa b ON a.id_siswa = b.id WHERE a.id_kelas = '".$pc_bawa[1]."' AND a.ta = '".$this->d['tahun']."'")->result_array();

        $d_kd = array();
        if (!empty($q_kd_guru_ini)) {
            foreach ($q_kd_guru_ini as $v) {
                $idx = $v['id'];
                $d_kd[$idx]['kode'] = $v['no_kd'];
            }
        }
        if (!empty($d_kd)) {
            // 1. LOGIN TO GET TOKEN
        // === GET TOKEN (cached 3 menit) ===
        $token = get_buzz_token();
        if (!$token) {
            // file_put_contents($logFile, "❌ Failed to get token\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => 'Failed to get Buzz token']);
            return;
        }
        
        // 2. FETCH COURSE LIST TO GET ENTITY ID
        $dateNow = gmdate('Y-m-d\TH:i:s.000\Z'); // Current UTC time in ISO format
        $coursesUrl = "http://103.247.217.153:8000/listcourses?_token={$token}&limit=500&query=%2Fstartdate%3C%27{$dateNow}%27and%2Fenddate%3E%27{$dateNow}%27&domainid=175758880";
        
        $ch = curl_init($coursesUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json',
    'Accept: application/json']);
        $coursesResponse = curl_exec($ch);
        curl_close($ch);
        
        $xmlCourses = @simplexml_load_string($coursesResponse);
        $courseId = null;
    
        if ($xmlCourses && isset($xmlCourses->courses->course)) {
            foreach ($xmlCourses->courses->course as $course) {
                if (trim((string)$course['title']) === trim($name_mapel)) {
                    $courseId = (string)$course['id'];
                    break;
                }
            }
        }
    
        if (!$courseId) {
            echo json_encode(['status' => 'error', 'message' => "Course '{$name_mapel}' tidak ditemukan di Buzz."]);
            return;
        }

        // 3. FETCH GRADEBOOK JSON DATA
        $gradebookUrl = "http://103.247.217.153:8000/getentitygradebook2?_token={$token}&itemid=*&allstatus=false&daysactivepastend=14&select=user.data(personalneedspreferences%2Ciep%2Ciep2)&entityid={$courseId}";

        $ch = curl_init($gradebookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json',
    'Accept: application/json']);
        $gradebookResponse = curl_exec($ch);
        curl_close($ch);
        $xmlGradebook = @simplexml_load_string($gradebookResponse);
        if (!$xmlGradebook || !isset($xmlGradebook->enrollments->enrollment)) {
            echo json_encode(['status' => 'error', 'message' => "Gagal mengambil data gradebook XML."]);
            return;
        }

        // 4. FETCH MANIFEST TO GET KNOWLEDGE FOLDER ID
        $manifestUrl = "http://103.247.217.153:8000/getmanifest?_token={$token}&entityid={$courseId}";
        $ch = curl_init($manifestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $manifestResponse = curl_exec($ch);
        curl_close($ch);

        $xmlManifest = @simplexml_load_string($manifestResponse);
        $knowledgeFolderId = null;
        $targetFolderName = ($semester == 2) ? 'Knowledge 2' : 'Knowledge';

        if ($xmlManifest) {
            $eReportItem = null;
            $stack = [];
            if (isset($xmlManifest->manifest->item)) {
                foreach ($xmlManifest->manifest->item as $it) { $stack[] = $it; }
            }
            while (!empty($stack)) {
                $item = array_pop($stack);
                if ((string)$item->data->title === "E-report Score") {
                    $eReportItem = $item;
                    break;
                }
                if (isset($item->item)) {
                    foreach ($item->item as $child) { $stack[] = $child; }
                }
            }

            if ($eReportItem) {
                $stack = [];
                if (isset($eReportItem->item)) {
                    foreach ($eReportItem->item as $it) { $stack[] = $it; }
                }
                while (!empty($stack)) {
                    $item = array_pop($stack);
                    if ((string)$item->data->title === $targetFolderName) {
                        $knowledgeFolderId = (string)$item['id'];
                        break;
                    }
                    if (isset($item->item)) {
                        foreach ($item->item as $child) { $stack[] = $child; }
                    }
                }
            }
        }

// 6. PROSES IMPORT NILAI
    $successCount = 0;
    foreach ($xmlGradebook->enrollments->enrollment as $enrollment) {
        $firstname = trim((string)$enrollment->user['firstname']);
        $lastname  = trim((string)$enrollment->user['lastname']);
        $fullname  = trim($firstname . ' ' . $lastname);

        // Cari ID Siswa di DB Lokal
        $siswa = $this->db->query("SELECT id FROM m_siswa WHERE nama = ? ORDER BY id DESC LIMIT 1", [$fullname])->row();
        if (!$siswa) continue;

        $id_siswa = $siswa->id;

        $midtermScore = 0;
        $finalScore = 0;

        // Ambil Nilai per Item
        if (isset($enrollment->grades->items->item)) {
            foreach ($enrollment->grades->items->item as $item) {
                $gradable = (string)$item['gradable'];
                $achieved = (string)$item['achieved'];
                $title    = (string)$item['title'];
                $parentId = (string)$item['parentid'];

                if ($knowledgeFolderId && $parentId !== $knowledgeFolderId) {
                    continue;
                }

                if ($gradable === 'true') {
                    $nilai = ($achieved !== '') ? floatval($achieved) : 0;

                    if ($title === 'Midterm Score') {
                        $midtermScore = $nilai;
                    } elseif ($title === 'Final Score') {
                        $finalScore = $nilai;
                    } elseif ($achieved !== '') {
                        // Cari KD berdasarkan Title di tabel t_mapel_kd
                        $kd = $this->db->query("SELECT id FROM t_mapel_kd WHERE no_kd = ? AND id_mapel = ? AND tingkat = ? AND semester = ? AND jenis = 'P'", 
                                               [$title, $id_mapel, $tingkat, $semester])->row();
                        
                        if ($kd) {
                            $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai)
                                              VALUES (?, ?, 'h', ?, ?, ?)", [$id_guru_mapel, $tasm, $kd->id, $id_siswa, $nilai]);
                        }
                    }
                    $successCount++;
                }
            }
        }

        // Simpan Midterm & Final (0 jika tidak ada)
        $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai)
                          VALUES (?, ?, 't', ?, ?, ?)", [$id_guru_mapel, $tasm, $id_mapel, $id_siswa, $midtermScore]);
        $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai)
                          VALUES (?, ?, 'a', ?, ?, ?)", [$id_guru_mapel, $tasm, $id_mapel, $id_siswa, $finalScore]);
    }

    echo json_encode([
        'status' => 'sukses',
        'data' => "Berhasil mengimpor nilai dari Buzz LMS (XML)."
    ]);
        } else {
                    exit("KD masih kosong. Silakan diisi");
                }
        
    }
public function webhook_grade_created()
{
    header('Content-Type: application/json');
    $input = file_get_contents("php://input");
    $json = json_decode($input, true);

    $enrollmentId = $json['data']['enrollmentId'] ?? null;
    $itemId       = $json['data']['itemId'] ?? null;
    $achieved     = $json['data']['newGrade']['achieved'] ?? 0;

    // 1. GET TOKEN
    $token = get_buzz_token();

    // 2. CALL API BUZZ
    $url = "http://103.247.217.153:8000/getenrollmentgradebook2?_token={$token}&itemid={$itemId}&enrollmentid={$enrollmentId}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    // 3. PROSES XML (Direct Parsing)
    // Gunakan simplexml untuk efisiensi memory
    $xml = @simplexml_load_string($response);
    
    if ($xml === false || !isset($xml->enrollment)) {
        // Log jika gagal agar bisa di debug
        file_put_contents(FCPATH . 'application/logs/webhook_error.log', date('Y-m-d H:i:s') . " | XML Fail | " . substr($response, 0, 100) . "\n", FILE_APPEND);
        return;
    }

    // Parsing data dari struktur XML Buzz
    // Format XML biasanya: <response> <enrollment> <entity title="...">...
    $enrollment = $xml->enrollment;
    $entityTitle = (string)$enrollment->entity['title'];
    
    $parts = explode(" - ", $entityTitle);
    $nmmapel = trim($parts[0] ?? '');
    $nmkelas = trim($parts[1] ?? '');
    
    $firstname = (string)$enrollment->user['firstname'];
    $lastname  = (string)$enrollment->user['lastname'];
    $fullname  = trim($firstname . ' ' . $lastname);

    // 4. DATABASE LOGIC
    $siswa = $this->db->query("SELECT id FROM m_siswa WHERE nama = ? ORDER BY id DESC LIMIT 1", [$fullname])->row();
    
    $tasm = $this->d['tasm'];
    $detil_mp = $this->db->query("SELECT a.id, a.id_mapel, c.tingkat 
                                  FROM t_guru_mapel a
                                  INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                  INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                  WHERE b.nama = ? AND c.nama = ? AND a.tasm = ?", 
                                  [$nmmapel, $nmkelas, $tasm])->row_array();

    if ($siswa && $detil_mp) {
        $itemTitle = '';
        
        // Cari Title di dalam list items (XML structure)
        // Biasanya: <grades><items><item itemid="..." title="..." />
        if (isset($enrollment->grades->items->item)) {
            foreach ($enrollment->grades->items->item as $it) {
                if ((string)$it['itemid'] === $itemId) {
                    $itemTitle = (string)$it['title'];
                    break;
                }
            }
        }

        // Simpan Nilai
        $this->save_score_from_xml($itemTitle, $detil_mp, $siswa->id, $achieved);
    }

    echo json_encode(['status' => 'success']);
}

/**
 * Fungsi simpan nilai yang mendukung logika XML
 */
private function save_score_from_xml($title, $mp, $siswa_id, $nilai) {
    $tasm = $this->d['tasm'];
    $sem  = $this->d['semester'];

    if ($title === 'Midterm Score') {
        $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai) 
                          VALUES (?, ?, 't', ?, ?, ?)", [$mp['id'], $tasm, $mp['id_mapel'], $siswa_id, $nilai]);
    } elseif ($title === 'Final Score') {
        $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai) 
                          VALUES (?, ?, 'a', ?, ?, ?)", [$mp['id'], $tasm, $mp['id_mapel'], $siswa_id, $nilai]);
    } else {
        // Cari ID KD berdasarkan title yang didapat dari XML
        $kd = $this->db->query("SELECT id FROM t_mapel_kd 
                                WHERE no_kd = ? AND id_mapel = ? AND tingkat = ? AND semester = ? AND jenis = 'P'", 
                                [$title, $mp['id_mapel'], $mp['tingkat'], $sem])->row();
        if ($kd) {
            $this->db->query("REPLACE INTO t_nilai (id_guru_mapel, tasm, jenis, id_mapel_kd, id_siswa, nilai) 
                              VALUES (?, ?, 'h', ?, ?, ?)", [$mp['id'], $tasm, $kd->id, $siswa_id, $nilai]);
        }
    }
}
  
public function create_gradebook_items_in_buzz($bawa) {
    header('Content-Type: application/json');
    $pc_bawa = explode("-", $bawa);
    $this->load->helper('buzz');

    // 1. Get mapel & kelas dari DB Lokal
    $detil_mp = $this->db->query("SELECT 
        a.*, b.nama nmmapel, c.nama nmkelas, c.tingkat tingkat
        FROM t_guru_mapel a
        INNER JOIN m_mapel b ON a.id_mapel = b.id 
        INNER JOIN m_kelas c ON a.id_kelas = c.id 
        WHERE b.id = ? AND c.id = ? AND a.tasm = ?", 
        [$pc_bawa[0], $pc_bawa[1], $this->d['tasm']]
    )->row_array();

    if (!$detil_mp) {
        echo json_encode(['status' => 'error', 'msg' => 'Mapping tidak ditemukan.']);
        return;
    }

    $tingkat = $detil_mp['tingkat'];
    $id_mapel = $detil_mp['id_mapel'];
    $name_mapel = $detil_mp['nmmapel'] . " - " . $detil_mp['nmkelas'] . " - " . $this->d['tahun'];

    // 2. Get KD dari DB Lokal
    $kd_list = $this->db->query("SELECT no_kd FROM t_mapel_kd WHERE id_mapel = ? AND tingkat = ? AND semester = ? AND jenis = 'P'", 
        [$id_mapel, $tingkat, $this->d['semester']]
    )->result_array();

    // 3. GET TOKEN
    $token = get_buzz_token();
    if (!$token) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to get Buzz token']);
        return;
    }

    // 4. Get course (XML Version)
    $dateNow = gmdate('Y-m-d\TH:i:s.000\Z');
    $courseUrl = "http://103.247.217.153:8000/listcourses?_token={$token}&limit=500&query=%2Fstartdate%3C%27{$dateNow}%27and%2Fenddate%3E%27{$dateNow}%27&domainid=175758880";
    
    $ch = curl_init($courseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Kita tidak mengirim header Accept: application/json agar mendapat XML default
    $courseResponse = curl_exec($ch);
    curl_close($ch);

    $xmlCourse = @simplexml_load_string($courseResponse);
    $courseId = null;

    if ($xmlCourse && isset($xmlCourse->courses->course)) {
        foreach ($xmlCourse->courses->course as $c) {
            if (trim((string)$c['title']) === trim($name_mapel)) {
                $courseId = (string)$c['id'];
                break;
            }
        }
    }

    if (!$courseId) {
        echo json_encode(['status' => 'error', 'data' => "Course '{$name_mapel}' tidak ditemukan."]);
        return;
    }

    // 5. Get manifest (XML Version)
    $manifestUrl = "http://103.247.217.153:8000/getmanifest?_token={$token}&entityid={$courseId}";
    $ch = curl_init($manifestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $manifestResponse = curl_exec($ch);
    curl_close($ch);

    $xmlManifest = @simplexml_load_string($manifestResponse);
    if (!$xmlManifest) {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal membaca manifest XML']);
        return;
    }

    // Rekursif Search di XML Manifest untuk mencari Folder
    $topicId = null;
    $knowledgeFolderId = null;
    $titles = 'Knowledge ' . $this->d['semester'];

    // Kita pakai stack untuk iterasi XML nodes
    $stack = [];
    if (isset($xmlManifest->manifest->item)) {
        foreach ($xmlManifest->manifest->item as $it) { $stack[] = $it; }
    }

    while (!empty($stack)) {
        $item = array_pop($stack);
        $currTitle = (string)$item->data->title;
        $currId = (string)$item['id'];
        $currParent = (string)$item->data->parent;

        // Cari Topic Utama
        if ($currTitle === "E-report Score") {
            $topicId = $currId;
        }

        // Cari Folder Knowledge
        if ($currTitle === $titles) {
            $knowledgeFolderId = $currId;
        }

        // Masukkan children ke stack jika ada
        if (isset($item->item)) {
            foreach ($item->item as $child) { $stack[] = $child; }
        }
    }

    // 6. Logika Create Folder (Jika tidak ada)
    if (!$topicId) {
        $topicId = "e-report-" . time();
        $this->buzz_put_xml($token, "<requests><item entityid='{$courseId}' itemid='{$topicId}'><data><title>E-report Score</title><parent>DEFAULT</parent><sequence>zb</sequence></data></item></requests>");
    }

    if (!$knowledgeFolderId) {
        $knowledgeFolderId = 'knowledge-' . time();
        $this->buzz_put_xml($token, "<requests><item entityid='{$courseId}' itemid='{$knowledgeFolderId}'><data><title>{$titles}</title><parent>{$topicId}</parent><sequence>a</sequence></data></item></requests>");
        
        // Buat Midterm & Final otomatis
        $fixed = ['Midterm Score', 'Final Score'];
        foreach ($fixed as $f) {
            $fId = strtolower(str_replace(' ', '-', $f)) . '-' . time();
            $this->create_assignment_xml($token, $courseId, $fId, $f, $knowledgeFolderId);
        }
    }

    // 7. Sinkronisasi KD (Cari yang belum ada)
    $existingTitles = [];
    // Reset stack untuk scan ulang judul di bawah Knowledge Folder
    $stack = [];
    if (isset($xmlManifest->manifest->item)) {
        foreach ($xmlManifest->manifest->item as $it) { $stack[] = $it; }
    }
    while (!empty($stack)) {
        $item = array_pop($stack);
        if ((string)$item->data->parent === $knowledgeFolderId) {
            $existingTitles[] = (string)$item->data->title;
        }
        if (isset($item->item)) {
            foreach ($item->item as $child) { $stack[] = $child; }
        }
    }

    foreach ($kd_list as $kd) {
        if (in_array($kd['no_kd'], $existingTitles)) continue;
        
        $uId = 'kd-' . strtolower(str_replace('.', '-', $kd['no_kd'])) . '-' . time();
        $this->create_assignment_xml($token, $courseId, $uId, $kd['no_kd'], $knowledgeFolderId);
    }

    echo json_encode(['status' => 'sukses', 'data' => "Sinkronisasi XML Berhasil."]);
}

/**
 * Helper: Create Assignment via XML
 */
private function create_assignment_xml($token, $courseId, $itemId, $title, $parentId) {
    $xml = "
    <requests>
        <item entityid='{$courseId}' itemid='{$itemId}'>
            <data>
                <type>Assessment</type>
                <category>1</category>
                <gradeentry2>2</gradeentry2>
                <href>Templates/Data/0677fdf9bee34c4191cb57e773fcd30c/index.html</href>
                <title>".htmlspecialchars($title)."</title>
                <parent>{$parentId}</parent>
                <gradable>true</gradable>
                <weight>100</weight>
            </data>
        </item>
    </requests>";
    return $this->buzz_put_xml($token, $xml);
}

/**
 * Helper: Put Items XML
 */
private function buzz_put_xml($token, $xml) {
    $ch = curl_init("http://103.247.217.153:8000/putitems?_token={$token}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
    public function ambil_siswa($kelas) {
        $id_kd = $this->uri->segment(4);
        $jenis = $this->uri->segment(5);
        $id_guru_mapel = $this->session->userdata('id_guru_mapel');

        $list_data = array();
        if ($jenis == "h") {
            $ambil_nilai = $this->db->query("SELECT
                                        b.id ids, 
                                        b.nama nama, 
                                        IFNULL(a.nilai,0) nilai
                                        FROM m_siswa b 
                                        INNER JOIN t_nilai a ON a.id_siswa = b.id
                                        INNER JOIN t_guru_mapel c ON a.id_guru_mapel = c.id
                                        INNER JOIN t_mapel_kd d ON a.id_mapel_kd = d.id 
                                        INNER JOIN m_guru e ON c.id_guru = e.id 
                                        INNER JOIN m_mapel f ON c.id_mapel = f.id
                                        INNER JOIN t_kelas_siswa g ON a.id_siswa = g.id_siswa
                                        INNER JOIN m_kelas h ON g.id_kelas = h.id
                                        WHERE h.id = $kelas AND a.id_mapel_kd = $id_kd
                                        AND a.jenis = '$jenis' 
                                        AND a.tasm = '".$this->d['tasm']."'
                                        ORDER BY b.nama ASC")->result_array();
            //echo $this->db->last_query();
            //exit;
            
            $ambil_nilai = $this->db->query("SELECT 
                        m_siswa.id ids, m_siswa.nama, IFNULL(t_nilai.nilai,0) AS nilai,
                        t_kelas_siswa.id_kelas
                        FROM m_siswa
                        LEFT JOIN t_nilai ON m_siswa.id = t_nilai.id_siswa
                        AND t_nilai.jenis = 'h'
                        AND t_nilai.id_guru_mapel = '$id_guru_mapel' 
                        AND t_nilai.id_mapel_kd = '$id_kd' 
                        AND t_nilai.tasm = '".$this->d['tasm']."'
                        LEFT JOIN t_kelas_siswa ON m_siswa.id = t_kelas_siswa.id_siswa
                        WHERE (t_nilai.jenis = 'h' OR t_nilai.jenis IS NULL)
                        AND t_kelas_siswa.id_kelas = $kelas
                        AND t_kelas_siswa.ta = '".$this->d['tahun']."' ORDER BY m_siswa.nama ASC")->result_array();

            //echo $this->db->last_query();
            //exit;
        } else if($jenis == "c") {
            /*
            $ambil_nilai = $this->db->query("SELECT
                                        b.id ids, 
                                        b.nama nama, 
                                        IFNULL(a.nilai,0) nilai
                                        FROM m_siswa b 
                                        INNER JOIN t_nilai a ON a.id_siswa = b.id
                                        INNER JOIN t_guru_mapel c ON a.id_guru_mapel = c.id
                                        INNER JOIN m_guru e ON c.id_guru = e.id 
                                        INNER JOIN m_mapel f ON c.id_mapel = f.id
                                        INNER JOIN t_kelas_siswa g ON a.id_siswa = g.id_siswa
                                        INNER JOIN m_kelas h ON g.id_kelas = h.id
                                        WHERE h.id = $kelas AND c.id_mapel = $id_kd
                                        AND a.jenis = '$jenis' 
                                        AND a.tasm = '".$this->d['tasm']."'
                                        ORDER BY b.nama ASC")->result_array();
            */
            $ambil_nilai = $this->db->query("SELECT 
            m_siswa.id AS ids,
            m_siswa.nama,
            COALESCE(t_nilai.catatan, '') AS nilai,
            COALESCE(t_nilai.catatan_mid, '') AS nilai_mid,
            t_kelas_siswa.id_kelas
        FROM m_siswa
        LEFT JOIN t_nilai ON m_siswa.id = t_nilai.id_siswa
            AND t_nilai.jenis = 'c'
            AND t_nilai.id_guru_mapel = '$id_guru_mapel'
            AND t_nilai.tasm = '".$this->d['tasm']."'
        LEFT JOIN t_kelas_siswa ON m_siswa.id = t_kelas_siswa.id_siswa
        WHERE (t_nilai.jenis = 'c' OR t_nilai.jenis IS NULL)
            AND t_kelas_siswa.id_kelas = $kelas
            AND t_kelas_siswa.ta = '".$this->d['tahun']."' ORDER BY m_siswa.nama ASC")->result_array();
            //j($ambil_nilai);
            //exit;
            //echo $this->db->last_query();
            //exit;
        } else {
            /*
            $ambil_nilai = $this->db->query("SELECT
                                        b.id ids, 
                                        b.nama nama, 
                                        IFNULL(a.nilai,0) nilai
                                        FROM m_siswa b 
                                        INNER JOIN t_nilai a ON a.id_siswa = b.id
                                        INNER JOIN t_guru_mapel c ON a.id_guru_mapel = c.id
                                        INNER JOIN m_guru e ON c.id_guru = e.id 
                                        INNER JOIN m_mapel f ON c.id_mapel = f.id
                                        INNER JOIN t_kelas_siswa g ON a.id_siswa = g.id_siswa
                                        INNER JOIN m_kelas h ON g.id_kelas = h.id
                                        WHERE h.id = $kelas AND c.id_mapel = $id_kd
                                        AND a.jenis = '$jenis' 
                                        AND a.tasm = '".$this->d['tasm']."'
                                        ORDER BY b.nama ASC")->result_array();
            */
            $ambil_nilai = $this->db->query("SELECT 
                        m_siswa.id ids, m_siswa.nama, IFNULL(t_nilai.nilai,0) AS nilai,
                        t_kelas_siswa.id_kelas
                        FROM m_siswa
                        LEFT JOIN t_nilai ON m_siswa.id = t_nilai.id_siswa
                        AND t_nilai.jenis = '$jenis'
                        AND t_nilai.id_guru_mapel = '$id_guru_mapel' 
                        AND t_nilai.id_mapel_kd = '$id_kd' 
                        AND t_nilai.tasm = '".$this->d['tasm']."'
                        LEFT JOIN t_kelas_siswa ON m_siswa.id = t_kelas_siswa.id_siswa
                        WHERE (t_nilai.jenis = '$jenis' OR t_nilai.jenis IS NULL)
                        AND t_kelas_siswa.id_kelas = $kelas
                        AND t_kelas_siswa.ta = '".$this->d['tahun']."' ORDER BY m_siswa.nama ASC")->result_array();
            //j($ambil_nilai);
            //exit;
            //echo $this->db->last_query();
            //exit;
        }
        
        //echo $this->db->last_query();
        //exit;
        
        if (empty($ambil_nilai)) {
            $list_data = $this->db->query("SELECT 
                                        b.id ids, b.nama, 0 nilai
                                        FROM t_kelas_siswa a 
                                        INNER JOIN m_siswa b ON a.id_siswa = b.id
                                        WHERE a.id_kelas = $kelas 
                                        AND a.ta = '".$this->d['tahun']."'
                                        ORDER BY b.nama ASC")->result_array();
            $d['sik_endi'] = "belum ada";
        } else {
            $list_data = $ambil_nilai;
            $d['sik_endi'] = "sudah ada";
        }

        //echo $this->db->last_query();

        $d['id_guru_mapel'] = $id_guru_mapel;
        $d['status'] = "ok";
        $d['data'] = $list_data;
        
        j($d);
    }
    public function simpan_nilai() {
        $p = $this->input->post();
        $jumlah_sudah = 0;
        $i = 0;
        
        $queri = array();
        
        foreach ($p['nilai'] as $s) {
            if ($p['jenis'] == 'c'){
                $cek = $this->db->query("SELECT id FROM t_nilai WHERE id_guru_mapel = '".$p['id_guru_mapel']."' AND id_mapel_kd = '".$p['id_mapel_kd']."' AND id_siswa = '".$p['id_siswa'][$i]."' AND jenis = '".$p['jenis']."' AND tasm = '".$this->d['tasm']."'")->num_rows();
                //$queri[] = $this->db->last_query();
                //exit;
                if ($cek > 0) {
                    $jumlah_sudah ++;
                    $this->db->query("UPDATE t_nilai SET catatan = '".addslashes($s)."', catatan_mid = '".addslashes($p['nilai_mid'][$i])."' WHERE id_guru_mapel = '".$p['id_guru_mapel']."' AND id_mapel_kd = '".$p['id_mapel_kd']."' AND id_siswa = '".$p['id_siswa'][$i]."' AND jenis = '".$p['jenis']."'");
                    //$queri[] = "update";
                } else {
                    $this->db->query("INSERT INTO t_nilai (tasm,jenis, id_guru_mapel, id_mapel_kd, id_siswa, catatan, catatan_mid) VALUES ('".$this->d['tasm']."', '".$p['jenis']."', '".$p['id_guru_mapel']."', '".$p['id_mapel_kd']."', '".$p['id_siswa'][$i]."', '".addslashes($s)."', '".addslashes($p['nilai_mid'][$i])."')");
                    //$queri[] = "add";
                }
            }else{
                $cek = $this->db->query("SELECT id FROM t_nilai WHERE id_guru_mapel = '".$p['id_guru_mapel']."' AND id_mapel_kd = '".$p['id_mapel_kd']."' AND id_siswa = '".$p['id_siswa'][$i]."' AND jenis = '".$p['jenis']."' AND tasm = '".$this->d['tasm']."'")->num_rows();
                //$queri[] = $this->db->last_query();
                //exit;
                if ($cek > 0) {
                    $jumlah_sudah ++;
                    $this->db->query("UPDATE t_nilai SET nilai = '$s' WHERE id_guru_mapel = '".$p['id_guru_mapel']."' AND id_mapel_kd = '".$p['id_mapel_kd']."' AND id_siswa = '".$p['id_siswa'][$i]."' AND jenis = '".$p['jenis']."'");
                    //$queri[] = "update";
                } else {
                    $this->db->query("INSERT INTO t_nilai (tasm,jenis, id_guru_mapel, id_mapel_kd, id_siswa, nilai) VALUES ('".$this->d['tasm']."', '".$p['jenis']."', '".$p['id_guru_mapel']."', '".$p['id_mapel_kd']."', '".$p['id_siswa'][$i]."', '".$s."')");
                    //$queri[] = "add";
                }
            }
            $i++;
        }
        
        $d['status'] = "ok";
        $d['data'] = $i."Data berhasil disimpan ";
        j($d);
    }
    public function hapus($id) {
        $this->db->query("DELETE FROM t_guru_mapel WHERE id = '$id'");
        $d['status'] = "ok";
        $d['data'] = "Data berhasil dihapus";
        
        j($d);
    }
    public function index($id) {

        $this->d['detil_mp'] = $this->db->query("SELECT 
                                        a.*, b.nama nmmapel,b.kd_singkat, c.nama nmkelas, c.tingkat tingkat
                                        FROM t_guru_mapel a
                                        INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                        INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                        WHERE a.id  = '$id'")->row_array();
        $this->d['list_kd'] = $this->db->query("SELECT * FROM t_mapel_kd 
                                    WHERE id_guru = '".$this->d['detil_mp']['id_guru']."'
                                    AND id_mapel = '".$this->d['detil_mp']['id_mapel']."'
                                    AND tingkat = '".$this->d['detil_mp']['tingkat']."'
                                    AND semester = '".$this->d['semester']."'
                                    AND jenis = 'P'")->result_array();
                                    
        //echo $this->db->last_query();
        //exit;
        $this->d['id_guru_mapel'] = $id;

        $this->session->set_userdata("id_guru_mapel", $id);


    	$this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

    public function list_kd($id) {
        $this->d['detil_mp'] = $this->db->query("SELECT 
                                        a.*, b.nama nmmapel, c.nama nmkelas, c.tingkat tingkat
                                        FROM t_guru_mapel a
                                        INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                        INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                        WHERE a.id  = '$id'")->row_array();
        $this->d['list_kd'] = $this->db->query("SELECT * FROM t_mapel_kd 
                                    WHERE id_mapel = '".$this->d['detil_mp']['id_mapel']."'
                                    AND tingkat = '".$this->d['detil_mp']['tingkat']."'
                                    AND semester = '".$this->d['semester']."'
                                    AND jenis = 'P'")->result_array();

        j($this->d['list_kd']);
        exit;
    }
}
