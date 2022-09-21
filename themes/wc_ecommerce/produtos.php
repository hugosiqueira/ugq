<?php
$Read->ExeRead(DB_PDT_CATS, "WHERE cat_name = :cat", "cat={$URL[1]}");
if (!$Read->getResult()):
    require '404.php';
else:
    extract($Read->getResult()[0]);

    $departament = null;
    if ($cat_parent):
        $Read->FullRead("SELECT cat_title, cat_name FROM " . DB_PDT_CATS . " WHERE cat_id = :id", "id={$cat_parent}");
        $departament = " / <a title='{$Read->getResult()[0]['cat_title']} em " . SITE_NAME . "' href='" . BASE . "/produtos/{$Read->getResult()[0]['cat_name']}'>{$Read->getResult()[0]['cat_title']}</a>";
    endif;
    ?>
    <div class="container">
        <section class="content">
            <div class="single_list">
                <h1 class="breadcrumbs">
                    <a title="<?= SITE_NAME; ?>" href="<?= BASE; ?>"><?= SITE_NAME; ?></a>
                    <?= $departament; ?> / 
                    <?= $cat_title; ?>
                </h1>
                <?php
                $Page = (!empty($URL[2]) && filter_var($URL[2], FILTER_VALIDATE_INT) ? $URL[2] : 1);
                $Pager = new Pager(BASE . "/produtos/{$URL[1]}/", "<<", ">>", 3);
                $Pager->ExePager($Page, 9);
                $Read->ExeRead(DB_PDT, "WHERE (pdt_category = :cat OR pdt_subcategory = :cat) AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_title ASC LIMIT :limit OFFSET :offset", "cat={$cat_id}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $LastPDT):
                        extract($LastPDT);
                        $PdtBox = 'box3';
                        require 'inc/product.php';
                    endforeach;

                    $Pager->ExePaginator(DB_PDT, "WHERE (pdt_category = :cat OR pdt_subcategory = :cat) AND (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1", "cat={$cat_id}");
                    echo $Pager->getPaginator();

                else:
                    $Pager->ReturnPage();
                    Erro("Não existem produtos cadastrados em {$cat_title}. Mas temos outras opções! :)", E_USER_NOTICE);
                endif;
                ?>
            </div>

            <?php require 'inc/sidebar.php'; ?>

            <div class="clear"></div>
        </section>
    </div>
<?php
endif;