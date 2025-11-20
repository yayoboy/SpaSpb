<?php
/**
 * Template: Header/Navbar
 */
$bgColor = $settings['backgroundColor'] ?? '#ffffff';
$textColor = $settings['textColor'] ?? '#1f2937';
$logoText = $settings['logoText'] ?? 'Brand';
$logoImage = $settings['logoImage'] ?? '';
$sticky = $settings['sticky'] ?? false;
$transparent = $settings['transparent'] ?? false;

// Menu items (JSON array)
$menuItems = $settings['menuItems'] ?? [
    ['label' => 'Home', 'url' => '#'],
    ['label' => 'Features', 'url' => '#features'],
    ['label' => 'Pricing', 'url' => '#pricing'],
    ['label' => 'Contact', 'url' => '#contact']
];

$ctaText = $settings['ctaText'] ?? '';
$ctaUrl = $settings['ctaUrl'] ?? '#';

$stickyClass = $sticky ? 'position-sticky top-0' : '';
$bgStyle = $transparent ? 'background: transparent;' : "background-color: {$bgColor};";

switch ($uiLibrary) {
    case 'bootstrap':
        $stickyClass = $sticky ? 'sticky-top' : '';
        ?>
        <nav class="navbar navbar-expand-lg <?= $stickyClass ?>" style="<?= $bgStyle ?> color: <?= $textColor ?>;">
            <div class="container">
                <a class="navbar-brand" href="#" style="color: <?= $textColor ?>;">
                    <?php if ($logoImage): ?>
                        <img src="<?= htmlspecialchars($logoImage) ?>" alt="<?= htmlspecialchars($logoText) ?>" height="40">
                    <?php else: ?>
                        <?= htmlspecialchars($logoText) ?>
                    <?php endif; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php foreach ($menuItems as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars($item['url']) ?>" style="color: <?= $textColor ?>;">
                                    <?= htmlspecialchars($item['label']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($ctaText): ?>
                            <li class="nav-item ms-2">
                                <a class="btn btn-primary" href="<?= htmlspecialchars($ctaUrl) ?>">
                                    <?= htmlspecialchars($ctaText) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
        break;

    case 'bulma':
        $stickyClass = $sticky ? 'is-fixed-top' : '';
        ?>
        <nav class="navbar <?= $stickyClass ?>" role="navigation" style="<?= $bgStyle ?>">
            <div class="container">
                <div class="navbar-brand">
                    <a class="navbar-item" href="#" style="color: <?= $textColor ?>;">
                        <?php if ($logoImage): ?>
                            <img src="<?= htmlspecialchars($logoImage) ?>" alt="<?= htmlspecialchars($logoText) ?>">
                        <?php else: ?>
                            <strong><?= htmlspecialchars($logoText) ?></strong>
                        <?php endif; ?>
                    </a>
                    <a role="button" class="navbar-burger" data-target="navMenu">
                        <span></span><span></span><span></span>
                    </a>
                </div>
                <div id="navMenu" class="navbar-menu">
                    <div class="navbar-end">
                        <?php foreach ($menuItems as $item): ?>
                            <a class="navbar-item" href="<?= htmlspecialchars($item['url']) ?>" style="color: <?= $textColor ?>;">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($ctaText): ?>
                            <div class="navbar-item">
                                <a class="button is-primary" href="<?= htmlspecialchars($ctaUrl) ?>">
                                    <?= htmlspecialchars($ctaText) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
        <?php
        break;

    case 'shadcn':
        $stickyClass = $sticky ? 'sticky top-0 z-50' : '';
        ?>
        <header class="<?= $stickyClass ?> border-b" style="<?= $bgStyle ?>">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <a href="#" class="flex items-center space-x-2" style="color: <?= $textColor ?>;">
                        <?php if ($logoImage): ?>
                            <img src="<?= htmlspecialchars($logoImage) ?>" alt="<?= htmlspecialchars($logoText) ?>" class="h-8">
                        <?php else: ?>
                            <span class="font-bold text-xl"><?= htmlspecialchars($logoText) ?></span>
                        <?php endif; ?>
                    </a>
                    <nav class="hidden md:flex items-center space-x-6">
                        <?php foreach ($menuItems as $item): ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>"
                               class="text-sm font-medium transition-colors hover:text-primary"
                               style="color: <?= $textColor ?>;">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($ctaText): ?>
                            <a href="<?= htmlspecialchars($ctaUrl) ?>"
                               class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">
                                <?= htmlspecialchars($ctaText) ?>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </header>
        <?php
        break;

    default: // tailwind
        $stickyClass = $sticky ? 'sticky top-0 z-50' : '';
        ?>
        <header class="<?= $stickyClass ?> shadow-sm" style="<?= $bgStyle ?>">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <a href="#" class="flex items-center" style="color: <?= $textColor ?>;">
                        <?php if ($logoImage): ?>
                            <img src="<?= htmlspecialchars($logoImage) ?>" alt="<?= htmlspecialchars($logoText) ?>" class="h-8">
                        <?php else: ?>
                            <span class="font-bold text-xl"><?= htmlspecialchars($logoText) ?></span>
                        <?php endif; ?>
                    </a>
                    <nav class="hidden md:flex items-center space-x-6">
                        <?php foreach ($menuItems as $item): ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>"
                               class="text-sm font-medium hover:text-blue-600 transition-colors"
                               style="color: <?= $textColor ?>;">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($ctaText): ?>
                            <a href="<?= htmlspecialchars($ctaUrl) ?>"
                               class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                <?= htmlspecialchars($ctaText) ?>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </header>
        <?php
        break;
}
?>
