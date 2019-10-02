<?php
/**
 * @var Page[] $pages
 * @var string $currentPageId
 * @var array $sectionContent
 */

use Ultraleet\WP\Settings\Components\Page;

?>
<div class="wrap wcerply">
    <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php foreach ($pages as $pageId => $page): ?>
                <a href="<?= add_query_arg(['tab' => $pageId]) ?>"
                   class="nav-tab<?= $pageId == $currentPageId ? ' nav-tab-active' : '' ?>"><?= esc_html(
                        $page->getTitle()
                    ) ?>
                </a>

            <?php endforeach; ?>
        </nav>
    </form>
<?php foreach ($sectionContent as $content): ?>
    <?= $content ?>
<?php endforeach; ?>

</div>
