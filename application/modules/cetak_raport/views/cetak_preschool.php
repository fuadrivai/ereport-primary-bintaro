<page backtop="7mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<page_header><br>

	</page_header>
	<style type="text/css">
		body {
			font-family: arial;
			font-size: 11pt;
			width: 8.5in
		}

		hr {
			background-color: white;
			margin: 0 0 45px 0;
			max-width: 600px;
			border-width: 0;
		}

		hr.s1 {
			height: 5px;
			border-top: 1px solid black;
			border-bottom: 2px solid black;
		}

		hr.s2 {
			height: 9px;
			border-top: 2px solid black;
			border-bottom: 4px solid black;
		}

		hr.s3 {
			height: 14px;
			border-top: 4px solid black;
			border-bottom: 8px solid black;
		}

		hr.s4 {
			height: 14px;
			border-top: 2px solid black;
			border-bottom: 9px solid black;
		}

		hr.s5 {
			height: 5px;
			border-top: 2px solid black;
			border-bottom: 1px solid black;
		}

		hr.s6 {
			height: 9px;
			border-top: 4px solid black;
			border-bottom: 2px solid black;
		}

		hr.s7 {
			height: 14px;
			border-top: 8px solid black;
			border-bottom: 4px solid black;
		}

		hr.s8 {
			height: 12px;
			border-top: 7px solid black;
			border-bottom: 1px solid black;
		}

		hr.s9 {
			height: 6px;
			border-top: 2px solid black;
			border-bottom: 2px solid black;
		}

		.table {
			border-collapse: collapse;
			border: solid 1px #999;
			width: 100%;
			font-size: 9pt;
		}

		.table tr td,
		.table tr th {
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
			<td colspan="9" style="width: 650px;">
				<p>
				<h5 class="font_kecil" style="text-align: center;"><img src="https://report.mhis.link/images/Logo-MH-Transparan-01.png"
						width="120"><br>LAPORAN PERKEMBANGAN PESERTA DIDIK</h5>
				</p>

			</td>
		</tr>

	</table>
	<table>
		<tr>
			<td style="width:100px">Nama Siswa</td>
			<td>:</td>
			<td style="font-weight: bold; width:300px">
				<?php echo $det_siswa['nama']; ?>
			</td>
		</tr>
			<tr>
			<td style="width:100px">Nomor Induk</td>
			<td>:</td>
			<td style="font-weight: bold; width:300px">
				<?php echo $det_siswa['nis'] . " / " . $det_siswa['nisn']; ?>
			</td>
		</tr>
		<tr>
			<td>Kelas</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo strtoupper($wali_kelas['nmkelas']); ?>
			</td>
		</tr>

		<tr>
			<td>Semester</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo $semester; ?>
			</td>
		</tr>
		<tr>
			<td>Tahun Pelajaran</td>
			<td>:</td>
			<td style="font-weight: bold;">
				<?php echo $ta; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><br></td>
		</tr>
		<tr>
			<td>Tema Semester <?php echo $semester; ?></td>
			<td>:</td>
			<td style="font-weight: bold; width:500px">
				<?php echo $det_raport['tema']; ?>
			</td>
		</tr>
			
	</table>
	<br><br>
	<table>
	    <tr>
			<td colspan="9"><b>A. Nilai Agama dan Budi Pekerti</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
				<tr>
					<th style="padding: 15px 10px;width:200px" colspan="2">Indikator</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Belum Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sangat Baik</th>
							
				</tr>
			</thead>
			<tbody>
			    	<?php echo $nna; ?>
				<tr>
				    <td colspan="10">Teacher’s Note:</td>
				</tr>
			</tbody>
	</table>
	<br><br>
	<table>
	    <tr>
			<td colspan="9"><b>B. Jati Diri</b></td>
		</tr>
		<tr>
		    <td colspan="2"></td>
			<td colspan="7"><b>1. Sosial-Emosional dan Kemandirian</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
				<tr>
					<th style="padding: 15px 10px;width:200px" colspan="2">Indikator</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Belum Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sangat Baik</th>
							
				</tr>
			</thead>
			<tbody>
			    <?php echo $sek; ?>
				<tr>
				    <td colspan="10">Teacher’s Note:</td>
				</tr>
			</tbody>
	</table>
	<br><br>
	<table>
		<tr>
		    <td colspan="2"></td>
			<td colspan="7"><b>2. Fisik Motorik</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
				<tr>
					<th style="padding: 15px 10px;width:200px" colspan="2">Indikator</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Belum Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sangat Baik</th>
							
				</tr>
			</thead>
			<tbody>
			    <?php echo $fimo; ?>
				<tr>
				    <td colspan="10">Teacher’s Note:</td>
				</tr>
			</tbody>
	</table>
	<page backtop="7mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<table>
	    <tr>
			<td colspan="9"style="width:350px;"><b>C. Dasar-dasar Literasi, Matematika, Sains, Teknologi, Rekayasa, dan Seni</b></td>
		</tr>
		<tr>
		    <td colspan="2"style="width:15px;"></td>
			<td colspan="7"><b>1. Berbahasa</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
				<tr>
					<th style="padding: 15px 10px;width:200px" colspan="2">Indikator</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Belum Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sangat Baik</th>
							
				</tr>
			</thead>
			<tbody>
			    <?php echo $bi; ?>
				<tr>
				    <td colspan="10">Teacher’s Note:</td>
				</tr>
			</tbody>
	</table>
	<br><br>
	<table>
		<tr>
		    <td colspan="2"></td>
			<td colspan="7"><b>2. Kognitif</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
				<tr>
					<th style="padding: 15px 10px;width:200px" colspan="2">Indikator</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Belum Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Mulai Berkembang</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sesuai Harapan</th>
					<th style="padding: 15px 10px;width:55px" colspan="2">Berkembang Sangat Baik</th>
							
				</tr>
			</thead>
			<tbody>
			    <?php echo $kog; ?>
				<tr>
				    <td colspan="10">Teacher’s Note:</td>
				</tr>
			</tbody>
	</table>

	<page backtop="7mm" backbottom="17mm" backleft="25mm" backimg="https://report.mhis.link/images/hanya_logo_op.png" backimgw="50%">
	<table>
	    <tr>
			<td colspan="9"><b>D. Prestasi</b></td>
		</tr>
	</table>
	<table class="table">
			<thead>
    			<tr>
    				<th style="width:50px">No</th>
    				<th style="width:110px">Jenis Prestasi</th>
    				<th style="width:430px">Deskripsi</th>
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
						<td style="width:110px">
							<?php echo $p['jenis']; ?>
						</td>
						<td style="width:430px">
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
			<td colspan="9"><b>E. Ketidakhadiran</b></td>
		</tr>
	</table>
	<table class="table">
					<tr>
						<td style="width:400px">Sakit</td>
						<td style="width:210px" class="ctr">
							<?php echo $nilai_absensi['s']; ?> hari
						</td>
					</tr>
					<tr>
						<td style="width:400px">Izin</td>
						<td style="width:210px" class="ctr">
							<?php echo $nilai_absensi['i']; ?> hari
						</td>
					</tr>
					<tr>
						<td style="width:400px">Tanpa Keterangan</td>
						<td style="width:210px" class="ctr">
							<?php echo $nilai_absensi['a']; ?> hari
						</td>
					</tr>
				</table>
	<br><br>
	<table>
		<tr>
			<td colspan="9"><b>E. Lain-lain / Informasi Guru</b></td>
		</tr>
		<tr>
			<td colspan="6" style="border: solid 1px #000; padding: 20px 10px; width:600px;">
				<?php echo $catatan['catatan_wali']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="6"><br><br></td>
		</tr>
	</table>
	<table>
		<tr>
			<td style="width:200px;text-align: center;">
            Acknowledged by,
				<br><br><br><br><br><br>
				<u><b>
						<?php echo $det_raport['nama_kepsek']; ?>
					</b></u><br>
				Preschool Principal
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
					<?php echo tjs($det_raport['tgl_raport'], "l"); ?><br>
				<?php } else { ?>
					<?php echo $this->config->item('kota'); ?>,
					<?php echo tjs($det_raport['tgl_raport_kelas3'], "l"); ?><br>
				<?php } ?>
				<br><br><br><br><br>
				<u><b>
						<?php echo $wali_kelas['nmguru']; ?><br>
					</b></u>Homeroom Teacher<br>
			</td>
		</tr>
        <tr>
			<td style="text-align: center;">
			</td>
			<td style="text-align: center;">
            Mengetahui,<br>
            Orangtua / Wali
				<br><br><br><br><br><br>
				<u><b>(&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;)
					</b></u><br><br>
				Tgl:....................................
				<br>
			</td>
			<td></td>
			<td style="text-align: center;">
			</td>
		</tr>
	</table>
</page>