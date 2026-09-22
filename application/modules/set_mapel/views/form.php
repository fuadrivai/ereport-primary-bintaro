<?php
$mode = isset($mode) ? $mode : 'create';
$row  = $row ?? null;

$selected_guru  = $row['id_guru']  ?? '';
$selected_mapel = $row['id_mapel'] ?? '';
$selected_kelas = $row['id_kelas'] ?? '';
$subject_kkm    = isset($row['subject_kkm']) ? (int)$row['subject_kkm'] : ''; // prefill on edit
?>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="header">
        <h4 class="title">
          <?= $mode === 'edit' ? 'Edit Mata Pelajaran Guru' : 'Set Mata Pelajaran Guru' ?>
        </h4>
      </div>
      <div class="content">
        <form method="post" id="<?= $nama_form; ?>" name="<?= $nama_form; ?>" action="<?= base_url().$url; ?>/simpan">

          <?php if ($mode === 'edit' && !empty($row['id'])): ?>
            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
          <?php endif; ?>

          <div class="row">
            <div class="col-md-4">
              <label>Pilih Guru</label>
              <select name="guru" id="guru" class="form-control" required>
                <option value=""></option>
                <?php if (!empty($r_guru)) foreach ($r_guru as $g): ?>
                  <option value="<?= (int)$g['id']; ?>" <?= ((int)$g['id']==(int)$selected_guru?'selected':''); ?>>
                    <?= htmlspecialchars($g['nama']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row" style="margin-top:10px;">
            <div class="col-md-4">
              <label>Pilih Mapel</label>
              <select name="mapel" id="mapel" class="form-control" required>
                <option value=""></option>
                <?php if (!empty($r_mapel)) foreach ($r_mapel as $m): ?>
                  <option value="<?= (int)$m['id']; ?>" <?= ((int)$m['id']==(int)$selected_mapel?'selected':''); ?>>
                    <?= htmlspecialchars($m['nama']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row" style="margin-top:10px;">
          <div class="col-md-4">
            <label>KKM Mapel (subject_kkm)</label>
            <input
              type="number"
              name="subject_kkm"
              id="subject_kkm"
              class="form-control"
              min="0" max="100" step="1"
              value="<?= htmlspecialchars($subject_kkm, ENT_QUOTES); ?>"
              placeholder="Contoh: 75"
              required>
            <small class="text-muted">Isi nilai Kriteria Ketuntasan Minimal (0–100).</small>
          </div>
        </div>
          <!-- KELAS PICKER -->
          <?php if ($mode === 'edit'): ?>
            <div class="row" style="margin-top:10px;">
              <div class="col-md-12"><label>Pilih Kelas (edit hanya satu kelas)</label></div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <select id="data_semua" size="10" multiple class="form-control">
                  <?php if (!empty($r_kelas)) foreach ($r_kelas as $k): if ((int)$k['id'] !== (int)$selected_kelas): ?>
                    <option value="<?= (int)$k['id']; ?>"><?= htmlspecialchars($k['nama']); ?></option>
                  <?php endif; endforeach; ?>
                </select>
                <small class="text-muted">*Double-click kiri untuk memilih</small>
              </div>
              <div class="col-md-2" style="display:flex;align-items:center;justify-content:center;">
                <i class="fa fa-exchange"></i>
              </div>
              <div class="col-md-4">
                <select name="kelas" id="data_pilih" size="10" class="form-control" required>
                  <?php if (!empty($r_kelas)) foreach ($r_kelas as $k): if ((int)$k['id'] === (int)$selected_kelas): ?>
                    <option value="<?= (int)$k['id']; ?>" selected><?= htmlspecialchars($k['nama']); ?></option>
                  <?php endif; endforeach; ?>
                </select>
              </div>
            </div>
          <?php else: ?>
            <div class="">
              <div class="col-md-12">
                <label>Pilih Kelas (*) Untuk memilih satu persatu, gunakan Ctrl+Klik, Untuk memilih Semua gunakan Ctrl+A</label>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <select name="data_semua" size="10" multiple id="data_semua" class="form-control">
                  <?php if (!empty($r_kelas)) foreach ($r_kelas as $k): ?>
                    <option value="<?= (int)$k['id']; ?>"><?= htmlspecialchars($k['nama']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <center>
                  <button type="button" class="btn btn-success" id="tambah"><i class="fa fa-chevron-right"></i></button>
                  <button type="button" class="btn btn-success" id="kurang" style="margin-top:6px;"><i class="fa fa-chevron-left"></i></button>
                </center>
              </div>
              <div class="col-md-4">
                <select name="data_pilih[]" size="10" multiple id="data_pilih" class="form-control" required></select>
              </div>
            </div>
          <?php endif; ?>

          <div style="margin-top:12px;">
            <?php if ($mode === 'edit'): ?>
              <button type="submit" class="btn btn-primary" name="action" value="update">Update</button>
              <button type="submit" class="btn btn-success" name="action" value="save_as_new">Save as New</button>
            <?php else: ?>
              <button type="submit" class="btn btn-primary" name="action" value="create">Simpan</button>
            <?php endif; ?>
            <a href="<?= base_url().$url; ?>" class="btn btn-default">Kembali</a>
          </div>

          <div class="clearfix"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function () {
  <?php if ($mode === 'edit'): ?>
    // double-click left to select as the only kelas on the right
    $('#data_semua').on('dblclick', 'option', function () {
      var val  = $(this).val();
      var text = $(this).text();
      $('#data_pilih').empty().append($('<option>', {value: val, text: text, selected: true}));
    });
  <?php else: ?>
    // original dual list behavior
    $('#data_semua').pairMaster();
    $('#tambah').click(function(){ $('#data_semua').addSelected('#data_pilih'); });
    $('#kurang').click(function(){ $('#data_pilih').removeSelected('#data_semua'); });
  <?php endif; ?>
});
</script>
