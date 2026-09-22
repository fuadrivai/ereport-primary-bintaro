<div class="card">
    <div class="header">
        <h4 class="title">Form Tahun Aktif</h4>
    </div>
    <div class="content">
        <form method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>">
            <input type="hidden" name="_id" id="_id" value="<?= isset($id) ? $id : ''; ?>">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tahun" class="control-label">Tahun</label>
                        <?= form_dropdown('tahun', $p_tahun, '', 'class="form-control" id="tahun" required'); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tahun" class="control-label">Semester</label>
                        <?= form_dropdown('semester', $p_semester, '', 'class="form-control" id="semester" required'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tahun" class="control-label">Kepala Sekolah</label>
                        <input type="text" name="nama_kepsek" class="form-control" id="nama_kepsek" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tahun" class="control-label">NIP Kepsek</label>
                        <input type="text" name="nip_kepsek" class="form-control" id="nip_kepsek" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tahun" class="control-label">Tgl TTD Raport</label>
                        <input type="date" name="tgl_raport" class="form-control" id="tgl_raport" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tahun" class="control-label">Tgl TTD Raport Final</label>
                        <input type="date" name="tgl_raport_kelas3" class="form-control" id="tgl_raport_kelas3" required>
                    </div>
                </div>
            </div>
            <div class="row text-left">
                <div class="col-3">
                    <button data-toggle="modal" data-target="#modal_data" data-backdrop="static" data-keyboard="false" type="button" class="btn btn-success"><i class="fa fa-plus"></i> Template Rapor</button>
                </div>
            </div>
        </form>
        <br>
        <div class="col-12">
            <table class="table table-hover table-striped" id="datatabel">
                <thead>
                    <tr>
                        <th>Tingkat</th>
                        <th>Kurikulum</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <br>
        <div class="row text-center">
            <div class="col-12">
                <button type="button" onclick="save()" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>


<div class="modal" id="modal_data">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Template Rapor</h4>
            </div>
            <form class="form-horizontal" method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>">
                <input type="hidden" name="_id" id="_id" value="">
                <input type="hidden" name="_mode" id="_mode" value="">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="tingkat" class="col-sm-3 control-label">Tingkat kelas</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="tingkat" id="tingkat" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="template" class="col-sm-3 control-label">Kurikulum</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="template" id="template">
                                <option value="">-- Pilih Kurikulum --</option>
                                <?php for ($i = 0; $i < count($template); $i++) { ?>
                                    <?php $data = $template[$i] ?>
                                    <option value="<?= $data['id'] ?>"><?= $data['nama'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="addData()" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    let tahunId = "<?= isset($id) ? $id : null ?>";
    let tahunAktif = {
        id: tahunId,
        tahun: null,
        semester: null,
        nama_kepsek: null,
        nip_kepsek: null,
        tgl_raport: null,
        tgl_raport_kelas3: null,
        templates: []
    };
    $(document).ready(function() {
        tahunId == "" ? "" : getData(tahunId);
        tblTemplate = $('#datatabel').DataTable({
            paging: false,
            searching: false,
            ordering: false,
            data: tahunAktif.templates,
            columns: [{
                    data: "tingkat",
                    bSortable: false,
                    defaultContent: "--"
                },
                {
                    data: "nama",
                    bSortable: false,
                    defaultContent: "--"
                },
                {
                    data: "tingkat",
                    bSortable: false,
                    defaultContent: "--",
                    mRender: function(data, type, full) {
                        return `<a class="btn btn-xs btn-danger delete-template"><i class="fa fa-trash"></i> Hapus</a>`
                    }
                },
            ]
        });

        $('#datatabel').on('click', '.delete-template', function() {
            let data = tblTemplate.row($(this).parents('tr')).index();
            tahunAktif.templates.splice(data, 1);
            reloadJsonDataTable(tblTemplate, tahunAktif.templates);
        });
    })

    function addData() {
        let tingkat = $('#tingkat').val();
        let id_jenis_rapor = $('#template').val();
        let nama = $('#template').find('option:selected').text();

        if (id_jenis_rapor == "") {
            noti("danger", "Pilih Template Rapor");
            return false;
        }
        let isExist = 0;
        tahunAktif.templates.forEach(val => {
            if (tingkat == val.tingkat) {
                isExist++;
            }
        })

        if (isExist > 0) {
            noti("danger", "Template rapor pada tingkat tersebut sudah ada.");
            return false;
        }

        let template = {
            tingkat: tingkat,
            id_jenis_rapor: id_jenis_rapor,
            nama: nama
        }
        tahunAktif.templates.push(template);
        reloadJsonDataTable(tblTemplate, tahunAktif.templates);
        $('#modal_data').modal('hide');
    }

    function save() {
        tahunAktif.tahun = $('#tahun').val();
        tahunAktif.semester = $('#semester').val();
        tahunAktif.nama_kepsek = $('#nama_kepsek').val();
        tahunAktif.nip_kepsek = $('#nip_kepsek').val();
        tahunAktif.tgl_raport = $('#tgl_raport').val();
        tahunAktif.tgl_raport_kelas3 = $('#tgl_raport_kelas3').val();

        if (tahunAktif.nama_kepsek == "" || tahunAktif.tgl_raport == "" || tahunAktif.tgl_raport_kelas3 == "") {
            $('input, button').attr('disabled', false);
            noti("danger", "Silahkan lengkapi data !");
            return false;
        }

        if (tahunAktif.templates.length == 0) {
            $('input, button').attr('disabled', false);
            noti("danger", "Silahkan isi template rapor");
            return false;
        }

        let url = tahunAktif.id == "" ? "post" : "put"
        $.ajax({
            type: "POST",
            data: tahunAktif,
            url: base_url + "<?php echo $url; ?>/" + url,
            beforeSend: function() {
                $('input, button').attr('disabled', true);
            },
            success: function(r) {
                if (r.status == "gagal") {
                    $('input, button').attr('disabled', false);
                    noti("danger", r.data);
                    return false;
                }
                noti("success", r.data);
                setTimeout(() => {
                    window.location.href = base_url + "<?php echo $url; ?>"
                }, 2000);
            },
            error: function(r) {
                $('input, button').attr('disabled', false);
                noti("danger", r.data);
            }
        });
    }

    function getData() {
        $.ajax({
            type: "GET",
            url: base_url + "<?php echo $url; ?>/get_data_tahun/" + tahunId,
            success: function(year) {
                let tasm = year.tahun;
                let tahun = tasm.substring(0, 4);
                let semester = tasm.substring(4);
                $('#tahun').val(tahun).trigger('change');
                $('#semester').val(semester).trigger('change');
                $('#nama_kepsek').val(year.nama_kepsek);
                $('#nip_kepsek').val(year.nip_kepsek);
                $('#tgl_raport').val(year.tgl_raport);
                $('#tgl_raport_kelas3').val(year.tgl_raport_kelas3);
                tahunAktif.templates = year.templates.sort((a, b) => a.tingkat - b.tingkat);;
                reloadJsonDataTable(tblTemplate, tahunAktif.templates);
            },
            error: function(r) {
                $('input, button').attr('disabled', false);
                noti("danger", r.data);
            }
        });
    }


    function reloadJsonDataTable(dtable, json) {
        dtable.clear().draw();
        dtable.rows.add(json).draw();
    }
</script>