<style>
    .profile-card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 40px;
        box-shadow: 0 8px 15px rgba(99, 102, 241, 0.3);
    }
    .profile-info h3 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 24px;
    }
    .profile-info p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 16px;
    }
    .profile-info .badge {
        margin-top: 10px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
    }
</style>

<div class="card">
    <div class="header">
        <h4 class="title">Selamat datang di Aplikasi E-Raport Online</h4>
    </div>
    <div class="content">
        <div class="profile-card">
            <div class="profile-avatar">
                <i class="fa fa-user"></i>
            </div>
            <div class="profile-info">
                <h3><?php echo $this->session->userdata('app_rapot_nama'); ?></h3>
                <p>NIS: <strong><?php echo $this->session->userdata('app_rapot_nip'); ?></strong></p>
                <div class="badge">Student</div>
            </div>
        </div>
    </div>
</div>
