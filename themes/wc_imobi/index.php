<?php

if (APP_SLIDE):
    $SlideSeconts = 5;
    require '_cdn/widgets/slide/slide.wc.php';
endif;

if (!$Read):
    $Read = new Read;
endif;

require './_cdn/widgets/imobi/filter.wc.php';

$Read->ExeRead(DB_IMOBI, "WHERE realty_status = 1 AND realty_observation = :ob ORDER BY realty_date DESC LIMIT :limit", "ob=2&limit=3");
if ($Read->getResult()):
    $BOX = 3;
    echo '<section class="wc_imobi_launch">';
    echo '<div class="content">';
    echo '<header class="section_header">';
    echo '<h1>Confira os lançamentos:</h1>';
    echo '<p>Os melhores investimentos para sua morada!</p>';
    echo '</header>';
    foreach ($Read->getResult() as $LAUNCH):
        extract($LAUNCH);
        require REQUIRE_PATH . '/inc/realty.php';
    endforeach;
    echo "</div><div class='clear'></div>";
    echo '</section>';
endif;


$Read->setPlaces("ob=1&limit=4");
if ($Read->getResult()):
    $BOX = 4;
    echo '<section class="wc_imobi_featured">';
    echo '<div class="content">';
    echo '<header class="section_header">';
    echo '<h1>Imóveis em destaque:</h1>';
    echo '<p>Ótimos negócios. Ótimos investimentos!</p>';
    echo '</header>';
    foreach ($Read->getResult() as $LAUNCH):
        extract($LAUNCH);
        require REQUIRE_PATH . '/inc/realty.php';
    endforeach;
    echo "</div><div class='clear'></div>";
    echo '</section>';
else:
    $Read->setPlaces("ob=&limit=4");
    $BOX = 4;
    echo '<section class="wc_imobi_featured">';
    echo '<div class="content">';
    echo '<header class="section_header">';
    echo '<h1>Novidades ' . SITE_NAME . ':</h1>';
    echo '<p>Confira os melhores imóveis em sua região!</p>';
    echo '</header>';
    foreach ($Read->getResult() as $LAUNCH):
        extract($LAUNCH);
        require REQUIRE_PATH . '/inc/realty.php';
    endforeach;
    echo "</div><div class='clear'></div>";
    echo '</section>';
endif;