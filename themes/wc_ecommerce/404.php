<div class="container not_found">
    <div class="content">
        <section>
            <header class="header">
                <h1>Desculpe, mas não encontramos o que você procura!</h1>
                <p>A página ou conteúdo acessado não foi encontrado em nosso site. Sentimos muito por isso! Por favor. Faça uma pesquisa, ou ainda veja abaixo uma lista de nossos produtos mais vendidos!</p>
            </header>

            <form class="search_form" name="search" action="" method="post" enctype="multipart/form-data">
                <input type="text" name="s" placeholder="Pesquisar Produtos:" required/>
                <button class="btn btn_blue">Pesquisar</button>
            </form>

            <?php
            $Read->ExeRead(DB_PDT, "WHERE (pdt_inventory >= 1 OR pdt_inventory IS NULL) AND pdt_status = 1 ORDER BY pdt_delivered DESC LIMIT 4");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $PdtNot):
                    extract($PdtNot);
                    require 'inc/product.php';
                endforeach;
            endif;
            ?>

        </section>
        <div class="clear"></div>
    </div>
</div>