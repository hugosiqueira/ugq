<?php

ob_start();
session_start();

require '../_app/Config.inc.php';

$Class = filter_input(INPUT_GET, 'f', FILTER_VALIDATE_INT);
$MsgAccess = "Desculpe, acesso restrito a alunos do curso!";
$NotAccess = "<div style='position: absolute; left: 0; top: 0; width: 100%; height: 100%; display: flex; text-align: center; background: #BE2B12; color: #fff;'><div style='margin: auto; text-transform: uppercase; font-weight: bold; text-shadow: 0 1px #000;'><p style='font-size: 5em; margin: 0;'>&#9888;</p>{$MsgAccess}</div></div>";

if (!file_exists("../uploads/courses/material/.htaccess")):
    $FileAccess = fopen("../uploads/courses/material/.htaccess", "w");
    fwrite($FileAccess, "RewriteEngine On\r\nRewriteRule ^(.*)$ ../../../\r\nOptions All -Indexes");
    fclose($FileAccess);
endif;

if (empty($Class) || empty($_SESSION['userLogin'])):
    echo "<meta charset='utf-8'/><title>" . SITE_NAME . " Downloads!</title>";
    echo $NotAccess;
    exit;
else:
    $Read = new Read;
    $Read->FullRead("SELECT "
            . "c.class_id, "
            . "c.class_material, "
            . "e.user_id ,"
            . "e.course_id "
            . "FROM " . DB_EAD_CLASSES . " c "
            . "INNER JOIN " . DB_EAD_ENROLLMENTS . " e ON c.course_id = e.course_id "
            . "INNER JOIN " . DB_EAD_STUDENT_CLASSES . " sc ON c.class_id = sc.class_id "
            . "WHERE e.user_id = :user AND c.class_id = :class AND sc.user_id = :user", "user={$_SESSION['userLogin']['user_id']}&class={$Class}"
    );

    if (!$Read->getResult()):
        echo "<meta charset='utf-8'/><title>" . SITE_NAME . " Downloads!</title>";
        $MsgAccess = "Oops, arquivo não existe ou ainda não está liberado!";
        $NotAccess = "<div style='position: absolute; left: 0; top: 0; width: 100%; height: 100%; display: flex; text-align: center; background: #BE2B12; color: #fff;'><div style='margin: auto; text-transform: uppercase; font-weight: bold; text-shadow: 0 1px #000;'><p style='font-size: 5em; margin: 0;'>&#9888;</p>{$MsgAccess}</div></div>";
        echo $NotAccess;
        exit;
    else:
        extract($Read->getResult()[0]);
        $DownloadFile = "../uploads/{$class_material}";

        $CreateStudentDownload = [
            "user_id" => $user_id,
            "user_ip" => $_SERVER['REMOTE_ADDR'],
            "course_id" => $course_id,
            "class_id" => $class_id,
            "download_file" => BASE . "/uploads/{$class_material}",
            "download_filename" => substr(strrchr($class_material, "/"), 1),
            "download_date" => date("Y-m-d H:i:s")
        ];

        $Create = new Create;
        $Create->ExeCreate(DB_EAD_STUDENT_DOWNLOADS, $CreateStudentDownload);

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
endif;

ob_end_flush();
