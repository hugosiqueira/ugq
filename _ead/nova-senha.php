<?php
if (!$Read):
    $Read = new Read;
endif;

//CHECK TIME FOR NEW PASS
$RecoverTime = filter_input(INPUT_GET, 't', FILTER_DEFAULT);
if (empty($RecoverTime) || base64_decode($RecoverTime) < time()):
    $_SESSION['recover_end'] = true;
    header("Location: " . BASE . "/campus/senha");
    exit;
endif;

//CHECK USER DATA
$RecoverMail = filter_input(INPUT_GET, 'm', FILTER_DEFAULT);
$RecoverPass = filter_input(INPUT_GET, 'p', FILTER_DEFAULT);

if (!$RecoverMail || !$RecoverPass):
    $_SESSION['recover_end'] = true;
    header("Location: " . BASE . "/campus/senha");
    exit;
else:
    //CHECK USER BY DB
    $RecoverMailCheck = base64_decode($RecoverMail);
    $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :mail AND user_password = :pass", "mail={$RecoverMailCheck}&pass={$RecoverPass}");
    if (!$Read->getResult()):
        $_SESSION['recover_end'] = true;
        header("Location: " . BASE . "/campus/senha");
        exit;
    else:
        //SET RECOVER DATA FOR UPDATE
        $_SESSION['wc_recover_password'] = array();
        $_SESSION['wc_recover_password']['user_time'] = base64_decode($RecoverTime);
        $_SESSION['wc_recover_password']['user_email'] = $RecoverMailCheck;
        $_SESSION['wc_recover_password']['user_email'] = $RecoverMailCheck;
        $_SESSION['wc_recover_password']['user_password'] = $RecoverPass;
    endif;
endif;
?>
<div class="wc_ead_enter" style="min-height: 420px;">
    <div class="wc_ead_content">
        <article class="box wc_ead_login_single wc_ead_login">
            <header>
                <h1 class="icon-key2">Criar nova senha:</h1>
                <p>Cadastre uma nova senha para acessar sua conta!</p>
            </header>

            <form name="wc_ead_password_change" action="" method="post">
                <label>
                    <span class="text">E-mail:</span>
                    <input type="email" name="" value="<?= $RecoverMailCheck; ?>" readonly="readonly" placeholder="Informe seu e-mail:" required/>
                </label>
                <label>
                    <span class="text">Nova Senha:</span>
                    <input type="password" name="user_password" placeholder="Informe a nova senha:" required/>
                </label>
                <label>
                    <span class="text">Repetir Nova Senha:</span>
                    <input type="password" name="user_password_re" placeholder="Informe a nova senha novamente:" required/>
                </label>
                <div class="wc_ead_enter_actions">
                    <a class="wc_ead_login_recover" href="<?= BASE; ?>/campus">Logar-se!</a>
                    <button class="btn btn_blue icon-key">Atualizar Minha Senha!</button>
                    <img class="jwc_load" src="<?= BASE; ?>/_ead/images/load.gif" alt="Efetuando Login!" title="Efetuando Login!"/>
                </div>
            </form>
        </article>
    </div>
</div>