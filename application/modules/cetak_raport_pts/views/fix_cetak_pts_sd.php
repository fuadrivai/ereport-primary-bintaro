<page backtop="5mm" backbottom="7mm" backleft="10mm" backright="10mm"
    backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
    <page_header><br>

    </page_header>
    <style type="text/css">
        body {
            font-family: arial;
            font-size: 11pt;
            width: 8.5in
        }

        hr {
            background-color: white;
            margin: 0 0 45px 0;
            max-width: 600px;
            border-width: 0;
        }

        hr.s1 {
            height: 5px;
            border-top: 1px solid black;
            border-bottom: 2px solid black;
        }

        hr.s2 {
            height: 9px;
            border-top: 2px solid black;
            border-bottom: 4px solid black;
        }

        hr.s3 {
            height: 14px;
            border-top: 4px solid black;
            border-bottom: 8px solid black;
        }

        hr.s4 {
            height: 14px;
            border-top: 2px solid black;
            border-bottom: 9px solid black;
        }

        hr.s5 {
            height: 5px;
            border-top: 2px solid black;
            border-bottom: 1px solid black;
        }

        hr.s6 {
            height: 9px;
            border-top: 4px solid black;
            border-bottom: 2px solid black;
        }

        hr.s7 {
            height: 14px;
            border-top: 8px solid black;
            border-bottom: 4px solid black;
        }

        hr.s8 {
            height: 12px;
            border-top: 7px solid black;
            border-bottom: 1px solid black;
        }

        hr.s9 {
            height: 6px;
            border-top: 2px solid black;
            border-bottom: 2px solid black;
        }

        .table {
            border-collapse: collapse;
            border: solid 1px #999;
            width: 100%;
            font-size: 9pt;
        }

        .table tr td,
        .table tr th {
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
            <td colspan="9" style="width: 700px;">
                <p>
                    <h5 class="font_kecil" style="text-align: center;"><img
                            src="https://report.mhis.link/images/hanya-logo.png" width="80"><br>MUTIARA HARAPAN ISLAMIC
                        SCHOOL<br>PRIMARY LEVEL</h5>
                    <hr class="s5">
                </p>

            </td>
        </tr>
        <tr>
            <td colspan="9" class="font_kecil" style="text-align: center;"><b>MID SEMESTER EVALUATION REPORT<br>
                    SCHOOL YEAR
                    <?= $tasm; ?><br>
                    <?php if ($semester == 1) {; ?>FIRST<?php } else { ?>SECOND<?php } ?> SEMESTER
                </b></td>
        </tr>
        <tr>
            <td colspan="9" class="font_kecil" style="text-align: center;"><b>Name of Student:
                    <?= $nama ?>
                </b></td>
        </tr>
        <tr>
            <td colspan="9" class="font_kecil" style="text-align: center;"><b>Grade:
                    <?= strtoupper($kelas ?? "--"); ?>
                </b></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="9"><b>A. SIKAP</b></td>
        </tr>
        <tr>
            <td colspan="9">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding: 15px 10px;">No</th>
                            <th style="padding: 15px 10px;" colspan="2">Aspek yang Dinilai</th>
                            <th style="padding: 15px 10px;" colspan="3">Capaian</th>
                            <th style="padding: 15px 10px;" colspan="3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($catatan_kl1)) { ?>
                        <tr>
                            <td style="padding: 15px 10px;">1</td>
                            <td colspan="2" style="width:150px; padding: 20px 10px;">Sikap Spiritual</td>
                            <td colspan="3" style="text-align:center; padding: 20px 10px;">
                                <?= $capaian_kl1; ?>
                            </td>
                            <td colspan="3" style="width:380px; padding: 20px 10px;">
                                <?= $catatan_kl1 ?>
                            </td>
                        </tr>
                        <?php } else { ?>
                        <tr>
                            <td colspan="3">-</td>
                        </tr>;
                        <?php } ?>
                        <?php if (!empty($catatan_kl2)) { ?>
                        <tr>
                            <td style="padding: 15px 10px;">2</td>
                            <td colspan="2" style="width:150px; padding: 20px 10px;">Sikap Sosial</td>
                            <td colspan="3" style="text-align:center; padding: 20px 10px;">
                                <?= $capaian_kl2 ?>
                            </td>
                            <td colspan="3" style="width:335px; padding: 20px 10px;">
                                <?= $catatan_kl2; ?>
                            </td>
                        </tr>
                        <?php } else {
                            echo '<tr><td colspan="3">-</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="6"><br><br></td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan="9"><b>B. Pengetahuan dan Keterampilan</b></td>
        </tr>
        <tr>
            <td colspan="9">
                <?php $is1or2 = ($tingkat != 2 && $tingkat != 1) ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding: 15px 10px;">No</th>
                            <th style="padding: 15px 10px;" colspan="2">Mata Pelajaran</th>
                            <th style="padding: 15px 10px;" colspan="2">KKM</th>
                            <th style="padding: 15px 10px;" colspan="2">Pengetahuan</th>
                            <th style="padding: 15px 10px;" colspan="2">Keterampilan</th>
                            <?php if ($is1or2) { ?>
                            <th style="padding: 15px 10px;" colspan="2">UTS</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1 ?>

                        <?php
                        $fixDetails = array_filter($details, function ($ket) {
                            return $ket['kd_singkat'] != "BTQ";
                        });
                        $filterB = array_filter($fixDetails, function ($ket) {
                            return $ket['kelompok'] == "B";
                        });
                        $kelompokB = array_values($filterB);
                        ?>
                        <?php foreach ($kelompokB as $kelB) { ?>

                        <tr>
                            <td class="ctr"><?= $no ?></td>
                            <td colspan="2" style="width:200;"><?= $kelB['mapel'] ?></td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;"><?= $kelB['kkm'] ?></td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;">
                                <?= ($kelB['nilai_pengetahuan'] == 0 || $kelB['nilai_pengetahuan'] === '0' || $kelB['nilai_pengetahuan'] === '0.00') ? "-" : $kelB['nilai_pengetahuan'] ?>
                            </td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;">
                                <?= ($kelB['nilai_keterampilan'] == 0 || $kelB['nilai_keterampilan'] === '0' || $kelB['nilai_keterampilan'] === '0.00') ? "-" : $kelB['nilai_keterampilan'] ?>
                            </td>
                            <?php if ($is1or2) { ?>
                            <?php $hide =  $kelB['kd_singkat'] == 'GP' || $kelB['kd_singkat'] == 'SBK' || $kelB['kd_singkat'] == 'DL' || $kelB['kd_singkat'] == 'PE' || $kelB['kd_singkat'] == 'Music' ?>
                            <?php if ($hide) { ?>
                            <td class="ctr" style="width:65px;padding:10px;" colspan="2">-</td>
                            <?php } else { ?>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;">
                                <?= ($kelB['nilai_uts'] == 0 || $kelB['nilai_uts'] === '0' || $kelB['nilai_uts'] === '0.00') ? "-" : $kelB['nilai_uts'] ?>
                            </td>
                            <?php } ?>
                            <?php } ?>
                        </tr>
                        <?php $no++; ?>
                        <?php } ?>
                        <tr>
                            <td class="ctr"></td>
                            <td colspan="2" style="width:200px;">Muatan Lokal</td>
                            <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
                            <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
                            <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
                            <td colspan="2" class="ctr" style="width:68px;padding:10px;"></td>
                        </tr>
                        <?php
                        $filterMulok = array_filter($fixDetails, function ($ket) {
                            return $ket['kelompok'] == "MULOK";
                        });
                        $kelompokMulok = array_values($filterMulok);
                        ?>
                        <?php foreach ($kelompokMulok as $mulok) { ?>
                        <tr>
                            <td class="ctr"><?= $no ?></td>
                            <td colspan="2" style="width:200;"><?= $mulok['mapel'] ?></td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;"><?= $mulok['kkm'] ?></td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;">
                                <?= $mulok['nilai_pengetahuan'] ?></td>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;">
                                <?= $mulok['nilai_keterampilan'] ?></td>
                            <?php if ($is1or2) { ?>
                            <?php $hide =  $mulok['kd_singkat'] == 'GP' || $mulok['kd_singkat'] == 'SBK' || $mulok['kd_singkat'] == 'DL' || $mulok['kd_singkat'] == 'PE' || $mulok['kd_singkat'] == 'Music' ?>
                            <?php if ($hide) { ?>
                            <td class="ctr" style="width:65px;padding:10px;" colspan="2">-</td>
                            <?php } else { ?>
                            <td colspan="2" class="ctr" style="width:65px;padding:10px;"><?= $mulok['nilai_uts'] ?></td>
                            <?php } ?>
                            <?php } ?>
                        </tr>
                        <?php $no++; ?>
                        <?php } ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="6"><br><br></td>
        </tr>
    </table>
    <page backtop="5mm" backbottom="7mm" backleft="10mm" backright="10mm"
        backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
        <table>
            <tr>
                <td colspan="9"><b>C. Catatan Guru</b></td>
            </tr>
            <tr>
                <td colspan="9"></td>
            </tr>
            <tr>
                <td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:665px;">
                    <?= $catatan_naik_kelas; ?>
                </td>
            </tr>
            <tr>
                <td colspan="6"><br><br></td>
            </tr>
        </table>
        <table>
            <tr>
                <td style="width:233px;text-align: center;">
                    Undersign,
                    <br><br><br><br><br><br>
                    <u><b>
                            <?= $kepala_sekolah; ?>
                        </b></u><br>
                    Primary Principal
                    <br>
                </td>
                <td style="width:233px;text-align: center;">

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
                    <br><br><br><br><br>
                    <u><b>
                            <?= $wali_kelas; ?><br>
                        </b></u>Homeroom Teacher<br>
                </td>
            </tr>
        </table>
    </page>