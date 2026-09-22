<!DOCTYPE html>
<html>
<head>
	<title>Cetak Raport</title>
	<style type="text/css">
		body {font-family: arial; font-size: 12pt}
		.table {border-collapse: collapse; border: solid 1px #999; width:100%}
		.table tr td, .table tr th {border:  solid 1px #999; padding: 3px; font-size: 12px}
		.rgt {text-align: right;}
		.ctr {text-align: center;}
		table tr td {vertical-align: top}
	</style>
</head>
<body>
	<center>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<b>LAPORAN HASIL HASIL BELAJAR<br><br>
		<?php echo strtoupper($this->config->item('jenis_sekolah')); ?><br><br>
		(<?php echo strtoupper($this->config->item('singkatan_jenis_sekolah')); ?>)
		</b>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<table style="margin-left:10%; width: 70%">
			<tr>
				<td width="20%">Nama Madrasah</td>
				<td width="2%">:</td>
				<td width="50%"><?php echo strtoupper($this->config->item('nama_sekolah')); ?></td>
			</tr>
			<tr>
				<td>NPSN</td>
				<td>:</td>
				<td><?=$this->config->item('npsn')?></td>
			</tr>
			<tr>
				<td>Alamat</td>
				<td>:</td>
				<td><?=$this->config->item('alamat_sekolah')?>, <br>Telepon : <?=$this->config->item('notelp')?></td>
			</tr>
			<tr>
				<td>Desa/Kelurahan</td>
				<td>:</td>
				<td><?=$this->config->item('kelurahan')?></td>
			</tr>
			<tr>
				<td>Kecamatan</td>
				<td>:</td>
				<td><?=$this->config->item('kecamatan')?></td>
			</tr>
			<tr>
				<td>Kabupaten/Kota</td>
				<td>:</td>
				<td><?=$this->config->item('kota')?></td>
			</tr>
			<tr>
				<td>Provinsi</td>
				<td>:</td>
				<td><?=$this->config->item('provinsi')?></td>
			</tr>
			<tr>
				<td>Website</td>
				<td>:</td>
				<td><?=$this->config->item('website')?></td>
			</tr>
			<tr>
				<td>Email</td>
				<td>:</td>
				<td><?=$this->config->item('email')?></td>
			</tr>
		</table>



	</center>
	
</body>
</html>