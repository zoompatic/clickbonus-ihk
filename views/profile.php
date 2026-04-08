<?php // views/profile.php ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card bg-white mt-4 shadow-sm">
            <div class="card-body p-4">
                <h2 class="text-primary mb-1 text-uppercase fw-bold">Mein Profil</h2>
                <p class="text-muted small mb-4">Hier können Sie Ihr persönliches Passwort für den Login ändern.</p>

                <form method="POST" action="?action=update_profile_password" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Altes Passwort <span class="text-danger">*</span></label>
                        <input type="password" name="old_password" class="form-control bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Neues Passwort <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Neues Passwort (Wiederholung) <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold text-uppercase">Passwort ändern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
