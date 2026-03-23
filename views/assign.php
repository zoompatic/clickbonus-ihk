<!-- Diese Seite zeigt Details zu einem Projekt und erlaubt das Zuweisen von Mitarbeitern sowie das Beantragen von Prämien.
Es ist wie ein Projekt-Dashboard, wo man das Team managt und Belohnungen verteilt. -->
<div class="card">
    <div class="form-section-header">
        <a href="?action=<?php echo ($_SESSION['role_id'] == 4) ? 'my_projects' : 'projects'; ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem; margin-bottom: 1rem;">&larr; ZURÜCK</a>
        <h1 class="text-danger">Projekt: <?php echo htmlspecialchars($project['name']); ?></h1>
        <div style="display: flex; gap: 20px;" class="text-muted">
            <span><strong>Status:</strong> <?php echo htmlspecialchars($project['clickup_status']); ?></span>
            <span><strong>Beschreibung:</strong> <?php echo htmlspecialchars($project['description'] ?: '-'); ?></span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        
        <?php if (in_array($_SESSION['role_id'], [1, 2, 4])): ?>
            <div class="form-container-light" style="margin-bottom: 0;">
                <h3 class="form-section-title">Mitarbeiter zuweisen</h3>
                <!-- Formular, um einen Mitarbeiter ins Boot zu holen. -->
                <!-- Das ist so, als würde man jemanden zur WhatsApp-Gruppe des Projekts hinzufügen. -->
                <form method="POST" action="?action=assign_user">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Mitarbeiter auswählen</label>
                        <select name="user_id" required class="form-control">
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['last_name'] . ', ' . $user['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success" style="width: 100%;">+ ZUWEISEN</button>
                </form>
            </div>
        <?php endif; ?>

        <div>
            <h3 class="form-section-title" style="border-bottom: 2px solid #eee; padding-bottom: 0.5rem;">Projekt-Team</h3>
            <!-- Liste aller Personen, die bereits an diesem Projekt arbeiten. -->
            <!-- Hier kann der Vorgesetzte jedem direkt einen "Bonus-Antrag" in den Briefkasten werfen. -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($assignedUsers as $au): ?>
                    <div style="border: 1px solid #eee; border-left: 4px solid var(--clr-primary); padding: 1rem; background: var(--clr-surface);">
                        <div style="margin-bottom: 1rem;">
                            <strong>👤 <?php echo htmlspecialchars($au['first_name'] . ' ' . $au['last_name']); ?></strong>
                        </div>
                        
                        <form method="POST" action="?action=store_bonus" style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="assignment_id" value="<?php echo $au['assignment_id']; ?>">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            
                            <input type="number" name="amount" step="0.01" placeholder="Betrag €" required class="form-control" style="width: 100px;">
                            <input type="text" name="comment" placeholder="Grund..." required class="form-control" style="flex: 1;">
                            <button type="submit" class="btn btn-primary">BEANTRAGEN</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>