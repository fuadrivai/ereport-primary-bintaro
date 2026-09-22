<style>
    .welcome-section {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        border-radius: 20px;
        padding: 40px;
        color: #fff;
        margin-bottom: 40px;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
        z-index: 1;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .welcome-content {
        position: relative;
        z-index: 2;
    }

    .welcome-section h3 {
        font-weight: 800;
        margin-top: 0;
        letter-spacing: -0.5px;
    }

    .welcome-section p {
        font-size: 16px;
        opacity: 0.9;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        flex-shrink: 0;
    }

    .stat-details h4 {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-details .number {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-top: 4px;
    }

    .bg-indigo { background: linear-gradient(135deg, #6366f1, #818cf8); }
    .bg-pink { background: linear-gradient(135deg, #ec4899, #f472b6); }
    .bg-emerald { background: linear-gradient(135deg, #10b981, #34d399); }
    .bg-amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .bg-sky { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
    .bg-violet { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }


    /* ── Low-completable teachers widget ─────────────────────────────── */
    .alert-widget {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #fee2e2;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(239,68,68,0.08);
    }

    .alert-widget-header {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
    }

    .alert-widget-header .icon-wrap {
        width: 42px;
        height: 42px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .alert-widget-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 15px;
    }

    .alert-widget-header p {
        margin: 2px 0 0;
        font-size: 12px;
        opacity: 0.85;
    }

    .alert-widget-body {
        padding: 0;
    }

    .low-teacher-row {
        display: flex;
        align-items: center;
        padding: 14px 24px;
        border-bottom: 1px solid #fef2f2;
        gap: 14px;
        transition: background 0.2s;
    }

    .low-teacher-row:last-child {
        border-bottom: none;
    }

    .low-teacher-row:hover {
        background: #fff5f5;
    }

    .low-teacher-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fee2e2;
        color: #ef4444;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .low-teacher-name {
        flex: 1;
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
    }

    .low-teacher-subjects {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }

    .low-teacher-badge {
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: #fff;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .low-teacher-empty {
        padding: 30px 24px;
        text-align: center;
        color: #94a3b8;
        font-size: 14px;
    }

    .low-teacher-loading {
        padding: 24px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.3; }
    }

    /* ── Low-completion-% teachers widget (orange) ───────────────────── */
    .alert-widget-orange {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #fed7aa;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(249,115,22,0.08);
    }

    .alert-widget-orange .alert-widget-header {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
    }

    .alert-widget-orange .low-teacher-row {
        border-bottom: 1px solid #fff7ed;
    }

    .alert-widget-orange .low-teacher-row:hover {
        background: #fff7ed;
    }

    .alert-widget-orange .low-teacher-rank {
        background: #ffedd5;
        color: #f97316;
    }

    .alert-widget-orange .low-teacher-badge {
        background: linear-gradient(135deg, #f97316, #fb923c);
    }

</style>

<?php 
$wali_kelas = $this->session->userdata('app_rapot_walikelas');
$is_wali = $wali_kelas['is_wali'];
?>

<div class="welcome-section">
    <div class="welcome-content">
        <h3>Welcome to the Integrated Academic System</h3>
        <p style="margin-bottom: 15px; font-weight: 500;">School Management Platform, e-Learning & Communication Integration</p>
        <p>You are logged in as <strong><?php echo $this->session->userdata('app_rapot_nama'); ?></strong></p>
        <?php if($is_wali): ?>
            <p><span class="badge" style="background: rgba(255,255,255,0.2); padding: 8px 15px;">Homeroom Teacher: <?=$wali_kelas['nama_walikelas']?></span></p>
        <?php endif; ?>
    </div>
</div>
<?php if ($admlevel == 'admin'): ?>
<!-- ── Low Completable Teachers Widget ──────────────────────────────── -->
<div id="lowTeachersWidget" class="alert-widget" style="display:none;">
    <div class="alert-widget-header">
        <div class="icon-wrap"><i class="fa fa-exclamation-triangle"></i></div>
        <div>
            <h5>Teachers with Low LMS Completable</h5>
            <p>Average student completable &lt; 10 — requires attention</p>
        </div>
        <span id="lowTeacherCount" style="margin-left:auto; background:rgba(255,255,255,0.2); border-radius:20px; padding:4px 14px; font-size:13px; font-weight:700;"></span>
    </div>
    <div class="alert-widget-body" id="lowTeacherList">
        <div class="low-teacher-loading"><i class="fa fa-spinner fa-spin"></i> Loading teacher data...</div>
    </div>
</div>

<!-- ── Low Completion % Teachers Widget ─────────────────────────────── -->
<div id="lowPctTeachersWidget" class="alert-widget alert-widget-orange" style="display:none;">
    <div class="alert-widget-header">
        <div class="icon-wrap"><i class="fa fa-bar-chart"></i></div>
        <div>
            <h5>Teachers with Low Student Completion Rate</h5>
            <p>Average completion % per subject &lt; 35% — requires attention</p>
        </div>
        <span id="lowPctTeacherCount" style="margin-left:auto; background:rgba(255,255,255,0.2); border-radius:20px; padding:4px 14px; font-size:13px; font-weight:700;"></span>
    </div>
    <div class="alert-widget-body" id="lowPctTeacherList">
        <div class="low-teacher-loading"><i class="fa fa-spinner fa-spin"></i> Loading teacher data...</div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-indigo">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <div class="stat-details">
                <h4>Academic Year</h4>
                <div class="number"><?=$tasm?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-emerald">
                <i class="fa fa-user-secret"></i>
            </div>
            <div class="stat-details">
                <h4>Total Teachers</h4>
                <div class="number"><?=$jml_guru?> Persons</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-sky">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-details">
                <h4>Total Students</h4>
                <div class="number">M: <?=$jml_siswa['jml_l']?> | F: <?=$jml_siswa['jml_p']?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-amber">
                <i class="fa fa-clipboard"></i>
            </div>
            <div class="stat-details">
                <h4>Subjects</h4>
                <div class="number"><?=$jml_mapel?> Subjects</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-pink">
                <i class="fa fa-object-group"></i>
            </div>
            <div class="stat-details">
                <h4>Total Classes</h4>
                <div class="number"><?=$jml_kelas?> Classes</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-violet">
                <i class="fa fa-asterisk"></i>
            </div>
            <div class="stat-details">
                <h4>Extracurriculars</h4>
                <div class="number"><?=$jml_ekstra?> Units</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Active Academic Year</h4>
            </div>
            <div class="content">
                <table class="table table-hover table-striped" id="datatabel" style="width: 100%">
                    <thead>
                        <th width="10%">Year</th>
                        <th width="20%">Principal</th>
                        <th width="15%">Report Date</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$tahun_akademik['tahun']??"--" ?></td>
                            <td><?=$tahun_akademik['nama_kepsek']??"--" ?></td>
                            <td><?=$tahun_akademik['tgl_raport']??"--" ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="header">
                <h4 class="title">Student Completable Buzz LMS</h4>
                <p class="category">Overall Student Completion across all subjects</p>
            </div>
            <div class="content">
                <div style="position: relative; height: 300px; display: flex; justify-content: center; align-items: center;">
                    <canvas id="buzzPieChart"></canvas>
                    <div id="chartLoading" style="position: absolute;">Loading data...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="header">
                <h4 class="title">KooBits Proficiency Summary</h4>
                <p class="category">Overall proficiency across all students</p>
            </div>
            <div class="content">
                <div style="position: relative; height: 300px; display: flex; justify-content: center; align-items: center;">
                    <canvas id="koobitsPieChart"></canvas>
                    <div id="koobitsLoading" style="position: absolute;">Loading data...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 20px 25px;">
                <div>
                    <h4 class="title" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Top 5 KooBits Students by Level</h4>
                    <p class="category" style="margin: 5px 0 0; color: #64748b;">Highest proficiency scores (Mastered + Passed) grouped by grade level</p>
                </div>
                <div style="background: #f1f5f9; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #475569;">
                    <i class="fa fa-trophy" style="color: #f59e0b;"></i> Rankings
                </div>
            </div>
            <div class="content" style="padding: 20px !important;">
                <div id="topStudentsLoading" class="text-center" style="padding: 40px;">
                    <i class="fa fa-spinner fa-spin fa-3x" style="color: #4f46e5;"></i>
                    <p style="margin-top: 15px; color: #64748b; font-weight: 500;">Calculating rankings...</p>
                </div>
                <div id="topStudentsContainer" class="row" style="display:none; margin: 0;">
                    <!-- Level blocks will be injected here -->
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $.getJSON('<?php echo base_url(); ?>scratch/get_student_completable', function(response) {
        if (response && response.status === 'success') {
            let coursePercentages = [];

            response.data.forEach(function(course) {
                if (course.students && course.students.length > 0) {
                    let courseCompletable = 0;
                    let courseCompleted = 0;

                    course.students.forEach(function(student) {
                        courseCompletable += parseFloat(student.completable) || 0;
                        courseCompleted += parseFloat(student.completed) || 0;
                    });

                    // Only factor in subjects that have a completable target > 0
                    if (courseCompletable > 0) {
                        let coursePct = (courseCompleted / courseCompletable) * 100;
                        coursePercentages.push(coursePct);
                    }
                }
            });

            $('#chartLoading').hide();

            let percentCompleted = 0;
            if (coursePercentages.length > 0) {
                let sumPct = coursePercentages.reduce((a, b) => a + b, 0);
                percentCompleted = sumPct / coursePercentages.length;
            }
            
            let percentIncomplete = 100 - percentCompleted;
            if (percentIncomplete < 0) percentIncomplete = 0;

            percentCompleted = percentCompleted.toFixed(1);
            percentIncomplete = percentIncomplete.toFixed(1);

            var ctx = document.getElementById('buzzPieChart').getContext('2d');
            var pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Completed', 'Incomplete'],
                    datasets: [{
                        data: [percentCompleted, percentIncomplete],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)', // emerald for completed
                            'rgba(239, 68, 68, 0.8)'   // red/rose for incomplete
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            $('#chartLoading').text('Failed to load data');
        }
    }).fail(function() {
        $('#chartLoading').text('Error fetching data');
    });
    // ── Low-completable teachers (raw completable avg < 10) ────────────
    <?php if ($admlevel == 'admin'): ?>
    $.getJSON('<?php echo base_url(); ?>home/get_low_completable_teachers', function(res) {
        if (res && res.status === 'success' && res.data.length > 0) {
            var teachers = res.data;
            var $list = $('#lowTeacherList');
            $list.empty();

            $('#lowTeacherCount').text(teachers.length + ' Teacher' + (teachers.length > 1 ? 's' : ''));
            teachers.forEach(function(t, idx) {
                var subjects = t.courses.map(function(c) {
                    return c.title + ' <span style="color:#ef4444;">(' + c.avg_completable + ')</span>';
                }).join(' &nbsp;|&nbsp; ');

                $list.append(
                    '<div class="low-teacher-row">' +
                        '<div class="low-teacher-rank">' + (idx + 1) + '</div>' +
                        '<div style="flex:1;">' +
                            '<div class="low-teacher-name">' + t.nama + '</div>' +
                            '<div class="low-teacher-subjects">' + subjects + '</div>' +
                        '</div>' +
                        '<div class="low-teacher-badge">avg&nbsp;' + t.avg_completable + '</div>' +
                    '</div>'
                );
            });

            $('#lowTeachersWidget').fadeIn(300);
        }
    }).fail(function() {
        // silently fail — widget stays hidden
    });

    // ── Low-completion-% teachers (completion % avg < 10%) ─────────────
    $.getJSON('<?php echo base_url(); ?>home/get_low_completion_pct_teachers', function(res) {
        if (res && res.status === 'success' && res.data.length > 0) {
            var teachers = res.data;
            var $list = $('#lowPctTeacherList');
            $list.empty();

            $('#lowPctTeacherCount').text(teachers.length + ' Teacher' + (teachers.length > 1 ? 's' : ''));
            teachers.forEach(function(t, idx) {
                var subjects = t.courses.map(function(c) {
                    return c.title + ' <span style="color:#f97316;">(' + c.avg_pct + '%)</span>';
                }).join(' &nbsp;|&nbsp; ');

                $list.append(
                    '<div class="low-teacher-row">' +
                        '<div class="low-teacher-rank">' + (idx + 1) + '</div>' +
                        '<div style="flex:1;">' +
                            '<div class="low-teacher-name">' + t.nama + '</div>' +
                            '<div class="low-teacher-subjects">' + subjects + '</div>' +
                        '</div>' +
                        '<div class="low-teacher-badge">avg&nbsp;' + t.avg_pct + '%</div>' +
                    '</div>'
                );
            });

            $('#lowPctTeachersWidget').fadeIn(300);
        }
    }).fail(function() {
        // silently fail — widget stays hidden
    });
    <?php endif; ?>

    // ── KooBits Dashboard Data ───────────────────────────────────────
    // We call the sync endpoint directly so it triggers a refresh if needed
    $.getJSON('<?php echo base_url(); ?>scratch/get_koobits_data', function(response) {
        if (response && response.status === 'success' && response.data && response.data.length > 0) {
            $('#koobitsLoading').hide();
            
            const rawData = response.data;
            
            // 1. Calculate Summary for Pie Chart
            let summary = { Mastered: 0, Passed: 0, NeedImprove: 0, Incomplete: 0 };
            let levels = {};

            rawData.forEach(function(s) {
                // Summary
                summary.Mastered += (parseInt(s.CompetencyDetails.Mastered) || 0);
                summary.Passed += (parseInt(s.CompetencyDetails.Passed) || 0);
                summary.NeedImprove += (parseInt(s.CompetencyDetails.NeedImprove) || 0);
                summary.Incomplete += (parseInt(s.CompetencyDetails.Incomplete) || 0);

                // Grouping for rankings
                let lvl = s.Level;
                if (!levels[lvl]) levels[lvl] = [];
                
                let mastered = (parseInt(s.CompetencyDetails.Mastered) || 0);
                let passed = (parseInt(s.CompetencyDetails.Passed) || 0);
                let score = (mastered * 1000) + passed;
                
                levels[lvl].push({
                    Fullname: s.Fullname,
                    Grade: s.Grade,
                    Mastered: mastered,
                    Passed: passed,
                    Score: score
                });
            });

            // Render Pie Chart
            const ctx = document.getElementById('koobitsPieChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Mastered', 'Passed', 'Need Improve'],
                    datasets: [{
                        data: [summary.Mastered, summary.Passed, summary.NeedImprove],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',  // green-500
                            'rgba(59, 130, 246, 0.8)', // blue-500
                            'rgba(245, 158, 11, 0.8)', // amber-500
                            'rgba(148, 163, 184, 0.8)' // slate-400
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // 2. Process Rankings
            $('#topStudentsLoading').hide();
            const $container = $('#topStudentsContainer');
            $container.empty();

            // Sort levels 1-6
            for (let i = 1; i <= 6; i++) {
                if (levels[i] && levels[i].length > 0) {
                    // Sort by score desc
                    levels[i].sort((a, b) => b.Score - a.Score);
                    const top5 = levels[i].slice(0, 5);

                    let studentRows = '';
                    top5.forEach(function(s, idx) {
                        const medalColor = idx === 0 ? '#f59e0b' : (idx === 1 ? '#94a3b8' : (idx === 2 ? '#b45309' : '#e2e8f0'));
                        studentRows += `
                            <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8fafc;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: ${medalColor}; color: ${idx < 3 ? '#fff' : '#64748b'}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; margin-right: 12px;">
                                    ${idx + 1}
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">${s.Fullname}</div>
                                    <div style="font-size: 11px; color: #64748b;">${s.Grade}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 13px; font-weight: 700; color: #059669;">${s.Mastered} <span style="font-size: 10px; font-weight: 500; color: #64748b;">Mastered</span></div>
                                    <div style="font-size: 10px; color: #94a3b8;">${s.Passed} Passed</div>
                                </div>
                            </div>
                        `;
                    });

                    $container.append(`
                        <div class="col-md-4" style="margin-bottom: 30px;">
                            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px;">
                                <div style="font-size: 14px; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #4f46e5; color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 11px;">Level ${i}</span>
                                    Top Proficiency
                                </div>
                                ${studentRows}
                            </div>
                        </div>
                    `);
                }
            }

            $container.fadeIn(300);
        } else {
            $('#koobitsLoading').text('No KooBits data found');
            $('#topStudentsLoading').html('<p style="color:#64748b;">No ranking data available yet.</p>');
        }
    }).fail(function() {
        $('#koobitsLoading').text('Error fetching KooBits data');
        $('#topStudentsLoading').hide();
    });
});
</script>
