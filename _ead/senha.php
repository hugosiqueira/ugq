<?php
if (!empty($_SESSION['recover_end'])):
    ?>
    <div class="wc_ead_alert" style="display: flex;">
        <div class="wc_ead_alert_box yellow">
            <div class="wc_ead_alert_icon icon-switch icon-notext"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">LINK EXPIRADO OU INVÁLIDO:</p>
                <p class="wc_ead_alert_content">O link de recuperação não é mais válido. Você pode gerar outro!</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>
    <?php
    unset($_SESSION['recover_end']);
endif;
?>
<div class="wc_ead_enter" style="min-height: 420px;">
    <div class="wc_ead_content">
        <article class="box wc_ead_login_single wc_ead_login">
            <header>
                <h1 class="icon-key">Recuperar Senha:</h1>
                <p>Informe seu e-mail para receber as intruções!</p>
            </header>

            <form name="wc_ead_password" action="" method="post">
                <label>
                    <span class="text">E-mail:</span>
                    <input type="email" name="user_email" placeholder="Informe seu e-mail:" required/>
                </label>
                <div class="wc_ead_enter_actions">
                    <a class="wc_ead_login_recover" href="<?= BASE; ?>/campus">Logar-se!</a>
                    <button class="btn btn_blue icon-mail4">Enviar Senha</button>
                    <img class="jwc_load" src="<?= BASE; ?>/_ead/images/load.gif" alt="Efetuando Login!" title="Efetuando Login!"/>
                </div>
            </form>
        </article>
    </div>
</div>