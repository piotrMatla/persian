<?php
/**
 * @var callable $e      helper escapujący
 * @var string   $title
 * @var string   $content
 * @var string|null $flash
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'eWnioski') ?></title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f5f7; color: #1a1a1a; }
        header { background: #0b3d6b; color: #fff; padding: 12px 20px; }
        main { max-width: 960px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 16px; }
        label { display: block; margin: 10px 0 4px; font-weight: 600; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #0b3d6b; color: #fff; border: 0; padding: 10px 18px; border-radius: 4px; cursor: pointer; }
        .flash { padding: 12px; border-radius: 4px; margin-bottom: 16px; }
        .flash.ok { background: #e6f4ea; color: #137333; }
        .flash.err { background: #fce8e6; color: #c5221f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; font-size: 14px; }
    </style>
</head>
<body>
<header><strong>eWnioski</strong></header>
<main>
    <?php if (!empty($flash)): ?>
        <div class="flash <?= $e($flashType ?? 'ok') ?>"><?= $e($flash) ?></div>
    <?php endif; ?>
    <?= $content /* already-escaped partial */ ?>
</main>
</body>
</html>
