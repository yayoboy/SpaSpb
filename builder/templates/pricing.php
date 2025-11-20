<?php
/**
 * Template: Pricing Tables
 */
$bgColor = $settings['backgroundColor'] ?? '#ffffff';
$textColor = $settings['textColor'] ?? '#1f2937';
$title = $settings['title'] ?? 'Scegli il tuo piano';
$subtitle = $settings['subtitle'] ?? 'Prezzi semplici e trasparenti per tutti';

// Pricing plans
$plans = $settings['plans'] ?? [
    [
        'name' => 'Starter',
        'price' => '9',
        'period' => '/mese',
        'description' => 'Perfetto per iniziare',
        'features' => ['5 progetti', '10GB storage', 'Supporto email', 'API access'],
        'cta' => 'Inizia gratis',
        'ctaUrl' => '#',
        'highlighted' => false
    ],
    [
        'name' => 'Professional',
        'price' => '29',
        'period' => '/mese',
        'description' => 'Per professionisti e team',
        'features' => ['Progetti illimitati', '100GB storage', 'Supporto prioritario', 'API access', 'Analytics avanzati', 'Integrazioni'],
        'cta' => 'Prova gratuita',
        'ctaUrl' => '#',
        'highlighted' => true
    ],
    [
        'name' => 'Enterprise',
        'price' => '99',
        'period' => '/mese',
        'description' => 'Per grandi organizzazioni',
        'features' => ['Tutto in Professional', 'Storage illimitato', 'Account manager dedicato', 'SLA garantito', 'On-premise disponibile'],
        'cta' => 'Contattaci',
        'ctaUrl' => '#',
        'highlighted' => false
    ]
];

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
                <div class="row g-4 justify-content-center">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-lg-4">
                            <div class="card h-100 <?= $plan['highlighted'] ? 'border-primary shadow' : 'border-0 shadow-sm' ?>">
                                <?php if ($plan['highlighted']): ?>
                                    <div class="card-header bg-primary text-white text-center py-2">
                                        <small class="fw-bold">PIU' POPOLARE</small>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold"><?= htmlspecialchars($plan['name']) ?></h5>
                                    <p class="text-muted small"><?= htmlspecialchars($plan['description']) ?></p>
                                    <div class="my-4">
                                        <span class="display-4 fw-bold">€<?= htmlspecialchars($plan['price']) ?></span>
                                        <span class="text-muted"><?= htmlspecialchars($plan['period']) ?></span>
                                    </div>
                                    <ul class="list-unstyled mb-4">
                                        <?php foreach ($plan['features'] as $feature): ?>
                                            <li class="mb-2">
                                                <span class="text-success me-2">✓</span>
                                                <?= htmlspecialchars($feature) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?= htmlspecialchars($plan['ctaUrl']) ?>"
                                       class="btn <?= $plan['highlighted'] ? 'btn-primary' : 'btn-outline-primary' ?> w-100">
                                        <?= htmlspecialchars($plan['cta']) ?>
                                    </a>
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
                <div class="columns is-centered">
                    <?php foreach ($plans as $plan): ?>
                        <div class="column is-4">
                            <div class="card <?= $plan['highlighted'] ? 'has-background-primary-light' : '' ?>">
                                <?php if ($plan['highlighted']): ?>
                                    <div class="card-header has-background-primary">
                                        <p class="card-header-title has-text-white is-centered">PIU' POPOLARE</p>
                                    </div>
                                <?php endif; ?>
                                <div class="card-content">
                                    <p class="title is-5"><?= htmlspecialchars($plan['name']) ?></p>
                                    <p class="subtitle is-7 has-text-grey"><?= htmlspecialchars($plan['description']) ?></p>
                                    <p class="title is-2 my-4">
                                        €<?= htmlspecialchars($plan['price']) ?>
                                        <span class="subtitle is-6 has-text-grey"><?= htmlspecialchars($plan['period']) ?></span>
                                    </p>
                                    <div class="content">
                                        <ul style="list-style: none; padding: 0;">
                                            <?php foreach ($plan['features'] as $feature): ?>
                                                <li class="mb-2">
                                                    <span class="has-text-success mr-2">✓</span>
                                                    <?= htmlspecialchars($feature) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <a href="<?= htmlspecialchars($plan['ctaUrl']) ?>"
                                       class="button is-fullwidth <?= $plan['highlighted'] ? 'is-primary' : 'is-primary is-outlined' ?>">
                                        <?= htmlspecialchars($plan['cta']) ?>
                                    </a>
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
                <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                    <?php foreach ($plans as $plan): ?>
                        <div class="rounded-lg border <?= $plan['highlighted'] ? 'border-primary shadow-lg relative' : 'bg-card' ?> p-6">
                            <?php if ($plan['highlighted']): ?>
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <span class="bg-primary text-primary-foreground text-xs font-bold px-3 py-1 rounded-full">
                                        PIU' POPOLARE
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="mb-4">
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($plan['name']) ?></h3>
                                <p class="text-sm text-muted-foreground"><?= htmlspecialchars($plan['description']) ?></p>
                            </div>
                            <div class="mb-6">
                                <span class="text-4xl font-bold">€<?= htmlspecialchars($plan['price']) ?></span>
                                <span class="text-muted-foreground"><?= htmlspecialchars($plan['period']) ?></span>
                            </div>
                            <ul class="space-y-3 mb-6">
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li class="flex items-center text-sm">
                                        <span class="text-primary mr-2">✓</span>
                                        <?= htmlspecialchars($feature) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= htmlspecialchars($plan['ctaUrl']) ?>"
                               class="inline-flex w-full items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors <?= $plan['highlighted'] ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground' ?>">
                                <?= htmlspecialchars($plan['cta']) ?>
                            </a>
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
                <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                    <?php foreach ($plans as $plan): ?>
                        <div class="rounded-lg border <?= $plan['highlighted'] ? 'border-blue-600 shadow-lg relative' : 'border-gray-200 bg-white' ?> p-6">
                            <?php if ($plan['highlighted']): ?>
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        PIU' POPOLARE
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="mb-4">
                                <h3 class="font-bold text-lg"><?= htmlspecialchars($plan['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($plan['description']) ?></p>
                            </div>
                            <div class="mb-6">
                                <span class="text-4xl font-bold">€<?= htmlspecialchars($plan['price']) ?></span>
                                <span class="text-gray-500"><?= htmlspecialchars($plan['period']) ?></span>
                            </div>
                            <ul class="space-y-3 mb-6">
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li class="flex items-center text-sm">
                                        <span class="text-green-500 mr-2">✓</span>
                                        <?= htmlspecialchars($feature) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= htmlspecialchars($plan['ctaUrl']) ?>"
                               class="block w-full text-center rounded-md px-4 py-2 text-sm font-medium transition-colors <?= $plan['highlighted'] ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border border-gray-300 hover:bg-gray-50' ?>">
                                <?= htmlspecialchars($plan['cta']) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        break;
}
?>
