
<page backtop="7mm" backbottom="7mm" backleft="10mm" backright="10mm" backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%"> 
<style type="text/css">
		body {font-family: arial; font-size: 11pt; width: 8.5in}
		.table {border-collapse: collapse; border: solid 1px #0162b1; width:100%}
		.table tr td, .table tr th {border:  solid 1px #0162b1; padding: 3px;}
		.table tr th {font-weight: bold; text-align: center}
		.rgt {text-align: right;}
		.ctr {text-align: center;}
		.tbl {font-weight: bold}

		table tr td {vertical-align: top}
		.font_kecil {font-size: 12px}
	</style>
<table>
    <tr>
        <td>
            <img src="C:\Users\sallu\Downloads\Logo MH Transparan-01.png" width="150" style="margin-left:20px"></td>
        <td>
            <p style="text-align: center; margin-left:180px;"><b>Students' Mid Semester Progress Report of <?php echo $semester; ?> Semester<br>
            July - October <?php echo $ta; ?> School Year<br>
            Mutiara Harapan Islamic Preschool</b></p>
        </td>
    </tr>
</table>
<br><br>
<table style="font-weight: bold;">
    <tr>
        <td style="width:150px">Level</td>
        <td>:  <?php echo strtoupper($wali_kelas['nmkelas']); ?></td>
    </tr>
    <tr>
        <td style="width:150px">Name</td>
        <td>:  <?php echo $det_siswa['nama']; ?></td>
    </tr>
</table>
<br><br>
<table class="table">
    <tr style="font-weight: bold;text-align: center;">
        <td style="width:90px;padding: 10px 5px;">Aspect of Development</td>
        <td style="width:425px;padding: 10px 5px;">Progress</td>
        <td style="width:425px;padding: 10px 5px;">Need to Improve</td>
    </tr>
    <tr>
        <td>Islamic Values</td>
        <?php echo $ta_catatan_nna; ?>
    </tr>
</table>
<br><br>
<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
<table class="table">
    <tr style="font-weight: bold;text-align: center;">
        <td style="width:90px;padding: 10px 5px;">Aspect of Development</td>
        <td style="width:275px;padding: 10px 5px;">Progress</td>
        <td style="width:275px;padding: 10px 5px;">Need to Improve</td>
    </tr>
    <tr>
        <td>Social-Emotional</td>
        <?php echo $ta_catatan_sek; ?>
    </tr>
</table>
<br><br>
<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
<table class="table">
    <tr style="font-weight: bold;text-align: center;">
        <td style="width:90px;padding: 10px 5px;">Aspect of Development</td>
        <td style="width:275px;padding: 10px 5px;">Progress</td>
        <td style="width:275px;padding: 10px 5px;">Need to Improve</td>
    </tr>
    <tr>
        <td>Language Skills</td>
        <?php echo $ta_catatan_bi; ?>
    </tr>
</table>
<br><br>
<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
<table class="table">
    <tr style="font-weight: bold;text-align: center;">
        <td style="width:90px;padding: 10px 5px;">Aspect of Development</td>
        <td style="width:275px;padding: 10px 5px;">Progress</td>
        <td style="width:275px;padding: 10px 5px;">Need to Improve</td>
    </tr>
    <tr>
        <td>Cognitive Skills</td>
        <?php echo $ta_catatan_kog; ?>
    </tr>
</table>
<br><br>
<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
<table class="table">
    <tr style="font-weight: bold;text-align: center;">
        <td style="width:90px;padding: 10px 5px;">Aspect of Development</td>
        <td style="width:275px;padding: 10px 5px;">Progress</td>
        <td style="width:275px;padding: 10px 5px;">Need to Improve</td>
    </tr>
    <tr>
        <td style="width:90px;padding: 10px 5px;">Fine and Gross Motor</td>
        <?php echo $ta_catatan_fimo; ?>
    </tr>
</table>
<br><br>
<page backtop="30mm" backbottom="7mm" backleft="10mm" backright="10mm"
	backimg="C:\Users\sallu\Downloads\hanya_logo_op.png" backimgw="50%">
<table>
		<tr>
			<td style="width:300px;text-align: center;">
            Acknowledged by,
				<br><br><br><br><br><br>
				<u><b>
						<?php echo $det_raport['nama_kepsek']; ?>
					</b></u><br>
				Primary Principal
				<br>
			</td>
			<td style="width:380px;text-align: center;">

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