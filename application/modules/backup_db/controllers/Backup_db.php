<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
-- ---------------------------------------------------------------
-- MYRAPORT K13
-- CREATED BY : NGODING PINTAR
-- COPYRIGHT  : Copyright (c) 2019 - 2020, (youtube.com/ngodingpintar)
-- CREATED ON : 2019-11-26
-- UPDATED ON : 2020-02-10
-- ---------------------------------------------------------------
*/
class Backup_db extends CI_Controller
{
    function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['url'] = "backup_db";
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];        
    }

    public function datatable() {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $d_total_row = $this->db->query("SELECT id FROM t_backupdb")->num_rows();
    
        $qdata = $this->db->query("SELECT * FROM t_backupdb WHERE tahun_akademik LIKE '%".$search['value']."%' ORDER BY id DESC LIMIT ".$start.", ".$length."");
        $q_datanya = $qdata->result_array();
        $j_datanya = $qdata->num_rows();

        $data = array();
        $no = ($start+1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[] = $no++;
            $data_ok[] = $d['tahun_akademik'];
            $data_ok[] = $d['nama_file'];
            $data_ok[] = $d['tgl_simpan'];
            $data_ok[] = '<a href="'.base_url().'/backup/'.$d['nama_file'].'" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> Download</a> 
            ';

            $data[] = $data_ok;
        }

        $json_data = array(
                    "draw" => $draw,
                    "iTotalRecords" => $j_datanya,
                    "iTotalDisplayRecords" => $d_total_row,
                    "data" => $data
                );
        j($json_data);
        exit;
    }

     public function index() {
      $this->d['p'] = "list";
        
        $this->load->view("template_utama", $this->d);
    }

  // backup files in directory
  function files()
  {
     $opt = array(
       'src' => '../www', // dir name to backup
       'dst' => 'backup/' // dir name backup output destination
     );
     
     // Codeigniter v3x
     $this->load->library('recurseZip_lib', $opt);
     $download = $this->recursezip_lib->compress();
     
     /* Codeigniter v2x
     $zip    = $this->load->library('recurseZip_lib', $opt);     
     $download = $zip->compress();
     */
     
     redirect(base_url($download));
  }
   
  // backup database.sql
  public function db()
  {
     $this->load->dbutil();
   
     $prefs = array(
       'format' => 'zip',
       'filename' => 'Backup_db_TA'.$this->d['tasm'].'_'. date("d-m-Y").'.sql'
     );
   
     $backup =& $this->dbutil->backup($prefs);
   
     $db_name = 'Backup_db_TA'.$this->d['tasm'].'_'. date("d-m-Y") . '.zip'; // file name
     $save  = 'backup/' . $db_name; // dir name backup output destination
   
     $this->load->helper('file');
     write_file($save, $backup);
      
     $this->db->query("INSERT into t_backupdb (tahun_akademik, nama_file) VALUES ('".$this->d['tasm']."', '$db_name')");
     
    
     $this->session->set_flashdata('flash', 'berhasil');
     redirect('backup_db');


     
   //  $this->load->helper('download');
     //force_download($db_name, $backup);
  }
   



}