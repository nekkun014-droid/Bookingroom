<?php
// admin timeslot management
?>
<section>
    <h2>Timeslots</h2>
    <p>Manage predefined timeslots used for quick booking ranges.</p>

    <?php if (!empty($timeslots)): ?>
        <div style="margin-bottom:.5rem">
            <a class="btn btn--secondary" href="?action=export_timeslots_csv">Export CSV</a>
        </div>
        <table class="responsive-table">
            <thead><tr><th>Name</th><th>Start</th><th>End</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($timeslots as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                    <td><?php echo htmlspecialchars($t['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($t['end_time']); ?></td>
                    <td>
                        <button type="button" class="btn btn--secondary edit-timeslot"
                                data-id="<?php echo (int)$t['id']; ?>"
                                data-name="<?php echo htmlspecialchars($t['name'], ENT_QUOTES); ?>"
                                data-start="<?php echo htmlspecialchars($t['start_time']); ?>"
                                data-end="<?php echo htmlspecialchars($t['end_time']); ?>">
                            Edit
                        </button>
                        <form method="post" action="?action=timeslot_delete" style="display:inline;margin-left:.5rem">
                            <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                            <button type="submit" class="btn btn--secondary" onclick="return confirm('Delete this timeslot?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No timeslots defined yet.</p>
    <?php endif; ?>

    <h3>Add Timeslot</h3>
    <form method="post" action="?action=timeslot_create" style="max-width:480px">
        <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
        <div class="form-group">
            <label for="ts_name">Name</label>
            <input id="ts_name" name="name" type="text" required>
        </div>
        <div class="form-group">
            <label for="ts_start">Start time</label>
            <input id="ts_start" name="start_time" type="time" required>
        </div>
        <div class="form-group">
            <label for="ts_end">End time</label>
            <input id="ts_end" name="end_time" type="time" required>
        </div>
        <button type="submit" class="btn btn--primary">Create Timeslot</button>
    </form>

</section>

<!-- Edit timeslot modal -->
<div id="timeslotModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <button class="close">×</button>
        <h3>Edit Timeslot</h3>
        <form method="post" action="?action=timeslot_update" id="timeslotEditForm">
            <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="id" id="ts_edit_id">
            <div class="form-group">
                <label for="ts_edit_name">Name</label>
                <input id="ts_edit_name" name="name" type="text" required>
            </div>
            <div class="form-group">
                <label for="ts_edit_start">Start time</label>
                <input id="ts_edit_start" name="start_time" type="time" required>
            </div>
            <div class="form-group">
                <label for="ts_edit_end">End time</label>
                <input id="ts_edit_end" name="end_time" type="time" required>
            </div>
            <button type="submit" class="btn btn--primary">Save changes</button>
        </form>
    </div>
</div>
