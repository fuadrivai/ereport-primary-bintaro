<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class N_catatan_kl2 extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['url'] = "n_catatan_kl2";

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($this->d['tasm'], 0, 4);

        $wali = $this->session->userdata($this->sespre."walikelas");

        $this->d['id_kelas'] = $wali['id_walikelas'];
        $this->d['nama_kelas'] = $wali['nama_walikelas'];
    }



    public function simpan() {
        $p = $this->input->post();
        
        $mode_form = $p['mode_form'];

        for ($i = 1; $i < $p['jumlah']; $i++) {
            $tasm = $this->d['tasm'];
            $id_siswa = $p['id_siswa_'.$i];
            $catatan = $p['catatan_mid_'.$i] == "" ? "-" : $p['catatan_mid_'.$i];
            $catatan_final = $p['catatan_final_'.$i] == "" ? "-" : $p['catatan_final_'.$i];
            $capaian_final = $p['capaian_final_'.$i] == "" ? "-" : $p['capaian_final_'.$i];
            $capaian_mid = $p['capaian_mid_'.$i] == "" ? "-" : $p['capaian_mid_'.$i];
            if ($mode_form == "add") {
                $strq = "INSERT INTO t_catatan_kl2 (id_siswa,ta,catatan_mid,capaian_mid,catatan_final,capaian_final) VALUES ('$id_siswa','$tasm','$catatan','$capaian_mid','$catatan_final','$capaian_final')";
            } else if ($mode_form == "edit"){
               // Check if the student already has a record in t_catatan_bi
                $existingRecord = $this->db->query("SELECT id FROM t_catatan_kl2 WHERE ta = '" . $tasm . "' AND id_siswa = '" . $id_siswa . "'")->row();
                if (!$existingRecord) {
                    // The student doesn't have a record in t_catatan_bi, so insert a new record
                    $strq = "INSERT INTO t_catatan_kl2 (id_siswa,ta,catatan_mid,capaian_mid,catatan_final,capaian_final) VALUES ('$id_siswa','$tasm','$catatan','$capaian_mid','$catatan_final','$capaian_final')";
                } else {
                    // The student already has a record in t_catatan_bi, so update the existing record
                    $strq = "UPDATE t_catatan_kl2 SET  catatan_mid = '".$catatan."', capaian_mid = '".$capaian_mid."', catatan_final = '".$catatan_final."' , capaian_final = '".$capaian_final."' WHERE ta = '".$tasm."' AND id_siswa = '".$id_siswa."'";
                }
            }
            
          //  echo $strq;
            $this->db->query($strq);
        }

        $d['status'] = "ok";
        $d['data'] = "Data berhasil disimpan..";

        j($d);
    }

    public function index() {

        $this->d['siswa_kelas'] = $this->db->query("SELECT 
                                                    a.id_siswa, b.nama, IFNULL(c.catatan_mid, '') as catatan_mid, IFNULL(c.capaian_mid, '') as capaian_mid, IFNULL(c.catatan_final, '') as catatan_final, IFNULL(c.capaian_final, '') as capaian_final
                                                    FROM t_kelas_siswa a
                                                    INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                    LEFT JOIN t_catatan_kl2 c ON a.id_siswa = c.id_siswa AND c.ta = '" . $this->d['tasm'] . "'
                                                    WHERE a.id_kelas = '" . $this->d['id_kelas'] . "' AND a.ta = '" . $this->d['ta'] . "' ORDER by b.nama ASC")->result_array();

        $this->d['mode_form'] = "edit";
        
        


        if (empty($this->d['siswa_kelas'])) {
            $this->d['siswa_kelas'] = $this->db->query("SELECT 
                                                    a.id_siswa, b.nama, '' catatan_mid, '' capaian_mid, '' catatan_final, '' capaian_final
                                                    FROM t_kelas_siswa a
                                                    INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                    WHERE a.id_kelas = '".$this->d['id_kelas']."' AND a.ta = '".$this->d['ta']."'")->result_array();
            $this->d['mode_form'] = "add";
            
            
        }
        
        // echo $this->db->last_query();
        // exit;
        
    	$this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

}