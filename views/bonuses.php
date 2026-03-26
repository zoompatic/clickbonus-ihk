<!-- Diese Seite zeigt alle wartenden Prämienanträge zur Genehmigung oder Ablehnung.
Es ist wie ein Schreibtisch mit Papieren, die bearbeitet werden müssen. -->
<div class="card bg-white border-top border-primary border-4 p-2 mb-4">
    <div class="card-body p-4">
        <h2 class="text-primary mb-1 text-uppercase fw-bold">Wartende Freigaben</h2>
        <p class="text-muted mb-4 small">Hier landen alle beantragten Prämien zur Prüfung und Freigabe.</p>

        <div class="table-responsive">
            <table class="table table-bordered align-middle m-0">
                <thead>
                    <tr>
                        <th class="text-nowrap text-muted">Datum</th>
                        <th>Empfänger</th>
                        <th>Einreicher</th>
                        <th>Projekt</th>
                        <th>Grund</th>
                        <th class="text-end text-nowrap">Betrag</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 140px;">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allBonuses)): ?>
                        <?php foreach ($allBonuses as $bonus): ?>
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    <?php echo date('d.m.Y', strtotime($bonus['created_at'])); ?><br>
                                    <?php echo date('H:i', strtotime($bonus['created_at'])); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($bonus['first_name'] . ' ' . $bonus['last_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold">
                                        <?php echo htmlspecialchars(($bonus['req_first_name'] ?? 'System') . ' ' . ($bonus['req_last_name'] ?? '')); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($bonus['project_name']); ?></strong>
                                </td>
                                <td class="small">
                                    <?php if ($bonus['comment']): ?>
                                        <span class="text-muted fst-italic"><?php echo htmlspecialchars($bonus['comment']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold fs-5 text-nowrap">
                                    <?php echo number_format($bonus['amount'], 2, ',', '.'); ?> €
                                </td>
                                <td class="text-end">
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if ($bonus['current_status_id'] == 1) $badgeClass = 'bg-warning text-dark';
                                        if ($bonus['current_status_id'] == 2) $badgeClass = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> text-uppercase rounded-1 px-2 py-1">
                                        <?php echo htmlspecialchars($bonus['current_status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if (in_array($bonus['current_status_id'], [1, 2])): ?>
                                        <div class="d-flex flex-column gap-2 align-items-end">
                                            <form method="POST" action="?action=update_bonus_status" class="w-100" style="max-width: 140px;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="bonus_id" value="<?php echo $bonus['bonus_id']; ?>">
                                                <input type="hidden" name="action_type" value="approve">
                                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">FREIGEBEN</button>
                                            </form>

                                        <form method="POST" action="?action=update_bonus_status" class="w-100 mt-1" style="max-width: 140px;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="bonus_id" value="<?php echo $bonus['bonus_id']; ?>">
                                            <input type="hidden" name="action_type" value="reject">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="comment" class="form-control" placeholder="Grund..." required>
                                                <button type="submit" class="btn btn-primary" title="Ablehnen">❌</button>
                                            </div>
                                        </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fw-bold text-uppercase">Abgeschlossen</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-5 text-center text-muted">Es liegen aktuell keine Freigabe-Anträge vor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>