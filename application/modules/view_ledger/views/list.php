<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">View Ledger</h4>

            </div>
            <div class="content">
                <?php if (($admlevel ?? '') == 'guru') { ?>
                    <div class="alert alert-info" style="margin-bottom: 15px; border-left: 4px solid #0dcaf0;">
                        <i class="fa fa-info-circle"></i> <b>Teacher Access Information:</b> Showing classes where you are assigned as <b>Homeroom Teacher</b> (can view all subjects) or <b>Subject Teacher</b> (can only view the subjects you teach).
                    </div>
                <?php } ?>

                <table class="table table-hover table-striped" id="datatabel" style="width: 100%">
                    <thead>
                        <td width="10%">No</td>
                        <td width="20%">Classroom</td>
                        <td width="50%">Homeroom</td>
                        <td width="20%">Action</td>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>


<?php if (($admlevel ?? '') == 'admin') { ?>
<div class="modal" id="modal_data">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Homeroom Teacher Assignment</h4>
            </div>
            <form class="form-horizontal" method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>">
            <input type="hidden" name="_id" id="_id" value="">
            <input type="hidden" name="_mode" id="_mode" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label for="id_kelas" class="col-sm-3 control-label">Classroom</label>
                    <div class="col-sm-9">
                        <?php echo form_dropdown('id_kelas',$p_kelas,'','class="form-control" required id="id_kelas"'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="id_guru" class="col-sm-3 control-label">Teacher</label>
                    <div class="col-sm-9">
                        <?php echo form_dropdown('id_guru',$p_guru,'','class="form-control" required id="id_guru"'); ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php } ?>

<script type="text/javascript">
    $(document).on("ready", function() {
        pagination("datatabel", base_url+"<?php echo $url; ?>/datatable", []);

        $("#<?php echo $nama_form; ?>").on("submit", function() {

            var data    = $(this).serialize();
    
            $.ajax({
                type: "POST",
                data: data,
                url: base_url+"<?php echo $url; ?>/simpan",
                success: function(r) {
                    if (r.status == "gagal") {
                        noti("danger", r.data);
                        $("#modal_data").modal('hide');
                    } else {
                        $("#modal_data").modal('hide');
                        noti("success", r.data);
                        pagination("datatabel", base_url+"<?php echo $url; ?>/datatable", []);
                    }
                }
            });

            return false;
        });
    });

    function edit(id) {
        if (id == 0) {
            $("#_mode").val('add');
        } else {
            $("#_mode").val('edit');
        }

        $("#modal_data").modal('show');

        $.ajax({
            type: "GET",
            url: base_url+"<?php echo $url; ?>/edit/"+id,
            success: function(data) {
                $("#_id").val(data.data.id);
                $("#id_kelas").val(data.data.id_kelas);
                $("#id_guru").val(data.data.id_guru);
            }
        });
        return false;
    }

    function hapus(id) {
        if (id == 0) {
            noti("danger", "Please select an item.");
        } else {
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    type: "GET",
                    url: base_url+"<?php echo $url; ?>/hapus/"+id,
                    success: function(data) {
                        noti("success", "Successfully deleted!");
                        pagination("datatabel", base_url+"<?php echo $url; ?>/datatable", []);
                    }
                });
            }
        }
        return false;
    }


</script>