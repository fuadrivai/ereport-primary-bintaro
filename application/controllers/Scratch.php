<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scratch extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('buzz');
    }

    public function get_student_completable() {
        header('Content-Type: application/json');

        $cache_file = APPPATH . 'cache/student_completable_cache.json';
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
            echo file_get_contents($cache_file);
            return;
        }

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $tasm = $get_tasm['tahun'];
        $tahun = substr($tasm, 0, 4);

        // Get all valid name_mapel configurations for current year
        $valid_names = [];
        $mappings = $this->db->query("SELECT b.nama nmmapel, c.nama nmkelas 
                                      FROM t_guru_mapel a
                                      INNER JOIN m_mapel b ON a.id_mapel = b.id 
                                      INNER JOIN m_kelas c ON a.id_kelas = c.id 
                                      WHERE a.tasm = '$tasm'")->result_array();

        if (empty($mappings)) {
            echo json_encode(['status' => 'error', 'message' => 'No active mapping found for current year']);
            return;
        }

        foreach ($mappings as $map) {
            $valid_names[] = trim($map['nmmapel']." - ".$map['nmkelas']." - ".$tahun);
        }
        
        $token = get_buzz_token();
        if (!$token) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to get Buzz token']);
            return;
        }

        $dateNow = gmdate('Y-m-d\TH:i:s.000\Z');
        
        // 1. Get Course List
        // You can switch to mutiaraharapan.sch.id/bridge.php if CORS/Proxy is needed
        $coursesUrl = "http://103.247.217.153:8000/listcourses?_token={$token}&limit=500&query=%2Fstartdate%3C%27{$dateNow}%27and%2Fenddate%3E%27{$dateNow}%27&select=enrollmentmetrics&domainid=175758880";
        
        $ch = curl_init($coursesUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $coursesResponse = curl_exec($ch);
        curl_close($ch);
        
        // Parse XML directly
        $xmlCourses = @simplexml_load_string($coursesResponse);
        
        if (!$xmlCourses || !isset($xmlCourses->courses->course)) {
             echo json_encode(['status' => 'error', 'message' => 'Failed to parse course list']);
             return;
        }

        $siswa_query = $this->db->query("SELECT nama FROM m_siswa WHERE stat_data = 'A'")->result_array();
        $active_students = [];
        foreach ($siswa_query as $s) {
            $active_students[] = strtolower(trim($s['nama']));
        }

        $result = [];

        // 2. Loop each course and get entity gradebook
        foreach ($xmlCourses->courses->course as $course) {
            $courseId = isset($course['id']) ? (string)$course['id'] : '';
            $courseTitle = isset($course['title']) ? (string)$course['title'] : '';
            
            if (!$courseId) continue;
            if (!in_array(trim($courseTitle), $valid_names)) continue;

            $gradebookUrl = "http://103.247.217.153:8000/getentitygradebook2?_token={$token}&itemid=**&daysactivepastend=14&entityid={$courseId}";

            $ch = curl_init($gradebookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $gradebookResponse = curl_exec($ch);
            curl_close($ch);

            $xmlGradebook = @simplexml_load_string($gradebookResponse);
            
            $enrollments = [];
            if ($xmlGradebook && isset($xmlGradebook->enrollments->enrollment)) {
                $enrollments = $xmlGradebook->enrollments->enrollment;
            }

            $studentsInfo = [];
            foreach ($enrollments as $enrollment) {
                // Parse user details
                $firstname = isset($enrollment->user['firstname']) ? (string)$enrollment->user['firstname'] : '';
                $lastname = isset($enrollment->user['lastname']) ? (string)$enrollment->user['lastname'] : '';
                
                $buzz_fullname = strtolower(trim($firstname . ' ' . $lastname));
                
                $is_valid_student = false;
                foreach ($active_students as $as) {
                    if ($as === $buzz_fullname || 
                        ($buzz_fullname !== '' && strpos($as, $buzz_fullname) !== false) ||
                        ($as !== '' && strpos($buzz_fullname, $as) !== false)) {
                        $is_valid_student = true;
                        break;
                    }
                }

                if (!$is_valid_student) {
                    continue;
                }

                // Parse grades details
                $completable = isset($enrollment->grades['completable']) ? (float)$enrollment->grades['completable'] : 0;
                if ($completable > 50) {
                    $completable = 50;
                }

                $completed = isset($enrollment->grades['completed']) ? (float)$enrollment->grades['completed'] : 0;
                if ($completed > 50) {
                    $completed = 50;
                }

                $complete = isset($enrollment->grades['complete']) ? (float)$enrollment->grades['complete'] : 0;
                
                $studentsInfo[] = [
                    'firstname' => trim($firstname),
                    'lastname'  => trim($lastname),
                    'completable' => $completable,
                    'completed' => $completed,
                    'complete'  => $complete
                ];
            }

            $result[] = [
                'courseId' => $courseId,
                'courseTitle' => $courseTitle,
                'students' => $studentsInfo
            ];
        }

        $json_output = json_encode([
            'status' => 'success',
            'data'   => $result
        ]);
        
        @file_put_contents($cache_file, $json_output);
        
        echo $json_output;
    }
    public function get_koobits_data() {
        set_time_limit(0); // Allow long-running sync
        header('Content-Type: application/json');

        $cache_file = APPPATH . 'cache/koobits_data.json';
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
            echo file_get_contents($cache_file);
            return;
        }

        $token_url = 'https://mutiaraharapan.sch.id/sistem-koobits/koobits_token_primary_bintaro.json';

        // Fetch the content from the URL
        $json_content = @file_get_contents($token_url);
        
        if ($json_content === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Unable to access remote token file']);
            return;
        }
        
        $token_data = json_decode($json_content, true);
        
        // Verify the JSON was decoded correctly and the key exists
        $bearer_token = isset($token_data['bearerToken']) ? $token_data['bearerToken'] : null;
        if (!$bearer_token) {
            echo json_encode(['status' => 'error', 'message' => 'Bearer token not found in cache']);
            return;
        }

        $siswa_query = $this->db->query("SELECT nama FROM m_siswa WHERE stat_data = 'A'")->result_array();
        $active_students = [];
        foreach ($siswa_query as $s) {
            $active_students[] = strtolower(trim($s['nama']));
        }

        $all_results = [];

        for ($level = 1; $level <= 6; $level++) {
            $classes_url = "https://prod.api.koobits.com/teacherapi/Lookup/Classes/{$level}";
            
            $ch = curl_init($classes_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer {$bearer_token}",
                "Content-Type: application/json"
            ]);
            $classes_response = curl_exec($ch);
            curl_close($ch);

            $classes_data = json_decode($classes_response, true);

            if (isset($classes_data['IsSuccessful']) && $classes_data['IsSuccessful'] && isset($classes_data['Result'])) {
                foreach ($classes_data['Result'] as $class) {
                    $class_id = $class['ID'];
                    $class_name = $class['Name'];
                    
                    $report_url = "https://prod.api.koobits.com/teacherapi/Report/GetAllUserProficiencyReport/Student/{$class_id}/{$level}/102";
                    
                    $ch = curl_init($report_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: Bearer {$bearer_token}",
                        "Content-Type: application/json"
                    ]);
                    $report_response = curl_exec($ch);
                    curl_close($ch);

                    $report_data = json_decode($report_response, true);

                    if (isset($report_data['IsSuccessful']) && $report_data['IsSuccessful'] && isset($report_data['Result'])) {
                        foreach ($report_data['Result'] as $student) {
                            $kb_fullname = strtolower(trim($student['Fullname']));
                            
                            $is_valid_student = false;
                            foreach ($active_students as $as) {
                                if ($as === $kb_fullname || 
                                    ($kb_fullname !== '' && strpos($as, $kb_fullname) !== false) ||
                                    ($as !== '' && strpos($kb_fullname, $as) !== false)) {
                                    $is_valid_student = true;
                                    break;
                                }
                            }

                            if (!$is_valid_student) {
                                continue;
                            }

                            $all_results[] = [
                                'Fullname' => $student['Fullname'],
                                'Grade' => $class_name,
                                'Level' => $level,
                                'CompetencyDetails' => $student['CompetencyDetails']
                            ];
                        }
                    }
                }
            }
        }

        $json_output = json_encode([
            'status' => 'success',
            'data'   => $all_results
        ]);
        
        @file_put_contents($cache_file, $json_output);
        
        echo $json_output;
    }
}
