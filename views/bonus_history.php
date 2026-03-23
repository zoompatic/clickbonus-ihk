<div class="modal fade" id="modal<?php echo $b['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 0; border-left: 10px solid var(--m-red);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">GENEHMIGUNGSVERLAUF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 border-bottom pb-2">
                    <small class="text-muted text-uppercase d-block">Mitarbeiter & Projekt</small>
                    <strong><?php echo htmlspecialchars($b['last_name'] . ', ' . $b['first_name']); ?></strong><br>
                    <span class="text-danger fw-bold"><?php echo htmlspecialchars($b['project_name']); ?></span>
                </div>

                <div class="timeline" style="border-left: 2px solid #eee; padding-left: 20px; margin-left: 10px;">
                    <?php foreach ($b['history'] as $h): ?>
                        <div class="mb-4 position-relative">
                            <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--m-green);"></div>
                            <div class="small fw-bold text-uppercase" style="font-size: 10px; color: #888;">
                                <?php echo date('d.m.Y H:i', strtotime($h['created_at'])); ?> - <?php echo htmlspecialchars($h['status_name']); ?>
                            </div>
                            <div class="fw-bold"><?php echo htmlspecialchars($h['first_name'] . ' ' . $h['last_name']); ?> <small>(<?php echo htmlspecialchars($h['role_name']); ?>)</small></div>
                            <?php if (!empty($h['comment'])): ?>
                                <div class="p-2 bg-light mt-1 italic small" style="border-radius: 4px;">"<?php echo htmlspecialchars($h['comment']); ?>"</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>