<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cetak_cache extends CI_Controller
{
    // === CONFIG ===
    // Cache lives on DO volume but is bind-mounted into webroot:
    // mount --bind /mnt/volume_sgp1_02/document/rapor_cache/bintaro/primary \
    //              /www/wwwroot/report.mhis.link/bintaro/primary/storage_cache
    private $CACHE_BASE = '/www/wwwroot/report.mhis.link/bintaro/primary/storage_cache/';

    // Use a long random string and keep it secret
    private $SECRET = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';

    // Path to Html2Pdf autoload (relative to FCPATH)
    private $HTML2PDF_AUTOLOAD = 'aset/html2pdf/autoload.php';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Pre-generate PDFs over HTTP (chunked).
     *
     * URL:
     *   https://report.mhis.link/bintaro/primary/index.php/cetak_cache/pregen_http/20241/YOURSECRET?limit=200&offset=0&force=0
     *
     * @param string $tasm   yyyys (e.g., 20241)
     * @param string $token  secret
     * GET: limit (default 200), offset (default 0), force (0/1)
     */
    public function pregen_http($tasm = null, $token = null)
    {
        // 1) Security first
        if ($token !== $this->SECRET) {
            return $this->_end('Unauthorized', 401);
        }

        // 2) Validate input
        if (!$tasm || !preg_match('/^\d{5}$/', (string)$tasm)) {
            return $this->_end('Invalid tasm', 400);
        }
        $limit  = (int)($this->input->get('limit')  ?? 200);
        $offset = (int)($this->input->get('offset') ?? 0);
        $force  = (int)($this->input->get('force')  ?? 0);

        if ($limit <= 0 || $limit > 1000) $limit = 200;
        if ($offset < 0) $offset = 0;

        // 3) Per-chunk lock (avoid duplicate runs for same chunk)
        $lockFile = $this->_lockPath($tasm, $offset);
        if (file_exists($lockFile) && (time() - @filemtime($lockFile)) < 1800) { // 30 min TTL
            return $this->_end("Locked: tasm=$tasm offset=$offset", 429);
        }
        @file_put_contents($lockFile, date('c'));

        // 4) Make sure cache dir exists (after auth ok)
        if (!is_dir($this->CACHE_BASE)) {
            @mkdir($this->CACHE_BASE, 0775, true);
        }

        $tahun    = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);

        // 5) Fetch a chunk of students
        $rapors = $this->db->query("
            SELECT r.id, r.id_siswa, r.nama, r.kelas, r.tipe_rapor
            FROM t_rapor r
            WHERE r.tahun = ? AND r.semester = ? AND r.jenis_rapor = 1
            ORDER BY r.id
            LIMIT ? OFFSET ?
        ", [$tahun, $semester, $limit, $offset])->result();

        // 6) Generate
        $generated = 0; $skipped = 0; $failed = 0;
        foreach ($rapors as $r) {
            $ok = $this->_generate_single_pdf((int)$r->id_siswa, $tasm, (bool)$force);
            if ($ok === true)        $generated++;
            elseif ($ok === 'skip')  $skipped++;
            else                     $failed++;
            gc_collect_cycles();
        }

        // 7) Unlock
        @unlink($lockFile);

        return $this->_end("OK tasm=$tasm offset=$offset limit=$limit generated=$generated skipped=$skipped failed=$failed", 200);
    }

    /**
     * Serve cached PDF to parents.
     * Example: /cetak_raport/mhis_cached/12345/20241
     */
    public function mhis_cached($id_siswa = null, $tasm = null)
    {
        if (!$id_siswa || !$tasm || !preg_match('/^\d{5}$/', (string)$tasm)) {
            return $this->_end('Bad request', 400);
        }
        $tahun    = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);

        $rapor = $this->db->query("
            SELECT nama, kelas
            FROM t_rapor
            WHERE id_siswa = ? AND tahun = ? AND semester = ? AND jenis_rapor = 1
            LIMIT 1
        ", [$id_siswa, $tahun, $semester])->row();
        if (!$rapor) {
            return $this->_end('No rapor found', 404);
        }

        $safeName  = $this->_safe($rapor->nama ?? 'SISWA');
        $safeKelas = $this->_safe($rapor->kelas ?? 'KELAS');
        $pdfPath   = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";

        if (is_file($pdfPath)) {
            // Stream with minimal memory
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($pdfPath));
            header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
            readfile($pdfPath);
            return;
        }
        // Optional: trigger on-demand generation, or ask user to try again later
        return $this->_end('Rapor sedang disiapkan. Silakan coba lagi beberapa saat.', 503);
    }

    // ===== Internals =====

    private function _generate_single_pdf(int $id_siswa, string $tasm, bool $force = false)
    {
        $tahun    = substr($tasm, 0, 4);
        $semester = substr($tasm, -1);

        $rapor = $this->db->query("
            SELECT * FROM t_rapor
            WHERE id_siswa = ? AND tahun = ? AND semester = ? AND jenis_rapor = 1
            LIMIT 1
        ", [$id_siswa, $tahun, $semester])->row();
        if (!$rapor) return false;

        $rapor_detail     = $this->db->query("SELECT * FROM t_rapor_detail WHERE id_rapor = ?", [$rapor->id])->result_array();
        $rapor_characters = $this->db->query("SELECT * FROM t_raport_character WHERE id_rapor = ?", [$rapor->id])->result_array();

        // Checksum so we only regenerate if data changed
        $payload  = ['rapor' => $rapor, 'detail' => $rapor_detail, 'characters' => $rapor_characters];
        $checksum = md5(json_encode($payload));

        $safeName  = $this->_safe($rapor->nama ?? 'SISWA');
        $safeKelas = $this->_safe($rapor->kelas ?? 'KELAS');
        $pdfPath   = $this->CACHE_BASE . "{$id_siswa}-{$tasm}-{$safeName}-{$safeKelas}.pdf";
        $metaPath  = $pdfPath . '.json';

        if (!$force && is_file($pdfPath) && is_file($metaPath)) {
            $meta = json_decode(@file_get_contents($metaPath), true);
            if (!empty($meta['checksum']) && $meta['checksum'] === $checksum) {
                return 'skip'; // already up-to-date
            }
        }

        // Render HTML from view (no direct output)
        ob_start();
        $data = (array)$rapor;
        $data['details']    = $rapor_detail;
        $data['characters'] = $rapor_characters;

        if ((int)$rapor->tipe_rapor === 1) {
            $this->load->view('fix_cetak_pts_ikm', $data);
        } else {
            $this->load->view('fix_cetak_pts_sd', $data);
        }
        $html = ob_get_clean();

        // Safety for heavy render
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        // Generate PDF
        $autoload = FCPATH . $this->HTML2PDF_AUTOLOAD;
        if (!is_file($autoload)) {
            log_message('error', 'Html2Pdf autoload not found at: '.$autoload);
            return false;
        }
        require_once $autoload;

        try {
            $pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'UTF-8', ['7mm','7mm','10mm','10mm']);
            $pdf->setTestTdInOnePage(false);
            $pdf->writeHTML($html);

            // Ensure cache base exists
            if (!is_dir($this->CACHE_BASE)) {
                @mkdir($this->CACHE_BASE, 0775, true);
            }

            $pdf->output($pdfPath, 'F');

            // Write meta
            @file_put_contents($metaPath, json_encode([
                'checksum'  => $checksum,
                'generated' => date('c'),
                'id_siswa'  => $id_siswa,
                'tasm'      => $tasm,
            ], JSON_PRETTY_PRINT));

            unset($pdf);
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'PDF gen failed for id_siswa='.$id_siswa.' tasm='.$tasm.' : '.$e->getMessage());
            return false;
        }
    }

    private function _lockPath(string $tasm, int $offset): string
    {
        $dir = sys_get_temp_dir() . '/rapor_cache_locks';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        return $dir . "/pregen_{$tasm}_offset{$offset}.lock";
    }

    private function _safe(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }

    private function _end(string $msg, int $code = 200)
    {
        $this->output->set_status_header($code)
            ->set_content_type('text/plain')
            ->set_output($msg);
    }
}
