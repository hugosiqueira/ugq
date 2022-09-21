<?php

echo '<aside class="single_sidebar">';

//In Offer
$Read->ExeRead(DB_PDT, "WHERE (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 AND pdt_offer_start <= NOW() AND pdt_offer_end >= NOW() ORDER BY RAND() LIMIT 3");
if ($Read->getResult()):
    echo "<section class='single_sidebar_widget delivered'>";
    echo "<h1><span>Ofertas Especiais:</span></h1>";
    foreach ($Read->getResult() as $InOffer):
        echo "<article>";
        echo "<a title='{$InOffer['pdt_title']}' href='" . BASE . "/produto/{$InOffer['pdt_name']}'><img title='{$InOffer['pdt_title']}' alt='{$InOffer['pdt_title']}' src='" . BASE . "/tim.php?src=uploads/{$InOffer['pdt_cover']}&w=" . THUMB_W / 3 . "&h=" . THUMB_H / 3 . "'/></a>";
        echo "<div>";
        echo "<h1><a title='{$InOffer['pdt_title']}' href='" . BASE . "/produto/{$InOffer['pdt_name']}'>{$InOffer['pdt_title']}</a></h1>";
        echo "<p class='offer'>de <strike>R$ " . number_format($InOffer['pdt_price'], 2, ',', '.') . "</strike> <span>por R$ " . number_format($InOffer['pdt_offer_price'], 2, ',', '.') . "</span></p>";
        echo "<p class='offerend'>Em oferta até " . date('d/m/y', strtotime($InOffer['pdt_offer_end'])) . "</p>";
        echo "</div>";
        echo "</article>";
    endforeach;
    echo "</section>";
endif;

//Delivered By Car, Brand or All
if (!empty($cat_id)):
    $Read->ExeRead(DB_PDT, "WHERE (pdt_category = :cat OR pdt_subcategory = :cat) AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_delivered DESC, pdt_title ASC LIMIT 5", "cat={$cat_id}");
elseif (!empty($brand_id)):
    $Read->ExeRead(DB_PDT, "WHERE pdt_brand = :brand AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_delivered DESC, pdt_title ASC LIMIT 5", "brand={$brand_id}");
else:
    $Read->ExeRead(DB_PDT, "WHERE (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_delivered DESC, pdt_title ASC LIMIT 5");
endif;

if ($Read->getResult()):
    echo "<section class='single_sidebar_widget delivered'>";
    echo "<h1><span>Mais Vendidos:</span></h1>";
    foreach ($Read->getResult() as $Delivered):
        echo "<article>";
        echo "<a title='{$Delivered['pdt_title']}' href='" . BASE . "/produto/{$Delivered['pdt_name']}'><img title='{$Delivered['pdt_title']}' alt='{$Delivered['pdt_title']}' src='" . BASE . "/tim.php?src=uploads/{$Delivered['pdt_cover']}&w=" . THUMB_W / 3 . "&h=" . THUMB_H / 3 . "'/></a>";
        echo "<div>";
        echo "<h1><a title='{$Delivered['pdt_title']}' href='" . BASE . "/produto/{$Delivered['pdt_name']}'>{$Delivered['pdt_title']}</a></h1>";
        echo "<p>" . Check::Words($Delivered['pdt_subtitle'], 16) . "</p>";
        echo "</div>";
        echo "</article>";
    endforeach;
    echo "</section>";
endif;

/*
  $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT 3");
  if ($Read->getResult()):
  echo "<section class='single_sidebar_widget posts'>";
  echo "<h1><span>Nosso Blog:</span></h1>";
  foreach ($Read->getResult() as $LastPosts):
  echo "<article>";
  echo "<a title='{$LastPosts['post_title']}' href='" . BASE . "/artigo/{$LastPosts['post_name']}'><img title='{$LastPosts['post_title']}' alt='{$LastPosts['post_title']}' src='" . BASE . "/tim.php?src=uploads/{$LastPosts['post_cover']}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "'/></a>";
  echo "<div>";
  echo "<h1><a title='{$LastPosts['post_title']}' href='" . BASE . "/artigo/{$LastPosts['post_name']}'>{$LastPosts['post_title']}</a></h1>";
  echo "</div>";
  echo "</article>";
  endforeach;
  echo "</section>";
  endif;
 */
echo '</aside>';

