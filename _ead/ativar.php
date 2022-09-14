<?php
if (!EAD_REGISTER):
    header("Location: " . BASE . "/campus/");
    exit;
endif;

//GET CODE REGISTER
$wcActiveCode = (!empty($URL[2]) ? $URL[2] : null);
if (!$wcActiveCode):
    header("Location: " . BASE . "/campus/login/register");
    exit;
endif;

//GET USER DATA
$user_name = explode("&", base64_decode($wcActiveCode))[0];
$user_lastname = explode("&", base64_decode($wcActiveCode))[1];
$user_email = explode("&", base64_decode($wcActiveCode))[2];

//VALIDATE MAIL
if (!Check::Email($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)):
    header("Location: " . BASE . "/campus/login/register");
    exit;
endif;
?>
<section class="wc_ead_content">
    <header class="wc_ead_header_center">
        <h1 class="icon-checkmark">Ative Sua Conta:</h1>
        <p>Complete seu cadastro para ativar e entrar!</p>
    </header>
    <div class="wc_ead_enter">
        <form autocomplete="off" name="wc_ead_register_create" class="" action="" method="post">
            <input type="hidden" name="user_email" value="<?= $user_email; ?>"/>
            <article class="box box2 wc_ead_login">
                <header>
                    <h1 class="icon-user">Meus Dados:</h1>
                    <p>Seus dados pessoas de identificação!</p>
                </header>
                <label>
                    <span class="text">Nome:</span>
                    <input type="text" name="user_name" value="<?= $user_name; ?>" placeholder="Informe seu nome:" required=""/>
                </label>
                <label>
                    <span class="text">Sobrenome:</span>
                    <input type="text" name="user_lastname" value="<?= $user_lastname; ?>" placeholder="Informe sobrenome:" required=""/>
                </label>
                <label>
                    <span class="text">Gênero:</span>
                    <select name="user_genre" required>
                        <option value="">Selecione:</option>
                        <option value="1">Masculino</option>
                        <option value="2">Feminino</option>
                    </select>
                </label>
            </article><article class="box box2 wc_ead_login">
                <header>
                    <h1 class="icon-key2">Meus Login:</h1>
                    <p>Seu dados de acesso a plataforma!</p>
                </header>
                <label>
                    <span class="text">E-mail:</span>
                    <input type="email" name="" value="<?= $user_email; ?>" readonly="readonly" placeholder="Informe seu e-mail:" required=""/>
                </label>

                <label>
                    <span class="text">Senha:</span>
                    <input type="password" name="user_password" placeholder="Informe sua senha:" required=""/>
                </label>
                <div class="wc_ead_enter_actions">
                    <button class="btn btn_blue icon-checkmark">Ativar Minha Conta Agora</button>
                    <img class="jwc_load" src="<?= BASE; ?>/_ead/images/load.gif" alt="Efetuando Login!" title="Efetuando Login!"/>
                </div>
            </article>
        </form>
    </div>
</section>
