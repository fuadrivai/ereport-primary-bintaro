<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">KooBits Student Proficiency Report</h4>
                <p class="category">Student progress across various skills categorized by competency levels.</p>
            </div>
            <div class="content">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tblKoobitsReport" style="width: 100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Fullname</th>
                                <th width="10%">Level</th>
                                <th width="10%">Grade</th>
                                <th width="10%">Mastered</th>
                                <th width="10%">Passed</th>
                                <th width="10%">Needs Imp.</th>
                                <th width="10%">Incomplete</th>
                                <th width="10%">Total Skills</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="tblLoadingRow">
                                <td colspan="9" class="text-center">Loading data, please wait...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $.getJSON('<?php echo base_url(); ?>scratch/get_koobits_data', function(response) {
        if (response && response.status === 'success') {
            $('#tblLoadingRow').remove();
            let tbody = $('#tblKoobitsReport tbody');
            let counter = 1;

            response.data.forEach(function(student) {
                let det = student.CompetencyDetails;
                let total = (det.Mastered || 0) + (det.Passed || 0) + (det.NeedImprove || 0) + (det.Incomplete || 0);
                
                let rowHtml = `<tr>
                    <td>${counter++}</td>
                    <td style="font-weight: 600; color: #1e293b;">${student.Fullname}</td>
                    <td><span class="label label-info">Level ${student.Level}</span></td>
                    <td>${student.Grade}</td>
                    <td><b class="text-success">${det.Mastered || 0}</b></td>
                    <td><b class="text-primary">${det.Passed || 0}</b></td>
                    <td><b class="text-warning">${det.NeedImprove || 0}</b></td>
                    <td><b class="text-danger">${det.Incomplete || 0}</b></td>
                    <td>${total}</td>
                </tr>`;
                tbody.append(rowHtml);
            });

            if (counter === 1) {
                tbody.append('<tr><td colspan="9" class="text-center">No KooBits data found.</td></tr>');
            } else {
                $('#tblKoobitsReport').DataTable({
                    "language": {
                        "url": base_url + "aset/plugins/datatables/Indonesian.json"
                    },
                    "pageLength": 25
                });
            }
        } else {
            $('#tblLoadingRow td').text('Failed to load KooBits data. ' + (response.message || ''));
        }
    }).fail(function() {
        $('#tblLoadingRow td').text('Error fetching KooBits data.');
    });
});
</script>
