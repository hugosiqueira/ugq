<?php
if (!class_exists('Read')):
    header('Location:../campus');
    exit;
endif;

//READ CLASS
if (empty($Read)):
    $Read = new Read;
endif;

//GET URL
$EadNav = (!empty($URL[1]) ? strip_tags($URL[1]) : null);

//EAD LOGIN
if (empty($_SESSION['userLogin']) && empty($EadNav)):
    require 'login.php';
else:
    //GET NAVIGATION
    if ($EadNav && file_exists("_ead/{$EadNav}.php")):
        require "{$EadNav}.php";
    else:
        require 'home.php';
    endif;
endif;

$WcEadLogOff = filter_input(INPUT_GET, "sair", FILTER_VALIDATE_BOOLEAN);
if ($WcEadLogOff && !empty($_SESSION['userLogin'])):

    $UpdateDataLogOff = ['user_login' => null];
    $Update = new Update;
    $Update->ExeUpdate(DB_USERS, $UpdateDataLogOff, "WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");

    $_SESSION['ead_logoff'] = $_SESSION['userLogin']['user_name'];
    unset($_SESSION['userLogin']);
    header('Location: ' . BASE . "/campus");
    exit;
endif;
?>

<!--BASE ALERT-->
<div class="wc_ead_alert">
    <div class="wc_ead_alert_box">
        <div class="wc_ead_alert_icon icon-switch icon-notext"></div><div class="wc_ead_alert_text">
            <p class="wc_ead_alert_title">{TITLE}</p>
            <p class="wc_ead_alert_content">{CONTENT}</p>
        </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
    </div>
</div>

<!--BASE MODAL-->
<div class="wc_ead_modal">
    <div class="wc_ead_modal_box">
        <span class="wc_ead_modal_close icon-cross icon-notext"></span>
        <p class="wc_ead_modal_title"><span>{TITLE}</span></p>
        <div class="wc_ead_modal_content">{CONTENT}</div>
        <div class="wc_ead_modal_help">Precisa de ajuda? <a href="mailto:<?= SITE_ADDR_EMAIL; ?>" title="Enviar E-mail!">Entre em contato!</a></div>
    </div>
</div>

<!--UPLOAD MODAL-->
<div class="wc_ead_upload jwc_ead_upload">
    <div class="wc_ead_upload_progress jwc_ead_upload_progress">0%</div>
</div>

<!--LOAD MODAL-->
<div class="wc_ead_load jwc_ead_load">
    <div class="wc_ead_load_content">
        <img src="<?= BASE; ?>/_ead/images/load_w.gif" alt="Aguarde, enviando solicitação!" width="50" title="Aguarde, enviando solicitação!"/>
        <p class="wc_ead_load_content_msg">Aguarde, enviando solicitação!</p>
    </div>
</div>

<script src="<?= BASE; ?>/_ead/wc_ead.post.js"></script>
<script src="<?= BASE; ?>/_cdn/jquery.form.js"></script>
