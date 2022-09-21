<?php
require '_cdn/widgets/ecommerce/cart.inc.php';
require '_cdn/widgets/contact/contact.wc.php';
?>
<div class="main_header container">
    <div class="content">
        <header>
            <h1><?= SITE_NAME ?></h1>
            <div class="clear"></div>
        </header>

        <div class="main_header_bar">
            <div class="main_header_bar_line contact">
                <div class="main_header_bar_contact"><?= SITE_ADDR_PHONE_A; ?> - <a href="mailto:" title="Enviar E-mail"><?= SITE_ADDR_EMAIL; ?></a></div>
                <ul class="main_header_bar_social">
                    <?php
                    if (SITE_SOCIAL_FB):
                        echo '<li><a class="facebook" target="_blank" href="https://www.facebook.com/' . SITE_SOCIAL_FB_PAGE . '" title="No Facebook"><img src="' . INCLUDE_PATH . '/images/facebook.png" alt="' . SITE_NAME . ' - Facebook" title="' . SITE_NAME . ' - Facebook"></a></li>';
                    endif;

                    if (SITE_SOCIAL_TWITTER):
                        echo '<li><a class="twitter" target="_blank" href="https://www.twitter.com/' . SITE_SOCIAL_TWITTER . '" title="No Twitter"><img src="' . INCLUDE_PATH . '/images/twitter.png" alt="' . SITE_NAME . ' - Twitter" title="' . SITE_NAME . ' - Twitter"></a></li>';
                    endif;

                    if (SITE_SOCIAL_GOOGLE):
                        echo '<li><a class="google" target="_blank" href="https://plus.google.com/' . SITE_SOCIAL_GOOGLE_PAGE . '" title="No Google Plus"><img src="' . INCLUDE_PATH . '/images/google.png" alt="' . SITE_NAME . ' - Google Plus" title="' . SITE_NAME . ' - Google Plus"></a></li>';
                    endif;

                    if (SITE_SOCIAL_YOUTUBE):
                        echo '<li><a class="youtube" target="_blank" href="https://www.youtube.com/user/' . SITE_SOCIAL_YOUTUBE . '" title="No YouTube"><img src="' . INCLUDE_PATH . '/images/youtube.png" alt="' . SITE_NAME . ' - YouTube" title="' . SITE_NAME . ' - YouTube"></a></li>';
                    endif;
                    ?>
                </ul>
            </div>

            <div class="main_header_bar_line search">
                <form class="search_form" name="search" action="" method="post" enctype="multipart/form-data">
                    <input class="input" type="search" name="s" placeholder="Pesquisar Produtos:" required/>
                    <button class="btn btn_blue">Pesquisar</button>
                </form>

                <div class="main_header_cart"><?php require '_cdn/widgets/ecommerce/cart.bar.php'; ?></div><div class="main_header_user"><?php require '_cdn/widgets/account/account.bar.php'; ?></div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="content main_nav">
        <nav>
            <h1 class="main_nav_mobile_menu">&#9776; MENU</h1>
            <ul>
                <li><a title="<?= SITE_NAME; ?>" href="<?= BASE; ?>">Home</a></li>
                <?php
                $Read->FullRead("SELECT cat_id, cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent IS NULL AND cat_id IN(SELECT pdt_category FROM " . DB_PDT . " WHERE pdt_status = 1) ORDER BY cat_title ASC");
                if ($Read->getResult()):
                    echo "<li><span>Departamentos:</span><ul class='sub'>";
                    foreach ($Read->getResult() as $NavSectors):
                        echo "<li><a title='{$NavSectors['cat_title']}' href='" . BASE . "/produtos/{$NavSectors['cat_name']}'>{$NavSectors['cat_title']}</a>";
                        $Read->FullRead("SELECT cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_parent = :cat_id ORDER BY cat_title ASC", "cat_id={$NavSectors['cat_id']}");
                        if ($Read->getResult()):
                            echo "<ul class='subsub'>";
                            foreach ($Read->getResult() as $NavProductsCat):
                                echo "<li><a title='{$NavProductsCat['cat_title']}' href='" . BASE . "/produtos/{$NavProductsCat['cat_name']}'>{$NavProductsCat['cat_title']}</a></li>";
                            endforeach;
                            echo "</ul>";
                        endif;
                        echo "</li>";
                    endforeach;
                    echo "</ul></li>";
                endif;

                $Read->FullRead("SELECT brand_title, brand_name FROM " . DB_PDT_BRANDS . " WHERE brand_id IN(SELECT pdt_brand FROM " . DB_PDT . " WHERE pdt_status = 1) ORDER BY brand_title ASC");
                if ($Read->getResult()):
                    echo "<li><span>Fabricantes:</span><ul class='sub'>";
                    foreach ($Read->getResult() as $NavBrand):
                        echo "<li><a title='{$NavBrand['brand_title']}' href='" . BASE . "/marca/{$NavBrand['brand_name']}'>{$NavBrand['brand_title']}</a></li>";
                    endforeach;
                    echo "</ul></li>";
                endif;

                $Read->FullRead("SELECT page_title, page_name FROM " . DB_PAGES . " WHERE page_status = 1 ORDER BY page_order ASC, page_name ASC");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $NavPage):
                        echo "<li><a title='{$NavPage['page_title']}' href='" . BASE . "/{$NavPage['page_name']}'>{$NavPage['page_title']}</a></li>";
                    endforeach;
                endif;
                ?>
                <li><a title="Fale Conosco" class="jwc_contact" href="#">Fale Conosco</a></li>
            </ul>
        </nav>
        <div class="clear"></div>
    </div>
</div>