<?php

session_start();

$getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (empty($getPost) || empty($getPost['workcontrol'])):
    die('Acesso Negado!');
endif;

$strPost = array_map('strip_tags', $getPost);
$POST = array_map('trim', $strPost);

$Action = $POST['workcontrol'];
$jSON = null;
unset($POST['workcontrol']);

require '../../../_app/Config.inc.php';
$Read = new Read;
$Create = new Create;
$Update = new Update;

switch ($Action):
    case 'transaction':
        $jSON['type'] = null;
        $_SESSION['wc_imobi_filter'] = array();
        $_SESSION['wc_imobi_filter']['realty_transaction'] = $POST['transaction'];

        $Read->FullRead("SELECT realty_type FROM " . DB_IMOBI . " WHERE realty_transaction = :tra GROUP BY realty_type ORDER BY realty_type ASC", "tra={$POST['transaction']}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $TRA):
                $jSON['type'] .= "<option value='{$TRA['realty_type']}'>" . getWcRealtyType($TRA['realty_type']) . "</option>";
            endforeach;
        endif;
        break;

    case 'type':
        $jSON['finality'] = null;
        $_SESSION['wc_imobi_filter']['realty_type'] = $POST['type'];

        $Read->FullRead("SELECT realty_finality FROM " . DB_IMOBI . " WHERE realty_transaction = :tra AND realty_type = :typ GROUP BY realty_finality ORDER BY realty_finality ASC", "tra={$POST['transaction']}&typ={$POST['type']}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $TRA):
                $jSON['finality'] .= "<option value='{$TRA['realty_finality']}'>" . getWcRealtyFinality($TRA['realty_finality']) . "</option>";
            endforeach;
        endif;
        break;

    case 'finality':
        $jSON['district'] = null;
        $_SESSION['wc_imobi_filter']['realty_finality'] = $POST['finality'];

        $Read->FullRead("SELECT realty_district FROM " . DB_IMOBI . " WHERE realty_transaction = :tra AND realty_type = :typ AND realty_finality = :fin GROUP BY realty_district ORDER BY realty_district ASC", "tra={$POST['transaction']}&typ={$POST['type']}&fin={$POST['finality']}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $TRA):
                $jSON['district'] .= "<option value='{$TRA['realty_district']}'>{$TRA['realty_district']}</option>";
            endforeach;
        endif;
        break;

    case 'district':
        $jSON['bedrooms'] = null;
        $_SESSION['wc_imobi_filter']['realty_district'] = $POST['district'];

        $Read->FullRead("SELECT realty_bedrooms FROM " . DB_IMOBI . " WHERE realty_transaction = :tra AND realty_type = :typ AND realty_finality = :fin AND realty_district = :dis GROUP BY realty_bedrooms ORDER BY realty_bedrooms ASC", "tra={$POST['transaction']}&typ={$POST['type']}&fin={$POST['finality']}&dis={$POST['district']}");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $TRA):
                $jSON['bedrooms'] .= "<option value='{$TRA['realty_bedrooms']}'>A partir de {$TRA['realty_bedrooms']} quarto(s)</option>";
            endforeach;
        endif;
        break;

    case 'bedrooms':
        $jSON['min_price'] = null;
        $_SESSION['wc_imobi_filter']['realty_bedrooms'] = $POST['bedrooms'];

        $Read->FullRead("SELECT realty_price FROM " . DB_IMOBI . " WHERE realty_transaction = :tra AND realty_type = :typ AND realty_finality = :fin AND realty_district = :dis AND realty_bedrooms = :bed ORDER BY realty_price DESC LIMIT 1", "tra={$POST['transaction']}&typ={$POST['type']}&fin={$POST['finality']}&dis={$POST['district']}&bed={$POST['bedrooms']}");
        $MaxPrice = (!empty($Read->getResult()[0]['realty_price']) ? $Read->getResult()[0]['realty_price'] : $POST['min_price']);
        for ($Min = 100; $Min < $MaxPrice; $Min = $Min * 10) {
            $jSON['min_price'] .= "<option value='{$Min}'>A partir de R$ " . number_format($Min, '2', ',', '.') . "</option>";
        }
        break;

    case 'min_price':
        $jSON['max_price'] = null;
        $_SESSION['wc_imobi_filter']['min_price'] = $POST['min_price'];

        $Read->FullRead("SELECT realty_price FROM " . DB_IMOBI . " WHERE realty_transaction = :tra AND realty_type = :typ AND realty_finality = :fin AND realty_district = :dis AND realty_bedrooms = :bed AND realty_price >= :prc ORDER BY realty_price ASC LIMIT 1", "tra={$POST['transaction']}&typ={$POST['type']}&fin={$POST['finality']}&dis={$POST['district']}&bed={$POST['bedrooms']}&prc={$POST['min_price']}");
        $MinPrice = (!empty($Read->getResult()[0]['realty_price']) ? $Read->getResult()[0]['realty_price'] : $POST['min_price']);
        for ($Min = 10000000; $Min > $MinPrice; $Min = $Min / 10):
            $jSON['max_price'] .= "<option value='{$Min}'>Até R$ " . number_format($Min, '2', ',', '.') . "</option>";
        endfor;
        break;

    case 'max_price':
        $_SESSION['wc_imobi_filter']['max_price'] = $POST['max_price'];
        break;
endswitch;

if (!empty($jSON)):
    echo json_encode($jSON);
endif;
