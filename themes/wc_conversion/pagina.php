<?php
if (!$Read):
    $Read = new Read;
endif;

$Email = new Email;

$Read->ExeRead(DB_PAGES, "WHERE page_name = :nm", "nm={$URL[0]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
endif;
?>
<div class="top_conversion breadcrumbs">
    <div class="content">
        <p><a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a> / <?= $page_title; ?></p>
        <div class="clear"></div>
    </div>
</div>

<div class="container page_single" style="background: #eee;">
    <div class="content">
        <h1 class="site_title"><?= $page_title; ?></h1>
        <div class="htmlchars" style="background: #fff;">
            <?= $page_content; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php if (APP_COMMENTS && COMMENT_ON_PAGES): ?>
    <div class="container" style="background: #fff; padding: 20px 0;">
        <div class="content">
            <?php
            $CommentKey = $page_id;
            $CommentType = 'page';
            require '_cdn/widgets/comments/comments.php';
            ?>
            <div class="clear"></div>
        </div>
    </div>
<?php endif; ?>