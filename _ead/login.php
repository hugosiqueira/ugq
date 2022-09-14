<?php
if (!empty($_SESSION['ead_logoff'])):
    ?>
    <div class="wc_ead_alert" style="display: flex;">
        <div class="wc_ead_alert_box blue">
            <div class="wc_ead_alert_icon icon-switch icon-exit"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">Volte logo <?= $_SESSION['ead_logoff']; ?>,</p>
                <p class="wc_ead_alert_content">Sua conta foi desconectada com sucesso!</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>
    <?php
    unset($_SESSION['ead_logoff']);
endif;

if (!empty($URL[2]) && $URL[2] == 'restrito'):
    ?>
    <div class="wc_ead_alert" style="display: flex;">
        <div class="wc_ead_alert_box red">
            <div class="wc_ead_alert_icon icon-switch"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">ACESSO NEGADO</p>
                <p class="wc_ead_alert_content">Não foi possível identificar seu login :/</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>
    <?php
endif;

if (!empty($URL[2]) && $URL[2] == 'multiple'):
    ?>
    <div class="wc_ead_alert" style="display: flex;">
        <div class="wc_ead_alert_box red">
            <div class="wc_ead_alert_icon icon-switch"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">Oppsss. Você foi desconectado!</p>
                <p class="wc_ead_alert_content">Sua conta foi conectada por outra pessoa ou dispositivo!</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>
    <?php
endif;

if (!empty($URL[2]) && $URL[2] == 'register'):
    ?>
    <div class="wc_ead_alert" style="display: flex;">
        <div class="wc_ead_alert_box yellow">
            <div class="wc_ead_alert_icon icon-switch"></div><div class="wc_ead_alert_text">
                <p class="wc_ead_alert_title">ERRO AO ATIVAR CONTA:</p>
                <p class="wc_ead_alert_content">Desculpe, mas não foi possível validar o email de ativação! :/</p>
            </div><div class="wc_ead_alert_close"><span class="icon-cross icon-notext"></span></div>
        </div>
    </div>
    <?php
endif;
?>
<div class="wc_ead_enter">
    <div class="wc_ead_content">
        <article class="box <?= EAD_REGISTER ? 'box2' : 'wc_ead_login_single'; ?> wc_ead_login">
            <header>
                <h1 class="icon-user">Já sou aluno:</h1>
                <p>Informe seu e-mail e senha para efetuar login!</p>
            </header>
            <form name="wc_ead_login" action="" method="post">
                <label>
                    <span class="text">E-mail:</span>
                    <input type="email" name="user_email" placeholder="Informe seu e-mail:" required/>
                </label>
                <label>
                    <span class="text">Senha:</span>
                    <input type="password" name="user_password" placeholder="Informe sua senha:" required/>
                </label>
                <div class="wc_ead_enter_actions">
                    <a class="wc_ead_login_recover" href="<?= BASE; ?>/campus/senha">Esqueci minha Senha!</a>
                    <button class="btn btn_blue icon-share">Entrar</button>
                    <img class="jwc_load" src="<?= BASE; ?>/_ead/images/load.gif" alt="Efetuando Login!" title="Efetuando Login!"/>
                </div>
            </form>
        </article><?php if (EAD_REGISTER): ?><article class="box box2 wc_ead_register">
                <header>
                    <h1 class="icon-user-plus">Ainda não sou aluno:</h1>
                    <p>Preencha os dados abaixo para criar sua conta!</p>
                </header>
                <form name="wc_ead_register" action="" method="post">
                    <label>
                        <span class="text">Nome:</span>
                        <input type="text" name="user_name" placeholder="Informe seu nome:" required/>
                    </label>
                    <label>
                        <span class="text">Sobrenome:</span>
                        <input type="text" name="user_lastname" placeholder="Informe sobrenome:" required/>
                    </label>
                    <label>
                        <span class="text">E-mail:</span>
                        <input type="email" name="user_email" placeholder="Informe seu e-mail:" required/>
                    </label>
                    <div class="wc_ead_enter_actions">
                        <button class="btn btn_green icon-new-tab">Quero Me Cadastrar!</button>
                        <img class="jwc_load" src="<?= BASE; ?>/_ead/images/load.gif" alt="Efetuando Cadastro!" title="Efetuando Cadastro!"/>
                    </div>
                </form>
            </article>
        <?php endif; ?>
    </div>
</div>