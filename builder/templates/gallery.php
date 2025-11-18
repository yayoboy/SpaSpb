<?php
/**
 * Template: Gallery Block
 */
$images = $settings['images'] ?? [];
$columns = $settings['columns'] ?? 3;
$gap = $settings['gap'] ?? '20px';

$classes = match($uiLibrary) {
    'bootstrap' => 'gallery-block py-5',
    'tailwind' => 'gallery-block py-12',
    'shadcn' => 'gallery-block py-12',
    'bulma' => 'section',
    default => 'gallery-block'
};
?>

<section class="<?= $classes ?>">
    <div class="container mx-auto px-4">
        <?php if ($uiLibrary === 'bootstrap'): ?>
            <div class="row g-<?= intval($gap) / 5 ?>">
                <?php foreach ($images as $img): ?>
                    <div class="col-md-<?= 12 / $columns ?>">
                        <img src="<?= $img ?>" class="img-fluid rounded" alt="Gallery image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($uiLibrary === 'bulma'): ?>
            <div class="columns is-multiline">
                <?php foreach ($images as $img): ?>
                    <div class="column is-<?= 12 / $columns ?>">
                        <figure class="image">
                            <img src="<?= $img ?>" alt="Gallery image">
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-<?= $columns ?> gap-<?= intval($gap) / 4 ?>">
                <?php foreach ($images as $img): ?>
                    <div>
                        <img src="<?= $img ?>" class="w-full h-auto rounded-lg" alt="Gallery image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
