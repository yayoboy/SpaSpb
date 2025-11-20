<?php
/**
 * Template: Image Block
 */
$image = $settings['image'] ?? '';
$alt = $settings['alt'] ?? 'Immagine';
$width = $settings['width'] ?? '100%';
$align = $settings['align'] ?? 'center';

// Mapping allineamento per ogni libreria UI
$alignMapping = [
    'left' => [
        'bootstrap' => 'text-start',
        'tailwind' => 'text-left',
        'shadcn' => 'text-left',
        'bulma' => 'has-text-left'
    ],
    'center' => [
        'bootstrap' => 'text-center',
        'tailwind' => 'text-center',
        'shadcn' => 'text-center',
        'bulma' => 'has-text-centered'
    ],
    'right' => [
        'bootstrap' => 'text-end',
        'tailwind' => 'text-right',
        'shadcn' => 'text-right',
        'bulma' => 'has-text-right'
    ]
];

$alignClass = $alignMapping[$align][$uiLibrary] ?? $alignMapping['center'][$uiLibrary];

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
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" class="img-fluid rounded" style="max-width: <?= htmlspecialchars($width) ?>;">
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($uiLibrary === 'bulma'): ?>
        <div class="container">
            <div class="<?= $alignClass ?>">
                <figure class="image is-inline-block">
                    <?php if ($image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" style="max-width: <?= htmlspecialchars($width) ?>;">
                    <?php endif; ?>
                </figure>
            </div>
        </div>
    <?php elseif ($uiLibrary === 'shadcn'): ?>
        <div class="container mx-auto px-4">
            <div class="<?= $alignClass ?>">
                <?php if ($image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" class="inline-block max-w-full h-auto rounded-md shadow-sm" style="max-width: <?= htmlspecialchars($width) ?>;">
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Tailwind -->
        <div class="container mx-auto px-4">
            <div class="<?= $alignClass ?>">
                <?php if ($image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" class="inline-block max-w-full h-auto rounded-lg" style="max-width: <?= htmlspecialchars($width) ?>;">
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
