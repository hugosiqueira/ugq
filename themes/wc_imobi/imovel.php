<?php
if (!$Read):
    $Read = new Read;
endif;

$RealTyName = strip_tags(trim($URL[1]));
$Read->ExeRead(DB_IMOBI, "WHERE realty_name = :name", "name={$RealTyName}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
else:
    extract($Read->getResult()[0]);
    $UpdateView = ['realty_views' => $realty_views + 1];
    $Update = new Update;
    $Update->ExeUpdate(DB_IMOBI, $UpdateView, "WHERE realty_id = :id", "id={$realty_id}");
    ?>
    <article class='imobi_single'>
        <div class="content">
            <h1 class="site_title"><?= $realty_title; ?></h1>
            <div class='imobi_single_gb'>
                <img class="jwc_target" src="<?= BASE; ?>/tim.php?src=uploads/<?= $realty_cover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" title="<?= $realty_title; ?>" alt="<?= $realty_title; ?>"/>

                <?php
                $Read->ExeRead(DB_IMOBI_GALLERY, "WHERE realty_id = :id", "id={$realty_id}");
                if ($Read->getResult()):
                    $iGb = 0;
                    echo "<aside><h1 class='site_title'>Fotos do imóvel {$realty_title}</h1>";
                    foreach ($Read->getResult() as $RealtyGallety):
                        $iGb++;
                        extract($RealtyGallety);
                        echo "<article class='jwc_select'><h1 class='site_title'>{$realty_title} - Foto {$iGb} de {$Read->getRowCount()}</h1><img src='" . BASE . "/tim.php?src=uploads/{$image}&w=" . IMAGE_W . "&h=" . IMAGE_H . "' title='{$realty_title} - Foto {$iGb} de {$Read->getRowCount()}' alt='{$realty_title} - Foto {$iGb} de {$Read->getRowCount()}'/></article>";
                    endforeach;
                    echo "</aside>";
                endif;
                ?>

            </div><div class="imobi_single_desc">
                <header>
                    <h2><?= $realty_title; ?></h2>
                </header>

                <div class="realty_item_content">
                    <h3>Características do Imóvel:</h3>
                    <p class="price">
                        <?= (is_string(getWcRealtyTransaction($realty_transaction)) ? getWcRealtyTransaction($realty_transaction) : 'PREÇO') ?>: <b><?= ($realty_price ? "R$ " . number_format($realty_price, '2', ',', '.') : 'A combinar'); ?></b></p><p class="ref">
                        Referência: <b><?= $realty_ref; ?></b></p><p class="city">
                        Cidade: <b><?= $realty_city; ?></b></p><p class="district">
                        Bairro: <b><?= $realty_district; ?></b></p><p class="bed">
                        Quartos: <b><?= $realty_bedrooms; ?></b></p><p class="aparts">
                        Suítes: <b><?= $realty_apartments; ?></b></p><p class="bat">
                        Banheiros: <b><?= $realty_bathrooms; ?></b></p><p class="parking">
                        Garagem: <b><?= $realty_parkings; ?></b></p><p class="total">
                        Área: <b><?= $realty_builtarea; ?>m<sup>2</sup></b></p><p class="total">
                        Total: <b><?= $realty_totalarea; ?>m<sup>2</sup></b></p>
                </div>
            </div>
        </div>
        <div class="imobi_brokers">
            <div class="content">
                <p><b>Corretores de plantão:</b> Ligue agora para <?= SITE_ADDR_PHONE_A; ?> e informe o código <?= $realty_ref; ?>!</p>
                <p>De segunda a sexta entre 8h00 e 18h00, sábados entre 8h00 e 12h00</p>
            </div>
        </div>
        <div class="single_imobi_desc">
            <div class="content">
                <div class="htmlchars" style="padding: 0;"><?= $realty_desc; ?></div>
            </div>
        </div>

        <div class="imobi_single_part">
            <div class="content">
                <h3>Particularidades deste imóvel:</h3>
                <?= "<span><b>√</b> " . str_replace(',', '</span><span><b>√</b>', ucwords($realty_particulars)) . "</span>"; ?>
            </div>
        </div>

    </article>
    <?php
    echo "</div>";
endif;

