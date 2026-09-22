<?php

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<link rel="icon" type="image/png" href="https://mutiaraharapan.sch.id/wp-content/uploads/2024/05/cropped-cropped-hanya-logo-300x300-1-32x32.png">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>MHIS Portal</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <!-- Bootstrap core CSS     -->
    <link href="<?php echo base_url(); ?>aset/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Animation library for notifications   -->
    <link href="<?php echo base_url(); ?>aset/css/animate.min.css" rel="stylesheet"/>
    <!--  Light Bootstrap Table core CSS    -->
    <link href="<?php echo base_url(); ?>aset/css/light-bootstrap-dashboard-unminify.css" rel="stylesheet"/>
    <!--  CSS for Demo Purpose, don't include it in your project     -->
    <link href="<?php echo base_url(); ?>aset/css/demo.css" rel="stylesheet" />
    <!--     Fonts and icons     
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>
    -->
    <link href="<?php echo base_url(); ?>aset/css/pe-icon-7-stroke.css" rel="stylesheet" />
    
    <!-- PLUGIN -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>aset/plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>aset/plugins/fa/css/font-awesome.min.css">

    <link rel="stylesheet" href="<?php echo base_url(); ?>aset/plugins/swal/sweetalert2.min.css">

    


    <!-- Javascript Files -->
    <!--   Core JS Files   -->
    <script src="<?php echo base_url(); ?>aset/js/jquery-1.10.2.js" type="text/javascript"></script>
    <script src="<?php echo base_url(); ?>aset/js/bootstrap.min.js" type="text/javascript"></script>
    <!--  Checkbox, Radio & Switch Plugins -->
    <script src="<?php echo base_url(); ?>aset/js/bootstrap-checkbox-radio-switch.js"></script>
    <!--  Charts Plugin -->
    <script src="<?php echo base_url(); ?>aset/js/chartist.min.js"></script>
    <!--  Notifications Plugin    -->
    <script src="<?php echo base_url(); ?>aset/js/bootstrap-notify.js"></script>
    <!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
    <script src="<?php echo base_url(); ?>aset/js/light-bootstrap-dashboard.js"></script>
    <!-- Light Bootstrap Table DEMO methods, don't include it in your project! -->
    <script src="<?php echo base_url(); ?>aset/js/demo.js"></script>
    <script src="<?php echo base_url(); ?>aset/js/js.cookie.js"></script>
    <!--- PLUGINS -->
    <!-- datatables -->
    <script src="<?php echo base_url(); ?>aset/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>aset/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>aset/plugins/pairselect/pair-select.min.js"></script>
    <script src="<?php echo base_url(); ?>aset/plugins/swal/sweetalert2.min.js"></script>
    
    
    <!-- select search -->
    <link href="<?php echo base_url(); ?>aset/css/select2.min.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>aset/js/select2.min.js"></script>
    
    
    <script type="text/javascript">
        base_url = "<?php echo base_url(); ?>";   
        
        // $(document).on("ready", function() {
        //     $('.js-example-basic-single').select2();
        // });
        
        function noti(tipe, value) {
            $.notify({
                icon: 'pe-7s-info',
                message: '<strong>Informasi</strong><p>'+value+'</p>'
            },{
                type: tipe,
                timer: 1000
            });
            return true;
        } 
        function getFormData($form){
            var unindexed_array = $form.serializeArray();
            var indexed_array = {};
            $.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });
            return indexed_array;
        }
        function pagination(indentifier, url, config) {
            $('#'+indentifier).DataTable({
                "language": {
                    "url": base_url+"<?php echo base_url(); ?>aset/plugins/datatables/Indonesian.json"
                },
                "ordering": false,
                "columnDefs": config,
                "bProcessing": true,
                "serverSide": true,
                "bDestroy" : true,
                "ajax":{
                    url : url, // json datasource
                    type: "post",  // type of method  , by default would be get
                    error: function(){  // error handling code
                        $("#"+indentifier).css("display","none");
                    }
                }
            }); 
        }
        function getFormData($form){
            var unindexed_array = $form.serializeArray();
            var indexed_array = {};
            $.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });
            return indexed_array;
        }
    </script>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --sidebar-bg: #020617; /* Slate-950 - Ultra dark rich blue/black */
            --sidebar-item-hover: rgba(255, 255, 255, 0.03);
            --sidebar-item-active: #4f46e5; /* Indigo-600 */
            --sidebar-text: #94a3b8; /* Slate-400 */
            --sidebar-text-active: #ffffff;
            --sidebar-accent: #6366f1; /* Indigo-500 */
            --main-bg: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--main-bg) !important;
        }

        .sidebar {
            background: var(--sidebar-bg) !important;
            box-shadow: 1px 0 0 0 rgba(255, 255, 255, 0.05) !important;
            border: none !important;
            z-index: 1050 !important;
        }

        /* Override legacy gradients and backgrounds */
        .sidebar:after,
        .sidebar:before,
        .sidebar-background,
        .sidebar[data-color]:after,
        .sidebar[data-color]:before {
            background: none !important;
            display: none !important;
        }

        .sidebar .sidebar-wrapper {
            background: var(--sidebar-bg) !important;
            display: flex;
            flex-direction: column;
            width: 100% !important;
            position: relative;
            z-index: 2;
        }

        .sidebar .logo {
            padding: 32px 24px !important;
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            background: rgba(255,255,255,0.02);
            text-align: center;
        }

        .sidebar .logo .simple-text {
            color: #fff !important;
            font-weight: 800 !important;
            text-transform: none !important;
            font-size: 18px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .sidebar .logo .simple-text i {
            font-size: 24px;
            color: var(--sidebar-accent);
        }

        .sidebar .nav {
            margin-top: 15px !important;
            float: none !important;
            padding-bottom: 30px;
        }

        .sidebar .nav li {
            position: relative;
            margin: 2px 16px !important;
        }

        .sidebar .nav li a {
            margin: 0 !important;
            padding: 12px 16px !important;
            border-radius: 12px !important;
            color: var(--sidebar-text) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: flex !important;
            align-items: center !important;
            opacity: 1 !important;
            background: transparent !important;
            border: 1px solid transparent;
        }

        .sidebar .nav li:not(.active) a:hover {
            background: var(--sidebar-item-hover) !important;
            color: #fff !important;
            transform: translateX(4px);
        }

        .sidebar .nav li.active > a {
            background: var(--sidebar-item-active) !important;
            color: var(--sidebar-text-active) !important;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .sidebar .nav li a i {
            font-size: 20px !important;
            margin-right: 14px !important;
            width: 24px;
            text-align: center;
            color: inherit !important;
            opacity: 0.7;
            transition: all 0.2s ease;
        }

        .sidebar .nav li.active a i {
            opacity: 1;
            transform: scale(1.1);
        }

        .sidebar .nav li a p {
            font-weight: 600 !important;
            margin: 0 !important;
            text-transform: none !important;
            font-size: 14px !important;
            letter-spacing: 0.2px;
        }

        /* Divider */
        .sidebar .nav .devider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 15px 20px !important;
            list-style: none;
        }

        /* Main Panel Refinement */
        .main-panel {
            background: var(--main-bg) !important;
            border: none !important;
            transition: all 0.33s cubic-bezier(0.685, 0.0473, 0.346, 1);
        }

        .navbar {
            border: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #fff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }

        /* Navbar Refinement */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            padding: 10px 0 !important;
        }

        .navbar-brand {
            font-weight: 700 !important;
            color: var(--text-main) !important;
        }

        /* Card Modernization */
        .card {
            border-radius: 16px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
            background: #fff !important;
            overflow: hidden;
        }

        .card .header {
            padding: 20px 25px !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .card .title {
            font-weight: 700 !important;
            color: var(--text-main) !important;
            font-size: 18px !important;
        }

        .card .content {
            padding: 25px !important;
        }

        .main-panel {
            background: var(--main-bg) !important;
        }

        #datatabel { width: 100% !important; border-radius: 12px; overflow: hidden; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
    <!--
        Tip 1: you can change the color of the sidebar using: data-color="blue | azure | green | orange | red | purple"
        Tip 2: you can also add an image using data-image tag
    -->
    	<div class="sidebar-wrapper" >
            <div class="logo">
                <a href="<?php echo base_url(); ?>" class="simple-text">
                    <i class="pe-7s-notebook"></i> MHIS Portal
                </a>
            </div>
            <ul class="nav" style="background: none;">
                <?php 
                $prefix = $this->config->item('session_name_prefix');
                
                $walikelas = $this->session->userdata($prefix."walikelas");
                if (isset($walikelas)){
                    echo generate_menu($admlevel,$walikelas['is_wali']);  
                }
                
                ?>
            </ul>
            
            
    	</div>
    </div>
    <div class="main-panel"  >
        <nav class="navbar navbar-default navbar-fixed " >
            <div class="container-fluid">
                <div class="navbar-header ">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigation-example-2">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" >
                    <ul class="nav navbar-nav navbar-right " >
                        <?php 
                        
                        if ($this->session->userdata($prefix."valid") == true) {
                        ?>
                        <li><a href="#">Akun Login : <?php echo $this->session->userdata($prefix."user"); ?> </a></li>
                        <li>
                            <a href="<?php echo base_url(); ?>login/logout" onclick="return hilangkan_gambar();">
                                <i class="fa fa-sign-out"></i> Log out
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="content">
            <div class="container-fluid">
                <?php $this->load->view($p); ?>
            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <p class="copyright pull-left">
                    <b><?php echo $this->config->item('nama_sekolah'); ?></b>
                </p>
                <p class="copyright pull-right">
                    Waktu proses {elapsed_time} detik. &copy; 2019-<?=date('Y')?> By Salludin Gozali
                </p>
            </div>
        </footer>
        
    </div>
</div>
</body>
</html>
