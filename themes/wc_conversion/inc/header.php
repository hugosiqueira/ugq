<header class="main_header" id="h">
    <div class="content">
        <h1 class="site_title"><?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?></h1>
        <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>">
            <img class="main_logo" src="<?= INCLUDE_PATH; ?>/images/workcontrol.png" alt="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>"/>
        </a>

        <div class="mobile_menu">&#9776;</div>

        <nav class="main_nav">
            <h1 class="site_title">Empreendedorismo e Marketing Digital!</h1>
            <?php
            if (empty($URL[1]) || $URL[0] == 'index'):
                require REQUIRE_PATH . '/inc/menu.php';
            else:
                require REQUIRE_PATH . '/inc/menulink.php';
            endif;
            require REQUIRE_PATH . '/inc/social.php';
            ?>
        </nav>
        <div class="clear"></div>
    </div>
</header>