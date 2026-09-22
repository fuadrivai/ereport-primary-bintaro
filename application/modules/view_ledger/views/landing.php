<style>
.table-striped > tbody > tr:nth-of-type(odd) {
    background-color: #f9f9f9 !important;
}
.table-striped > tbody > tr:nth-of-type(even) {
    background-color: #ffffff !important;
}
.table thead th {
    background-color: #f1f1f1 !important;
    color: #222 !important;
    border: 1px solid #ccc !important;
}
.table td {
    color: #333 !important;
    background-color: #fff !important;
    border: 1px solid #ccc !important;
}
.nav-tabs .nav-link.active {
    background-color: #198754 !important;
    color: #fff !important;
    border: none !important;
}
.nav-tabs .nav-link {
    color: #198754 !important;
    font-weight: 500;
}
.card {
    background-color: #ffffff !important;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #ddd;">
            <div class="header" style="padding: 15px 20px; border-bottom: 1px solid #eee;">
                <h4 class="title mb-0" style="font-weight:600;">Ledger</h4>
                <small class="text-muted">Preview and compare Midterm & Finalterm scores</small>
            </div>

            <div class="content" style="padding: 20px;">
                <div class="mb-3">
                    <!--<a href="<?php echo base_url().$url; ?>/cetak" target="_blank" class="btn btn-success me-2">-->
                    <!--    <i class="fa fa-print"></i> Cetak Leger Nilai Pengetahuan dan Keterampilan-->
                    <!--</a> -->
                    <!--<a href="<?php echo base_url().$url; ?>/cetak_ekstra" target="_blank" class="btn btn-success">-->
                    <!--    <i class="fa fa-print"></i> Cetak Leger Nilai Ekstrakurikuler & Absensi-->
                    <!--</a>-->
                </div>

                <ul class="nav nav-tabs" id="scoreTabs" role="tablist" style="margin-bottom:15px;">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="midterm-tab" href="#" onclick="return showTab('midterm');">Midterm (UTS)</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="finalterm-tab" href="#" onclick="return showTab('finalterm');">Finalterm (UAS)</a>
                    </li>
                </ul>

                <div class="tab-content" id="scoreTabsContent">
                    <div id="midterm" class="tab-pane" style="display:block;">
                        <?= $table_midterm ?? '<p class="text-muted">Tidak ada data Midterm tersedia.</p>'; ?>
                    </div>

                    <div id="finalterm" class="tab-pane" style="display:none;">
                        <?= $table_finalterm ?? '<p class="text-muted">Nilai Finalterm belum tersedia.</p>'; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pure JS control like view_kd() tabs
function showTab(tab) {
    // Remove active state from all
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');

    // Add active to selected
    document.getElementById(tab + '-tab').classList.add('active');
    document.getElementById(tab).style.display = 'block';

    return false; // prevent page reload
}
</script>
