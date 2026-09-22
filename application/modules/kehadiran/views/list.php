<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="header">
                <div class="row">
                    <div class="col-md-6 text-left">
                        <h4 class="title">Log Attendance </h4> <br>

                    </div>
                    <div class="col-md-6 text-right">
                        <div class="form-group">
                            <label for="date" class="col-sm-3 pt-2 control-label" style="
    padding-top: 8px !important;
">Date : </label>
                            <div class="col-sm-9">
                                <?php $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>
                                <input type="date" class="form-control datepicker" id="date"
                                    name="date" placeholder="Select Date" value="<?= $date ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="content">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Student's Name</th>
                            <th>Schedule IN</th>
                            <th>Clock IN</th>
                            <th>Clock OUT</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1 ?>
                        <?php if (!empty($siswa_kelas)) { ?>
                            <?php foreach ($siswa_kelas as $sk) { ?>
                                <?php
                                // jam aturan sekolah (format 24 jam)
                                $jam_masuk = strtotime('07:10:00');
                                $jam_pulang = strtotime('12:00:00');

                                // konversi jam absensi siswa
                                $clock_in_time = !empty($sk['clock_in']) ? strtotime($sk['clock_in']) : null;
                                $clock_out_time = !empty($sk['clock_out']) ? strtotime($sk['clock_out']) : null;

                                // cek terlambat / pulang cepat
                                $isLate = $clock_in_time && $clock_in_time > $jam_masuk;
                                $isEarlyLeave = $clock_out_time && $clock_out_time < $jam_pulang;

                                // warna badge
                                $badge_in_class = $isLate ? '#a94442' : '';
                                $badge_out_class = $isEarlyLeave ? '#a94442' : '';
                                ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $sk['nis']; ?></td>
                                    <td><?= $sk['nama']; ?></td>
                                    <td class="text-center">07:00</td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: <?= $badge_in_class ?>;">
                                            <?= $sk['clock_in'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: <?= $badge_out_class ?>;">
                                            <?= $sk['clock_out'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url() . $url . '/detail/' . $sk['nis']  ?>" class="btn btn-info btn-sm">View Details</a>
                                    </td>
                                </tr>
                                <?php $no++ ?>
                            <?php } ?>
                        <?php } else { ?>
                            <?= '<tr><td colspan="8" class="text-center">No data available</td></tr>' ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let idKelas = "<?= $idKelas ?? "" ?>";
    $(document).ready(function() {
        $('#date').on('change', function() {
            var selectedDate = $(this).val();
            var url = '<?= base_url() . $url; ?>';
            if (idKelas != "") {
                url += `/detail_kelas/${idKelas}`;
            }
            window.location.href = url + '?date=' + selectedDate;
        })
    });
</script>