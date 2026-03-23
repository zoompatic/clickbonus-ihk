<!-- Diese Seite zeigt alle importierten Projekte in einer Tabelle an.
Es ist wie ein Projektkatalog, wo man alle laufenden Arbeiten sieht. -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="color: var(--clr-primary); margin-bottom: 5px;">Importierte Projekte</h2>
            <span style="color: var(--clr-text-muted); font-size: 0.9rem;">Hier siehst du alle Projekte, die aus ClickUp synchronisiert wurden.</span>
        </div>
        <!-- Button zum Synchronisieren neuer Projekte von ClickUp. -->
        <!-- Wie der Knopf an einer frischen Kaffeemaschine: Einmal drücken und die neuesten Daten werden aus dem Internet hereingepumpt. -->
        <a href="?action=sync" class="btn btn-outline">ClickUp Import</a>
    </div>

    <!-- Tabelle mit Projektinformationen. -->
    <!-- Wie das Inhaltsverzeichnis eines großen Aktenordners, in dem jedes Projekt aufgelistet ist. -->
    <div class="table-responsive">
        <table class="table-monolith">
            <thead>
                <tr>
                    <th>ClickUp ID</th>
                    <th>Projektname</th>
                    <th>Status</th>
                    <th>Letzter Sync</th>
                    <th style="text-align: right;">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td data-label="ClickUp ID" style="color: var(--clr-text-muted); font-size: 0.9rem;">
                                <strong>#<?php echo htmlspecialchars($project['clickup_task_id']); ?></strong>
                            </td>
                            <td data-label="Projektname">
                                <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                            </td>
                            <td data-label="Status">
                                <span style="background: var(--clr-bg); padding: 4px 8px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; border-left: 3px solid #ccc; white-space: nowrap;">
                                    <?php echo htmlspecialchars($project['clickup_status']); ?>
                                </span>
                            </td>
                            <td data-label="Letzter Sync" style="font-size: 0.85rem; color: var(--clr-text-muted);">
                                <?php echo date('d.m.Y H:i', strtotime($project['last_sync_at'])); ?>
                            </td>
                            <td data-label="Aktion" style="text-align: right;">
                                <!-- Link zur Detailseite des Projekts, um Mitarbeiter zuzuweisen. -->
                                <a href="?action=assign&project_id=<?php echo $project['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">
                                    Mitarbeiter Zuweisen
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 40px; text-align: center; color: var(--clr-text-muted);">
                            Keine Projekte gefunden. Bitte synchronisiere ClickUp!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>