<section>
    <h2>Rooms</h2>
    <p>Available rooms. Click "Book" to create a booking (must be logged in).</p>

    <form method="get" action="" class="search-form" style="margin-bottom:1rem">
        <input type="hidden" name="action" value="rooms">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q ?? ''); ?>" placeholder="Search rooms or location..." style="padding:.4rem .6rem;border:1px solid #ccc;border-radius:6px;width:60%;max-width:360px">
        <button type="submit" class="btn btn--secondary" style="margin-left:.5rem">Search</button>
    </form>

    <?php if (!empty($total)): ?>
        <p><small>Showing page <?php echo (int)($page ?? 1); ?> of <?php echo (int)$totalPages; ?> — <?php echo (int)$total; ?> rooms found.</small>
        <span style="margin-left:1rem"><a class="btn btn--secondary" href="?action=export_rooms_csv<?php echo $q ? '&q='.urlencode($q):''; ?>">Export CSV</a></span>
        </p>
    <?php endif; ?>

    <div class="room-grid">
        <?php foreach ($rooms as $r): ?>
            <article class="room-card">
                <h3><?php echo htmlspecialchars($r['name']); ?></h3>
                <p>Location: <?php echo htmlspecialchars($r['location']); ?></p>
                <p>Capacity: <?php echo htmlspecialchars($r['capacity']); ?></p>
                <button class="btn open-booking" data-room-id="<?php echo $r['id']; ?>">Book</button>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Pagination" style="margin-top:1rem">
            <?php
            $qsBase = [];
            if (!empty($q)) $qsBase['q'] = $q;
            // build helper to make links
            function pageLink($p, $qsBase) {
                $qs = $qsBase;
                $qs['page'] = $p;
                $qs['action'] = 'rooms';
                return '?' . http_build_query($qs);
            }
            $start = max(1, ($page - 2));
            $end = min($totalPages, ($page + 2));
            ?>
            <?php if ($page > 1): ?>
                <a class="btn btn--secondary" href="<?php echo pageLink($page-1, $qsBase); ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($p = $start; $p <= $end; $p++): ?>
                <?php if ($p == $page): ?>
                    <span class="btn" style="opacity:.8;margin:0 .25rem"><?php echo $p; ?></span>
                <?php else: ?>
                    <a class="btn btn--secondary" href="<?php echo pageLink($p, $qsBase); ?>" style="margin:0 .25rem"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="btn btn--secondary" href="<?php echo pageLink($page+1, $qsBase); ?>">Next &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <!-- booking modal -->
    <div id="bookingModal" class="modal" aria-hidden="true">
        <div class="modal-content">
            <button class="close">×</button>
            <h3>Book Room</h3>
            <form method="post" action="?action=create_booking" id="bookingForm">
                <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="room_id" id="room_id">
                            <div class="form-group">
                                <label for="timeslot_select">Timeslot (optional)</label>
                                <select id="timeslot_select" name="timeslot_id">
                                    <option value="">-- choose a timeslot --</option>
                                    <?php if (!empty($timeslots)): ?>
                                        <?php foreach ($timeslots as $ts): ?>
                                            <option value="<?php echo (int)$ts['id']; ?>" data-start="<?php echo htmlspecialchars($ts['start_time']); ?>" data-end="<?php echo htmlspecialchars($ts['end_time']); ?>"><?php echo htmlspecialchars($ts['name'] . ' (' . substr($ts['start_time'],0,5) . ' - ' . substr($ts['end_time'],0,5) . ')'); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="muted">Selecting a timeslot will autofill the start/end fields for today's date (you can edit before submitting).</small>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start</label>
                                <input id="start_time" name="start_time" type="datetime-local" required>
                            </div>
                            <div class="form-group">
                                <label for="end_time">End</label>
                                <input id="end_time" name="end_time" type="datetime-local" required>
                            </div>
                <button type="submit" class="btn">Request Booking</button>
            </form>
        </div>
    </div>
</section>
