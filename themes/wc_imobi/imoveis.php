<?php

echo '<div class="container" style="background: #fff;">';
require '_cdn/widgets/imobi/filter.wc.php';
echo '</div>';

echo '<section>';
echo '<div class="content">';
echo '<header class="section_header">';
echo '<h1>Imóveis' . (!empty($URL[1]) ? " / {$URL[1]}" : "") . (!empty($URL[2]) && filter_var($URL[2], FILTER_DEFAULT) ? " / " . $URL[2] . "s" : ' / TODOS') . '</h1>';
echo '<p>Confira abaixo o que encontramos para você!</p>';
echo '</header>';

$Transaction = null;
if (!empty($URL[1])):
    foreach (getWcRealtyTransaction() as $trKey => $trValue):
        if (strtolower($trValue) == $URL[1]):
            $Transaction = "AND realty_transaction = {$trKey}";
            $pagerTr = $trKey;
        endif;
    endforeach;
endif;

$Type = null;
if (!empty($URL[2])):
    foreach (getWcRealtyType() as $tyKey => $tyValue):
        if (strtolower($tyValue) == $URL[2]):
            $Type = "AND realty_type = '{$tyKey}'";
            $pagerTy = $tyKey;
        endif;
    endforeach;
endif;

$pagerTran = (!empty($pagerTr) && !empty(getWcRealtyTransaction($pagerTr)) ? strtolower(getWcRealtyTransaction($pagerTr)) : 'indiferente');
$pagerType = (!empty($pagerTy) && !empty(getWcRealtyType($pagerTy)) ? strtolower(getWcRealtyType($pagerTy)) : 'indiferente');

$Page = (!empty($URL[3]) && intval($URL[3]) ? $URL[3] : 1);
$Pager = new Pager(BASE . "/imoveis/{$pagerTran}/{$pagerType}/", "<<", ">>", 3);
$Pager->ExePager($Page, 12);
$Read->ExeRead(DB_IMOBI, "WHERE realty_status = 1 {$Transaction} {$Type} ORDER BY realty_date DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
if ($Read->getResult()):
    $BOX = 3;

    foreach ($Read->getResult() as $LAUNCH):
        extract($LAUNCH);
        require REQUIRE_PATH . '/inc/realty.php';
    endforeach;

    $Pager->ExePaginator(DB_IMOBI, "WHERE realty_status = 1 {$Transaction} {$Type}");
    echo $Pager->getPaginator();
else:
    $Pager->ReturnPage();
    Erro("<div style='text-align: center'>Desculpe, mas não encontramos imóveis cadastrados nos termos desta consulta!</div>", E_USER_NOTICE);
endif;

echo "</div><div class='clear'></div>";
echo '</section>';
