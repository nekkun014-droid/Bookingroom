<section>
    <h2>My Bookings</h2>
    <?php if (empty($bookings)): ?>
        <p>No bookings yet.</p>
    <?php else: ?>
        <!-- Surface wrapper gives a white, readable background for the table -->
        <div class="surface" style="padding:.75rem 0.9rem;margin-bottom:.75rem;border-radius:10px;">
            <div class="table-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;gap:1rem;">
                <div class="table-title" style="font-weight:800;color:var(--accent-600);">
                    My Bookings <span style="font-weight:600;color:var(--muted);font-size:0.95rem">(<?php echo count($bookings); ?>)</span>
                </div>
                <div class="table-actions" style="text-align:right;">
                    <a class="btn btn--secondary" href="?action=export_bookings_csv" title="Export CSV">
                        <!-- simple download icon SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:.45rem"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Horizontal scroll wrapper for small screens -->
            <div class="table-wrap">
                <table class="responsive-table">
                    <caption class="sr-only">Daftar pemesanan ruangan</caption>
            <thead>
                <tr>
                    <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                        <th>User</th>
                    <?php endif; ?>
                    <th>Room</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                        <th class="actions">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                        <td><?php echo htmlspecialchars($b['user_name']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($b['end_time']); ?></td>
                    <td><span class="status-badge <?php echo htmlspecialchars($b['status']); ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                    <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                        <td class="actions">
                            <?php if ($b['status'] === 'pending'): ?>
                                <form method="post" action="?action=booking_action" style="display:inline">
                                    <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                    <input type="hidden" name="op" value="approve">
                                    <button type="submit" class="btn btn--primary btn--small" onclick="return confirm('Approve this booking?')" aria-label="Approve booking <?php echo (int)$b['id']; ?>">Approve</button>
                                </form>
                                <form method="post" action="?action=booking_action" style="display:inline;margin-left:.4rem">
                                    <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                    <input type="hidden" name="op" value="reject">
                                    <button type="submit" class="btn btn--secondary btn--small" onclick="return confirm('Reject this booking?')" aria-label="Reject booking <?php echo (int)$b['id']; ?>">Reject</button>
                                </form>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
                </table>
            </div><!-- .table-wrap -->
        </div><!-- .surface -->
    <?php endif; ?>
</section>
