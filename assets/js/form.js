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

// ── Client-side required-field highlighting ───────────────────────────────
const stepForm = document.getElementById('stepForm');
if (stepForm) {
    stepForm.addEventListener('submit', function (e) {
        // Only validate on "next" action, not "Save & Exit"
        const action = document.activeElement?.value;
        if (action === 'exit') return;

        let firstInvalid = null;
        this.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                if (!firstInvalid) firstInvalid = field;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
        }
    });

    // Clear invalid state on input
    stepForm.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', () => field.classList.remove('is-invalid'));
    });
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
