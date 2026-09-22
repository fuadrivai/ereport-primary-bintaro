<div class="card ">
    <div class="header">
        <h4 class="title">List Pengumuman</h4>

    </div>
    <div class="content">

        <div class="panel">
            <div class="panel-body ">
                <a href="#" class="btn btn-success pull-left" data-toggle="modal" data-target="#modal_data"
                    data-backdrop="static" data-keyboard="false">Tambah</a>
            </div>
        </div>

        <table class="table table-hover table-striped" id="datatabel" style="width: 100%">
            <thead>
                <td>No</td>
                <td>Kelas</td>
                <td>Judul</td>
                <td>Link</td>
                <!-- <td>Tgl Dibuat</td>
                <td>Tgl Terkirim</td>
                <td>Status</td> -->
                <!-- <td>Action</td> -->
            </thead>

        </table>
    </div>
</div>

<div class="modal" id="modal_data">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Kelas</h4>
            </div>
            <form class="form-horizontal" method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>">
                <input type="hidden" name="_id" id="_id" value="">
                <input type="hidden" name="_mode" id="_mode" value="">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="class" class="col-sm-3 control-label">Kelas</label>
                        <div class="col-sm-9">
                            <?php
                            $wali_kelas = $this->session->userdata('app_rapot_walikelas');
                            $is_wali = $wali_kelas['is_wali'];
                            if ($is_wali == true) {


                                ?>
                                <select name="class_name" id="" class="form-control">
                                        <option value="<?= $wali_kelas['nama_walikelas'] ?>"> <?= $wali_kelas['nama_walikelas'] ?></option>
                                </select>
                                <?php
                            } else {
                                ?>
                                <select name="class_name" id="" class="form-control">
                                    <?php foreach ($p_class as $class): ?>
                                        <option value="<?= $class['nama'] ?>"> <?= $class['nama'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-sm-3 control-label">Judul</label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control" id="title" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="link" class="col-sm-3 control-label">Link</label>
                        <div class="col-sm-9">
                            <input type="text" name="link" class="form-control" id="link" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    $(document).ready(function () {
        pagination("datatabel", base_url + "<?php echo $url; ?>/datatable", []);

        $("#<?php echo $nama_form; ?>").on("submit", function () {
            $("#_mode").val('add');
            var data = $(this).serialize();

            $.ajax({
                type: "POST",
                data: data,
                url: base_url + "<?php echo $url; ?>/simpan",
                success: function (r) {
                    if (r.status == "gagal") {
                        noti("danger", r.data);
                    } else {
                        $("#modal_data").modal('hide');
                        noti("success", r.data);
                        pagination("datatabel", base_url + "<?php echo $url; ?>/datatable", []);
                    }
                }
            });

            return false;
        });
    });
</script>