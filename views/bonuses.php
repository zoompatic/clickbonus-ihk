<!-- Diese Seite zeigt alle wartenden Prämienanträge zur Genehmigung oder Ablehnung.
Es ist wie ein Schreibtisch mit Papieren, die bearbeitet werden müssen. -->
<div class="card">
    <h2 style="color: var(--clr-primary); margin-bottom: 5px; text-transform: uppercase;">Wartende Freigaben</h2>
    <p style="margin-bottom: 20px; color: var(--clr-text-muted);">Hier landen alle beantragten Prämien zur Prüfung und Freigabe.</p>

    <!-- Tabelle mit allen wartenden Prämien. -->
    <!-- Das ist der Stapel auf dem Schreibtisch: Ganz oben liegen die neuesten Anträge. -->
    <div class="table-responsive">
        <table class="table-monolith">
            <thead>
                <tr>
                    <th style="white-space: nowrap;">Datum</th>
                    <th>Empfänger</th>
                    <th>Einreicher</th>
                    <th>Projekt</th>
                    <th>Grund</th>
                    <th style="text-align: right; white-space: nowrap;">Betrag</th>
                    <th style="text-align: right;">Status</th>
                    <th style="text-align: right; width: 140px;">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($allBonuses)): ?>
                    <?php foreach ($allBonuses as $bonus): ?>
                        <tr>
                            <td data-label="Datum" style="font-size: 0.85rem; color: var(--clr-text-muted); white-space: nowrap;">
                                <?php echo date('d.m.Y', strtotime($bonus['created_at'])); ?><br>
                                <?php echo date('H:i', strtotime($bonus['created_at'])); ?>
                            </td>
                            <td data-label="Empfänger">
                                <strong><?php echo htmlspecialchars($bonus['first_name'] . ' ' . $bonus['last_name']); ?></strong>
                            </td>
                            <td data-label="Einreicher">
                                <span style="color: var(--clr-primary); font-weight: 600;">
                                    <?php echo htmlspecialchars(($bonus['req_first_name'] ?? 'System') . ' ' . ($bonus['req_last_name'] ?? '')); ?>
                                </span>
                            </td>
                            
                            <td data-label="Projekt" style="font-size: 0.9rem;">
                                <strong><?php echo htmlspecialchars($bonus['project_name']); ?></strong>
                            </td>
                            
                            <td data-label="Grund" style="font-size: 0.85rem;">
                                <?php if ($bonus['comment']): ?>
                                    <span style="color: var(--clr-text-muted); font-style: normal;"> <?php echo htmlspecialchars($bonus['comment']); ?></span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td data-label="Betrag" style="text-align: right; font-weight: 800; font-size: 1.1rem; color: var(--clr-text-main); white-space: nowrap;">
                                <?php echo number_format($bonus['amount'], 2, ',', '.'); ?> €
                            </td>
                            
                            <td data-label="Status" style="text-align: right;">
                                <?php 
                                    $statusBg = '#eee';
                                    $statusColor = '#333';
                                    if ($bonus['current_status_id'] == 1) { $statusBg = '#fff3cd'; $statusColor = '#856404'; }
                                    if ($bonus['current_status_id'] == 2) { $statusBg = '#cce5ff'; $statusColor = '#004085'; }
                                ?>
                                <span style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>; padding: 4px 10px; border-radius: 0px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($bonus['current_status']); ?>
                                </span>
                            </td>
                            
                            <!-- Buttons zum Genehmigen oder Ablehnen der Prämie. -->
                            <!-- Das sind die beiden Stempel des Chefs: Ein grüner (Freigeben) und ein roter (Ablehnen mit Begründung). -->
                            <td data-label="Aktion" style="text-align: right;">
                                <?php if (in_array($bonus['current_status_id'], [1, 2])): ?>
                                    <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                                        <form method="POST" action="?action=update_bonus_status" style="margin: 0; width: 100%; max-width: 140px;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="bonus_id" value="<?php echo $bonus['bonus_id']; ?>">
                                            <input type="hidden" name="action_type" value="approve">
                                            <button type="submit" class="btn btn-success" style="width: 100%; padding: 6px; font-size: 0.75rem;">FREIGEBEN</button>
                                        </form>

                                        <form method="POST" action="?action=update_bonus_status" style="display: flex; gap: 4px; margin: 0; width: 100%; max-width: 140px;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="bonus_id" value="<?php echo $bonus['bonus_id']; ?>">
                                            <input type="hidden" name="action_type" value="reject">
                                            <input type="text" name="comment" placeholder="Grund..." required style="flex: 1; min-width: 0; padding: 5px; border: 1px solid #ccc; font-family: inherit; font-size: 0.75rem; border-radius: 0; outline: none;">
                                            <button type="submit" class="btn btn-primary" style="padding: 5px 8px;" title="Ablehnen">❌</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #ccc; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Abgeschlossen</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: var(--clr-text-muted);">Es liegen aktuell keine Freigabe-Anträge vor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>