<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
	<page_header><br>
		<img src="C:\Users\sallu\Downloads\logo mh primary black.png" width="200" style="margin-left:20px">
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
				<h3 style="text-align: center;">RAPOR PESERTA DIDIK DAN PROFIL PESERTA DIDIK</h3>
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
			<td>Semester</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo $semester; ?>
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
		<tr>
			<td colspan="9"><b>A. SIKAP</b></td>
		</tr>
		<tr>
			<td colspan="9"><b>1. Sikap Spiritual (KI 1)</b></td>
		</tr>
		<tr>
			<td colspan="9">
				<table class="table">
					<thead>
						<tr>
							<th colspan="3">Aspek yang Dinilai</th>
							<th colspan="3">Capaian</th>
							<th colspan="3">Deskripsi</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!empty($nilai_kl1)) {

							foreach ($nilai_kl1 as $p) {
								?>
								<tr>
									<td colspan="3" style="width:150px; padding: 20px 10px;">Menerima dan menjalankan ajaran
										agama yang dianutnya.</td>
									<td colspan="3" style="text-align:center; padding: 20px 10px;">
										<?php echo $p['capaian_final']; ?>
									</td>
									<td colspan="3" style="width:435px; padding: 20px 10px;">
										<?php echo $p['catatan_final']; ?>
									</td>
								</tr>
								<?php

							}
						} else {
							echo '<tr><td colspan="3">-</td></tr>';
						}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<br>
		<tr>
			<td colspan="9"><b>2. Sikap Sosial (KI 2)</b></td>
		</tr>
		<tr>
			<td colspan="9">
				<table class="table">
					<thead>
						<tr>
							<th colspan="3">Aspek yang Dinilai</th>
							<th colspan="3">Capaian</th>
							<th colspan="3">Deskripsi</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!empty($nilai_kl2)) {

							foreach ($nilai_kl2 as $p) {
								?>
								<tr>
									<td colspan="3" style="width:150px; padding: 20px 10px;">Perilaku jujur, disiplin, tanggung
										jawab, santun, peduli, dan percaya
										diri dalam berinteraksi dengan
										keluarga, teman, dan guru.</td>
									<td colspan="3" style="text-align:center; padding: 20px 10px;">
										<?php echo $p['capaian_final']; ?>
									</td>
									<td colspan="3" style="width:435px; padding: 20px 10px;">
										<?php echo $p['catatan_final']; ?>
									</td>
								</tr>
								<?php

							}
						} else {
							echo '<tr><td colspan="3">-</td></tr>';
						}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="6"><br><br></td>
		</tr>
	</table>
	<table>
		<tr>
			<td colspan="9">
				<b>B. PENGETAHUAN (KI 3)</b>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<br>
			</td>
		</tr>
	</table>
	<table class="table">
		<thead>
			<tr>
				<th colspan="2">Mata Pelajaran</th>
				<th>KKM</th>
				<th colspan="2">Angka</th>
				<th colspan="2">Predikat</th>
				<th colspan="2">Deskripsi</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $nilai_utama; ?>
		</tbody>
	</table>
	<br>
	<table>
		<tr>
			<td colspan="9">
				<b>C. KETERAMPILAN (KI 4)</b>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<br>
			</td>
		</tr>
	</table>
	<table class="table">
		<thead>
			<tr>
				<th colspan="2">Mata Pelajaran</th>
				<th>KKM</th>
				<th colspan="2">Angka</th>
				<th colspan="2">Predikat</th>
				<th colspan="2">Deskripsi</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $nilai_keterampilan; ?>
		</tbody>
	</table>
	<br><br>
	<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
	<table class="table">
		<thead>
			<tr>
				<th colspan="2" rowspan="2" style="width:150px">KKM</th>

				<th colspan="8" style="width:510px">Predikat</th>
			</tr>
			<tr>
				<th colspan="2">Kurang (D)</th>
				<th colspan="2">Cukup (C)</th>
				<th colspan="2">Baik (B)</th>
				<th colspan="2">Sangat Baik (SB)</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $kkm; ?>
		</tbody>
	</table>
	<br><br>
	
	<table>
		<tr>
			<td colspan="9">
				<b>D. EKSTRAKURIKULER</b>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<br>
			</td>
		</tr>
	</table>
	<table class="table">
		<thead>
			<tr>
				<th>No</th>
				<th style="width:220px">Nama Kegiatan</th>
				<th style="width:100px">Nilai</th>
				<th style="width:300px">Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (!empty($nilai_ekstra)) {
				$no = 1;
				foreach ($nilai_ekstra as $ne) {

					?>
					<tr>
						<td class="ctr">
							<?php echo $no; ?>
						</td>
						<td>
							<?php echo $ne['nama']; ?>
						</td>
						<td class="ctr">
							<?php echo $ne['nilai']; ?>
						</td>
						<td style="width:300px">
							<?php echo $ne['desk']; ?>
						</td>
					</tr>
					<?php
					$no++;
				}
			} else {
				echo '<tr><td colspan="4">-</td></tr>';
			}
			?>
		</tbody>
	</table>
	<br><br>
	<table>
		<tr>
			<td colspan="9">
				<b>E. PRESTASI</b>
			</td>
		</tr>
		<!--<tr>-->
		<!--	<td colspan="9">-->
		<!--		<br>-->
		<!--	</td>-->
		<!--</tr>-->
	</table>
	<table class="table">
		<thead>
			<tr>
				<th>No</th>
				<th style="width:320px">Jenis Prestasi</th>
				<th style="width:315px">Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (!empty($prestasi)) {
				$no = 1;
				foreach ($prestasi as $p) {
					?>
					<tr>
						<td>
							<?php echo $no; ?>
						</td>
						<td>
							<?php echo $p['jenis']; ?>
						</td>
						<td style="width:315px">
							<?php echo $p['keterangan']; ?>
						</td>
					</tr>
					<?php
					$no++;
				}
			} else {
				echo '<tr><td colspan="3">-</td></tr>';
			}
			?>
		</tbody>
	</table>
	<br><br>
	<table>
		<tr>
			<td colspan="9">
				<b>F. KETIDAKHADIRAN</b>
			</td>
		</tr>
		<tr>
			<td colspan="6">
				<table class="table">
					<tr>
						<td style="width:200px">Sakit</td>
						<td style="width:100px" class="ctr">
							<?php echo $nilai_absensi['s']; ?> hari
						</td>
					</tr>
					<tr>
						<td style="width:200px">Izin</td>
						<td style="width:100px" class="ctr">
							<?php echo $nilai_absensi['i']; ?> hari
						</td>
					</tr>
					<tr>
						<td style="width:200px">Tanpa Keterangan</td>
						<td style="width:100px" class="ctr">
							<?php echo $nilai_absensi['a']; ?> hari
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<br>
			</td>
		</tr>
	</table>
	<br><br>
	<table>
		<tr>
			<td colspan="9">
				<b>G. CATATAN WALI KELAS</b>
			</td>
		</tr>
		<tr>
			<td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:640px;">
				<?php echo $catatan['catatan_wali_final']; ?>
			</td>
		</tr>
	</table>
	<br><br>
	<table>
		<tr>
			<td colspan="9">
				<b>H. TANGGAPAN ORANGTUA/WALI</b>
			</td>
		</tr>
		<tr>
			<td colspan="6" style="border: solid 1px #000; padding: 20px 10px; height: 200px; width:640px;"></td>
		</tr>
	</table>
	<br><br>
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
			<td style="width:400px">
				MUTIARA HARAPAN ISLAMIC SCHOOL<br>
				<?php if ($wali_kelas['tingkat'] != 9) {
					?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport'], "l"); ?><br>
				<?php } else { ?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
				<?php } ?>
				<br><br><br><br>
				<u><b>
						<?php echo $wali_kelas['nmguru']; ?>
					</b></u><br>
				Wali Kelas <br>
			</td>
			<td>

			</td>
			<td></td>
			<td>
				<?php
				if ($wali_kelas['tingkat'] != 9) {
					?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport'], "l"); ?><br>
				<?php } else { ?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
				<?php } ?>
				<br><br><br><br><br>
				<u><b>
						<?php echo $det_raport['nama_kepsek']; ?>
					</b></u><br>
				Kepala
				<?php echo $this->config->item('jenis_sekolah'); ?>
			</td>
		</tr>
	</table>



</page>