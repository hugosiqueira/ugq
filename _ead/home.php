<?php
//READ IF EMPTY
if (empty($Read)):
    $Read = new Read;
endif;

//CREATE IF EMPTY
if (empty($Create)):
    $Create = new Create;
endif;

//GET LOGIN
if (!empty($_SESSION['userLogin'])):
    $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
    if (!$Read->getResult()):
        unset($_SESSION['userLogin']);
        header("Location: " . BASE . "/campus/login/restrito");
        exit;
    else:
        $Student = $Read->getResult()[0];
        extract($Student);

        $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];
        $Update = new Update;
        $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");

        //USER VARS
        $user_thumb = (file_exists("uploads/{$user_thumb}") && !is_dir("uploads/{$user_thumb}") ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');

        $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
        if ($Read->getResult()):
            extract($Read->getResult()[0]);
        else:
            $NewAddr = ['user_id' => $user_id, 'addr_key' => 1, 'addr_name' => "Meu Endereço"];
            $Create = new Create;
            $Create->ExeCreate(DB_USERS_ADDR, $NewAddr);

            $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
            extract($Read->getResult()[0]);
        endif;
    endif;
else:
    header("Location: " . BASE . "/campus/login/restrito");
    exit;
endif;

//GET FEEBACK ALERTS
if (!empty($_SESSION['wc_ead_alert'])):
    echo "<div class='wc_ead_alert' style='display: flex;'>
        <div class='wc_ead_alert_box {$_SESSION['wc_ead_alert'][0]}'>
            <div class='wc_ead_alert_icon icon-switch icon-notext'></div><div class='wc_ead_alert_text'>
                <p class='wc_ead_alert_title'>{$_SESSION['wc_ead_alert'][1]}</p>
                <p class='wc_ead_alert_content'>{$_SESSION['wc_ead_alert'][2]}</p>
            </div><div class='wc_ead_alert_close'><span class='icon-cross icon-notext'></span></div>
        </div>
    </div>";
    unset($_SESSION['wc_ead_alert']);
endif;
?>


<div class='jwc_ead_win wc_ead_win wc_ead_win_default'>
    <div class='wc_ead_win_box'>
        {{IMAGE}}
        <div class='wc_ead_win_box_content al_center'>
            <p class='title icon-{{ICON}}'>{{TITLE}}</p>
            <p>{{CONTENT}}</p>
            <a target="_blank" title='{{LINK_TITLE}}' href='{{LINK}}' class='btn btn_blue icon-{{LINK_ICON}}'>{{LINK_NAME}}</a>
            <span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>
        </div>
    </div>
</div>

<div class="wc_ead_content jwc_ead_restrict">
    <article class="wc_ead_home_student">
        <div class="wc_ead_home_student_thumb">
            <img width="120" height="120" class="rounded user_thumb" alt="" title="" src="<?= BASE; ?>/tim.php?src=<?= $user_thumb; ?>&w=<?= AVATAR_W ?>&h=<?= AVATAR_H; ?>"/>
            <header>
                <h1><?= "{$user_name} {$user_lastname}"; ?></h1>
                <p class="icon-envelop"><?= $user_email; ?></p>
            </header>
        </div>
        <div class="wc_ead_home_student_content">
            <div class="wc_ead_home_student_nav">
                <a href="#courses" class="wc_tab wc_active icon-lab">Meus Cursos</a>
                <a href="#account" class="wc_tab icon-user">Minha Conta</a>
                <a href="#address" class="wc_tab icon-location">Meu Endereço</a>
                <a href="#access" class="wc_tab icon-key2">MINHA SENHA</a>
            </div>

            <div class="wc_ead_home_help icon-exit">Para Desconectar <a href="<?= BASE; ?>/campus&sair=true">Clique aqui</a></div>
        </div>

    </article><div class="wc_ead_home_courses">
        <?php
        //CHARGEBACK ALERT
        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status", "user={$user_id}&status=chargeback");
        if ($Read->getResult()):
            $OrderChargeback = $Read->getResult()[0];

            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$OrderChargeback['course_id']}");
            $OrderChargebackCourse = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto {$OrderChargeback['order_product_id']}");
            ?>
            <div class="trigger trigger_error" style="margin-bottom: 30px; padding: 30px;">
                <p style="margin: 0 0 20px 0; text-align: center; font-size: 4em;"><span class="icon-lock icon-notext"></span></p>
                <b class="icon-warning">SEU PEDIDO SOFREU UM CHARGEBACK:</b>
                <p style="margin-top: 15px;">Olá <?= $user_name; ?>, identificamos que seu pedido #<?= str_pad($OrderChargeback['order_id'], 5, 0, 0); ?> para o <?= $OrderChargebackCourse; ?> sofreu um chargeback de compra na hotmart.</p>
                <p style="margin-top: 20px;"><b class="icon-cross">SEU ACESSO FOI BLOQUEADO TEMPORARIAMENTE:</b></p>
                <p style="margin-top: 15px;">Um chargeback é uma negativa de compra no cartão de crédito. Isso ocorre quando o portador do cartão informa junto a operadora que não autorizou ou reconhece a compra!</p>
                <p style="margin-top: 15px;">Quando um chargeback ocorre o acesso a todos os cursos fica bloqueado temporariamente até que o pagamento seja processado e autorizado novamente!</p>
                <p style="margin-top: 20px;"><b class="icon-info">COMO RESOLVER UM CHARGEBACK:</b></p>
                <p style="margin-top: 15px;">Entre em contato com a Hotmart pelo e-mail suporte@hotmart.com.br e solicite liberação do seu pagamento para a transação <?= $OrderChargeback['order_transaction']; ?>.</p>
                <p style="margin-top: 15px;">Precisa de nossa ajuda? Entre em contato pelo e-mail <?= SITE_ADDR_EMAIL; ?>.</p>
                <p style="margin-top: 20px;"><b class="icon-alarm">IMPORTANTE:</b></p>
                <p style="margin-top: 15px;"><?= $user_name; ?>, não resolver o chargeback em menos de 7 dias poderá gerar bloqueio permanente da sua conta!</p>
            </div>
            <?php
        else:
            ?>
            <section class="wc_tab_target wc_active" id="courses">
                <header class="wc_ead_home_header">
                    <h1 class="icon-lab">MEUS CURSOS:</h1>
                    <p>Confira e estude seus cursos!</p>
                </header>
                <?php
                $Read->FullRead("SELECT "
                        . "e.*,"
                        . "c.*"
                        . "FROM " . DB_EAD_ENROLLMENTS . " e "
                        . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                        . "WHERE e.user_id = :user "
                        . "ORDER BY e.enrollment_access DESC, c.course_order ASC", "user={$user_id}"
                );

                if (!$Read->getResult()):
                    echo "<div class='trigger trigger_info trigger-none icon-info al_center'>Você ainda não tem cursos {$user_name}!</div>";
                else:
                    foreach ($Read->getResult() as $CS):
                        extract($CS);

                        $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE course_id = :cs", "cs={$course_id}");
                        $ClassCount = $Read->getResult()[0]['ClassCount'];

                        $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL", "user={$user_id}&course={$course_id}");
                        $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                        $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : 0);
                        $course_cover = (file_exists("uploads/{$course_cover}") && !is_dir("uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

                        $CourseBonusOpen = null;
                        if ($enrollment_bonus):
                            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_id = :enrollbonus)", "enrollbonus={$enrollment_bonus}");
                            $CourseBonusOpen = "<p class='wc_ead_home_courses_course_bonus icon-heart icon-notext radius wc_tooltip'><span class='wc_tooltip_balloon'>Bônus do curso {$Read->getResult()[0]['course_title']}</span></p>";
                        endif;
                        ?><article class="box box3 wc_ead_home_courses_course">
                            <div class="wc_ead_home_courses_course_thumb">
                                <?php
                                if (!empty($enrollment_end)):
                                    $EndDayNow = new DateTime();
                                    $EndDayRow = new DateTime($enrollment_end);
                                    $EndDayDif = $EndDayNow->diff($EndDayRow);

                                    if ($EndDayDif->days < 90 || $EndDayDif->invert):
                                        if ($course_vendor_renew && ($EndDayDif->days < 90 || $EndDayDif->invert)):
                                            echo "<a target='_blank' href='{$course_vendor_renew}&sck={$course_name}_re' class='wc_ead_home_courses_course_bonus " . ($CourseBonusOpen ? 'wc_ead_home_courses_course_renew' : '') . " icon-fire icon-notext wc_tooltip'><span class='wc_tooltip_balloon icon-fire'>Renove seu acesso a esse curso por mais {$course_vendor_access} meses com 80% de desconto.</span></a>";
                                        endif;
                                    endif;
                                endif;

                                echo $CourseBonusOpen;
                                ?>
                                <a href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>" title="Acessar o Curso <?= $course_title; ?>"><img alt="<?= $course_title; ?>" title="<?= $course_title; ?>" src="<?= BASE; ?>/tim.php?src=<?= $course_cover; ?>&w=<?= round(IMAGE_W / 3); ?>&h=<?= round(IMAGE_H / 3); ?>"/></a>
                            </div>
                            <div class="progress"><span class="progress_bar" style="width: <?= $CourseCompletedPercent; ?>%"><?= $CourseCompletedPercent; ?>%</span></div>
                            <div class="wc_ead_home_courses_course_content">
                                <h1 class="icon-lab"><a href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>" title="Acessar o Curso <?= $course_title; ?>"><?= $course_title; ?></a></h1>
                                <p class="icon-clock">Minha Matrícula: <?= date("d/m/y", strtotime($enrollment_start)); ?></p>
                                <p class="icon-history">Último acesso: <?= $enrollment_access ? date("d/m H\hi", strtotime($enrollment_access)) : "NUNCA"; ?></p>
                                <?php
                                $Read->LinkResult(DB_EAD_ORDERS, "course_id", $course_id, 'order_signature_plan, order_signature_recurrency, order_signature_period');

                                if (!empty($Read->getResult()[0]['order_signature_plan'])):
                                    $order_signature_recurrency = str_pad($Read->getResult()[0]['order_signature_recurrency'], 2, 0, 0);
                                    echo "<p class='icon-rss2'>Assinatura UpInside Club{$Read->getResult()[0]['order_signature_plan']}</p>";
                                elseif ($enrollment_end && empty($enrollment_bonus)):
                                    if (!$EndDayDif->invert):
                                        echo "<p class='icon-heart wc_tooltip'>Mais {$EndDayDif->days} dias para estudar!<span class='wc_tooltip_balloon'>Sua matrícula vence dia " . date("d/m/Y \a\s H\hi", strtotime($enrollment_end)) . "</span></p>";
                                    else:
                                        echo "<p class='icon-heart-broken'>Acesso expirado a {$EndDayDif->days} dias!</p>";
                                    endif;

                                elseif (!empty($enrollment_bonus)):
                                    echo "<p class='icon-heart'>Meu bônus de matrícula!</p>";
                                endif;
                                ?>
                            </div>
                            <?php
                            if ($course_certification_workload && EAD_STUDENT_CERTIFICATION):
                                if ($ClassCount == 0 || $CourseCompletedPercent < $course_certification_request):
                                    echo "<div class='icon-trophy wc_ead_home_courses_certifications wc_tooltip'>CERTIFICADO PENDENTE<span class='wc_tooltip_balloon icon-info'>Complete {$course_certification_request}% do curso para soliticar seu certificado!</span></div>";
                                else:
                                    $Read->FullRead("SELECT certificate_key FROM " . DB_EAD_STUDENT_CERTIFICATES . " WHERE enrollment_id = :enrol", "enrol={$enrollment_id}");
                                    if (!$Read->getResult()):
                                        echo "<div class='icon-trophy wc_ead_home_courses_certifications wc_ead_home_courses_certifications_true jwc_ead_certification' id='{$enrollment_id}'>SOLICITAR CERTIFICADO</div>";
                                    else:
                                        echo "<a title='Salvar, Imprimir Certificado!' href='" . BASE . "/campus/imprimir/{$Read->getResult()[0]['certificate_key']}' class='icon-printer wc_ead_home_courses_certifications wc_ead_home_courses_certifications_print'>IMPRIMIR CERTIFICADO</a>";
                                    endif;
                                endif;
                            endif;
                            ?>
                        </article><?php
                    endforeach;
                endif;

                $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = 'started' ORDER BY order_confirmation_purchase_date DESC, order_purchase_date DESC", "user={$user_id}");
                if ($Read->getResult()):
                    ?>
                    <section>
                        <header class="wc_ead_home_header" style="margin-top: 30px; font-size: 0.8em;">
                            <h2 class="icon-cart">Pedidos em Aberto:</h2>
                            <p>Pedidos aguardando confirmação de compra!</p>
                        </header>
                        <?php
                        foreach ($Read->getResult() as $StudentOrders):
                            $StudentOrders['order_currency'] = ($StudentOrders['order_currency'] ? $StudentOrders['order_currency'] : "BRL");

                            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$StudentOrders['course_id']}");
                            $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$StudentOrders['order_product_id']} na Hotmart");
                            ?><article class="wc_ead_studend_orders">
                                <h1 class="row">
                                    <?= $CourseTitle; ?>
                                </h1><p class="row">
                                    <?= date("d/m/Y H\hi", strtotime($StudentOrders['order_purchase_date'])); ?>
                                </p><p class="row row_pay">
                                    <span>$ <?= number_format($StudentOrders['order_price'], '2', ',', '.'); ?> (<?= $StudentOrders['order_currency']; ?>)</span> <img width="25" src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $StudentOrders['order_payment_type']; ?>.png" alt="<?= $StudentOrders['order_payment_type']; ?>" title="<?= $StudentOrders['order_payment_type']; ?>"/>
                                </p><p class="row">
                                    <span class="radius bar_<?= getWcHotmartStatusClass($StudentOrders['order_status']) ?>" id="<?= $StudentOrders['order_id']; ?>"><?= getWcHotmartStatus($StudentOrders['order_status']); ?></span>
                                </p>
                            </article><?php
                        endforeach;
                        ?>
                    </section>
                    <?php
                endif;

                //READ BONUS
                $Read->FullRead(
                        "SELECT "
                        . "b.course_id,"
                        . "b.bonus_course_id,"
                        . "b.bonus_wait,"
                        . "c.course_title,"
                        . "c.course_name,"
                        . "c.course_cover,"
                        . "e.enrollment_id,"
                        . "e.enrollment_start,"
                        . "e.enrollment_end "
                        . "FROM " . DB_EAD_COURSES_BONUS . " b "
                        . "INNER JOIN " . DB_EAD_COURSES . " c ON b.bonus_course_id = c.course_id "
                        . "LEFT JOIN " . DB_EAD_ENROLLMENTS . " e ON (b.course_id = e.course_id AND e.user_id = :user) "
                        . "WHERE b.course_id IN(SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user) "
                        . "AND (b.bonus_course_id NOT IN(SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user) "
                        . "OR e.enrollment_end > (SELECT enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user AND course_id = b.bonus_course_id) ) "
                        . "AND CASE WHEN b.bonus_ever = 2 THEN e.enrollment_start >= b.bonus_ever_date ELSE 1 = 1 END "
                        . "ORDER BY e.enrollment_end DESC, b.bonus_wait ASC", "user={$user_id}"
                );

                //SHOW BONUS
                $ShowBonus = null;
                if ($Read->getResult()):
                    $EnrollmentUpdateKey = array();
                    foreach ($Read->getResult() as $Bonus):
                        $Read->FullRead(
                                "SELECT "
                                . "e.enrollment_id "
                                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                . "WHERE e.user_id = :user "
                                . "AND e.course_id = :course", "user={$user_id}&course={$Bonus['bonus_course_id']}"
                        );

                        if ($Read->getResult()):
                            if (!in_array($Bonus['course_title'], $EnrollmentUpdateKey)):
                                $EnrollmentUpdateKey[] = $Bonus['course_title'];
                                $UpdateBonus = ['enrollment_bonus' => $Bonus['enrollment_id'], 'enrollment_end' => $Bonus['enrollment_end']];
                                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateBonus, "WHERE user_id = :user AND enrollment_id = :enroll", "user={$user_id}&enroll={$Read->getResult()[0]['enrollment_id']}");
                            endif;
                        else:
                            $Bonus['course_cover'] = (file_exists("uploads/{$Bonus['course_cover']}") && !is_dir("uploads/{$Bonus['course_cover']}") ? "uploads/{$Bonus['course_cover']}" : 'admin/_img/no_image.jpg');

                            //FREE DAYS || TIME
                            $DayThis = new DateTime(date("Y-m-d H:i:s"));
                            $DayPlay = new DateTime($Bonus['enrollment_start'] . "+{$Bonus['bonus_wait']}days");
                            $BonusDiff = $DayThis->diff($DayPlay);

                            if (($BonusDiff->h <= 1 || $BonusDiff->invert) && empty($EnrollmentFree)):
                                $EnrollmentFree = true;

                                $CreateUserBonus = ['user_id' => $user_id, 'course_id' => $Bonus['bonus_course_id'], 'enrollment_bonus' => $Bonus['enrollment_id'], 'enrollment_start' => date('Y-m-d H:i:s'), 'enrollment_end' => $Bonus['enrollment_end']];
                                $Create->ExeCreate(DB_EAD_ENROLLMENTS, $CreateUserBonus);

                                echo "<div class='wc_ead_win'>"
                                . "<div class='wc_ead_win_box'>"
                                . "<img src='" . BASE . "/tim.php?src={$Bonus['course_cover']}&w=" . round(IMAGE_W / 2) . "&h=" . round(IMAGE_H / 2) . "' alt='{$Bonus['course_title']}' title='{$Bonus['course_title']}'/>"
                                . "<div class='wc_ead_win_box_content al_center'>"
                                . "<p class='title icon-heart'>Uoolll {$user_name} :)</p>"
                                . "<p>O curso <b>{$Bonus['course_title']}</b> acaba de ser liberado como bônus em sua conta, e você já pode iniciar seus estudos!</p>"
                                . "<a title='Ver Curso Agora!' href='" . BASE . "/campus/curso/{$Bonus['course_name']}' class='btn btn_blue icon-lab'>Ver curso agora</a>"
                                . "<span title='Fechar Aviso!' class='m_left btn btn_red icon-cross jwc_ead_close_bonus'>Fechar</span>"
                                . "</div>"
                                . "</div>"
                                . "</div>";
                            else:
                                $ShowBonus .= "<article class='box box4'>"
                                        . "<img alt='{$Bonus['course_title']}' title='{$Bonus['course_title']}' src='" . BASE . "/tim.php?src={$Bonus['course_cover']}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "'/>"
                                        . "<div style='text-align: center;'>"
                                        . " <h1 style='margin: 0; padding: 10px; font-weight: 500; font-size: 0.65em; background: #333; color: #fff;'><span class='icon-lock wc_tooltip'><span class='wc_tooltip_balloon'>Curso {$Bonus['course_title']}</span>" . ($BonusDiff->days > 1 ? "Libera em {$BonusDiff->days} Dias" : ($BonusDiff->days == 1 ? "Libera em 1 Dia" : "Libera em {$BonusDiff->h} Horas")) . "</span></h1>"
                                        . "</div>"
                                        . "</article>";
                            endif;
                        endif;
                    endforeach;
                    if ($ShowBonus):
                        echo '<section>'
                        . '<header class="wc_ead_home_header" style="margin-top: 30px; font-size: 0.8em;">'
                        . '<h2 class="icon-rocket">Liberações Pendentes:</h2>'
                        . '<p>Confira bônus a serem liberados em sua conta!</p>'
                        . '</header>'
                        . $ShowBonus .
                        '</section>';
                    endif;
                endif;
                ?>
            </section>

            <section class="wc_tab_target ds_none" id="orders">
                <header class="wc_ead_home_header">
                    <h1 class="icon-cart">Minhas Capacitações:</h1>
                    <p>Confira as capacitações que você deve fazer!</p>
                </header>
                <?php
                $Page = (!empty($URL[2]) ? $URL[2] : 1);
                $Pager = new Pager(BASE . "/campus/home/", "<", ">", 3);
                $Pager->ExePager($Page, 10);
                $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user ORDER BY order_confirmation_purchase_date DESC, order_purchase_date DESC LIMIT :limit OFFSET :offset", "user={$user_id}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                if (!$Read->getResult()):
                    $Pager->ReturnPage();
                    echo "<div class='trigger trigger_info trigger-none icon-info al_center'>Olá {$user_name}, você ainda não tem pedidos!</div>";
                else:
                    foreach ($Read->getResult() as $StudentOrders):
                        $StudentOrders['order_currency'] = ($StudentOrders['order_currency'] ? $StudentOrders['order_currency'] : "BRL");

                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$StudentOrders['course_id']}");
                        $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$StudentOrders['order_product_id']} na Hotmart");
                        ?><article class="wc_ead_studend_orders">
                            <h1 class="row">
                                <?= $CourseTitle; ?>
                            </h1><p class="row">
                                <?= date("d/m/Y H\hi", strtotime($StudentOrders['order_purchase_date'])); ?>
                            </p><p class="row row_pay">
                                <span>$ <?= number_format($StudentOrders['order_price'], '2', ',', '.'); ?> (<?= $StudentOrders['order_currency']; ?>)</span> <img width="25" src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $StudentOrders['order_payment_type']; ?>.png" alt="<?= $StudentOrders['order_payment_type']; ?>" title="<?= $StudentOrders['order_payment_type']; ?>"/>
                            </p><p class="row">
                                <span class="radius bar_<?= getWcHotmartStatusClass($StudentOrders['order_status']); ?>" id="<?= $StudentOrders['order_id']; ?>"><?= getWcHotmartStatus($StudentOrders['order_status']); ?></span>
                            </p>
                        </article><?php
                    endforeach;
                    $Pager->ExePaginator(DB_EAD_ORDERS, "WHERE user_id = :user", "user={$user_id}", "#orders");
                    echo $Pager->getPaginator();
                endif;
                ?>
            </section>

            <article class="wc_tab_target ds_none" id="account">
                <header class="wc_ead_home_header">
                    <h1 class="icon-user">MEUS DADOS:</h1>
                    <p>Mantenha seus dados atualizados!</p>
                </header>
                <form name="wc_ead_student_account_update" action="" method="post" enctype="multipart/form-data">
                    <label class="label">
                        <span class="legend icon-image">Foto (<?= AVATAR_W; ?>x<?= AVATAR_H; ?>px, JPG ou PNG):</span>
                        <input type="file" name="user_thumb" class="wc_loadimage" id="user_thumb"/>
                    </label>

                    <h2 class="students_gerent_subtitle icon-user-tie">Dados Pessoais:</h2>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Primeiro nome:</span>
                            <input value="<?= $user_name; ?>" type="text" name="user_name" placeholder="Primeiro Nome:" required />
                        </label>

                        <label class="label">
                            <span class="legend">Sobrenome:</span>
                            <input value="<?= $user_lastname; ?>" type="text" name="user_lastname" placeholder="Sobrenome:" required />
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">CPF:</span>
                            <input value="<?= $user_document; ?>" type="text" name="user_document" class="formCpf" placeholder="CPF:" />
                        </label>

                        <label class="label">
                            <span class="legend">Gênero do Usuário:</span>
                            <select name="user_genre" required>
                                <option selected disabled value="">Selecione o Gênero do Usuário:</option>
                                <option value="1" <?= ($user_genre == 1 ? 'selected="selected"' : ''); ?>>Masculino</option>
                                <option value="2" <?= ($user_genre == 2 ? 'selected="selected"' : ''); ?>>Feminino</option>
                            </select>
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Telefone:</span>
                            <input value="<?= $user_telephone; ?>" class="formPhone" type="text" name="user_telephone" placeholder="(55) 5555.5555" />
                        </label>

                        <label class="label">
                            <span class="legend">Celular:</span>
                            <input value="<?= $user_cell; ?>" class="formPhone" type="text" name="user_cell" placeholder="(55) 5555.5555" />
                        </label>
                    </div>

                    <h2 class="students_gerent_subtitle icon-rss">Social:</h2>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend icon-facebook2">Facebook:</span>
                            <input value="<?= $user_facebook; ?>" type="url" name="user_facebook" placeholder="https://www.facebook.com/username" />
                        </label>

                        <label class="label">
                            <span class="legend icon-twitter">Twitter:</span>
                            <input value="<?= $user_twitter; ?>" type="url" name="user_twitter" placeholder="https://www.twitter.com/username" />
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend icon-youtube2">YouTube:</span>
                            <input value="<?= $user_youtube; ?>" type="url" name="user_youtube" placeholder="https://www.youtube.com/username" />
                        </label>

                        <label class="label">
                            <span class="legend icon-google-plus2">Google +:</span>
                            <input value="<?= $user_google; ?>" type="url" name="user_google" placeholder="https://plus.google.com/+username" />
                        </label>
                    </div>

                    <div class="form_actions">
                        <button name="public" value="1" class="btn btn_green icon-share" style="margin-left: 5px;">Atualizar Meus Dados!</button>
                        <img class="jwc_load" style="margin-left: 10px;" alt="Atualizando Dados!" title="Atualizando Dados!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                    </div>
                </form>
            </article>

            <article class="wc_tab_target ds_none" id="address">
                <header class="wc_ead_home_header">
                    <h1 class="icon-location">MEU ENDEREÇO:</h1>
                    <p>Onde você esta morando <?= $user_name; ?>?</p>
                </header>
                <form name="wc_ead_student_address_update" action="" method="post">
                    <label class="label">
                        <span class="legend">Nome do Endereço:</span>
                        <input name="addr_name" style="font-size: 1.3em;" value="<?= $addr_name; ?>" placeholder="Ex: Minha Casa:" required/>
                    </label>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">CEP:</span>
                            <input name="addr_zipcode" value="<?= $addr_zipcode; ?>" class="formCep wc_getCep" placeholder="Informe o CEP:" required/>
                        </label>

                        <label class="label">
                            <span class="legend">Rua:</span>
                            <input class="wc_logradouro" name="addr_street" value="<?= $addr_street; ?>" placeholder="Nome da Rua:" required/>
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Número:</span>
                            <input name="addr_number" value="<?= $addr_number; ?>" placeholder="Número:" required/>
                        </label>

                        <label class="label">
                            <span class="legend">Complemento:</span>
                            <input class="wc_complemento" name="addr_complement" value="<?= $addr_complement; ?>" placeholder="Ex: Casa, Apto, Etc:"/>
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Bairro:</span>
                            <input class="wc_bairro" name="addr_district" value="<?= $addr_district; ?>" placeholder="Nome do Bairro:" required/>
                        </label>

                        <label class="label">
                            <span class="legend">Cidade:</span>
                            <input class="wc_localidade" name="addr_city" value="<?= $addr_city; ?>" placeholder="Informe a Cidade:" required/>
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Estado (UF):</span>
                            <input class="wc_uf" name="addr_state" value="<?= $addr_state; ?>" maxlength="2" placeholder="Ex: SP" required/>
                        </label>

                        <label class="label">
                            <span class="legend">País:</span>
                            <input name="addr_country" value="<?= ($addr_country ? $addr_country : 'Brasil'); ?>" required/>
                        </label>
                    </div>

                    <div class="form_actions">
                        <button name="public" value="1" class="btn btn_green icon-share" style="margin-left: 5px;">Atualizar Meu Endereço!</button>
                        <img class="jwc_load" style="margin-left: 10px;" alt="Atualizando Endereço!" title="Atualizando Endereço!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                    </div>
                </form>
            </article>

            <article class="wc_tab_target ds_none" id="access">
                <header class="wc_ead_home_header">
                    <h1 class="icon-key2">MINHA SENHA:</h1>
                    <p>Atualize sua senha de acesso!</p>
                </header>
                <form name="wc_ead_student_password_update" action="" method="post" enctype="multipart/form-data">

                    <label class="label">
                        <span class="legend">E-mail:</span>
                        <input value="<?= $user_email; ?>" type="email" readonly="readonly" name="" placeholder="E-mail:" required />
                    </label>

                    <label class="label">
                        <span class="legend">Nova Senha:</span>
                        <input value="" type="password" name="user_password" placeholder="Senha:" required />
                    </label>

                    <label class="label">
                        <span class="legend">Repita a nova senha</span>
                        <input value="" type="password" name="user_password_re" placeholder="Senha:" required />
                    </label>

                    <div class="form_actions">
                        <button name="public" value="1" class="btn btn_green icon-share" style="margin-left: 5px;">Atualizar Minha Senha!</button>
                        <img class="jwc_load" style="margin-left: 10px;" alt="Atualizando Senha!" title="Atualizando Senha!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                    </div>
                </form>
            </article>
        <?php
        endif;
        ?>
    </div>
</div>