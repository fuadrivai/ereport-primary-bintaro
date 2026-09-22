<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Kehadiran extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->sespre = $this->config->item('session_name_prefix');
        $this->d['admlevel'] = $this->session->userdata($this->sespre . 'level');
        $this->d['admkonid'] = $this->session->userdata($this->sespre . 'konid');
        $this->d['url'] = "kehadiran";
        $this->d['idnya'] = "id_kehadiran";
        $this->d['nama_form'] = "f_kehadiran";
        $get_tasm = $this->db->query("SELECT tahun FROM tahun WHERE aktif = 'Y'")->row_array();
        $this->d['tasm'] = $get_tasm['tahun'];
        $this->d['ta'] = substr($this->d['tasm'], 0, 4);
        $wali = $this->session->userdata($this->sespre . "walikelas");
        $this->d['id_kelas'] = $wali['id_walikelas'];
        $this->d['nama_kelas'] = $wali['nama_walikelas'];
    }
    public function index()
    {
        $date = $this->input->get('date');
        if (!$date) {
            $date = date('Y-m-d');
        }
        $wali = $this->session->userdata($this->sespre . "walikelas");
        $this->d['siswa_kelas'] = $this->getAttendanceBYDate($wali['id_walikelas'], $date);
        $this->d['p'] = "list";
        $this->load->view("template_utama", $this->d);
    }

    public function detail_kelas($idKelas)
    {
        $date = $this->input->get('date');
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->d['siswa_kelas'] = $this->getAttendanceBYDate($idKelas, $date);
        $this->d['p'] = "list";
        $this->d['idKelas'] = $idKelas;
        $this->load->view("template_utama", $this->d);
    }

    public function kelas()
    {
        $this->d['list_kelas'] = $this->db->query("SELECT a.id, a.id_kelas, b.nama nmguru, c.nama nmkelas,c.tingkat
                                    FROM t_walikelas a
                                    INNER JOIN m_guru b ON a.id_guru = b.id
                                    INNER JOIN m_kelas c ON a.id_kelas = c.id
                                    WHERE a.tasm = " . $this->d['ta'] . " ORDER by c.tingkat ASC")->result_array();
        $this->d['p'] = "list_kelas";
        $this->load->view("template_utama", $this->d);
    }

    public function detail($nis)
    {
        $month = $this->input->get('month');

        if (!$month) {
            $month = date('Y-m');
        }
        $month_start = $month . '-01';



        $this->d['absensi'] = $this->getListAttendance($nis, $month_start);
        $summary =   $this->getAttendanceSummary($nis, $month);
        $this->d['summary'] = json_decode($summary, true);
        $this->d['p'] = "detail";
        $this->load->view("template_utama", $this->d);
    }

    function getAttendanceBYDate($idKelas, $date)
    {
        $studentAttendances = $this->db->query("SELECT a.id_siswa, b.nis, b.nama,
            -- Clock IN: absen pertama antara jam 04:00 - 11:00
            DATE_FORMAT(
                MIN(
                    CASE 
                        WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                        THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                        ELSE NULL
                    END
                ), 
                '%H:%i'
            ) AS clock_in,

            -- Clock OUT: absen terakhir antara jam 11:01 - 19:00
            DATE_FORMAT(
                MAX(
                    CASE 
                        WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 11 AND 19
                            AND NOT (HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 11 AND MINUTE(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 0)
                        THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                        ELSE NULL
                    END
                ), 
                '%H:%i'
            ) AS clock_out,

            -- Durasi (clock_out - clock_in)
            CASE 
                WHEN 
                    MIN(
                        CASE 
                            WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                            THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                            ELSE NULL
                        END
                    ) IS NOT NULL
                    AND MAX(
                        CASE 
                            WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 11 AND 19
                                AND NOT (HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 11 AND MINUTE(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 0)
                            THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                            ELSE NULL
                        END
                    ) IS NOT NULL
                THEN 
                    CONCAT(
                        TIMESTAMPDIFF(HOUR, 
                            MIN(
                                CASE 
                                    WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                                    THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                                    ELSE NULL
                                END
                            ),
                            MAX(
                                CASE 
                                    WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 11 AND 19
                                        AND NOT (HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 11 AND MINUTE(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 0)
                                    THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                                    ELSE NULL
                                END
                            )
                        ), ' jam ',
                        MOD(
                            TIMESTAMPDIFF(MINUTE, 
                                MIN(
                                    CASE 
                                        WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                                        THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                                        ELSE NULL
                                    END
                                ),
                                MAX(
                                    CASE 
                                        WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 11 AND 19
                                            AND NOT (HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 11 AND MINUTE(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = 0)
                                        THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                                        ELSE NULL
                                    END
                                )
                            ), 60
                        ), ' menit'
                    )
                ELSE '-'
            END AS durasi,

            -- Status pindah ke paling akhir
            CASE 
                WHEN 
                    MIN(
                        CASE 
                            WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                            THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                            ELSE NULL
                        END
                    ) IS NULL THEN '-'
                WHEN HOUR(
                    MIN(
                        CASE 
                            WHEN HOUR(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) BETWEEN 4 AND 11
                            THEN CONVERT_TZ(la.timestamp, '+00:00', '+07:00')
                            ELSE NULL
                        END
                    )
                ) < 7 THEN 'P'
                ELSE 'L'
            END AS status

        FROM t_kelas_siswa a
        INNER JOIN m_siswa b ON a.id_siswa = b.id
        INNER JOIN m_kelas c ON a.id_kelas = c.id
        LEFT JOIN t_log_absensi la 
            ON la.nis = b.nis 
            AND DATE(CONVERT_TZ(la.timestamp, '+00:00', '+07:00')) = '$date'
        WHERE a.id_kelas = '" . $idKelas . "' 
        AND a.ta = '" . $this->d['ta'] . "'
        GROUP BY a.id_siswa, b.nis, b.nama
        ORDER BY b.nama ASC;")->result_array();

        return $studentAttendances;
    }


    function getListAttendance($nis, $month_start)
    {
        $sql = "
            SELECT d.dt AS tanggal,
                DATE_FORMAT(
                    MIN(
                        CASE 
                            WHEN TIME(CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')) BETWEEN '04:00:00' AND '11:00:00'
                            THEN CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')
                            ELSE NULL
                        END
                    ), '%H:%i'
                ) AS clock_in,
                DATE_FORMAT(
                    MAX(
                        CASE 
                            WHEN TIME(CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')) BETWEEN '11:01:00' AND '19:00:00'
                            THEN CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')
                            ELSE NULL
                        END
                    ), '%H:%i'
                ) AS clock_out,
                CASE 
                    WHEN MIN(
                        CASE 
                            WHEN TIME(CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')) BETWEEN '04:00:00' AND '11:00:00'
                            THEN CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')
                        END
                    ) IS NULL THEN '-'
                    ELSE 'H'
                END AS status
            FROM (
                SELECT DATE_ADD(?, INTERVAL n DAY) AS dt
                FROM (
                    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                    UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
                    UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
                    UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                    UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
                    UNION ALL SELECT 30
                ) nums
            ) d
            LEFT JOIN t_log_absensi la ON la.nis = ?
            AND DATE(CONVERT_TZ(la.`timestamp`,'+00:00','+07:00')) = d.dt
            WHERE MONTH(d.dt) = MONTH(?)
            GROUP BY d.dt ORDER BY d.dt;
            ";

        // disable ONLY_FULL_GROUP_BY if needed
        $this->db->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        // execute with bindings: [ month_start, nis, month_start ]
        $query = $this->db->query($sql, [$month_start, $nis, $month_start]);
        $result = $query->result_array();
        return $result;
    }

    function getAttendanceSummary($nis, $bulan)
    {

        $query = "
SELECT 
    JSON_OBJECT(
        'total_late', SUM(CASE WHEN clock_in > '07:00:00' THEN 1 ELSE 0 END),
        'total_durasi', CONCAT(IFNULL(FLOOR(SUM(durasi_menit)), 0), ' menit'),
        'periode', DATE_FORMAT(DATE(CONCAT('$bulan', '-01')), '%M %Y'),
        'nama', MAX(nama)
    ) AS result
FROM (
    SELECT 
        s.nama,
        DATE(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')) AS tanggal,
        TIME(MIN(
            CASE 
                WHEN TIME(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')) BETWEEN '04:00:00' AND '11:00:00'
                THEN CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')
            END
        )) AS clock_in,
        TIME(MAX(
            CASE 
                WHEN TIME(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')) BETWEEN '11:00:00' AND '19:00:00'
                THEN CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')
            END
        )) AS clock_out,
        TIMESTAMPDIFF(
            MINUTE,
            MIN(
                CASE 
                    WHEN TIME(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')) BETWEEN '04:00:00' AND '11:00:00'
                    THEN CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')
                END
            ),
            MAX(
                CASE 
                    WHEN TIME(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')) BETWEEN '11:00:00' AND '19:00:00'
                    THEN CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')
                END
            )
        ) AS durasi_menit
    FROM t_log_absensi la
    INNER JOIN m_siswa s ON la.nis = s.nis
    WHERE la.nis = '$nis'
      AND DATE_FORMAT(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00'), '%Y-%m') = '$bulan'
    GROUP BY DATE(CONVERT_TZ(la.`timestamp`, '+00:00', '+07:00')), s.nama
) daily;
";

        // Jika kamu pakai CodeIgniter 3:
        $result = $this->db->query($query)->row();
        $json = $result->result;

        // Tampilkan hasil JSON:
        return $json;
    }
}
