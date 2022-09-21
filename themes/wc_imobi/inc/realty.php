<article class="box box<?= $BOX; ?> realty_item">
    <header class="realty_item_header">
        <a title="Detalhes de <?= $realty_title; ?>" href="<?= BASE; ?>/imovel/<?= $realty_name; ?>"><img title="<?= $realty_title; ?>" alt="<?= $realty_title; ?>" src="<?= BASE; ?>/tim.php?src=uploads/<?= $realty_cover; ?>&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/></a>
        <h1><a title="Detalhes de <?= $realty_title; ?>" href="<?= BASE; ?>/imovel/<?= $realty_name; ?>"><?= Check::Chars($realty_title, 50); ?></a></h1>
        <?= '<p class="realty_transaction cs' . $realty_transaction . '">' . (is_string(getWcRealtyTransaction($realty_transaction)) ? getWcRealtyTransaction($realty_transaction) : '') . '</p>'; ?>
        <?= (!empty($realty_observation) ? "<p class='realty_observation'>" . (is_string(getWcRealtyNote($realty_observation)) ? getWcRealtyNote($realty_observation) : '') . "</p>" : ''); ?>
    </header>

    <div class="realty_item_content">
        <p class="built">
            Área: <b><?= $realty_builtarea; ?>m<sup>2</sup></b></p><p class="total">
            Total: <b><?= $realty_totalarea; ?>m<sup>2</sup></b></p><p class="bed">
            Quartos: <b><?= $realty_bedrooms; ?></b></p><p class="aparts">
            Suítes: <b><?= $realty_apartments; ?></b></p><p class="bat">
            Banheiros: <b><?= $realty_bathrooms; ?></b></p><p class="parking">
            Garagem: <b><?= $realty_parkings; ?></b></p>
    </div>

    <div class="realty_item_price">
        <?= ($realty_price ? "R$ " . number_format($realty_price, '2', ',', '.') : 'A combinar'); ?>
    </div>
</article>