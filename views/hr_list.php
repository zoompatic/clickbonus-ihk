<?php
// views/hr_list.php
// Diese Seite zeigt alle genehmigten Prämien für die HR-Abteilung zur Auszahlung.
?>
<div class="card bg-white">
    <div class="card-body p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 d-print-none">
            <div>
                <h2 class="text-primary mb-1 text-uppercase fw-bold">HR-Auszahlungsliste</h2>
                <span class="text-muted small">Final genehmigte Prämien für die Buchhaltung</span>
            </div>
            <button onclick="window.print();" class="btn btn-outline-dark fw-bold text-uppercase">🖨️ Drucken / PDF</button>
        </div>

        <form method="GET" action="index.php" class="row g-3 align-items-end mb-4 pb-4 border-bottom d-print-none">
            <input type="hidden" name="action" value="hr_list">
            
            <div class="col-12 col-md-auto">
                <label class="form-label text-muted small text-uppercase fw-bold mb-1">Von Datum:</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" onchange="this.form.submit()">
            </div>
            
            <div class="col-12 col-md-auto">
                <label class="form-label text-muted small text-uppercase fw-bold mb-1">Bis Datum:</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>" onchange="this.form.submit()">
            </div>

            <div class="col-12 col-md-auto ms-md-auto">
                <div class="form-check form-switch form-check-switch border p-2 rounded-0 px-3 ps-5 bg-light">
                    <input class="form-check-input" type="checkbox" role="switch" id="groupSwitch" name="group_by_employee" value="1" <?php echo(isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1') ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label class="form-check-label fw-bold small text-uppercase ms-2" for="groupSwitch">Nach Mitarbeiter gruppieren</label>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle m-0">
                <thead>
                    <tr>
                        <th>Zahlungsempfänger / Projekt</th>
                        <th>Datum</th>
                        <th class="text-end">Betrag</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if (!empty($allBonuses)):
    if (isset($_GET['group_by_employee']) && $_GET['group_by_employee'] == '1'):
        foreach ($allBonuses as $name => $dataPackage): ?>
                                <tr class="table-secondary border-dark">
                                    <td colspan="2" class="fw-bold fs-6">👤 <?php echo htmlspecialchars($name); ?></td>
                                    <td class="text-end fs-5 fw-bold text-nowrap">
                                        <?php echo number_format($dataPackage['total'], 2, ',', '.'); ?> €
                                    </td>
                                </tr>
                                <?php foreach ($dataPackage['items'] as $item): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small">└ <?php echo htmlspecialchars($item['project_name']); ?></td>
                                        <td class="text-muted small"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></td>
                                        <td class="text-end text-muted"><?php echo number_format($item['amount'], 2, ',', '.'); ?> €</td>
                                    </tr>
                                <?php
            endforeach; ?>
                            <?php
        endforeach; ?>

                        <?php
    else:
        foreach ($allBonuses as $bonus): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($bonus['last_name'] . ', ' . $bonus['first_name']); ?></strong><br>
                                        <span class="text-muted small"><?php echo htmlspecialchars($bonus['project_name']); ?></span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('d.m.Y', strtotime($bonus['created_at'])); ?></td>
                                    <td class="text-end fw-bold text-nowrap">
                                        <?php echo number_format($bonus['amount'], 2, ',', '.'); ?> €
                                    </td>
                                </tr>
                            <?php
        endforeach; ?>
                        <?php
    endif; ?>



                    <?php
else: ?>
                        <tr>
                            <td colspan="3" class="text-center p-5 text-muted">
                                Keine final genehmigten Prämien im gewählten Zeitraum gefunden.
                            </td>
                        </tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>