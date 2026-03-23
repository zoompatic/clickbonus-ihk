<!-- Diese Seite zeigt alle genehmigten Prämien für die HR-Abteilung zur Auszahlung.
Es ist wie eine Gehaltsliste, die zur Buchhaltung geht. -->
<div class="card">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="color: var(--clr-primary); margin-bottom: 5px;">HR-Auszahlungsliste</h2>
            <span style="color: var(--clr-text-muted); font-size: 0.9rem;">Final genehmigte Prämien für die Buchhaltung</span>
        </div>
        <!-- Button zum Drucken der Liste. -->
        <button onclick="window.print();" class="btn btn-outline" style="color: #333; border-color: #333;">🖨️ Drucken / PDF</button>
    </div>

    <!-- Filter für Datum und Gruppierung. -->
    <!-- Wie die Sortierknöpfe in einer Registrierkasse: "Zeige mir nur den Mai" oder "Fasse alle Beträge pro Person zusammen". -->
    <form method="GET" action="index.php" class="filter-bar no-print">
        <input type="hidden" name="action" value="hr_list">
        
        <div class="filter-group">
            <label>Von Datum:</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" onchange="this.form.submit()">
        </div>
        
        <div class="filter-group">
            <label>Bis Datum:</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>" onchange="this.form.submit()">
        </div>

        <div class="filter-group" style="margin-left: auto;">
            <label>Ansicht</label>
            <div class="toggle-wrapper">
                <span style="font-size: 0.85rem; font-weight: bold;">Nach Mitarbeiter gruppieren</span>
                <label class="toggle-switch">
                    <input type="checkbox" name="group_by_employee" value="1" <?php echo (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1') ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
    </form>

    <!-- Tabelle mit den genehmigten Prämien. -->
    <!-- Das finale Kassenbuch: Hier stehen nur Beträge, die zuvor zwingend vom Chef durchgewinkt (Grün) wurden. -->
    <div class="table-responsive">
        <table class="table-monolith">
            <thead>
                <tr>
                    <th>Zahlungsempfänger / Projekt</th>
                    <th>Datum</th>
                    <th style="text-align: right;">Betrag</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                if (!empty($bonuses)): 
                    // --- GRUPPIERTE ANSICHT ---
                    if (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1'):
                        foreach ($bonuses as $name => $data): 
                            $grandTotal += $data['total']; ?>
                            <tr class="group-row">
                                <td colspan="2">👤 <?php echo htmlspecialchars($name); ?></td>
                                <td style="text-align: right; font-size: 1.1rem; font-weight: bold;">
                                    <?php echo number_format($data['total'], 2, ',', '.'); ?> €
                                </td>
                            </tr>
                            <?php foreach ($data['items'] as $item): ?>
                                <tr style="font-size: 0.9rem;">
                                    <td style="padding-left: 30px;">└ <?php echo htmlspecialchars($item['project_name']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></td>
                                    <td style="text-align: right;"><?php echo number_format($item['amount'], 2, ',', '.'); ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                    <?php 
                    // --- NORMALE LISTE (Chronologisch) ---
                    else: 
                        foreach ($bonuses as $b): 
                            $grandTotal += $b['amount']; ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($b['last_name'] . ', ' . $b['first_name']); ?></strong><br>
                                    <span style="font-size: 0.85rem;"><?php echo htmlspecialchars($b['project_name']); ?></span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($b['created_at'])); ?></td>
                                <td style="text-align: right; font-weight: bold;">
                                    <?php echo number_format($b['amount'], 2, ',', '.'); ?> €
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Gesamtsumme am Ende. -->
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right; padding: 15px;">GESAMTAUSZAHLUNG:</td>
                        <td style="text-align: right; padding: 15px;"><?php echo number_format($grandTotal, 2, ',', '.'); ?> €</td>
                    </tr>

                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #888;">
                            Keine final genehmigten Prämien im gewählten Zeitraum gefunden.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Unterschriftsfelder für den Druck. -->
    <div class="print-signatures">
        <div class="sig-box">Erstellt (System / <?php echo date('d.m.Y'); ?>)</div>
        <div class="sig-box">Geprüft (Personalabteilung)</div>
        <div class="sig-box">Freigabe (Buchhaltung)</div>
    </div>
</div>