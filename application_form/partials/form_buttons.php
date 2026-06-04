<div class="d-flex justify-content-between align-items-center mt-2">
    <button type="submit" name="action" value="exit" class="btn btn-outline-secondary"
            formnovalidate>
        <i class="bi bi-save me-1"></i> Save &amp; Exit
    </button>
    <div class="d-flex gap-2">
        <?php if ($step > 1): ?>
            <a href="<?= APP_URL ?>/application_form/index.php?step=<?= $step - 1 ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Previous
            </a>
        <?php endif; ?>
        <button type="submit" name="action" value="next" class="btn btn-udm-primary">
            Next <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
