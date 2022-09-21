<?php

$WcImobiFilter = (!empty($_SESSION['wc_imobi_filter']) ? $_SESSION['wc_imobi_filter'] : null);
unset($WcImobiFilter['min_price'], $WcImobiFilter['max_price'], $WcImobiFilter['realty_bedrooms']);

$FilterAdd = null;
$FilterValues = (!empty($WcImobiFilter) ? http_build_query($WcImobiFilter) : null);

if ($WcImobiFilter):
    foreach ($WcImobiFilter as $fKey => $fValue):
        $FilterAdd .= " AND {$fKey} = :{$fKey}";
    endforeach;
endif;

$BedRooms = (!empty($_SESSION['wc_imobi_filter']['realty_bedrooms']) ? "AND realty_bedrooms >= '{$_SESSION['wc_imobi_filter']['realty_bedrooms']}'" : '');
$MinPrice = (!empty($_SESSION['wc_imobi_filter']['min_price']) ? "AND realty_price >= '{$_SESSION['wc_imobi_filter']['min_price']}'" : '');
$MaxPrice = (!empty($_SESSION['wc_imobi_filter']['max_price']) ? "AND realty_price <= '{$_SESSION['wc_imobi_filter']['max_price']}'" : '');

$Page = (!empty($URL[1]) ? $URL[1] : 1);
$Pager = new Pager(BASE . "/filtro/", "<<", ">>", 3);
$Pager->ExePager($Page, 12);
$Read->ExeRead(DB_IMOBI, "WHERE realty_status = 1 {$FilterAdd} {$BedRooms} {$MinPrice} {$MaxPrice} LIMIT :limit OFFSET :offset", "{$FilterValues}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");

if ($Read->getResult()):
    foreach ($Read->getResult() as $IMOBI):
        extract($IMOBI);
        $BOX = 3;
        require REQUIRE_PATH . '/inc/realty.php';
    endforeach;

    $Pager->ExePaginator(DB_IMOBI, "WHERE realty_status = 1 {$FilterAdd} {$BedRooms} {$MinPrice} {$MaxPrice}", "{$FilterValues}");
    echo $Pager->getPaginator();
else:
    $Pager->ReturnPage();
    Erro("<div style='text-align: center'>Desculpe, mas não encontramos imóveis cadastrados nos termos desta consulta!</div>", E_USER_NOTICE);
endif;