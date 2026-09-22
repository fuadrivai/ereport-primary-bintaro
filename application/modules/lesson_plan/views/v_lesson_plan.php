<style>
    .generator-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid #edf2f7;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .generator-header {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        padding: 30px 40px;
        color: #ffffff;
        position: relative;
    }

    .generator-header h3 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 24px;
    }

    .generator-header p {
        margin: 10px 0 0;
        opacity: 0.9;
        font-size: 15px;
    }

    .generator-body {
        padding: 40px;
    }

    .form-group label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        font-size: 14px;
        display: block;
    }

    .form-control {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        padding: 12px 16px;
        height: auto;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .btn-generate {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-generate:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        color: #fff;
    }

    .btn-generate:disabled {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
    }

    .result-container {
        margin-top: 30px;
        display: none;
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .result-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        border: 1px dashed #6366f1;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .result-info h4 {
        margin: 0;
        color: #1e293b;
        font-weight: 700;
        font-size: 16px;
    }

    .result-info p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .btn-view {
        background: #10b981;
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-view:hover {
        background: #059669;
        color: #fff;
    }

    .loader {
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        display: none;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .custom-checkbox input {
        display: none;
    }

    .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        display: inline-block;
        position: relative;
        transition: all 0.2s;
    }

    .custom-checkbox input:checked + .checkmark {
        background: #6366f1;
        border-color: #6366f1;
    }

    .custom-checkbox input:checked + .checkmark:after {
        content: '';
        position: absolute;
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
</style>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="generator-card">
            <div class="generator-header">
                <h3>Lesson Plan Generator</h3>
                <p>Generate comprehensive lesson plans tailored to your curriculum in seconds.</p>
            </div>
            <div class="generator-body">
                <form id="lessonPlanForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grade Level</label>
                                <select name="grade" class="form-control" required>
                                    <option value="">Select Grade</option>
                                    <?php foreach($grades as $g): ?>
                                        <option value="Grade <?=$g['tingkat']?> (<?=$g['nama']?>)">Grade <?=$g['tingkat']?> - <?=$g['nama']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Number of Meetings</label>
                                <input type="number" name="numMeetings" class="form-control" placeholder="e.g. 3" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>New Unit?</label>
                                <div style="padding-top: 8px;">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" name="isNewUnit" value="true">
                                        <span class="checkmark"></span>
                                        <span style="font-size: 14px; font-weight: 500;">Yes</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Unit Title</label>
                        <input type="text" name="unit" class="form-control" placeholder="e.g. Parts of a Book" required>
                    </div>

                    <div class="form-group">
                        <label>Learning Objectives (LO)</label>
                        <textarea name="lo" class="form-control" rows="3" placeholder="What should students learn?"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Success Criteria (SC)</label>
                        <textarea name="sc" class="form-control" rows="3" placeholder="I can..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Key Concepts (KC)</label>
                        <input type="text" name="kc" class="form-control" placeholder="Keywords or main concepts">
                    </div>

                    <div class="form-group">
                        <label>Teacher Email</label>
                        <input type="email" name="teacherEmail" class="form-control">
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-generate" id="btnSubmit">
                            <span class="loader"></span>
                            <i class="fa fa-magic"></i> Generate Lesson Plan
                        </button>
                    </div>
                </form>

                <div class="result-container" id="resultContainer">
                    <div class="result-card">
                        <div class="result-info">
                            <h4 id="docTitle">Lesson Plan Generated!</h4>
                            <p>Your document has been created and is ready for review.</p>
                        </div>
                        <a href="#" id="docLink" target="_blank" class="btn-view">
                            <i class="fa fa-external-link"></i> Open Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#lessonPlanForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btnSubmit');
        const $loader = $('.loader');
        const $icon = $('.btn-generate i');
        const $result = $('#resultContainer');
        
        // UI Reset
        $result.hide();
        $btn.prop('disabled', true);
        $loader.show();
        $icon.hide();
        
        $.ajax({
            url: '<?=base_url()?>lesson_plan/generate',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#docTitle').text(response.document_title);
                    $('#docLink').attr('href', response.document_link);
                    $result.fadeIn();
                    
                    swal({
                        title: "Success!",
                        text: "Your lesson plan has been generated.",
                        type: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    swal("Error", response.message || "Failed to generate lesson plan", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                swal("API Error", "There was an error connecting to the generation service.", "error");
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loader.hide();
                $icon.show();
            }
        });
    });
});
</script>
