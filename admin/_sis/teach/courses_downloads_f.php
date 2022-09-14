<?php

ob_start();
session_start();

require '../../../_app/Config.inc.php';

$Class = filter_input(INPUT_GET, 'f', FILTER_VALIDATE_INT);
$MsgAccess = "Desculpe, acesso restrito a administradores!";
$NotAccess = "<div style='position: absolute; left: 0; top: 0; width: 100%; height: 100%; display: flex; text-align: center; background: #BE2B12; color: #fff;'><div style='margin: auto; text-transform: uppercase; font-weight: bold; text-shadow: 0 1px #000;'><p style='font-size: 5em; margin: 0;'>&#9888;</p>{$MsgAccess}</div></div>";

if (empty($Class) || empty($_SESSION['userLogin']) || $_SESSION['userLogin']['user_level'] < 6):
    echo "<meta charset='utf-8'/><title>" . SITE_NAME . " Downloads!</title>";
    echo $NotAccess;
    exit;
else:

    $Read = new Read;
    $Read->FullRead("SELECT class_material FROM " . DB_EAD_CLASSES . " WHERE class_id = :id", "id={$Class}");

    $DownloadFile = "../../../uploads/{$Read->getResult()[0]['class_material']}";

    if (file_exists($DownloadFile) && !is_dir($DownloadFile)):
        header('Content-type: octet/stream');
        header('Content-disposition: attachment; filename="' . basename($DownloadFile) . '";');
        header('Content-Length: ' . filesize($DownloadFile));
        readfile($DownloadFile);
        exit;
    else:
        echo "<meta charset='utf-8'/><title>" . SITE_NAME . " Downloads!</title>";
        $MsgAccess = "Desculpe, você tentou baixar um arquivo que não existe!";
        $NotAccess = "<div style='position: absolute; left: 0; top: 0; width: 100%; height: 100%; display: flex; text-align: center; background: #000; color: #fff;'><div style='margin: auto; text-transform: uppercase; font-weight: bold; text-shadow: 0 1px #000;'><p style='font-size: 5em; margin: 0;'>&#9888;</p>{$MsgAccess}</div></div>";
        echo $NotAccess;
    endif;
endif;

ob_end_flush();
