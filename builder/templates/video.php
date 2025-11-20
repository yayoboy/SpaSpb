<?php
/**
 * Template: Video/Embed
 */
$bgColor = $settings['backgroundColor'] ?? '#ffffff';
$textColor = $settings['textColor'] ?? '#1f2937';
$videoUrl = $settings['videoUrl'] ?? '';
$videoType = $settings['videoType'] ?? 'youtube'; // youtube, vimeo, direct
$aspectRatio = $settings['aspectRatio'] ?? '16:9';
$autoplay = $settings['autoplay'] ?? false;
$muted = $settings['muted'] ?? false;
$loop = $settings['loop'] ?? false;
$title = $settings['title'] ?? '';
$subtitle = $settings['subtitle'] ?? '';

// Calculate padding for aspect ratio
$ratios = [
    '16:9' => '56.25%',
    '4:3' => '75%',
    '1:1' => '100%',
    '21:9' => '42.86%'
];
$paddingBottom = $ratios[$aspectRatio] ?? '56.25%';

// Extract video ID
$videoId = '';
if ($videoType === 'youtube' && preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $videoUrl, $matches)) {
    $videoId = $matches[1];
} elseif ($videoType === 'vimeo' && preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches)) {
    $videoId = $matches[1];
}

// Build embed URL
$embedUrl = '';
$params = [];
if ($autoplay) $params[] = 'autoplay=1';
if ($muted) $params[] = 'mute=1';
if ($loop) $params[] = 'loop=1';
$paramString = $params ? '?' . implode('&', $params) : '';

if ($videoType === 'youtube' && $videoId) {
    $embedUrl = "https://www.youtube.com/embed/{$videoId}{$paramString}";
} elseif ($videoType === 'vimeo' && $videoId) {
    $embedUrl = "https://player.vimeo.com/video/{$videoId}{$paramString}";
}

switch ($uiLibrary) {
    case 'bootstrap':
        ?>
        <section class="py-5" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <?php if ($title || $subtitle): ?>
                    <div class="text-center mb-4">
                        <?php if ($title): ?>
                            <h2 class="fw-bold"><?= htmlspecialchars($title) ?></h2>
                        <?php endif; ?>
                        <?php if ($subtitle): ?>
                            <p class="text-muted"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="ratio" style="--bs-aspect-ratio: <?= $paddingBottom ?>;">
                    <?php if ($videoType === 'direct'): ?>
                        <video controls <?= $autoplay ? 'autoplay' : '' ?> <?= $muted ? 'muted' : '' ?> <?= $loop ? 'loop' : '' ?>>
                            <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                        </video>
                    <?php elseif ($embedUrl): ?>
                        <iframe src="<?= htmlspecialchars($embedUrl) ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light">
                            <span class="text-muted">Inserisci URL video</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
        break;

    case 'bulma':
        ?>
        <section class="section" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container">
                <?php if ($title || $subtitle): ?>
                    <div class="has-text-centered mb-5">
                        <?php if ($title): ?>
                            <h2 class="title"><?= htmlspecialchars($title) ?></h2>
                        <?php endif; ?>
                        <?php if ($subtitle): ?>
                            <p class="subtitle has-text-grey"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <figure class="image" style="padding-bottom: <?= $paddingBottom ?>; position: relative; height: 0;">
                    <?php if ($videoType === 'direct'): ?>
                        <video controls <?= $autoplay ? 'autoplay' : '' ?> <?= $muted ? 'muted' : '' ?> <?= $loop ? 'loop' : '' ?>
                               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                            <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                        </video>
                    <?php elseif ($embedUrl): ?>
                        <iframe src="<?= htmlspecialchars($embedUrl) ?>"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                                frameborder="0"
                                allowfullscreen></iframe>
                    <?php else: ?>
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                            <span class="has-text-grey">Inserisci URL video</span>
                        </div>
                    <?php endif; ?>
                </figure>
            </div>
        </section>
        <?php
        break;

    case 'shadcn':
        ?>
        <section class="py-12" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4">
                <?php if ($title || $subtitle): ?>
                    <div class="text-center mb-8">
                        <?php if ($title): ?>
                            <h2 class="text-3xl font-bold tracking-tight"><?= htmlspecialchars($title) ?></h2>
                        <?php endif; ?>
                        <?php if ($subtitle): ?>
                            <p class="text-muted-foreground mt-2"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="relative rounded-lg overflow-hidden shadow-lg" style="padding-bottom: <?= $paddingBottom ?>;">
                    <?php if ($videoType === 'direct'): ?>
                        <video controls <?= $autoplay ? 'autoplay' : '' ?> <?= $muted ? 'muted' : '' ?> <?= $loop ? 'loop' : '' ?>
                               class="absolute inset-0 w-full h-full object-cover">
                            <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                        </video>
                    <?php elseif ($embedUrl): ?>
                        <iframe src="<?= htmlspecialchars($embedUrl) ?>"
                                class="absolute inset-0 w-full h-full"
                                frameborder="0"
                                allowfullscreen></iframe>
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center bg-muted">
                            <span class="text-muted-foreground">Inserisci URL video</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
        break;

    default: // tailwind
        ?>
        <section class="py-12" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>;">
            <div class="container mx-auto px-4">
                <?php if ($title || $subtitle): ?>
                    <div class="text-center mb-8">
                        <?php if ($title): ?>
                            <h2 class="text-3xl font-bold"><?= htmlspecialchars($title) ?></h2>
                        <?php endif; ?>
                        <?php if ($subtitle): ?>
                            <p class="text-gray-600 mt-2"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="relative rounded-lg overflow-hidden shadow-lg" style="padding-bottom: <?= $paddingBottom ?>;">
                    <?php if ($videoType === 'direct'): ?>
                        <video controls <?= $autoplay ? 'autoplay' : '' ?> <?= $muted ? 'muted' : '' ?> <?= $loop ? 'loop' : '' ?>
                               class="absolute inset-0 w-full h-full object-cover">
                            <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                        </video>
                    <?php elseif ($embedUrl): ?>
                        <iframe src="<?= htmlspecialchars($embedUrl) ?>"
                                class="absolute inset-0 w-full h-full"
                                frameborder="0"
                                allowfullscreen></iframe>
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-100">
                            <span class="text-gray-500">Inserisci URL video</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
        break;
}
?>
