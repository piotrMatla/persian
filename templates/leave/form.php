<?php
/**
 * @var callable $e
 * @var string   $csrfField
 * @var array    $types  list<array{value:int,label:string}>
 * @var string   $employeeNumber
 */
?>
<div class="card">
    <h1>Nowy wniosek urlopowy</h1>
    <form method="post" action="leave/submit">
        <?= $csrfField ?>

        <label for="target">Numer ewidencyjny pracownika</label>
        <input type="text" id="target" name="target" value="<?= $e($employeeNumber) ?>" required>

        <label for="type">Rodzaj wniosku</label>
        <select id="type" name="type">
            <?php foreach ($types as $t): ?>
                <option value="<?= $e($t['value']) ?>"><?= $e($t['label']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="start">Początek</label>
        <input type="date" id="start" name="start" required>

        <label for="end">Koniec</label>
        <input type="date" id="end" name="end" required>

        <p style="margin-top:16px;"><button type="submit">Zapisz wniosek</button></p>
    </form>
</div>
