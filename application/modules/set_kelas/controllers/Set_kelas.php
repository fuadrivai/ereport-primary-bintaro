<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Set_kelas extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['url'] = "set_kelas";
        $this->d['idnya'] = "setkelas";
        $this->d['nama_form'] = "f_setkelas";

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($this->d['tasm'], 0, 4);

        //echo $this->d['ta'];
        //exit;
    }

    public function datatable() {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $d_total_row = $this->db->query("SELECT 
                                        a.id, b.nama nmkelas, c.nama nmsiswa
                                        FROM t_kelas_siswa a
                                        INNER JOIN m_kelas b ON a.id_kelas = b.id
                                        INNER JOIN m_siswa c ON a.id_siswa = c.id
                                        ORDER BY nmkelas ASC, nmsiswa ASC")->num_rows();
    
        $q_datanya = $this->db->query("SELECT 
                                    a.id, b.nama nmkelas, c.nama nmsiswa
                                    FROM t_kelas_siswa a
                                    INNER JOIN m_kelas b ON a.id_kelas = b.id
                                    INNER JOIN m_siswa c ON a.id_siswa = c.id
                                    WHERE c.nama LIKE '%".$search['value']."%' 
                                    ORDER BY nmkelas ASC, nmsiswa ASC LIMIT ".$start.", ".$length."")->result_array();
        $data = array();
        $no = ($start+1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[0] = $no++;
            $data_ok[1] = $d['nmkelas'];
            $data_ok[2] = $d['nmsiswa'];

            $data_ok[3] = '<a href="#" onclick="return hapus(\''.$d['id'].'\');" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i> Hapus</a> ';

            $data[] = $data_ok;
        }

        $json_data = array(
                    "draw" => $draw,
                    "iTotalRecords" => $d_total_row,
                    "iTotalDisplayRecords" => $d_total_row,
                    "data" => $data
                );
        j($json_data);
        exit;
    }

    public function edit($id) {
        $q = $this->db->query("SELECT id, nama FROM m_siswa a 
                                WHERE YEAR(a.diterima_tgl) > 2015 AND stat_data = 'A' AND a.id NOT IN 
                                (SELECT id_siswa FROM t_kelas_siswa WHERE ta = ".$this->d['ta'].") 
                                ORDER BY id ASC");

        
        
        $t = $this->db->query("SELECT ta from t_kelas_siswa where ta != '".$this->d['ta']."' order by ta desc limit 1  ")->row_array();

        // $q1 = $this->db->query("SELECT 
        //         a.ta, b.nama nmkelas, c.id, c.nama nmsiswa
        //         from t_kelas_siswa a 
        //         inner join m_kelas b on a.id_kelas = b.id
        //         inner join m_siswa c on a.id_siswa = c.id
        //         where c.stat_data = 'A' and a.ta= '".$t['ta']."' order by b.nama asc ");

        $r = $this->db->query("SELECT * FROM m_kelas ORDER BY tingkat ASC, nama ASC");

        $this->d['siswa_asal'] = $q->result_array();
        $this->d['kelas'] = $r->result_array();
        $this->d['p'] = "form";
        $this->load->view("template_utama", $this->d);
    }

    public function simpan() {
        $p = $this->input->post();
        $tahun = $this->d['tasm'];
        $teks_val = array();
        foreach ($p['siswa_pilih'] as $s) {
            $teks_val[] = "('".$p['kelas']."', '".$s."', '".$p['ta']."')";
            $queryabsen = $this->db->query("SELECT id_siswa,tasm FROM t_nilai_absensi WHERE id_siswa='".$s."' AND tasm='".$tahun."'")->num_rows();
            $querynaik = $this->db->query("SELECT id_siswa,ta FROM t_naikkelas WHERE id_siswa='".$s."' AND ta='".$tahun."'")->num_rows();
            $queryso = $this->db->query("SELECT tasm,id_siswa FROM t_nilai_sikap_so WHERE id_siswa='".$s."' AND tasm='".$tahun."'")->num_rows();
            $querysp = $this->db->query("SELECT tasm,id_siswa FROM t_nilai_sikap_sp WHERE id_siswa='".$s."' AND tasm='".$tahun."'")->num_rows();
            if($queryabsen == 0){
                $insertabsen = $this->db->query("INSERT INTO t_nilai_absensi (tasm,id_siswa,s,i,a) VALUES ('".$tahun."','".$s."','','','')");
            }
            if($queryso == 0){
                $insertso = $this->db->query("INSERT INTO t_nilai_sikap_so (tasm,id_kelas,id_siswa,is_wali,selalu,mulai_meningkat) VALUES ('".$tahun."','".$p['kelas']."','".$s."','Y','','')");
            }
            if($querysp == 0){
                $insertsp = $this->db->query("INSERT INTO t_nilai_sikap_sp (tasm,id_kelas,id_siswa,is_wali,selalu,mulai_meningkat) VALUES ('".$tahun."','".$p['kelas']."','".$s."','Y','','')");
            }
            if($querynaik == 0){
                $insertnaik = $this->db->query("INSERT INTO t_naikkelas (id_siswa,ta,naik,catatan_wali) VALUES ('".$s."','".$tahun."','Y','')");
            }

        }

        $query = "INSERT IGNORE INTO t_kelas_siswa (id_kelas, id_siswa, ta) VALUES ".implode(", ", $teks_val).";";
        
        $this->db->query($query);
        redirect($this->d['url']);
    }

    public function hapus($id) {
        $this->db->query("DELETE FROM t_kelas_siswa WHERE id = '$id'");
        $this->db->query("DELETE FROM t_nilai WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");
        $this->db->query("DELETE FROM t_nilai_absensi WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");
        $this->db->query("DELETE FROM t_nilai_ekstra WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");
        $this->db->query("DELETE FROM t_nilai_ket WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");
        $this->db->query("DELETE FROM t_nilai_sikap_so WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");
        $this->db->query("DELETE FROM t_nilai_sikap_sp WHERE id_siswa = '$id' AND tasm = '".$this->d['tasm']."'");

        $d['status'] = "ok";
        $d['data'] = "Data berhasil dihapus";
        
        j($d);
    }

    public function index() {
        $ambil_data_kelas = $this->db->query("SELECT id, nama FROM m_kelas ORDER BY tingkat ASC, nama ASC")->result_array();

        $tampil = "";

        if (!empty($ambil_data_kelas)) {
            foreach ($ambil_data_kelas as $v) {
                $tampil .= '<div class="col-md-4"><div class="panel panel-info">
                                <div class="panel-heading">'.$v['nama'].'</div>
                                <div class="panel-body" style="height: 300px; overflow: auto">
                                <table class="table table-stripped">
                                    <thead>
                                        <tr><th>No</th><th>Nama</th><th>Aksi</th></tr>
                                    </thead>
                                    <tbody>';

                $q_siswa_per_kelas = $this->db->query("SELECT 
                                                        a.id, a.id_kelas,b.id as idSiswa, b.nama nmsiswa
                                                        FROM t_kelas_siswa a
                                                        INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                        WHERE a.id_kelas = '".$v['id']."' 
                                                        AND a.ta = '".$this->d['ta']."'
                                                        ORDER BY b.nama ASC, b.nis ASC")->result_array();

                if (!empty($q_siswa_per_kelas)) {
                    $no = 1;
                    foreach ($q_siswa_per_kelas as $k) {
                        $tampil .= '<tr><td>'.$no++.'</td><td>'.$k['nmsiswa'].'</td><td class="ctr"><a href="#" onclick="return hapus('.$k['id'].');" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i></a></td></tr>';
                    }
                }

                $tampil .= '</tbody></table></div></div></div>';
            }
        }

        $this->d['tampil'] = $tampil;
    	$this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }
    
    public function sync()
    {

        $siswa  = $this->db->query("SELECT nis, s.nama as student ,k.nama as class FROM `m_siswa` as s
                left join `t_kelas_siswa` as ks on s.id = ks.id_siswa
                left JOIN `m_kelas` as k on k.id = ks.id_kelas where ks.ta = '".$this->d['ta']."'")->result();
        $level = 2;
        $schoolDb = $this->load->database('schoolDb', TRUE);
        $pickupDb = $this->load->database('pickupDb', TRUE);

        for ($i = 0; $i < count($siswa); $i++) {
            $sw = $siswa[$i];
            $siswaPortal = $schoolDb->select('*')->from('student')->where('nis', $sw->nis)->get()->row();
            if (isset($siswaPortal)) {
                $schoolDb->set('grade', $sw->class);
                $schoolDb->set('level', $level);
                $schoolDb->where('id', $siswaPortal->id);
                $schoolDb->update('student');

                $pickupDb->set('grade', $sw->class);
                $pickupDb->where('rfidid', $siswaPortal->rfidid);
                $pickupDb->update('student');
            } else {
                $data = $schoolDb->select('*')->from('student')->like('student_name', $sw->student, 'both')->get()->row();
                if (isset($data)) {
                    $schoolDb->set('grade', $sw->class);
                    $schoolDb->set('nis', $sw->nis);
                    $schoolDb->set('level', $level);
                    $schoolDb->where('id', $data->id);
                    $schoolDb->update('student');

                    $pickupDb->set('grade', $sw->class);
                    $pickupDb->where('rfidid', $data->rfidid);
                    $pickupDb->update('student');
                } else {
                    $data = array(
                        'nis' => $sw->nis,
                        'student_name' => $sw->student,
                        'grade' => $sw->class,
                        'level' => $level,
                        'time' => date_format(date_create(), "Y-m-d H:i:s"),
                    );
                    $schoolDb->insert('student', $data);
                }
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

}