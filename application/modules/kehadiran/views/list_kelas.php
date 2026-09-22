<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Vew Attendance</h4>

            </div>
            <div class="content">

                <table class="table table-hover table-striped" id="datatabel" style="width: 100%">
                    <thead>
                        <td width="10%">No</td>
                        <td width="20%">Classroom</td>
                        <td width="50%">Homeroom</td>
                        <td width="20%">Action</td>
                    </thead>
                    <tbody>
                        <?php foreach ($list_kelas as $index => $kelas): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $kelas['nmkelas']; ?></td>
                                <td><?php echo $kelas['nmguru']; ?></td>
                                <td>
                                    <a href="<?php echo base_url('kehadiran/detail_kelas/' . $kelas['id_kelas']); ?>" class="btn btn-info btn-sm">View Attendance</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#datatabel').DataTable({
            ordering: false, // <--- ini kuncinya
            pageLength: 10,
        });
    });
</script>