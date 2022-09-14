<?php
$AdminLevel = LEVEL_WC_EAD_STUDENTS;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$Student = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($Student):
    $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$Student}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        $user_name = ($user_name ? $user_name : 'Novo');
        $user_lastname = ($user_lastname ? $user_lastname : 'Usuário');
        $user_thumb = (file_exists("../uploads/{$user_thumb}") && !is_dir("../uploads/{$user_thumb}") ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um aluno que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=teach/students');
    endif;
else:
    $CreateUserDefault = [
        "user_registration" => date('Y-m-d H:i:s'),
        "user_level" => 1
    ];
    $Create->ExeCreate(DB_USERS, $CreateUserDefault);
    header("Location: dashboard.php?wc=teach/students_gerent&id={$Create->getResult()}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-user-check">Gerenciar Aluno</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students">Alunos</a>
            <span class="crumb">/</span>
            Gerenciar Aluno
        </p>
    </div>

    <div class="dashboard_header_search" style="font-size: 0.875em; margin-top: 16px;" id="<?= $user_id; ?>">
        <span rel='dashboard_header_search' class='j_delete_action icon-warning btn btn_red' id='<?= $user_id; ?>'>Deletar Aluno!</span>
        <span rel='dashboard_header_search' callback='Courses' callback_action='student_remove' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='<?= $user_id; ?>'>EXCLUIR AGORA!</span>
    </div>
</header>
<div class="dashboard_content students_gerent">
    <div class="box box70">
        <div class="student_gerent_home wc_tab_target wc_active" id="profile">
            <div class="student_gerent_home_left">

                <?php
                //COUNT ENROLLMENTS
                $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE user_id = :user", "user={$user_id}");
                $CountCourse = ($Read->getResult() ? $Read->getRowCount() : 0);

                //COUNT CERTIFICATES
                $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user", "user={$user_id}");
                $CountCertificate = ($Read->getResult() ? $Read->getRowCount() : 0);

                //COUNT ORDERS
                $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user", "user={$user_id}");
                $CountOrder = ($Read->getResult() ? $Read->getRowCount() : 0);
                
                //COUNT SUPPORT
                $Read->ExeRead(DB_EAD_SUPPORT, "WHERE user_id = :user", "user={$user_id}");
                $CountSupport = ($Read->getResult() ? $Read->getRowCount() : 0);
                ?>

                <div class="student_gerent_home_data">
                    <div class="panel_header default">
                        <h2 class="icon-user-check"><?= $user_name; ?> <?= $user_lastname; ?>:</h2>
                    </div>
                    <div class="panel">
                        <p><span class="icon-user-check">Cadastro:</span> <?= date('d/m/Y', strtotime($user_registration)); ?></p>
                        <p><span class="icon-pencil">Atualização:</span> <?= date('d/m/Y', strtotime(($user_lastupdate ? $user_lastupdate : $user_registration))); ?></p>
                        <p><span class="icon-pie-chart">Acesso:</span> <?= (!empty($user_lastaccess) ? date('d/m/Y H\hi', strtotime($user_lastaccess)) : 'Nunca acessou!'); ?></p>
                        <p><span class="icon-lab">Cursos:</span> <?= $CountCourse; ?></p>
                        <p><span class="icon-trophy">Certificados:</span> <?= $CountCertificate; ?></p>
                        <p><span class="icon-cart">Pedidos:</span> <?= $CountOrder; ?></p>
                        <p><span class="icon-bubbles2">Tickets:</span> <a href="dashboard.php?wc=teach/support&user_id=<?= $user_id; ?>" class="btn btn_green icon-eye "><?= $CountSupport; ?></a><p>
                    </div>
                </div>


                <div class="trigger student_gerent_home_alert m_top" style="padding: 30px;">
                    <?php
                    //VERIFY ACTION TO BLOCK/UNBLOCK USER
                    if (empty($user_blocking_reason)):
                        ?>
                        <b class="icon-lock">GERAR BLOQUEIO:</b> Identificou uso indevido da conta e deseja bloquear?
                        <form name="user_manager" action="" method="post" enctype="multipart/form-data" style="margin-top: 25px;">
                            <input type="hidden" name="callback" value="Users"/>
                            <input type = "hidden" name = "callback_action" value = "block_user"/>

                            <input type = "hidden" name = "user_id" value = "<?= $user_id; ?>"/>
                            <input type = "hidden" name = "admin_id" value = "<?= $_SESSION['userLogin']['user_id']; ?>"/>

                            <textarea name = "user_blocking_reason" rows = "5" class = "radius user_blocking_reason wc_value" required placeholder = "Insira o motivo do bloqueio (Será visível para o aluno):"></textarea>
                            <div class = "wc_actions m_top">
                                <button class = "btn btn_red icon-cancel-circle">BLOQUEAR ACESSO!</button>
                                <img class = "form_load none" style = "margin-left: 10px;" alt = "Enviando Requisição!" title = "Enviando Requisição!" src = "_img/load.gif"/>
                            </div>
                        </form>
                        <?php
                    else:
                        ?>
                        <b class="icon-lock">GERAR DESBLOQUEIO:</b> Resolvido o problema com aluno e deseja desbloquear?
                        <form name="user_manager" action="" method="post" enctype="multipart/form-data" style="margin-top: 25px;">
                            <input type="hidden" name="callback" value="Users"/>
                            <input type="hidden" name="callback_action" value="unblock_user"/>

                            <input type="hidden" name="user_id" value="<?= $user_id; ?>"/>
                            <input type="hidden" name="admin_id" value="<?= $_SESSION['userLogin']['user_id']; ?>"/>

                            <textarea name = "user_blocking_reason" rows = "5" class = "radius user_blocking_reason wc_value" required placeholder = "Insira o motivo do bloqueio (Será visível para o aluno):"></textarea>
                            <div class = "wc_actions m_top">
                                <button class = "btn btn_green icon-plus">DESBLOQUEAR ACESSO!</button>
                                <img class = "form_load none" style = "margin-left: 10px;" alt = "Enviando Requisição!" title = "Enviando Requisição!" src = "_img/load.gif"/>
                            </div>
                        </form>
                    <?php
                    endif;
                    ?>
                </div>

            </div><div class = "student_gerent_home_anotations">
                <div class = "panel_header default">
                    <span><span class = "icon-trello icon-notext btn btn_blue reload_notes wc_tooltip" id="<?= $user_id; ?>" rel="list_notes_all"><span class="wc_tooltip_balloon">Ver notas em Rascunho</span></span></span>
                    <h2 class = "icon-bubbles2">Anotações:</h2>
                </div>
                <div class = "panel" style = "padding-bottom: 1px;">
                    <div class = "j_content_note">
                        <?php
                        $Read->ExeRead(DB_USERS_NOTES, "WHERE user_id = :user AND note_status IS NULL ORDER BY note_datetime DESC", "user={$user_id}");

                        if ($Read->getRowCount() == 0):
                            echo "<div class='trigger trigger_info al_center icon-cancel-circle' style='margin-bottom: 0px;'>Aluno sem notas até o momento...</div>";
                        else:
                            foreach ($Read->getResult() as $Note):
                                $Read->LinkResult(DB_USERS, 'user_id', $Note['admin_id']);
                                ?>
                                <article class="student_gerent_home_anotation" id="<?= $Note['note_id']; ?>">
                                    <form>
                                        <span class="icon-cross icon-notext student_gerent_home_anotation_remove j_delete_action_confirm" callback='Users' callback_action='note_draft' id="<?= $Note['note_id']; ?>" rel="student_gerent_home_anotation"></span>
                                    </form>
                                    <div class="student_gerent_home_anotation_content icon-pushpin">
                                        <?= nl2br($Note['note_text']); ?>
                                        <p class="icon-calendar"><?= date('d/m/Y H:i', strtotime($Note['note_datetime'])) . ' por ' . $Read->getResult()[0]['user_name'] . ' ' . $Read->getResult()[0]['user_lastname']; ?></p>
                                    </div>
                                </article>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div><div class="student_gerent_home_anotations_act">
                    <form name="user_manager" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="callback" value="Users"/>
                        <input type="hidden" name="callback_action" value="note_add"/>
                        <input type="hidden" name="user_id" value="<?= $user_id; ?>"/>
                        <input type="hidden" name="admin_id" value="<?= $_SESSION['userLogin']['user_id']; ?>"/>

                        <textarea rows="3" class="radius" name="note_text"></textarea>

                        <div class="wc_actions m_top">
                            <button class="btn btn_green icon-plus">Anotação</button>
                            <img class="form_load none fl_right" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="students_gerent_profile  wc_tab_target none" id="gerent">
            <div class="panel_header default">
                <h2 class="icon-user-check">Gerenciar <?= $user_name; ?>:</h2>
            </div>

            <div class="box_content">
                <form name="user_manager" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Users"/>
                    <input type="hidden" name="callback_action" value="manager"/>
                    <input type="hidden" name="user_id" value="<?= $user_id; ?>"/>
                    <input type="hidden" name="user_level" value="<?= $user_level; ?>"/>

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
                            <span class="legend">E-mail:</span>
                            <input value="<?= $user_email; ?>" type="email" name="user_email" placeholder="E-mail:" required />
                        </label>

                        <label class="label">
                            <span class="legend">Senha:</span>
                            <input value="" type="password" name="user_password" placeholder="Senha:" />
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Foto (<?= AVATAR_W; ?>x<?= AVATAR_H; ?>px, JPG ou PNG):</span>
                            <input type="file" name="user_thumb" class="wc_loadimage" />
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

                    <div class="clear"></div>
                    <h3 class="students_gerent_subtitle icon-user-tie m_botton">Administrativo:</h3>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">CPF:</span>
                            <input value="<?= $user_document; ?>" type="text" name="user_document" class="formCpf" placeholder="CPF:" />
                        </label>

                        <label class="label">
                            <span class="legend">Data de Nascimento:</span>
                            <input value="<?= (!empty($user_datebirth) ? date("d/m/Y", strtotime($user_datebirth)) : null); ?>" type="text" name="user_datebirth" class="jwc_datepicker formDate" placeholder="Data de Nascimento:" />
                        </label>
                    </div>

                    <?php if ($_SESSION['userLogin']['user_level'] >= LEVEL_WC_USERS): ?>
                        <label class="label">
                            <span class="legend">Nível de acesso:</span>
                            <select name="user_level" required>
                                <option selected disabled value="">Selecione o nível de acesso:</option>
                                <?php
                                $NivelDeAcesso = getWcLevel();
                                foreach ($NivelDeAcesso as $Nivel => $Desc):
                                    if ($Nivel <= $_SESSION['userLogin']['user_level']):
                                        echo "<option";
                                        if ($Nivel == $user_level):
                                            echo " selected='selected'";
                                        endif;
                                        echo " value='{$Nivel}'>{$Desc}</option>";
                                    endif;
                                endforeach;
                                ?>
                            </select>
                        </label>
                    <?php endif; ?>


                    <div class="clear"></div>
                    <h3 class="students_gerent_subtitle icon-phone">Contatos:</h3>

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

                    <div class="clear"></div>
                    <h3 class="students_gerent_subtitle icon-rss">Social:</h3>

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

                    <img class="form_load none fl_right" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    <button name="public" value="1" class="btn btn_green fl_right icon-share" style="margin-left: 5px;">Atualizar Usuário!</button>
                    <div class="clear"></div>
                </form>
            </div>
        </div>

        <div class="wc_tab_target box_conf none" id="courses">
            <div class="panel_header default">
                <h2 class="icon-lab">Cursos de <?= $user_name; ?>:</h2>
            </div>
            <div class="box_content">
                <div class="j_content">
                    <?php
                    $Read->FullRead("SELECT " . DB_EAD_ENROLLMENTS . ".*, " . DB_EAD_COURSES . ".* FROM " . DB_EAD_ENROLLMENTS . ", " . DB_EAD_COURSES . " WHERE " . DB_EAD_ENROLLMENTS . ".user_id = :user AND " . DB_EAD_ENROLLMENTS . ".course_id = " . DB_EAD_COURSES . ".course_id ORDER BY " . DB_EAD_COURSES . ".course_order ASC, " . DB_EAD_COURSES . ".course_title ASC", "user={$user_id}");
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $Encollment):
                            extract($Encollment);
                            $Cover = (file_exists("../uploads/{$course_cover}") && !is_dir("../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

                            $DayNow = new DateTime();
                            $DayEnd = new DateTime($enrollment_end);
                            $DayDif = $DayNow->diff($DayEnd);

                            //PROGRESS
                            $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
                            $ClassCount = $Read->getResult()[0]['ClassCount'];

                            $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course", "user={$user_id}&course={$course_id}");
                            $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                            $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");

                            //SUPPORT
                            $Read->FullRead("SELECT COUNT(support_id) AS SupportOpen FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course)) AND support_status = 1", "user={$user_id}&course={$course_id}");
                            $SupportOpen = $Read->getResult()[0]['SupportOpen'];

                            $Read->FullRead("SELECT COUNT(support_id) AS SupportSolved FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course)) AND support_status > 1", "user={$user_id}&course={$course_id}");
                            $SupportSolved = $Read->getResult()[0]['SupportSolved'];
                            ?><article class="box box33 students_gerent_course" style="margin:0;" id="<?= $enrollment_id; ?>">
                            <?php
                            if ($enrollment_bonus):
                                $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_id = :enrol)", "enrol={$enrollment_bonus}");
                                if ($Read->getResult()):
                                    echo "<p class='students_gerent_course_bonus wc_tooltip icon-heart icon-notext'><span>Bônus do curso {$Read->getResult()[0]['course_title']}</span></p>";
                                endif;
                            endif;
                            ?>
                                <img src="../tim.php?src=<?= $Cover; ?>&w=<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>" title="<?= $course_title; ?>" alt="<?= $course_title; ?>"/>
                                <div class="upload_bar"><span class="upload_progress" style="width: <?= $CourseCompletedPercent; ?>%"><?= $CourseCompletedPercent; ?>%</span></div>
                                <div class="students_gerent_course_content">
                                    <h1><?= $course_title; ?></h1>
                                    <p>Último acesso em: <?= ($enrollment_access ? date('d/m/Y H\hi', strtotime($enrollment_access)) : "<b>NUNCA ACESSOU</b>"); ?></p>
                                    <p>Tickets abertos: <?= str_pad($SupportOpen, 2, 0, 0); ?> | Resolvidos: <?= str_pad($SupportSolved, 2, 0, 0); ?></p>
                                    <p>Liberado de: <?= date('d/m/Y', strtotime($enrollment_start)); ?> a: <?= ($enrollment_end ? date('d/m/Y', strtotime($enrollment_end)) : "<b>PARA SEMPRE</b>"); ?></p>
                                    <p style="border-bottom: none;">
                                        <?php
                                        if (!$enrollment_end && !$enrollment_bonus):
                                            echo "<b class='icon-spinner9'>Curso liberado para sempre!</b>";
                                        elseif (!$enrollment_end && $enrollment_bonus):
                                            echo "<b class='icon-heart'>Bônus de Matrícula!</b>";
                                        elseif (!$DayDif->invert):
                                            echo "<b class='icon-checkmark' style='color: #00B494;'>Assinatura Expira em {$DayDif->days} dias!</b>";
                                        else:
                                            echo "<b class='icon-cross' style='color: #C54550;'>Assinatura Vencida a {$DayDif->days} dias!</b>";
                                        endif;
                                        ?>
                                    </p>
                                </div>
                                <div class="students_gerent_course_actions">
                                    <a href="dashboard.php?wc=teach/students_course&enrollment=<?= $enrollment_id; ?>&student=<?= $user_id; ?>" title="Ver Andamento" class="icon-stats-dots icon-notext btn btn_green"></a>
                                    <a href="dashboard.php?wc=teach/students_enrollment&enrollment=<?= $enrollment_id; ?>" title="Editar Matrícula" class="icon-pencil icon-notext btn btn_blue"></a>
                                    <span rel="students_gerent_course" class="j_delete_action icon-cancel-circle icon-notext btn btn_red" id="<?= $enrollment_id; ?>"></span>
                                    <span rel="students_gerent_course" callback="Courses" callback_action="student_course_remove" class="j_delete_action_confirm icon-warning btn btn_yellow" style="display: none" id="<?= $enrollment_id; ?>">Excluir?</span>
                                </div>
                            </article><?php
                        endforeach;
                    else:
                        echo "<div class='trigger trigger_info icon-info al_center'>{$user_name} ainda não esta matriculado em nenhum curso!</div>";
                    endif;
                    ?>
                </div>

                <form style="padding: 20px 10px 0 10px;" name="student_course_add" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Courses"/>
                    <input type="hidden" name="callback_action" value="student_course_add"/>
                    <input type="hidden" name="user_id" value="<?= $user_id; ?>"/>
                    <div class="label_50">
                        <label class="label">
                            <select name="course_id" required>
                                <option value="">Adicionar para o curso:</option>
                                <?php
                                $Read->ExeRead(DB_EAD_COURSES, "ORDER BY course_title ASC");
                                if (!$Read->getResult()):
                                    echo "<option value='' disabled='disabled'>Ainda não existem cursos cadastrados!</option>";
                                else:
                                    foreach ($Read->getResult() as $Courses):
                                        extract($Courses);
                                        echo "<option name='course_id' value='{$course_id}'>{$course_title}</option>";
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </label>

                        <label class="label">
                            <input name="course_end" type="number" max="120" placeholder="Leberar por X meses (0 para sempre)" required>
                        </label>
                    </div>

                    <div class="wc_actions">
                        <label class="label_check label_publish"><input style="margin-top: -1px;" type="checkbox" value="1" name="send_notification">Notificar Aluno?</label>
                        <button class="btn btn_green icon-lab">Liberar Curso!</button>
                        <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                </form>
            </div>
        </div>

        <div class="wc_tab_target box_conf none" id="certification">
            <div class="panel_header default">
                <h2 class="icon-trophy">Certificados de <?= $user_name; ?>:</h2>
            </div>
            <div class="panel">
                <?php
                $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user", "user={$user_id}");
                if (!$Read->getResult()):
                    echo "<div class='trigger trigger_none trigger_info icon-info al_center'>{$user_name} ainda não possui certificados emitidos!</div>";
                else:
                    foreach ($Read->getResult() as $Certification):

                        $Read->LinkResult(DB_EAD_COURSES, 'course_id', $Certification['course_id']);
                        $CertificationCourse = $Read->getResult()[0];
                        ?>
                        <article class="student_gerent_certification box box33">
                            <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $CertificationCourse['course_certification_mockup']; ?>&w=500" alt="Certificado <?= $CertificationCourse['course_title']; ?>" title="Certificado <?= $CertificationCourse['course_title']; ?>"/>
                            <div class="student_gerent_certification_content">
                                <h1 class="student_gerent_certification_info icon-trophy"><?= $CertificationCourse['course_title']; ?></h1>
                                <p class="student_gerent_certification_info">Emitido dia <?= date("d/m/Y", strtotime($Certification['certificate_issued'])); ?></p>
                            </div>
                        </article>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>

        <div class="wc_tab_target box_conf none" id="orders">
            <div class="panel_header default">
                <h2 class="icon-cart">Pedidos de <?= $user_name; ?>:</h2>
            </div>

            <div class="panel">
                <?php
                echo "<div class='student_gerent_orders_detail'>
                <div class='student_gerent_orders_detail_content'>
                    <div class='j_order_detail'></div>
                    <p class='close'><span class='icon icon-cross icon-notext btn btn_red order_close j_student_order_close student_gerent_orders_detail_content_close'></span></p>
                </div></div>";

                $getPage = (filter_input(INPUT_GET, 'page'));
                $Page = ($getPage ? $getPage : 1);
                $Pager = new Pager("dashboard.php?wc=teach/students_gerent&id={$user_id}&page=", "<", ">", 3);
                $Pager->ExePager($Page, 8);
                $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user ORDER BY order_confirmation_purchase_date DESC, order_purchase_date DESC LIMIT :limit OFFSET :offset", "user={$user_id}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                if (!$Read->getResult()):
                    $Pager->ReturnPage();
                    echo "<div class='trigger trigger_info trigger_none icon-info al_center'>{$user_name} ainda não efetuou nenhum pedido!</div><div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $EadOrder):
                        extract($EadOrder);
                        $order_currency = ($order_currency ? $order_currency : "BRL");

                        $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$course_id}");
                        $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$order_product_id} na Hotmart");
                        ?><article class="student_gerent_orders">
                            <h1 class="row">
                                <?= $CourseTitle; ?>
                            </h1><p class="row">
                                <?= date("d/m/Y H\hi", strtotime($order_purchase_date)); ?>
                            </p><p class="row row_pay">
                                <span>$ <?= number_format($order_price, '2', ',', '.'); ?>&nbsp;(<?= $order_currency; ?>)</span>&nbsp;&nbsp;<img width="25" src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $order_payment_type; ?>.png" alt="" title=""/>
                            </p><p class="row">
                                <span class="btn btn_<?= getWcHotmartStatusClass($order_status); ?> j_student_order_open jwc_open_<?= $order_id; ?>" id="<?= $order_id; ?>"><?= getWcHotmartStatus($order_status); ?></span>
                            </p>
                        </article><?php
                    endforeach;

                    $Pager->ExePaginator(DB_EAD_ORDERS, "WHERE user_id = :user", "user={$user_id}", '#orders');
                    echo $Pager->getPaginator();
                    echo "<div class='clear'></div>";
                endif;
                ?>
            </div>
        </div>

        <div class="wc_tab_target box_conf none" id="address">
            <div class="panel_header default">
                <h2 class="icon-location">Endereço de <?= $user_name; ?>:</h2>
            </div>

            <div class="panel">
                <?php
                $Read->ExeRead(DB_USERS_ADDR, "WHERE user_id = :id", "id={$user_id}");
                if (!$Read->getResult()):
                    $CreateAddr = ['user_id' => $user_id, 'addr_key' => 1, "addr_name" => "Meu Endereço"];
                    $Create->ExeCreate(DB_USERS_ADDR, $CreateAddr);

                    $Read->ExeRead(DB_USERS_ADDR, "WHERE user_id = :id", "id={$user_id}");
                    extract($Read->getResult()[0]);
                else:
                    extract($Read->getResult()[0]);
                endif;
                ?>

                <form name="user_add_address" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Users"/>
                    <input type="hidden" name="callback_action" value="addr_add"/>
                    <input type="hidden" name="addr_id" value="<?= $addr_id; ?>"/>

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

                    <img class="form_load none fl_right" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    <button name="public" value="1" class="btn btn_green fl_right icon-share" style="margin-left: 5px;">Atualizar Endereço!</button>
                    <div class="clear"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="box box30">
        <?php
        if (!empty($user_blocking_reason)):
            ?>
            <div class="trigger trigger_error al_center icon-cancel-circle" style="margin-bottom: 0px;">ALUNO BLOQUEADO</div>
            <?php
        endif;
        ?>
        <img class="user_thumb" style="width: 100%;" src="../tim.php?src=<?= $user_thumb; ?>&w=400&h=400" alt="" title=""/>

        <div class="panel">
            <div class="box_conf_menu no_icon">
                <a class='conf_menu wc_tab wc_active' href='#profile'><span class="icon-user">Perfil</span></a>
                <a class='conf_menu wc_tab' href='#gerent'><span class="icon-cog">Gestão</span></a>
                <a class='conf_menu wc_tab' href='#courses'><span class="icon-lab">Cursos</span></a>
                <?php if (EAD_STUDENT_CERTIFICATION): ?>
                    <a class='conf_menu wc_tab' href='#certification'><span class="icon-trophy">Certificados</span></a>
                <?php endif; ?>
                <a class='conf_menu wc_tab' href='#orders'><span class="icon-cart">Pedidos</span></a>
                <a class='conf_menu wc_tab' href='#address'><span class="icon-location">Endereço</span></a>
            </div>
        </div>
    </div>

</div>
