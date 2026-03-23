<div class="card" style="max-width: 400px; margin: 50px auto; text-align: center;">
    <h2>Login</h2>
    
    <?php if (!empty($error)): ?>
        <div style="background: #ffdddd; color: #d8000c; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="?action=login" style="text-align: left;">
        <div style="margin-bottom: 15px;">
            <label for="email">E-Mail Adresse:</label><br>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 20px;">
            <label for="password">Passwort:</label><br>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
        </div>
        <button type="submit" style="width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; cursor: pointer; font-size: 16px;">Einloggen</button>
    </form>
</div>