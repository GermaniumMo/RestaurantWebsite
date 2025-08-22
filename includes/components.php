<?php

// Flash message display component
function render_flash_messages() {
    if (has_flash('success') || has_flash('error') || has_flash('warning') || has_flash('info')): ?>
        <div class="container-fluid" style="margin-top: 100px;">
            <div class="row">
                <div class="col-12">
                    <?php flash_show_all(); ?>
                </div>
            </div>
        </div>
    <?php endif;
}

// Feature card component
function render_feature_card($icon_svg, $title, $description) {
?>
    <div class="d-flex flex-column justify-content-center align-items-center">
        <?= $icon_svg ?>
        <h3 class="text-center"><?= htmlspecialchars($title) ?></h3>
        <p class="text-center"><?= htmlspecialchars($description) ?></p>
    </div>
<?php
}

// Menu item card component
function render_menu_card($image_src, $title, $description, $price, $alt_text = '') {
    $alt_text = $alt_text ?: $title . ' Image';
?>
    <div class="card" style="width: 24.666rem">
        <img src="<?= htmlspecialchars($image_src) ?>" class="card-img-top shadow" alt="<?= htmlspecialchars($alt_text) ?>" />
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($title) ?></h5>
            <p class="card-text"><?= htmlspecialchars($description) ?></p>
            <span>$<?= htmlspecialchars($price) ?></span>
        </div>
    </div>
<?php
}

// Section header component
function render_section_header($title, $subtitle = '') {
?>
    <div class="w-100 d-flex flex-column justify-content-center align-items-center gap-3">
        <h1><?= htmlspecialchars($title) ?></h1>
        <?php if ($subtitle): ?>
            <p><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
<?php
}

// Document head component
function render_document_head($title = 'Savoria', $additional_css = []) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/main.css" />
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css_file) ?>" />
    <?php endforeach; ?>
</head>
<body>
<?php
}
?>
