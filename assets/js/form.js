/* ── UdM Application Form – Client-side Logic ───────────────────────────── */
'use strict';

// ── Faculty → Course filtering (works on static + dynamic rows) ──────────
function bindFacultyFilter(row) {
    var sel = row.querySelector('.faculty-sel');
    if (!sel) { return; }
    sel.addEventListener('change', function() {
        var chosen    = this.value;
        var courseEl  = row.querySelector('.course-sel');
        if (!courseEl) { return; }
        courseEl.querySelectorAll('option[data-faculty]').forEach(function(opt) {
            opt.style.display = (!chosen || opt.dataset.faculty === chosen) ? '' : 'none';
        });
        var current = courseEl.options[courseEl.selectedIndex];
        if (current && current.style.display === 'none') { courseEl.value = ''; }
    });
    // Restore saved state on load
    sel.dispatchEvent(new Event('change'));
}
document.querySelectorAll('.course-row').forEach(function(row) {
    bindFacultyFilter(row);
});

// ── Renumber all course rows ──────────────────────────────────────────────
function renumberCourseRows() {
    var rows = document.querySelectorAll('#courseRows .course-row');
    rows.forEach(function(row, idx) {
        var badge = row.querySelector('.row-num');
        if (badge) { badge.textContent = idx + 1; }
        // Show/hide remove button: first row has no remove button (or hide it)
        var rmBtn = row.querySelector('.remove-course-row');
        if (rmBtn) { rmBtn.style.display = idx > 0 ? '' : 'none'; }
    });
}

// ── Add Course row ────────────────────────────────────────────────────────
var addCourseBtn = document.getElementById('addCourse');
if (addCourseBtn) {
    addCourseBtn.addEventListener('click', function() {
        // Clone first row as template
        var template = document.querySelector('#courseRows .course-row');
        if (!template) { return; }
        var newRow = template.cloneNode(true);

        // Reset values
        newRow.querySelectorAll('select').forEach(function(s) { s.value = ''; });
        newRow.querySelectorAll('option[data-faculty]').forEach(function(o) {
            o.style.display = '';
        });

        // Ensure remove button exists and is visible
        var rmBtn = newRow.querySelector('.remove-course-row');
        if (!rmBtn) {
            // Create remove button
            var colDiv = document.createElement('div');
            colDiv.className = 'col-auto';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-danger remove-course-row';
            btn.innerHTML = '<i class="bi bi-trash"></i>';
            colDiv.appendChild(btn);
            newRow.appendChild(colDiv);
        } else {
            rmBtn.style.display = '';
        }

        document.getElementById('courseRows').appendChild(newRow);
        bindFacultyFilter(newRow);
        renumberCourseRows();
        bindRemoveCourseRows();
    });
}

// ── Remove Course row ─────────────────────────────────────────────────────
function bindRemoveCourseRows() {
    document.querySelectorAll('.remove-course-row').forEach(function(btn) {
        btn.onclick = function() {
            btn.closest('.course-row').remove();
            renumberCourseRows();
        };
    });
}
bindRemoveCourseRows();

// ── "Currently employed" checkbox disables end-date ───────────────────────
function bindCurrentCheckboxes() {
    document.querySelectorAll('.current-check').forEach(cb => {
        cb.addEventListener('change', function () {
            const row     = this.closest('.employment-row');
            const endDate = row.querySelector('[name="end_date[]"]');
            if (endDate) {
                endDate.disabled = this.checked;
                if (this.checked) endDate.value = '';
            }
        });
    });
}
bindCurrentCheckboxes();

// ── Add / Remove employment rows ──────────────────────────────────────────
const addBtn = document.getElementById('addEmployer');
if (addBtn) {
    addBtn.addEventListener('click', () => {
        const container = document.getElementById('employmentRows');
        const idx       = container.querySelectorAll('.employment-row').length;
        const row       = document.createElement('div');
        row.className   = 'employment-row record-block position-relative';
        row.innerHTML   = `
            <button type="button"
                    class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-row"
                    aria-label="Remove record">
                <i class="bi bi-trash"></i>
            </button>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Employer / Organisation</label>
                    <input type="text" name="employer[]" class="form-control" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Job Title / Position</label>
                    <input type="text" name="job_title[]" class="form-control" maxlength="255">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date[]" class="form-control">
                </div>
                <div class="col-md-3 end-date-col">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date[]" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input current-check" type="checkbox"
                               name="is_current[${idx}]" id="current_${idx}">
                        <label class="form-check-label" for="current_${idx}">
                            Currently employed here
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Main Responsibilities</label>
                    <textarea name="responsibilities[]" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>`;
        container.appendChild(row);
        bindCurrentCheckboxes();
        bindRemoveButtons();
    });
}

function bindRemoveButtons() {
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.onclick = () => btn.closest('.employment-row').remove();
    });
}
bindRemoveButtons();

// ── Step 3: Add Record buttons ────────────────────────────────────────────

function makeSelect(fieldName, options) {
    var sel = document.createElement('select');
    sel.name = fieldName;
    sel.className = 'form-select';
    for (var i = 0; i < options.length; i++) {
        var opt = document.createElement('option');
        opt.value = options[i][0];
        opt.textContent = options[i][1];
        sel.appendChild(opt);
    }
    return sel;
}

function makeMonthSelect(fieldName) {
    var opts = [['0','Month'],['1','January'],['2','February'],['3','March'],
                ['4','April'],['5','May'],['6','June'],['7','July'],
                ['8','August'],['9','September'],['10','October'],
                ['11','November'],['12','December']];
    return makeSelect(fieldName, opts);
}

function makeYearSelect(fieldName) {
    var y0 = new Date().getFullYear();
    var opts = [['0','Year']];
    for (var y = y0; y >= 1990; y--) {
        opts.push([String(y), String(y)]);
    }
    return makeSelect(fieldName, opts);
}

function makeTextInput(fieldName, placeholder, maxLen, isSmall, inlineStyle) {
    var inp = document.createElement('input');
    inp.type = 'text';
    inp.name = fieldName;
    inp.className = isSmall ? 'form-control form-control-sm' : 'form-control';
    inp.maxLength = maxLen || 255;
    if (placeholder) { inp.placeholder = placeholder; }
    if (inlineStyle) { inp.style.cssText = inlineStyle; }
    return inp;
}

// Add school row
var schoolBtn = document.getElementById('addSchool');
if (schoolBtn) {
    schoolBtn.addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'school-row row g-2 mb-2 align-items-center';

        var c1 = document.createElement('div');
        c1.className = 'col-md-5';
        c1.appendChild(makeTextInput('school_name[]', 'Institution name', 255, false, ''));
        row.appendChild(c1);

        var c2 = document.createElement('div');
        c2.className = 'col-md-4';
        var f2 = document.createElement('div');
        f2.className = 'd-flex gap-1';
        f2.appendChild(makeMonthSelect('entered_month[]'));
        f2.appendChild(makeYearSelect('entered_year[]'));
        c2.appendChild(f2);
        row.appendChild(c2);

        var c3 = document.createElement('div');
        c3.className = 'col-md-3';
        var f3 = document.createElement('div');
        f3.className = 'd-flex gap-1';
        f3.appendChild(makeMonthSelect('left_month[]'));
        f3.appendChild(makeYearSelect('left_year[]'));
        c3.appendChild(f3);
        row.appendChild(c3);

        document.getElementById('schoolRows').appendChild(row);
    });
}

// Generic: add subject row (O-Level or A-Level)
function addSubjRow(containerId, subjName, colNames) {
    var container = document.getElementById(containerId);
    if (!container) { return; }

    var row = document.createElement('div');
    row.className = 'subj-row row g-2 mb-2 align-items-center';

    var c0 = document.createElement('div');
    c0.className = 'col-md-3';
    c0.appendChild(makeTextInput(subjName + '[]', 'Subject', 255, true, ''));
    row.appendChild(c0);

    for (var a = 0; a < 3; a++) {
        var c = document.createElement('div');
        c.className = 'col';
        var grp = document.createElement('div');
        grp.className = 'input-group input-group-sm';
        grp.appendChild(makeTextInput(colNames[a * 2] + '[]',     'MM/YYYY', 7, true, ''));
        grp.appendChild(makeTextInput(colNames[a * 2 + 1] + '[]', 'A',       5, true, 'max-width:58px'));
        c.appendChild(grp);
        row.appendChild(c);
    }

    container.appendChild(row);
}

// Wire up O-Level button
var oBtn = document.getElementById('addOLevel');
if (oBtn) {
    oBtn.addEventListener('click', function() {
        addSubjRow('oLevelRows', 'o_subject',
            ['o_a1_month','o_a1_grade','o_a2_month','o_a2_grade','o_a3_month','o_a3_grade']);
    });
}

// Wire up A-Level Principal button
var pBtn = document.getElementById('addPrincipal');
if (pBtn) {
    pBtn.addEventListener('click', function() {
        addSubjRow('principalRows', 'a_principal_subject',
            ['ap_a1_month','ap_a1_grade','ap_a2_month','ap_a2_grade','ap_a3_month','ap_a3_grade']);
    });
}

// Wire up A-Level Subsidiary button
var sBtn = document.getElementById('addSubsidiary');
if (sBtn) {
    sBtn.addEventListener('click', function() {
        addSubjRow('subsidiaryRows', 'a_subsidiary_subject',
            ['as_a1_month','as_a1_grade','as_a2_month','as_a2_grade','as_a3_month','as_a3_grade']);
    });
}

// ── Step 5: Add another document slot ────────────────────────────────────
var addDocBtn = document.getElementById('addDocSlot');
if (addDocBtn) {
    var extraDocCount = 0;
    addDocBtn.addEventListener('click', function() {
        extraDocCount++;
        var container = document.getElementById('docRows');
        if (!container) { return; }

        var col = document.createElement('div');
        col.className = 'col-md-6';

        var card = document.createElement('div');
        card.className = 'doc-upload-card record-block position-relative';

        // Remove button (top-right)
        var rmBtn = document.createElement('button');
        rmBtn.type = 'button';
        rmBtn.className = 'btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-1';
        rmBtn.setAttribute('aria-label', 'Remove');
        rmBtn.textContent = '✕';
        rmBtn.addEventListener('click', function() { col.remove(); });

        // Label
        var lbl = document.createElement('label');
        lbl.className = 'form-label fw-medium';
        lbl.textContent = 'Additional Document ' + extraDocCount;

        // File input
        var inp = document.createElement('input');
        inp.type = 'file';
        inp.name = 'other_extra[]';
        inp.className = 'form-control form-control-sm';
        inp.accept = '.pdf,.jpg,.jpeg,.png';

        card.appendChild(rmBtn);
        card.appendChild(lbl);
        card.appendChild(inp);
        col.appendChild(card);
        container.appendChild(col);

        // Validate file size on change
        inp.addEventListener('change', function() {
            if (this.files[0] && this.files[0].size > 5 * 1024 * 1024) {
                this.value = '';
                alert('File too large (max 5 MB).');
            }
        });
    });
}

// ── Client-side validation – step 7 (final submit) only ──────────────────
// Steps 1-6 rely exclusively on server-side validation so the red borders
// never appear while navigating between steps with "Next".
const stepForm   = document.getElementById('stepForm');
const hiddenStep = stepForm?.querySelector('[name="step"]');
const isFinalStep = hiddenStep && hiddenStep.value === '7';

if (stepForm && isFinalStep) {
    stepForm.addEventListener('submit', function (e) {
        const declCheck = document.getElementById('declCheck');
        if (declCheck && !declCheck.checked) {
            e.preventDefault();
            declCheck.classList.add('is-invalid');
            declCheck.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    const declCheck = document.getElementById('declCheck');
    if (declCheck) {
        declCheck.addEventListener('change', () => declCheck.classList.remove('is-invalid'));
    }
}

// ── DOB → show/hide guardian section dynamically ──────────────────────────
const dobInput = document.querySelector('[name="date_of_birth"]');
if (dobInput) {
    dobInput.addEventListener('change', function () {
        const dob     = new Date(this.value);
        const now     = new Date();
        const ageMs   = now - dob;
        const ageYrs  = ageMs / (1000 * 60 * 60 * 24 * 365.25);
        const guardian = document.querySelector('.guardian-section');
        if (guardian) {
            guardian.style.display = ageYrs < 18 ? '' : 'none';
        }
    });
}

// ── File size validation on document upload ───────────────────────────────
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const maxBytes = 5 * 1024 * 1024;
        if (this.files[0] && this.files[0].size > maxBytes) {
            this.value = '';
            alert('File is too large. Maximum size is 5 MB.');
        }
    });
});

// ── Auto-dismiss alerts after 6 seconds ──────────────────────────────────
document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(alert => {
    if (!alert.querySelector('[data-bs-dismiss]')) return;
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if (bsAlert) bsAlert.close();
    }, 6000);
});
