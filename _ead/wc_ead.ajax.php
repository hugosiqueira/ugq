<?php

ob_start();
session_start();

require '../_app/Config.inc.php';

$Read = new Read;
$Update = new Update;
$Create = new Create;
$Delete = new Delete;

$jSON = null;
$POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);
unset($POST['user_level']);

if ($POST && $POST['callback']):
    //PRESERVE TINYMCE
    $TinyText = (!empty($POST['ticket_content']) ? $POST['ticket_content'] : null);

    //STRIP SCRIPTS
    $DataStrip = array_map("strip_tags", $POST);

    //NORMALIZE TINYTEXT
    if ($TinyText):
        $DataStrip['ticket_content'] = $TinyText;
    endif;

    //REMOVE W SPACES
    $DataTrim = array_map("trim", $DataStrip);
    $DataRTrim = array_map("rtrim", $DataTrim);

    //MAKE Callback
    $Callback = $DataRTrim['callback'];
    unset($DataRTrim['callback']);

    //MAKE DATA
    $DATA = $DataRTrim;

    /*
     * EAD RETURN
     * MODAL: $jSON['modal'] = ["Color", "Icon", "Title", "Content"];
     * ALERT: $jSON['alert'] = ["Color", "Title", "Content"];
     */
    switch ($Callback):
        //LOGIN
        case 'wc_ead_login':
            $LoginTrue = false;
            $_SESSION['userLogin'] = null;

            if (in_array("", $DATA)):
                $jSON['alert'] = ['red', 'Erro ao logar:', 'Favor informe seu e-mail e senha para logar!'];
            elseif (!Check::Email($DATA['user_email']) || !filter_var($DATA['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['alert'] = ['red', 'E-mail Inválido:', 'Favor informe seu e-mail cadastrado para logar!'];
            elseif (strlen($DATA['user_password']) < 5):
                $jSON['alert'] = ['red', 'Senha Inválida:', 'Sua senha deve ter no mínimo 5 caracteres!'];
            else:
                $Read->ExeRead(DB_USERS, "WHERE user_email = :email AND user_password = :pass", "email={$DATA['user_email']}&pass=" . hash("sha512", $DATA['user_password']));
                if (!$Read->getResult()):
                    $jSON['alert'] = ['red', 'Erro ao logar:', 'E-mail ou senha não conferem!'];
                else:
                    $UserLogin = $Read->getResult()[0];

                    if (!empty($UserLogin['user_blocking_reason'])):
                        unset($UserLogin);
                        $jSON['alert'] = ['red', 'Erro ao logar:', 'Sua conta está bloqueada! Entre em contato conosco através dos canais de atendimento para maiores detalhes.'];
                        break;
                    endif;

                    if (EAD_STUDENT_MULTIPLE_LOGIN):
                        $LoginTrue = true;
                    else:
                        $LoginCookieFree = filter_input(INPUT_COOKIE, "wc_ead_login", FILTER_DEFAULT);
                        $LoginTimeFree = (!empty($UserLogin['user_login']) ? $UserLogin['user_login'] + (EAD_STUDENT_MULTIPLE_LOGIN_BLOCK * 60) : 0);

                        if (!$UserLogin['user_login_cookie'] || time() > $LoginTimeFree || ($LoginCookieFree && $LoginCookieFree == $UserLogin['user_login_cookie'])):
                            $wc_ead_login_cookie = hash("sha512", time());
                            $_SESSION['userLogin']['user_login_cookie'] = $wc_ead_login_cookie;
                            setcookie('wc_ead_login', $wc_ead_login_cookie, time() + 2592000, '/');

                            $UpdateUserLogin = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time(), 'user_login_cookie' => $wc_ead_login_cookie];
                            $Update->ExeUpdate(DB_USERS, $UpdateUserLogin, "WHERE user_id = :user", "user={$UserLogin['user_id']}");

                            $LoginTrue = true;
                        else:
                            $jSON['modal'] = ['red', "warning", "Conta já conectada:", "<p>Olá {$UserLogin['user_name']}, sua <b>conta já esta conectada por outro dispositivo ou por outra pessoa.</b> Isso não é permitido em nossa plataforma!</p><p>Caso tenha efetuado login de outro dispositivo, você pode conectar por ele agora!</p><p><b class='font_red icon-hour-glass'>Ou espere até as " . date("H\hi", $LoginTimeFree + 60) . " para conectar novamente!</b></p><p><p class='icon-warning'>IMPORTANTE: Leia com atenção nossa regras para não ter sua conta bloqueada permanentemente!</p><p class='icon-info'>Caso alguem esteja conectado na sua conta o tempo de liberação deve aumentar!</p>"];
                        endif;
                    endif;

                    //LOGIN EFFECT
                    if ($LoginTrue):
                        $_SESSION['userLogin'] = $UserLogin;
                        $jSON['alert'] = ['green', "Seja bem vind" . ($UserLogin['user_genre'] == 1 ? 'o' : 'a') . " {$UserLogin['user_name']},", "Seu login foi efetuado com sucesso..."];
                        $jSON['redirect'] = BASE . "/campus";
                    endif;
                endif;
            endif;
            break;

        //STUDENT ACTIONS :: LOGIN FIX
        case 'wc_ead_login_fix':
            //MULTIPLE LOGIN
            if (!EAD_STUDENT_MULTIPLE_LOGIN):
                $wc_ead_login_cookie = filter_input(INPUT_COOKIE, "wc_ead_login", FILTER_DEFAULT);

                if (empty($_SESSION['userLogin']) || !$wc_ead_login_cookie):
                    unset($_SESSION['userLogin']);
                    $jSON['redirect'] = BASE . "/campus/login/multiple";
                else:
                    $Read->FullRead("SELECT user_login_cookie FROM " . DB_USERS . " WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
                    if (!$Read->getResult() || $_SESSION['userLogin']['user_login_cookie'] != $Read->getResult()[0]['user_login_cookie']):
                        unset($_SESSION['userLogin']);
                        $jSON['redirect'] = BASE . "/campus/login/multiple";
                    else:
                        setcookie('wc_ead_login', $Read->getResult()[0]['user_login_cookie'], time() + 2592000, '/');
                    endif;
                endif;
            endif;

            //LOGIN FIX
            $jSON['login'] = true;
            break;

        //REGISTRATION ACTIVE
        case 'wc_ead_register':
            if (in_array("", $DATA)):
                $jSON['alert'] = ['red', 'Erro ao Cadastrar:', 'Favor preencha todos os campos para criar sua conta!'];
            elseif (!Check::Email($DATA['user_email']) || !filter_var($DATA['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['alert'] = ['red', 'E-mail Inválido:', 'Favor informe um e-mail válido para se cadastrar!'];
            else:
                //READ USER_EMAIL
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$DATA['user_email']}");
                if ($Read->getResult()):
                    $jSON['alert'] = ['yellow', 'E-mail já cadastrado:', 'E-mail informado já cadastrado! <a href="' . BASE . '/campus/senha" title="Recuperar Senha">Recupere sua senha aqui!</a>'];
                else:
                    $wcActiveCode = base64_encode("{$DATA['user_name']}&{$DATA['user_lastname']}&{$DATA['user_email']}");

                    require './wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em;'>Prezado(a) {$DATA['user_name']},</p>
                        <p>Você está recebendo este e-mail pois iniciou seu cadastro em nossa plataforma de ensino.</p>
                        <p>Seja muito bem-vindo(a) a nossa escola online!</p>
                        <p><b>FALTA POUCO:</b> Para completar seu cadastro e ativar sua conta, basta seguir o link abaixo, e então preencher os últimos dados de ativação!</p>
                        <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/ativar/{$wcActiveCode}' title='Ativar Minha Conta Agora'>ATIVAR MINHA CONTA AGORA!</a></b></p>
                        <p>...</p>    
                        <p>Assim que completar seu perfil sua conta estará ativa, e você já pode desfrutar de nossos treinamentos.</p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a disposição!</p>
                        <p><em>Atenciosamente " . SITE_NAME . "!</em></p>
                    ";
                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("Ative sua conta {$DATA['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$DATA['user_name']} {$DATA['user_lastname']}", $DATA['user_email']);

                    $jSON['clear'] = true;
                    $jSON['modal'] = ['green', 'checkmark2', "Falta pouco {$DATA['user_name']}!", "<div class='al_center'><p>Bem-vindo(a) {$DATA['user_name']}, falta pouco!</p><p>Para completar seu cadastro e ativar sua conta, é preciso acessar seu e-mail e encontrar nossa mensagem de confirmação.</p><p><a class='btn btn_medium btn_green icon-share' target='_blank' href='//" . substr($DATA['user_email'], strrchr("@", $DATA['user_email'])) . "' title='Acessar meu webmail!'>Acessar meu webmail!</a></p><p style='font-size: 0.8em;'><b class='icon-warning font_red'>IMPORTANTE:</b> ACESSE SEU E-MAIL, ENCONTRE NOSSA MENSAGEM E CLIQUE EM <b>ATIVAR MINHA CONTA AGORA</b> PARA COMPLETAR SEU CADASTRO!</p></div>"];
                endif;
            endif;
            break;

        //USER REGISTRATION
        case 'wc_ead_register_create':
            if (in_array("", $DATA)):
                $jSON['alert'] = ['yellow', "Erro ao cadastrar {$DATA['user_name']},", 'Você precisa informar todos os dados para criar e ativar sua conta!'];
            elseif (!Check::Email($DATA['user_email']) || !filter_var($DATA['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['alert'] = ['red', 'E-mail Inválido:', 'Favor informe seu e-mail para criar e ativar sua conta!'];
            elseif (strlen($DATA['user_password']) < 5):
                $jSON['alert'] = ['red', 'Senha Inválida:', 'Sua senha deve ter no mínimo 5 caracteres!'];
            else:
                $Read->FullRead("SELECT user_id FROM " . DB_USERS . " WHERE user_email = :email", "email={$DATA['user_email']}");
                if ($Read->getResult()):
                    $jSON['alert'] = ['yellow', 'E-mail já cadastrado:', 'E-mail informado já cadastrado! <a href="' . BASE . '/campus/senha" title="Recuperar Senha">Recupere sua senha aqui!</a>'];
                else:
                    $DATA['user_registration'] = date("Y-m-d H:i:s");
                    $DATA['user_lastupdate'] = date("Y-m-d H:i:s");
                    $DATA['user_lastaccess'] = date("Y-m-d H:i:s");
                    $DATA['user_login'] = time();
                    $DATA['user_level'] = 1;
                    $DATA['user_password'] = hash("sha512", $DATA['user_password']);
                    $DATA['user_channel'] = "Ead Register";

                    $Create->ExeCreate(DB_USERS, $DATA);
                    $UserGenre = ($DATA['user_genre'] == 1 ? 'o' : 'a');

                    $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$Create->getResult()}");
                    $_SESSION['userLogin'] = $Read->getResult()[0];

                    //CLEAR ACTIVATION
                    if (!empty($_SESSION['user_registration'])):
                        unset($_SESSION['user_registration']);
                    endif;

                    $jSON['alert'] = ["green", "Seja bem-vind{$UserGenre} {$DATA['user_name']},", "Sua conta foi criada e ativada com sucesso!"];
                    $jSON['redirect'] = BASE . "/campus";
                endif;
            endif;
            break;

        //USER RECOVER PASSWORD SEND
        case 'wc_ead_password':
            if (empty($DATA['user_email'])):
                $jSON['alert'] = ['red', 'Erro ao recuperar:', 'Favor informe seu e-mail de acesso para recuperar a senha!'];
            elseif (!Check::Email($DATA['user_email']) || !filter_var($DATA['user_email'], FILTER_VALIDATE_EMAIL)):
                $jSON['alert'] = ['yellow', 'E-mail Inválido:', 'O e-mail informado parece não ter um formato válido!'];
            else:
                $Read->ExeRead(DB_USERS, "WHERE user_email = :email", "email={$DATA['user_email']}");
                if (!$Read->getResult()):
                    $jSON['alert'] = ['red', 'E-mail não cadastrado:', 'Caso não tenha conta ainda, você pode <a href="' . BASE . '/campus" title="Cadastrar-se Aqui">Cadastrar-se Aqui!</a>'];
                else:
                    $UserMail = base64_encode($Read->getResult()[0]['user_email']);
                    $UserPass = $Read->getResult()[0]['user_password'];
                    $UserTime = base64_encode(time() + 3600);
                    $RecoverLink = "&m={$UserMail}&p={$UserPass}&t={$UserTime}";

                    require './wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em;'>Prezado(a) {$Read->getResult()[0]['user_name']},</p>
                        <p>Foi solicitado uma recuperação de senha para seu e-mail em nossa plataforma. Caso queira recuperar sua senha, basta clicar no link abaixo:</p>
                        <p style='font-size: 1.2em;'><a href='" . BASE . "/campus/nova-senha/{$RecoverLink}' title='Ativar Minha Conta Agora'>RECUPERAR MINHA SENHA AGORA!</a></p>
                        <p>...</p>
                        <p>OBS.: Caso não tenha solicitado, favor ignore essa mensagem!</p>  
                        <p>IMPORTANTE: O link de recuperação é válido por 1 hora, e somente para o dispositivo que fez a solicitação!</p>
                        <p>...</p>
                        <p>Qualquer dúvida ou problema não deixe de entrar em contato pelo e-mail " . SITE_ADDR_EMAIL . ", ficamos a disposição!</p>
                        <p><em>Atenciosamente " . SITE_NAME . "!</em></p>
                    ";
                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("Recupere sua senha {$Read->getResult()[0]['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}", $Read->getResult()[0]['user_email']);

                    $jSON['modal'] = ["green", "envelop", "Instruções enviadas por e-mail:", "<p style='font-size: 1.2em'>Prezado(a) {$Read->getResult()[0]['user_name']},</p><p>As intruções junto com o link para recuperar sua senha foram enviadas para seu e-mail neste instante.</p><p><a class='btn btn_green icon-share' target='_blank' href='//" . substr($DATA['user_email'], strrchr("@", $DATA['user_email'])) . "' title='Acessar meu webmail!'>Acessar meu webmail!</a></p><p><b class='icon-warning'>IMPORTANTE:</b> Para sua segurança o link de recuperação é válido por 1 hora e apenas para este dispositivo!<p>Qualquer problema não deixe de entrar em contato!</p>"];
                endif;
            endif;
            break;

        //USER RECOVER PASSWORD CHANGE
        case 'wc_ead_password_change':
            //CHECK SESSION VALIDADE AND TIME
            if (empty($_SESSION['wc_recover_password']) || $_SESSION['wc_recover_password']['user_time'] < time()):
                $jSON['alert'] = ['red', "Link Expirado:", 'O link para recuperar senha não é mais válido! Você pode <a href="' . BASE . '/campus/senha" title="Recuperar Minha Senha">gerar um novo link</a> de recuperação.'];
                break;
            endif;

            //CHECK USER ON DB
            $Read->FullRead("SELECT user_name FROM " . DB_USERS . " WHERE user_email = :mail AND user_password = :pass", "mail={$_SESSION['wc_recover_password']['user_email']}&pass={$_SESSION['wc_recover_password']['user_password']}");
            if (!$Read->getResult()):
                $jSON['alert'] = ['red', "Dados Incorretos:", 'Os dados informados estão incorretos ou já foram alterados! Você pode <a href="' . BASE . '/campus/senha" title="Recuperar Minha Senha">gerar um novo link</a> de recuperação.'];
                break;
            endif;

            //CHECK DATA FOR NEW PASS
            if (in_array("", $DATA)):
                $jSON['alert'] = ['yellow', "Erro ao atualizar senha:", 'Você precisa informar e repetir uma nova senha!'];
            elseif (strlen($DATA['user_password']) < 5):
                $jSON['alert'] = ['red', 'Senha Inválida:', 'Sua senha deve ter no mínimo 5 caracteres!'];
            elseif ($DATA['user_password'] != $DATA['user_password_re']):
                $jSON['alert'] = ['yellow', "Senhas diferentes:", 'Você repetiu uma senha diferente da nova senha!'];
            else:
                //UPDATE PASSWORD
                $UpdateUserPassword = ['user_password' => hash("sha512", $DATA['user_password']), 'user_lastupdate' => date("Y-m-d H:i:s")];
                $Update->ExeUpdate(DB_USERS, $UpdateUserPassword, "WHERE user_email = :mail AND user_password = :pass", "mail={$_SESSION['wc_recover_password']['user_email']}&pass={$_SESSION['wc_recover_password']['user_password']}");

                $jSON['alert'] = ["green", "Senha alterada:", "Sua senha foi atualizada com sucesso, <a title='Efetuar Login' href='" . BASE . "/campus'>CLIQUE AQUI</a> para logar-se"];
            endif;
            break;

        //STUDENT ACTIONS :: ACCOUNT UPDATE
        case 'wc_ead_student_account_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            else:
                $User = $Read->getResult()[0];
                $UserUpdate = $DATA;

                $UserUpdate['user_name'] = (!empty($UserUpdate['user_name']) ? $UserUpdate['user_name'] : $User['user_name']);
                $UserUpdate['user_lastname'] = (!empty($UserUpdate['user_lastname']) ? $UserUpdate['user_lastname'] : $User['user_lastname']);
                $UserUpdate['user_genre'] = (!empty($UserUpdate['user_genre']) ? $UserUpdate['user_genre'] : $User['user_genre']);

                $File = (!empty($_FILES['user_thumb']) ? $_FILES['user_thumb'] : null);
                if ($File):
                    if (file_exists("../uploads/{$User['user_thumb']}") && !is_dir("../uploads/{$User['user_thumb']}")):
                        unlink("../uploads/{$User['user_thumb']}");
                    endif;
                    $Upload = new Upload("../uploads/");
                    $Upload->Image($File, $User['user_id'] . "-" . Check::Name($UserUpdate['user_name'] . $UserUpdate['user_lastname']), AVATAR_W);
                    $UserUpdate['user_thumb'] = $Upload->getResult();
                else:
                    unset($UserUpdate['user_thumb']);
                endif;

                if (!empty($UserUpdate['user_document']) && !Check::CPF($UserUpdate['user_document'])):
                    $jSON['alert'] = ["yellow", "Opppsss {$UserUpdate['user_name']},", "O número de CPF informado não é valido!"];
                    break;
                endif;

                if (!empty($DATA['user_email'])):
                    unset($DATA['user_email']);
                endif;

                $Update->ExeUpdate(DB_USERS, $UserUpdate, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['alert'] = ["green", "Tudo certo {$UserUpdate['user_name']},", "Seus dados foram atualizados com sucesso!"];
            endif;
            break;

        //STUDENT ACTIONS :: ADDRESS UPDATE
        case 'wc_ead_student_address_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            else:
                $User = $Read->getResult()[0];

                $Update->ExeUpdate(DB_USERS_ADDR, $DATA, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['alert'] = ["green", "Tudo certo {$User['user_name']},", "Seu endereço foi atualizado com sucesso!"];
            endif;
            break;

        //STUDENT ACTIONS :: ADDRESS UPDATE
        case 'wc_ead_student_password_update':
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            endif;

            $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                unset($_SESSION['userLogin']);
                $jSON['redirect'] = BASE . "/campus/login/restrito";
            elseif (strlen($DATA['user_password']) < 5):
                $jSON['alert'] = ["yellow", "Oppsss {$Read->getResult()[0]['user_name']},", "Sua nova senha deve ter no mínimo 5 caracteres!"];
            elseif ($DATA['user_password'] != $DATA['user_password_re']):
                $jSON['alert'] = ["yellow", "Oppsss {$Read->getResult()[0]['user_name']},", "Você informou 2 novas senhas diferentes!"];
            else:
                $User = $Read->getResult()[0];
                $UpdatePassword = ['user_password' => hash("sha512", $DATA['user_password'])];

                $Update->ExeUpdate(DB_USERS, $UpdatePassword, "WHERE user_id = :id", "id={$User['user_id']}");
                $jSON['alert'] = ["green", "Tudo certo {$User['user_name']},", "Sua senha foi atualizada com sucesso!"];
            endif;
            break;

        //STUDENT ACTIONS :: TASK MANAGER
        case 'wc_ead_student_task_manager':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $start_time = (!empty($_SESSION['wc_student_task']) ? $_SESSION['wc_student_task'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);
            $end_time = 0;

            if ($studend_class_id && $start_time && $user_id):
                $Read->FullRead("SELECT class_time FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND student_class_id = :class AND student_class_free IS NULL)", "user={$user_id}&class={$studend_class_id}");

                if ($Read->getResult()):

                    //SAVE CLASS TIME
                    $ClassTotalTime = $Read->getResult()[0]['class_time'];

                    if (EAD_STUDENT_CLASS_PERCENT):
                        $end_time = ceil(($start_time + ($Read->getResult()[0]['class_time'] * 60) * (EAD_STUDENT_CLASS_PERCENT / 100)));
                        $ClassTotalTime = floor($ClassTotalTime * (EAD_STUDENT_CLASS_PERCENT / 100));
                    endif;

                    if (EAD_STUDENT_CLASS_AUTO_CHECK):
                        if (time() >= $end_time):
                            $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                            $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                            $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                            $jSON['stop'] = true;
                            break;
                        endif;
                    endif;

                    if (time() > $end_time && !EAD_STUDENT_CLASS_AUTO_CHECK):
                        $UpdateStudenClass = ['student_class_free' => 1];
                        $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                        $jSON['aprove'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                        $jSON['stop'] = true;
                        break;
                    else:
                        $Read->FullRead("SELECT student_class_seconds FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :st_class", "st_class={$studend_class_id}");
                        if ($Read->getResult()):
                            $student_class_seconds = ['student_class_seconds' => $Read->getResult()[0]['student_class_seconds'] + 10];
                            $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $student_class_seconds, "WHERE student_class_id = :st_class", "st_class={$studend_class_id}");

                            //RECOVER USER LAST VIEW TIME
                            $ClassFreeTime = floor($Read->getResult()[0]['student_class_seconds'] / 60);
                            if ($ClassFreeTime >= $ClassTotalTime):
                                if (EAD_STUDENT_CLASS_AUTO_CHECK):
                                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                                    $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                                    $jSON['stop'] = true;
                                    break;
                                else:
                                    $UpdateStudenClass = ['student_class_free' => 1];
                                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");
                                    $jSON['aprove'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                                    $jSON['stop'] = true;
                                    break;
                                endif;
                            else:
                                $jSON['waiting'] = true;
                            endif;
                        else:
                            $jSON['waiting'] = true;
                        endif;
                    endif;
                else:
                    $jSON['stop'] = true;
                    break;
                endif;
            else:
                $jSON['stop'] = true;
                break;
            endif;
            break;

        //STUDENT ACTIONS :: TASK CHECK
        case 'wc_ead_student_task_manager_check':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);

            if (!$user_id || !$studend_class_id):
                $jSON['modal'] = ["red", "warning", "Erro ao concluir tarefa:", "<p>Desculpe, não foi possível identificar seu login ou a tarefa acessada. As aulas devem ser feitas uma de cada vez.</p><p><b>EVITE ESSE ERRO:</b> Para evitar esse erro procure não abrir mais de uma aula em abas ao mesmo tempo.</p><p><a href='' class='btn btn_red icon-loop2' title=''>Atualizar Página Agora!</a></p><p>Por favor, atualize sua página e tente concluir novamente!</p>"];
            else:
                $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                if ($Read->getResult()):
                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => date("Y-m-d H:i:s")];
                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");

                    $jSON['check'] = "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi") . "</span>";
                    $jSON['alert'] = ["green", "Tarefa concluída {$_SESSION['userLogin']['user_name']},", "Parabéns você concluiu a tarefa {$Read->getResult()[0]['class_title']}!"];
                else:
                    $jSON['alert'] = ["yellow", "Tarefa não identificada {$_SESSION['userLogin']['user_name']},", "Atualize a página para que a tarefa seja identificada!"];
                endif;
            endif;
            break;

        //STUDENT ACTIONS :: TASK UNCHECK
        case 'wc_ead_student_task_manager_uncheck':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);
            $user_id = (!empty($_SESSION['userLogin']['user_id']) ? $_SESSION['userLogin']['user_id'] : null);

            if (!$user_id || !$studend_class_id):
                $jSON['modal'] = ["red", "warning", "Erro ao desmarcar tarefa:", "<p>Desculpe, não foi possível identificar seu login ou a tarefa acessada. As aulas devem ser feitas uma de cada vez.</p><p><b>EVITE ESSE ERRO:</b> Para evitar esse erro procure não abrir mais de uma aula em abas ao mesmo tempo.</p><p><a href='' class='btn btn_red icon-loop2' title=''>Atualizar Página Agora!</a></p><p>Por favor, atualize sua página e tente desmarcar novamente!</p>"];
            else:
                $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                if ($Read->getResult()):
                    $UpdateStudenClass = ['student_class_free' => 1, "student_class_check" => null];
                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class", "class={$studend_class_id}");

                    $jSON['check'] = "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                    $jSON['alert'] = ["blue", "Volte aqui depois {$_SESSION['userLogin']['user_name']},", "Você desmarcou a tarefa {$Read->getResult()[0]['class_title']}!"];
                else:
                    $jSON['alert'] = ["yellow", "Tarefa não identificada {$_SESSION['userLogin']['user_name']},", "Atualize a página para que a tarefa seja identificada!"];
                endif;
            endif;
            break;

        //STUDENT ACTION :: SEND SUPPORT
        case 'wc_ead_student_task_ticket_add':
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);

            //ALL CHECK
            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
                break;
            elseif (!$studend_class_id):
                $jSON['alert'] = ["red", "Aula não identificada:", "Desculpe {$_SESSION['userLogin']['user_name']}, para enviar sua dúvida antes atualize a página :/"];
                break;
            elseif (empty($DATA['ticket_content'])):
                $jSON['alert'] = ["yellow", "O que quer perguntar {$_SESSION['userLogin']['user_name']}?", "Para enviar uma dúvida, é preciso escrever sua dúvida :)"];
                break;
            endif;

            //VALIDATE TICKET
            $PregContent = preg_replace("/<p[^>]*>[\s|&nbsp;\ ]*<\/p>/", '', $DATA['ticket_content']);
            $TicketContent = str_replace("\r\n", "", $PregContent);

            if (empty($TicketContent)):
                $jSON['alert'] = ["yellow", "Tarefa não identificada {$_SESSION['userLogin']['user_name']},", "Atualize a página e tente enviar sua dúvida novamente!"];
            else:
                $Read->FullRead("SELECT course_id, class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class", "class={$studend_class_id}");
                if (!$Read->getResult()):
                    $jSON['alert'] = ["red", "Aula não identificada:", "Desculpe {$_SESSION['userLogin']['user_name']}, para enviar sua dúvida antes atualize a página :/"];
                else:
                    $SupportClass = $Read->getResult()[0];

                    //CREATE SUPPORT OR RESPONSE
                    $Read->FullRead("SELECT user_id, support_id FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class", "user={$_SESSION['userLogin']['user_id']}&class={$SupportClass['class_id']}");
                    if (!$Read->getResult()):
                        $CreateTicket = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'class_id' => $SupportClass['class_id'], 'support_content' => $TicketContent, 'support_status' => 1, 'support_open' => date("Y-m-d H:i:s")];
                        $Create->ExeCreate(DB_EAD_SUPPORT, $CreateTicket);

                        $UserThumb = "../uploads/{$_SESSION['userLogin']['user_thumb']}";
                        $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $jSON['alert'] = ["green", "Dúvida enviada com sucesso:", "Pronto {$_SESSION['userLogin']['user_name']}, agora basta aguardar uma resposta :)"];
                        $jSON['ead_support'] = true;
                        $jSON['ead_support_content'] = "<article class='wc_ead_course_task_forum_ticket' id='{$Create->getResult()}'>
                            <div class='wc_ead_course_task_forum_ticket_thumb " . ($_SESSION['userLogin']['user_level'] > 5 ? 'admin' : '') . "'>
                                 <img class='rounded thumb' src='" . BASE . "/tim.php?src={$SuportUserThumb}&w=" . AVATAR_W / 3 . "&h=" . AVATAR_H / 3 . "'/>
                            </div><div class='wc_ead_course_task_forum_ticket_content'>
                                <header class='wc_ead_course_task_forum_ticket_header'>
                                    <h1 class='icon-bubble2'><span class='user'>Pergunta de {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}</span> <span class='time'>dia " . date('d/m/Y H\hi') . "</span> <span class='status bar_red radius'>Em Aberto</span></h1>
                                </header>
                                <div class='htmlchars'>{$TicketContent}</div>
                                <div class='wc_ead_course_task_forum_response'></div>
                            </div> 
                        </article>";

                        //ALERT TUTOR FOR NEW TICKET
                        if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                            $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                            $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');

                            require './wc_ead.email.php';
                            $MailBody = "
                                <p style='font-size: 1.4em;'>Nova dúvida para responder,</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma dúvida na aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/tarefa/{$MailClassName}#{$Create->getResult()}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Dúvida de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                            $Email = new Email;
                            $Email->EnviarMontando("#" . str_pad($Create->getResult(), 4, 0, 0) . " - Nova dúvida em aberto!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                        endif;
                    else:
                        $SupportResponseId = $Read->getResult()[0]['support_id'];
                        $CreateResponse = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'support_id' => $SupportResponseId, 'response_content' => $TicketContent, 'response_open' => date("Y-m-d H:i:s")];
                        $Create->ExeCreate(DB_EAD_SUPPORT_REPLY, $CreateResponse);

                        $UpdateTicket = ['support_status' => 1, 'support_reply' => date("Y-m-d H:i:s"), 'support_close' => null, 'support_review' => null, 'support_comment' => null];
                        $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateTicket, "WHERE support_id = :support", "support={$Read->getResult()[0]['support_id']}");

                        $UserThumb = "../uploads/{$_SESSION['userLogin']['user_thumb']}";
                        $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $jSON['alert'] = ["blue", "Resposta enviada com sucesso:", "Pronto {$_SESSION['userLogin']['user_name']}, agora basta aguardar outra resposta :)"];
                        $jSON['ead_support'] = true;
                        $jSON['ead_support_id'] = $Read->getResult()[0]['support_id'];
                        $jSON['ead_support_content'] = "<article class='wc_ead_course_task_forum_ticket' id='{$Create->getResult()}'>
                            <div class='wc_ead_course_task_forum_ticket_thumb " . ($_SESSION['userLogin']['user_level'] > 5 ? 'admin' : '') . "'>
                                <img class='rounded thumb' src='" . BASE . "/tim.php?src={$SuportUserThumb}&w=" . AVATAR_W / 3 . "&h=" . AVATAR_H / 3 . "'/>
                            </div><div class='wc_ead_course_task_forum_ticket_content'>
                                <header class='wc_ead_course_task_forum_ticket_header'>
                                    <h1 class='icon-bubbles3'><span class='user'>Resposta de {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}</span> <span class='time'>dia " . date('d/m/Y H\hi') . "</span></h1>
                                </header>
                                <div class='htmlchars'>{$TicketContent}</div>
                            </div>
                        </article>";

                        //ALERT TUTOR FOR RESPONSE
                        if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                            $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                            $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');

                            require './wc_ead.email.php';
                            $MailBody = "
                                <p style='font-size: 1.4em;'>Nova resposta em uma dúvida,</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida da aula <b>{$MailClassTitle}</b> no curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/tarefa/{$MailClassName}#{$SupportResponseId}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Resposta de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                            $Email = new Email;
                            $Email->EnviarMontando("#" . str_pad($SupportResponseId, 4, 0, 0) . " - Nova resposta em uma dúvida!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                        endif;
                    endif;
                    $jSON['clear'] = true;
                endif;
            endif;
            break;

        //STUDENT ACTION :: SEND REVIEW
        case 'wc_ead_student_task_ticket_review':
            $support_id = (!empty($DATA['support_id']) ? $DATA['support_id'] : null);
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);

            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
                break;
            elseif (!$support_id || !$studend_class_id):
                $jSON['alert'] = ["red", "Aula não identificada:", "Desculpe {$_SESSION['userLogin']['user_name']}, para avaliar o suporte, antes atualize a página :/"];
                break;
            endif;

            if (empty($DATA['support_review'])):
                $jSON['alert'] = ['red', "Selecione a nota {$_SESSION['userLogin']['user_name']},", "Para avaliar o suporte é preciso selecionar a nota :)"];
            else:
                $DATA['support_status'] = 3;
                $DATA['support_close'] = date("Y-m-d H:i:s");
                unset($DATA['support_id']);
                $Update->ExeUpdate(DB_EAD_SUPPORT, $DATA, "WHERE support_id = :support AND user_id = :user", "support={$support_id}&user={$_SESSION['userLogin']['user_id']}");

                if (!empty($DATA['support_comment'])) {
                    //ALERT TUTOR FOR RESPONSE
                    if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                        $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');

                        require './wc_ead.email.php';
                        $MailBody = "
                                <p style='font-size: 1.4em;'>Nova avaliação de suporte,</p>
                                <p>{$_SESSION['userLogin']['user_name']} enviou um feedback sobre o suporte recebido na aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                                <p>A nota para o suporte recebido foi {$DATA['support_review']} de 5!</p>
                                <p>...</p>
                                <p><b>Feedback de {$_SESSION['userLogin']['user_name']}:</b></p>
                                <p>" . nl2br($DATA['support_comment']) . "</p>
                                <p>...</p>
                                <p><b>Dados de contato:</b></p>
                                <p>
                                  Nome: {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}<br>
                                  E-mail: {$_SESSION['userLogin']['user_email']}<br>
                                  Celular: {$_SESSION['userLogin']['user_cell']}<br>
                                  Telefone: {$_SESSION['userLogin']['user_telephone']} 
                                </p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                        $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                        $Email = new Email;
                        $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Feedback do suporte!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, MAIL_USER);
                    endif;
                }

                $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                $ReviewTicket = str_repeat($ReviewPositive, $DATA['support_review']) . str_repeat($ReviewNegative, 5 - $DATA['support_review']);

                $jSON['alert'] = ['green', "Obrigado por sua avaliação {$_SESSION['userLogin']['user_name']} :)", "Suporte concluído! Se precisar você pode adionar outra resposta!"];
                $jSON['close'] = ".jwc_ticket_review_content";
                $jSON['clear'] = true;
                $jSON['review'] = $ReviewTicket;
            endif;
            break;

        //STUDENT ACTION :: SEND REPLY
        case 'wc_ead_student_task_ticket_reply':
            $support_id = (!empty($DATA['support_id']) ? $DATA['support_id'] : null);
            $studend_class_id = (!empty($_SESSION['wc_student_class']) ? $_SESSION['wc_student_class'] : null);

            if (empty($_SESSION['userLogin'])):
                $jSON['redirect'] = BASE . "/campus/login/restrito";
                break;
            elseif (!$support_id || !$studend_class_id):
                $jSON['alert'] = ["red", "Aula não identificada:", "Desculpe {$_SESSION['userLogin']['user_name']}, para avaliar o suporte, antes atualize a página :/"];
                break;
            endif;

            if (empty($DATA['ticket_content'])):
                $jSON['alert'] = ["yellow", "Oppsss {$_SESSION['userLogin']['user_name']},", "Você esqueceu de escrever sua resposta :/"];
            else:
                //VALIDATE TICKET CONTENT
                $PregContent = preg_replace("/<p[^>]*>[\s|&nbsp;\ ]*<\/p>/", '', $DATA['ticket_content']);
                $TicketContent = str_replace("\r\n", "", $PregContent);

                //VALID THUMB
                $UserThumb = "../uploads/{$_SESSION['userLogin']['user_thumb']}";
                $SuportUserThumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$_SESSION['userLogin']['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                //READ USER
                $Read->FullRead("SELECT user_id FROM " . DB_EAD_SUPPORT . " WHERE support_id = :support", "support={$support_id}");
                $UserResponderId = $Read->getResult()[0]['user_id'];

                if (!$Read->getResult()):
                    $jSON['alert'] = ["red", "Desculpe {$_SESSION['userLogin']['user_name']},", "Não foi possível identificar a dúvida. Recarregue a página!"];
                    break;
                endif;

                //RESPONSE UPDATE STATUS
                $SetSupportStatus = 1;
                if ($UserResponderId != $_SESSION['userLogin']['user_id'] || $_SESSION['userLogin']['user_level'] > 5):
                    if ($_SESSION['userLogin']['user_level'] > 5):
                        //REPLY BY ADMIN
                        $SetSupportStatus = 2;
                    else:
                        //REPLY BY OTHER STUDENT
                        $SetSupportStatus = (EAD_TASK_SUPPORT_STUDENT_RESPONSE ? 2 : 1);
                    endif;
                endif;

                //RESPONSE UPDATE DATA
                $ReponseUpdate = ['support_status' => $SetSupportStatus, 'support_reply' => date("Y-m-d H:i:s"), 'support_close' => null, 'support_review' => null, 'support_comment' => null];
                $Update->ExeUpdate(DB_EAD_SUPPORT, $ReponseUpdate, "WHERE support_id = :support", "support={$support_id}");

                //REPLY CREATE
                $CreateReply = ['user_id' => $_SESSION['userLogin']['user_id'], 'enrollment_id' => $_SESSION['wc_student_enrollment_id'], 'support_id' => $support_id, 'response_content' => $TicketContent, 'response_open' => date("Y-m-d H:i:s")];
                $Create->ExeCreate(DB_EAD_SUPPORT_REPLY, $CreateReply);

                if ($UserResponderId == $_SESSION['userLogin']['user_id']):
                    //ALERT ADMIN :: Avisa admin sobre novo ticket!
                    if (EAD_TASK_SUPPORT_EMAIL && Check::Email(EAD_TASK_SUPPORT_EMAIL)):
                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                        $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                        $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                        $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');

                        require './wc_ead.email.php';
                        $MailBody = "
                                <p style='font-size: 1.4em;'>Nova resposta em uma dúvida,</p>
                                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida da aula <b>{$MailClassTitle}</b> no curso <b>{$MailCourseTitle}.</b></p>
                                <p><b>IMPORTANTE:</b> Quanto antes responder as dúvidas, melhor é a avaliação de seus alunos para o suporte prestado!</p>
                                <p style='font-size: 1.2em;'><b><a href='" . BASE . "/campus/tarefa/{$MailClassName}#{$support_id}' title='Responder dúvida agora!'>RESPONDER AGORA!</a></b></p>
                                <p>Você também pode responder todas as dúvidas de forma otimizada no menu suporte da plataforma!</p>
                                <p>...</p>
                                <p><b>Resposta de {$_SESSION['userLogin']['user_name']}:</b> {$TicketContent}</p>
                                <p>...</p>
                                <p>Este e-mail automático não deve ser respondido!</p>
                            ";

                        $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                        $Email = new Email;
                        $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Nova resposta em uma dúvida!", $MailContent, SITE_NAME, MAIL_USER, MAIL_SENDER, EAD_TASK_SUPPORT_EMAIL);
                    endif;
                else:
                    //ALERT RESPONDER :: Avisa aluno sobre nova resposta!
                    $Read->FullRead("SELECT user_name, user_lastname, user_email, user_genre FROM " . DB_USERS . " WHERE user_id = :user", "user={$UserResponderId}");
                    $UserResponderData = $Read->getResult()[0];
                    $UserGenreString = ($Read->getResult()[0]['user_genre'] == 1 ? 'o' : 'a');

                    $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                    $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

                    $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id = (SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE student_class_id = :class)", "class={$studend_class_id}");
                    $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
                    $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');

                    require './wc_ead.email.php';
                    $MailBody = "
                        <p style='font-size: 1.4em;'>Prezad{$UserGenreString} {$UserResponderData['user_name']},</p>
                        <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida!</p>
                        <p>Para responder, efetue <a href='" . BASE . "/campus' title='Acessar minha conta na plataforma!'>login aqui</a> e acesse a aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                        <p>Já esta logad{$UserGenreString} na plataforma? Então acesse diretamente <a href='" . BASE . "/campus/tarefa/{$MailClassName}#{$support_id}' title='Acessar aula {$MailClassTitle}!'>clicando aqui!</a></p>
                        <p><b>IMPORTANTE:</b> Para concluir sua dúvida envie sua avaliação no ticket, ou adicione outra resposta para tirar mais dúvidas!</p>
                        <p>...</p>
                        <p>Se tiver qualquer problema, não deixe de responder este e-mail!</p>
                    ";

                    $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                    $Email = new Email;
                    $Email->EnviarMontando("#" . str_pad($support_id, 4, 0, 0) . " - Sua dúvida foi respondida!", $MailContent, MAIL_SENDER, MAIL_USER, "{$UserResponderData['user_name']} {$UserResponderData['user_lastname']}", $UserResponderData['user_email']);
                endif;

                //RETURN REPLY
                $jSON['clear'] = true;
                $jSON['close'] = ".jwc_ticket_reply_content";
                $jSON['ead_support'] = true;
                $jSON['ead_support_id'] = $support_id;
                $jSON['ead_support_content'] = "<article class='wc_ead_course_task_forum_ticket' id='{$Create->getResult()}'>
                    <div class='wc_ead_course_task_forum_ticket_thumb " . ($_SESSION['userLogin']['user_level'] > 5 ? 'admin' : '') . "'>
                        <img class='rounded thumb' src='" . BASE . "/tim.php?src={$SuportUserThumb}&w=" . AVATAR_W / 3 . "&h=" . AVATAR_H / 3 . "'/>
                    </div><div class='wc_ead_course_task_forum_ticket_content'>
                        <header class='wc_ead_course_task_forum_ticket_header'>
                            <h1 class='icon-bubbles3'><span class='user'>Resposta de {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}</span> <span class='time'>dia " . date('d/m/Y H\hi') . "</span></h1>
                        </header>
                        <div class='htmlchars'>{$TicketContent}</div>
                    </div>
                </article>";
            endif;
            break;

        //STUDENT :: CERTIFICATION
        case 'wc_ead_studend_certification':
            sleep(1);

            if (empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_id'])):
                $jSON['alert'] = ['red', 'Oppsss, perdemos algo:', "Sua conta não está mais conectada, recarregando!"];
                $jSON['reload'] = true;
                break;
            endif;

            $Read->FullRead("SELECT certificate_id FROM " . DB_EAD_STUDENT_CERTIFICATES . " WHERE enrollment_id = :enrol AND user_id = :user", "enrol={$POST['enrollment_id']}&user={$_SESSION['userLogin']['user_id']}");
            if ($Read->getResult()):
                $jSON['alert'] = ['red', 'Oppsss, perdemos algo:', "Seu certificado para este curso já foi emitido {$_SESSION['userLogin']['user_name']}!"];
                break;
            endif;

            $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_id = :enrol AND user_id = :user", "enrol={$POST['enrollment_id']}&user={$_SESSION['userLogin']['user_id']}");
            if (!$Read->getResult()):
                $jSON['alert'] = ['red', 'Erro ao Emitir Certificado:', "<p style='margin: 15px 0 5px 0;'>Desculpe {$_SESSION['userLogin']['user_name']} mas não foi possível ler a matrícula deste curso.</p><p>Favor atualize a página e tente novamente, e caso o erro persista entre em contato via " . SITE_ADDR_EMAIL . ".</p>"];
                break;
            endif;

            extract($Read->getResult()[0]);

            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
            $ClassCount = $Read->getResult()[0]['ClassCount'];

            $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL", "user={$user_id}&course={$course_id}");
            $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

            $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : 0);

            $Read->LinkResult(DB_EAD_COURSES, "course_id", $course_id, 'course_title, course_certification_request');
            extract($Read->getResult()[0]);

            if ($_SESSION['userLogin']['user_level'] < 6 && $CourseCompletedPercent < $course_certification_request):
                $jSON['alert'] = ['red', "Oppsss {$_SESSION['userLogin']['user_name']},", "Para solicitar seu certificado <b>antes complete {$course_certification_request}% do curso!</b>"];
                break;
            endif;

            $CreateCertification = ['user_id' => $user_id, 'course_id' => $course_id, 'enrollment_id' => $enrollment_id, 'certificate_key' => "{$user_id}{$course_id}" . date('Ym'), 'certificate_issued' => date("Y-m-d"), 'certificate_status' => 1];
            $Create->ExeCreate(DB_EAD_STUDENT_CERTIFICATES, $CreateCertification);

            $jSON['certification'] = [
                "Image" => "<div class='wc_ead_win_image'><span class='wc_ead_win_image_icon icon-trophy icon-notext'></span></div>",
                "Icon" => "heart",
                "Title" => "Parabéns {$_SESSION['userLogin']['user_name']} :)",
                "Content" => "Mais uma conquista em sua carreira. Seu certificado para o curso <b>{$course_title}</b> foi emitido com sucesso!</p>",
                "Link" => BASE . "/campus/imprimir/{$CreateCertification['certificate_key']}",
                "LinkIcon" => "printer",
                "LinkTitle" => "Imprimir Certificado!"
            ];
            break;
    endswitch;
endif;

if ($jSON):
    echo json_encode($jSON);
else:
    $jSON['modal'] = ['red', 'warning', 'Erro inesperado!', '<p><b>Opppssss:</b> Um erro inesperado foi encontrado no sistema. Favor atualize a página e tente novamente!</p><p>Caso o erro persista, não deixe de nos avisar enviando um e-mail para ' . SITE_ADDR_EMAIL . '!</p><p>Obrigado. Atenciosamente ' . SITE_NAME . '!</p>'];
    echo json_encode($jSON);
endif;

ob_end_flush();
