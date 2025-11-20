<?php
/**
 * Template: Testimonials
 */
$bgColor = $settings['backgroundColor'] ?? '#f9fafb';
$textColor = $settings['textColor'] ?? '#1f2937';
$title = $settings['title'] ?? 'Cosa dicono i nostri clienti';
$subtitle = $settings['subtitle'] ?? '';
$columns = $settings['columns'] ?? 3;

// Testimonials
$testimonials = $settings['testimonials'] ?? [
    [
        'name' => 'Maria Rossi',
        'role' => 'CEO, TechCorp',
        'image' => '',
        'text' => 'Un prodotto eccezionale che ha trasformato il modo in cui lavoriamo. Altamente raccomandato!',
        'rating' => 5
    ],
    [
        'name' => 'Giuseppe Verdi',
        'role' => 'Marketing Manager',
        'image' => '',
        'text' => 'Servizio clienti impeccabile e risultati oltre le aspettative. Continuate così!',
        'rating' => 5
    ],
    [
        'name' => 'Anna Bianchi',
        'role' => 'Freelancer',
        'image' => '',
        'text' => 'Finalmente uno strumento facile da usare e potente. Ha semplificato tutto il mio workflow.',
        'rating' => 4
    ]
];

// Column mapping
$colMapping = [
    1 => ['bootstrap' => '12', 'tailwind' => '1', 'bulma' => '12'],
    2 => ['bootstrap' => '6', 'tailwind' => '2', 'bulma' => '6'],
    3 => ['bootstrap' => '4', 'tailwind' => '3', 'bulma' => '4']
];
$cols = $colMapping[$columns] ?? $colMapping[3];

// Star rating helper
function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating ? '★' : '☆';
    }
    return $stars;
}

switch ($uiLibrary) {
    case 'bootstrap':
        ?>
        <section class="py-5" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold"><?= htmlspecialchars($title) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="text-muted"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <div class="row g-4">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-<?= $cols['bootstrap'] ?>">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="text-warning mb-3"><?= renderStars($testimonial['rating'] ?? 5) ?></div>
                                    <p class="card-text mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
                                    <div class="d-flex align-items-center">
                                        <?php if ($testimonial['image']): ?>
                                            <img src="<?= htmlspecialchars($testimonial['image']) ?>"
                                                 class="rounded-circle me-3"
                                                 style="width: 48px; height: 48px; object-fit: cover;"
                                                 alt="<?= htmlspecialchars($testimonial['name']) ?>">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                 style="width: 48px; height: 48px; font-weight: bold;">
                                                <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($testimonial['name']) ?></strong>
                                            <div class="text-muted small"><?= htmlspecialchars($testimonial['role']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        break;

    case 'bulma':
        ?>
        <section class="section" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <div class="has-text-centered mb-5">
                    <h2 class="title"><?= htmlspecialchars($title) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="subtitle has-text-grey"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <div class="columns is-multiline">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="column is-<?= $cols['bulma'] ?>">
                            <div class="card">
                                <div class="card-content">
                                    <div class="has-text-warning mb-3"><?= renderStars($testimonial['rating'] ?? 5) ?></div>
                                    <p class="mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
                                    <div class="media">
                                        <div class="media-left">
                                            <?php if ($testimonial['image']): ?>
                                                <figure class="image is-48x48">
                                                    <img class="is-rounded" src="<?= htmlspecialchars($testimonial['image']) ?>" alt="">
                                                </figure>
                                            <?php else: ?>
                                                <div class="has-background-primary has-text-white is-flex is-align-items-center is-justify-content-center"
                                                     style="width: 48px; height: 48px; border-radius: 50%; font-weight: bold;">
                                                    <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="media-content">
                                            <p class="title is-6"><?= htmlspecialchars($testimonial['name']) ?></p>
                                            <p class="subtitle is-7 has-text-grey"><?= htmlspecialchars($testimonial['role']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        break;

    case 'shadcn':
        ?>
        <section class="py-12" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold tracking-tight"><?= htmlspecialchars($title) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="text-muted-foreground mt-2"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <div class="grid gap-6 md:grid-cols-<?= $cols['tailwind'] ?>">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="rounded-lg border bg-card p-6 shadow-sm">
                            <div class="text-yellow-500 mb-3"><?= renderStars($testimonial['rating'] ?? 5) ?></div>
                            <p class="text-sm text-muted-foreground mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
                            <div class="flex items-center">
                                <?php if ($testimonial['image']): ?>
                                    <img src="<?= htmlspecialchars($testimonial['image']) ?>"
                                         class="rounded-full mr-3 w-10 h-10 object-cover"
                                         alt="">
                                <?php else: ?>
                                    <div class="rounded-full bg-primary text-primary-foreground flex items-center justify-center mr-3 w-10 h-10 font-bold text-sm">
                                        <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-semibold text-sm"><?= htmlspecialchars($testimonial['name']) ?></div>
                                    <div class="text-xs text-muted-foreground"><?= htmlspecialchars($testimonial['role']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        break;

    default: // tailwind
        ?>
        <section class="py-12" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold"><?= htmlspecialchars($title) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="text-gray-600 mt-2"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <div class="grid gap-6 md:grid-cols-<?= $cols['tailwind'] ?>">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="bg-white rounded-lg p-6 shadow-sm">
                            <div class="text-yellow-500 mb-3"><?= renderStars($testimonial['rating'] ?? 5) ?></div>
                            <p class="text-gray-600 mb-4">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
                            <div class="flex items-center">
                                <?php if ($testimonial['image']): ?>
                                    <img src="<?= htmlspecialchars($testimonial['image']) ?>"
                                         class="rounded-full mr-3 w-10 h-10 object-cover"
                                         alt="">
                                <?php else: ?>
                                    <div class="rounded-full bg-blue-600 text-white flex items-center justify-center mr-3 w-10 h-10 font-bold text-sm">
                                        <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-semibold text-sm"><?= htmlspecialchars($testimonial['name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($testimonial['role']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        break;
}
?>
