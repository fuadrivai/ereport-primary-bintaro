<div class="row">

    <div class="col-md-12">
        <div class="alert alert-warning" style="color: #000">
            <b>Instructions:</b><br>
            <ul>
                <li>This menu is used to input knowledge scores for the subject <b><i>
                            <?php echo $detil_mp['nmmapel'] . ", Class " . $detil_mp['nmkelas']; ?>.
                        </i></b> The scoring is integrated with the <b>Agilix Buzz LMS</b>.</li>

                <li>If the basic competencies have not been added yet, please click the <b><i>Add Topic</i></b> button.
                    Each competency will be created as a gradable item in Buzz LMS under the <b>"E-report Score →
                        Knowledge"</b> folder. To edit or delete a competency name, click the "<i
                        class="fa fa-pencil"></i>" or "<i class="fa fa-remove"></i>" button respectively.</li>

                <li>To enter student scores, go to the <b>Agilix Buzz LMS gradebook</b> and input scores under each
                    corresponding competency item. All scores should be entered on a <b><i>scale of 1–100</i></b>.</li>

                <li>If there are any updates or changes to scores in Buzz, please click the <b><i>Import Score from Buzz
                            LMS</i></b> button to synchronize the latest scores into this system.</li>
            </ul>

        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <a href="<?php echo base_url(); ?>view_mapel" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i> Back to Subject List
                </a>
                <?php if($detil_mp['kd_singkat']=="BTQ") {?>
                <!--<a href="<?php echo base_url(); ?>n_pengetahuan/cetak/<?php echo $detil_mp['id_mapel']."-".$detil_mp['id_kelas']; ?>" class="btn btn-warning" target="_blank">-->
                <!--    <i class="fa fa-print"></i> Print Report-->
                <!--</a>-->
                <a href="<?php echo base_url(); ?>n_pengetahuan/import/<?php echo $detil_mp['id_mapel']."-".$detil_mp['id_kelas']; ?>"
                    class="btn btn-danger">
                    <i class="fa fa-download"></i> Download Excel Template
                </a>

                <a href="<?php echo base_url(); ?>n_pengetahuan/upload/<?php echo $detil_mp['id_mapel']."-".$detil_mp['id_kelas']; ?>"
                    class="btn btn-success">
                    <i class="fa fa-upload"></i> Upload Excel File
                </a>
                <?php } else { ?>
                <a href="#" class="btn btn-success" onclick="return importFromBuzz();">
                    <i class="fa fa-cloud-download"></i> Import Scores from Buzz LMS
                </a>
                <a href="#" class="btn btn-success" onclick="return create_gradebook_items_in_buzz();">
                    <i class="fa fa-refresh"></i> Synchronize Topics to Buzz LMS
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h5 class="title"><?php if ($detil_mp['tingkat'] == '1' || $detil_mp['tingkat'] == '4'){ ?>Sumatif
                    <?php }else{ ?> Knowledge <?php } ?> Score &raquo;
                    <?php echo $detil_mp['nmmapel']." - ".$detil_mp['nmkelas']; ?></h5>
            </div>
            <div class="content">
                <p>
                    <a href="#" onclick="return edit(0);" class="btn btn-info"><i class="fa fa-plus-circle"></i> Create
                        Topic</a>
                </p>
                <ul class="list-group" id="list_kd">
                    <div id="list_kd_2" style="margin-bottom: 10px"></div>

                    <li class="list-group-item"
                        onclick="return view_kd(<?php echo $detil_mp['id_mapel'].", ".$detil_mp['id_kelas']; ?>,'t');">
                        <a href="#"><i class="fa fa-chevron-right"></i> Midterm Score</a></li>
                    <li class="list-group-item"
                        onclick="return view_kd(<?php echo $detil_mp['id_mapel'].", ".$detil_mp['id_kelas']; ?>,'a');">
                        <a href="#"><i class="fa fa-chevron-right"></i> Final Score</a></li>
                    <li class="list-group-item"
                        onclick="return view_kd(<?php echo $detil_mp['id_mapel'].", ".$detil_mp['id_kelas']; ?>,'c');">
                        <a href="#"><i class="fa fa-chevron-right"></i> CATATAN DIKNAS</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Score Details </h4>
            </div>
            <div class="content">
                <form name="f_input_nilai" method="post" action="#" id="f_input_nilai">
                    <input type="hidden" name="id_guru_mapel" id="id_guru_mapel" value="<?php echo $detil_mp['id']; ?>">
                    <input type="hidden" name="id_mapel_kd" id="id_mapel_kd" value="">
                    <input type="hidden" name="jenis" id="jenis" value="">
                    <div id="load_nilai">

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php //echo var_dump($detil_mp); ?>
<div class="modal" id="modal_data">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Set KD</h4>
            </div>
            <form class="form-horizontal" method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>"
                onsubmit="return simpan_kd();">
                <input type="hidden" name="_id" id="_id" value="">
                <input type="hidden" name="_mode" id="_mode" value="">
                <input type="hidden" name="id_guru" id="id_guru" value="<?php echo $detil_mp['id_guru']; ?>">
                <input type="hidden" name="id_mapel" id="id_mapel" value="<?php echo $detil_mp['id_mapel']; ?>">
                <input type="hidden" name="tingkat" id="tingkat" value="<?php echo $detil_mp['tingkat']; ?>">
                <input type="hidden" name="jenis" id="jenis" value="P">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama" class="col-sm-2 control-label">Code</label>
                        <div class="col-sm-10">
                            <input type="text" name="kode" class="form-control" autofocus="true" id="kode" required>
                            <!--<p style="color: red; font-size: 10pt">DILARANG memakai spasi, koma, strip. Contoh : K01</p>-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama" class="col-sm-2 control-label">Indicator Name</label>
                        <div class="col-sm-10">
                            <input type="text" name="nama" class="form-control" id="nama" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama" class="col-sm-2 control-label">Mid/Final</label>
                        <div class="col-sm-10">
                            <select name="semester" class="form-control" id="semester" required>
                                <option value="1">Mid</option>
                                <option value="2">Final</option>
                            </select>
                            <!--<p style="color: red; font-size: 10pt">DILARANG memakai spasi, koma, strip. Contoh : K01</p>-->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="tbSimpanKd">Submit</button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<input type="hidden" id="view_kd_kelas" value="<?php echo $detil_mp['id_kelas']; ?>">
<input type="hidden" id="is_btq" value="<?php echo $detil_mp['kd_singkat'] == "BTQ" ? '1' : '0'; ?>">
<script type="text/javascript">
    id_guru_mapel = document.getElementById('id_guru_mapel').value;
    let isBtq = document.getElementById('is_btq').value === '1';
    $(document).on("ready", function () {
        view_kd(0, 0);
        list_kd();

        $('#list_kd li').on('click', function () {
            $('li.active').removeClass('active');
            $(this).addClass('active');
        });
        $("#f_input_nilai").on("submit", function () {
            var data = $(this).serialize();

            $.ajax({
                type: "POST",
                data: data,
                url: base_url + "<?php echo $url; ?>/simpan_nilai",
                beforeSend: function () {
                    $("#tbSimpan").attr("disabled", true);
                },
                success: function (r) {
                    $("#tbSimpan").attr("disabled", false);
                    if (r.status == "gagal") {
                        noti("danger", r.data);
                    } else {
                        $("#modal_data").modal('hide');
                        noti("success", r.data);
                        pagination("datatabel", base_url + "data_guru/datatable", []);
                    }
                }
            });
            return false;
        });
    });

    function view_kd(id, kelas, jenis = 'h') {

        if (id == 0 && kelas == 0) {
            $("#load_nilai").html('<div class="alert alert-warning">Silakan pilih KD di samping</div>');
        } else {
            $("#id_mapel_kd").val(id);
            $("#jenis").val(jenis);

            $("#load_nilai").html("Loading...");
            $.getJSON(base_url + "<?php echo $url; ?>/ambil_siswa/" + kelas + "/" + id + "/" + jenis, function (data) {
                $("#load_nilai").show('slow');

                if (jenis == 'c') {
                    html =
                        '<table class="table table-condensed table-bordered table-hover"><thead><tr><th width="5%">No</th><th width="15%">Nama</th><th width="35%">Catatan Mid</th><th width="35%">Catatan Final</th></tr></thead><tbody>';
                    var i = 1;
                    $.each(data.data, function (k, v) {
                        html += '<tr><td>' + i + '</td><td>' + v.nama +
                            '</td><td><input name="id_siswa[]" type="hidden" value="' + v.ids +
                            '"><textarea name="nilai_mid[]" class="form-control input-sm" value="' + v
                            .nilai_mid + '">' + v.nilai_mid +
                            '</textarea></td><td><textarea name="nilai[]" class="form-control input-sm" value="' +
                            v.nilai + '">' + v.nilai + '</textarea></td></tr>';
                        i++;
                    });
                    html +=
                        '</tbody></table><p><button type="submit" class="btn btn-success" id="tbSimpan"><i class="fa fa-check"></i> Simpan</button> &nbsp; <a href="#" class="btn btn-warning" onclick="return view_kd(0, 0);"><i class="fa fa-minus-circle"></i> Batal</a></p>';
                } else {
                    html =
                        '<table class="table table-condensed table-bordered table-hover"><thead><tr><th width="10%">No</th><th width="60%">Nama</th><th width="30%">Nilai</th></tr></thead><tbody>';
                    var i = 1;
                    $.each(data.data, function (k, v) {
                        html +=
                            `<tr><td>${i}</td><td>${v.nama}</td><td><input name="id_siswa[]" type="hidden" value="${v.ids}"><input ${isBtq?'':"disabled"} name="nilai[]" type="number" min="0" max="100" class="form-control input-sm" value="${v.nilai}"></td></tr>`;
                        i++;
                    });

                    if (isBtq) {
                        html +=
                            '</tbody></table><p><button type="submit" class="btn btn-success" id="tbSimpan"><i class="fa fa-check"></i> Simpan</button> &nbsp; <a href="#" class="btn btn-warning" onclick="return view_kd(0, 0);"><i class="fa fa-minus-circle"></i> Batal</a></p>';
                    }
                }

                $("#load_nilai").html(html);
            });

        }
        return false;
    }

    function importFromBuzz() {
        if (!confirm("Are you sure you want to import scores from Buzz LMS?")) return false;

        $.ajax({
            type: "GET",
            url: base_url +
                "n_pengetahuan/import_from_agilix/<?php echo $detil_mp['id_mapel'] . '-' . $detil_mp['id_kelas']; ?>",
            beforeSend: function () {
                noti("info", "Importing scores from Buzz LMS, please wait...");
            },
            success: function (response) {
                if (response.status === "sukses" || response.status === "success") {
                    noti("success", response.data || "Scores imported successfully!");
                    // Optional: refresh view_kd if needed
                    view_kd(0, 0);
                } else {
                    noti("danger", response.data || "Failed to import scores.");
                }
            },
            error: function (xhr, status, error) {
                noti("danger", "An error occurred while importing: " + error);
            }
        });

        return false;
    }

    function create_gradebook_items_in_buzz() {
        if (!confirm("Are you sure you want to synchronize topics from Buzz LMS?")) return false;

        $.ajax({
            type: "GET",
            url: base_url +
                "n_pengetahuan/create_gradebook_items_in_buzz/<?php echo $detil_mp['id_mapel'] . '-' . $detil_mp['id_kelas']; ?>",
            beforeSend: function () {
                noti("info", "Syncronize topic to Buzz LMS, please wait...");
            },
            success: function (response) {
                if (response.status === "sukses" || response.status === "success") {
                    noti("success", response.data || "Scores imported successfully!");
                    // Optional: refresh view_kd if needed
                    view_kd(0, 0);
                } else {
                    noti("danger", response.data || "Failed to import scores.");
                }
            },
            error: function (xhr, status, error) {
                noti("danger", "An error occurred while importing: " + error);
            }
        });

        return false;
    }

    function edit(id) {
        if (id == 0) {
            $("#_mode").val('add');
        } else {
            $("#_mode").val('edit');
        }
        $("#kode").prop("readonly", true);
        $("#nama").prop("readonly", true);
        $("#tbSimpan").prop("disabled", true);
        $("#kode").val('');
        $("#nama").val('');
        $("#modal_data").modal('show');
        $.ajax({
            type: "GET",
            url: base_url + "set_kd/edit/" + id,
            success: function (data) {
                $("#_id").val(data.data.id);
                $("#mapel").val(data.data.id_mapel + "-" + data.data.tingkat);
                $("#jenis").val(data.data.jenis);
                $("#kode").val(data.data.no_kd);
                $("#nama").val(data.data.nama_kd);
                $("#semester").val(data.data.mid_final);
                $("#kode").prop("readonly", false);
                $("#nama").prop("readonly", false);
                $("#tbSimpan").prop("disabled", false);
            }
        });
        return false;
    }

    function hapus(id) {
        if (id == 0) {
            noti("danger", "Silakan pilih datanya..!");
        } else {
            if (confirm('Anda yakin...?')) {
                $.ajax({
                    type: "GET",
                    url: base_url + "set_kd/hapus/" + id,
                    success: function (data) {
                        noti("success", "Berhasil dihapus...!");
                        list_kd();
                    }
                });
            }
        }
        return false;
    }

    function simpan_kd() {
        var data = $("#f_setmapel").serialize();

        $.ajax({
            type: "POST",
            data: data,
            url: base_url + "set_kd/simpan",
            beforeSend: function () {
                $("#tbSimpanKd").attr("disabled", true);
            },
            success: function (r) {
                $("#tbSimpanKd").attr("disabled", false);
                if (r.status == "gagal") {
                    noti("danger", r.data);
                } else {
                    $("#modal_data").modal('hide');
                    noti("success", r.data);
                    list_kd();
                }
            }
        });
        return false;
    }

    function list_kd() {
        $.ajax({
            type: "GET",
            url: base_url + "n_pengetahuan/list_kd/" + id_guru_mapel,
            beforeSend: function () {
                $("#list_kd_2").html('<i class="fa fa-spin fa-spinner"></i> Loading');
            },
            success: function (data) {
                var h = '';
                if (data.length > 0) {
                    $.each(data, function (i, v) {
                        h += '<li class="list-group-item"><a href="#" onclick="return view_kd(' + v
                            .id + ', ' + document.getElementById('view_kd_kelas').value + ');">(' +
                            v.no_kd + ') ' + v.nama_kd +
                            '</a><div class="pull-right"><a href="#" onclick="return edit(' + v.id +
                            ');" class="btn btn-xs btn-success"><i class="fa fa-pencil"></i> </a><a href="#" onclick="return hapus(' +
                            v.id +
                            ');" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i> </a></div></li>';
                    });
                } else {
                    h += '<div class="alert alert-info">KD Belum satupun diinputkan</div>';
                }

                $("#list_kd_2").html(h);
            }
        });
    }
</script>