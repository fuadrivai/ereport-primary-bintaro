<style type="text/css">
    .ctr {
        text-align: center
    }

    .nso {}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">

                <a href="<?php echo base_url(); ?>n_catatan_sek/import/<?php echo  $id_kelas; ?>"
                    class="btn btn-danger"><i class="fa fa-download"></i> Download File Excel</a>
                <a href="<?php echo base_url(); ?>n_catatan_sek/upload/<?php echo  $id_kelas; ?>"
                    class="btn btn-success"><i class="fa fa-upload"></i> Upload File Excel</a>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Midtrem Notes</h4>
            </div>

            <div class="content">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nama</th>
                            <th width="30%">Progress</th>
                            <th width="30%">Need a Improvement</th>
                        </tr>
                    </thead>

                    <tbody>
                        <form method="post" id="<?php echo $url; ?>">
                            <input type="hidden" name="mode_form" value="<?php echo $mode_form; ?>">

                            <?php

                            $no = 1;
                            if (!empty($siswa_kelas)) {
                                foreach ($siswa_kelas as $sk) {
                                    echo '<input type="hidden" name="id_siswa_' . $no . '" value="' . $sk['id_siswa'] . '">';
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo $no; ?>
                                        </td>
                                        <td>
                                            <?php echo $sk['nama']; ?>
                                        </td>
                                        <td>
                                            <textarea rows="3" class="form-control input-sm" id="catatan_mid_<?php echo $no; ?>"
                                                name="catatan_mid_<?php echo $no; ?>"><?php echo $sk['catatan_mid']; ?></textarea>

                                        </td>
                                        <td>
                                            <textarea rows="3" class="form-control input-sm"
                                                id="catatan_final_<?php echo $no; ?>"
                                                name="catatan_final_<?php echo $no; ?>"><?php echo $sk['catatan_final']; ?></textarea>

                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                }
                            } else {
                                echo '<tr><td colspan="4">Belum ada data siswa</td></tr>';
                            }
                            ?>



                    </tbody>

                </table>

                <input type="hidden" name="jumlah" value="<?php echo $no; ?>">
                <button type="submit" class="btn btn-success" id="tbsimpan"><i class="fa fa-check"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>



</div>


<script type="text/javascript">
    $(document).on("ready", function () {

        $("#<?php echo $url; ?>").on("submit", function () {

            var data = $(this).serialize();


            $.ajax({
                type: "POST",
                data: data,
                url: base_url + "<?php echo $url; ?>/simpan",
                beforeSend: function () {
                    $("#tbsimpan").attr("disabled", true);
                },
                success: function (r) {
                    $("#tbsimpan").attr("disabled", false);
                    if (r.status == "ok") {
                        noti("success", r.data);
                    } else {
                        noti("danger", r.data);
                    }
                }
            });

            return false;
        });
    });

</script>