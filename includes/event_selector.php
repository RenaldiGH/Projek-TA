<?php

?>
<form method="GET" class="filter-bar">
    <div class="filter-group">
        <label for="event_id">Pilih Event</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()">
            <?php foreach ($events as $event): ?>
                <option value="<?= (int) $event['id'] ?>" <?= (int) $event_id === (int) $event['id'] ? 'selected' : '' ?>>
                    <?= h($event['nama_event']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label for="q">&nbsp;</label>
        <input
            type="text"
            name="q"
            id="q"
            placeholder="<?= h($search_label ?? 'Cari...') ?>"
            value="<?= h($search_value ?? '') ?>"
        >
    </div>
</form>