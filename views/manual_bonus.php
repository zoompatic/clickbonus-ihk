<?php // views/manual_bonus.php ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card bg-white mt-4 shadow-sm">
            <div class="card-body p-4">
                <h2 class="text-primary mb-1 text-uppercase fw-bold">Freie Prämie vergeben</h2>
                <p class="text-muted small mb-4">Manuelle und projektunabhängige Bonuszahlungen für Mitarbeiter erfassen.</p>

                <form method="POST" action="?action=store_manual_bonus" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Mitarbeiter auswählen <span class="text-danger">*</span></label>
                        <select name="target_user_id" class="form-select bg-light" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?php echo $u['id']; ?>">
                                    <?php echo htmlspecialchars($u['last_name'] . ', ' . $u['first_name'] . ' (' . $u['role_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Betrag in Euro <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="z. B. 250.00" required>
                            <span class="input-group-text bg-light">€</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Grund / Begründung <span class="text-danger">*</span></label>
                        <input type="text" name="comment" class="form-control" placeholder="z.B. Weihnachtsbonus, Gesundheitsprämie..." required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold text-uppercase">Prämie eintragen & beantragen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
