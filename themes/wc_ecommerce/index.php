<div class="container">
    <?php
    if (APP_SLIDE):
        $SlideSeconts = 3;
        require '_cdn/widgets/slide/slide.wc.php';
    endif;
    ?>

    <section class="content">
        <div class="single_list">
            <h1 class="breadcrumbs">Nossos Produtos</h1>

            <?php
            $Page = (!empty($URL[1]) && filter_var($URL[1], FILTER_VALIDATE_INT) ? $URL[1] : 1);
            $Pager = new Pager(BASE . "/index/", "<<", ">>", 3);
            $Pager->ExePager($Page, 12);
            $Read->ExeRead(DB_PDT, "WHERE pdt_inventory >= 1 AND pdt_status = 1 ORDER BY pdt_title ASC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $LastPDT):
                    extract($LastPDT);
                    $PdtBox = 'box3';
                    require 'inc/product.php';
                endforeach;

                $Pager->ExePaginator(DB_PDT, "WHERE pdt_inventory >= 1 AND pdt_status = 1");
                echo $Pager->getPaginator();

            else:
                $Pager->ReturnPage();
                Erro("Ainda Não Existe Produtos Cadastrados. Por favor, volte mais tarde!", E_USER_NOTICE);
            endif;
            ?>
        </div>

        <?php require 'inc/sidebar.php'; ?>
        <div class="clear"></div>
    </section>
</div>