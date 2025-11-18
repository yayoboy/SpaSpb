<?php
/**
 * Template: Contact Block
 */
$email = $settings['email'] ?? 'info@example.com';
$phone = $settings['phone'] ?? '+39 123 456 789';
$address = $settings['address'] ?? 'Via Roma 1, Milano';

$classes = match($uiLibrary) {
    'bootstrap' => 'contact-block py-5',
    'tailwind' => 'contact-block py-16',
    'shadcn' => 'contact-block py-16',
    'bulma' => 'section',
    default => 'contact-block'
};
?>

<section class="<?= $classes ?>">
    <div class="container mx-auto px-4">
        <?php if ($uiLibrary === 'bootstrap'): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <?= $content ?>
                    </div>
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <div class="row g-4">
                                <div class="col-md-4 text-center">
                                    <div class="contact-icon mb-3" style="font-size: 2rem;">📧</div>
                                    <h6>Email</h6>
                                    <p><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="contact-icon mb-3" style="font-size: 2rem;">📞</div>
                                    <h6>Telefono</h6>
                                    <p><a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a></p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="contact-icon mb-3" style="font-size: 2rem;">📍</div>
                                    <h6>Indirizzo</h6>
                                    <p><?= htmlspecialchars($address) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($uiLibrary === 'bulma'): ?>
            <div class="columns is-centered">
                <div class="column is-8">
                    <div class="has-text-centered mb-5">
                        <?= $content ?>
                    </div>
                    <div class="box">
                        <div class="columns">
                            <div class="column has-text-centered">
                                <div class="contact-icon mb-3" style="font-size: 2rem;">📧</div>
                                <h6 class="title is-6">Email</h6>
                                <p><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p>
                            </div>
                            <div class="column has-text-centered">
                                <div class="contact-icon mb-3" style="font-size: 2rem;">📞</div>
                                <h6 class="title is-6">Telefono</h6>
                                <p><a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a></p>
                            </div>
                            <div class="column has-text-centered">
                                <div class="contact-icon mb-3" style="font-size: 2rem;">📍</div>
                                <h6 class="title is-6">Indirizzo</h6>
                                <p><?= htmlspecialchars($address) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <?= $content ?>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="contact-icon mb-4 text-5xl">📧</div>
                            <h6 class="font-semibold text-lg mb-2">Email</h6>
                            <p><a href="mailto:<?= htmlspecialchars($email) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($email) ?></a></p>
                        </div>
                        <div class="text-center">
                            <div class="contact-icon mb-4 text-5xl">📞</div>
                            <h6 class="font-semibold text-lg mb-2">Telefono</h6>
                            <p><a href="tel:<?= htmlspecialchars($phone) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($phone) ?></a></p>
                        </div>
                        <div class="text-center">
                            <div class="contact-icon mb-4 text-5xl">📍</div>
                            <h6 class="font-semibold text-lg mb-2">Indirizzo</h6>
                            <p class="text-gray-600"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
