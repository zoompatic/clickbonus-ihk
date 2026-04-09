<?php // views/history.php ?>
<div class="card bg-white">
    <div class="card-body p-4">
        
        <div class="mb-4">
            <h2 class="text-primary mb-1 text-uppercase fw-bold">Audit-Log (Historie)</h2>
            <span class="text-muted small">Chronologische Übersicht aller Prämien-Aktivitäten im System.</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle m-0">
                <thead>
                    <tr>
                        <th class="text-nowrap text-muted">Datum / Uhrzeit</th>
                        <th>Aktion durch</th>
                        <th>Projekt / Empfänger</th>
                        <th class="text-end text-nowrap">Betrag</th>
                        <th class="text-center">Aktion / Status</th>
                        <th>Kommentar / Begründung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($auditLogs)): ?>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    <?php echo date('d.m.Y', strtotime($log['created_at'])); ?><br>
                                    <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold">
                                        <?php echo htmlspecialchars($log['actor_first'] . ' ' . $log['actor_last']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['target_last'] . ', ' . $log['target_first']); ?></strong><br>
                                    <span class="text-muted small"><?php echo htmlspecialchars($log['project_name']); ?></span>
                                </td>
                                <td class="text-end fw-bold text-nowrap">
                                    <?php echo number_format($log['amount'], 2, ',', '.'); ?> €
                                </td>
                                <td class="text-center">
                                    <?php
                                        $badgeClass = 'bg-secondary';
                                        $statusText = 'Unbekannt';
                                        if ($log['approval_status_id'] == 1) { // PENDING
                                            $badgeClass = 'bg-warning text-dark';
                                            $statusText = 'BEANTRAGT';
                                        } elseif ($log['approval_status_id'] == 2) { // APPROVED
                                            $badgeClass = 'bg-success';
                                            $statusText = 'GENEHMIGT';
                                        } elseif ($log['approval_status_id'] == 3) { // REJECTED
                                            $badgeClass = 'bg-danger';
                                            $statusText = 'ABGELEHNT';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> text-uppercase rounded-1 px-2 py-1">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="small">
                                    <?php
                                        $displayComment = $log['comment'];
                                        if ($log['approval_status_id'] == 1 && $log['comment'] === 'Prämie beantragt' && !empty($log['bonus_comment'])) {
                                            $displayComment = $log['bonus_comment'];
                                        }
                                    ?>
                                    <?php if ($displayComment): ?>
                                        <span class="fst-italic"><?php echo htmlspecialchars($displayComment); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-5 text-center text-muted">Bisher keine Einträge in der Historie.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
