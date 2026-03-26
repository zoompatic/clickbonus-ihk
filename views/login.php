<!-- Diese Seite zeigt das Anmeldeformular an.
Es ist wie ein Türschild mit einem Schloss: Man gibt E-Mail und Passwort ein, um reinzukommen. -->
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card bg-white border-top border-primary border-4 p-3">
            <div class="card-body">
                <h3 class="text-center mb-4 text-uppercase fw-bold text-dark">Login</h3>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-0 border-0 text-center" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?action=login">
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small">E-Mail Adresse:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-muted small">Passwort:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-login w-100 text-uppercase fw-bold text-white">Einloggen</button>
                </form>
            </div>
        </div>
    </div>
</div>