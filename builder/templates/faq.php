<?php
/**
 * Template: FAQ/Accordion
 */
$bgColor = $settings['backgroundColor'] ?? '#ffffff';
$textColor = $settings['textColor'] ?? '#1f2937';
$title = $settings['title'] ?? 'Domande Frequenti';
$subtitle = $settings['subtitle'] ?? '';

// FAQ items
$items = $settings['items'] ?? [
    [
        'question' => 'Come posso iniziare?',
        'answer' => 'Registrati gratuitamente e inizia subito a creare il tuo progetto.'
    ],
    [
        'question' => 'Quali metodi di pagamento accettate?',
        'answer' => 'Accettiamo tutte le principali carte di credito, PayPal e bonifico bancario.'
    ],
    [
        'question' => 'Posso annullare in qualsiasi momento?',
        'answer' => 'Sì, puoi annullare il tuo abbonamento in qualsiasi momento senza penali.'
    ],
    [
        'question' => 'Offrite assistenza clienti?',
        'answer' => 'Sì, il nostro team di supporto è disponibile 24/7 via chat, email o telefono.'
    ]
];

$accordionId = 'faq-' . uniqid();

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
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion" id="<?= $accordionId ?>">
                            <?php foreach ($items as $index => $item): ?>
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#<?= $accordionId ?>-<?= $index ?>">
                                            <?= htmlspecialchars($item['question']) ?>
                                        </button>
                                    </h3>
                                    <div id="<?= $accordionId ?>-<?= $index ?>"
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                         data-bs-parent="#<?= $accordionId ?>">
                                        <div class="accordion-body">
                                            <?= htmlspecialchars($item['answer']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
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
                    <div class="column is-8">
                        <?php foreach ($items as $index => $item): ?>
                            <div class="card mb-3">
                                <header class="card-header">
                                    <p class="card-header-title"><?= htmlspecialchars($item['question']) ?></p>
                                    <button class="card-header-icon" onclick="this.closest('.card').querySelector('.card-content').classList.toggle('is-hidden')">
                                        <span class="icon"><i>▼</i></span>
                                    </button>
                                </header>
                                <div class="card-content <?= $index > 0 ? 'is-hidden' : '' ?>">
                                    <div class="content"><?= htmlspecialchars($item['answer']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
                <div class="max-w-3xl mx-auto">
                    <div class="divide-y rounded-md border">
                        <?php foreach ($items as $index => $item): ?>
                            <div class="faq-item">
                                <button class="flex w-full items-center justify-between py-4 px-6 text-left font-medium transition-all hover:bg-muted/50"
                                        onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('span').textContent = this.nextElementSibling.classList.contains('hidden') ? '+' : '−';">
                                    <?= htmlspecialchars($item['question']) ?>
                                    <span class="text-xl"><?= $index === 0 ? '−' : '+' ?></span>
                                </button>
                                <div class="px-6 pb-4 text-sm text-muted-foreground <?= $index > 0 ? 'hidden' : '' ?>">
                                    <?= htmlspecialchars($item['answer']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
                <div class="max-w-3xl mx-auto">
                    <div class="divide-y divide-gray-200 rounded-lg border border-gray-200">
                        <?php foreach ($items as $index => $item): ?>
                            <div class="faq-item">
                                <button class="flex w-full items-center justify-between py-4 px-6 text-left font-medium hover:bg-gray-50 transition-colors"
                                        onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('span').textContent = this.nextElementSibling.classList.contains('hidden') ? '+' : '−';">
                                    <?= htmlspecialchars($item['question']) ?>
                                    <span class="text-xl text-gray-500"><?= $index === 0 ? '−' : '+' ?></span>
                                </button>
                                <div class="px-6 pb-4 text-sm text-gray-600 <?= $index > 0 ? 'hidden' : '' ?>">
                                    <?= htmlspecialchars($item['answer']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
        break;
}
?>
