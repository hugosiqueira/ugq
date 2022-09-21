<?php
$AdminLevel = LEVEL_WC_IMOBI;
if (!APP_IMOBI || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$RealtyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($RealtyId):
    $Read->ExeRead(DB_IMOBI, "WHERE realty_id = :id", "id={$RealtyId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um imóvel que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=imobi/home');
        exit;
    endif;
else:
    $ImobiCreate = ['realty_date' => date('Y-m-d H:i:s'), 'realty_status' => 0];
    $Create->ExeCreate(DB_IMOBI, $ImobiCreate);
    header('Location: dashboard.php?wc=imobi/create&id=' . $Create->getResult());
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-home3"><?= $realty_title ? $realty_title : 'Novo Imóvel'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=imobi/home">Imóveis</a>
            <span class="crumb">/</span>
            Gerenciar Imóvel
        </p>
    </div>

    <div class="dashboard_header_search">
        <a target="_blank" title="Ver no site" href="<?= BASE; ?>/imovel/<?= $realty_name; ?>" class="wc_view btn btn_green icon-eye">Ver imóvel no site!</a>
    </div>
</header>

<div class="dashboard_content">
    <form class="auto_save" name="realty_create" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Properties"/>
        <input type="hidden" name="callback_action" value="manager"/>
        <input type="hidden" name="realty_id" value="<?= $RealtyId; ?>"/>

        <div class="box box70">
            
            <div class="panel_header default">
                <h2 class="icon-home3">Dados do Imóvel:</h2>
            </div>
            
            <div class="panel">
                <label class="label">
                    <span class="legend">Título:</span>
                    <input style="font-size: 1.5em;" type="text" name="realty_title" value="<?= $realty_title; ?>" required/>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Referência:</span>
                        <input style="font-size: 1.5em;" type="text" name="realty_ref" value="<?= ($realty_ref ? $realty_ref : str_pad($RealtyId, 4, 0, 0)); ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Preço: (OPCIONAL)</span>
                        <input style="font-size: 1.5em;" type="text" name="realty_price" value="<?= ($realty_price ? number_format($realty_price, 2, ',', '.') : ''); ?>"/>
                    </label>
                </div>

                <?php if (APP_LINK_PROPERTIES): ?>
                    <label class="label">
                        <span class="legend">Link Alternativo (Opcional):</span>
                        <input type="text" name="realty_name" value="<?= $realty_name; ?>" placeholder="Link do Imóvel:"/>
                    </label>
                <?php endif; ?>

                <label class="label">
                    <span class="legend">Capa: (JPG <?= IMAGE_W; ?>x<?= IMAGE_H; ?>px)</span>
                    <input type="file" class="wc_loadimage" name="realty_cover"/>
                </label>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea class="work_mce_basic" name="realty_desc" rows="5"><?= $realty_desc; ?></textarea>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Finalidade:</span>
                        <select name="realty_finality" required>
                            <option value="">Selecione a finalidade:</option>
                            <?php
                            foreach (getWcRealtyFinality() as $FinId => $FinValue):
                                echo "<option " . ($realty_finality == $FinId ? "selected='selected'" : null) . " value='{$FinId}'>{$FinValue}</option>";
                            endforeach;
                            ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Tipo de imóvel:</span>
                        <select name="realty_type" required>
                            <option value="">Selecione o tipo de imóvel:</option>
                            <?php
                            foreach (getWcRealtyType() as $TypesId => $TypeName):
                                echo "<option " . ($realty_type == $TypesId ? "selected='selected'" : null) . " value='{$TypesId}'>{$TypeName}</option>";
                            endforeach;
                            ?>
                        </select>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Área Construída: (M<sup>2</sup>)</span>
                        <input type="text" name="realty_builtarea" value="<?= $realty_builtarea; ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Área Total: (M<sup>2</sup>)</span>
                        <input type="text" name="realty_totalarea" value="<?= $realty_totalarea; ?>" required/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Dormitórios:</span>
                        <input type="number" min="0" max="100" name="realty_bedrooms" value="<?= $realty_bedrooms; ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Suítes:</span>
                        <input type="number" min="0" max="100" name="realty_apartments" value="<?= $realty_apartments; ?>" required/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Banheiros:</span>
                        <input type="number" min="0" max="100" name="realty_bathrooms" value="<?= $realty_bathrooms; ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">Vagas na Garagem:</span>
                        <input type="number" min="0" max="100" name="realty_parkings" value="<?= $realty_parkings; ?>" required/>
                    </label>
                </div>

                <label class="label">
                    <span class="legend">Características: (separe com vírgula)</span>
                    <input type="text" name="realty_particulars" value="<?= $realty_particulars; ?>" required/>
                </label>
                <div class="clear"></div>
            </div>
        </div>

        <div class="box box30">
            <?php
            $RealtyCover = (!empty($realty_cover) && file_exists("../uploads/{$realty_cover}") && !is_dir("../uploads/{$realty_cover}") ? "uploads/{$realty_cover}" : 'admin/_img/no_image.jpg');
            ?>
            <img class="realty_cover" alt="Capa" title="Capa" src="../tim.php?src=<?= $RealtyCover; ?>&w=<?= IMAGE_W/3; ?>&h=<?= IMAGE_H/3; ?>" default="../tim.php?src=<?= $RealtyCover; ?>&w=<?= IMAGE_W/3; ?>&h=<?= IMAGE_H/3; ?>"/>
            <?php
            $Read->ExeRead(DB_IMOBI_GALLERY, "WHERE realty_id = :id", "id={$realty_id}");
            if ($Read->getResult()):
                echo '<div class="pdt_images gallery pdt_single_image">';
                foreach ($Read->getResult() as $Image):
                    $ImageUrl = ($Image['image'] && file_exists("../uploads/{$Image['image']}") && !is_dir("../uploads/{$Image['image']}") ? "../uploads/{$Image['image']}" : '_img/no_image.jpg');
                    echo "<img rel='Properties' id='{$Image['id']}' alt='Imagem em {$realty_title}' title='Imagem em {$realty_title}' src='{$ImageUrl}'/>";
                endforeach;
                echo '</div>';
            else:
                echo '<div class="pdt_images gallery pdt_single_image"></div>';
            endif;
            ?>
            <div class="panel" style="margin-bottom: 15px;">
                <label class="label">
                    <span class="legend">Fotos Adicionais (JPG <?= IMAGE_W; ?>x<?= IMAGE_H; ?>px):</span>
                    <input type="file" name="image[]" multiple/>
                </label>

                <label class="label">
                    <span class="legend">Transação:</span>
                    <select name="realty_transaction" required>
                        <option value="">Selecione o tipo de transação:</option>
                        <?php
                        foreach (getWcRealtyTransaction() as $TransId => $TransValue):
                            echo "<option " . ($realty_transaction == $TransId ? "selected='selected'" : null) . " value='{$TransId}'>{$TransValue}</option>";
                        endforeach;
                        ?>
                    </select>
                </label>

                <label class="label">
                    <?php
                    $Read->FullRead("SELECT realty_state FROM " . DB_IMOBI . " GROUP BY realty_state ORDER BY realty_state ASC");
                    if ($Read->getResult()):
                        echo '<datalist id="realty_state">';
                        foreach ($Read->getResult() as $RealTyState):
                            echo "<option value='{$RealTyState['realty_state']}'></option>";
                        endforeach;
                        echo '</datalist>';
                    endif;
                    ?>
                    <span class="legend">UF - Estado:</span>
                    <input type="text" maxlength="2" list="realty_state" name="realty_state" value="<?= $realty_state; ?>" required/>
                </label>

                <label class="label">
                    <span class="legend">Cidade:</span>
                    <?php
                    $Read->FullRead("SELECT realty_city FROM " . DB_IMOBI . " GROUP BY realty_city ORDER BY realty_city ASC");
                    if ($Read->getResult()):
                        echo '<datalist id="realty_city">';
                        foreach ($Read->getResult() as $RealTyCity):
                            echo "<option value='{$RealTyCity['realty_city']}'></option>";
                        endforeach;
                        echo '</datalist>';
                    endif;
                    ?>
                    <input type="text" name="realty_city" list="realty_city" value="<?= $realty_city; ?>" required/>
                </label>

                <label class="label">
                    <?php
                    $Read->FullRead("SELECT realty_district FROM " . DB_IMOBI . " GROUP BY realty_district ORDER BY realty_district ASC");
                    if ($Read->getResult()):
                        echo '<datalist id="realty_district">';
                        foreach ($Read->getResult() as $RealTyDistrict):
                            echo "<option value='{$RealTyDistrict['realty_district']}'></option>";
                        endforeach;
                        echo '</datalist>';
                    endif;
                    ?>
                    <span class="legend">Bairro:</span>
                    <input type="text" name="realty_district" list="realty_district" value="<?= $realty_district; ?>" required/>
                </label>
            </div>

            <div class="panel_header default">
                <h2 class="icon-pushpin">Publicar:</h2>
            </div>

            <div class="panel">
                <label class="label">
                    <span class="legend">DIA:</span>
                    <input type="text" class="formTime" name="realty_date" value="<?= $realty_date ? date('d/m/Y H:i', strtotime($realty_date)) : date('d/m/Y H:i'); ?>" required/>
                </label>

                <label class="label">
                    <span class="legend">Observação: (OPCIONAL)</span>
                    <select name="realty_observation">
                        <option value="">Selecione uma nota:</option>
                        <?php
                        foreach (getWcRealtyNote() as $NoteId => $NoteName):
                            echo "<option " . ($realty_observation == $NoteId ? "selected='selected'" : null) . " value='{$NoteId}'>{$NoteName}</option>";
                        endforeach;
                        ?>
                    </select>
                </label>

                <label class="label">
                    <span class="legend">Corretor:</span>
                    <select name="realty_contact" required>
                        <option value="<?= $Admin['user_id']; ?>"><?= $Admin['user_name']; ?> <?= $Admin['user_lastname']; ?></option>
                        <?php
                        $Read->FullRead("SELECT user_id, user_name, user_lastname FROM " . DB_USERS . " WHERE user_level >= :lv AND user_id != :uid", "lv=7&uid={$Admin['user_id']}");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $PostAuthors):
                                echo "<option";
                                if ($PostAuthors['user_id'] == $post_author):
                                    echo " selected='selected'";
                                endif;
                                echo " value='{$PostAuthors['user_id']}'>{$PostAuthors['user_name']} {$PostAuthors['user_lastname']}</option>";
                            endforeach;
                        endif;
                        ?>
                    </select>
                </label>

                <div class="m_top">&nbsp;</div>
                <div class="wc_actions" style="text-align: center">
                    <label class="label_check label_publish <?= ($realty_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="realty_status" <?= ($realty_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                    <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
                <?php
                $URLSHARE = "/imovel/{$realty_name}";
                require '_tpl/Share.wc.php';
                ?>
            </div>
        </div>
    </form>
</div>