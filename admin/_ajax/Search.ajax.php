<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_POSTS;

if (empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Search';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);

    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

    // AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

    // AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;

    // AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        //STATS
        case 'publish':
            $Publish = ['search_publish' => 1];
            $Update->ExeUpdate(DB_SEARCH, $Publish, "WHERE search_id = :search", "search={$PostData['key']}");
            $jSON['redirect'] = 'dashboard.php?wc=home';
            $jSON['trigger'] = AjaxErro("<b class='icon-warning'>Pesquisa Publicada:</b>Esse termo de pesquisa foi publicado com sucesso!");
            break;
        
        //STATS
        case 'delete':
            $Delete->ExeDelete(DB_SEARCH, "WHERE search_id = :search", "search={$PostData['key']}");
            $jSON['redirect'] = 'dashboard.php?wc=home';
            $jSON['trigger'] = AjaxErro("<b class='icon-warning'>Pesquisa Removida:</b>Esse termo de pesquisa foi removido com sucesso!");
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente. Ao persistir, contate o desenvolvedor!', E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
