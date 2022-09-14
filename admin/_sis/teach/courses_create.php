<?php
$AdminLevel = LEVEL_WC_EAD_COURSES;
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

$CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($CourseId):
    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :id", "id={$CourseId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um curso que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=teach/courses');
    endif;
else:
    $PostCreate = ['course_created' => date('Y-m-d H:i:s'), 'course_updated' => date('Y-m-d H:i:s'), 'course_status' => 0];
    $Create->ExeCreate(DB_EAD_COURSES, $PostCreate);
    header('Location: dashboard.php?wc=teach/courses_create&id=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-new-tab"><?= $course_title ? "Editar " . $course_title : 'Novo Curso'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_gerent&id=<?= $course_id; ?>">Gerenciar <?= $course_title; ?></a>
        </p>
    </div>

    <div class="dashboard_header_search" id="<?= $CourseId; ?>">
        <a title="Gerenciar Curso!" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab">Gerenciar Curso!</a>
        <span rel='dashboard_header_search' class='j_delete_action icon-warning btn btn_red' id='<?= $CourseId; ?>'>Deletar Curso!</span>
        <span rel='dashboard_header_search' callback='Courses' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='<?= $CourseId; ?>'>EXCLUIR AGORA!</span>
    </div>
</header>

<div class="dashboard_content">
    <div class="box box70">
        <div class="panel wc_tab_target wc_active" id="course">
            <form class="auto_save" name="course_create" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="manager"/>
                <input type="hidden" name="course_id" value="<?= $CourseId; ?>"/>
                <label class="label">
                    <span class="legend">Curso:</span>
                    <input style="font-size: 1.4em;" type="text" name="course_title" value="<?= $course_title; ?>" required/>
                </label>

                <label class="label">
                    <span class="legend">Headline:</span>
                    <input type="text" name="course_headline" value="<?= $course_headline; ?>" required/>
                </label>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea class="work_mce" name="course_desc" rows="3"><?= $course_desc; ?></textarea>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Segmentação:</span>
                        <select name="course_segment">
                            <option value="">Curso sem Segmentação!</option>
                            <?php
                            $Read->FullRead("SELECT segment_id, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " ORDER BY segment_order ASC, segment_title ASC");
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $CourseSegment):
                                    echo "<option";
                                    if ($CourseSegment['segment_id'] == $course_segment):
                                        echo " selected='selected'";
                                    endif;
                                    echo " value='{$CourseSegment['segment_id']}'>{$CourseSegment['segment_title']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Preço:</span>
                        <input type="text" name="course_vendor_price" value="<?= $course_vendor_price ? number_format($course_vendor_price, '2', ',', '.') : "0,00"; ?>" placeholder="Preço do Curso:"/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Capa: (JPG <?= IMAGE_W; ?>x<?= IMAGE_H; ?>px)</span>
                        <input type="file" class="wc_loadimage" name="course_cover"/>
                    </label>

                    <label class="label">
                        <span class="legend icon-link">Link Alternativo:</span>
                        <input type="text" name="course_name" value="<?= $course_name; ?>" placeholder="Link do Curso:"/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">DIA:</span>
                        <input type="text" class="formTime" name="course_created" value="<?= $course_created ? date('d/m/Y H:i', strtotime($course_created)) : date('d/m/Y H:i'); ?>" required/>
                    </label>

                    <label class="label">
                        <span class="legend">AUTOR:</span>
                        <select name="course_author" required>
                            <option value="<?= $Admin['user_id']; ?>"><?= $Admin['user_name']; ?> <?= $Admin['user_lastname']; ?></option>
                            <?php
                            $Read->FullRead("SELECT user_id, user_name, user_lastname FROM " . DB_USERS . " WHERE user_level >= :lv AND user_id != :uid", "lv=6&uid={$Admin['user_id']}");
                            if ($Read->getResult()):
                                foreach ($Read->getResult() as $PostAuthors):
                                    echo "<option";
                                    if ($PostAuthors['user_id'] == $course_author):
                                        echo " selected='selected'";
                                    endif;
                                    echo " value='{$PostAuthors['user_id']}'>{$PostAuthors['user_name']} {$PostAuthors['user_lastname']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>
                </div>

                <div class="wc_actions">
                    <label class="label_check label_publish <?= ($course_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="course_status" <?= ($course_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                    <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
            </form>
        </div>

        <div class="panel wc_tab_target none" id="bonus">
            <div class="j_content">
                <?php
                $Read->ExeRead(DB_EAD_COURSES_BONUS, "WHERE course_id = :course ORDER BY bonus_wait ASC", "course={$CourseId}");
                if (!$Read->getResult()):
                    echo "<div class='trigger trigger_info icon-info al_center'>Ainda não existem bônus para o curso {$course_title}!</div><div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $Bonus):

                        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :bonus ORDER BY course_order ASC, course_name ASC", "bonus={$Bonus['bonus_course_id']}");
                        $BonusCourse = $Read->getResult()[0];

                        $BonusCover = (file_exists("../uploads/{$BonusCourse['course_cover']}") && !is_dir("../uploads/{$BonusCourse['course_cover']}") ? "uploads/{$BonusCourse['course_cover']}" : 'admin/_img/no_image.jpg');
                        ?><article class="box box33 students_gerent_course" style="margin:0;" id="<?= $Bonus['bonus_id']; ?>">
                            <img src="../tim.php?src=<?= $BonusCover; ?>&w=<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>" title="<?= $BonusCourse['course_title']; ?>" alt="<?= $BonusCourse['course_title']; ?>"/>
                            <div class="students_gerent_course_content">
                                <h1><?= $BonusCourse['course_title']; ?></h1>
                                <p><?= ($Bonus['bonus_wait'] ? "Aguardar por {$Bonus['bonus_wait']} dias" : 'Liberar Imediatamente'); ?></p>
                                <p><?= ($Bonus['bonus_ever'] == 1 ? 'Para todas as matriculas' : "Matrículas a partir de " . date("d/m/Y", strtotime($Bonus['bonus_ever_date']))); ?></p>
                            </div>
                            <div class="students_gerent_course_actions">
                                <span rel="students_gerent_course" class="j_delete_action icon-cancel-circle btn btn_red" id="<?= $Bonus['bonus_id']; ?>">Excluir Bônus!</span>
                                <span rel="students_gerent_course" callback="Courses" callback_action="courses_bonus_remove" class="j_delete_action_confirm icon-warning btn btn_yellow" style="display: none" id="<?= $Bonus['bonus_id']; ?>">Excluir Agora?</span>
                            </div>
                        </article><?php
                    endforeach;
                endif;
                ?>
            </div>
            <form style="padding: 20px 10px 0 10px;" name="student_course_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="courses_bonus_add"/>
                <input type="hidden" name="course_id" value="<?= $course_id; ?>"/>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Curso Bônus:</span>
                        <select name="bonus_course_id" required>
                            <option value="">Selecione o Curso:</option>
                            <?php
                            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id != :course ORDER BY course_title ASC", "course={$course_id}");
                            if (!$Read->getResult()):
                                echo "<option value='' disabled='disabled'>Cadastre outro curso para bônus!</option>";
                            else:
                                foreach ($Read->getResult() as $Courses):
                                    echo "<option value='{$Courses['course_id']}'>{$Courses['course_title']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Liberar depois de XX dias:</span>
                        <input name="bonus_wait" type="number" max="120" placeholder="Liberar em X dias!" required>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Liberar para:</span>
                        <select name="bonus_ever" required>
                            <option value="1">Todas as Matrículas!</option>
                            <option value="2">Novas Matrículas!</option>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend icon-warning">Novas matrículas a partir de:</span>
                        <input type="text" name="bonus_ever_date" class="formDate" placeholder="Somente se para novas matrículas:"/>
                    </label>
                </div>

                <div class="wc_actions">
                    <button style="padding: 9px 12px;" class="btn btn_green icon-rocket">CADASTRAR BÔNUS</button>
                    <img class="form_load none" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
            </form>
        </div>

        <div class="panel wc_tab_target none" id="hotmart">
            <div class="trigger trigger_none trigger_info">
                <p class="icon-link" style="margin-bottom: 10px; color: #FFF;">API e Notificações URL1:</p>
                <div class="wc_copy">
                    <input type="text" name="hotmart_notification" value="<?= BASE; ?>/_ead/wc_ead.hotmart.php" readonly="readonly"/><span class="jwc_copy icon-copy icon-notext" id="hotmart_notification"></span>
                </div>
            </div><div class="clear"></div>

            <form style="margin-top: 20px;" name="student_course_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="course_vendor"/>
                <input type="hidden" name="course_id" value="<?= $CourseId; ?>"/>

                <div class="label_50">
                    <label class="label">
                        <span class="legend icon-fire">ID do curso:</span>
                        <input name="course_vendor_id" value="<?= $course_vendor_id; ?>" type="text" placeholder="ID do produto na hotmart:" required>
                    </label>

                    <label class="label">
                        <span class="legend icon-hour-glass">Tempo de acesso em meses:</span>
                        <input name="course_vendor_access" value="<?= $course_vendor_access; ?>" type="number" min="0" max="120" placeholder="Informe 0 para liberar para sempre:" required>
                    </label>
                </div>

                <label class="label">
                    <span class="legend icon-link">Link do Checkout:</span>
                    <input name="course_vendor_checkout" value="<?= $course_vendor_checkout; ?>" type="text" placeholder="Link de pagamento direto:" required>
                </label>

                <label class="label">
                    <span class="legend icon-link">Link de Oferta de Renovação (opcional):</span>
                    <input name="course_vendor_renew" value="<?= $course_vendor_renew; ?>" type="text" placeholder="Link de pagamento direto:">
                </label>

                <label class="label">
                    <span class="legend icon-display">Página de Vendas Externa: (opcional)</span>
                    <input name="course_vendor_page" value="<?= $course_vendor_page; ?>" type="text" placeholder="Link da página de vendas:">
                </label>

                <div class="wc_actions">
                    <button style="padding: 9px 12px;" class="btn btn_green icon-fire">ATUALIZAR OFERTA</button>
                    <img class="form_load none" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
                <div class="clear"></div>
            </form>
        </div>

        <?php if (EAD_STUDENT_CERTIFICATION): ?>
            <div class="panel wc_tab_target none" id="certification">
                <div class="trigger trigger_info icon-warning al_center"><b>IMPORTANTE:</b> Antes de cadastrar um certificado cadastre todos os módulos e aulas do curso!</div>
                <form name="course_certification" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Courses"/>
                    <input type="hidden" name="callback_action" value="course_certification"/>
                    <input type="hidden" name="course_id" value="<?= $CourseId; ?>"/>

                    <div class="certification_cover" style="margin-bottom: 15px;">
                        <?php
                        $CourseCertification = (!empty($course_certification_mockup) && file_exists("../uploads/{$course_certification_mockup}") && !is_dir("../uploads/{$course_certification_mockup}") ? "uploads/{$course_certification_mockup}" : 'admin/_img/ead_certification.png');
                        ?>
                        <img class="course_certification_mockup post_cover" alt="Certificado" title="Certificado" src="../tim.php?src=<?= $CourseCertification; ?>&w=1300" default="../tim.php?src=<?= $CourseCertification; ?>&w=1300"/>
                        <div class="clear"></div>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend icon-hour-glass">Carga Horária:</span>
                            <input name="course_certification_workload" value="<?= $course_certification_workload; ?>" type="number" placeholder="Tempo em Horas:" required>
                        </label>

                        <label class="label">
                            <span class="legend icon-warning">Requisito de Certificação:</span>
                            <select name="course_certification_request">
                                <?php
                                $Requests = [100, 90, 80, 70, 60, 50, 40, 30, 20, 10];
                                foreach ($Requests as $RequestPercent):
                                    echo "<option " . ($RequestPercent == $course_certification_request ? "selected='selected'" : '') . " value='{$RequestPercent}'>Completar {$RequestPercent}% do Curso</option>";
                                endforeach;
                                ?>
                            </select>
                        </label>
                    </div>

                    <label class="label">
                        <span class="legend icon-image">Plano de Fundo (Mockup):</span>
                        <input name="course_certification_mockup" class="wc_loadimage" value="" type="file">
                    </label>

                    <div class="wc_actions" id="<?= $course_id; ?>">
                        <span style="padding: 9px 12px; margin-right: 10px !important;" rel='wc_actions' class='j_delete_action icon-cross icon-notext btn btn_red' id='<?= $course_id; ?>'></span>
                        <span style="padding: 9px 12px; margin-right: 10px !important; display: none" rel='wc_actions' callback='Courses' callback_action='course_certification_remove' class='j_delete_action_confirm icon-warning btn btn_yellow' id='<?= $course_id; ?>'>EXCLUIR AGORA!</span>

                        <a class="btn btn_blue icon-download" href="_img/ead_certification_mockup.zip" title="Download Mockup" style="padding: 9px 12px; margin-right: 10px;">MOCKUP</a>
                        <button style="padding: 9px 12px;" class="btn btn_green icon-trophy">ATUALIZAR</button>
                        <img class="form_load none" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                    <div class="clear"></div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <article class="box box30">
        <div class="course_create_cover">
            <div class="upload_progress none">0%</div>
            <?php
            $CourseCover = (!empty($course_cover) && file_exists("../uploads/{$course_cover}") && !is_dir("../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');
            ?>
            <img class="course_thumb course_cover" alt="Capa" title="Capa" src="../tim.php?src=<?= $CourseCover; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>" default="../tim.php?src=<?= $CourseCover; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>"/>
        </div>

        <div class="box_conf_menu no_icon">
            <a class='conf_menu wc_tab wc_active' href='#course'><span class="icon-lab">Curso</span></a>
            <a class='conf_menu wc_tab' href='#bonus'><span class="icon-rocket">Bônus</span></a>
            <a class='conf_menu wc_tab' href='#hotmart'><span class="icon-fire">Hotmart</span></a>
            <?php if (EAD_STUDENT_CERTIFICATION): ?>
                <a class='conf_menu wc_tab' href='#certification'><span class="icon-trophy">Certificado</span></a>
            <?php endif; ?>
        </div>
    </article>
</div>