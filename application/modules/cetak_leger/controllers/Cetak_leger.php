<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cetak_leger extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');
        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre.'konid');
        $this->d['url'] = "cetak_leger";
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($this->d['tasm'], 0, 4);
        $this->d['sm'] = substr($this->d['tasm'], 4, 1);
        $this->d['wk'] = $this->session->userdata('app_rapot_walikelas');
        $wali = $this->session->userdata($this->sespre."walikelas");
        $this->d['id_kelas'] = $wali['id_walikelas'];
        $this->d['nama_kelas'] = $wali['nama_walikelas'];

        $this->d['dw'] = $this->db->query("select 
                b.nama nmkelas, c.nama nmguru
                from t_walikelas a 
                inner join m_kelas b on a.id_kelas = b.id
                inner join m_guru c on a.id_guru = c.id
                where left(a.tasm,4) = '".$this->d['ta']."' and a.id_kelas = '".$this->d['id_kelas']."'")->row_array();
        
    }
public function index()
{
    $this->d['p'] = "landing";
    $id_kelas = !empty($this->d['id_kelas']) ? $this->d['id_kelas'] : ($this->d['wk']['id_walikelas'] ?? null);

    if (empty($id_kelas)) {
        $this->d['table_midterm'] = '<div class="alert alert-warning">You are not registered as an active homeroom teacher for this semester.</div>';
        $this->d['table_finalterm'] = '<div class="alert alert-warning">You are not registered as an active homeroom teacher for this semester.</div>';
        $this->d['audit_json_mid'] = json_encode([], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->d['audit_json_fin'] = json_encode([], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->load->view("template_utama", $this->d);
        return;
    }

    $this->d['role_info'] = [
        'type' => 'homeroom',
        'badge' => 'Homeroom Teacher',
        'desc' => 'Displaying all subjects for your homeroom classroom (<b>' . ($this->d['dw']['nmkelas'] ?? 'Classroom') . '</b>).'
    ];

    $mid_data = $this->_build_ledger_data($id_kelas, true, null);
    $fin_data = $this->_build_ledger_data($id_kelas, false, null);

    $this->d['jenis_rapor']         = $mid_data['jenis_rapor'];
    $this->d['table_midterm']       = $mid_data['table_summary'];
    $this->d['details_mapel_mid']   = $mid_data['mapel_details_html'];
    $this->d['matrix_midterm']       = $mid_data['matrix_html'];
    $this->d['audit_json_mid']      = json_encode($mid_data['audit_json'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    $this->d['table_finalterm']     = $fin_data['table_summary'];
    $this->d['details_mapel_fin']   = $fin_data['mapel_details_html'];
    $this->d['matrix_finalterm']     = $fin_data['matrix_html'];
    $this->d['audit_json_fin']      = json_encode($fin_data['audit_json'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    $this->d['active_mapels_mid']   = $mid_data['active_mapels'];
    $this->d['active_mapels_fin']   = $fin_data['active_mapels'];

    $this->load->view("template_utama", $this->d);
}

private function _build_ledger_data($id_kelas, $is_pts, $allowed_mapel_ids = null)
{
    $_tasm   = $this->d['tasm'];
    $_ta     = substr($_tasm, 0, 4);
    $sm      = $this->d['sm'];

    $siswa = $this->db->query("
        SELECT a.id_siswa, b.nama, c.tingkat 
        FROM t_kelas_siswa a 
        INNER JOIN m_siswa b ON a.id_siswa = b.id
        INNER JOIN m_kelas c ON a.id_kelas = c.id
        WHERE a.id_kelas = $id_kelas AND a.ta = '$_ta'
        ORDER BY b.nama ASC
    ")->result_array();

    $tahun = $this->db->query("SELECT * FROM tahun WHERE tahun = '$_tasm'")->row();
    $tingkat = $siswa[0]['tingkat'] ?? null;
    $jenis = getJenisRaport($tahun->id ?? 0, $tingkat);
    $jenis_rapor = ($jenis->nama ?? "") == "K13" ? 2 : 1;

    $mapel_all = $this->db->query("SELECT id, kd_singkat, nama, kategori_penilaian FROM m_mapel ORDER BY id ASC")->result_array();

    if ($allowed_mapel_ids !== null) {
        $mapel = array_values(array_filter($mapel_all, fn($m) => in_array((int)$m['id'], $allowed_mapel_ids)));
    } else {
        $mapel = $mapel_all;
    }

    $active_mapel_ids = [];
    $res_amp = $this->db->query("SELECT DISTINCT id_mapel FROM t_guru_mapel WHERE id_kelas = $id_kelas AND tasm LIKE '$_ta%'")->result_array();
    foreach ($res_amp as $row) {
        $mid_int = (int)$row['id_mapel'];
        if ($allowed_mapel_ids === null || in_array($mid_int, $allowed_mapel_ids)) {
            $active_mapel_ids[] = $mid_int;
        }
    }

    $d = [];
    $audit_json = [];
    $mapel_detail_data = [];

    if (!empty($siswa)) {
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];
            $tipe_rapor = $is_pts ? 1 : 2;

            $rapor = $this->db->query("
                SELECT id 
                FROM t_rapor 
                WHERE id_siswa = $id_siswa 
                  AND tahun = '$_ta'
                  AND semester = '$sm'
                  AND tipe_rapor = $tipe_rapor
                LIMIT 1
            ")->row();

            $live = get_live_ledger_scores_siswa($id_siswa, $_tasm, $is_pts, $tingkat, true);
            $is_locked = (!empty($rapor) && !empty($rapor->id));

            if ($is_locked) {
                $rapor_detail = $this->db->query("
                    SELECT kd_singkat, nilai_pengetahuan, nilai_keterampilan
                    FROM t_rapor_detail
                    WHERE id_rapor = $rapor->id
                    ORDER BY kd_singkat ASC
                ")->result_array();

                foreach ($rapor_detail as $rd) {
                    $mapel_match = array_filter($mapel, fn($m) => $m['kd_singkat'] == $rd['kd_singkat']);
                    $mapel_id = !empty($mapel_match) ? array_values($mapel_match)[0]['id'] : null;

                    if ($mapel_id && ($allowed_mapel_ids === null || in_array((int)$mapel_id, $allowed_mapel_ids))) {
                        $d['np'][$id_siswa][$mapel_id] = (int)$rd['nilai_pengetahuan'];
                        $d['nk'][$id_siswa][$mapel_id] = (int)$rd['nilai_keterampilan'];
                    }
                }

                $sum_p = array_sum($d['np'][$id_siswa] ?? []);
                $sum_k = array_sum($d['nk'][$id_siswa] ?? []);
                $d['peringkat'][$id_siswa] = ($jenis_rapor == 1) ? $sum_p : ($sum_p + $sum_k);
            } else {
                foreach ($live['np'] as $mid => $sc) {
                    if ($allowed_mapel_ids === null || in_array((int)$mid, $allowed_mapel_ids)) {
                        $d['np'][$id_siswa][$mid] = $sc;
                    }
                }
                foreach ($live['nk'] as $mid => $sc) {
                    if ($allowed_mapel_ids === null || in_array((int)$mid, $allowed_mapel_ids)) {
                        $d['nk'][$id_siswa][$mid] = $sc;
                    }
                }
                $sum_p = array_sum($d['np'][$id_siswa] ?? []);
                $sum_k = array_sum($d['nk'][$id_siswa] ?? []);
                $d['peringkat'][$id_siswa] = ($jenis_rapor == 1) ? $sum_p : ($sum_p + $sum_k);
            }

            $audit_json[$id_siswa] = [
                'id' => $id_siswa,
                'nama' => $s['nama'],
                'is_locked' => $is_locked,
                'sum_p' => $sum_p,
                'sum_k' => $sum_k,
                'scores' => []
            ];

            foreach ($mapel as $m) {
                $mid = $m['id'];
                $det_p = $live['details_p'][$mid] ?? null;
                $det_k = $live['details_k'][$mid] ?? null;
                $score_p = $d['np'][$id_siswa][$mid] ?? ($live['np'][$mid] ?? 0);
                $score_k = $d['nk'][$id_siswa][$mid] ?? ($live['nk'][$mid] ?? 0);

                $is_active = in_array($mid, $active_mapel_ids) || !empty($det_p['komponen']['ut']) || !empty($det_p['komponen']['mid']) || !empty($det_p['komponen']['final']) || !empty($det_p['komponen']['perf']) || $score_p > 0;

                if ($is_active) {
                    $audit_json[$id_siswa]['scores'][] = [
                        'id_mapel' => $mid,
                        'kd_singkat' => $m['kd_singkat'],
                        'nama_mapel' => $m['nama'],
                        'kategori' => $det_p['kategori'] ?? $m['kategori_penilaian'],
                        'kategori_label' => $det_p['kategori_label'] ?? '-',
                        'komponen' => $det_p['komponen'] ?? ['ut' => 0, 'buzz' => 0, 'mid' => 0, 'final' => 0, 'perf' => 0],
                        'bobot' => $det_p['bobot'] ?? [],
                        'total_bobot_aktif' => $det_p['total_bobot_aktif'] ?? 100,
                        'langkah_hitung' => $det_p['langkah_hitung'] ?? '-',
                        'rumus_dasar' => $det_p['rumus_dasar'] ?? '-',
                        'penjelasan' => $det_p['penjelasan'] ?? '-',
                        'np' => $score_p,
                        'nk' => $score_k,
                        'nk_langkah' => $det_k['langkah_hitung'] ?? '-'
                    ];

                    if (!isset($mapel_detail_data[$mid])) {
                        $mapel_detail_data[$mid] = [
                            'id' => $mid,
                            'kd_singkat' => $m['kd_singkat'],
                            'nama_mapel' => $m['nama'],
                            'kategori' => $det_p['kategori'] ?? $m['kategori_penilaian'],
                            'kategori_label' => $det_p['kategori_label'] ?? '-',
                            'bobot' => $det_p['bobot'] ?? [],
                            'rumus_dasar' => $det_p['rumus_dasar'] ?? '-',
                            'students' => []
                        ];
                    }

                    $mapel_detail_data[$mid]['students'][$id_siswa] = [
                        'nama' => $s['nama'],
                        'komponen' => $det_p['komponen'] ?? ['ut' => 0, 'buzz' => 0, 'mid' => 0, 'final' => 0, 'perf' => 0],
                        'langkah_hitung' => $det_p['langkah_hitung'] ?? '-',
                        'np' => $score_p,
                        'nk' => $score_k,
                        'nk_langkah' => $det_k['langkah_hitung'] ?? '-'
                    ];
                }
            }
        }
    }

    $sorted_scores = $d['peringkat'] ?? [];
    arsort($sorted_scores);
    $sorted_values = array_values($sorted_scores);

    foreach ($audit_json as $sid => &$aj) {
        $score_for_rank = $d['peringkat'][$sid] ?? 0;
        $peringkat = array_search($score_for_rank, $sorted_values);
        $aj['peringkat'] = ($peringkat === false ? count($sorted_values) : $peringkat) + 1;
    }
    unset($aj);

    // 1. Build Summary Table
    $term_name = $is_pts ? "MIDTERM" : "FINALTERM";
    $term_key = $is_pts ? "mid" : "fin";
    $html = '<p align="left"><b>LEDGER ' . $term_name . '</b><br>
             Classroom : ' . ($this->d['dw']['nmkelas'] ?? '') . ', Homeroom : ' . ($this->d['dw']['nmguru'] ?? '') . ', Academic year ' . $this->d['tasm'] . '
             <hr style="border: solid 1px #000; margin-top: -10px"></p>';

    if ($jenis_rapor == 1) {
        $html .= '<table border="1" class="table table-striped table-bordered table-hover"><thead><tr><th>No</th><th>Student Name</th>';
        foreach ($mapel as $m) { $html .= '<th>' . $m['kd_singkat'] . '</th>'; }
        $html .= '<th>Total</th><th>Rank</th></tr></thead><tbody>';
    } else {
        $html .= '<table border="1" class="table table-striped table-bordered table-hover"><thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Student Name</th>';
        $baris_kedua = '';
        foreach ($mapel as $m) {
            $html .= '<th colspan="2">' . $m['kd_singkat'] . '</th>';
            $baris_kedua .= '<th>K</th><th>S</th>';
        }
        $html .= '<th colspan="3">Total</th><th rowspan="2">Rank</th></tr>
                  <tr>' . $baris_kedua . '<th>K</th><th>S</th><th>Tot</th></tr>
                  </thead><tbody>';
    }

    if (!empty($siswa)) {
        $no = 1;
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];
            $kroscek_btn = ' <button type="button" class="btn btn-xs btn-info pull-right" onclick="bukaModalKroscek(\'' . $term_key . '\', ' . $id_siswa . ')" style="padding:1px 6px; font-size:11px; margin-left:5px;" title="Audit Student Formula & Score"><i class="fa fa-calculator"></i> Audit</button>';
            $html .= '<tr><td class="ctr">' . $no++ . '</td><td>' . $s['nama'] . $kroscek_btn . '</td>';
            $total_p = 0; $total_k = 0;
            foreach ($mapel as $m) {
                $id_mapel = $m['id'];
                $p = $d['np'][$id_siswa][$id_mapel] ?? '-';
                $k = $d['nk'][$id_siswa][$id_mapel] ?? '-';
                if ($jenis_rapor == 1) {
                    $html .= '<td class="ctr">' . $p . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                } else {
                    $html .= '<td class="ctr">' . $p . '</td><td class="ctr">' . $k . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                    $total_k += ($k !== '-' ? (int)$k : 0);
                }
            }
            $score_for_rank = $d['peringkat'][$id_siswa] ?? 0;
            $peringkat = array_search($score_for_rank, $sorted_values);
            $peringkat = ($peringkat === false ? count($sorted_values) : $peringkat) + 1;
            $wr = ($peringkat <= 3) ? 'style="background:yellow;font-weight:bold;"' : '';
            if ($jenis_rapor == 1) {
                $html .= '<td class="ctr"><b>' . $total_p . '</b></td><td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            } else {
                $jml = $total_p + $total_k;
                $html .= '<td class="ctr"><b>' . $total_p . '</b></td>
                          <td class="ctr"><b>' . $total_k . '</b></td>
                          <td class="ctr"><b>' . $jml . '</b></td>
                          <td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            }
        }
    }
    $html .= '</tbody></table>';

    // 2. Build Mapel Detail HTML
    $mapel_detail_html = $this->_render_mapel_detail_html($mapel_detail_data, $is_pts, $term_key);

    // 3. Build Matrix HTML
    $matrix_html = $this->_render_matrix_html($siswa, $mapel, $active_mapel_ids, $mapel_detail_data, $d, $is_pts, $sorted_values, $jenis_rapor);

    return [
        'jenis_rapor' => $jenis_rapor,
        'table_summary' => $html,
        'mapel_details_html' => $mapel_detail_html,
        'matrix_html' => $matrix_html,
        'audit_json' => $audit_json,
        'active_mapels' => array_values($mapel_detail_data)
    ];
}

private function _render_mapel_detail_html($mapel_detail_data, $is_pts, $term_key) {
    if (empty($mapel_detail_data)) {
        return '<div class="alert alert-info">No component score breakdown is available for this period.</div>';
    }

    $html = '<div class="mapel-detail-wrapper">';
    foreach ($mapel_detail_data as $mid => $mdata) {
        $kategori = $mdata['kategori'];
        $badge_style = 'background:#0d6efd; color:#fff;';
        if ($kategori === 'without_written') {
            $badge_style = 'background:#ffc107; color:#212529;';
        } elseif ($kategori === 'with_performance') {
            $badge_style = 'background:#0dcaf0; color:#212529;';
        }

        $html .= '<div class="card mapel-detail-box mapel-detail-box-' . $term_key . '" id="detail_box_' . $term_key . '_' . $mid . '" style="border: 1px solid #ced4da; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
        
        // Header
        $html .= '<div style="background: #f8f9fa; padding: 12px 18px; border-bottom: 1px solid #dee2e6; border-radius: 8px 8px 0 0;">';
        $html .= '<div class="row" style="margin:0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">';
        $html .= '<div>';
        $html .= '<h4 style="margin:0; font-weight:700; color:#333;">' . htmlspecialchars($mdata['nama_mapel']) . ' (' . htmlspecialchars($mdata['kd_singkat']) . ')</h4>';
        $html .= '<span class="badge" style="' . $badge_style . ' margin-top:4px; font-size:11px; padding:4px 8px;">' . htmlspecialchars($mdata['kategori_label']) . '</span>';
        $html .= '</div>';
        $html .= '<div style="text-align:right;">';
        $html .= '<small style="color:#6c757d; display:block;">Official SOP Assessment Weighting:</small>';
        $html .= '<span style="font-weight:700; color:#198754; font-size:12px;">' . htmlspecialchars($mdata['rumus_dasar']) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Table
        $html .= '<div class="table-responsive" style="padding:0;">';
        $html .= '<table class="table table-bordered table-striped table-hover" style="margin:0; font-size:12px;">';
        $html .= '<thead><tr style="background:#f1f3f5; color:#495057;">';
        $html .= '<th style="width:35px; text-align:center;">No</th>';
        $html .= '<th style="min-width:180px;">Student Name</th>';
        $html .= '<th style="width:65px; text-align:center;" title="Average of Daily Unit Tests / KD">UT (h)</th>';
        $html .= '<th style="width:65px; text-align:center;" title="Digital Assignments from Agilix Buzz LMS">Buzz (b)</th>';
        if ($kategori !== 'without_written') {
            $html .= '<th style="width:65px; text-align:center;" title="Mid-Semester Exam">Mid (t)</th>';
            if (!$is_pts) {
                $html .= '<th style="width:65px; text-align:center;" title="Final Semester / End-of-Year Exam">Final (a)</th>';
            }
        }
        if ($kategori === 'with_performance') {
            $html .= '<th style="width:75px; text-align:center;" title="Practical Test Score">Practical (p)</th>';
        } elseif ($kategori === 'without_written') {
            $html .= '<th style="width:75px; text-align:center;" title="Alternative Assessment Score">Alternative (p)</th>';
        }
        $html .= '<th style="min-width:320px;">Step-by-Step Mathematical Calculation</th>';
        $html .= '<th style="width:70px; text-align:center;">Knowledge (NP)</th>';
        $html .= '<th style="width:70px; text-align:center;">Skills (NK)</th>';
        $html .= '</tr></thead><tbody>';

        $no = 1;
        foreach ($mdata['students'] as $id_s => $st) {
            $html .= '<tr>';
            $html .= '<td style="text-align:center;">' . $no++ . '</td>';
            $html .= '<td style="font-weight:600;">' . htmlspecialchars($st['nama']) . '</td>';

            // UT
            $ut_val = $st['komponen']['ut'] > 0 ? $st['komponen']['ut'] : '<span class="text-muted">0</span>';
            $html .= '<td style="text-align:center;">' . $ut_val . '</td>';

            // Buzz
            $buzz_val = $st['komponen']['buzz'] > 0 ? $st['komponen']['buzz'] : '<span class="text-muted" title="Not synced / 0">-</span>';
            $html .= '<td style="text-align:center;">' . $buzz_val . '</td>';

            // Mid & Final
            if ($kategori !== 'without_written') {
                $mid_val = $st['komponen']['mid'] > 0 ? $st['komponen']['mid'] : '<span class="text-muted">0</span>';
                $html .= '<td style="text-align:center;">' . $mid_val . '</td>';
                if (!$is_pts) {
                    $final_val = $st['komponen']['final'] > 0 ? $st['komponen']['final'] : '<span class="text-muted">0</span>';
                    $html .= '<td style="text-align:center;">' . $final_val . '</td>';
                }
            }

            // Perf / Alt
            if ($kategori === 'with_performance' || $kategori === 'without_written') {
                $perf_val = $st['komponen']['perf'] > 0 ? $st['komponen']['perf'] : '<span class="text-muted">0</span>';
                $html .= '<td style="text-align:center;">' . $perf_val . '</td>';
            }

            // Langkah Rumus
            $html .= '<td style="font-family: Consolas, monospace; font-size:11px; color:#212529;">' . htmlspecialchars($st['langkah_hitung']) . '</td>';

            // NP
            $np_val = (int)$st['np'];
            $np_badge = ($np_val >= 75) ? 'background:#d1e7dd; color:#0f5132; font-weight:bold;' : 'background:#fff3cd; color:#664d03; font-weight:bold;';
            $html .= '<td style="text-align:center;"><span class="badge" style="' . $np_badge . ' font-size:12px; padding:3px 6px;">' . $np_val . '</span></td>';

            // NK
            $nk_val = (int)$st['nk'];
            $html .= '<td style="text-align:center;"><span class="badge" style="background:#e2e3e5; color:#41464b; font-size:12px; padding:3px 6px;" title="' . htmlspecialchars($st['nk_langkah']) . '">' . ($nk_val > 0 ? $nk_val : '-') . '</span></td>';

            $html .= '</tr>';
        }

        $html .= '</tbody></table></div></div>';
    }
    $html .= '</div>';
    return $html;
}

private function _render_matrix_html($siswa, $mapel, $active_mapel_ids, $mapel_detail_data, $d, $is_pts, $sorted_values, $jenis_rapor) {
    if (empty($siswa) || empty($mapel_detail_data)) {
        return '<div class="alert alert-info">No component matrix data available for this class.</div>';
    }

    $active_mapels = [];
    foreach ($mapel as $m) {
        if (isset($mapel_detail_data[$m['id']])) {
            $active_mapels[] = $m;
        }
    }

    $html = '<div class="table-responsive" style="overflow-x:auto;">';
    $html .= '<table class="table table-bordered table-striped table-hover" style="font-size:11px; white-space:nowrap;">';
    $html .= '<thead>';
    $html .= '<tr style="background:#e9ecef; color:#212529;">';
    $html .= '<th rowspan="2" style="vertical-align:middle; text-align:center; width:30px;">No</th>';
    $html .= '<th rowspan="2" style="vertical-align:middle; min-width:160px;">Student Name</th>';
    
    $sub_headers = '';
    foreach ($active_mapels as $m) {
        $mid = $m['id'];
        $kat = $mapel_detail_data[$mid]['kategori'] ?? 'without_performance';
        $cols = 5; // UT, Buzz, Mid/Final, Perf, NP
        $html .= '<th colspan="' . $cols . '" style="text-align:center; border-left:2px solid #adb5bd;" title="' . htmlspecialchars($m['nama']) . '">' . htmlspecialchars($m['kd_singkat']) . '</th>';
        $sub_headers .= '<th style="text-align:center; border-left:2px solid #adb5bd; width:35px;" title="Unit Tests / KD">UT</th>';
        $sub_headers .= '<th style="text-align:center; width:35px;" title="Buzz LMS">BZ</th>';
        $sub_headers .= '<th style="text-align:center; width:35px;" title="' . ($is_pts ? 'Midterm' : 'Finalterm') . '">' . ($is_pts ? 'MID' : 'FIN') . '</th>';
        $sub_headers .= '<th style="text-align:center; width:35px;" title="Practical / Alternative">PRF</th>';
        $sub_headers .= '<th style="text-align:center; width:35px; background:#e2e3e5; font-weight:bold;" title="Final Knowledge Score">TOT</th>';
    }

    $html .= '<th rowspan="2" style="vertical-align:middle; text-align:center; background:#d1e7dd; font-weight:bold;">Total</th>';
    $html .= '<th rowspan="2" style="vertical-align:middle; text-align:center; background:#cff4fc; font-weight:bold;">Rank</th>';
    $html .= '</tr>';
    $html .= '<tr style="background:#f8f9fa;">' . $sub_headers . '</tr>';
    $html .= '</thead><tbody>';

    $no = 1;
    foreach ($siswa as $s) {
        $id_siswa = $s['id_siswa'];
        $html .= '<tr>';
        $html .= '<td style="text-align:center;">' . $no++ . '</td>';
        $html .= '<td style="font-weight:600;">' . htmlspecialchars($s['nama']) . '</td>';

        $total_p = 0;
        foreach ($active_mapels as $m) {
            $mid = $m['id'];
            $st_data = $mapel_detail_data[$mid]['students'][$id_siswa] ?? null;
            $ut = $st_data['komponen']['ut'] ?? 0;
            $bz = $st_data['komponen']['buzz'] ?? 0;
            $mid_val = $is_pts ? ($st_data['komponen']['mid'] ?? 0) : ($st_data['komponen']['final'] ?? 0);
            $prf = $st_data['komponen']['perf'] ?? 0;
            $np = (int)($st_data['np'] ?? 0);
            $total_p += $np;

            $html .= '<td style="text-align:center; border-left:2px solid #adb5bd;">' . ($ut > 0 ? $ut : '<span class="text-muted">0</span>') . '</td>';
            $html .= '<td style="text-align:center;">' . ($bz > 0 ? $bz : '<span class="text-muted">-</span>') . '</td>';
            $html .= '<td style="text-align:center;">' . ($mid_val > 0 ? $mid_val : '<span class="text-muted">0</span>') . '</td>';
            $html .= '<td style="text-align:center;">' . ($prf > 0 ? $prf : '<span class="text-muted">-</span>') . '</td>';
            $html .= '<td style="text-align:center; background:#f8f9fa; font-weight:bold;">' . $np . '</td>';
        }

        $score_for_rank = $d['peringkat'][$id_siswa] ?? $total_p;
        $peringkat = array_search($score_for_rank, $sorted_values);
        $peringkat = ($peringkat === false ? count($sorted_values) : $peringkat) + 1;
        $wr = ($peringkat <= 3) ? 'style="background:#fff3cd; font-weight:bold; text-align:center;"' : 'style="text-align:center;"';

        $html .= '<td style="text-align:center; font-weight:bold; background:#e8f5e9;">' . $total_p . '</td>';
        $html .= '<td ' . $wr . '>' . $peringkat . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
}


public function cetak()
{
   // --- Detect parameters ---
    $uri3 = $this->uri->segment(3); // could be tasm or 'print'/'excel'
    $uri4 = $this->uri->segment(4); // optional mode if tasm is given

    // Determine tasm and output mode properly
    if ($uri3 == "print" || $uri3 == "excel" || empty($uri3)) {
        $_tasm = $this->d['tasm'];   // use default semester
        $mauke = $uri3 ?: 'view';    // empty means normal preview
    } else {
        $_tasm = $uri3;              // tasm code like 20251
        $mauke = $uri4 ?: 'view';    // optional print/excel
    }
    $_ta = substr($_tasm, 0, 4);
    $id_kelas = $this->d['wk']['id_walikelas'];

    // --- Get students (sorted alphabetically)
    $siswa = $this->db->query("
        SELECT a.id_siswa, b.nama, c.tingkat 
        FROM t_kelas_siswa a 
        INNER JOIN m_siswa b ON a.id_siswa = b.id
        INNER JOIN m_kelas c ON a.id_kelas = c.id
        WHERE a.id_kelas = $id_kelas AND a.ta = '$_ta'
        ORDER BY b.nama ASC
    ")->result_array();

    // --- Determine jenis_rapor (Kurmer or K13)
    $tahun = $this->db->query("SELECT * FROM tahun WHERE tahun = '" . $this->d['tasm'] . "'")->row();
    $tingkat = $siswa[0]['tingkat'] ?? null;
    $jenis = getJenisRaport($tahun->id, $tingkat);
    $jenis_rapor = ($jenis->nama ?? "") == "K13" ? 2 : 1; // 1 = Kurmer, 2 = K13

    // --- Get subject list
    $mapel = $this->db->query("SELECT id, kd_singkat FROM m_mapel ORDER BY id ASC")->result_array();
    $d = [];

    // --- Loop through students
    if (!empty($siswa)) {
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];

            // Step 1. Check if student already has stored rapor
            $rapor = $this->db->query("
                SELECT id 
                FROM t_rapor 
                WHERE id_siswa = $id_siswa 
                  AND tahun = '" . $this->d['ta'] . "'
                  AND semester = '" . $this->d['sm'] . "'
                LIMIT 1
            ")->row();

            if (!empty($rapor)) {
                // Step 2. Load stored rapor_detail values
                $rapor_detail = $this->db->query("
                    SELECT kd_singkat, nilai_pengetahuan, nilai_keterampilan
                    FROM t_rapor_detail
                    WHERE id_rapor = $rapor->id
                    ORDER BY kd_singkat ASC
                ")->result_array();

                foreach ($rapor_detail as $rd) {
                    $mapel_match = array_filter($mapel, fn($m) => $m['kd_singkat'] == $rd['kd_singkat']);
                    $mapel_id = !empty($mapel_match) ? array_values($mapel_match)[0]['id'] : null;

                    if ($mapel_id) {
                        $d['np'][$id_siswa][$mapel_id] = (int)$rd['nilai_pengetahuan'];
                        $d['nk'][$id_siswa][$mapel_id] = (int)$rd['nilai_keterampilan'];
                    }
                }

                $sum_p = array_sum($d['np'][$id_siswa] ?? []);
                $sum_k = array_sum($d['nk'][$id_siswa] ?? []);
                $d['peringkat'][$id_siswa] = $sum_p + $sum_k;
            } else {
                // Step 3. Fallback live calculation sesuai SOP
                $live = get_live_ledger_scores_siswa($id_siswa, $_tasm, true, $tingkat);
                foreach ($live['np'] as $mid => $sc) {
                    $d['np'][$id_siswa][$mid] = $sc;
                }
                foreach ($live['nk'] as $mid => $sc) {
                    $d['nk'][$id_siswa][$mid] = $sc;
                }
                $sum_p = array_sum($d['np'][$id_siswa] ?? []);
                $sum_k = array_sum($d['nk'][$id_siswa] ?? []);
                $d['peringkat'][$id_siswa] = ($jenis_rapor == 1) ? $sum_p : ($sum_p + $sum_k);
            }
        }
    }

    // --- Build ranking
    $sorted_scores = $d['peringkat'] ?? [];
    arsort($sorted_scores);
    $sorted_values = array_values($sorted_scores);

    // --- HTML Header
    $html = '<p align="left"><b>LEDGER MIDTERM ' . ($jenis_rapor == 1 ? 'MERDEKA CURRICULUM' : 'K13') . '</b><br>
             Classroom : ' . $this->d['dw']['nmkelas'] . ', Homeroom : ' . $this->d['dw']['nmguru'] . ', Academic Year ' . $this->d['tasm'] . '
             <hr style="border: solid 1px #000; margin-top: -10px"></p>';

    // --- Table structure
    if ($jenis_rapor == 1) {
        $html .= '<table border="1" class="table"><thead><tr><th>No</th><th>Student Name</th>';
        foreach ($mapel as $m) {
            $html .= '<th>' . $m['kd_singkat'] . '</th>';
        }
        $html .= '<th>Total</th><th>Rank</th></tr></thead><tbody>';
    } else {
        $html .= '<table border="1" class="table"><thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Student Name</th>';
        $baris_kedua = '';
        foreach ($mapel as $m) {
            $html .= '<th colspan="2">' . $m['kd_singkat'] . '</th>';
            $baris_kedua .= '<th>K</th><th>S</th>';
        }
        $html .= '<th colspan="3">Total</th><th rowspan="2">Rank</th></tr>
                  <tr>' . $baris_kedua . '<th>K</th><th>S</th><th>Tot</th></tr>
                  </thead><tbody>';
    }

    // --- Fill rows
    if (!empty($siswa)) {
        $no = 1;
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];
            $html .= '<tr><td class="ctr">' . $no++ . '</td><td>' . $s['nama'] . '</td>';

            $total_p = 0;
            $total_k = 0;

            foreach ($mapel as $m) {
                $id_mapel = $m['id'];
                $p = $d['np'][$id_siswa][$id_mapel] ?? '-';
                $k = $d['nk'][$id_siswa][$id_mapel] ?? '-';

                if ($jenis_rapor == 1) {
                    $html .= '<td class="ctr">' . $p . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                } else {
                    $html .= '<td class="ctr">' . $p . '</td><td class="ctr">' . $k . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                    $total_k += ($k !== '-' ? (int)$k : 0);
                }
            }

            $score_for_rank = $d['peringkat'][$id_siswa] ?? 0;
            $peringkat = array_search($score_for_rank, $sorted_values);
            $peringkat = ($peringkat === false ? count($sorted_values) : $peringkat) + 1;
            $wr = ($peringkat <= 3) ? 'style="background:yellow;font-weight:bold;"' : '';

            if ($jenis_rapor == 1) {
                $html .= '<td class="ctr"><b>' . $total_p . '</b></td><td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            } else {
                $jml = $total_p + $total_k;
                $html .= '<td class="ctr"><b>' . $total_p . '</b></td>
                          <td class="ctr"><b>' . $total_k . '</b></td>
                          <td class="ctr"><b>' . $jml . '</b></td>
                          <td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            }
        }
    }

    $html .= '</tbody></table>';

    // --- Output
    $d['html'] = $html;
    $d['teks_tasm'] = "Academic Year " . $this->d['ta'] . "/" . ($this->d['ta'] + 1) . ", Semester " . $this->d['sm'];

// === Output Section ===
if ($mauke == "excel") {
    $filename = "leger_" . $_tasm . "_" . date('YmdHis') . ".xls";
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: application/vnd.ms-excel");
    echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo $d['html'];
    echo '</html>';
    exit;
}

if ($mauke == "print" || $mauke == "view") {
    $this->load->view('cetak', $d);
}


}

    public function cetak_ekstra() {

        $data = array();
        $q_d_ekstra = $this->db->query("SELECT * FROM m_ekstra ORDER BY id ASC");
        $j_d_ekstra = $q_d_ekstra->num_rows();
        $d_d_ekstra = $q_d_ekstra->result_array();
        $q_absensi = $this->db->query("SELECT
                                        a.id, a.id_siswa, c.nama,a.s, a.i, a.a
                                        FROM t_nilai_absensi a
                                        LEFT JOIN t_kelas_siswa b ON a.id_siswa = b.id_siswa
                                        LEFT JOIN m_siswa c ON b.id_siswa = c.id
                                        WHERE b.id_kelas = '".$this->d['id_kelas']."' AND a.tasm = '".$this->d['tasm']."'")->result_array();
        $q_ekstra = $this->db->query("SELECT 
                                        a.*
                                        FROM t_nilai_ekstra a 
                                        INNER JOIN t_kelas_siswa b ON a.id_siswa = b.id_siswa
                                        WHERE b.id_kelas = '".$this->d['id_kelas']."' AND a.tasm = '".$this->d['tasm']."'")->result_array();
        if (!empty($q_absensi)) {
            foreach ($q_absensi as $d) {
                $idx = $d['id_siswa'];
                $data[$idx]['nama'] = $d['nama'];
                $data[$idx]['absensi']['s'] = $d['s'];
                $data[$idx]['absensi']['i'] = $d['i'];
                $data[$idx]['absensi']['a'] = $d['a'];
            }
        }
        if (!empty($q_ekstra)) {
            foreach ($q_ekstra as $e) {
                $idx = $e['id_siswa'];
                $idx_id_ekstra = $e['id_ekstra'];
                $data[$idx]['ekstra'][$idx_id_ekstra]['nilai'] = $e['nilai'];
                $data[$idx]['ekstra'][$idx_id_ekstra]['desk'] = $e['desk'];
            }
        }
        


        $html = '<p align="left"><b>EXTRACURRICULAR & ATTENDANCE LEDGER</b>
                <br>
                Classroom : '.$this->d['dw']['nmkelas'].', Homeroom : '.$this->d['dw']['nmguru'].', Academic Year '.$this->d['tasm'].'<hr style="border: solid 1px #000; margin-top: -10px"></p>';

        $html .= '<table class="table"><thead><tr><th rowspan="2">No</th><th rowspan="2">Student Name</th><th colspan="4">Attendance</th><th colspan="'.$j_d_ekstra.'">Extracurricular</th></tr><tr><th title="Sick">S</th><th title="Permit">I</th><th title="Unexcused">A</th><th>Tot</th>';
        $arr_id_ekstra = array();
        if (!empty($d_d_ekstra)) {
            foreach ($d_d_ekstra as $k) {
                $html .= '<th>'.$k['nama'].'</th>';
                $arr_id_ekstra[] = $k['id'];
            }
        }
        $no = 1;
        foreach ($data as $k => $v) {
            $html .= '<tr><td class="ct"r>'.$no++.'</td><td>'.$v['nama'].'</td>';
            $jml_absen = $data[$k]['absensi']['s']+$data[$k]['absensi']['i']+$data[$k]['absensi']['a'];
            $html .= '<td class="ctr">'.$data[$k]['absensi']['s'].'</td><td class="ctr">'.$data[$k]['absensi']['i'].'</td><td class="ctr">'.$data[$k]['absensi']['a'].'</td><td class="ctr">'.$jml_absen.'</td>'; 
            if (!empty($arr_id_ekstra)) {
                foreach ($arr_id_ekstra as $e) {
                    $nekstra = !empty($data[$k]['ekstra'][$e]['nilai']) ? $data[$k]['ekstra'][$e]['nilai'].' ('.$data[$k]['ekstra'][$e]['desk'].')' : "-";

                    $html .= '<td>'.$nekstra.'</td>';
                }
            }
        }
        $html .= '</table>';
        
        $this->d['html'] = $html;
        $this->load->view('cetak_ekstra', $this->d);
    }
}