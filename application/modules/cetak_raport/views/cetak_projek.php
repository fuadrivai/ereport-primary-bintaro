<page backtop="20mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<page_header><br>
		<img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
	</page_header>
	<style type="text/css">
		body {
			font-family: freeserif;
			font-size: 11pt;
			width: 8.5in
		}

		.table {
			border-collapse: collapse;
			border: solid 1px #999;
			width: 100%
		}

		.table tr td,
		.table tr th {
			font-family: freeserif;
			border: solid 1px #000;
			padding: 3px;
		}

		.table tr th {
			font-weight: bold;
			text-align: center
		}

		.rgt {
			text-align: right;
		}

		.ctr {
			text-align: center;
		}

		.tbl {
			font-weight: bold
		}

		table tr td {
			vertical-align: top
		}

		.font_kecil {
			font-size: 12px
		}
	</style>
	<table>
		<tr>
			<td colspan="6">
				<p>
				<h3 style="text-align: center;">HASIL PENCAPAIAN KOMPETENSI PESERTA DIDIK</h3>
				</p>
			</td>
		</tr>
		<tr>
			<td style="width:100px">Nama Sekolah</td>
			<td>:</td>
			<td style="font-weight: bold; width:350px">
				<?php echo $this->config->item('nama_sekolah'); ?>
			</td>
			<td>Kelas</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo strtoupper($wali_kelas['nmkelas']); ?>
			</td>
		</tr>
		<tr>
			<td style="width:100px">Alamat Sekolah</td>
			<td>:</td>
			<td style=" width:350px">
				<?php echo $this->config->item('alamat_sekolah'); ?>
			</td>
			<td>Fase</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php if ($det_siswa['tingkat'] == 1 || $det_siswa['tingkat'] == 2){ ?>
				A
				<?php }else if ($det_siswa['tingkat'] == 4 || $det_siswa['tingkat'] == 3){ ?>
				B
				<?php } else { ?>
				C
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td style="width:100px">Nama Siswa</td>
			<td>:</td>
			<td style="font-weight: bold; width:350px">
				<?php echo $det_siswa['nama']; ?>
			</td>
			<td>Tahun Pelajaran</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo $ta; ?>
			</td>
		</tr>
		<tr>
			<td style="width:100px">NIS / NISN</td>
			<td>:</td>
			<td style="font-weight: bold; width:350px">
				<?php echo $det_siswa['nis'] . " / " . $det_siswa['nisn']; ?>
			</td>
			<td colspan="3"></td>
		</tr>
		<tr>
			<td colspan="6"><br><br></td>
		</tr>
	</table>
	<table>
		<?php echo $projek; ?>
	</table>
	<br>
	<table>
	    <tr>
	        <td>Rubrik penilaian:</td>
	    </tr>
	</table>
	
	<table class="table">
	    <tr style="text-align:center;font-weight: bold;">
	        <td style="width:150px">Mulai Berkembang (MB)</td>
	        <td style="width:150px">Sedang Berkembang (SB)</td>
	        <td style="width:150px">Berkembang Sesuai Harapan (BSH)</td>
	        <td style="width:150px">Sangat Berkembang (SB)</td>
	    </tr>
	    <tr>
	        <td style="width:150px">Bila anak melakukannya harus dengan bimbingan atau dicontohkan oleh guru.</td>
	        <td style="width:150px">Bila anak melakukannya masih harus diingatkan atau dibantu oleh guru.</td>
	        <td style="width:150px">Bila anak sudah dapat melakukannya secara mandiri, dapat konsisten tanpa harus diingatkan atau dicontohkan oleh guru.</td>
	        <td style="width:150px">Bila anak sudah dapat melakukannya secara mandiri dan sudah dapat membantu temannya yang belum mencapai kemampuan sesuai dengan indikator yang diharapkan.</td>
	    </tr>
	</table>
	<page backtop="20mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<page_header><br>
		<img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
	</page_header>
	<?php
			if (!empty($nama_projek)) {
				$no = 1;
				foreach ($nama_projek as $n_p) {

					?>
					<table class="table">
                			<thead>
                				<tr>
                					<th style="padding: 15px 10px;width:200px" colspan="2"><?php echo $n_p['nama']; ?></th>
                					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
                					<th style="padding: 15px 10px;width:55px" colspan="2">Sedang Berkembang</th>
                					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
                					<th style="padding: 15px 10px;width:55px" colspan="2">Sangat Berkembang</th>
                							
                				</tr>
                			</thead>
                			<tbody>
                			    <?php
                			    $q_mapel = $this->db->query("SELECT a.id as id,kkm,a.nama as nama, c.nama as namaguru FROM m_kelompok a
                                        INNER JOIN t_guru_kelompok b ON a.id = b.id_kelompok
                                        INNER JOIN m_guru c ON b.id_guru = c.id
                                        WHERE kelompok = '" . $n_p['p_singkat'] . "' AND tambahan_sub = 'NO' AND b.id_kelas= '" . $det_siswa['idkelas'] . "' ORDER BY kkm ASC")->result_array();
                                foreach ($q_mapel as $i => $m) {
                                $kkmx = $m['kkm'];
                                $idx = $m['id'];
                			    ?>
                			    <tr>
                                <td colspan="10"><b><?php echo $m['nama']; ?></b></td>
                                </tr>
                                <?php
                                foreach ($nilai_projek[$idx]['h'] as $aspect) {
                                $aspect_array = explode("//", $aspect);
                                ?>
                                <tr>
                                <td colspan="2" style="padding: 15px 10px;width:200px;text-align: justify;"><?php echo $aspect_array[1]; ?></td>
                                <?php
                                if ($aspect_array[0] == 1) {
                                ?>
                                <td colspan="2" style="font-family:freeserif;text-align:center;">&#10004;</td>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                </tr>
                                <?php }elseif ($aspect_array[0] == 2) { ?>
                                <td colspan="2"></td>
                                <td colspan="2" style="font-family:freeserif;text-align:center;">&#10004;</td>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                </tr>
                                <?php }elseif ($aspect_array[0] == 3) { ?>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                <td colspan="2" style="font-family:freeserif;text-align:center;">&#10004;</td>
                                <td colspan="2"></td>
                                </tr>
                                <?php }else{ ?>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                <td colspan="2"></td>
                                <td colspan="2" style="font-family:freeserif;text-align:center;">&#10004;</td>
                                </tr>
                                <? }
                                } ?>
                                <? } ?>
                				<tr style="text-align:justify">
                				    <td colspan="10" style="text-align: justify;width:400px;text-align:justify;"><b>Catatan Proses:</b><br><br>
                				    <?php
                			            $q_mapel = $this->db->query("SELECT catatan_final FROM t_catatan_projek
                                        WHERE id_kelompok = '" . $n_p['p_singkat'] . "' AND ta = '" . $tasm . "' AND id_siswa= '" . $det_siswa['idsiswa'] . "'")->result_array();
                                        foreach ($q_mapel as $i) { ?>
                                            <span style="text-align:justify"><?= str_replace("\n", "", $i['catatan_final']);?></span>
                                    <?php } ?>
                				    </td>
                				    
                				</tr>
                			</tbody>
                	</table>
                	<br><br>
                	<?php
					$no++;
				}
			}
			?>
	
	<page backtop="20mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<page_header><br>
		<img src="https://report.mhis.link/images/logo_MH_primary.png" width="200" style="margin-left:20px">
	</page_header>
	<?php
	if ($semester == 2) {
		?>
		<table>
			<tr>
				<td colspan="6">
					<?php
					$naik_kelas = $det_siswa['tingkat'] + 1;
					$kelas_now = $det_siswa['tingkat'];

					if ($kelas_now != 9) {

						if ($catatan['naik'] == 'N') {
							$naik = 'text-decoration: line-through';
							$tidak_naik = '';
						} else {
							$naik = '';
							$tidak_naik = 'text-decoration: line-through';
						}

						?>


						<div style="border: solid 1px; padding: 10px; margin-top: 40px">
							<b>Keputusan : </b>
							<p>Berdasarkan pencapaian kompetensi pada semester ke-1 dan ke-2, peserta didik ditetapkan *) :<br>

							<div style="display: block">
								<div style="diplay: inline; float: left; width: 200px;  <?= $naik; ?> ">naik ke kelas </div>
								<div style="diplay: inline; float: left; font-weight: bold; <?= $naik; ?>"><?php echo $naik_kelas . " (" . terbilang($naik_kelas) . ")"; ?></div>
							</div><br>
							<div style="display: block">
								<div style="diplay: inline; float: left; width: 200px;<?= $tidak_naik; ?>">tinggal di kelas
								</div>
								<div style="diplay: inline; float: left; font-weight: bold; <?= $tidak_naik; ?>"><?php echo $kelas_now . " (" . terbilang($kelas_now) . ")"; ?></div>
							</div>
							<br><br>
							*) Coret yang tidak perlu
						</div>

					<?php } else { ?>
						<div style="border: solid 1px; padding: 10px; margin-top: 40px">
							<b>Keputusan : </b>
							<p>Berdasarkan pencapaian kompetensi pada kelas 4, 5 dan 6, maka, peserta didik dinyatakan : *)
								:<br>
							<div style="display: block; font-weight: bold">
								LULUS / <strike>TIDAK LULUS</strike>
							</div><br><br>
							*) Coret yang tidak perlu
						</div>

					<?php } ?>
				</td>
			</tr>
		</table>
	<?php } ?>
	<br><br><br>
	<table>
		<tr>
			<td style="width:200px;text-align: center;">
				Mengetahui<br>
				Orang Tua/Wali,
				<br><br><br><br><br>
				<b>
					______________________
				</b><br>
				<br>
			</td>
			<td style="width:200px;text-align: center;">
				
			</td>
			<td></td>
			<td style="text-align: center;">
				<?php
				if ($wali_kelas['tingkat'] != 9) {
					?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
				<?php } else { ?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
				<?php } ?>
				Guru Kelas,
				<br><br><br><br><br>
				<u><b>
						<?php echo $wali_kelas['nmguru']; ?>
					</b></u><br>
			</td>
		</tr>
		<tr>
			<td style="width:200px;text-align: center;">
			</td>
			<td style="width:200px;text-align: center;">
				Mengetahui,<br>
				Kepala Sekolah
				<br><br><br><br><br>
				<u><b><?php echo $det_raport['nama_kepsek']; ?></b></u>
			</td>
			<td></td>
			<td style="text-align: center;">
			</td>
		</tr>
	</table>



</page>