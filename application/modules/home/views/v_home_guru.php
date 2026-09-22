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
    .bg-emerald { background: linear-gradient(135deg, #10b981, #34d399); }
    .bg-sky { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
    .bg-amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); }

</style>

<?php 
$wali_kelas = $this->session->userdata('app_rapot_walikelas');
$is_wali = $wali_kelas['is_wali'];
?>

<div class="welcome-section">
    <div class="welcome-content">
        <h3>Welcome to the Online E-Report MHIS</h3>
        <p>You are logged in as <strong><?php echo $this->session->userdata('app_rapot_nama'); ?></strong></p>
        <?php if($is_wali): ?>
            <p><span class="badge" style="background: rgba(255,255,255,0.2); padding: 8px 15px;">Homeroom Class: <?=$wali_kelas['nama_walikelas']?></span></p>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-indigo">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <div class="stat-details">
                <h4>Active Academic Year</h4>
                <div class="number"><?=$tasm?></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-sky">
                <i class="fa fa-clipboard"></i>
            </div>
            <div class="stat-details">
                <h4>Total Subjects</h4>
                <div class="number"><?=$mapel_diampuh?> Subjects</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-emerald">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-details">
                <h4>Total Students</h4>
                <div class="number">LK: <?=$stat_kelas['jmlk_l']?> | PR: <?=$stat_kelas['jmlk_p']?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-amber">
                <i class="fa fa-calendar"></i>
            </div>
            <div class="stat-details">
                <h4>Report Distribution</h4>
                <div class="number"><?=$bagi_raport?></div>
            </div>
        </div>
    </div>
</div>
