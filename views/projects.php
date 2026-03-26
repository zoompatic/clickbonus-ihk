<!-- Diese Seite zeigt alle importierten Projekte in einer Tabelle an.
Es ist wie ein Projektkatalog, wo man alle laufenden Arbeiten sieht. -->
<div class="card bg-white border-top border-primary border-4 p-2 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="text-primary mb-1 text-uppercase fw-bold">Importierte Projekte</h2>
                <span class="text-muted small">Hier siehst du alle Projekte, die aus ClickUp synchronisiert wurden.</span>
            </div>
            <a href="?action=sync" class="btn btn-outline-primary fw-bold text-uppercase">ClickUp Import</a>
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
                                        Mitarbeiter Zuweisen
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-5 text-center text-muted">
                                Keine Projekte gefunden. Bitte synchronisiere ClickUp!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>