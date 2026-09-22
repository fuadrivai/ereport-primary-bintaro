<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lesson_plan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['admnama'] = $this->session->userdata($this->sespre . 'nama');
        $this->d['admuser'] = $this->session->userdata($this->sespre . 'user');
        $this->d['url'] = "lesson_plan";
        
        cek_aktif();

        // Access control: All except student (siswa)
        if ($this->d['admlevel'] == "siswa") {
            redirect('home');
        }
    }

    public function index()
    {
        // Get grades for the dropdown
        $this->d['grades'] = $this->db->query("SELECT * FROM m_kelas ORDER BY tingkat ASC, nama ASC")->result_array();
        
        // Try to determine teacher email
        // Logic: if username looks like an email, use it. Otherwise, construct it or use a default.
        $email = $this->d['admuser'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->d['admuser'] . "@mutiaraharapan.sch.id";
        }
        $this->d['teacher_email'] = $email;

        $this->d['p'] = "v_lesson_plan";
        $this->load->view("template_utama", $this->d);
    }

    public function generate()
    {
        if ($this->input->method() !== 'post') {
            j(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $p = $this->input->post();
        
        // Prepare parameters for the API
        $params = [
            "grade" => $p['grade'],
            "unit" => $p['unit'],
            "numMeetings" => (int)$p['numMeetings'],
            "lo" => $p['lo'],
            "sc" => $p['sc'],
            "kc" => $p['kc'],
            "isNewUnit" => isset($p['isNewUnit']) && ($p['isNewUnit'] === 'true' || $p['isNewUnit'] === '1'),
            "teacherEmail" => $p['teacherEmail']
        ];

        // API Endpoint (Google Apps Script)
        $url = "https://script.google.com/macros/s/AKfycbzA4Rqe6hxVGPMklLsT6lF3E8uulMZ5FYobCs6bcEWkUT_XTx6IWuTk5ZrmppRjO-2cMw/exec";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects (crucial for Google Apps Script)
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            j(['status' => 'error', 'message' => 'API Connection Error: ' . $error]);
            return;
        }

        if ($http_code !== 200) {
            j(['status' => 'error', 'message' => 'API returned status code ' . $http_code, 'raw' => $response]);
            return;
        }

        $result = json_decode($response, true);
        if (!$result) {
            j(['status' => 'error', 'message' => 'Failed to parse API response']);
            return;
        }

        j($result);
    }
}
