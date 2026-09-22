<style type="text/css">
    .ctr {
        text-align: center
    }

    .nso {}
</style>
<div class="">
    <div class="col-md-12">
        <p>
            <!--<a href="<?php echo base_url() . "/" . $url; ?>/cetak/<?php echo $this->uri->segment(3); ?>" class="btn btn-warning" target="_blank">Cetak</a>-->
        </p>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Report Card</h4>
            </div>
            <div class="content">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="40%">Name</th>
                            <th width="50%">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($i = 0; $i < count($tahun); $i++) {
                            $th = $tahun[$i];
                            $_th =  substr($th['tahun'], 0, 4);
                            $smt = substr($th['tahun'], -1);
                            $tglmid = new DateTime($th['tgl_raport']);
                            $tglfinal = new DateTime($th['tgl_raport_kelas3']);
                            $today = new DateTime();
                        ?>
                            <tr>
                                <td><?php echo ($i + 1); ?></td>
                                <td><?php echo "Semester " . $smt . " / " . $_th . "-" . ($_th + 1); ?></td>
                                <td>
                                    <?php if (!empty($siswa_kelas)) { ?>
                                        <?php for ($j = 0; $j < count($siswa_kelas); $j++) { ?>
                                            <?php
                                            $sk = $siswa_kelas[$j];
                                            $bool =  ($sk['tingkat'] == 1 || $sk['tingkat'] == 2 || $sk['tingkat'] == 4 || $sk['tingkat'] == 5);
                                            ?>
                                            <?php if ($_th == $sk['ta']) { ?>
                                                <?php $url = $bool ? "cetak_ikm" : "cetak_sd"; ?>
                                                <?php $name = str_replace(" ", "_", $sk['nama']); ?>
                                                <?php $name2 = str_replace(["(", ")", ""], '', $name); ?>
                                                <?php $kelas = str_replace(" ", "_", $sk['nama_kelas']) ?>
                                                <?php $th_sms = str_replace(" ", "_", $th['tahun']) ?>
                                                <?php $file = $name2 . "-" . $kelas . "-" . $th_sms ?>
                                                <?php if ($tglmid <= $today) { ?>
                                                    <?php $isFile = $th['tahun'] == 20231 || $th['tahun'] == 20232 || $th['tahun'] == 20241 || $th['tahun'] == 20242 ?>
                                                    <?php if ($isFile) { ?>
                                                        <a href="https://document.mhis.link/mid/<?= $file ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-print"></i> Midterm Report</a>
                                                    <?php } else { ?>
                                                        <a href="<?= base_url() . "cetak_raport_pts/mhis/" . $sk['id_siswa'] . "/" . $th['tahun']  ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-print"></i> Midterm Report</a>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if ($tglfinal <= $today) { ?>
                                                    <?php $isFile = $th['tahun'] == 20231 || $th['tahun'] == 20232 || $th['tahun'] == 20241 ?>
                                                    <?php if ($isFile) { ?>
                                                        <a href="https://document.mhis.link/final/<?= $file ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-print"></i> Final Report </a>
                                                    <?php } else { ?>
                                                        <a href="<?= base_url() . "cetak_raport/diknas/" . $sk['id_siswa'] . "/" . $th['tahun'] ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-print"></i> Final Report</a>
                                                    <?php } ?>
                                                    <?php if ($bool) { ?>
                                                        <a style="<?=$th['tahun'] >= 20251?"display:none":""?>" href="https://report.mhis.link/bintaro/primary/cetak_raport/cetak_projek/<?php echo $sk['id_siswa'] . "/" . $th['tahun']; ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-print"></i> Report Project P5</a>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>