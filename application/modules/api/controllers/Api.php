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

    /**
     * GET /api/get_master_data
     *
     * Returns a consolidated JSON payload containing:
     *  - academic_year  : active academic year label (e.g. "2025/2026")
     *  - classes        : list of classes with generated code, name, and grade level
     *  - subjects       : list of subjects with code (kd_singkat) and name
     *  - teachers       : list of teachers with username and their teaching assignments
     *  - students       : list of students with NIS, name, and their class code
     */
    public function get_master_data()
    {
        // -- Set JSON header --
        $this->output->set_content_type('application/json');

        // ------------------------------------------------------------------
        // 1. Academic year
        // ------------------------------------------------------------------
        $tasm_row = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y' LIMIT 1")->row_array();

        if (empty($tasm_row)) {
            echo json_encode(['error' => 'Tahun aktif tidak ditemukan'], JSON_PRETTY_PRINT);
            return;
        }

        $tasm = $tasm_row['tahun'];                      // e.g. "20261"
        $ta   = substr($tasm, 0, 4);                     // e.g. "2026"  → used for t_kelas_siswa.ta
        $sem  = substr($tasm, 4, 1);                     // e.g. "1"

        // Build human-readable academic year label, e.g. "2025/2026"
        $year_start    = (int)$ta - ($sem == '2' ? 0 : 1);
        $year_end      = $year_start + 1;
        $academic_year = $year_start . '/' . $year_end;

        // ------------------------------------------------------------------
        // 2. Classes  →  code generated as "CLS-{id}" to keep it stable
        // ------------------------------------------------------------------
        $raw_kelas = $this->db->query(
            "SELECT id, nama, tingkat FROM m_kelas ORDER BY tingkat ASC, nama ASC"
        )->result_array();

        $classes    = [];
        $kelas_code = [];   // map: id → code

        foreach ($raw_kelas as $k) {
            $code = 'CLS-' . $k['id'];
            $kelas_code[$k['id']] = $code;

            $classes[] = [
                'code'        => $code,
                'name'        => $k['nama'],
                'grade_level' => (int)$k['tingkat'],
            ];
        }

        // ------------------------------------------------------------------
        // 3. Subjects  →  kd_singkat used as code
        // ------------------------------------------------------------------
        $raw_mapel = $this->db->query(
            "SELECT id, kd_singkat, nama FROM m_mapel ORDER BY kelompok ASC, nama ASC"
        )->result_array();

        $subjects    = [];
        $mapel_code  = [];  // map: id → kd_singkat

        foreach ($raw_mapel as $m) {
            $mapel_code[$m['id']] = $m['kd_singkat'];
            $subjects[] = [
                'code' => $m['kd_singkat'],
                'name' => $m['nama'],
            ];
        }

        // ------------------------------------------------------------------
        // 4. Teachers with assignments for the active semester (tasm)
        // ------------------------------------------------------------------
        $raw_assignments = $this->db->query(
            "SELECT
                g.id         AS guru_id,
                g.nama       AS guru_nama,
                a.username   AS username,
                gm.id_mapel,
                gm.id_kelas
             FROM t_guru_mapel gm
             INNER JOIN m_guru  g ON gm.id_guru  = g.id
             LEFT  JOIN m_admin a ON a.level = 'guru' AND a.konid = g.id
             WHERE gm.tasm = '" . $this->db->escape_str($tasm) . "'
             ORDER BY g.nama ASC"
        )->result_array();

        // Group assignments by teacher
        $teacher_map = [];

        foreach ($raw_assignments as $row) {
            $gid = $row['guru_id'];

            if (!isset($teacher_map[$gid])) {
                $teacher_map[$gid] = [
                    'username'    => $row['username'] !== null ? $row['username'] : '',
                    'name'        => $row['guru_nama'],
                    'assignments' => [],
                ];
            }

            $subject_code = isset($mapel_code[$row['id_mapel']]) ? $mapel_code[$row['id_mapel']] : null;
            $class_code   = isset($kelas_code[$row['id_kelas']]) ? $kelas_code[$row['id_kelas']] : null;

            if ($subject_code !== null && $class_code !== null) {
                $teacher_map[$gid]['assignments'][] = [
                    'subject_code' => $subject_code,
                    'class_code'   => $class_code,
                ];
            }
        }

        $teachers = array_values($teacher_map);

        // ------------------------------------------------------------------
        // 5. Students with their class code for the active academic year
        // ------------------------------------------------------------------
        $raw_siswa = $this->db->query(
            "SELECT
                s.nis,
                s.nama,
                ks.id_kelas
             FROM t_kelas_siswa ks
             INNER JOIN m_siswa s ON ks.id_siswa = s.id
             WHERE ks.ta = '" . $this->db->escape_str($ta) . "'
               AND s.stat_data = 'A'
             ORDER BY s.nama ASC"
        )->result_array();

        $students = [];

        foreach ($raw_siswa as $s) {
            $class_code = isset($kelas_code[$s['id_kelas']]) ? $kelas_code[$s['id_kelas']] : null;

            $students[] = [
                'nis'        => $s['nis'],
                'name'       => $s['nama'],
                'class_code' => $class_code,
            ];
        }

        // ------------------------------------------------------------------
        // 6. Build and return the final payload
        // ------------------------------------------------------------------
        $payload = [
            'academic_year' => $academic_year,
            'classes'       => $classes,
            'subjects'      => $subjects,
            'teachers'      => $teachers,
            'students'      => $students,
        ];

        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}