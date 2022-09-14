<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = 6;

if (empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Dashboard';
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

    //LICENCE CHECK
    if (file_exists("../dashboard.json")):
        $LicenseFile = file_get_contents("../dashboard.json");
        $LicenseDomain = json_decode($LicenseFile);

        if (empty($LicenseDomain->license_auth_date) || empty($LicenseDomain->license_hash)):
            unlink("../dashboard.json");
            exit;
        endif;

        if (!empty($LicenseDomain->license_auth_date)):
            $DateNow = new DateTime();
            $DatePing = new DateTime($LicenseDomain->license_auth_date);
            $DateDiff = $DateNow->diff($DatePing)->days;

            if ($DateDiff >= 5):
                set_error_handler(create_function('$severity, $message, $file, $line', 'throw new ErrorException($message, $severity, $severity, $file, $line);'));
                try {
                    $PostLicence = file_get_contents("https://download.workcontrol.com.br?h={$LicenseDomain->license_hash}&d=" . urlencode(BASE));
                    $resultLicence = json_decode($PostLicence);

                    if (!empty($resultLicence->trigger)):
                        $_SESSION['trigger_controll'] = $resultLicence->trigger;
                        unlink("../dashboard.json");
                    else:
                        //UPDATE LICENSE
                        $LicenseUpdate = str_replace('"license_auth_date":"' . $LicenseDomain->license_auth_date . '"', '"license_auth_date":"' . date("Y-m-d H:i:s") . '"', $LicenseFile);
                        chmod("../dashboard.json", 0755);
                        $LicenseFile = fopen("../dashboard.json", "w+");
                        fwrite($LicenseFile, $LicenseUpdate);
                        fclose($LicenseFile);
                        chmod("../dashboard.json", 0644);
                    endif;
                } catch (Exception $e) {
                    //ERROR HANDLER
                }
                restore_error_handler();
            endif;
        endif;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        //WC LOGIN FIX
        case 'wc_login_fix':
            if (!empty($_SESSION['userLogin']) && $_SESSION['userLogin']['user_level'] >= 6):
                $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
                if ($Read->getResult() && $Read->getResult()[0]['user_level'] >= 6):
                    $_SESSION['userLogin'] = $Read->getResult()[0];
                    $jSON['login'] = true;
                else:
                    unset($_SESSION['userLogin']);
                    $_SESSION['trigger_login'] = AjaxErro("<div class='al_center icon-warning'>Sua sessão expirou ou você não tem permissão para acessar o painel!</div>", E_USER_ERROR);
                    $jSON['redirect'] = BASE . "/admin";
                endif;
            else:
                unset($_SESSION['userLogin']);
                $_SESSION['trigger_login'] = AjaxErro("<div class='al_center icon-warning'>Sua sessão expirou ou você não tem permissão para acessar o painel!</div>", E_USER_ERROR);
                $jSON['redirect'] = BASE . "/admin";
            endif;
            break;

        //STATS
        case 'siteviews':
            $Read->FullRead("SELECT count(online_id) AS total from " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW()");
            $jSON['useron'] = str_pad($Read->getResult()[0]['total'], 4, 0, STR_PAD_LEFT);

            $Read->ExeRead(DB_VIEWS_VIEWS, "WHERE views_date = date(NOW())");
            if (!$Read->getResult()):
                $jSON['users'] = '0000';
                $jSON['views'] = '0000';
                $jSON['pages'] = '0000';
                $jSON['stats'] = '0.00';
            else:
                $Views = $Read->getResult()[0];
                $Stats = number_format($Views['views_pages'] / $Views['views_views'], 2, '.', '');
                $jSON['users'] = str_pad($Views['views_users'], 4, 0, STR_PAD_LEFT);
                $jSON['views'] = str_pad($Views['views_views'], 4, 0, STR_PAD_LEFT);
                $jSON['pages'] = str_pad($Views['views_pages'], 4, 0, STR_PAD_LEFT);
                $jSON['stats'] = $Stats;
            endif;

            $Read->FullRead("SELECT COUNT(online_id) AS TotalOnline FROM " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW() AND online_user IN(SELECT user_id FROM " . DB_EAD_ENROLLMENTS . ")");
            $jSON['students'] = str_pad($Read->getResult()[0]['TotalOnline'], 4, 0, 0);
            break;

        case 'onlinenow':
            $Where = "";
            $ParseString = "";

            if (!empty($PostData['user'])):
                $Where = "AND online_user = :user";
                $ParseString = "user={$PostData['user']}";
            endif;

            if (!empty($PostData['url'])):
                $Where = "AND online_url = :url";
                $ParseString = "url={$PostData['url']}";
            endif;
            
            $Read->ExeRead(DB_VIEWS_ONLINE, "WHERE online_endview >= NOW() {$Where} ORDER BY online_endview DESC", "{$ParseString}");
            if (!$Read->getResult()):
                $jSON['data'] = '<div class="trigger trigger_info"><span class="icon-earth al_center">Não existem usuárion online neste momento!</span></div>';
                $jSON['data'] .= '<div class="clear"></div>';
                $jSON['now'] = '0000';
            else:
                $i = 0;
                $jSON['data'] = null;
                $jSON['now'] = str_pad($Read->getRowCount(), 4, 0, 0);
                foreach ($Read->getResult() as $Online):
                    $i++;
                    $Name = ($Online['online_name'] ? "<a href='dashboard.php?wc=" . (APP_EAD ? 'teach/students_gerent' : 'users/create') . "&id={$Online['online_user']}' title='Ver Cliente'>{$Online['online_name']}</a>" : 'guest user');
                    $Date = date('d/m/Y H\hi', strtotime($Online['online_startview']));
                    $jSON['data'] .= "<div class='single_onlinenow'>
                    <p>" . str_pad($i, 4, 0, STR_PAD_LEFT) . "</p>
                    <p><a href='" . BASE . "/admin/dashboard.php?wc=onlinenow&user={$Online['online_user']}' class='btn btn_green btn_small icon-notext icon-filter'></a> {$Name}</p>
                    <p>{$Date}</p>
                    <p>{$Online['online_ip']}</p>
                    <p><a href='" . BASE . "/admin/dashboard.php?wc=onlinenow&url={$Online['online_url']}' class='btn btn_green btn_small icon-notext icon-filter'></a> <a target='_blank' href='" . BASE . "/{$Online['online_url']}' title='Ver Destino'>" . ($Online['online_url'] ? $Online['online_url'] : 'home') . "</a></p>
                    </div>";
                endforeach;
            endif;
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
