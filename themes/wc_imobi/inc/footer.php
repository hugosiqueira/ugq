<footer class="wc_footer">
    <div class="content">
        <article class="wc_imobi_footerinfo box box4">
            <img class="wc_logo" src="<?= INCLUDE_PATH; ?>/images/workcontrol_w.png" alt="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>"/>
            <h1 class="site_title"><?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?></h1>
            <p><?= SITE_DESC; ?></p>
        </article><article class="wc_imobi_footerlinks box box4">
            <h1>Encontre fácil:</h1>
            <ul>
                <li class="li"><a title="Home" href="<?= BASE; ?>">Home</a></li>
                <?php
                foreach (getWcRealtyTransaction() as $ItemMenu):
                    echo '<li class="li"><a title="' . $ItemMenu . ' Imóveis" href="' . BASE . '/imoveis/' . Check::Name($ItemMenu) . '">' . $ItemMenu . '</a></li>';
                endforeach;
                ?>
            </ul>
        </article><article class="wc_imobi_footercontact box box4">
            <h1>Fale Conosco:</h1>
            <p>Fone: <?= SITE_ADDR_PHONE_A; ?></p>
            <p>E-mail: <?= SITE_ADDR_EMAIL; ?></p>
            <p><?= SITE_ADDR_ADDR; ?>, <?= SITE_ADDR_DISTRICT; ?>, <?= SITE_ADDR_CITY; ?>/<?= SITE_ADDR_UF; ?></p>
        </article>
        <div class="clear"></div>
    </div>
</footer>
