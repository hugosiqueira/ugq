<?php
$Read->ExeRead(DB_PDT, "WHERE pdt_name = :nm AND pdt_status = 1", "nm={$URL[1]}");
if (!$Read->getResult()):
    header('Location: ' . BASE . "/404.php");
    exit;
else:
    extract($Read->getResult()[0]);
    $CommentKey = $pdt_id;
    $CommentType = 'product';

    $PdtViewUpdate = ['pdt_views' => $pdt_views + 1, 'pdt_lastview' => date('Y-m-d H:i:s')];
    $Update = new Update;
    $Update->ExeUpdate(DB_PDT, $PdtViewUpdate, "WHERE pdt_id = :id", "id={$pdt_id}");

    $Read->FullRead("SELECT brand_name, brand_title FROM " . DB_PDT_BRANDS . " WHERE brand_id = :id", "id={$pdt_brand}");
    $Brand = ($Read->getResult() ? $Read->getResult()[0] : '');

    $Read->FullRead("SELECT cat_name, cat_title FROM " . DB_PDT_CATS . " WHERE cat_id = :id", "id={$pdt_subcategory}");
    $Category = ($Read->getResult() ? $Read->getResult()[0] : '');

    $CommentModerate = (COMMENT_MODERATE ? " AND (status = 1 OR status = 3)" : '');
    $Read->FullRead("SELECT id FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
    $Aval = $Read->getRowCount();

    $Read->FullRead("SELECT SUM(rank) as total FROM " . DB_COMMENTS . " WHERE pdt_id = :pid{$CommentModerate}", "pid={$pdt_id}");
    $TotalAval = $Read->getResult()[0]['total'];
    $TotalRank = $Aval * 5;
    $getRank = ($TotalAval ? (($TotalAval / $TotalRank) * 50) / 10 : 0);
    $Rank = str_repeat("&starf;", intval($getRank)) . str_repeat("&star;", 5 - intval($getRank));

    if ($pdt_hotlink):
        header("Location: {$pdt_hotlink}");
        exit;
    endif;
endif;
?>

<div class="container produtct_basics" id="mais">
    <div class="content">
        <div class="produtct_basics_cover">
            <?php
            $PdtStockeOut = ($pdt_inventory && $pdt_inventory <= 5 ? true : false);

            if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                $PdtDiscount = number_format(100 - ((100 / $pdt_price) * $pdt_offer_price), '0', '', '');
                $PdtIdentBox = (!empty($PdtStockeOut) ? ' produtct_basics_cover_ident' : null);
                echo "<span class='produtct_basics_cover_offer{$PdtIdentBox}'>{$PdtDiscount}% OFF</span>";
            endif;

            if ($PdtStockeOut):
                echo "<span class='produtct_basics_cover_stock'>Últimas Unidades</span>";
            endif;

            echo "<img title='{$pdt_title}' alt='{$pdt_title}' src='" . BASE . "/uploads/{$pdt_cover}'/>";

            $Read->ExeRead(DB_PDT_GALLERY, "WHERE product_id = :id", "id={$pdt_id}");
            if ($Read->getResult()):
                $i = 0;
                echo "<ul>";
                foreach ($Read->getResult() as $PDTIMG):
                    $i++;
                    echo "<li><a rel='shadowbox[img{$pdt_id}]' title='{$pdt_title} - Foto {$i}' href='" . BASE . "/uploads/{$PDTIMG['image']}'><img title='{$pdt_title} - Foto {$i}' alt='{$pdt_title} - Foto {$i}' src='" . BASE . "/tim.php?src=uploads/{$PDTIMG['image']}&w=" . THUMB_W / 3 . "&h=" . THUMB_H / 3 . "'/></a></li>";
                endforeach;
                echo "</ul>";
            endif;
            ?>
        </div>
        <div class="produtct_basics_infor">
            <header>
                <h1><?= $pdt_title; ?></h1>
                <p><?= $pdt_subtitle; ?></p>
            </header>
            <div class="info">
                <p>Código: <?= $pdt_code; ?></p>
                <?php if ($Brand): ?>
                    <p>Marca: <a title="Mais produtos da marca <?= $Brand['brand_title']; ?>" href="<?= BASE . "/marca/{$Brand['brand_name']}"; ?>"><?= $Brand['brand_title']; ?></a></p>
                    <?php
                endif;
                if ($Category):
                    ?>
                    <p>Categoria: <a title="Mais produtos em <?= $Category['cat_title']; ?>" href="<?= BASE . "/produtos/{$Category['cat_name']}"; ?>"><?= $Category['cat_title']; ?></a></p>
                <?php endif; ?>
                <p>Estoque: <?= $pdt_inventory ? str_pad($pdt_inventory, 3, 0, STR_PAD_LEFT) . " unidades" : 'Fora de Estoque'; ?></p>
                <p class="reviews"><?= $Rank; ?> - <a class="wc_goto" href="#comments" title="Ver Avaliações!"><?= str_pad($Aval, 2, 0, 0); ?> avaliações!</a></p>
                <div class="price">
                    <?php
                    $PdtPrice = null;
                    if ($pdt_offer_price && $pdt_offer_start <= date('Y-m-d H:i:s') && $pdt_offer_end >= date('Y-m-d H:i:s')):
                        $PdtPrice = $pdt_offer_price;
                        echo "R$ " . number_format($pdt_offer_price, '2', ',', '.') . " <strike>R$ " . number_format($pdt_price, '2', ',', '.') . "</strike>";
                    else:
                        $PdtPrice = $pdt_price;
                        echo "R$ " . number_format($pdt_price, '2', ',', '.');
                    endif;

                    //By Wallyson Alemão
                    if (ECOMMERCE_PAY_SPLIT):
                        $MakeSplit = intval($PdtPrice / ECOMMERCE_PAY_SPLIT_MIN);
                        $NumSplit = (!$MakeSplit ? 1 : ($MakeSplit && $MakeSplit <= ECOMMERCE_PAY_SPLIT_NUM ? $MakeSplit : ECOMMERCE_PAY_SPLIT_NUM));
                        if ($NumSplit <= ECOMMERCE_PAY_SPLIT_ACN):
                            $SplitPrice = number_format(($PdtPrice / $NumSplit), '2', ',', '.');
                        elseif ($NumSplit - ECOMMERCE_PAY_SPLIT_ACN == 1):
                            $SplitPrice = number_format(($PdtPrice * (pow(1 + (ECOMMERCE_PAY_SPLIT_ACM / 100), $NumSplit - ECOMMERCE_PAY_SPLIT_ACN)) / $NumSplit), '2', ',', '.');
                        else:
                            $ParcSj = round($PdtPrice / $NumSplit, 2); // Valor das parcelas sem juros
                            $ParcRest = (ECOMMERCE_PAY_SPLIT_ACN > 1 ? $NumSplit - ECOMMERCE_PAY_SPLIT_ACN : $NumSplit);
                            $DiffParc = round(($PdtPrice * getFactor($ParcRest) * $ParcRest) - $PdtPrice, 2);
                            $SplitPrice = number_format($ParcSj + ($DiffParc / $NumSplit), '2', ',', '.');
                        endif;
                        echo "<p class='pdt_single_split'>Em até {$NumSplit}x de R$ {$SplitPrice}</p>";
                    endif;
                    ?>
                </div>
            </div>
            <div class="cart">
                <?php require '_cdn/widgets/ecommerce/cart.add.php'; ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<?php if (!empty($pdt_content)): ?>
    <div class="container produtct_infor">
        <div class="content">
            <div class="htmlchars">
                <?= $pdt_content; ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
<?php endif; ?>

<?php
$Read->ExeRead(DB_PDT, "WHERE pdt_id != {$pdt_id} AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 AND pdt_offer_start <= NOW() AND pdt_offer_end >= NOW() ORDER BY RAND() LIMIT 4");
if ($Read->getResult()):
    ?>
    <div class="container produtct_more">
        <section class="content">
            <header>
                <h1>Produtos em Oferta:</h1>
                <p>Confira e aproveite nossas ofertas especiais!</p>
            </header>
            <?php
            foreach ($Read->getResult() as $PdtMore):
                extract($PdtMore);
                require 'inc/product.php';
            endforeach;
            ?>
            <div class="clear"></div>
        </section>
    </div>
<?php endif; ?>

<?php if (APP_COMMENTS && COMMENT_ON_PRODUCTS): ?>
    <div class="container produtct_reviews">
        <div class="content">
            <?php
            require '_cdn/widgets/comments/comments.php';
            ?>
            <div class="clear"></div>
        </div>
    </div>
<?php endif; ?>