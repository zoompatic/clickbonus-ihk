<?php
// views/my_projects.php
// Diese Seite zeigt die Projekte, denen der eingeloggte Benutzer zugewiesen ist.
?>
<div class="card bg-white">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="text-primary mb-1 text-uppercase fw-bold">Meine Projekte</h2>
                <span class="text-muted small">Hier siehst du alle Projekte, die dir zugewiesen wurden.</span>
            </div>
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
                                        Detail / Prämien
                                    </a>
                                </td>
                            </tr>
                        <?php
    endforeach; ?>
                    <?php
else: ?>
                        <tr>
                            <td colspan="5" class="p-5 text-center text-muted">
                                Keine Projekte zugewiesen.
                            </td>
                        </tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
