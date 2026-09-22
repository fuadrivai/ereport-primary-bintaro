<style type="text/css">
    .ctr {text-align: center}
    .nso {}
</style>
<div class="row">
    <div class="col-md-9">
        <div class="card">
            <div class="header">
                <h4 class="title">Status Naik Kelas dan Catatan Wali</h4>
            </div>
            <div class="content">  

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nama</th>
                            <th width="10%">Naik Kelas</th>
                            <th width="25%">Catatan Mid</th>
                            <th width="25%">Catatan Final</th>
                        </tr>
                    </thead>

                    <tbody>
                        <form method="post" id="<?php echo $url; ?>">
                        <input type="hidden" name="mode_form" value="<?php echo $mode_form; ?>">

                        <?php 

                        $no = 1;
                        if (!empty($siswa_kelas)) {
                            foreach ($siswa_kelas as $sk) {
                                echo '<input type="hidden" name="id_siswa_'.$no.'" value="'.$sk['id_siswa'].'">';
                        ?>
                            <tr>
                                <td><?php echo $no; ?></td>
                                <td><?php echo $sk['nama']; ?></td>
                                <td>
                                    <?php 
                                    echo form_dropdown("naik_".$no,$p_naik,$sk['naik'],'class="form-control input-sm" required id="naik_'.$no.'"');
                                    ?>
                                </td>
                                <td>
                                    <textarea rows="3" class="form-control input-sm" id="catatan_mid_<?php echo $no; ?>" name="catatan_mid_<?php echo $no; ?>"><?php echo $sk['catatan_wali']; ?></textarea>
                                   
                                </td>
                                <td>
                                    <textarea rows="3" class="form-control input-sm" id="catatan_final_<?php echo $no; ?>" name="catatan_final_<?php echo $no; ?>"><?php echo $sk['catatan_wali_final']; ?></textarea>
                                   
                                </td>
                            </tr>
                        <?php 
                                $no++;
                            }
                        } else {
                            echo '<tr><td colspan="4">Belum ada data siswa</td></tr>';
                        }
                        ?>

                        
                        
                    </tbody>
                    
                </table>

                <input type="hidden" name="jumlah" value="<?php echo $no; ?>">
                <button type="submit" class="btn btn-success" id="tbsimpan"><i class="fa fa-check"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div> 
    
    <div class="col-md-3">
        <div class="card">
            <div class="header">
                <h5 class="title">Deskripsi untuk Catatan guru</h5>
            </div>
            <div class="content">  
                <table class="table table-bordered">
                    <tr>
                        <th>No.</th>
                        <th>Catatan</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Karakteristik siswa dalam menggali memahami pengetahuan masih memerlukan peningkatan konsentrasi dan motivasi sehingga masih diperlukan bimbingan dari orang tua untuk meningkakan motivasi,</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Potensi untuk meningkatkan keterampilan sangat potensial mengingat peserta didik ini, mudah mencerna dan mendapatkan pengetahuan,</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Sikap sosial dan spiritual telah mengalami perkembangan ke arah positif secara signifikan, interaksi, dan keteladanan untuk mempengaruhi teman bersikap positif perlu ditumbuhkan</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Motivasi belajar siswa telah tumbuh dengan pesat, bimbingan dan fasilitasi kelengkapan belajar yang disediakan oleh orang tua akan dapat meningkatkan kembali presasi siswa,</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Perlu peningkatan dalam berkomunikasi dan unjuk kemampuan melalui motivasi menjadi petugas, presentasi dan aktivitas unjuk kerja,</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Kegiatan eksploratif dan pengembangan potensi melalui ekstrakurikuler perlu ditingkatkan</td>
                    </tr>

                </table>
            </div>
        </div>
    </div>

</div>


<script type="text/javascript">
    $(document).on("ready", function() {
        
        $("#<?php echo $url; ?>").on("submit", function() {
                
            var data    = $(this).serialize();


            $.ajax({
                type: "POST",
                data: data,
                url: base_url+"<?php echo $url; ?>/simpan",
                beforeSend: function(){
                    $("#tbsimpan").attr("disabled", true);
                },
                success: function(r) {
                    $("#tbsimpan").attr("disabled", false);
                    if (r.status == "ok") {
                        noti("success", r.data);
                    } else {
                        noti("danger", r.data);
                    }
                }
            });

            return false;
        });
    });

</script>