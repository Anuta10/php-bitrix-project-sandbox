<div class="page-head">
    <div>
        <h1><?= e(t('cases.title')) ?></h1>
        <p><?= e(t('cases.subtitle')) ?></p>
    </div>
</div>

<div class="case-study-list">
    <?php foreach ($cases as $index => $case): ?>
        <article class="case-study panel" id="<?= e($case['slug']) ?>">
            <div class="case-study-number">0<?= $index + 1 ?></div>

            <div class="case-study-body">
                <h2><?= e($case['title_' . $locale]) ?></h2>

                <div class="case-columns">
                    <div>
                        <span><?= e(t('cases.problem')) ?></span>
                        <p><?= e($case['problem_' . $locale]) ?></p>
                    </div>

                    <div>
                        <span><?= e(t('cases.solution')) ?></span>
                        <p><?= e($case['solution_' . $locale]) ?></p>
                    </div>

                    <div>
                        <span><?= e(t('cases.production')) ?></span>
                        <p><?= e($case['production_' . $locale]) ?></p>
                    </div>
                </div>

                <div class="tag-row">
                    <?php foreach ($case['skills'] as $skill): ?>
                        <span><?= e($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
