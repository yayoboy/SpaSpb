<?php
/**
 * Template: Gallery Block
 */
$images = $settings['images'] ?? [];
$columns = intval($settings['columns'] ?? 3);
$gap = $settings['gap'] ?? 'medium';

// Limita colonne a valori validi (che dividono 12)
$validColumns = [1, 2, 3, 4, 6];
if (!in_array($columns, $validColumns)) {
    $columns = 3; // default
}

// Mapping colonne per ogni libreria UI
$columnMapping = [
    1 => ['bootstrap' => '12', 'tailwind' => '1', 'bulma' => '12'],
    2 => ['bootstrap' => '6',  'tailwind' => '2', 'bulma' => '6'],
    3 => ['bootstrap' => '4',  'tailwind' => '3', 'bulma' => '4'],
    4 => ['bootstrap' => '3',  'tailwind' => '4', 'bulma' => '3'],
    6 => ['bootstrap' => '2',  'tailwind' => '6', 'bulma' => '2'],
];

// Mapping gap per ogni libreria UI
$gapMapping = [
    'small'  => ['bootstrap' => '2', 'tailwind' => '2', 'bulma' => '1'],
    'medium' => ['bootstrap' => '3', 'tailwind' => '4', 'bulma' => '3'],
    'large'  => ['bootstrap' => '4', 'tailwind' => '6', 'bulma' => '5'],
];

// Normalizza gap a chiave valida
if (is_numeric($gap)) {
    $gapValue = intval($gap);
    if ($gapValue <= 10) $gap = 'small';
    elseif ($gapValue <= 20) $gap = 'medium';
    else $gap = 'large';
}
if (!isset($gapMapping[$gap])) {
    $gap = 'medium';
}

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
            <div class="row g-<?= $gapMapping[$gap]['bootstrap'] ?>">
                <?php foreach ($images as $img): ?>
                    <div class="col-md-<?= $columnMapping[$columns]['bootstrap'] ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded" alt="Gallery image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($uiLibrary === 'bulma'): ?>
            <div class="columns is-multiline is-variable is-<?= $gapMapping[$gap]['bulma'] ?>">
                <?php foreach ($images as $img): ?>
                    <div class="column is-<?= $columnMapping[$columns]['bulma'] ?>">
                        <figure class="image">
                            <img src="<?= htmlspecialchars($img) ?>" alt="Gallery image">
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($uiLibrary === 'shadcn'): ?>
            <div class="grid grid-cols-1 md:grid-cols-<?= $columnMapping[$columns]['tailwind'] ?> gap-<?= $gapMapping[$gap]['tailwind'] ?>">
                <?php foreach ($images as $img): ?>
                    <div class="overflow-hidden rounded-md">
                        <img src="<?= htmlspecialchars($img) ?>" class="w-full h-auto object-cover transition-transform hover:scale-105" alt="Gallery image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Tailwind -->
            <div class="grid grid-cols-1 md:grid-cols-<?= $columnMapping[$columns]['tailwind'] ?> gap-<?= $gapMapping[$gap]['tailwind'] ?>">
                <?php foreach ($images as $img): ?>
                    <div>
                        <img src="<?= htmlspecialchars($img) ?>" class="w-full h-auto rounded-lg" alt="Gallery image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
