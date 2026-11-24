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
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 24px 28px;
        border: 1.5px solid #e2e8f0;
        border-left: 5px solid #4f46e5;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .result-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-tag.badge-success {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    .badge-tag.badge-spirit {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }

    .result-info h4 {
        margin: 0;
        color: #1e293b;
        font-weight: 700;
        font-size: 17px;
    }

    .doa-text {
        margin: 6px 0 0;
        color: #475569;
        font-size: 13.5px;
        line-height: 1.5;
        font-style: italic;
    }

    .doa-text i {
        color: #6366f1;
        opacity: 0.7;
        margin-right: 4px;
    }

    .btn-view {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff !important;
        padding: 12px 22px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none !important;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        white-space: nowrap;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.35);
        color: #fff !important;
    }

    @media (max-width: 768px) {
        .result-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .btn-view {
            width: 100%;
            justify-content: center;
        }
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
                        <div class="col-md-4">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Language</label>
                                <select name="language" class="form-control" required>
                                    <option value="English" selected>English</option>
                                    <option value="Indonesia">Indonesia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <input type="email" name="teacherEmail" class="form-control" value="<?=isset($teacher_email) ? htmlspecialchars($teacher_email) : ''?>" required>
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
                            <div class="result-badges">
                                <span class="badge-tag badge-success"><i class="fa fa-check-circle"></i> Lesson Plan Ready</span>
                                <span class="badge-tag badge-spirit"><i class="fa fa-heart"></i> Inspiring Educators</span>
                            </div>
                            <h4 id="docTitle">Lesson Plan Generated!</h4>
                            <p class="doa-text">
                                <i class="fa fa-quote-left"></i>
                                May Allah SWT bless your hard work and grant ease to every classroom moment today. Teach with passion, love, and inspire greatness in every student! 🌟🤲
                            </p>
                        </div>
                        <div class="result-action">
                            <a href="#" id="docLink" target="_blank" class="btn-view">
                                <i class="fa fa-external-link"></i> Open Document
                            </a>
                        </div>
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
                    $('#docTitle').text(response.document_title || "Lesson Plan Generated!");
                    $('#docLink').attr('href', response.document_link);
                    $result.fadeIn();
                    
                    swal({
                        title: "Alhamdulillah, Success! 🎉✨",
                        html: '<div style="font-size: 14px; color: #334155; line-height: 1.6; text-align: center; margin-top: 10px;">' +
                              '<p style="font-size: 16px; font-weight: 700; color: #4338ca; margin-bottom: 12px;">' +
                              'Your lesson plan has been successfully created and is ready to use! 📝' +
                              '</p>' +
                              '<p style="margin-bottom: 12px;">' +
                              '<strong>Keep up the wonderful spirit, amazing teachers! 💪🌟</strong><br>' +
                              'May every effort, second of your time, and knowledge shared become an enduring source of continuous blessings and rewards for you and your family.' +
                              '</p>' +
                              '<p style="color: #64748b; font-style: italic; margin-bottom: 0;">' +
                              '"May Allah SWT grant you ease, patience, and boundless joy in nurturing our future generations. Have an inspiring and blessed teaching day!" 🤲❤️' +
                              '</p>' +
                              '</div>',
                        type: "success",
                        confirmButtonText: '<i class="fa fa-heart"></i> Aamiin, Ready to Teach! 🚀',
                        confirmButtonColor: "#4f46e5"
                    });
                } else {
                    swal("Failed", response.message || "Failed to generate lesson plan. Please try again.", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                swal("Connection Error", "There was an error connecting to the lesson plan generation service.", "error");
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
