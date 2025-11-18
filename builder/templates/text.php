<?php
/**
 * Template: Text Block
 */
$bgColor = $settings['backgroundColor'] ?? '#ffffff';
$textColor = $settings['textColor'] ?? '#1f2937';
$align = $settings['align'] ?? 'left';
$padding = $settings['padding'] ?? '40px';

$style = "background-color: {$bgColor}; color: {$textColor}; text-align: {$align}; padding: {$padding};";

$classes = match($uiLibrary) {
    'bootstrap' => 'text-block py-5',
    'tailwind' => 'text-block py-12',
    'shadcn' => 'text-block py-12',
    'bulma' => 'section',
    default => 'text-block'
};
?>

<section class="<?= $classes ?>" style="<?= $style ?>">
    <?php if ($uiLibrary === 'bootstrap'): ?>
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <?= $content ?>
                </div>
            </div>
        </div>
    <?php elseif ($uiLibrary === 'bulma'): ?>
        <div class="container">
            <div class="content">
                <?= $content ?>
            </div>
        </div>
    <?php else: ?>
        <div class="container mx-auto px-4 max-w-4xl">
            <?= $content ?>
        </div>
    <?php endif; ?>
</section>
