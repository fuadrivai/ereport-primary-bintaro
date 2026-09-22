<style type="text/css">
      body{
        background-size: cover;
        background: url(aset/img/);
        background-size: 400px;
      }
     /* .warna-bg{
          background: linear-gradient(to bottom, rgba(147,104,233, .92), rgba(148,59,234, .92));
        padding: 0;
        margin: 0;*/
      }
  </style>  

<body>
 <div class="warna-bg">
  
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6" style="margin-top: 10%;">
        <div class="card">
            <div class="header">
            <!-- <h4 class="title">Login</h4> -->
                <td width="10%">
                <img src="aset/img/logo_MH.png" style="margin-top: 5px; width: 350px;">
			    </td> 
                
            </div>
            <div class="content">
                <div class="alert alert-info">
                <b>Welcome to the Online Report Application</b>
                    <br>Please log in with the username and password provided by the School Admin. If you haven't received them yet, please contact the School Admin.
                </div>
                <form id="<?php echo $nama_form; ?>" method="post">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" autocomplete="off" name="username" id="username" required class="form-control" autofocus="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" id="password" required class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="tbLogin" class="btn btn-info btn-fill pull-left"><i class="fa fa-check-circle"></i> Masuk</button>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>
</div> 
</body>
<script type="text/javascript">
    $(document).on("ready", function() {
        
        $("#<?php echo $nama_form; ?>").on("submit", function() {
            var data    = $(this).serialize();
    
            $.ajax({
                type: "POST",
                data: data,
                url: base_url+"login/do_login",
                beforeSend: function() {
                    $("#username, #password, #tbLogin").attr('disabled', true);
                    $("#tbLogin").html('<i class="fa fa-spinner fa-spin"></i> Prosess..');
                },
                success: function(r) {
                    $("#username, #password, #tbLogin").attr('disabled', false);
                    if (r.status == "gagal") {
                        alert(r.data);

                    	$("#tbLogin").html('<i class="fa fa-check-circle"></i> Login');
                        $("#password").val('');
                        $("#password").focus();
                    } else {
                        //alert(r.data);
                        window.location.assign(base_url+"home");
                    }
                }
            });
            return false;
        });
    });
</script>