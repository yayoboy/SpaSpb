<?php
/**
 * Template: Features Block
 */
$features = $settings['features'] ?? [];

$classes = match($uiLibrary) {
    'bootstrap' => 'features-block py-5',
    'tailwind' => 'features-block py-16',
    'shadcn' => 'features-block py-16',
    'bulma' => 'section',
    default => 'features-block'
};
?>

<section class="<?= $classes ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-5">
            <?= $content ?>
        </div>

        <?php if ($uiLibrary === 'bootstrap'): ?>
            <div class="row g-4">
                <?php foreach ($features as $feature): ?>
                    <div class="col-md-4">
                        <div class="card h-100 text-center p-4 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="feature-icon mb-3" style="font-size: 3rem;">
                                    <?= $feature['icon'] ?? '⭐' ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($feature['title'] ?? '') ?></h5>
                                <p class="card-text"><?= htmlspecialchars($feature['description'] ?? '') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($uiLibrary === 'bulma'): ?>
            <div class="columns is-multiline">
                <?php foreach ($features as $feature): ?>
                    <div class="column is-4">
                        <div class="box has-text-centered">
                            <div class="feature-icon mb-3" style="font-size: 3rem;">
                                <?= $feature['icon'] ?? '⭐' ?>
                            </div>
                            <h5 class="title is-5"><?= htmlspecialchars($feature['title'] ?? '') ?></h5>
                            <p><?= htmlspecialchars($feature['description'] ?? '') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($features as $feature): ?>
                    <div class="feature-card text-center p-6 bg-white rounded-lg shadow-sm">
                        <div class="feature-icon mb-4 text-6xl">
                            <?= $feature['icon'] ?? '⭐' ?>
                        </div>
                        <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($feature['title'] ?? '') ?></h3>
                        <p class="text-gray-600"><?= htmlspecialchars($feature['description'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
