<?php
if (!$Read):
    $Read = new Read;
endif;
?>
<header class="main_header">
    <div class="content">
        <img class="wc_logo" src="<?= INCLUDE_PATH; ?>/images/workcontrol.png" alt="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>"/>
        <h1 class="site_title"><?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?></h1>

        <article class="wc_contact">
            <h1><?= SITE_ADDR_PHONE_A; ?></h1>
            <p><?= SITE_ADDR_EMAIL; ?></p>
        </article>
        <div class="clear"></div>
    </div>

    <div class="wc_mobile_nav"><span>&#9776;</span><span>MENU</span></div>
    <nav class="wc_imobi_nav">
        <div class="content">
            <h1 class="site_title">Explore imóveis em <?= SITE_ADDR_CITY; ?></h1>
            <ul>
                <li class="li"><a title="<?= SITE_NAME; ?> | Home" href="<?= BASE; ?>">Home</a></li>
                <li class="li"><a title="<?= SITE_NAME; ?> | Imóveis" href="<?= BASE; ?>/imoveis">Imóveis</a></li><?php
                foreach (getWcRealtyTransaction() as $TransId => $TransValue):
                    $Read->FullRead("SELECT realty_type FROM " . DB_IMOBI . " WHERE realty_transaction = :f GROUP BY realty_type ORDER BY realty_type ASC", "f={$TransId}");
                    if ($Read->getResult()):
                        echo "<li class='li'><a title='{$TransValue} Imóveis' href='" . BASE . "/imoveis/" . mb_strtolower($TransValue) . "'>{$TransValue}</a>";
                        echo "<ul class='sub'>";
                        foreach ($Read->getResult() as $Imobi):
                            echo "<li><a title='{$TransValue} " . getWcRealtyType($Imobi['realty_type']) . "' href='" . BASE . "/imoveis/" . mb_strtolower($TransValue) . "/" . mb_strtolower(getWcRealtyType($Imobi['realty_type'])) . "'>" . getWcRealtyType($Imobi['realty_type']) . "</a></li>";
                        endforeach;
                        echo "</ul>";
                        echo "</li>";
                    endif;
                endforeach;

                $Read->FullRead("SELECT page_title, page_name FROM  " . DB_PAGES . " WHERE page_status = 1 ORDER BY page_order ASC, page_name ASC");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $Page):
                        echo "<li class='li'><a title='" . SITE_NAME . " | {$Page['page_title']}' href='" . BASE . "/{$Page['page_name']}'>{$Page['page_title']}</a></li>";
                    endforeach;
                endif;

                $SearchImobiCode = filter_input(INPUT_POST, 'realty_ref');
                if ($SearchImobiCode):
                    $Search = strip_tags(trim($SearchImobiCode));
                    $Read->FullRead("SELECT realty_name FROM " . DB_IMOBI . " WHERE realty_ref = :ref", "ref={$Search}");
                    if ($Read->getResult()):
                        header('Location: ' . BASE . "/imovel/{$Read->getResult()[0]['realty_name']}");
                        exit;
                    else:
                        header('Location: ' . BASE . '/404');
                        exit;
                    endif;
                endif;
                ?><li class="li imobi_search_code">
                    <form action="" name="search_code" method="post">
                        <input type="text" name="realty_ref" placeholder="Código do Imóvel:"/><button class="btn btn_green">IR!</button>
                    </form>
                </li>
            </ul>
            <div class="clear"></div>
        </div>
    </nav>
</header>