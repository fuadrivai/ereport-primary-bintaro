<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="header">
                <h4 class="title">LMS Subject Completion Report</h4>
                <p class="category">Percentage completion per subject based on Agilix Buzz LMS. Click on a subject to view student details.</p>
            </div>
            <div class="content">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tblBuzzReport" style="width: 100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Subject</th>
                                <th width="15%">Completable</th>
                                <th width="45%">Completion %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="tblLoadingRow">
                                <td colspan="5" class="text-center">Loading data, please wait...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Student Details -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="studentModalLabel">Student Completion Details</h4>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="tblStudentDetails" style="width: 100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="35%">Student Name</th>
                        <th width="15%">Completable</th>
                        <th width="15%">Completed</th>
                        <th width="30%">Completion %</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
window.coursesData = [];

function showStudentDetails(courseId) {
    if (!window.coursesData) return;
    
    // Find course from data
    let course = window.coursesData.find(c => c.courseId == courseId);
    if (!course) return;

    if ($.fn.DataTable.isDataTable('#tblStudentDetails')) {
        $('#tblStudentDetails').DataTable().clear().destroy();
    }

    $('#studentModalLabel').text('Student Completion Details - ' + course.courseTitle);
    
    let tbody = $('#tblStudentDetails tbody');
    tbody.empty();

    let studentCounter = 1;
    if (course.students && course.students.length > 0) {
        course.students.forEach(function(student) {
            let completable = parseFloat(student.completable) || 0;
            let completed = parseFloat(student.completed) || 0;
            
            let pct = 0;
            if (completable > 0) {
                pct = (completed / completable) * 100;
            }
            
            let name = student.firstname + ' ' + student.lastname;

            let rowHtml = `<tr>
                <td>${studentCounter++}</td>
                <td>${name}</td>
                <td>${completable}</td>
                <td>${completed}</td>
                <td>
                    <div class="progress" style="margin-bottom: 0; background-color: #e2e8f0; border-radius: 4px;">
                      <div class="progress-bar ${pct >= 75 ? 'progress-bar-success' : (pct >= 50 ? 'progress-bar-warning' : 'progress-bar-danger')}" 
                           role="progressbar" 
                           aria-valuenow="${pct}" 
                           aria-valuemin="0" 
                           aria-valuemax="100" 
                           style="width: ${pct}%; min-width: 2em; line-height: 20px;">
                        ${pct.toFixed(2)}%
                      </div>
                    </div>
                </td>
            </tr>`;
            tbody.append(rowHtml);
        });
    } else {
        tbody.append('<tr><td colspan="5" class="text-center">No students found for this subject.</td></tr>');
    }
    
    $('#tblStudentDetails').DataTable({
        "language": {
            "url": base_url + "aset/plugins/datatables/Indonesian.json"
        }
    });

    $('#studentModal').modal('show');
}

$(document).ready(function() {
    // Readjust columns when modal is completely shown
    $('#studentModal').on('shown.bs.modal', function () {
        if ($.fn.DataTable.isDataTable('#tblStudentDetails')) {
            $('#tblStudentDetails').DataTable().columns.adjust();
        }
    });

    $.getJSON('<?php echo base_url(); ?>scratch/get_student_completable', function(response) {
        if (response && response.status === 'success') {
            window.coursesData = response.data;
            $('#tblLoadingRow').remove();
            let tbody = $('#tblBuzzReport tbody');
            let counter = 1;

            response.data.forEach(function(course) {
                if (course.students && course.students.length > 0) {
                    let courseCompletable = 0;
                    let courseCompleted = 0;

                    course.students.forEach(function(student) {
                        courseCompletable += parseFloat(student.completable) || 0;
                        courseCompleted += parseFloat(student.completed) || 0;
                        studentcompletable = student.completable;
                    });

                    // Only display subjects with target metrics
                    if (courseCompletable > 0) {
                        let pct = (courseCompleted / courseCompletable) * 100;
                        let rowHtml = `<tr>
                            <td>${counter++}</td>
                            <td><a href="javascript:void(0)" onclick="showStudentDetails('${course.courseId}')" style="font-weight: 600; color: #4f46e5;">${course.courseTitle}</a></td>
                            <td>${studentcompletable}</td>
                            <td>
                                <div class="progress" style="margin-bottom: 0; background-color: #e2e8f0; border-radius: 4px;">
                                  <div class="progress-bar ${pct >= 75 ? 'progress-bar-success' : (pct >= 50 ? 'progress-bar-warning' : 'progress-bar-danger')}" 
                                       role="progressbar" 
                                       aria-valuenow="${pct}" 
                                       aria-valuemin="0" 
                                       aria-valuemax="100" 
                                       style="width: ${pct}%; min-width: 2em; line-height: 20px;">
                                    ${pct.toFixed(2)}%
                                  </div>
                                </div>
                            </td>
                        </tr>`;
                        tbody.append(rowHtml);
                    }
                }
            });

            if (counter === 1) {
                tbody.append('<tr><td colspan="5" class="text-center">No subjects found with completion targets.</td></tr>');
            } else {
                // Initialize DataTables
                $('#tblBuzzReport').DataTable({
                    "language": {
                        "url": base_url + "aset/plugins/datatables/Indonesian.json"
                    }
                });
            }
        } else {
            $('#tblLoadingRow td').text('Failed to load data.');
        }
    }).fail(function() {
        $('#tblLoadingRow td').text('Error fetching data.');
    });
});
</script>
