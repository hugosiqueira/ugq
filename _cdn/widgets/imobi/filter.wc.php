<link rel="stylesheet" href="<?= BASE; ?>/_cdn/widgets/imobi/filter.wc.css"/>
<script src="<?= BASE; ?>/_cdn/widgets/imobi/filter.wc.js"></script>

<article class="wc_imobi_filter">
    <div class="wc_mobile_filter"><span class="bar">&#9776;</span><span>FILTRAR IMÓVEIS</span></div>
    <div class="content">
        <h1>Encontre Seu Imóvel:</h1>
        <form class="wc_imobi_filter_form" name="wc_imobi_filter" action="" method="post" enctype="multipart/form-data">
            <select name="transaction">
                <option value="">Transação:</option>
                <?php
                foreach (getWcRealtyTransaction() as $TransId => $TransValue):
                    echo "<option value='{$TransId}'>{$TransValue}</option>";
                endforeach;
                ?>
            </select><select name="type">
                <option value="">Tipo de imóvel</option>
            </select><select name="finality">
                <option value="">Finalidade</option>
            </select><select name="district">
                <option value="">Bairro</option>
            </select><select name="bedrooms">
                <option value="">Dormitórios</option>
            </select><select name="min_price">
                <option value="">Valor mínimo</option>
            </select><select name="max_price">
                <option value="">Valor máximo</option>
            </select><a class="btn btn_green" href="<?= BASE; ?>/filtro" title="Filtrar Imóveis">BUSCAR!</a>
        </form>
        <div class="clear"></div>
    </div>
</article>