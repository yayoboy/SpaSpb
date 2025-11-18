<?php
/**
 * Template: Image Block
 */
$image = $settings['image'] ?? '';
$alt = $settings['alt'] ?? 'Immagine';
$width = $settings['width'] ?? '100%';
$align = $settings['align'] ?? 'center';

$alignClass = match($align) {
    'left' => 'text-left',
    'right' => 'text-right',
    default => 'text-center'
};

$classes = match($uiLibrary) {
    'bootstrap' => 'image-block py-4',
    'tailwind' => 'image-block py-8',
    'shadcn' => 'image-block py-8',
    'bulma' => 'section',
    default => 'image-block'
};
?>

<section class="<?= $classes ?>">
    <?php if ($uiLibrary === 'bootstrap'): ?>
        <div class="container">
            <div class="<?= $alignClass ?>">
                <?php if ($image): ?>
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($alt) ?>" class="img-fluid" style="max-width: <?= $width ?>;">
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($uiLibrary === 'bulma'): ?>
        <div class="container">
            <figure class="image">
                <?php if ($image): ?>
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($alt) ?>">
                <?php endif; ?>
            </figure>
        </div>
    <?php else: ?>
        <div class="container mx-auto px-4 <?= $alignClass ?>">
            <?php if ($image): ?>
                <img src="<?= $image ?>" alt="<?= htmlspecialchars($alt) ?>" class="max-w-full h-auto" style="max-width: <?= $width ?>;">
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
