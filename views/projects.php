<?php
// views/projects.php
// Diese gemeinsame View zeigt Projekte an – sowohl für IT-Manager als auch für Projektmanager.
// Der Anzeigemodus wird über die Variable $viewModus aus index.php gesteuert:
//   'manager'    → IT-Manager-Ansicht mit ClickUp-Import-Button und "Mitarbeiter Zuweisen"
//   'my_projects' → Projektmanager-Ansicht mit "Detail / Prämien"
?>
<div class="card bg-white">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <?php if ($viewModus === 'manager'): ?>
                    <h2 class="text-primary mb-1 text-uppercase fw-bold">Importierte Projekte</h2>
                    <span class="text-muted small">Hier siehst du alle Projekte, die aus ClickUp synchronisiert wurden.</span>
                <?php else: ?>
                    <h2 class="text-primary mb-1 text-uppercase fw-bold">Meine Projekte</h2>
                    <span class="text-muted small">Hier siehst du alle Projekte, die dir zugewiesen wurden.</span>
                <?php endif; ?>
            </div>

            <?php if ($viewModus === 'manager'): ?>
                <!-- Import-Button ist nur für den IT-Manager sichtbar. -->
                <a href="?action=sync" class="btn btn-outline-primary fw-bold text-uppercase">ClickUp Import</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle m-0">
                <thead>
                    <tr>
                        <th>ClickUp ID</th>
                        <th>Projektname</th>
                        <th>Status</th>
                        <th>Letzter Sync</th>
                        <th class="text-end">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td class="text-muted small">
                                    <strong>#<?php echo htmlspecialchars($project['clickup_task_id']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary border-start border-3 border-dark text-uppercase">
                                        <?php echo htmlspecialchars($project['clickup_status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('d.m.Y H:i', strtotime($project['last_sync_at'])); ?>
                                </td>
                                <td class="text-end">
                                    <a href="?action=assign&project_id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm fw-bold">
                                        <?php echo $viewModus === 'manager' ? 'Mitarbeiter Zuweisen' : 'Detail / Prämien'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-5 text-center text-muted">
                                <?php echo $viewModus === 'manager' ? 'Keine Projekte gefunden. Bitte synchronisiere ClickUp!' : 'Keine Projekte zugewiesen.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>