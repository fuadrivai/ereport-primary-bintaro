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
    // --- View name ---
    $this->d['p'] = "landing";

    // --- Shared params ---
    $_tasm   = $this->d['tasm'];
    $_ta     = substr($_tasm, 0, 4);
    $id_kelas = $this->d['wk']['id_walikelas'];

    // --- Students (A-Z) ---
    $siswa = $this->db->query("
        SELECT a.id_siswa, b.nama, c.tingkat 
        FROM t_kelas_siswa a 
        INNER JOIN m_siswa b ON a.id_siswa = b.id
        INNER JOIN m_kelas c ON a.id_kelas = c.id
        WHERE a.id_kelas = $id_kelas AND a.ta = '$_ta'
        ORDER BY b.nama ASC
    ")->result_array();

    // --- Jenis rapor (1=Kurmer, 2=K13) ---
    $tahun   = $this->db->query("SELECT * FROM tahun WHERE tahun = '" . $this->d['tasm'] . "'")->row();
    $tingkat = $siswa[0]['tingkat'] ?? null;
    $jenis   = getJenisRaport($tahun->id, $tingkat);
    $jenis_rapor = ($jenis->nama ?? "") == "K13" ? 2 : 1;
    $this->d['jenis_rapor'] = $jenis_rapor;

    // --- Subjects ---
    $mapel = $this->db->query("SELECT id, kd_singkat FROM m_mapel ORDER BY id ASC")->result_array();
    $this->d['mapel'] = $mapel;

    /* =========================================================
       MIDTERM (kept EXACTLY as your approved logic)
       ========================================================= */
    $d = [];
    if (!empty($siswa)) {
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];

            $rapor = $this->db->query("
                SELECT id 
                FROM t_rapor 
                WHERE id_siswa = $id_siswa 
                  AND tahun = '" . $this->d['ta'] . "'
                  AND semester = '" . $this->d['sm'] . "'
                  AND tipe_rapor = 1
                LIMIT 1
            ")->row();

            if (!empty($rapor)) {
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
                // Kurmer or K13 (MIDTERM)
                if ($jenis_rapor == 1) {
                    if (in_array($tingkat, [1, 2])) {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL(
                                        (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                        (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))
                                    )
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            LEFT JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0 AND d.mid_final=1
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                        
                    } else {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * IFNULL((SUM(IF(a.jenis='h', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='h', 1, NULL)), 0)), 0)) +
                                    IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='t', 1, NULL)), 0)), 0)
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    }

                    $total = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $total += (int)$np['na'];
                    }
                    $d['peringkat'][$id_siswa] = $total;
                } else {
                    if (in_array($tingkat, [1, 2])) {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL(
                                        (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                        (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))
                                    )
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            LEFT JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0 AND d.mid_final=1
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    } else {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * IFNULL((SUM(IF(a.jenis='h', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='h', 1, NULL)), 0)), 0)) +
                                    IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='t', 1, NULL)), 0)), 0)
                                ) / 3
                            ), 0) AS na

                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            LEFT JOIN t_mapel_kd d ON a.id_mapel_kd = d.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0 AND d.mid_final=1
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    }

                    $n_keterampilan = $this->db->query("
                        SELECT c.id, ROUND(AVG(a.nilai),0) AS na
                        FROM t_nilai_ket a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm'
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();

                    $sum = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $sum += (int)$np['na'];
                    }
                    foreach ($n_keterampilan as $nk) {
                        $d['nk'][$id_siswa][$nk['id']] = (int)$nk['na'];
                        $sum += (int)$nk['na'];
                    }
                    $d['peringkat'][$id_siswa] = $sum;
                }
            }
        }
    }

    // Ranking (midterm)
    $sorted_scores = $d['peringkat'] ?? [];
    arsort($sorted_scores);
    $sorted_values = array_values($sorted_scores);

    // HTML (midterm)
    $htmlMid = '<p align="left"><b>LEDGER MIDTERM</b><br>
             Classroom : ' . $this->d['dw']['nmkelas'] . ', Homeroom : ' . $this->d['dw']['nmguru'] . ', Academic year ' . $this->d['tasm'] . '
             <hr style="border: solid 1px #000; margin-top: -10px"></p>';

    if ($jenis_rapor == 1) {
        $htmlMid .= '<table border="1" class="table table-striped"><thead><tr><th>No</th><th>Nama Siswa</th>';
        foreach ($mapel as $m) { $htmlMid .= '<th>' . $m['kd_singkat'] . '</th>'; }
        $htmlMid .= '<th>Jumlah</th><th>Peringkat</th></tr></thead><tbody>';
    } else {
        $htmlMid .= '<table border="1" class="table table-striped"><thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Nama Siswa</th>';
        $baris_kedua = '';
        foreach ($mapel as $m) {
            $htmlMid .= '<th colspan="2">' . $m['kd_singkat'] . '</th>';
            $baris_kedua .= '<th>P</th><th>K</th>';
        }
        $htmlMid .= '<th colspan="3">Jumlah</th><th rowspan="2">Peringkat</th></tr>
                  <tr>' . $baris_kedua . '<th>P</th><th>K</th><th>Jml</th></tr>
                  </thead><tbody>';
    }

    if (!empty($siswa)) {
        $no = 1;
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];
            $htmlMid .= '<tr><td class="ctr">' . $no++ . '</td><td>' . $s['nama'] . '</td>';
            $total_p = 0; $total_k = 0;
            foreach ($mapel as $m) {
                $id_mapel = $m['id'];
                $p = $d['np'][$id_siswa][$id_mapel] ?? '-';
                $k = $d['nk'][$id_siswa][$id_mapel] ?? '-';
                if ($jenis_rapor == 1) {
                    $htmlMid .= '<td class="ctr">' . $p . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                } else {
                    $htmlMid .= '<td class="ctr">' . $p . '</td><td class="ctr">' . $k . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                    $total_k += ($k !== '-' ? (int)$k : 0);
                }
            }
            $score_for_rank = $d['peringkat'][$id_siswa] ?? 0;
            $peringkat = array_search($score_for_rank, $sorted_values);
            $peringkat = ($peringkat === false ? count($sorted_values) : $peringkat) + 1;
            $wr = ($peringkat <= 3) ? 'style="background:yellow;font-weight:bold;"' : '';
            if ($jenis_rapor == 1) {
                $htmlMid .= '<td class="ctr"><b>' . $total_p . '</b></td><td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            } else {
                $jml = $total_p + $total_k;
                $htmlMid .= '<td class="ctr"><b>' . $total_p . '</b></td>
                          <td class="ctr"><b>' . $total_k . '</b></td>
                          <td class="ctr"><b>' . $jml . '</b></td>
                          <td class="ctr" ' . $wr . '>' . $peringkat . '</td></tr>';
            }
        }
    }
    $htmlMid .= '</tbody></table>';

    $this->d['table_midterm'] = $htmlMid;

    /* =========================================================
       FINALTERM (same logic, only P uses: (2*h + t + a)/4
       ========================================================= */
    $d2 = [];
    if (!empty($siswa)) {
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];

            $rapor = $this->db->query("
                SELECT id 
                FROM t_rapor 
                WHERE id_siswa = $id_siswa 
                  AND tahun = '" . $this->d['ta'] . "'
                  AND semester = '" . $this->d['sm'] . "'
                  AND tipe_rapor = 2
                LIMIT 1
            ")->row();

            if (!empty($rapor)) {
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
                        $d2['np'][$id_siswa][$mapel_id] = (int)$rd['nilai_pengetahuan'];
                        $d2['nk'][$id_siswa][$mapel_id] = (int)$rd['nilai_keterampilan'];
                    }
                }

                $sum_p = array_sum($d2['np'][$id_siswa] ?? []);
                $sum_k = array_sum($d2['nk'][$id_siswa] ?? []);
                $d2['peringkat'][$id_siswa] = $sum_p + $sum_k;
            } else {
                // Kurmer or K13 (FINALTERM) — ONLY change is adding 'a' into P formula
                if ($jenis_rapor == 1) {
                    if (in_array($tingkat, [1, 2])) {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                           (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL((SUM(IF(a.jenis='a', a.nilai, NULL)) / COUNT(IF(a.jenis='a', 1, NULL))),
                                           (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL))))
                                ) / 4
                            ), 0) AS na

                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();                   
                    }else{
                    $n_pengetahuan = $this->db->query("
                        SELECT c.id,
                        ROUND((
                            (
                                (2 * IFNULL((SUM(IF(a.jenis='h', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='h', 1, NULL)), 0)), 0)) +
                                IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='t', 1, NULL)), 0)), 0) +
                                IFNULL((SUM(IF(a.jenis='a', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='a', 1, NULL)), 0)), 0)
                            ) / 4
                        ), 0) AS na
                        FROM t_nilai a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();
                    }
                    $total = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d2['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $total += (int)$np['na'];
                    }
                    $d2['peringkat'][$id_siswa] = $total;
                } else {
                    if (in_array($tingkat, [1, 2])) {
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                           (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL((SUM(IF(a.jenis='a', a.nilai, NULL)) / COUNT(IF(a.jenis='a', 1, NULL))),
                                           (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL))))
                                ) / 4
                            ), 0) AS na

                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();                   
                    }else{
                    $n_pengetahuan = $this->db->query("
                        SELECT c.id,
                        ROUND((
                            (
                                (2 * IFNULL((SUM(IF(a.jenis='h', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='h', 1, NULL)), 0)), 0)) +
                                IFNULL((SUM(IF(a.jenis='t', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='t', 1, NULL)), 0)), 0) +
                                IFNULL((SUM(IF(a.jenis='a', a.nilai, NULL)) / NULLIF(COUNT(IF(a.jenis='a', 1, NULL)), 0)), 0)
                            ) / 4
                        ), 0) AS na
                        FROM t_nilai a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();
                    }
                    $n_keterampilan = $this->db->query("
                        SELECT c.id, ROUND(AVG(a.nilai),0) AS na
                        FROM t_nilai_ket a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm'
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();

                    $sum = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d2['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $sum += (int)$np['na'];
                    }
                    foreach ($n_keterampilan as $nk) {
                        $d2['nk'][$id_siswa][$nk['id']] = (int)$nk['na'];
                        $sum += (int)$nk['na'];
                    }
                    $d2['peringkat'][$id_siswa] = $sum;
                }
            }
        }
    }

    // Ranking (finalterm)
    $sorted_scores2 = $d2['peringkat'] ?? [];
    arsort($sorted_scores2);
    $sorted_values2 = array_values($sorted_scores2);

    // HTML (finalterm)
    $htmlFin = '<p align="left"><b>LEDGER FINALTERM</b><br>
             Classroom : ' . $this->d['dw']['nmkelas'] . ', Homeroom : ' . $this->d['dw']['nmguru'] . ', Academic year ' . $this->d['tasm'] . '
             <hr style="border: solid 1px #000; margin-top: -10px"></p>';

    if ($jenis_rapor == 1) {
        $htmlFin .= '<table border="1" class="table table-striped"><thead><tr><th>No</th><th>Nama Siswa</th>';
        foreach ($mapel as $m) { $htmlFin .= '<th>' . $m['kd_singkat'] . '</th>'; }
        $htmlFin .= '<th>Jumlah</th><th>Peringkat</th></tr></thead><tbody>';
    } else {
        $htmlFin .= '<table border="1" class="table table-striped"><thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Nama Siswa</th>';
        $baris_kedua2 = '';
        foreach ($mapel as $m) {
            $htmlFin .= '<th colspan="2">' . $m['kd_singkat'] . '</th>';
            $baris_kedua2 .= '<th>P</th><th>K</th>';
        }
        $htmlFin .= '<th colspan="3">Jumlah</th><th rowspan="2">Peringkat</th></tr>
                  <tr>' . $baris_kedua2 . '<th>P</th><th>K</th><th>Jml</th></tr>
                  </thead><tbody>';
    }

    if (!empty($siswa)) {
        $no2 = 1;
        foreach ($siswa as $s) {
            $id_siswa = $s['id_siswa'];
            $htmlFin .= '<tr><td class="ctr">' . $no2++ . '</td><td>' . $s['nama'] . '</td>';
            $total_p = 0; $total_k = 0;
            foreach ($mapel as $m) {
                $id_mapel = $m['id'];
                $p = $d2['np'][$id_siswa][$id_mapel] ?? '-';
                $k = $d2['nk'][$id_siswa][$id_mapel] ?? '-';
                if ($jenis_rapor == 1) {
                    $htmlFin .= '<td class="ctr">' . $p . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                } else {
                    $htmlFin .= '<td class="ctr">' . $p . '</td><td class="ctr">' . $k . '</td>';
                    $total_p += ($p !== '-' ? (int)$p : 0);
                    $total_k += ($k !== '-' ? (int)$k : 0);
                }
            }
            $score_for_rank2 = $d2['peringkat'][$id_siswa] ?? 0;
            $peringkat2 = array_search($score_for_rank2, $sorted_values2);
            $peringkat2 = ($peringkat2 === false ? count($sorted_values2) : $peringkat2) + 1;
            $wr2 = ($peringkat2 <= 3) ? 'style="background:yellow;font-weight:bold;"' : '';
            if ($jenis_rapor == 1) {
                $htmlFin .= '<td class="ctr"><b>' . $total_p . '</b></td><td class="ctr" ' . $wr2 . '>' . $peringkat2 . '</td></tr>';
            } else {
                $jml2 = $total_p + $total_k;
                $htmlFin .= '<td class="ctr"><b>' . $total_p . '</b></td>
                          <td class="ctr"><b>' . $total_k . '</b></td>
                          <td class="ctr"><b>' . $jml2 . '</b></td>
                          <td class="ctr" ' . $wr2 . '>' . $peringkat2 . '</td></tr>';
            }
        }
    }
    $htmlFin .= '</tbody></table>';

    // Attach both tables to the view
    $this->d['table_midterm']   = $htmlMid;
    $this->d['table_finalterm'] = $htmlFin;

    // Load landing (tabs will show both immediately)
    $this->load->view("template_utama", $this->d);
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
                // Step 3. Fallback to calculation logic
                if ($jenis_rapor == 1) {
                        if (in_array($tingkat, [1, 2])) {
                        // Kurmer for Grade 1 & 2 — skip test if missing
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL(
                                        (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                        (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))
                                    )
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    } else {
                        // Kurmer for Grade 3+ — require test (if no test, result lower)
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL)))
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    }

                    $total = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $total += (int)$np['na'];
                    }
                    $d['peringkat'][$id_siswa] = $total;
                } else {
                    // K13: Pengetahuan + Keterampilan
                        if (in_array($tingkat, [1, 2])) {
                        // Kurmer for Grade 1 & 2 — skip test if missing
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    IFNULL(
                                        (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL))),
                                        (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))
                                    )
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    } else {
                        // Kurmer for Grade 3+ — require test (if no test, result lower)
                        $n_pengetahuan = $this->db->query("
                            SELECT c.id,
                            ROUND((
                                (
                                    (2 * (SUM(IF(a.jenis='h', a.nilai, NULL)) / COUNT(IF(a.jenis='h', 1, NULL)))) +
                                    (SUM(IF(a.jenis='t', a.nilai, NULL)) / COUNT(IF(a.jenis='t', 1, NULL)))
                                ) / 3
                            ), 0) AS na
                            FROM t_nilai a
                            LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                            LEFT JOIN m_mapel c ON b.id_mapel = c.id
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    }

                    $n_keterampilan = $this->db->query("
                        SELECT c.id, ROUND(AVG(a.nilai),0) AS na
                        FROM t_nilai_ket a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm'
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();

                    $sum = 0;
                    foreach ($n_pengetahuan as $np) {
                        $d['np'][$id_siswa][$np['id']] = (int)$np['na'];
                        $sum += (int)$np['na'];
                    }
                    foreach ($n_keterampilan as $nk) {
                        $d['nk'][$id_siswa][$nk['id']] = (int)$nk['na'];
                        $sum += (int)$nk['na'];
                    }
                    $d['peringkat'][$id_siswa] = $sum;
                }
            }
        }
    }

    // --- Build ranking
    $sorted_scores = $d['peringkat'] ?? [];
    arsort($sorted_scores);
    $sorted_values = array_values($sorted_scores);

    // --- HTML Header
    $html = '<p align="left"><b>LEDGER MIDTERM ' . ($jenis_rapor == 1 ? 'KURIKULUM MERDEKA' : 'K13') . '</b><br>
             Classroom : ' . $this->d['dw']['nmkelas'] . ', Homeroom : ' . $this->d['dw']['nmguru'] . ', Academic Year ' . $this->d['tasm'] . '
             <hr style="border: solid 1px #000; margin-top: -10px"></p>';

    // --- Table structure
    if ($jenis_rapor == 1) {
        $html .= '<table border="1" class="table"><thead><tr><th>No</th><th>Nama Siswa</th>';
        foreach ($mapel as $m) {
            $html .= '<th>' . $m['kd_singkat'] . '</th>';
        }
        $html .= '<th>Jumlah</th><th>Peringkat</th></tr></thead><tbody>';
    } else {
        $html .= '<table border="1" class="table"><thead>
                    <tr><th rowspan="2">No</th><th rowspan="2">Nama Siswa</th>';
        $baris_kedua = '';
        foreach ($mapel as $m) {
            $html .= '<th colspan="2">' . $m['kd_singkat'] . '</th>';
            $baris_kedua .= '<th>P</th><th>K</th>';
        }
        $html .= '<th colspan="3">Jumlah</th><th rowspan="2">Peringkat</th></tr>
                  <tr>' . $baris_kedua . '<th>P</th><th>K</th><th>Jml</th></tr>
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
    $d['teks_tasm'] = "Tahun Ajaran " . $this->d['ta'] . "/" . ($this->d['ta'] + 1) . ", Semester " . $this->d['sm'];

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
        


        $html = '<p align="left"><b>LEGER NILAI EKSTRAKURIKULER & ABSENSI</b>
                <br>
                Kelas : '.$this->d['dw']['nmkelas'].', Nama Wali : '.$this->d['dw']['nmguru'].', Tahun Pelajaran '.$this->d['tasm'].'<hr style="border: solid 1px #000; margin-top: -10px"></p>';

        $html .= '<table class="table"><thead><tr><th rowspan="2">No</th><th rowspan="2">Nama</th><th colspan="4">Absensi</th><th colspan="'.$j_d_ekstra.'">Ekstrakurikuler</th></tr><tr><th>S</th><th>I</th><th>A</th><th>Jml</th>';
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