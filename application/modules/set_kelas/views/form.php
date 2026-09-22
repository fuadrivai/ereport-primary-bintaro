<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">Set Kelas</h4>

            </div>
            <div class="content">

                <form method="post" id="<?php echo $nama_form; ?>" name="<?php echo $nama_form; ?>" action="<?php echo base_url().$url; ?>/simpan">
                    <input type="hidden" name="ta" value="<?=$ta?>">

                <div class="row">
                    <div class="col-md-4">
                        <label>Pilih Kelas</label>
                        <select name="kelas" id="kelas" class="form-control" required="true">
                            <option value=""></option>
                            <?php 
                            if (!empty($kelas)) {
                                foreach ($kelas as $k) {
                                    echo '<option value="'.$k['id'].'">'.$k['nama'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="">
                    <div class="col-md-12">
                        <label>Pilih Siswa (*) Untuk memilih satu persatu, gunakan Ctrl+Klik, Untuk memilih Semua gunakan Ctrl+A</label>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <select name="siswa_semua" size="20" multiple id="siswa_semua" class="form-control">
                            <?php 

                            if (!empty($siswa_asal)) {
                                foreach ($siswa_asal as $sa) {

                                   $t = $this->db->query("SELECT ta from t_kelas_siswa where ta != '".$this->d['ta']."' order by ta desc limit 1  ")->row_array();

                                   $q = $this->db->query("SELECT 
                                        a.ta, b.nama nmkelas, c.id, c.nama nmsiswa
                                        from t_kelas_siswa a 
                                        inner join m_kelas b on a.id_kelas = b.id
                                        inner join m_siswa c on a.id_siswa = c.id
                                        where a.id_siswa = '".$sa['id']."' and a.ta= '".$t['ta']."' order by b.nama asc ")->row_array();

                                    if(($q['tingkat']??0) % 2 == 0){
                                        $bg = 'background:#f98686;color:#000;padding:3px 3px;';
                                    }else{
                                        $bg = 'background:#f6f871;color:#000;padding:3px 3px;';
                                    }
                                
                                    if($q){
                                        echo '<option value="'.$q['id'].'" style="'.$bg.'">' .$q['nmsiswa'].' ---- <b>[ Kelas '.$q['nmkelas'].' ]</b></option>';
                                    }else{

                                        echo '<option value="'.$sa['id'].'" style="'.$bg.'">'.($sa['nama']??"").' ---- <b>[ Kelas '.($sa['kelas']??"").' ]</b></option>';
                                    }
                                    
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <center>
                        <button type="button" class="btn btn-success" id="tambah"><i class="fa fa-chevron-right"></i> </button>
                        <button type="button" class="btn btn-success" id="kurang"><i class="fa fa-chevron-left"></i> </button>
                        </center>
                    </div>
                    <div class="col-md-4">
                        <select name="siswa_pilih[]" size="20" multiple id="siswa_pilih" class="form-control" required="true">
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?php echo base_url().$url; ?>" class="btn btn-default">Kembali</a>
                <div class="clearfix"></div>
            
                </form>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).on("ready", function() {
        $('#siswa_semua').pairMaster();

        $('#tambah').click(function(){
            $('#siswa_semua').addSelected('#siswa_pilih');
        });

        $('#kurang').click(function(){
            $('#siswa_pilih').removeSelected('#siswa_semua'); 
        });
    });

</script>