<page backtop="5mm" backbottom="7mm" backleft="22mm" backright="10mm"
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
            <td colspan="9" style="width: 675px;">
                <p>
                    <h5 class="font_kecil" style="text-align: center;"><img
                            src="https://report.mhis.link/images/hanya-logo.png" width="80"><br>MUTIARA HARAPAN ISLAMIC
                        SCHOOL<br>PRIMARY LEVEL<br><?php if ($semester == 1) {; ?>FIRST<?php } else { ?>SECOND<?php } ?>
                        SEMESTER</h5>
                    <hr class="s5">
                </p>

            </td>
        </tr>

    </table>
    <table>
        <tr>
            <td style="width:100px">Nama Sekolah</td>
            <td>:</td>
            <td style="font-weight: bold; width:350px">
                <?= $this->config->item('nama_sekolah'); ?>
            </td>
            <td>Kelas</td>
            <td>:</td>
            <td style="font-weight: bold;">
                <?= strtoupper($kelas ?? "--"); ?>
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
                <?= $nama ?>
            </td>
            <td>Tahun Pelajaran</td>
            <td>:</td>
            <td style="font-weight: bold;">
                <?= $tasm ?>
            </td>
        </tr>
        <tr>
            <td style="width:100px">NIS / NISN</td>
            <td>:</td>
            <td style="font-weight: bold; width:350px">
                <?= $nis . " / " . $nisn ?>
            </td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
    </table>
    <hr class="s5">
    <table>
        <tr>
            <td colspan="9" style="width:700px;">
                <p>
                    <h3 style="text-align: center;">LAPORAN HASIL BELAJAR</h3>
                </p>
            </td>
        </tr>

    </table>
    <table>
        <tr>
            <td colspan="9">
                <?php $is4 = $tingkat != 0 ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding: 15px 10px;">No</th>
                            <th style="padding: 15px 10px;" colspan="2">Mata Pelajaran</th>
                            <th style="padding: 15px 10px;" colspan="2">Nilai Akhir</th>
                            <th style="padding: 15px 10px;" colspan="2">UTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fixDetails = array_filter($details, function ($ket) {
                            return $ket['kd_singkat'] != "BTQ";
                        });
                        ?>
                        <?php $no = 1 ?>
                        <?php
                        $filterB = array_filter($fixDetails, function ($ket) {
                            return $ket['kelompok'] == "B";
                        });
                        $kelompokB = array_values($filterB);
                        ?>
                        <?php foreach ($kelompokB as $kelB) { ?>
                        <tr>
                            <td class="ctr"><?= $no ?></td>
                            <td style="width:295px;" colspan="2"><?= $kelB['mapel'] ?></td>
                            <?php if ($is4) { ?>
                            <td colspan="2" class="ctr" style="width:80px;padding:10px;">
                                <?= $kelB['nilai_pengetahuan'] ?></td>
                            <?php $hide =  $kelB['kd_singkat'] == 'GP' || $kelB['kd_singkat'] == 'SBK' || $kelB['kd_singkat'] == 'DL' || $kelB['kd_singkat'] == 'PE' || $kelB['kd_singkat'] == 'Music' ?>
                            <?php if ($hide) { ?>
                            <td class="ctr" style="width:80px;padding:10px;" colspan="2">-</td>
                            <?php } else { ?>
                            <td class="ctr" style="width:80px;padding:10px;" colspan="2"><?= $kelB['nilai_uts'] ?></td>
                            <?php } ?>
                            <?php } else { ?>
                            <td class="ctr" style="width:240px;padding:10px;" colspan="2">
                                <?= $kelB['nilai_pengetahuan'] == 0 ? "-" : $kelB['nilai_pengetahuan'] ?></td>
                            <?php } ?>
                        </tr>
                        <?php $no++; ?>
                        <?php } ?>
                        <tr>
                            <td class="ctr"></td>
                            <td style="width:295px;" colspan="2">Muatan Lokal</td>
                            <?php if ($is4) { ?>
                            <td colspan="2" class="ctr" style="width:120px;padding:10px;"></td>
                            <td colspan="2" class="ctr" style="width:120px;padding:10px;"></td>
                            <?php } else { ?>
                            <td class="ctr" style="width:240px;padding:10px;" colspan="2"></td>
                            <?php } ?>
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
                            <td style="width:295px;" colspan="2"><?= $mulok['mapel'] ?></td>
                            <?php if ($is4) { ?>
                            <td colspan="2" class="ctr" style="width:80px;padding:10px;">
                                <?= $mulok['nilai_pengetahuan'] ?></td>
                            <?php $hide =  $mulok['kd_singkat'] == 'GP' || $mulok['kd_singkat'] == 'SBK' || $mulok['kd_singkat'] == 'DL' || $mulok['kd_singkat'] == 'PE' || $mulok['kd_singkat'] == 'Music' ?>
                            <?php if ($hide) { ?>
                            <td class="ctr" style="width:80px;padding:10px;" colspan="2">-</td>
                            <?php } else { ?>
                            <td class="ctr" style="width:80px;padding:10px;" colspan="2"><?= $mulok['nilai_uts'] ?></td>
                            <?php } ?>
                            <?php } else { ?>
                            <td class="ctr" style="width:240px;padding:10px;" colspan="2">
                                <?= $mulok['nilai_pengetahuan'] == 0 ? "-" : $mulok['nilai_pengetahuan'] ?></td>
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
    <page backtop="5mm" backbottom="7mm" backleft="22mm" backright="10mm"
        backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
        <table>
            <tr>
                <td colspan="9"><b>Catatan Wali Kelas</b></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:620px;">
                    <?= clean_text( $catatan_naik_kelas ?? ""); ?>
                </td>
            </tr>
            <tr>
                <td colspan="6"><br><br></td>
            </tr>
        </table>
        <table>
            <tr>
                <td style="width:200px;text-align: center;">
                    Undersign,
                    <br><br><br><br><br><br>
                    <u><b>
                            <?= $kepala_sekolah ?? "--"; ?>
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
                    <?= isset($tgl_rapor) ? tjs($tgl_rapor, "l") : ""; ?><br>
                    <?php } else { ?>
                    <?= $this->config->item('kota'); ?>,
                    <?= tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
                    <?php } ?>
                    <br><br><br><br><br>
                    <u><b>
                            <?= $wali_kelas ?? "--"; ?><br>
                        </b></u>Homeroom Teacher<br>
                </td>
            </tr>
        </table>
    </page>