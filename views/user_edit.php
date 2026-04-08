<?php // views/user_edit.php ?>
<div class="card bg-white mb-4">
    <div class="card-body p-4">
        
        <div class="mb-4">
            <a href="?action=users" class="btn btn-outline-secondary btn-sm fw-bold">&larr; Zurück zur Übersicht</a>
        </div>

        <h2 class="text-primary mb-1 text-uppercase fw-bold">Benutzer bearbeiten</h2>
        <span class="text-muted small d-block mb-4">Änderungen am Profil von <strong><?php echo htmlspecialchars($editUser['first_name'] . ' ' . $editUser['last_name']); ?></strong> vornehmen.</span>

        <form method="POST" action="?action=update_user" class="row g-3 bg-light p-3 rounded border">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-uppercase">Rolle <span class="text-danger">*</span></label>
                <select name="role_id" id="roleSelect" class="form-select" required>
                    <?php foreach ($allRoles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $editUser['role_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase">Vorname <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($editUser['first_name']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase">Nachname <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($editUser['last_name']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase">E-Mail <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
            </div>
            <div class="col-md-4" id="passwordContainer">
                <label class="form-label fw-bold small text-uppercase">Neues Passwort <span class="text-muted fw-normal">(Optional)</span></label>
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Leer lassen, um aktuelles zu behalten...">
                <div class="form-text small">Wird für den Login benötigt (nicht für normale Mitarbeiter).</div>
            </div>
            <div class="col-12 mt-4 text-end border-top pt-3">
                <button type="submit" class="btn btn-primary fw-bold text-uppercase px-4">Änderungen speichern</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const passwordInput = document.getElementById('passwordInput');
    
    function updatePasswordState() {
        if (roleSelect.value === '4') { // Mitarbeiter
            passwordInput.value = '';
            passwordInput.disabled = true;
            passwordInput.placeholder = 'Kein Login möglich';
        } else {
            passwordInput.disabled = false;
            passwordInput.placeholder = 'Leer lassen, um aktuelles zu behalten...';
        }
    }

    roleSelect.addEventListener('change', updatePasswordState);
    updatePasswordState(); // Initial run
});
</script>
