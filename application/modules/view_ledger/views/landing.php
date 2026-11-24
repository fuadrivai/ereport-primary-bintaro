<style>
.table-striped > tbody > tr:nth-of-type(odd) {
    background-color: #fcfcfc !important;
}
.table-striped > tbody > tr:nth-of-type(even) {
    background-color: #ffffff !important;
}
.table thead th {
    background-color: #f1f3f5 !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
    text-align: center;
    vertical-align: middle !important;
    font-size: 12px;
}
.table td {
    color: #333 !important;
    background-color: #fff !important;
    border: 1px solid #dee2e6 !important;
    vertical-align: middle !important;
    font-size: 12px;
}
.ctr {
    text-align: center;
}
.nav-tabs .nav-link.active {
    background-color: #198754 !important;
    color: #fff !important;
    border: none !important;
    font-weight: 600;
}
.nav-tabs .nav-link {
    color: #198754 !important;
    font-weight: 500;
    font-size: 14px;
}
.card {
    background-color: #ffffff !important;
}
.view-mode-bar {
    background: #f8f9fa;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.btn-view-mode.active {
    background-color: #0d6efd !important;
    color: #fff !important;
    border-color: #0d6efd !important;
    font-weight: 600;
}
.code-formula {
    font-family: Consolas, 'Courier New', monospace;
    font-size: 11.5px;
    background: #f8f9fa;
    padding: 3px 6px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    display: inline-block;
    color: #212529;
}

/* Modal Audit Screen Fitting & Positioning */
#modal_kroscek_siswa {
    z-index: 1060 !important;
}
@media (min-width: 992px) {
    #modal_kroscek_siswa.modal {
        left: 260px !important;
        width: calc(100% - 260px) !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    #modal_kroscek_siswa .modal-dialog {
        width: 96% !important;
        max-width: 1180px !important;
        margin: 25px auto !important;
    }
}
@media (max-width: 991px) {
    #modal_kroscek_siswa.modal {
        left: 0 !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    #modal_kroscek_siswa .modal-dialog {
        width: 96% !important;
        margin: 15px auto !important;
    }
}
#modal_kroscek_siswa .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25) !important;
    overflow: hidden;
}
#modal_kroscek_siswa .modal-body {
    padding: 16px 20px;
    max-height: calc(85vh - 140px);
    overflow-y: auto;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #ddd;">
            <div class="header" style="padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div>
                    <h4 class="title mb-0" style="font-weight:700; color:#198754;"><i class="fa fa-book"></i> Student Assessment Ledger</h4>
                    <small class="text-muted">
                        Class: <b><?= $dw['nmkelas'] ?? '-'; ?></b> | Homeroom: <b><?= $dw['nmguru'] ?? '-'; ?></b> | Academic Year: <b><?= $tasm; ?></b> (Curriculum: <?= (($jenis_rapor ?? 1) == 2 ? '2013' : 'Merdeka'); ?>)
                    </small>
                </div>
                <div style="margin-top: 5px;">
                    <a href="<?= base_url(); ?>view_ledger" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back to Class List</a>
                </div>
            </div>

            <div class="content" style="padding: 20px;">
                <?php if (!empty($role_info)): ?>
                    <div class="alert alert-<?= ($role_info['type'] == 'admin' ? 'info' : ($role_info['type'] == 'homeroom' ? 'success' : ($role_info['type'] == 'denied' ? 'danger' : 'warning'))); ?>" style="margin-bottom: 15px; border-left: 4px solid <?= ($role_info['type'] == 'admin' ? '#0dcaf0' : ($role_info['type'] == 'homeroom' ? '#198754' : ($role_info['type'] == 'denied' ? '#dc3545' : '#ffc107'))); ?>;">
                        <span class="label label-<?= ($role_info['type'] == 'admin' ? 'info' : ($role_info['type'] == 'homeroom' ? 'success' : ($role_info['type'] == 'denied' ? 'danger' : 'warning'))); ?>" style="font-size: 11px; margin-right: 5px;">
                            <i class="fa fa-<?= ($role_info['type'] == 'admin' ? 'shield' : ($role_info['type'] == 'homeroom' ? 'user' : ($role_info['type'] == 'denied' ? 'ban' : 'book'))); ?>"></i> <?= $role_info['badge']; ?>
                        </span>
                        <?= $role_info['desc']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($access_denied)): ?>
                    <div class="alert alert-danger" style="margin-top: 20px;">
                        <h4><i class="fa fa-ban"></i> Access Restricted</h4>
                        <p>You are only permitted to view the ledger for classrooms where you are assigned as <b>Homeroom Teacher</b> or teach a <b>Subject</b>.</p>
                        <p><a href="<?= base_url(); ?>view_ledger" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Your Class List</a></p>
                    </div>
                <?php else: ?>

                <!-- Main Tabs: Midterm & Finalterm -->
                <ul class="nav nav-tabs" id="scoreTabs" role="tablist" style="margin-bottom:15px;">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="midterm-tab" href="#" onclick="return showMainTab('midterm');">
                            <i class="fa fa-calendar-check-o"></i> Midterm Examination
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="finalterm-tab" href="#" onclick="return showMainTab('finalterm');">
                            <i class="fa fa-graduation-cap"></i> Finalterm Examination / End-of-Year
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="scoreTabsContent">
                    <!-- ================= MIDTERM TAB ================= -->
                    <div id="midterm" class="tab-pane" style="display:block;">
                        <!-- Mode Switcher Bar -->
                        <div class="view-mode-bar">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 600; color: #495057;"><i class="fa fa-eye"></i> View Mode:</span>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode active" id="btn_mid_summary" onclick="switchMode('mid', 'summary')">
                                        <i class="fa fa-table"></i> Summary Ledger (Total & Rank)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode" id="btn_mid_detail" onclick="switchMode('mid', 'detail')">
                                        <i class="fa fa-calculator"></i> Detailed Breakdown & Formula Check
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode" id="btn_mid_matrix" onclick="switchMode('mid', 'matrix')">
                                        <i class="fa fa-th"></i> Full Component Matrix
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="badge" style="background-color: #e7f1ff; color: #0c63e4; border: 1px solid #b6d4fe; padding: 6px 10px; font-size: 11.5px;">
                                    <i class="fa fa-lightbulb-o"></i> Click <b>[Audit]</b> next to any student's name for step-by-step formula verification
                                </span>
                            </div>
                        </div>

                        <!-- 1. Midterm Summary Mode -->
                        <div id="mid_view_summary" class="mode-view-container">
                            <div class="table-responsive">
                                <?= $table_midterm ?? '<p class="text-muted">No Midterm data available.</p>'; ?>
                            </div>
                        </div>

                        <!-- 2. Midterm Detail per Mapel Mode -->
                        <div id="mid_view_detail" class="mode-view-container" style="display:none;">
                            <div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:15px; margin-bottom:15px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                <label style="margin:0; font-weight:600; color:#333;"><i class="fa fa-filter"></i> Filter Subject:</label>
                                <select id="filter_mapel_mid" class="form-control" style="width:auto; min-width:280px; font-weight:600; display:inline-block;" onchange="filterMapelBox('mid', this.value)">
                                    <option value="all">-- Show All Subjects --</option>
                                    <?php if (!empty($active_mapels_mid)) {
                                        foreach ($active_mapels_mid as $am) { ?>
                                             <option value="<?= $am['id']; ?>"><?= htmlspecialchars($am['nama_mapel']) . " (" . htmlspecialchars($am['kd_singkat']) . ")"; ?></option>
                                     <?php } } ?>
                                </select>
                            </div>
                            <div id="mid_detail_boxes">
                                <?= $details_mapel_mid ?? '<p class="text-muted">No component breakdown available.</p>'; ?>
                            </div>
                        </div>

                        <!-- 3. Midterm Matrix Mode -->
                        <div id="mid_view_matrix" class="mode-view-container" style="display:none;">
                            <?= $matrix_midterm ?? '<p class="text-muted">No component matrix available.</p>'; ?>
                        </div>
                    </div>

                    <!-- ================= FINALTERM TAB ================= -->
                    <div id="finalterm" class="tab-pane" style="display:none;">
                        <!-- Mode Switcher Bar -->
                        <div class="view-mode-bar">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 600; color: #495057;"><i class="fa fa-eye"></i> View Mode:</span>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode active" id="btn_fin_summary" onclick="switchMode('fin', 'summary')">
                                        <i class="fa fa-table"></i> Summary Ledger (Total & Rank)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode" id="btn_fin_detail" onclick="switchMode('fin', 'detail')">
                                        <i class="fa fa-calculator"></i> Detailed Breakdown & Formula Check
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default btn-view-mode" id="btn_fin_matrix" onclick="switchMode('fin', 'matrix')">
                                        <i class="fa fa-th"></i> Full Component Matrix
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="badge" style="background-color: #e7f1ff; color: #0c63e4; border: 1px solid #b6d4fe; padding: 6px 10px; font-size: 11.5px;">
                                    <i class="fa fa-lightbulb-o"></i> Click <b>[Audit]</b> next to any student's name for step-by-step formula verification
                                </span>
                            </div>
                        </div>

                        <!-- 1. Finalterm Summary Mode -->
                        <div id="fin_view_summary" class="mode-view-container">
                            <div class="table-responsive">
                                <?= $table_finalterm ?? '<p class="text-muted">Finalterm scores not available yet.</p>'; ?>
                            </div>
                        </div>

                        <!-- 2. Finalterm Detail per Mapel Mode -->
                        <div id="fin_view_detail" class="mode-view-container" style="display:none;">
                            <div style="background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:15px; margin-bottom:15px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                <label style="margin:0; font-weight:600; color:#333;"><i class="fa fa-filter"></i> Filter Subject:</label>
                                <select id="filter_mapel_fin" class="form-control" style="width:auto; min-width:280px; font-weight:600; display:inline-block;" onchange="filterMapelBox('fin', this.value)">
                                    <option value="all">-- Show All Subjects --</option>
                                    <?php if (!empty($active_mapels_fin)) {
                                        foreach ($active_mapels_fin as $am) { ?>
                                            <option value="<?= $am['id']; ?>"><?= htmlspecialchars($am['nama_mapel']) . " (" . htmlspecialchars($am['kd_singkat']) . ")"; ?></option>
                                    <?php } } ?>
                                </select>
                            </div>
                            <div id="fin_detail_boxes">
                                <?= $details_mapel_fin ?? '<p class="text-muted">No component breakdown available.</p>'; ?>
                            </div>
                        </div>

                        <!-- 3. Finalterm Matrix Mode -->
                        <div id="fin_view_matrix" class="mode-view-container" style="display:none;">
                            <?= $matrix_finalterm ?? '<p class="text-muted">No component matrix available.</p>'; ?>
                        </div>
                    </div>
                </div>
                <?php endif; // end of !access_denied ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL AUDIT FORMULA & SCORES ================= -->
<div class="modal fade" id="modal_kroscek_siswa" tabindex="-1" role="dialog" aria-labelledby="modalKroscekLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #198754; color: white; padding: 15px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1; font-size: 24px;">&times;</button>
                <h4 class="modal-title" id="modalKroscekLabel" style="font-weight: 700; color: #fff;">
                    <i class="fa fa-calculator"></i> Student Formula & Score Audit: <span id="modal_student_name"></span>
                </h4>
                <div id="modal_student_meta" style="font-size: 13px; color: #d1e7dd; margin-top: 4px;"></div>
            </div>
            <div class="modal-body" id="modal_print_area">
                <div id="modal_kroscek_content">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #f8f9fa;">
                <button type="button" class="btn btn-default pull-left" onclick="cetakModalAudit()"><i class="fa fa-print"></i> Print Student Audit Sheet</button>
                <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// JSON audit data passed from controller
var auditDataMid = <?= $audit_json_mid ?? '{}'; ?>;
var auditDataFin = <?= $audit_json_fin ?? '{}'; ?>;

// 1. Main Tabs (Midterm vs Finalterm)
function showMainTab(tab) {
    document.querySelectorAll('#scoreTabs .nav-link').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('#scoreTabsContent .tab-pane').forEach(el => el.style.display = 'none');

    document.getElementById(tab + '-tab').classList.add('active');
    document.getElementById(tab).style.display = 'block';

    return false;
}

// 2. Mode Switcher (Summary, Detailed Breakdown, Matrix)
function switchMode(term, mode) {
    document.getElementById('btn_' + term + '_summary').classList.remove('active');
    document.getElementById('btn_' + term + '_detail').classList.remove('active');
    document.getElementById('btn_' + term + '_matrix').classList.remove('active');

    document.getElementById(term + '_view_summary').style.display = 'none';
    document.getElementById(term + '_view_detail').style.display = 'none';
    document.getElementById(term + '_view_matrix').style.display = 'none';

    document.getElementById('btn_' + term + '_' + mode).classList.add('active');
    document.getElementById(term + '_view_' + mode).style.display = 'block';
}

// 3. Subject Filter in Detail Mode
function filterMapelBox(term, mapelId) {
    var boxes = document.querySelectorAll('.mapel-detail-box-' + term);
    if (mapelId === 'all') {
        boxes.forEach(function(b) { b.style.display = 'block'; });
    } else {
        boxes.forEach(function(b) { b.style.display = 'none'; });
        var target = document.getElementById('detail_box_' + term + '_' + mapelId);
        if (target) {
            target.style.display = 'block';
        }
    }
}

// 4. Open Student Audit Modal
function bukaModalKroscek(term, idSiswa) {
    var sourceData = (term === 'mid') ? auditDataMid : auditDataFin;
    var sData = sourceData[idSiswa];

    if (!sData) {
        alert('Audit data not found for this student.');
        return;
    }

    var termLabel = (term === 'mid') ? 'MIDTERM EXAMINATION' : 'FINALTERM EXAMINATION / END-OF-YEAR';
    document.getElementById('modal_student_name').innerText = sData.nama;
    
    var lockBadge = sData.is_locked ? '<span class="badge" style="background:#198754; color:#fff;"><i class="fa fa-lock"></i> Report Card Locked</span>' : '<span class="badge" style="background:#0dcaf0; color:#000;"><i class="fa fa-refresh"></i> Live Calculation</span>';
    var rankInfo = sData.peringkat ? ' | Rank: <b>#' + sData.peringkat + '</b>' : '';
    document.getElementById('modal_student_meta').innerHTML = 'Term: <b>' + termLabel + '</b> | Classroom: <b><?= addslashes($dw['nmkelas'] ?? ''); ?></b> ' + rankInfo + ' | Status: ' + lockBadge;

    var html = '<div class="alert alert-info" style="font-size:12px; margin-bottom:15px; border-left: 4px solid #0dcaf0;">';
    html += '<i class="fa fa-info-circle"></i> The table below displays all raw assessment components, the official MHIS Assessment Policy SOP weightings, and the explicit step-by-step mathematical calculations used to produce the final score.';
    html += '</div>';

    html += '<div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch;">';
    html += '<table class="table table-bordered table-striped" style="font-size:12px; margin-bottom:0; min-width:880px;">';
    html += '<thead><tr style="background:#f1f3f5; color:#212529;">';
    html += '<th style="width:35px; text-align:center;">No</th>';
    html += '<th style="min-width:180px;">Subject</th>';
    html += '<th style="width:140px; text-align:center;">SOP Category</th>';
    html += '<th style="width:50px; text-align:center;">UT</th>';
    html += '<th style="width:50px; text-align:center;">Buzz</th>';
    html += '<th style="width:50px; text-align:center;">Mid</th>';
    if (term !== 'mid') {
        html += '<th style="width:50px; text-align:center;">Final</th>';
    }
    html += '<th style="width:60px; text-align:center;">Alt/Perf</th>';
    html += '<th style="min-width:280px;">Formula Steps & Mathematical Calculation</th>';
    html += '<th style="width:65px; text-align:center;">Knowledge (NP)</th>';
    html += '<th style="width:65px; text-align:center;">Skills (NK)</th>';
    html += '</tr></thead><tbody>';

    var no = 1;
    sData.scores.forEach(function(sc) {
        var katBadge = 'background:#0d6efd; color:#fff;';
        if (sc.kategori === 'without_written') {
            katBadge = 'background:#ffc107; color:#212529;';
        } else if (sc.kategori === 'with_performance') {
            katBadge = 'background:#0dcaf0; color:#212529;';
        }

        html += '<tr>';
        html += '<td style="text-align:center;">' + (no++) + '</td>';
        html += '<td><b>' + sc.nama_mapel + '</b> <span class="text-muted">(' + sc.kd_singkat + ')</span></td>';
        html += '<td style="text-align:center;"><span class="badge" style="' + katBadge + ' font-size:10.5px; padding:3px 6px;">' + sc.kategori_label + '</span></td>';

        // UT
        html += '<td style="text-align:center;">' + (sc.komponen.ut > 0 ? sc.komponen.ut : '<span class="text-muted">0</span>') + '</td>';
        // Buzz
        html += '<td style="text-align:center;">' + (sc.komponen.buzz > 0 ? sc.komponen.buzz : '<span class="text-muted">-</span>') + '</td>';
        // Mid
        html += '<td style="text-align:center;">' + (sc.komponen.mid > 0 ? sc.komponen.mid : '<span class="text-muted">0</span>') + '</td>';
        // Final
        if (term !== 'mid') {
            html += '<td style="text-align:center;">' + (sc.komponen.final > 0 ? sc.komponen.final : '<span class="text-muted">0</span>') + '</td>';
        }
        // Perf / Alt
        html += '<td style="text-align:center;">' + (sc.komponen.perf > 0 ? sc.komponen.perf : '<span class="text-muted">-</span>') + '</td>';

        // Formula
        var explain = (sc.penjelasan && sc.penjelasan !== '-') ? '<div style="font-size:11px; color:#6c757d; margin-top:3px;"><i class="fa fa-info-circle"></i> ' + sc.penjelasan + '</div>' : '';
        html += '<td><span class="code-formula">' + sc.langkah_hitung + '</span>' + explain + '</td>';

        // NP
        var npBadge = (sc.np >= 75) ? 'background:#d1e7dd; color:#0f5132; font-weight:bold;' : 'background:#fff3cd; color:#664d03; font-weight:bold;';
        html += '<td style="text-align:center;"><span class="badge" style="' + npBadge + ' font-size:13px; padding:4px 8px;">' + sc.np + '</span></td>';

        // NK
        html += '<td style="text-align:center;"><span class="badge" style="background:#e2e3e5; color:#41464b; font-size:12px; padding:3px 6px;" title="' + (sc.nk_langkah || '') + '">' + (sc.nk > 0 ? sc.nk : '-') + '</span></td>';

        html += '</tr>';
    });

    html += '</tbody></table></div>';
    document.getElementById('modal_kroscek_content').innerHTML = html;

    // Ensure modal is attached to body so it escapes any parent stacking context
    if (!$('#modal_kroscek_siswa').parent().is('body')) {
        $('#modal_kroscek_siswa').appendTo('body');
    }

    $('#modal_kroscek_siswa').modal('show');
}

// 5. Print Student Audit Sheet
function cetakModalAudit() {
    var printContents = document.getElementById('modal_print_area').innerHTML;
    var studentName = document.getElementById('modal_student_name').innerText;
    var studentMeta = document.getElementById('modal_student_meta').innerText;

    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Student Assessment Audit Sheet - ' + studentName + '</title>');
    win.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
    win.document.write('<style>body{padding:20px; font-family:Arial,sans-serif;} .badge{border:1px solid #ccc; padding:2px 6px;} .code-formula{font-family:monospace; font-size:11px;} </style>');
    win.document.write('</head><body>');
    win.document.write('<h3>Student Score & Formula Audit Sheet</h3>');
    win.document.write('<h4><b>' + studentName + '</b></h4>');
    win.document.write('<p>' + studentMeta + '</p><hr>');
    win.document.write(printContents);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() {
        win.print();
        win.close();
    }, 500);
}
</script>
