<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
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

    public function get_siswa() {
        $q_siswa_per_kelas = $this->db->query("SELECT 
                                                        b.nis nis, b.nama nama
                                                        FROM t_kelas_siswa a
                                                        INNER JOIN m_siswa b ON a.id_siswa = b.id
                                                        AND a.ta = ".$this->d['ta']."
                                                        ORDER BY b.nama ASC")->result_array();
        echo json_encode($q_siswa_per_kelas);
    }
    
    public function post_attendance()
    {
            $json = file_get_contents('php://input');
            $requests = json_decode($json, true); // hasilnya array of records
        try {
            
            if (empty($requests) || !is_array($requests)) {
                log_message('error', 'Invalid JSON received: ' . $json);
                $response = [
                    'status'  => false,
                    'message' => "Array is invalid",
                    'data'=>$requests
                ];
            }
            
            foreach ($requests as $req) {
                if (!empty($req['user_id']) && !empty($req['palm_id']) && !empty($req['ts'])) {
                    $data = [
                        'nis'       => $req['user_id'],
                        'nama'      => $req['palm_id'],
                        'timestamp' => $req['ts']
                    ];
        
                    $insert = $this->db->insert('t_log_absensi', $data);
        
                    if (!$insert) {
                        // ambil error dari database CI3
                        $db_error = $this->db->error();
                        throw new Exception($db_error['message']);
                    }
        
                    $response = [
                        'status'    => true,
                        'message'   => 'Attendance inserted',
                        'insert_id' => $this->db->insert_id()
                    ];
                } else {
                    $response = [
                        'status'  => false,
                        'message' => "Invalid data array",
                        'data'=>$req
                    ];
                }
            }
        } catch (Exception $e) {
            $response = [
                'status'  => false,
                'message' => $e->getMessage(),
                'data'=>$requests
            ];
        }
    
        echo json_encode($response);
    }
    
    public function get_summary($nis, $month)
    {
        $this->load->database();

        if (!$nis || !$month) {
            echo json_encode([
                "error" => "Page not fund, 404!"
            ]);
            return;
        }
        
        $input = $nis;
        $output = explode('-', $input)[0];

        $start_date = $month . '-01';
        $end_date   = date('Y-m-t', strtotime($start_date));

        $query = $this->db->select('nis, nama, timestamp')
            ->where('nis', $output)
            ->where('timestamp >=', $start_date . ' 00:00:00')
            ->where('timestamp <=', $end_date . ' 23:59:59')
            ->order_by('timestamp', 'ASC')
            ->get('t_log_absensi');

        $rows = $query->result_array();

        if (empty($rows)) {
            echo json_encode([
                "total_late" => 0,
                "nis" => $output,
                "name" => null,
                "periode" => date('F Y', strtotime($start_date)),
                "data" => []
            ], JSON_PRETTY_PRINT);
            return;
        }

        date_default_timezone_set('Asia/Jakarta');

        $by_date = [];

        foreach ($rows as $r) {
            $local_ts = date('Y-m-d H:i:s', strtotime($r['timestamp'] . ' +7 hours'));
            $date = date('Y-m-d', strtotime($local_ts));
            $time = date('H:i:s', strtotime($local_ts));

            if (!isset($by_date[$date])) {
                $by_date[$date] = [
                    'in' => [],
                    'out' => []
                ];
            }

            if ($time >= '05:00:00' && $time <= '07:59:59') {
                $by_date[$date]['in'][] = $time;
            } elseif ($time >= '09:00:00' && $time <= '18:59:59') {
                $by_date[$date]['out'][] = $time;
            }
        }

        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($end_date))->modify('+1 day')
        );

        $total_late = 0;
        $data = [];

        foreach ($period as $d) {
            $date_str = $d->format('Y-m-d');

            $clock_in = isset($by_date[$date_str]['in']) && count($by_date[$date_str]['in']) > 0
                ? min($by_date[$date_str]['in'])
                : null;

            $clock_out = isset($by_date[$date_str]['out']) && count($by_date[$date_str]['out']) > 0
                ? max($by_date[$date_str]['out'])
                : null;

            if ($clock_in && $clock_in > '07:10:00') {
                $total_late++;
            }

            $data[] = [
                'date' => $date_str,
                'clock_in' => $clock_in,
                'clock_out' => $clock_out
            ];
        }

        $name = $rows[0]['nama'];

        $result = [
            'total_late' => $total_late,
            'nis' => $nis,
            'name' => $name,
            'periode' => date('F Y', strtotime($start_date)),
            'data' => $data
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result, JSON_PRETTY_PRINT));
    }


}