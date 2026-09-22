<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Set_mapel extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['url'] = "set_mapel";
        $this->d['idnya'] = "setmapel";
        $this->d['nama_form'] = "f_setmapel";

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
    }

    public function datatable() {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $d_total_row = $this->db->query("SELECT
                                        a.id, b.nama nmguru, c.nama nmkelas, d.nama nmmapel
                                        FROM t_guru_mapel a
                                        INNER JOIN m_guru b ON a.id_guru = b.id
                                        INNER JOIN m_kelas c ON a.id_kelas = c.id
                                        INNER JOIN m_mapel d ON a.id_mapel = d.id
                                        WHERE a.tasm = '".$this->d['tasm']."'
                                        ORDER BY nmguru ASC, nmmapel ASC, nmkelas ASC")->num_rows();
    
        $q_datanya = $this->db->query("SELECT
                                    a.id,a.subject_kkm, b.nama nmguru, c.nama nmkelas, d.nama nmmapel
                                    FROM t_guru_mapel a
                                    INNER JOIN m_guru b ON a.id_guru = b.id
                                    INNER JOIN m_kelas c ON a.id_kelas = c.id
                                    INNER JOIN m_mapel d ON a.id_mapel = d.id
                                    WHERE a.tasm = '".$this->d['tasm']."' AND 
                                    (b.nama LIKE '%".$search['value']."%' 
                                    OR c.nama LIKE '%".$search['value']."%'
                                    OR d.nama LIKE '%".$search['value']."%')
                                    ORDER BY nmguru ASC, nmmapel ASC, nmkelas ASC
                                    LIMIT ".$start.", ".$length."")->result_array();
        $data = array();
        $no = ($start+1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[0] = $no++;
            $data_ok[1] = $d['nmguru'];
            $data_ok[2] = $d['nmmapel']." - ".$d['nmkelas'];
            $data_ok[3] = $d['subject_kkm'];
            $edit_url  = base_url().$this->d['url'].'/edit/'.$d['id'];
            $hapus_btn = '<a href="#" onclick="return hapus(\''.$d['id'].'\');" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i> Hapus</a>';
            $edit_btn  = '<a href="'.$edit_url.'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>';
            $data_ok[4] = $edit_btn.' '.$hapus_btn;

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

    public function edit($id=null) {
        // dropdowns (unchanged)
        $this->d['r_guru']  = $this->db->select('id,nama')->from('m_guru')->order_by('id','ASC')->get()->result_array();
        $this->d['r_mapel'] = $this->db->select('id,nama')->from('m_mapel')->order_by('id','ASC')->get()->result_array();
        $this->d['r_kelas'] = $this->db->select('id,nama')->from('m_kelas')->order_by('nama','ASC')->get()->result_array();
    
        if ($id) {
            // include subject_kkm
            $row = $this->db->select('id,id_guru,id_mapel,id_kelas,subject_kkm')
                            ->from('t_guru_mapel')
                            ->where('id', (int)$id)
                            ->get()->row_array();
            if (!$row) { show_404(); return; }
            $this->d['mode'] = 'edit';
            $this->d['row']  = $row;
        } else {
            $this->d['mode'] = 'create';
            $this->d['row']  = null;
        }
    
        $this->d['p'] = 'form';
        $this->load->view('template_utama', $this->d);
    }

    public function simpan()
    {
        $action = $this->input->post('action'); // update | save_as_new | create
        $id     = (int) $this->input->post('id');
    
        $guru   = (int) $this->input->post('guru');
        $mapel  = (int) $this->input->post('mapel');
    
        // sanitize subject_kkm (default null if empty)
        $kkm_in = $this->input->post('subject_kkm');
        $subject_kkm = is_numeric($kkm_in) ? max(0, min(100, (int)$kkm_in)) : null;
    
        // ---------- SAVE AS NEW (from edit page) ----------
        if ($action === 'save_as_new') {
            $kelas = (int) $this->input->post('kelas');
            if (!$guru || !$mapel || !$kelas || $subject_kkm === null) {
                $this->session->set_flashdata('k', '<div class="alert alert-danger">Data tidak lengkap untuk simpan baru.</div>');
                return redirect($this->d['url']);
            }
            $this->_create_batch($guru, $mapel, [$kelas], $subject_kkm);
            return redirect($this->d['url']);
        }
    
        // ---------- EDIT / UPDATE ----------
        if ($id > 0 && $action === 'update') {
            $kelas = (int) $this->input->post('kelas');
            if (!$guru || !$mapel || !$kelas || $subject_kkm === null) {
                $this->session->set_flashdata('k', '<div class="alert alert-danger">Data tidak lengkap untuk edit.</div>');
                return redirect($this->d['url']);
            }
    
            // exclude current id in duplicate check
            $dupe = $this->db->select('id')
                ->from('t_guru_mapel')
                ->where('tasm', $this->d['tasm'])
                ->where('id_mapel', $mapel)
                ->where('id_kelas', $kelas)
                ->where('id <>', $id)
                ->count_all_results();
    
            if ($dupe > 0) {
                $this->session->set_flashdata('k', '<div class="alert alert-danger">Kombinasi kelas & mapel sudah memiliki guru.</div>');
                return redirect($this->d['url']);
            }
    
            $upd = [
                'tasm'         => $this->d['tasm'],
                'id_guru'      => $guru,
                'id_mapel'     => $mapel,
                'id_kelas'     => $kelas,
                'subject_kkm'  => $subject_kkm,
            ];
            $this->db->where('id', $id)->update('t_guru_mapel', $upd);
    
            $this->session->set_flashdata('k',
                $this->db->affected_rows() >= 0
                ? '<div class="alert alert-success">Data berhasil diperbarui.</div>'
                : '<div class="alert alert-danger">Gagal memperbarui data.</div>'
            );
            return redirect($this->d['url']);
        }
    
        // ---------- CREATE (from create page) ----------
        if ($action === 'create') {
            $pilih = $this->input->post('data_pilih');
            if (!$guru || !$mapel || !is_array($pilih) || empty($pilih) || $subject_kkm === null) {
                $this->session->set_flashdata('k', '<div class="alert alert-danger">Data tidak lengkap untuk tambah.</div>');
                return redirect($this->d['url']);
            }
            $kelas_ids = array_values(array_unique(array_map('intval', $pilih)));
            $this->_create_batch($guru, $mapel, $kelas_ids, $subject_kkm);
            return redirect($this->d['url']);
        }
    
        $this->session->set_flashdata('k', '<div class="alert alert-warning">Aksi tidak dikenali.</div>');
        return redirect($this->d['url']);
    }
    
    private function _create_batch($guru, $mapel, array $kelas_ids, $subject_kkm)
    {
        // find duplicates
        $existing = [];
        if (!empty($kelas_ids)) {
            $existing = $this->db->select('id_kelas')
                ->from('t_guru_mapel')
                ->where('tasm', $this->d['tasm'])
                ->where('id_mapel', $mapel)
                ->where_in('id_kelas', $kelas_ids)
                ->get()->result_array();
        }
    
        $sudah_ids = array_column($existing, 'id_kelas');
        $sudah_set = array_flip($sudah_ids);
    
        $rows = [];
        foreach ($kelas_ids as $kls) {
            if (!isset($sudah_set[$kls])) {
                $rows[] = [
                    'tasm'         => $this->d['tasm'],
                    'id_guru'      => $guru,
                    'id_kelas'     => $kls,
                    'id_mapel'     => $mapel,
                    'subject_kkm'  => $subject_kkm,
                ];
            }
        }
    
        $this->db->trans_start();
        if (!empty($rows)) {
            $this->db->insert_batch('t_guru_mapel', $rows);
        }
        $this->db->trans_complete();
    
        $inserted = !empty($rows) ? $this->db->affected_rows() : 0;
        $dupes    = count($sudah_ids);
    
        $msg = [];
        if ($inserted > 0) $msg[] = "{$inserted} data berhasil ditambahkan.";
        if ($dupes > 0)    $msg[] = "{$dupes} kelas sudah memiliki guru pada mapel ini, dilewati.";
        if (empty($msg))   $msg[] = "Tidak ada perubahan.";
    
        $this->session->set_flashdata('k', '<div class="alert alert-info">'.implode(' ', $msg).'</div>');
    }

    public function hapus($id) {
        $this->db->query("DELETE FROM t_guru_mapel WHERE id = '$id'");

        $d['status'] = "ok";
        $d['data'] = "Data berhasil dihapus";
        
        j($d);
    }
    
    public function copy_semester_lalu() {
        $sekarang = $this->d['tasm'];
        $tahun_lalu = intval(substr($sekarang, 0, 4));
        $semester_lalu = intval(substr($sekarang, 4, 1));
        
        if ($semester_lalu == 1) {
            $tahun_lalu = $tahun_lalu - 1;
            $semester_lalu = 2;
        } else {
            $tahun_lalu = $tahun_lalu;
            $semester_lalu = 1;
        }
        
        $semester_yll = $tahun_lalu.$semester_lalu;
        
        $queri = $this->db->query("SELECT * FROM t_guru_mapel WHERE tasm = '".$semester_yll."'")->result_array();
        
        $arre_input = array();
        if (!empty($queri)) {
            foreach ($queri as $d) {
                $teks1 = "('".$sekarang."', '".$d['id_guru']."', '".$d['id_kelas']."', '".$d['id_mapel']."')";
            
                $arre_input[] = $teks1;
            }
        }
        
        $hapus_sekarang = $this->db->query("DELETE FROM t_guru_mapel WHERE tasm = '".$sekarang."'");
        
        $queri_input = "INSERT INTO t_guru_mapel (tasm, id_guru, id_kelas, id_mapel) VALUES ".implode(",", $arre_input).";";
        
        $this->db->query($queri_input);
        redirect('set_mapel');
    }

    public function index() {
    	$this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

}