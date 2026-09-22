<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengumuman extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['url'] = "pengumuman";
        $this->d['idnya'] = "id_pengumuman";
        $this->d['nama_form'] = "f_pengumuman";
    }

    public function datatable()
    {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $wali_kelas = $this->session->userdata('app_rapot_walikelas');
$is_wali = $wali_kelas['is_wali'];
$nama_walikelas = $wali_kelas['nama_walikelas'];

if ($is_wali) {
    // If the user is a wali kelas, only select announcements for their class
    $d_total_row = $this->db->query("SELECT * FROM t_pengumuman WHERE class_name = '$nama_walikelas'")->num_rows();

    $q_datanya = $this->db->query("SELECT * FROM t_pengumuman WHERE (class_name = '$nama_walikelas') AND (class_name LIKE '%" . $search["value"] . "%' OR title LIKE '%" . $search["value"] . "%')")->result_array();
} else {
    // If the user is not a wali kelas, select all announcements
    $d_total_row = $this->db->query("SELECT * FROM t_pengumuman")->num_rows();

    $q_datanya = $this->db->query("SELECT * FROM t_pengumuman WHERE class_name LIKE '%" . $search["value"] . "%' OR title LIKE '%" . $search["value"] . "%'")->result_array();
}

        $data = array();
        $no = ($start + 1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[0] = $no++;
            $data_ok[1] = $d['class_name'];
            $data_ok[2] = $d['title'];
            $data_ok[3] = $d['link'];
            // $data_ok[4] = $d['created_date'];
            // $data_ok[5] = $d['sent_date'];
            // $data_ok[6] = $d['status'];
            // $data_ok[4] = '<a href="#" onclick="return edit(\'' . $d['id'] . '\');" class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</a> 
            // <a href="#" onclick="return hapus(\'' . $d['id'] . '\');" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i> Hapus</a> ';

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

    public function edit($id)
    {
    }

    public function simpan()
    {
        $p = $this->input->post();

        $d['status'] = "";
        $d['data'] = "";
        $d['date'] = date("Y-m-d h:i:s");

        if ($p['_mode'] == "add") {
            $this->db->query("INSERT INTO t_pengumuman (class_name, title,link,status,created_date,sent_date) VALUES ('" . $p['class_name'] . "', '" . $p['title'] . "','" . $p['link'] . "',1,'" . $d['date'] . "','" . $d['date'] . "')");
            $schoolDb = $this->load->database('schoolDb', TRUE);
            $schoolDb->query("INSERT INTO class_notif (class_name, title,link,time) VALUES ('" . $p['class_name'] . "', '" . $p['title'] . "','" . $p['link'] . "','" . $d['date'] . "')");

            $d['status'] = "ok";
            $d['data'] = "Data berhasil disimpan";
        } else {
            $d['status'] = "gagal";
            $d['data'] = "Kesalahan sistem";
        }

        j($d);
    }

    public function hapus($id)
    {
    }

    public function index()
    {
        $data = $this->db->query("SELECT * FROM m_kelas")->result_array();
        $this->d['p'] = "list";
        $this->d['p_class'] =  $data;
        $this->load->view("template_utama", $this->d);
    }
}
