<?php
$Search = urldecode($URL[1]);
$SearchPage = urlencode($Search);

if (empty($_SESSION['search']) || !in_array($Search, $_SESSION['search'])):
    $Read->FullRead("SELECT search_id, search_count FROM " . DB_SEARCH . " WHERE search_key = :key", "key={$Search}");
    if ($Read->getResult()):
        $Update = new Update;
        $DataSearch = ['search_count' => $Read->getResult()[0]['search_count'] + 1];
        $Update->ExeUpdate(DB_SEARCH, $DataSearch, "WHERE search_id = :id", "id={$Read->getResult()[0]['search_id']}");
    else:
        $Create = new Create;
        $DataSearch = ['search_key' => $Search, 'search_count' => 1, 'search_date' => date('Y-m-d H:i:s'), 'search_commit' => date('Y-m-d H:i:s')];
        $Create->ExeCreate(DB_SEARCH, $DataSearch);
    endif;
    $_SESSION['search'][] = $Search;
endif;
?>
<div class="container">
    <section class="content">
        <h1 class="breadcrumbs">
            <a title="<?= SITE_NAME; ?>" href="<?= BASE; ?>"><?= SITE_NAME; ?></a>
            / Pesquisa Por: <?= $URL[1]; ?>
        </h1>
        <?php
        $Page = (!empty($URL[2]) && filter_var($URL[2], FILTER_VALIDATE_INT) ? $URL[2] : 1);
        $Pager = new Pager(BASE . "/pesquisa/" . urlencode($URL[1]) . "/", "<<", ">>", 3);
        $Pager->ExePager($Page, 8);
        $Read->ExeRead(DB_PDT, "WHERE (pdt_title LIKE '%' :s '%' OR pdt_subtitle LIKE '%' :s '%' OR pdt_code = :s) AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_title ASC LIMIT :limit OFFSET :offset", "s=" . urldecode($URL[1]) . "&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $LastPDT):
                extract($LastPDT);
                $PdtBox = 'box4';
                require 'inc/product.php';
            endforeach;

            $Pager->ExePaginator(DB_PDT, "WHERE (pdt_title LIKE '%' :s '%' OR pdt_subtitle LIKE '%' :s '%') AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1", "s=" . urldecode($URL[1]));
            echo $Pager->getPaginator();
        else:
            $Pager->ReturnPage();
            Erro("Desculpe, mas sua pesquisa por " . urldecode($URL[1]) . " não retornou resultados. Você pode tentar outros termos! :)", E_USER_NOTICE);
        endif;
        ?>
        <div class="clear"></div>
    </section>
</div>