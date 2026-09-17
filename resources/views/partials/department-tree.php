<?php
$renderTree = function (array $nodes, int $level = 0) use (&$renderTree, $selectedDepartment, $letter): void {
    foreach ($nodes as $node) {
        $active = (int) ($selectedDepartment ?? 0) === (int) $node['id'];
        $params = ['department' => (int) $node['id']];

        if (($letter ?? '') !== '') {
            $params['letter'] = $letter;
        }
        ?>
        <div class="phonebook-tree-node" style="--level: <?= $level ?>">
            <a
                class="phonebook-tree-link <?= $active ? 'is-active' : '' ?>"
                href="/employees?<?= e(http_build_query($params)) ?>"
            >
                <span class="phonebook-tree-marker">
                    <?= !empty($node['children']) ? '▾' : '•' ?>
                </span>
                <span><?= e($node['name']) ?></span>
                <em><?= (int) $node['count'] ?></em>
            </a>

            <?php if (!empty($node['children'])): ?>
                <div class="phonebook-tree-children">
                    <?php $renderTree($node['children'], $level + 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
};

$renderTree($tree);
