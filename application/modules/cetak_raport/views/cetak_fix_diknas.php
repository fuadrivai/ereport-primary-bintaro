<page backtop="20mm" backbottom="7mm" backleft="10mm" backright="10mm"
    backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
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
                <h3 style="text-align: center;">RAPOR PESERTA DIDIK DAN PROFIL PESERTA DIDIK</h3>
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
    <table>
        <tr>
            <td colspan="9"><b>A. SIKAP</b></td>
        </tr>
        <tr>
            <td colspan="9"><b>1. Sikap Spiritual (KI 1)</b></td>
        </tr>
        <tr>
            <td colspan="9">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3">Aspek yang Dinilai</th>
                            <th colspan="3">Capaian</th>
                            <th colspan="3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" style="width:100px; padding: 20px 10px;">Sikap Spiritual</td>
                            <td colspan="3" style="text-align:center;width:55px; padding: 20px 10px;">
                                <?= $capaian_kl1; ?>
                            </td>
                            <td colspan="3" style="width:435px; padding: 20px 10px;">
                                <?= $catatan_kl1; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <br>
        <tr>
            <td colspan="9"><b>2. Sikap Sosial (KI 2)</b></td>
        </tr>
        <tr>
            <td colspan="9">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3">Aspek yang Dinilai</th>
                            <th colspan="3">Capaian</th>
                            <th colspan="3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" style="width:100px; padding: 20px 10px;">Sikap Sosial</td>
                            <td colspan="3" style="text-align:center;width:55px; padding: 20px 10px;">
                                <?= $capaian_kl2; ?>
                            </td>
                            <td colspan="3" style="width:435px; padding: 20px 10px;">
                                <?= $catatan_kl2; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="6"><br><br></td>
        </tr>
    </table>
</page>
<page backtop="20mm" backbottom="7mm" backleft="10mm" backright="10mm"
    backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
    <page_header><br>
        <img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
    </page_header>
    <table>
        <tr>
            <td colspan="9">
                <b>B. Pengetahuan dan Keterampilan</b>
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
                <th colspan="3" rowspan="2">Mata Pelajaran</th>
                <th colspan="3">Pengetahuan</th>
                <th colspan="3">Keterampilan</th>
            </tr>
            <tr>
                <th>Nilai</th>
                <th>Predikat</th>
                <th>Deskripsi</th>
                <th>Nilai</th>
                <th>Predikat</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="9"><b>KELOMPOK B</b></td>
            </tr>
            <?php $no = 1 ?>
            <?php
            $filterB = array_filter($details, function ($ket) {
                return $ket['kelompok'] == "B";
            });
            $kelompokB = array_values($filterB);
            ?>
            <?php foreach ($kelompokB as $kelB) { ?>
                <tr>
                    <td valign="middle"><?= $no ?></td>
                    <td valign="middle" colspan="2" style="width:100px;"><?= $kelB['mapel'] ?></td>
                    <td valign="middle"><?= $kelB['nilai_pengetahuan'] ?></td>
                    <td valign="middle"><?= $kelB['predikat_pengetahuan'] ?></td>
                    <td style="width:130px;font-size:11px; padding: 20px 10px;"><?= wordwrap($kelB['desk_pengetahuan'], 100, "<br />\n") ?></td>
                    <td valign="middle"><?= $kelB['nilai_keterampilan'] ?></td>
                    <td valign="middle"><?= $kelB['predikat_keterampilan'] ?></td>
                    <td style="width:130px;font-size:11px; padding: 20px 10px;"><?= wordwrap($kelB['desk_keterampilan'], 100, "<br />\n") ?></td>
                </tr>
                <?php $no++; ?>
            <?php } ?>
            <tr>
                <td colspan="9"><b>MUATAN LOKAL</b></td>
            </tr>
            <?php
            $filterMulok = array_filter($details, function ($ket) {
                return $ket['kelompok'] == "MULOK";
            });
            $kelompokMulok = array_values($filterMulok);
            ?>
            <?php foreach ($kelompokMulok as $mulok) { ?>
                <tr>
                    <td valign="middle"><?= $no ?></td>
                    <td valign="middle" colspan="2" style="width:100px;"><?= $mulok['mapel'] ?></td>
                    <td valign="middle"><?= $mulok['nilai_pengetahuan'] ?></td>
                    <td valign="middle"><?= $mulok['predikat_pengetahuan'] ?></td>
                    <td style="width:130px;font-size:11px; padding: 20px 10px;"><?= wordwrap($mulok['desk_pengetahuan'], 100, "<br />\n") ?></td>
                    <td valign="middle"><?= $mulok['nilai_keterampilan'] ?></td>
                    <td valign="middle"><?= $mulok['predikat_keterampilan'] ?></td>
                    <td style="width:130px;font-size:11px; padding: 20px 10px;"><?= wordwrap($mulok['desk_keterampilan'], 100, "<br />\n") ?></td>
                </tr>
                <?php $no++; ?>
            <?php } ?>
        </tbody>
    </table>
    <page backtop="20mm" backbottom="7mm" backleft="10mm" backright="10mm"
        backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
        <page_header><br>
            <img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
        </page_header>
        <table class="table">
            <thead>
                <tr>
                    <th colspan="2" rowspan="2" style="width:190px">KKM</th>

                    <th colspan="8" style="width:510px">Predikat</th>
                </tr>
                <tr>
                    <th colspan="2">Kurang (D)</th>
                    <th colspan="2">Cukup (C)</th>
                    <th colspan="2">Baik (B)</th>
                    <th colspan="2">Sangat Baik (SB)</th>
                </tr>
            </thead>
            <tbody>
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
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>C. Ekstrakurikuler</b>
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
                    <th style="width:150px">Kegiatan Ekstrakurikuler</th>
                    <th style="width:70px">Nilai</th>
                    <th style="width:440px">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($exschool)) { ?>
                    <?php $no = 1; ?>
                    <?php foreach ($exschool as $ne) { ?>
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
                            <td style="width:340px">
                                <?= $ne['desk']; ?>
                            </td>
                        </tr>
                    <?php $no++;
                    } ?>
                <?php } else { ?>
                    <?= '<tr><td colspan="4">-</td></tr>' ?>
                <?php } ?>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>D. Saran - Saran</b>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:685px;">
                    <?= $catatan_naik_kelas; ?>
                </td>
            </tr>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>E. Tinggi dan Berat Badan</b>
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
                    <th rowspan="2" style="width:10px">No</th>
                    <th rowspan="2" style="width:350px">Aspek Yang Dinilai</th>
                    <th colspan="2" style="width:320px">Semester</th>
                </tr>
                <tr>
                    <th>1</th>
                    <th>2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Tinggi Badan</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Berat Badan</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>F. Kondisi Kesehatan</b>
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
                    <th style="width:350px">Aspek</th>
                    <th style="width:350px">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pendengaran</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Penglihatan</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Gigi</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Lainnya</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>G. PRESTASI</b>
                </td>
            </tr>
            <!--<tr>-->
            <!--    <td colspan="9">-->
            <!--        <br>-->
            <!--    </td>-->
            <!--</tr>-->
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:10px">No</th>
                    <th style="width:350px">Jenis Prestasi</th>
                    <th  style="width:320px">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prestasi)) { ?>
                    <?php $no = 1; ?>
                    <?php foreach ($prestasi as $p) { ?>
                        <tr>
                            <td>
                                <?= $no; ?>
                            </td>
                            <td>
                                <?= $p['jenis']; ?>
                            </td>
                            <td>
                                <?= $p['keterangan']; ?>
                            </td>
                        </tr>
                        <?php $no++; ?>
                    <?php } ?>
                <?php } else { ?>
                    <?= '<tr><td colspan="3">-</td></tr>' ?>
                <?php } ?>
            </tbody>
        </table>
        <br><br>
        <table>
            <tr>
                <td colspan="9">
                    <b>H. Ketidakhadiran</b>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <table class="table">
                        <tr>
                            <td style="width:200px">Sakit</td>
                            <td style="width:100px" class="ctr">
                                <?= $sakit; ?> hari
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
            <tr>
                <td colspan="9">
                    <br>
                </td>
            </tr>
        </table>

        <br><br>
        <?php if ($semester == 2) { ?>
            <table>
                <tr>
                    <td colspan="6">
                        <?php $naik_kelas = $tingkat + 1; ?>
                        <?php $kelas_now = $tingkat; ?>
                        <?php if ($kelas_now != 9) { ?>
                            <?php if ($naik == 'N') {
                                $naik = 'text-decoration: line-through';
                                $tidak_naik = '';
                            } else {
                                $naik = '';
                                $tidak_naik = 'text-decoration: line-through';
                            } ?>
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
                <td style="width:233px;text-align: center;">
                    Mengetahui<br>
                    Orang Tua/Wali,
                    <br><br><br><br><br>
                    <b>
                        ______________________
                    </b><br>
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
                    Guru Kelas,
                    <br><br><br><br><br>
                    <u><b>
                            <?= $wali_kelas; ?>
                        </b></u><br>
                </td>
            </tr>
            <tr>
                <td style="width:233px;text-align: center;">
                </td>
                <td style="width:233px;text-align: center;">
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