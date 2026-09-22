<div class="row">
    
    <div class="col-md-12">
        <div class="alert alert-warning" style="color: #000">
            <!--<b>Petunjuk : </b><br>-->
            <!--Menu ini digunakan untuk menginput nilai pada setiap masing-masing mata pelajaran diampu. Silakan klik menu <b><i>Nilai Pengetahuan</i></b> untuk menginput nilai pengetahuan, dan <b><i>Nilai Keterampilan</i></b> untuk menginput nilai keterampilan.-->
        </div>
    </div>
    <?php 
    if (!empty($list_mapelkelas)) {
        foreach ($list_mapelkelas as $mk) { 
    ?>
    <div class="col-md-4">
        <div class="card">
            <div class="header">
            <?php
                if ($mk['nmmapel'] == 'ICB' || $mk['nmmapel'] == 'PSS' || $mk['nmmapel'] == 'LA'){
                    ?>
                    <h5 class="title"><?php echo $mk['namamapel']." - ".$mk['nmkelas']; ?> </h5>
                <?php }else{ ?>
                    <h4 class="title"><?php echo $mk['nmmapel']." - ".$mk['nmkelas']; ?> </h4>
                 <?php } ?>
                <!--
                <a href="#" class="btn btn-success btn-xs pull-right"><i class="fa fa-check"></i> </a>
                -->
                
            </div>
            <div class="content">
                <ul class="list-group">
                    <?php
                    if ($mk['nmmapel'] == 'ICB'){
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_icb/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai <?php echo $mk['namamapel'] ?></a></li>
                    <?php }else if ($mk['nmmapel'] == 'PSS'){
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_pss/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai <?php echo $mk['namamapel'] ?></a></li>
                    <?php }else if ($mk['nmmapel'] == 'LA'){
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_la/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai <?php echo $mk['namamapel'] ?></a></li>
                    <?php }else if ($mk['kelompok'] == 'NNA' || $mk['kelompok'] == 'BI' || $mk['kelompok'] == 'KOG' || $mk['kelompok'] == 'FIMO' || $mk['kelompok'] == 'SEK'){
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_preschool/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai <?php echo $mk['namamapel'] ?></a></li>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_catatan_guru/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Teacher's Note</a></li>
                    <?php }else if ($mk['tingkat'] == '1' || $mk['tingkat'] == '4' || $mk['tingkat'] == '2' || $mk['tingkat'] == '5'){
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_kelompok/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Nilai <?php echo $mk['namamapel'] ?></a> <!--<a href="#" class="badge pull-right"><i class="fa fa-print"></i> --></li>
                    <?php }else{ ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_kelompok/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Knowledge</a> <!--<a href="#" class="badge pull-right"><i class="fa fa-print"></i> --></li>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_keterampilan/index/".$mk['id']; ?>"><i class="fa fa-chevron-right"></i>  Skills</a></li>
                    <?php
                    }
                    ?>
                    <!-- <li class="list-group-item"><a href="<?php echo base_url()."view_mapel/cetak_absensi/".$mk['id']; ?>" target="_blank"><i class="fa fa-chevron-right"></i>  Cetak Presensi</a></li> 
                    
                    <?php 
                    if ($mk['is_sikap'] == "1") {
                    ?>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_sikap_sp/index/".$mk['id']; ?>">Nilai Sikap Spritual</a></li>
                    <li class="list-group-item"><a href="<?php echo base_url()."n_sikap_so/index/".$mk['id']; ?>">Nilai Sikap Sosial</a></li>
                    <?php 
                    }
                    ?> -->
                </ul>
            </div>
        </div>
    </div>
    <?php 
        }
    } else {
        echo '<div class="alert alert-info">Belum ada Projek yang diampu..</div>';
    }
    ?>
</div>