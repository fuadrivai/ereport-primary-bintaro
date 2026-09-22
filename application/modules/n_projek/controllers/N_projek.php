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

class N_projek extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre . 'konid');
        $this->d['url'] = "n_projek";

        $get_tasm = $this->db->query("SELECT tahun, nama_kepsek, nip_kepsek, tgl_raport FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($get_tasm['tahun'], 0, 4);

        $this->d['wk'] = $this->session->userdata('app_rapot_walikelas');
    }

    public function index()
    {

        $wali = $this->session->userdata($this->sespre . "walikelas");

        $this->d['siswa_kelas'] = $this->db->query("SELECT DISTINCT
                                                d.kelompok, d.nama, d.description, d.p_singkat
                                            FROM 
                                                t_nilai_kelompok a
                                                INNER JOIN t_guru_kelompok b ON a.id_guru_mapel = b.id
                                                INNER JOIN m_kelompok c ON b.id_kelompok = c.id
                                                INNER JOIN m_projek d ON c.kelompok = d.p_singkat
                                            WHERE
                                                a.tasm = '" . $this->d['tasm'] . "'
                                                AND b.id_kelas = '" . $wali['id_walikelas'] . "'
                                    GROUP BY b.id_kelompok")->result_array();

        $this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

}