<?php
/**
 * Template: Footer
 */
$bgColor = $settings['backgroundColor'] ?? '#1f2937';
$textColor = $settings['textColor'] ?? '#ffffff';
$companyName = $settings['companyName'] ?? 'Company Name';
$companyDescription = $settings['companyDescription'] ?? '';
$copyrightText = $settings['copyrightText'] ?? '© ' . date('Y') . ' All rights reserved.';

// Footer columns (JSON array)
$columns = $settings['columns'] ?? [
    [
        'title' => 'Product',
        'links' => [
            ['label' => 'Features', 'url' => '#features'],
            ['label' => 'Pricing', 'url' => '#pricing'],
            ['label' => 'FAQ', 'url' => '#faq']
        ]
    ],
    [
        'title' => 'Company',
        'links' => [
            ['label' => 'About', 'url' => '#about'],
            ['label' => 'Blog', 'url' => '#blog'],
            ['label' => 'Careers', 'url' => '#careers']
        ]
    ],
    [
        'title' => 'Support',
        'links' => [
            ['label' => 'Contact', 'url' => '#contact'],
            ['label' => 'Documentation', 'url' => '#docs'],
            ['label' => 'Privacy Policy', 'url' => '#privacy']
        ]
    ]
];

// Social links
$socialLinks = $settings['socialLinks'] ?? [
    ['platform' => 'twitter', 'url' => '#'],
    ['platform' => 'facebook', 'url' => '#'],
    ['platform' => 'linkedin', 'url' => '#'],
    ['platform' => 'instagram', 'url' => '#']
];

$socialIcons = [
    'twitter' => '𝕏',
    'facebook' => 'f',
    'linkedin' => 'in',
    'instagram' => '📷',
    'youtube' => '▶',
    'github' => '⌘'
];

switch ($uiLibrary) {
    case 'bootstrap':
        ?>
        <footer class="py-5" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h5 class="fw-bold mb-3"><?= htmlspecialchars($companyName) ?></h5>
                        <?php if ($companyDescription): ?>
                            <p class="text-muted"><?= htmlspecialchars($companyDescription) ?></p>
                        <?php endif; ?>
                        <div class="d-flex gap-3 mt-3">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= htmlspecialchars($social['url']) ?>"
                                   class="text-decoration-none"
                                   style="color: <?= $textColor ?>; opacity: 0.7;">
                                    <?= $socialIcons[$social['platform']] ?? $social['platform'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php foreach ($columns as $column): ?>
                        <div class="col-6 col-md-4 col-lg-2 mb-4 mb-lg-0">
                            <h6 class="fw-bold mb-3"><?= htmlspecialchars($column['title']) ?></h6>
                            <ul class="list-unstyled">
                                <?php foreach ($column['links'] as $link): ?>
                                    <li class="mb-2">
                                        <a href="<?= htmlspecialchars($link['url']) ?>"
                                           class="text-decoration-none"
                                           style="color: <?= $textColor ?>; opacity: 0.7;">
                                            <?= htmlspecialchars($link['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr class="my-4" style="border-color: <?= $textColor ?>; opacity: 0.2;">
                <div class="text-center" style="opacity: 0.7;">
                    <small><?= htmlspecialchars($copyrightText) ?></small>
                </div>
            </div>
        </footer>
        <?php
        break;

    case 'bulma':
        ?>
        <footer class="footer" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <div class="columns">
                    <div class="column is-4">
                        <h4 class="title is-5" style="color: <?= $textColor ?>;"><?= htmlspecialchars($companyName) ?></h4>
                        <?php if ($companyDescription): ?>
                            <p class="has-text-grey"><?= htmlspecialchars($companyDescription) ?></p>
                        <?php endif; ?>
                        <div class="buttons mt-3">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= htmlspecialchars($social['url']) ?>" class="button is-small is-dark">
                                    <?= $socialIcons[$social['platform']] ?? $social['platform'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php foreach ($columns as $column): ?>
                        <div class="column is-2">
                            <h6 class="title is-6" style="color: <?= $textColor ?>;"><?= htmlspecialchars($column['title']) ?></h6>
                            <ul style="list-style: none; margin: 0; padding: 0;">
                                <?php foreach ($column['links'] as $link): ?>
                                    <li class="mb-2">
                                        <a href="<?= htmlspecialchars($link['url']) ?>" style="color: <?= $textColor ?>; opacity: 0.7;">
                                            <?= htmlspecialchars($link['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr style="background-color: <?= $textColor ?>; opacity: 0.2;">
                <div class="has-text-centered" style="opacity: 0.7;">
                    <small><?= htmlspecialchars($copyrightText) ?></small>
                </div>
            </div>
        </footer>
        <?php
        break;

    case 'shadcn':
        ?>
        <footer class="border-t" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
                    <div class="col-span-2">
                        <h3 class="font-bold text-lg mb-4"><?= htmlspecialchars($companyName) ?></h3>
                        <?php if ($companyDescription): ?>
                            <p class="text-sm text-muted-foreground mb-4"><?= htmlspecialchars($companyDescription) ?></p>
                        <?php endif; ?>
                        <div class="flex space-x-4">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= htmlspecialchars($social['url']) ?>"
                                   class="text-muted-foreground hover:text-foreground transition-colors">
                                    <?= $socialIcons[$social['platform']] ?? $social['platform'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php foreach ($columns as $column): ?>
                        <div>
                            <h4 class="font-semibold mb-4"><?= htmlspecialchars($column['title']) ?></h4>
                            <ul class="space-y-2">
                                <?php foreach ($column['links'] as $link): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($link['url']) ?>"
                                           class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                                            <?= htmlspecialchars($link['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="border-t mt-8 pt-8 text-center text-sm text-muted-foreground">
                    <?= htmlspecialchars($copyrightText) ?>
                </div>
            </div>
        </footer>
        <?php
        break;

    default: // tailwind
        ?>
        <footer style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
                    <div class="col-span-2">
                        <h3 class="font-bold text-lg mb-4"><?= htmlspecialchars($companyName) ?></h3>
                        <?php if ($companyDescription): ?>
                            <p class="text-sm opacity-70 mb-4"><?= htmlspecialchars($companyDescription) ?></p>
                        <?php endif; ?>
                        <div class="flex space-x-4">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= htmlspecialchars($social['url']) ?>"
                                   class="opacity-70 hover:opacity-100 transition-opacity">
                                    <?= $socialIcons[$social['platform']] ?? $social['platform'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php foreach ($columns as $column): ?>
                        <div>
                            <h4 class="font-semibold mb-4"><?= htmlspecialchars($column['title']) ?></h4>
                            <ul class="space-y-2">
                                <?php foreach ($column['links'] as $link): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($link['url']) ?>"
                                           class="text-sm opacity-70 hover:opacity-100 transition-opacity">
                                            <?= htmlspecialchars($link['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="border-t border-white border-opacity-20 mt-8 pt-8 text-center text-sm opacity-70">
                    <?= htmlspecialchars($copyrightText) ?>
                </div>
            </div>
        </footer>
        <?php
        break;
}
?>
