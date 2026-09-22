<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MHIS E-Report</title>
    <link rel="icon" type="image/png" href="<?=base_url('aset/img/logo-tut.png')?>">
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Plugins -->
    <link rel="stylesheet" href="<?=base_url('aset/plugins/fa/css/font-awesome.min.css')?>">
    <link rel="stylesheet" href="<?=base_url('aset/plugins/swal/sweetalert2.min.css')?>">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.3);
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            background-attachment: fixed;
            overflow: hidden;
        }

        /* Animated Mesh Gradient Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(236, 72, 153, 0.15) 0, transparent 50%),
                radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.15) 0, transparent 50%);
            z-index: -1;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            perspective: 1000px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            width: 280px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            margin-left: 4px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid transparent;
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Custom scrollbar for some cases */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            .logo-section img {
                width: 220px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="<?=base_url('aset/img/logo_MH.png')?>" alt="MHIS Logo">
            </div>
            
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Please log in using the credentials provided by the school administrator.</p>
            </div>

            <form id="f_login" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" required class="form-control" placeholder="Enter your username" autofocus>
                        <i class="fa fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required class="form-control" placeholder="Enter your password">
                        <i class="fa fa-lock"></i>
                    </div>
                </div>

                <button type="submit" id="tbLogin" class="btn-login">
                    <i class="fa fa-sign-in"></i> Sign In
                </button>
            </form>
        </div>
        
        <div class="footer-text">
            &copy; <?=date('Y')?> MHIS E-Report. All rights reserved.
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?=base_url('aset/js/jquery-1.10.2.js')?>"></script>
    <script src="<?=base_url('aset/plugins/swal/sweetalert2.min.js')?>"></script>
    
    <script>
        const base_url = "<?=base_url()?>";

        $(document).ready(function() {
            $("#f_login").on("submit", function(e) {
                e.preventDefault();
                
                const data = $(this).serialize();
                const $btn = $("#tbLogin");
                const $inputs = $("#username, #password");

                $.ajax({
                    type: "POST",
                    url: base_url + "login/do_login",
                    data: data,
                    dataType: 'json',
                    beforeSend: function() {
                        $btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                        $inputs.attr('disabled', true);
                    },
                    success: function(r) {
                        if (r.status == "gagal") {
                            $btn.attr('disabled', false).html('<i class="fa fa-sign-in"></i> Sign In');
                            $inputs.attr('disabled', false);
                            
                            swal({
                                type: 'error',
                                title: 'Login Failed',
                                text: r.data,
                                confirmButtonColor: '#6366f1'
                            });
                            
                            $("#password").val('').focus();
                        } else {
                            swal({
                                type: 'success',
                                title: 'Success',
                                text: 'Login successful! Redirecting...',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.assign(base_url + "home");
                            });
                        }
                    },
                    error: function() {
                        $btn.attr('disabled', false).html('<i class="fa fa-sign-in"></i> Sign In');
                        $inputs.attr('disabled', false);
                        
                        swal({
                            type: 'error',
                            title: 'Error',
                            text: 'Something went wrong. Please try again later.',
                            confirmButtonColor: '#6366f1'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
