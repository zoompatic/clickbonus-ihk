<?php
// views/assign.php 
?>
<div class="card bg-white">
    <div class="card-body p-4">
        <div class="mb-4">
            <a href="?action=projects" class="btn btn-outline-secondary btn-sm mb-3">&larr; ZURÜCK</a>
            <h1 class="text-primary mb-2 text-uppercase fw-bold">Projekt: <?php echo htmlspecialchars($project['name']); ?></h1>
            <div class="d-flex gap-4 text-muted">
                <span><strong>Status:</strong> <?php echo htmlspecialchars($project['clickup_status']); ?></span>
                <span><strong>Beschreibung:</strong> <?php echo htmlspecialchars($project['description'] ?: '-'); ?></span>
            </div>
        </div>

        <div class="row g-4">
            
            <?php if (in_array($_SESSION['role_id'], [1, 2, 4])): ?>
                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm border-top border-success border-4 h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-3 text-uppercase fw-bold text-muted border-bottom pb-2">Mitarbeiter zuweisen</h3>
                            <form method="POST" action="?action=assign_user">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Mitarbeiter auswählen</label>
                                    <select name="user_id" required class="form-select">
                                        <option value="">-- Bitte wählen --</option>
                                        <?php foreach ($allUsers as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['last_name'] . ', ' . $user['first_name'] . ' (' . $user['role_name'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success fw-bold w-100">+ ZUWEISEN</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12 col-lg-7">
                <h3 class="h5 mb-3 text-uppercase fw-bold border-bottom pb-2">Projekt-Team</h3>
                <div class="d-flex flex-column gap-3">
                    <?php if (empty($assignedUsers)): ?>
                        <div class="text-muted p-3 bg-light rounded">Noch keine Mitarbeiter zugewiesen.</div>
                    <?php endif; ?>
                    <?php foreach ($assignedUsers as $assignedEmployee): ?>
                        <div class="card shadow-sm border-start border-none border-4 bg-light">
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <strong class="fs-6">👤 <?php echo htmlspecialchars($assignedEmployee['first_name'] . ' ' . $assignedEmployee['last_name']); ?></strong>
                                </div>
                                
                                <form method="POST" action="?action=store_bonus" class="d-flex gap-2 flex-wrap align-items-end">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignedEmployee['assignment_id']; ?>">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    
                                    <div style="width: 120px;">
                                        <input type="number" name="amount" step="0.01" placeholder="Betrag €" required class="form-control">
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="text" name="comment" placeholder="Begründung für Prämie..." required class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold">BEANTRAGEN</button>
                                </form>

                                <?php $pastBonuses = \App\Models\Bonus::getForAssignment($assignedEmployee['assignment_id']); ?>
                                <?php if (!empty($pastBonuses)): ?>
                                    <div class="mt-3 pt-3 border-top">
                                        <span class="d-block small fw-bold text-uppercase text-muted mb-2">Bisherige Prämien in diesem Projekt:</span>
                                        <ul class="list-group list-group-flush mb-0">
                                            <?php foreach ($pastBonuses as $pastBonus): ?>
                                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-0 small">
                                                    <div>
                                                        <span class="fw-bold"><?php echo number_format($pastBonus['amount'], 2, ',', '.'); ?> €</span>
                                                        <span class="text-muted d-block" style="font-size: 0.75rem;"><?php echo htmlspecialchars($pastBonus['comment']); ?></span>
                                                    </div>
                                                    <?php
                                                        $badgeClass = 'bg-secondary';
                                                        if ($pastBonus['current_status'] == 'Beantragt') $badgeClass = 'bg-warning text-dark';
                                                        if ($pastBonus['current_status'] == 'Genehmigt') $badgeClass = 'bg-success';
                                                        if ($pastBonus['current_status'] == 'Abgelehnt') $badgeClass = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($pastBonus['current_status']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>