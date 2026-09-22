<link href="<?php echo base_url(); ?>aset/css/mystyle.css" rel="stylesheet" />

<div class="row">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-calendar-check-o" style="margin-right: 20%;color: #fff"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Periode</span>
                <span class="info-box-number"><?= $summary['periode'] ?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-clipboard" style="margin-right: 20%;color: #fff"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Late in days</span>
                <span class="info-box-number"><?= $summary['total_late'] ?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-users" style="margin-right: 25%;color: #fff"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Late in minutes</span>
                <span class="info-box-number" style="font-size: 20px;"><?= $summary['total_durasi'] ?></span>
            </div>
        </div><!-- /.info-box-content -->
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="header">
                <div class="row">
                    <div class="col-md-6 text-left">
                        <h4 class="title">Log Attendance : <?= $summary['nama'] ?></h4> <br>

                    </div>
                    <div class="col-md-6 text-right">
                        <div class="form-group">
                            <label for="birthDate" class="col-sm-3 pt-2 control-label" style="
    padding-top: 8px !important;
">Date : </label>
                            <div class="col-sm-9">
                                <?php $date = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); ?>
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
                            <th>Date</th>
                            <th>Clock IN</th>
                            <th>Clock OUT</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1 ?>
                        <?php if (!empty($absensi)) { ?>
                            <?php foreach ($absensi as $ab) { ?>
                                <?php
                                // jam aturan sekolah (format 24 jam)
                                $jam_masuk = strtotime('07:10:00');
                                $jam_pulang = strtotime('12:00:00');

                                // konversi jam absensi siswa
                                $clock_in_time = !empty($ab['clock_in']) ? strtotime($ab['clock_in']) : null;
                                $clock_out_time = !empty($ab['clock_out']) ? strtotime($ab['clock_out']) : null;

                                // cek terlambat / pulang cepat
                                $isLate = $clock_in_time && $clock_in_time > $jam_masuk;
                                $isEarlyLeave = $clock_out_time && $clock_out_time < $jam_pulang;

                                // warna badge
                                $badge_in_class = $isLate ? '#a94442' : '';
                                $badge_out_class = $isEarlyLeave ? '#a94442' : '';
                                ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= date('j F Y', strtotime($ab['tanggal'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: <?= $badge_in_class ?>;">
                                            <?= $ab['clock_in'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: <?= $badge_out_class ?>;">
                                            <?= $ab['clock_out'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= $ab['status'] ?>
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
    $('#date').on('change', function() {
        const selectedDate = $(this).val();
        const month = selectedDate.slice(0, 7);
        const url = window.location.origin + window.location.pathname;
        const parts = url.split('/');
        const nis = parts[parts.length - 1];
        const newUrl = '<?= base_url(); ?>kehadiran/detail/' + nis + '?month=' + encodeURIComponent(month);
        window.location.href = newUrl;
    });
</script>