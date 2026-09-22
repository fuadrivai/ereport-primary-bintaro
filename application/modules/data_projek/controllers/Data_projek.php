<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_projek extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['url'] = "data_projek";
        $this->d['idnya'] = "dataprojek";
        $this->d['nama_form'] = "f_dataprojek";
    }

    public function datatable() {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $d_total_row = $this->db->query("SELECT id FROM m_projek")->num_rows();
    
        $q_datanya = $this->db->query("SELECT * FROM m_projek WHERE nama LIKE '%".$search['value']."%' ORDER BY id DESC LIMIT ".$start.", ".$length."")->result_array();
        $data = array();
        $no = ($start+1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[] = $no++;
            $data_ok[] = $d['kelompok'];
            $data_ok[] = $d['p_singkat'];
            $data_ok[] = $d['nama'];
            $data_ok[] = $d['description'];
            //$data_ok[3] = ($d['is_sikap'] == "0") ? '<i class="fa fa-minus-circle"></i>' : '<i class="fa fa-check-circle"></i>';

            $data_ok[] = '<a href="#" onclick="return edit(\''.$d['id'].'\');" class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a> 
                <a href="#" onclick="return hapus(\''.$d['id'].'\');" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i> Hapus</a> ';

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
        $q = $this->db->query("SELECT *, 'edit' AS mode FROM m_projek WHERE id = '$id'")->row_array();

        $d = array();
        $d['status'] = "ok";
        if (empty($q)) {
            $d['data']['id'] = "";
            $d['data']['mode'] = "add";
            //$d['data']['is_sikap'] = "";
            $d['data']['nama'] = "";
            $d['data']['desc'] = "";
        } else {
            $d['data'] = $q;
        }

        j($d);
    }

    public function simpan() {
        $p = $this->input->post();

        $d['status'] = "";
        $d['data'] = "";
        $nama = $this->db->escape_str($p['nama']);
        $desc = $this->db->escape_str($p['desc']);
        if ($p['_mode'] == "add") {
            
            $this->db->query("INSERT INTO m_projek (kelompok,nama,p_singkat,description) VALUES ('".$p['kelompok']."','".$nama."', '".$p['kd_singkat']."','".$desc."')");

            $d['status'] = "ok";
            $d['data'] = "Data berhasil disimpan";
        } else if ($p['_mode'] == "edit") {
            $this->db->query("UPDATE m_projek SET kelompok = '".$p['kelompok']."', p_singkat = '".$p['kd_singkat']."', nama = '".$nama."', description = '".$desc."' WHERE id = '".$p['_id']."'");

            $d['status'] = "ok";
            $d['data'] = "Data berhasil disimpan";
        } else {
            $d['status'] = "gagal";
            $d['data'] = "Kesalahan sistem";
        }

        j($d);
    }

    public function hapus($id) {
        $this->db->query("DELETE FROM m_projek WHERE id = '$id'");

        $d['status'] = "ok";
        $d['data'] = "Data berhasil dihapus";
        
        j($d);
    }

    public function index() {
    	$this->d['p'] = "list";
        $this->d['p_kelompok'] = array("GHB"=>"Gaya Hidup Berkelanjutan","KL"=>"Kearifan Lokal","BTI"=>"Bhineka Tunggal Ika","BJR"=>"Bangunlah Jiwa dan Raganya","SD"=>"Suara Demokrasi","BBMN"=>"Berekayasa dan Berteknologi untuk Membangun NKRI","KW"=>"Kewirausahaan");
        $this->d['p_tambahansub'] = array("NO"=>"-");

        $this->load->view("template_utama", $this->d);
    }

}