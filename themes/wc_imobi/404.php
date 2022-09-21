<div class="container not_found">
    <div class="content">
        <section>
            <header class="section_header">
                <h1>Desculpe, mas não encontramos o que você procura!</h1>
                <p>Confira abaixo alguns destaques! Você também pode utilizar os menus para voltar a navegar!</p>
            </header>
            <?php
            $BOX = 3;
            $Read->ExeRead(DB_IMOBI, "WHERE realty_status = 1 AND realty_observation = :ob ORDER BY realty_date DESC LIMIT :limit", "ob=2&limit=3");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $LAUNCH):
                    extract($LAUNCH);
                    require REQUIRE_PATH . '/inc/realty.php';
                endforeach;
            else:
                $Read->setPlaces("ob=&limit=3");
                foreach ($Read->getResult() as $LAUNCH):
                    extract($LAUNCH);
                    require REQUIRE_PATH . '/inc/realty.php';
                endforeach;
            endif;
            ?>

        </section>
        <div class="clear"></div>
    </div>
</div>