<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');
        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['admnama'] = $this->session->userdata($this->sespre.'nama');
        $this->d['nama_form'] = "f_login";
        $get_tasm = $this->db->query("SELECT *  FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['bagi_raport'] = $get_tasm['tgl_raport'];
        $this->d['ta'] = substr($get_tasm['tahun'],0,4);
        $this->d['url'] = "home";
        cek_aktif();
        $wali_kelas = $this->session->userdata('app_rapot_walikelas');
        $this->d['id_kelas'] = $wali_kelas['id_walikelas'];
        $this->d['id_guru'] = $this->session->userdata('app_rapot_konid');
    }
public function index() {
    

    if ($this->d['admlevel'] == "admin") {
        $q_jml_siswa = $this->db->query("SELECT 
                                    SUM(IF(a.jk='L',1,0)) jml_l,
                                    SUM(IF(a.jk='P',1,0)) jml_p
                                    FROM m_siswa a
                                    WHERE a.stat_data = 'A'")->row_array();
         $q_jml_guru = $this->db->query("SELECT COUNT(id) jml
                                    FROM m_guru a
                                    WHERE a.stat_data = 'A'")->row_array();
         $q_jml_mapel = $this->db->query("SELECT COUNT(id) jml FROM m_mapel")->row_array();
         $q_jml_kelas = $this->db->query("SELECT COUNT(id) jml FROM m_kelas")->row_array();
         $q_jml_ekstra = $this->db->query("SELECT COUNT(id) jml FROM m_ekstra")->row_array();
         $tahun_akademik = $this->db->query("SELECT * FROM tahun where tahun like '%".$this->d['ta']."%' and aktif='Y' ")->row_array();

        $this->d['jml_siswa'] = $q_jml_siswa;
        $this->d['jml_guru'] = $q_jml_guru['jml'];
        $this->d['jml_mapel'] = $q_jml_mapel['jml'];
        $this->d['jml_kelas'] = $q_jml_kelas['jml'];
        $this->d['jml_ekstra'] = $q_jml_ekstra['jml'];
        $this->d['tahun_akademik'] = $tahun_akademik;

        $this->d['p'] = "v_home";
    } 
   else if ($this->d['admlevel'] == "guru") {
    $q_jml_kelas = $this->db->query("SELECT 
                                    SUM(IF(b.jk='L',1,0)) jmlk_l,
                                    SUM(IF(b.jk='P',1,0)) jmlk_p
                                    FROM t_kelas_siswa a
                                    INNER JOIN m_siswa b ON a.id_siswa = b.id
                                    WHERE a.ta = '".$this->d['ta']."' AND a.id_kelas = '".$this->d['id_kelas']."'")->row_array();
     $this->d['stat_kelas'] = $q_jml_kelas;

     $q_mapel_diampuh = $this->db->query("SELECT 
                                         count(*) as jml FROM t_guru_mapel 
                                         where id_guru = '".$this->d['id_guru']."' and tasm = '".$this->d['tasm']."' ")->row_array();
    
     $this->d['mapel_diampuh'] = $q_mapel_diampuh['jml'];

     $this->d['list_mapelkelas'] = $this->db->query("SELECT 
                                                a.id, b.kd_singkat nmmapel, a.id_mapel, a.id_kelas, c.nama nmkelas, b.is_sikap
                                                FROM t_guru_mapel a
                                                INNER JOIN m_mapel b ON a.id_mapel = b.id
                                                INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                                WHERE a.id_guru = '".$this->d['admkonid']."'
                                                AND a.tasm = '".$this->d['tasm']."'") ->result_array();

        $this->d['p'] = "v_home_guru";
    } 
    else {
        $this->d['p'] = "v_home_siswa";
    }
    $this->load->view("template_utama", $this->d);
}
    public function buzz_report() {
        if ($this->d['admlevel'] != "admin") {
            redirect('home');
        }
        $this->d['p'] = "v_buzz_report";
        $this->load->view("template_utama", $this->d);
    }
    public function get_low_completable_teachers() {
        header('Content-Type: application/json');

        if ($this->d['admlevel'] != "admin") {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        // Read cached Buzz LMS data
        $cache_file = APPPATH . 'cache/student_completable_cache.json';
        if (!file_exists($cache_file)) {
            echo json_encode(['status' => 'error', 'message' => 'Cache not available. Please wait for data to load.']);
            return;
        }

        $cache_data = json_decode(file_get_contents($cache_file), true);
        if (!$cache_data || $cache_data['status'] !== 'success') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cache data']);
            return;
        }

        // Build a lookup: courseTitle => [avg_completable]
        $course_completable = [];
        foreach ($cache_data['data'] as $course) {
            $title = trim($course['courseTitle']);
            $students = $course['students'] ?? [];
            if (empty($students)) {
                $course_completable[$title] = 0;
                continue;
            }
            $total = 0;
            foreach ($students as $s) {
                $total += (float)($s['completable'] ?? 0);
            }
            $course_completable[$title] = $total / count($students);
        }

        // Get active tasm and year
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $tasm  = $get_tasm['tahun'] ?? '';
        $tahun = substr($tasm, 0, 4);

        // Get teacher -> subject -> class assignments
        $assignments = $this->db->query("
            SELECT b.nama nmguru, d.nama nmmapel, c.nama nmkelas, a.id_guru
            FROM t_guru_mapel a
            INNER JOIN m_guru b ON a.id_guru = b.id
            INNER JOIN m_kelas c ON a.id_kelas = c.id
            INNER JOIN m_mapel d ON a.id_mapel = d.id
            WHERE a.tasm = '$tasm'
            ORDER BY nmguru ASC
        ")->result_array();

        // Group by teacher, collect avg_completable per subject+class course
        $teachers = [];
        foreach ($assignments as $row) {
            $guru_id  = $row['id_guru'];
            $course_title = trim($row['nmmapel'] . ' - ' . $row['nmkelas'] . ' - ' . $tahun);

            if (!isset($teachers[$guru_id])) {
                $teachers[$guru_id] = [
                    'nama'    => $row['nmguru'],
                    'courses' => []
                ];
            }

            if (isset($course_completable[$course_title])) {
                $teachers[$guru_id]['courses'][] = [
                    'title'          => $course_title,
                    'avg_completable' => round($course_completable[$course_title], 2)
                ];
            }
        }

        // Filter teachers whose overall avg completable < 10
        $low_teachers = [];
        foreach ($teachers as $guru_id => $info) {
            if (empty($info['courses'])) continue;

            $sum = array_sum(array_column($info['courses'], 'avg_completable'));
            $avg = $sum / count($info['courses']);

            if ($avg < 10) {
                $low_teachers[] = [
                    'nama'           => $info['nama'],
                    'avg_completable' => round($avg, 2),
                    'courses'        => $info['courses']
                ];
            }
        }

        // Sort ascending by avg
        usort($low_teachers, fn($a, $b) => $a['avg_completable'] <=> $b['avg_completable']);

        echo json_encode(['status' => 'success', 'data' => $low_teachers]);
    }
    public function get_low_completion_pct_teachers() {
        header('Content-Type: application/json');

        if ($this->d['admlevel'] != "admin") {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        // Read cached Buzz LMS data
        $cache_file = APPPATH . 'cache/student_completable_cache.json';
        if (!file_exists($cache_file)) {
            echo json_encode(['status' => 'error', 'message' => 'Cache not available. Please wait for data to load.']);
            return;
        }

        $cache_data = json_decode(file_get_contents($cache_file), true);
        if (!$cache_data || $cache_data['status'] !== 'success') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cache data']);
            return;
        }

        // Build a lookup: courseTitle => avg completion % (completed/completable*100) across students
        $course_pct = [];
        foreach ($cache_data['data'] as $course) {
            $title    = trim($course['courseTitle']);
            $students = $course['students'] ?? [];
            if (empty($students)) {
                $course_pct[$title] = 0;
                continue;
            }
            $pct_sum = 0;
            $count   = 0;
            foreach ($students as $s) {
                $completable = (float)($s['completable'] ?? 0);
                $completed   = (float)($s['completed']   ?? 0);
                if ($completable > 0) {
                    $pct_sum += ($completed / $completable) * 100;
                    $count++;
                }
            }
            $course_pct[$title] = $count > 0 ? $pct_sum / $count : 0;
        }

        // Get active tasm and year
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $tasm  = $get_tasm['tahun'] ?? '';
        $tahun = substr($tasm, 0, 4);

        // Get teacher -> subject -> class assignments (same logic as set_mapel: t_guru_mapel join)
        $assignments = $this->db->query("
            SELECT b.nama nmguru, d.nama nmmapel, c.nama nmkelas, a.id_guru
            FROM t_guru_mapel a
            INNER JOIN m_guru b ON a.id_guru = b.id
            INNER JOIN m_kelas c ON a.id_kelas = c.id
            INNER JOIN m_mapel d ON a.id_mapel = d.id
            WHERE a.tasm = '$tasm'
            ORDER BY nmguru ASC
        ")->result_array();

        // Group by teacher; collect avg completion % for each course they teach
        $teachers = [];
        foreach ($assignments as $row) {
            $guru_id     = $row['id_guru'];
            // Title format must match Scratch.php valid_names: "Mapel - Kelas - Tahun"
            $course_title = trim($row['nmmapel'] . ' - ' . $row['nmkelas'] . ' - ' . $tahun);

            if (!isset($teachers[$guru_id])) {
                $teachers[$guru_id] = [
                    'nama'    => $row['nmguru'],
                    'courses' => []
                ];
            }

            if (isset($course_pct[$course_title])) {
                $teachers[$guru_id]['courses'][] = [
                    'title'   => $course_title,
                    'avg_pct' => round($course_pct[$course_title], 2)
                ];
            }
        }

        // Filter teachers whose average completion % across all subjects they teach < 10%
        $low_teachers = [];
        foreach ($teachers as $guru_id => $info) {
            if (empty($info['courses'])) continue;

            $sum = array_sum(array_column($info['courses'], 'avg_pct'));
            $avg = $sum / count($info['courses']);

            if ($avg < 35) {
                $low_teachers[] = [
                    'nama'    => $info['nama'],
                    'avg_pct' => round($avg, 2),
                    'courses' => $info['courses']
                ];
            }
        }

        // Sort ascending by completion %
        usort($low_teachers, fn($a, $b) => $a['avg_pct'] <=> $b['avg_pct']);

        echo json_encode(['status' => 'success', 'data' => $low_teachers]);
    }
    public function koobits_report() {
        if ($this->d['admlevel'] != "admin") {
            redirect('home');
        }
        $this->d['p'] = "v_koobits_report";
        $this->load->view("template_utama", $this->d);
    }

    public function get_koobits_dashboard_data() {
        header('Content-Type: application/json');
        
        if ($this->d['admlevel'] != "admin") {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $cache_file = APPPATH . 'cache/koobits_data.json';
        if (!file_exists($cache_file)) {
            echo json_encode(['status' => 'error', 'message' => 'KooBits data not available.']);
            return;
        }

        $cache_data = json_decode(file_get_contents($cache_file), true);
        if (!$cache_data || $cache_data['status'] !== 'success') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data format.']);
            return;
        }

        $students = $cache_data['data'];
        
        // Group by level and calculate score
        $levels = [];
        foreach ($students as $s) {
            $lvl = $s['Level'];
            if (!isset($levels[$lvl])) {
                $levels[$lvl] = [];
            }
            
            $mastered = (int)($s['CompetencyDetails']['Mastered'] ?? 0);
            $passed = (int)($s['CompetencyDetails']['Passed'] ?? 0);
            
            // Score = Mastered * 1000 + Passed
            $score = ($mastered * 1000) + $passed;
            
            $levels[$lvl][] = [
                'Fullname' => $s['Fullname'],
                'Grade' => $s['Grade'],
                'Mastered' => $mastered,
                'Passed' => $passed,
                'Score' => $score
            ];
        }

        $top_per_level = [];
        for ($i = 1; $i <= 6; $i++) {
            if (isset($levels[$i])) {
                // Sort by Score descending
                usort($levels[$i], function($a, $b) {
                    return $b['Score'] <=> $a['Score'];
                });
                
                // Get top 5
                $top_per_level[$i] = array_slice($levels[$i], 0, 5);
            } else {
                $top_per_level[$i] = [];
            }
        }

        // Also calculate overall summary for pie chart
        $summary = [
            'Mastered' => 0,
            'Passed' => 0,
            'NeedImprove' => 0,
            'Incomplete' => 0
        ];
        
        foreach ($students as $s) {
            $summary['Mastered'] += (int)($s['CompetencyDetails']['Mastered'] ?? 0);
            $summary['Passed'] += (int)($s['CompetencyDetails']['Passed'] ?? 0);
            $summary['NeedImprove'] += (int)($s['CompetencyDetails']['NeedImprove'] ?? 0);
            $summary['Incomplete'] += (int)($s['CompetencyDetails']['Incomplete'] ?? 0);
        }

        echo json_encode([
            'status' => 'success',
            'top_students' => $top_per_level,
            'summary' => $summary
        ]);
    }
    public function ubah_password() {
        $this->d['p'] = "v_ubah_password";
        $this->load->view("template_utama", $this->d);
    }
    public function simpan_ubah_password() {
        $id_user = $this->session->userdata('app_rapot_id');
        $cek_user = $this->db->query("SELECT id, username, password FROM m_admin WHERE id = $id_user")->row_array();
        $p = $this->input->post();
        $plama = sha1(sha1($p['p1']));
        $d = array();
        if (empty($cek_user)) {
            $d['status'] = "gagal";
            $d['data'] = "User tidak ditemukan";
        } else if ($p['username'] != $cek_user['username'])  {
            $d['status'] = "gagal";
            $d['data'] = "Username tidak ditemukan";
        } else if ($plama != $cek_user['password'])  {
            $d['status'] = "gagal";
            $d['data'] = "Password lama tidak cocok";
        } else if (strlen($p['p2']) < 6) {
            $d['status'] = "gagal";
            $d['data'] = "Password minimal 6 karakter";
        } else if ($p['p2'] != $p['p3']) {
            $d['status'] = "gagal";
            $d['data'] = "Password baru tidak sama";
        } else {
            $this->db->query("UPDATE m_admin SET password = '".sha1(sha1($p['p2']))."' WHERE id = '".$id_user."'");
            $d['status'] = "ok";
            $d['data'] = "Password berhasil diubah";
        }
        j($d);
        exit;
    }
    
    public function cetak() {
        $this->d['siswa'] = $this->db->query("SELECT * FROM m_siswa WHERE m_siswa.id IN (SELECT id_siswa FROM t_kelas_siswa)")->result_array();
        $this->d['tahun'] = $this->db->query("SELECT * FROM tahun")->result_array();
        
        $this->d['p'] = "v_cetak";
        $this->load->view("template_utama", $this->d);
    }
    
    public function cetak_rapot_ok() {
        $p = $this->input->post();
        $id_siswa = $p['id_siswa'];
        $tahun = $p['tahun'];
        
        redirect('cetak_raport/cetak/'.$id_siswa.'/'.$tahun);
    }
    
    
}