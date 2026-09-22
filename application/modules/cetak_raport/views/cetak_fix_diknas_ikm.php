<page backtop="20mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
    <page_header><br>
        <img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
    </page_header>
    <style type="text/css">
        body {
            font-family: freeserif;
            font-size: 11pt;
            width: 8.5in
        }

        .table {
            border-collapse: collapse;
            border: solid 1px #999;
            width: 100%
        }

        .table tr td,
        .table tr th {
            font-family: freeserif;
            border: solid 1px #000;
            padding: 3px;
        }

        .table tr th {
            font-weight: bold;
            text-align: center
        }

        .rgt {
            text-align: right;
        }

        .ctr {
            text-align: center;
        }

        .tbl {
            font-weight: bold
        }

        table tr td {
            vertical-align: top
        }

        .font_kecil {
            font-size: 12px
        }
    </style>
    <table>
        <tr>
            <td colspan="6">
                <p>
                <h3 style="text-align: center;">HASIL PENCAPAIAN KOMPETENSI PESERTA DIDIK</h3>
                </p>
            </td>
        </tr>
        <tr>
            <td style="width:100px">Nama Sekolah</td>
            <td>:</td>
            <td style="font-weight: bold; width:350px">
                <?= $this->config->item('nama_sekolah'); ?>
            </td>
            <td>Kelas</td>
            <td>:</td>
            <td style="font-weight: bold;">
                <?= strtoupper($kelas); ?>
            </td>
        </tr>
        <tr>
            <td style="width:100px">Alamat Sekolah</td>
            <td>:</td>
            <td style=" width:350px">
                <?= $this->config->item('alamat_sekolah'); ?>
            </td>
            <td>Semester</td>
            <td>:</td>
            <td style="font-weight: bold;">
                <?= $semester; ?>
            </td>
        </tr>
        <tr>
            <td style="width:100px">Nama Siswa</td>
            <td>:</td>
            <td style="font-weight: bold; width:350px">
                <?= $nama; ?>
            </td>
            <td>Tahun Pelajaran</td>
            <td>:</td>
            <td style="font-weight: bold;">
                <?= $tasm; ?>
            </td>
        </tr>
        <tr>
            <td style="width:100px">NIS / NISN</td>
            <td>:</td>
            <td style="font-weight: bold; width:350px">
                <?= $nis . " / " . $nisn; ?>
            </td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="6"><br><br></td>
        </tr>
    </table>
    <table class="table">
        <thead>
            <tr>
                <th colspan="2">Mata Pelajaran</th>
                <th colspan="2">Nilai Akhir</th>
                <th colspan="2">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1 ?>
            <?php
            $fixDetail = array_filter($details, function ($ket) {
                return  $ket['nilai_pengetahuan'] > 1;
            });
            ?>
            <?php
            $filterB = array_filter($fixDetail, function ($ket) {
                return $ket['kelompok'] == "B";
            });
            $kelompokB = array_values($filterB);
            ?>
            <?php foreach ($kelompokB as $kelB) { ?>
                <tr>
                    <td class="ctr"><?= $no ?></td>
                    <td style="width:170px;"><?= $kelB['mapel'] ?></td>
                    <td colspan="2" class="ctr"><?= $kelB['nilai_pengetahuan'] ?></td>
                    <td colspan="2" style="width:330px; padding: 20px 10px;"><?= $kelB['desk_pengetahuan'] ?></td>
                </tr>
                <?php $no++; ?>
            <?php } ?>
            <?php
            $filterMulok = array_filter($fixDetail, function ($ket) {
                return $ket['kelompok'] == "MULOK";
            });
            $kelompokMulok = array_values($filterMulok);
            ?>
            <?php foreach ($kelompokMulok as $mulok) { ?>
                <tr>
                    <td class="ctr"><?= $no ?></td>
                    <td style="width:170px;"><?= $mulok['mapel'] ?></td>
                    <td colspan="2" class="ctr"><?= $mulok['nilai_pengetahuan'] ?></td>
                    <td colspan="2" style="width:330px; padding: 20px 10px;"><?= $mulok['desk_pengetahuan'] ?></td>
                </tr>
                <?php $no++; ?>
            <?php } ?>
        </tbody>
    </table>
    <page backtop="20mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
        <page_header><br>
            <img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
        </page_header>
        <?php if ($tingkat!=1){?>
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2" rowspan="2" style="width:150px">KKM</th>
                        <th colspan="8" style="width:450px">Predikat</th>
                    </tr>
                    <tr>
                        <th colspan="2">Kurang (D)</th>
                        <th colspan="2">Cukup (C)</th>
                        <th colspan="2">Baik (B)</th>
                        <th colspan="2">Sangat Baik (SB)</th>
                    </tr>
                </thead>
                <tbody style="text-align:center;">
                    <?php $kkms = explode(",", $kkm) ?>
                    <?php foreach ($kkms as $val) { ?>
                        <?php $rentang = round(((100 - $val) / 3), 0); ?>
                        <tr>
                            <td colspan="2"><?= $val ?></td>
                            <td colspan="2">0 - <?= ($val - 1) ?> </td>
                            <td colspan="2"> <?= $val ?> - <?= ($val + $rentang) ?> </td>
                            <td colspan="2"> <?= ($val + ($rentang * 1) + 1) ?> - <?= ($val + ($rentang * 2)) ?> </td>
                            <td colspan="2"> <?= ($val + ($rentang * 2) + 1) ?> - 100</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }?>
        <br><br>

        <table>
            <tr>
                <td colspan="9">
                    <b> EKSTRAKURIKULER</b>
                </td>
            </tr>
            <tr>
                <td colspan="9">
                    <br>
                </td>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th style="width:220px">Nama Kegiatan</th>
                    <th style="width:50px">Nilai</th>
                    <th style="width:290px">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($exschool)) {
                    $no = 1;
                    foreach ($exschool as $ne) {

                ?>
                        <tr>
                            <td class="ctr">
                                <?= $no; ?>
                            </td>
                            <td>
                                <?= $ne['nama']; ?>
                            </td>
                            <td class="ctr">
                                <?= $ne['nilai']; ?>
                            </td>
                            <td style="width:300px">
                                <?= $ne['desk']; ?>
                            </td>
                        </tr>
                <?php
                        $no++;
                    }
                } else {
                    echo '<tr><td colspan="4">-</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b> PRESTASI</b>
                </td>
            </tr>
            <tr>
                <td colspan="9">
                    <br>
                </td>
            </tr>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th style="width:320px">Jenis Prestasi</th>
                    <th style="width:260px">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($prestasi)) {
                    $no = 1;
                    foreach ($prestasi as $p) {
                ?>
                        <tr>
                            <td class="ctr">
                                <?= $no; ?>
                            </td>
                            <td style="width:320px">
                                <?= $p['jenis']; ?>
                            </td>
                            <td style="width:260px">
                                <?= $p['keterangan']; ?>
                            </td>
                        </tr>
                <?php
                        $no++;
                    }
                } else {
                    echo '<tr><td colspan="3">-</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b> KETIDAKHADIRAN</b>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <table class="table">
                        <tr>
                            <td style="width:200px">Sakit</td>
                            <td style="width:100px" class="ctr">
                                <?= $sakit ?> hari
                            </td>
                        </tr>
                        <tr>
                            <td style="width:200px">Izin</td>
                            <td style="width:100px" class="ctr">
                                <?= $izin; ?> hari
                            </td>
                        </tr>
                        <tr>
                            <td style="width:200px">Tanpa Keterangan</td>
                            <td style="width:100px" class="ctr">
                                <?= $tanpa_ket; ?> hari
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b> CATATAN WALI KELAS</b>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:600px;">
                    <?= $catatan_naik_kelas; ?>
                </td>
            </tr>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b> TANGGAPAN ORANGTUA/WALI</b>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: solid 1px #000; padding: 20px 10px; height: 80px; width:600px;"></td>
            </tr>
        </table>
        <br><br>
        <?php
        if ($semester == 2) {
        ?>
            <table>
                <tr>
                    <td colspan="6">
                        <?php
                        $naik_kelas = $tingkat + 1;
                        $kelas_now = $tingkat;

                        if ($kelas_now != 9) {

                            if ($naik == 'N') {
                                $naik = 'text-decoration: line-through';
                                $tidak_naik = '';
                            } else {
                                $naik = '';
                                $tidak_naik = 'text-decoration: line-through';
                            }

                        ?>


                            <div style="border: solid 1px; padding: 10px; margin-top: 40px">
                                <b>Keputusan : </b>
                                <p>Berdasarkan pencapaian kompetensi pada semester ke-1 dan ke-2, peserta didik ditetapkan *) :<br>

                                <div style="display: block">
                                    <div style="diplay: inline; float: left; width: 200px;  <?= $naik; ?> ">naik ke kelas </div>
                                    <div style="diplay: inline; float: left; font-weight: bold; <?= $naik; ?>"><?= $naik_kelas . " (" . terbilang($naik_kelas) . ")"; ?></div>
                                </div><br>
                                <div style="display: block">
                                    <div style="diplay: inline; float: left; width: 200px;<?= $tidak_naik; ?>">tinggal di kelas
                                    </div>
                                    <div style="diplay: inline; float: left; font-weight: bold; <?= $tidak_naik; ?>"><?= $kelas_now . " (" . terbilang($kelas_now) . ")"; ?></div>
                                </div>
                                <br><br>
                                *) Coret yang tidak perlu
                            </div>

                        <?php } else { ?>
                            <div style="border: solid 1px; padding: 10px; margin-top: 40px">
                                <b>Keputusan : </b>
                                <p>Berdasarkan pencapaian kompetensi pada kelas 4, 5 dan 6, maka, peserta didik dinyatakan : *)
                                    :<br>
                                <div style="display: block; font-weight: bold">
                                    LULUS / <strike>TIDAK LULUS</strike>
                                </div><br><br>
                                *) Coret yang tidak perlu
                            </div>

                        <?php } ?>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <br><br><br>
        <table>
            <tr>
                <td style="width:200px;text-align: center;">
                    Mengetahui<br>
                    Orang Tua/Wali,
                    <br><br><br><br><br>
                    <b>
                        ______________________
                    </b><br>
                    <br>
                </td>
                <td style="width:200px;text-align: center;">

                </td>
                <td></td>
                <td style="text-align: center;">
                    <?php
                    if ($tingkat != 9) {
                    ?>
                        <?= $this->config->item('kota'); ?>,
                        <?= tjs($tgl_rapor, "l"); ?><br>
                    <?php } else { ?>
                        <?= $this->config->item('kota'); ?>,
                        <?= tjs($tgl_rapor, "l"); ?><br>
                    <?php } ?>
                    Guru Kelas,
                    <br><br><br><br><br>
                    <u><b>
                            <?= $wali_kelas; ?>
                        </b></u><br>
                </td>
            </tr>
            <tr>
                <td style="width:200px;text-align: center;">
                </td>
                <td style="width:200px;text-align: center;">
                    Mengetahui,<br>
                    Kepala Sekolah
                    <br><br><br><br><br>
                    <u><b><?= $kepala_sekolah; ?></b></u>
                </td>
                <td></td>
                <td style="text-align: center;">
                </td>
            </tr>
        </table>
    </page>