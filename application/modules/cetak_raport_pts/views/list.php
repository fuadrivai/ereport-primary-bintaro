<style type="text/css">
    .ctr {
        text-align: center
    }

    .nso {}
</style>
<div class="row">
    <div class="col-md-12">
        <p>
            <!--<a href="<?php echo base_url() . "/" . $url; ?>/cetak/<?php echo $this->uri->segment(3); ?>" class="btn btn-warning" target="_blank">Cetak</a>-->
        </p>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Cetak Raport PTS </h4>
            </div>
            <div class="content">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="40%">Nama</th>
                            <th width="50%">Cetak</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                        $no = 1;
                        if (!empty($siswa_kelas)) {
                            foreach ($siswa_kelas as $sk) {
                        ?>
                                <tr>
                                    <td>
                                        <?php echo $no; ?>
                                    </td>
                                    <td>
                                        <?php echo $sk['nama']; ?>
                                    </td>
                                    <td>
                                        <?php if ($sk['terkunci']) { ?>
                                            <a href="<?= base_url() . $url . "/mhis/" . $sk['id_siswa'] . "/" . $tasm; ?>" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i> Raport</a>
                                        <?php } else { ?>
                                            <?php if ($jenis_rapor != 2) { ?>
                                                <a href="<?php echo base_url() . $url . "/cetak_ikm/" . $sk['id_siswa'] . "/" . $tasm; ?>#toolbar=0"
                                                    class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>
                                                    Raport</a>
                                            <?php } else { ?>
                                                <a href="<?php echo base_url() . $url . "/cetak_sd/" . $sk['id_siswa'] . "/" . $tasm; ?>#toolbar=0"
                                                    class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>
                                                    Raport</a>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sk['terkunci']) { ?>
                                            <span class="badge badge-primary"> <i class="fa fa-warning"></i> Terkunci</span>
                                        <?php } else { ?>
                                            <a href="<?= base_url() . $url . "/kunci_rapor/" . $sk['id_siswa'] . "/" . $tasm; ?>" class="btn btn-danger btn-sm"><i class="fa fa-key"></i> Kunci Rapor</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                        <?php
                                $no++;
                            }
                        } else {
                            echo '<tr><td colspan="3">Belum ada data siswa</td></tr>';
                        }
                        ?>



                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>