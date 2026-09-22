<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class View_ledger extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');

        $this->d['admlevel'] = $this->session->userdata($this->sespre.'level');
        $this->d['url'] = "view_ledger";
        $this->d['idnya'] = "setwalikelas";
        $this->d['nama_form'] = "f_setwalikelas";

        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = substr($get_tasm['tahun'],0,4);
        $this->d['tasm1'] = $get_tasm['tahun'];
        $this->d['sm'] = substr($this->d['tasm1'], 4, 1);
        $this->d['ta'] = substr($this->d['tasm'], 0, 4);
    }

    public function datatable() {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $draw = $this->input->post('draw');
        $search = $this->input->post('search');

        $d_total_row = $this->db->query("SELECT id FROM t_walikelas a
                                        WHERE a.tasm = '".$this->d['tasm']."'
                                        ORDER BY id ASC")->num_rows();
    
        $q_datanya = $this->db->query("SELECT a.id, a.id_kelas, b.nama nmguru, c.nama nmkelas
                                    FROM t_walikelas a
                                    INNER JOIN m_guru b ON a.id_guru = b.id
                                    INNER JOIN m_kelas c ON a.id_kelas = c.id
                                    WHERE (a.tasm = '".$this->d['tasm']."') AND (
                                    b.nama LIKE '%".$search['value']."%'
                                    OR c.nama LIKE '%".$search['value']."%')
                                    ORDER BY a.id ASC
                                    LIMIT ".$start.", ".$length."")->result_array();
        $data = array();
        $no = ($start+1);

        foreach ($q_datanya as $d) {
            $data_ok = array();
            $data_ok[0] = $no++;
            $data_ok[1] = $d['nmkelas'];
            $data_ok[2] = $d['nmguru'];
            $tasm = $this->d['tasm1'];
            $data_ok[3] = '<a href="view_ledger/show_ledger/'.$d['id'].'/'.$d['id_kelas'].'" class="btn btn-xs btn-success"><i class="fa fa-book"></i> View Ledger</a><a href="cetak_raport/bulk_kunci_rapor/'.$d['id_kelas'].'/'.$tasm.'" class="btn btn-xs btn-success"><i class="fa fa-book"></i> Kunci Rapor</a> ';

            $data[] = $data_ok;
        }

        $json_data = array(
                    "draw" => $draw,
                    "iTotalRecords" => $d_total_row,
                    "iTotalDisplayRecords" => $d_total_row,
                    "data" => $data
                );
        j($json_data);
        exit;
    }

    public function edit($id) {
        $q = $this->db->query("SELECT *, 'edit' AS mode FROM t_walikelas WHERE id = '$id'")->row_array();

        $d = array();
        $d['status'] = "ok";
        if (empty($q)) {
            $d['data']['id'] = "";
            $d['data']['mode'] = "add";
            $d['data']['id_guru'] = "";
            $d['data']['id_kelas'] = "";
        } else {
            $d['data'] = $q;
        }

        j($d);
    }

    public function simpan() {
        $p = $this->input->post();

        $d['status'] = "";
        $d['data'] = "";


        if ($p['_mode'] == "add") {
            $cek = $this->db->query("SELECT id FROM t_walikelas WHERE id_kelas = '".$p['id_kelas']."' AND tasm = '".$this->d['tasm']."'")->num_rows();

            if ($cek > 0) {
                $d['status'] = "gagal";
                $d['data'] = "Kelas tersebut sudah ada walinya..";                
            } else {
                $this->db->query("INSERT INTO t_walikelas (tasm, id_guru, id_kelas) VALUES ('".$this->d['tasm']."', '".$p['id_guru']."', '".$p['id_kelas']."')");

                $d['status'] = "ok";
                $d['data'] = "Data berhasil disimpan";
            }
        } else if ($p['_mode'] == "edit") {
            $this->db->query("UPDATE t_walikelas SET id_kelas = '".$p['id_kelas']."', id_guru = '".$p['id_guru']."' WHERE id = '".$p['_id']."'");

            $d['status'] = "ok";
            $d['data'] = "Data berhasil disimpan";
        } else {
            $d['status'] = "gagal";
            $d['data'] = "Kesalahan sistem";
        }

        j($d);
    }

    public function hapus($id) {
        $this->db->query("DELETE FROM t_walikelas WHERE id = '$id'");

        $d['status'] = "ok";
        $d['data'] = "Data berhasil dihapus";
        
        j($d);
    }

    public function index() {
    	$this->d['p'] = "list";
        
        $this->d['p_kelas'] = array(""=>"Kelas");

        $q_kelas = $this->db->query("SELECT * FROM m_kelas")->result_array();
        if (!empty($q_kelas)) {
            foreach ($q_kelas as $k) {
                $this->d['p_kelas'][$k['id']] = $k['nama'];
            }
        }

        $this->d['p_guru'] = array(""=>"Guru");

        $q_guru = $this->db->query("SELECT * FROM m_guru")->result_array();
        if (!empty($q_guru)) {
            foreach ($q_guru as $g) {
                $this->d['p_guru'][$g['id']] = $g['nama'];
            }
        }

        $this->load->view("template_utama", $this->d);
    }
public function show_ledger($id,$id_kelas)
{
    // --- View name ---
    $this->d['p'] = "landing";

    // --- Shared params ---
    $_tasm   = $this->d['tasm'];
    $_tasm1   = $this->d['tasm1'];
    $_ta     = substr($_tasm, 0, 4);

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
    $tahun   = $this->db->query("SELECT * FROM tahun WHERE tahun = '" . $this->d['tasm1'] . "'")->row();


    $tingkat = $siswa[0]['tingkat'] ?? null;
    $jenis   = getJenisRaport($tahun->id, $tingkat);
    $jenis_rapor = ($jenis->nama ?? "") == "K13" ? 2 : 1;
    $this->d['jenis_rapor'] = $jenis_rapor;
    // --- Subjects ---
    $mapel = $this->db->query("SELECT id, kd_singkat FROM m_mapel ORDER BY id ASC")->result_array();
    $this->d['mapel'] = $mapel;
    $this->d['dw'] = $this->db->query("select 
                    b.nama nmkelas, c.nama nmguru
                    from t_walikelas a 
                    inner join m_kelas b on a.id_kelas = b.id
                    inner join m_guru c on a.id_guru = c.id
                    where left(a.tasm,4) = '".$_ta."' and a.id_kelas = '".$id_kelas."'")->row_array();


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
                  AND tahun = '" . $_ta . "'
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
                            GROUP BY b.id_mapel
                            ORDER BY c.kd_singkat ASC
                        ")->result_array();
                    }

                    $n_keterampilan = $this->db->query("
                        SELECT c.id, ROUND(AVG(a.nilai),0) AS na
                        FROM t_nilai_ket a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1'
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                            WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
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
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1' AND a.nilai > 0
                        GROUP BY b.id_mapel
                        ORDER BY c.kd_singkat ASC
                    ")->result_array();
                    }
                    $n_keterampilan = $this->db->query("
                        SELECT c.id, ROUND(AVG(a.nilai),0) AS na
                        FROM t_nilai_ket a
                        LEFT JOIN t_guru_mapel b ON a.id_guru_mapel = b.id
                        LEFT JOIN m_mapel c ON b.id_mapel = c.id
                        WHERE a.id_siswa = $id_siswa AND a.tasm = '$_tasm1'
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
}