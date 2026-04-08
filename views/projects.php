<?php
// views/projects.php
use App\Models\Role;
$isITManager = ($_SESSION['role_id'] == Role::IT_MANAGER);
?>
<div class="card bg-white">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <?php if ($isITManager): ?>
                    <h2 class="text-primary mb-1 text-uppercase fw-bold">Importierte Projekte</h2>
                    <span class="text-muted small">Hier siehst du alle Projekte, die aus ClickUp synchronisiert
                        wurden.</span>
                <?php else: ?>
                    <h2 class="text-primary mb-1 text-uppercase fw-bold">Meine Projekte</h2>
                    <span class="text-muted small">Hier siehst du alle Projekte, die dir zugewiesen wurden.</span>
                <?php endif; ?>
            </div>

            <?php if ($isITManager): ?>
                <a href="?action=sync" class="btn btn-outline-primary fw-bold text-uppercase">ClickUp Import</a>
            <?php endif; ?>
        </div>

        <?php
        $uniqueStatuses = [];
        if (!empty($projects)) {
            foreach ($projects as $p) {
                if (!in_array($p['clickup_status'], $uniqueStatuses)) {
                    $uniqueStatuses[] = $p['clickup_status'];
                }
            }
            sort($uniqueStatuses);
        }
        ?>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">🔍</span>
                    <input type="text" id="projectSearch" class="form-control border-start-0 ps-0"
                        placeholder="Suche (Name oder ID)...">
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <select id="statusFilter" class="form-select">
                    <option value="">-- Alle Status --</option>
                    <?php foreach ($uniqueStatuses as $s): ?>
                        <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
                    <?php endforeach; ?>
                </select>
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
                            <tr class="project-row" data-id="<?php echo htmlspecialchars($project['clickup_task_id']); ?>"
                                data-name="<?php echo htmlspecialchars($project['name']); ?>"
                                data-status="<?php echo htmlspecialchars($project['clickup_status']); ?>">
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
                                    <a href="?action=assign&project_id=<?php echo $project['id']; ?>"
                                        class="btn btn-primary btn-sm fw-bold">
                                        <?php echo $isITManager ? 'Mitarbeiter Zuweisen' : 'Detail / Prämien'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="emptyRow">
                            <td colspan="5" class="p-5 text-center text-muted">
                                <?php echo $isITManager ? 'Keine Projekte gefunden. Bitte synchronisiere ClickUp!' : 'Keine Projekte zugewiesen.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('projectSearch');
        const statusSelect = document.getElementById('statusFilter');
        const tableRows = document.querySelectorAll('.project-row');

        function filterProjects() {
            if (!searchInput || !statusSelect) return;

            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusSelect.value.toLowerCase();

            tableRows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const id = row.getAttribute('data-id').toLowerCase();
                const status = row.getAttribute('data-status').toLowerCase();

                const matchesSearch = name.includes(searchTerm) || id.includes(searchTerm);
                const matchesStatus = (selectedStatus === '') || (status === selectedStatus);

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterProjects);
        if (statusSelect) statusSelect.addEventListener('change', filterProjects);
    });
</script>