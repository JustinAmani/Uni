/* ── UdM Application Form – Client-side Logic ───────────────────────────── */
'use strict';

// ── Faculty → Course filtering ────────────────────────────────────────────
document.querySelectorAll('.faculty-select').forEach(sel => {
    sel.addEventListener('change', function () {
        const idx       = this.dataset.index;
        const courseEl  = document.getElementById('courseSelect' + idx);
        const chosen    = this.value;

        courseEl.querySelectorAll('option[data-faculty]').forEach(opt => {
            opt.style.display = (!chosen || opt.dataset.faculty === chosen) ? '' : 'none';
        });

        // Reset selection if current choice is now hidden
        const current = courseEl.options[courseEl.selectedIndex];
        if (current && current.style.display === 'none') {
            courseEl.value = '';
        }
    });
    // Trigger on load to restore saved state
    sel.dispatchEvent(new Event('change'));
});

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
        row.className   = 'employment-row card border mb-3 p-3 position-relative';
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

// ── Step 3: Add Record buttons (DOM-only, no innerHTML) ───────────────────

/** Create a <select> for month names using safe DOM methods */
function createMonthSelect(fieldName) {
    const months = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    const sel = document.createElement('select');
    sel.name      = fieldName;
    sel.className = 'form-select';

    const def = document.createElement('option');
    def.value       = '0';
    def.textContent = 'Month';
    sel.appendChild(def);

    months.forEach((m, i) => {
        const opt = document.createElement('option');
        opt.value       = String(i + 1);
        opt.textContent = m;
        sel.appendChild(opt);
    });
    return sel;
}

/** Create a <select> for years (current year → 1990) using safe DOM methods */
function createYearSelect(fieldName) {
    const y0  = new Date().getFullYear();
    const sel = document.createElement('select');
    sel.name      = fieldName;
    sel.className = 'form-select';

    const def = document.createElement('option');
    def.value       = '0';
    def.textContent = 'Year';
    sel.appendChild(def);

    for (let y = y0; y >= 1990; y--) {
        const opt = document.createElement('option');
        opt.value       = String(y);
        opt.textContent = String(y);
        sel.appendChild(opt);
    }
    return sel;
}

/** Create a text <input> using safe DOM methods */
function createInput(fieldName, { placeholder = '', maxLen = 255, size = 'normal', style = '' } = {}) {
    const inp = document.createElement('input');
    inp.type      = 'text';
    inp.name      = fieldName;
    inp.className = size === 'sm' ? 'form-control form-control-sm' : 'form-control';
    inp.maxLength = maxLen;
    if (placeholder) inp.placeholder = placeholder;
    if (style)       inp.style.cssText = style;
    return inp;
}

/** Add one empty secondary-school row to #schoolRows */
const addSchoolBtn = document.getElementById('addSchool');
if (addSchoolBtn) {
    addSchoolBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'school-row row g-2 mb-2 align-items-center';

        // Institution col
        const c1 = document.createElement('div');
        c1.className = 'col-md-5';
        c1.appendChild(createInput('school_name[]', { placeholder: 'Institution name' }));
        row.appendChild(c1);

        // Entered / Left cols
        [['entered_month[]', 'entered_year[]', 'col-md-4'],
         ['left_month[]',    'left_year[]',    'col-md-3']].forEach(([mName, yName, cls]) => {
            const c    = document.createElement('div');
            c.className = cls;
            const flex = document.createElement('div');
            flex.className = 'd-flex gap-1';
            flex.appendChild(createMonthSelect(mName));
            flex.appendChild(createYearSelect(yName));
            c.appendChild(flex);
            row.appendChild(c);
        });

        document.getElementById('schoolRows').appendChild(row);
    });
}

/** Add one empty subject row to a results-grid container */
function addSubjectRow(containerId, subjName, colNames) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'subj-row row g-2 mb-2 align-items-center';

    // Subject col
    const c0 = document.createElement('div');
    c0.className = 'col-md-3';
    c0.appendChild(createInput(subjName + '[]', { placeholder: 'Subject', size: 'sm' }));
    row.appendChild(c0);

    // Three attempt cols (MM/YYYY + Grade)
    for (let a = 0; a < 3; a++) {
        const c     = document.createElement('div');
        c.className = 'col';
        const grp   = document.createElement('div');
        grp.className = 'input-group input-group-sm';
        grp.appendChild(createInput(colNames[a * 2] + '[]',
            { placeholder: 'MM/YYYY', maxLen: 7, size: 'sm' }));
        grp.appendChild(createInput(colNames[a * 2 + 1] + '[]',
            { placeholder: 'A', maxLen: 5, size: 'sm', style: 'max-width:58px' }));
        c.appendChild(grp);
        row.appendChild(c);
    }

    container.appendChild(row);
}

// Wire up the four Add Record buttons
[
    ['addOLevel',     'oLevelRows',      'o_subject',
     ['o_a1_month','o_a1_grade','o_a2_month','o_a2_grade','o_a3_month','o_a3_grade']],
    ['addPrincipal',  'principalRows',   'a_principal_subject',
     ['ap_a1_month','ap_a1_grade','ap_a2_month','ap_a2_grade','ap_a3_month','ap_a3_grade']],
    ['addSubsidiary', 'subsidiaryRows',  'a_subsidiary_subject',
     ['as_a1_month','as_a1_grade','as_a2_month','as_a2_grade','as_a3_month','as_a3_grade']],
].forEach(([btnId, containerId, subjName, colNames]) => {
    const btn = document.getElementById(btnId);
    if (btn) btn.addEventListener('click', () => addSubjectRow(containerId, subjName, colNames));
});

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
