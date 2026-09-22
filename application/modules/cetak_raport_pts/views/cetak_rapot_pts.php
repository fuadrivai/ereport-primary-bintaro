<!DOCTYPE html>
<html>
<head>
	<title>Cetak Raport PTS</title>
	<style type="text/css">
		body {font-family: arial; font-size: 11pt; width: 8.5in}
		.table {border-collapse: collapse; border: solid 1px #999; width:100%}
		.table tr td, .table tr th {border:  solid 1px #000; padding: 3px;}
		.table tr th {font-weight: bold; text-align: center}
		.rgt {text-align: right;}
		.ctr {text-align: center;}
		.tbl {font-weight: bold}

		table tr td {vertical-align: top}
		.font_kecil {font-size: 12px}
	</style>
    <script type="text/javascript">
        function PrintWindow() {                    
           window.print();            
           CheckWindowState();
        }
    
        function CheckWindowState()    {           
            if(document.readyState=="complete") {
                window.close(); 
            } else {           
                setTimeout("CheckWindowState()", 1000)
            }
        }
        //PrintWindow();
    </script> 
</head>
<body>
	<table>
		<thead><!-- biar bisa ganti lembar otomatis --></thead>

		<tbody>
		<tr>
			<td colspan="6" style="text-align: center; font-weight: bold"><p><h3>LAPORAN HASIL BELAJAR <br> PENILAIAN TENGAH SEMESTER (PTS)</h3></p></td>
		</tr>
		<tr>
			<td width="20%">Nama Madrasah</td><td width="1%">:</td><td width="39%" class="tbl"><?php echo $this->config->item('nama_sekolah'); ?></td>
			<td width="20%">Kelas</td><td width="1%">:</td><td width="19%" class="tbl"><?php echo strtoupper($wali_kelas['nmkelas']); ?></td>
		</tr>
		<tr>
			<td>Alamat Madrasah</td><td>:</td><td class="tbl"><?php echo $this->config->item('alamat_sekolah'); ?></td>
			<td>Semester</td><td>:</td><td class="tbl"><?php echo $semester; ?></td>
		</tr>
		<tr>
			<td>Nama Siswa</td><td>:</td><td class="tbl"><?php echo $det_siswa['nama']; ?></td>
			<td>Tahun Pelajaran</td><td>:</td><td class="tbl"><?php echo $ta; ?></td>
		</tr>
		<tr>
			<td>NIS / NISN</td><td>:</td><td class="tbl"><?php echo $det_siswa['nis']." / ".$det_siswa['nisn']; ?></td>
			<td colspan="3"></td>
		</tr>
		<tr><td colspan="6"><br><br></td></tr>
		
		<tr><td colspan="6">
			<table class="table">
				<thead>
				<tr>
					<th colspan="2" rowspan="2" width="25%">Mata Pelajaran</th>
					<th colspan="4">Nilai</th>
				</tr>
				<tr>
					<th width="5%">KKM</th>
					<th width="5%">Angka</th>
					<th width="5%">Predikat</th>
					<th width="25%">Deskripsi</th>
				</tr>
				
				</thead>
				<tbody>
				<?php echo $nilai_utama; ?>
				</tbody>
			</table>
			<br><br>
		</td></tr>

		</tbody>
	</table>
		
		<tr>
			<td colspan="6">
				<br><br>
				<table width="100%">
					<tr>
						<td width="10%"></td>
						<td width="20%">
							Mengetahui : <br>
							Orang Tua/Wali, <br>
							<br><br><br><br>
							<u>..........................</u>
						</td>
						<td width="8%"></td>
						<td width="25%">
							<br>
							Wali Kelas <br>
							<br><br><br><br>
							<u><b><?php echo $wali_kelas['nmguru']; ?></b></u><br>
							NIP. <?php echo $wali_kelas['nip']; ?>
						</td>
						<td width="8%"></td>
						<td width="29%">
						    <?php 
						    if ($wali_kelas['tingkat'] != 9) {
						    ?>
							<?php echo $this->config->item('kota'); ?>, <?php echo tjs($det_raport['tgl_raport'],"l"); ?><br>
							<?php } else { ?>
							<?php echo $this->config->item('kota'); ?>, <?php echo tjs($det_raport['tgl_raport_kelas3'],"l"); ?><br>
							<?php } ?>
							Kepala <?php echo $this->config->item('jenis_sekolah'); ?> <br>
							<br><br><br><br>
							<u><b><?php echo $det_raport['nama_kepsek']; ?></b></u><br>
							NIP. <?php echo $det_raport['nip_kepsek']; ?>
						</td>
					</tr>
				</table>

			</td>
		</tr>

		</tbody>
	</table>
</body>
</html>