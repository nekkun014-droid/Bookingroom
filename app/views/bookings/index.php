<section>
    <h2>My Bookings</h2>
    <?php if (empty($bookings)): ?>
        <p>No bookings yet.</p>
    <?php else: ?>
        <div style="margin-bottom:.5rem">
            <a class="btn btn--secondary" href="?action=export_bookings_csv">Export CSV</a>
        </div>
        <table class="responsive-table">
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
                        <th>Actions</th>
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
                    <td><?php echo htmlspecialchars($b['status']); ?></td>
                    <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                        <td>
                            <?php if ($b['status'] === 'pending'): ?>
                                <form method="post" action="?action=booking_action" style="display:inline">
                                    <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                    <input type="hidden" name="op" value="approve">
                                    <button type="submit" class="btn btn--primary" onclick="return confirm('Approve this booking?')">Approve</button>
                                </form>
                                <form method="post" action="?action=booking_action" style="display:inline;margin-left:.4rem">
                                    <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                    <input type="hidden" name="op" value="reject">
                                    <button type="submit" class="btn btn--secondary" onclick="return confirm('Reject this booking?')">Reject</button>
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
    <?php endif; ?>
</section>
