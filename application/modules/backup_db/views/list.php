<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" style="color: #000">
            <b>Informasi : </b><br>
            <p>Diharapkan selalu melakukan <b>Backup Database</b> setiap melakukan kenaikan maupun perpindahan Tahun Akademik</p>
        </div>

        <div class="card">
            <div class="header">
                <h4 class="title">Backup Database</h4>

            </div>
            <div class="content">

                <div class="panel">
                    <div class="panel-body">
                    <a href="<?=base_url('backup_db/db')?>" class="btn btn-danger pull-left"><i class="fa fa-download"></i>Backup Database !!</a>
                    </div>
                </div>

                <table class="table table-hover table-striped" id="datatabel" style="width: 100%">
                    <thead>
                        <td width="10%">No</td>
                        <td width="10%">Tahun Akademik</td>
                        <td width="20%">Nama File</td>
                        <td width="15%">Tanggal Backup</td>
                        <td width="15%">Aksi</td>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>
<?php
    
    if($this->session->flashdata('flash') == "berhasil"){
        echo "<script>alert('Backup database Berhasil!!')</script>";
    };
?>

<script type="text/javascript">
    $(document).on("ready", function() {
        pagination("datatabel", base_url+"backup_db/datatable", []);
    });
</script>
