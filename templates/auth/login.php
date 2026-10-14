<?php
/**
 * @var callable $e
 * @var string   $csrfField
 */
?>
<div class="card">
    <h1>Logowanie</h1>
    <form method="post" action="login">
        <?= $csrfField ?>
        <label for="login">Numer ewidencyjny</label>
        <input type="text" id="login" name="login" autocomplete="username" required>

        <label for="password">Hasło</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <p style="margin-top:16px;"><button type="submit">Zaloguj</button></p>
    </form>
</div>
