<style type="text/css">
    .ctr {
        text-align: center
    }

    .nso {}
</style>
<div class="row">
    <div class="col-md-12">
        <p>
            <!--<a href="<?= base_url() . "/" . $url; ?>/cetak/<?= $this->uri->segment(3); ?>" class="btn btn-warning" target="_blank">Cetak</a>-->
        </p>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Cetak Raport PAS</h4>
            </div>
            <div class="content">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Cetak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1 ?>
                        <?php if (!empty($siswa_kelas)) { ?>
                            <?php foreach ($siswa_kelas as $sk) { ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $sk['nama'] ?></td>
                                    <td class="text-center">
                                        <?php if ($sk['terkunci']) { ?>
                                            <a href="<?= base_url() . $url . "/diknas/" . $sk['id_siswa'] . "/" . $tasm; ?>" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i> Raport Diknas</a>
                                        <?php } else { ?>
                                            <a href="<?= base_url() . $url . "/cetak_sd/" . $sk['id_siswa'] . "/" . $tasm; ?>#toolbar=0" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i> Raport Diknas</a>
                                        <?php } ?>
                                        <?php if ($sk['rapor_project']) { ?>
                                            <a href="<?= base_url() . $url . "/cetak_projek/" . $sk['id_siswa'] . "/" . $tasm; ?>" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i> Raport Project</a>
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
                                <?php $no++; ?>
                            <?php } ?>
                        <?php } else { ?>
                            <?= '<tr><td colspan="3">Belum ada data siswa</td></tr>'; ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>