<?php
// views/users.php
?>
<div class="card bg-white mb-4">
    <div class="card-body p-4">
        <h2 class="text-primary mb-1 text-uppercase fw-bold">Neuen Benutzer anlegen</h2>
        <span class="text-muted small d-block mb-4">Hier können Sie neue Mitarbeiter oder Administratoren im System registrieren.</span>

        <form method="POST" action="?action=store_user" class="row g-3 bg-light p-3 rounded border">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-uppercase">Rolle <span class="text-danger">*</span></label>
                <select name="role_id" id="roleSelect" class="form-select" required>
                    <option value="">-- Wählen --</option>
                    <?php foreach ($allRoles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase">Vorname <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase">Nachname <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-uppercase">E-Mail <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-4" id="passwordContainer">
                <label class="form-label fw-bold small text-uppercase">Initiales Passwort <span class="text-danger" id="reqAsterisk">*</span></label>
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Passwort vergeben..." required>
                <div class="form-text small">Wird für den Login benötigt (nicht für normale Mitarbeiter).</div>
            </div>
            <div class="col-12 mt-4 text-end border-top pt-3">
                <button type="submit" class="btn btn-primary fw-bold text-uppercase px-4">Benutzer speichern</button>
            </div>
        </form>
    </div>
</div>

<div class="card bg-white">
    <div class="card-body p-4">
        <h2 class="text-primary mb-4 text-uppercase fw-bold">Aktive Benutzer</h2>
        <div class="table-responsive">
            <table class="table table-bordered align-middle m-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Rolle</th>
                        <th class="text-end">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allUsers)): ?>
                        <?php foreach ($allUsers as $u): ?>
                            <tr>
                                <td class="text-muted small"><strong>#<?php echo htmlspecialchars($u['id']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($u['last_name'] . ', ' . $u['first_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php
                                        $roleBg = 'bg-secondary';
                                        if ($u['role_id'] == 1) { // IT_MANAGER
                                            $roleBg = 'bg-danger';
                                        } elseif ($u['role_id'] == 2) { // PROJECT_MANAGER
                                            $roleBg = 'bg-warning text-dark';
                                        } elseif ($u['role_id'] == 3) { // HR
                                            $roleBg = 'bg-success';
                                        }
                                    ?>
                                    <span class="badge <?php echo $roleBg; ?> text-uppercase border-start border-3 border-dark">
                                        <?php echo htmlspecialchars($u['role_name']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="?action=edit_user&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="?action=delete_user" onsubmit="return confirm('Möchten Sie diesen Benutzer wirklich entfernen?');" class="m-0 p-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Sie können sich nicht selbst löschen">Löschen</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">Keine Benutzer gefunden.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const passwordInput = document.getElementById('passwordInput');
    const passwordContainer = document.getElementById('passwordContainer');
    const reqAsterisk = document.getElementById('reqAsterisk');

    roleSelect.addEventListener('change', function() {
        // Mitarbeiter Rolle hat ID = 4
        if (this.value === '4') {
            passwordInput.value = '';
            passwordInput.disabled = true;
            passwordInput.removeAttribute('required');
            reqAsterisk.style.display = 'none';
            passwordInput.placeholder = 'Kein Login möglich';
        } else {
            passwordInput.disabled = false;
            passwordInput.setAttribute('required', 'required');
            reqAsterisk.style.display = 'inline';
            passwordInput.placeholder = 'Passwort vergeben...';
        }
    });
});
</script>
