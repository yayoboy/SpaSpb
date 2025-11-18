<?php
/**
 * Template: Hero Section
 */
$bgColor = $settings['backgroundColor'] ?? '#f3f4f6';
$textColor = $settings['textColor'] ?? '#1f2937';
$align = $settings['align'] ?? 'center';
$height = $settings['height'] ?? '500px';
$bgImage = $settings['backgroundImage'] ?? '';

$style = "background-color: {$bgColor}; color: {$textColor}; text-align: {$align}; min-height: {$height};";
if ($bgImage) {
    $style .= " background-image: url('{$bgImage}'); background-size: cover; background-position: center;";
}

// Classi per librerie UI
$classes = match($uiLibrary) {
    'bootstrap' => 'hero-section d-flex align-items-center justify-content-center',
    'tailwind' => 'hero-section flex items-center justify-center',
    'shadcn' => 'hero-section flex items-center justify-center',
    'bulma' => 'hero is-fullheight',
    default => 'hero-section'
};
?>

<section class="<?= $classes ?>" style="<?= $style ?>">
    <?php if ($uiLibrary === 'bootstrap'): ?>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-<?= $align ?>">
                    <?= $content ?>
                </div>
            </div>
        </div>
    <?php elseif ($uiLibrary === 'bulma'): ?>
        <div class="hero-body">
            <div class="container has-text-<?= $align ?>">
                <?= $content ?>
            </div>
        </div>
    <?php else: ?>
        <div class="container mx-auto px-4 py-20">
            <?= $content ?>
        </div>
    <?php endif; ?>
</section>
