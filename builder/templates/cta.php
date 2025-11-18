<?php
/**
 * Template: Call to Action Block
 */
$bgColor = $settings['backgroundColor'] ?? '#3b82f6';
$textColor = $settings['textColor'] ?? '#ffffff';
$buttonText = $settings['buttonText'] ?? 'Inizia ora';
$buttonLink = $settings['buttonLink'] ?? '#';

$style = "background-color: {$bgColor}; color: {$textColor};";

$classes = match($uiLibrary) {
    'bootstrap' => 'cta-block py-5 text-center',
    'tailwind' => 'cta-block py-16 text-center',
    'shadcn' => 'cta-block py-16 text-center',
    'bulma' => 'section has-text-centered',
    default => 'cta-block text-center'
};

$buttonClasses = match($uiLibrary) {
    'bootstrap' => 'btn btn-light btn-lg mt-3',
    'tailwind' => 'inline-block mt-6 bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition',
    'shadcn' => 'inline-block mt-6 bg-white text-gray-900 px-8 py-3 rounded-md font-semibold hover:bg-gray-100 transition',
    'bulma' => 'button is-light is-large mt-4',
    default => 'cta-button'
};
?>

<section class="<?= $classes ?>" style="<?= $style ?>">
    <div class="container mx-auto px-4">
        <?php if ($uiLibrary === 'bootstrap'): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?= $content ?>
                    <a href="<?= htmlspecialchars($buttonLink) ?>" class="<?= $buttonClasses ?>">
                        <?= htmlspecialchars($buttonText) ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto">
                <?= $content ?>
                <a href="<?= htmlspecialchars($buttonLink) ?>" class="<?= $buttonClasses ?>">
                    <?= htmlspecialchars($buttonText) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
