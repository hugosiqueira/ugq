<?php

session_start();
require '../../_app/Config.inc.php';
$NivelAcess = LEVEL_WC_EAD_COURSES;

if (!APP_EAD || empty($_SESSION['userLogin']) || empty($_SESSION['userLogin']['user_level']) || $_SESSION['userLogin']['user_level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

usleep(50000);

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Courses';
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

    //SELECIONA AÇÃO
    switch ($Case):
        //COURSE DELETE
        case 'delete':
            $PostData['course_id'] = $PostData['del_id'];

            $Read->FullRead("SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id=:cs", "cs={$PostData['course_id']}");
            if ($Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>ERRO AO DELETAR:</b> Para deletar um curso, antes é preciso deletar todos os módulos do mesmo!", E_USER_WARNING);
            else:
                $Read->FullRead("SELECT course_cover FROM " . DB_EAD_COURSES . " WHERE course_id=:ps", "ps={$PostData['course_id']}");
                if ($Read->getResult() && file_exists("../../uploads/{$Read->getResult()[0]['course_cover']}") && !is_dir("../../uploads/{$Read->getResult()[0]['course_cover']}")):
                    unlink("../../uploads/{$Read->getResult()[0]['course_cover']}");
                endif;

                $Delete->ExeDelete(DB_EAD_COURSES, "WHERE course_id=:id", "id={$PostData['course_id']}");
                $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>SUCESSO:</b> Curso foi removido com sucesso do sistema.");
                $jSON['redirect'] = "dashboard.php?wc=teach/courses";
            endif;
            break;

        //COURSE MANAGER
        case 'manager':
            $CourseId = $PostData['course_id'];
            unset($PostData['course_id']);

            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id=:id", "id={$CourseId}");
            $ThisCourse = $Read->getResult()[0];

            $PostData['course_vendor_price'] = str_replace(',', '.', str_replace('.', '', $PostData['course_vendor_price']));

            $PostData['course_name'] = (!empty($PostData['course_name']) ? Check::Name($PostData['course_name']) : Check::Name($PostData['course_title']));
            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id != :id AND course_name=:name", "id={$CourseId}&name={$PostData['course_name']}");
            if ($Read->getResult()):
                $PostData['course_name'] = "{$PostData['course_name']}-{$CourseId}";
            endif;
            $jSON['name'] = $PostData['course_name'];

            if ($PostData['course_desc'] == '' || empty($PostData['course_desc'])):
                $jSON['trigger'] = AjaxErro("<b>ERRO AO SALVAR:</b> Olá {$_SESSION['userLogin']['user_name']}, adicione uma descrição para o curso <b>{$PostData['course_name']}</b>!", E_USER_WARNING);
                echo json_encode($jSON);
                return;
            endif;

            if (!empty($_FILES['course_cover'])):
                $File = $_FILES['course_cover'];

                if ($ThisCourse['course_cover'] && file_exists("../../uploads/{$ThisCourse['course_cover']}") && !is_dir("../../uploads/{$ThisCourse['course_cover']}")):
                    unlink("../../uploads/{$ThisCourse['course_cover']}");
                endif;

                $Upload = new Upload('../../uploads/');
                $Upload->Image($File, $PostData['course_name'] . '-' . time(), IMAGE_W, 'courses');
                if ($Upload->getResult()):
                    $PostData['course_cover'] = $Upload->getResult();
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-image'>ERRO AO ENVIAR CAPA:</b> Olá {$_SESSION['userLogin']['user_name']}, selecione uma imagem JPG ou PNG para enviar como capa!", E_USER_WARNING);
                    echo json_encode($jSON);
                    return;
                endif;
            else:
                unset($PostData['course_cover']);
            endif;

            $PostData['course_status'] = (!empty($PostData['course_status']) ? '1' : '0');
            $PostData['course_created'] = (!empty($PostData['course_created']) ? Check::Data($PostData['course_created']) : date('Y-m-d H:i:s'));
            $PostData['course_updated'] = date('Y-m-d H:i:s');

            $Update->ExeUpdate(DB_EAD_COURSES, $PostData, "WHERE course_id=:id", "id={$CourseId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>TUDO CERTO: </b> O curso <b>{$PostData['course_title']}</b> foi atualizado com sucesso!");
            break;

        //VENDOR
        case 'course_vendor':
            $CourseId = $PostData['course_id'];
            unset($PostData['course_id']);

            $Update->ExeUpdate(DB_EAD_COURSES, $PostData, "WHERE course_id=:course", "course={$CourseId}");
            $jSON['trigger'] = AjaxErro("<b>Sucesso:</b> Os dados de venda foram atualizados!");
            break;

        //COURSE ORDER    
        case 'courses_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdateCourse = ['course_order' => $RE[1]];
                    $Update->ExeUpdate(DB_EAD_COURSES, $UpdateCourse, "WHERE course_id = :course", "course={$RE[0]}");
                endforeach;

                $jSON['sucess'] = true;
            endif;
            break;

        //COURSE BONUS :: ADD
        case 'courses_bonus_add':
            $Bonus['course_id'] = $PostData['course_id'];
            $Bonus['bonus_course_id'] = $PostData['bonus_course_id'];
            $Bonus['bonus_ever'] = $PostData['bonus_ever'];
            $Bonus['bonus_wait'] = $PostData['bonus_wait'];

            if (in_array("", $Bonus)):
                $jSON['trigger'] = AjaxErro("<b class='icon-info'>OPPSSS:</b> Para cadastrar um bônus, selecione todos os campos e informe o tempo de acesso!", E_USER_WARNING);
                break;
            endif;

            //BONUS EVER DATE
            $Bonus['bonus_ever_date'] = (!empty($PostData['bonus_ever_date']) ? Check::Data($PostData['bonus_ever_date']) : date("Y-m-d"));

            //BONUS VALID
            $Read->FullRead("SELECT bonus_id FROM " . DB_EAD_COURSES_BONUS . " WHERE course_id=:course AND bonus_course_id=:bonus", "course={$Bonus['course_id']}&bonus={$Bonus['bonus_course_id']}");
            if ($Read->getResult()):
                //UPDATE BONUS
                $Update->ExeUpdate(DB_EAD_COURSES_BONUS, $Bonus, "WHERE bonus_id=:bonus", "bonus={$Read->getResult()[0]['bonus_id']}");
                $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>Bônus atualizado:</b> O bônus já estava cadastrado. Então o tempo de acesso foi atualizado!", E_USER_NOTICE);
            else:
                //CREATE BONUS
                $Create->ExeCreate(DB_EAD_COURSES_BONUS, $Bonus);
            endif;

            //RETURN COURSES
            $jSON['content'] = null;
            $Read->ExeRead(DB_EAD_COURSES_BONUS, "WHERE course_id=:course ORDER BY bonus_wait ASC", "course={$Bonus['course_id']}");
            if (!$Read->getResult()):
                $jSON['content'] = "<div class='trigger trigger_info icon-info al_center'>Todos os bônus para este curso foram removidos!</div><div class='clear'></div>";
            else:
                foreach ($Read->getResult() as $Bonus):
                    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id=:bonus ORDER BY course_order ASC, course_name ASC", "bonus={$Bonus['bonus_course_id']}");
                    $BonusCourse = $Read->getResult()[0];
                    $BonusCover = (file_exists("../../uploads/{$BonusCourse['course_cover']}") && !is_dir("../../uploads/{$BonusCourse['course_cover']}") ? "uploads/{$BonusCourse['course_cover']}" : 'admin/_img/no_image.jpg');

                    $jSON['content'] .= "<article class='box box33 students_gerent_course' style='margin:0;' id='{$Bonus['bonus_id']}'>
                            <img src='../tim.php?src={$BonusCover}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "' title='{$BonusCourse['course_title']}' alt='{$BonusCourse['course_title']}'/>
                            <div class='students_gerent_course_content'>
                                <h1>{$BonusCourse['course_title']}</h1>
                                <p>" . ($Bonus['bonus_wait'] ? "Aguardar por {$Bonus['bonus_wait']} dias" : 'Liberar Imediatamente') . "</p>
                                <p>" . ($Bonus['bonus_ever'] == 1 ? 'Para todas as matriculas' : "Matrículas a partir de " . date("d/m/Y", strtotime($Bonus['bonus_ever_date']))) . "</p>
                            </div>
                            <div class='students_gerent_course_actions'>
                                <span rel='students_gerent_course' class='j_delete_action icon-cancel-circle btn btn_red' id='{$Bonus['bonus_id']}'>Excluir Bônus!</span>
                                <span rel='students_gerent_course' callback='Courses' callback_action='courses_bonus_remove' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$Bonus['bonus_id']}'>Excluir Agora?</span>
                            </div>
                        </article>";
                endforeach;
            endif;
            break;

        //COURSE BONUS :: REMOVE
        case 'courses_bonus_remove':
            $Delete->ExeDelete(DB_EAD_COURSES_BONUS, "WHERE bonus_id=:bonus", "bonus={$PostData['del_id']}");
            $jSON['success'] = true;
            break;

        //MODULE DELETE
        case 'module_delete':
            $PostData['module_id'] = $PostData['del_id'];

            $Read->FullRead("SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id=:mod", "mod={$PostData['module_id']}");
            if ($Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>ERRO AO DELETAR:</b> Para deletar um módulo, antes é preciso deletar todas as aulas do mesmo!", E_USER_WARNING);
            else:
                $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_id=(SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id=:id)", "id={$PostData['module_id']}");
                if (!$Read->getResult()):
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>OPPSSS:</b> Não foi possível deletar o módulo pois o curso selecionado não existe!", E_USER_WARNING);
                else:
                    extract($Read->getResult()[0]);

                    $Delete->ExeDelete(DB_EAD_MODULES, "WHERE module_id=:id", "id={$PostData['module_id']}");
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>SUCESSO:</b> O módulo do curso {$course_title} foi removido com sucesso! Aguarde redirecionamento...");
                    $jSON['redirect'] = "dashboard.php?wc=teach/courses_gerent&id={$course_id}";
                endif;
            endif;
            break;

        //MODULE MANAGER
        case 'module_manage':
            $ModId = $PostData['module_id'];
            unset($PostData['module_id']);

            if (!empty($PostData['module_release_date'])):
                $PostData['module_release_date'] = Check::Data($PostData['module_release_date']);

                if ($PostData['module_release_date'] <= date('Y-m-d')):
                    $PostData['module_release_date'] = null;
                endif;
            endif;

            $PostData['module_name'] = $ModId . "-" . Check::Name($PostData['module_title']);
            $PostData['module_release'] = (!empty($PostData['module_release']) ? $PostData['module_release'] : 0);
            $Update->ExeUpdate(DB_EAD_MODULES, $PostData, "WHERE module_id=:id", "id={$ModId}");
            $jSON['inpuval'] = (($PostData['module_release_date'] != null) ? date('d/m/Y H:i', strtotime($PostData['module_release_date'])) : 'null');
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>SUCESSO:</b> O módulo <b>{$PostData['module_title']}</b> foi atualizado com sucesso!");
            break;

        //CLASS DELETE
        case 'class_delete':
            $ClassId = $PostData['del_id'];

            //ARQUIVOS DA AULA
            $Read->FullRead("SELECT class_material FROM " . DB_EAD_CLASSES . " WHERE class_id=:cs", "cs={$ClassId}");
            if ($Read->getResult()):
                if (file_exists("../../uploads/{$Read->getResult()[0]['class_material']}") && !is_dir("../../uploads/{$Read->getResult()[0]['class_material']}")):
                    unlink("../../uploads/{$Read->getResult()[0]['class_material']}");
                endif;
            endif;

            $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id != :class AND module_id=(SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_id=:class)", "class={$ClassId}");
            if (!$Read->getResult()):
                $jSON['content'] = '<div class="trigger trigger_info trigger_none al_center icon-checkmark2">Você removeu todas as aulas deste módulo!</div>';
                $jSON['content'] .= '<div class="clear"></div>';
            endif;

            $Delete->ExeDelete(DB_EAD_CLASSES, "WHERE class_id=:id", "id={$ClassId}");
            $jSON['inpuval'] = $Read->getRowCount() + 1;
            $jSON['success'] = true;
            break;

        //MODULE ORDER    
        case 'modules_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdateMod = ['module_order' => $RE[1]];
                    $Update->ExeUpdate(DB_EAD_MODULES, $UpdateMod, "WHERE module_id=:mod", "mod={$RE[0]}");
                endforeach;

                $jSON['sucess'] = true;
            endif;
            break;

        //COURSE MATERIAL :: CLASS SELECT
        case 'class_select':
            $ModuleId = $PostData['key'];
            $Read->FullRead("SELECT class_id, class_title FROM " . DB_EAD_CLASSES . " WHERE module_id = :mod ORDER BY class_order ASC", "mod={$ModuleId}");
            if (!$Read->getResult()):
                $jSON['content'] = "<option value=''>Não existem aulas para esse módulo!</option>";
            else:
                $jSON['content'] = "<option value=''>Selecione uma aula (OPCIONAL)</option>";
                foreach ($Read->getResult() as $CLASS):
                    $jSON['content'] .= "<option value='{$CLASS['class_id']}'>{$CLASS['class_title']}</option>";
                endforeach;
            endif;

            $jSON['target'] = ".jwc_combo_target";
            break;

        //CLASS ADD
        case 'class_add':
            $PostData['class_video'] = (!empty($PostData['class_video']) ? $PostData['class_video'] : null);
            $PostData['class_desc'] = (!empty($PostData['class_desc']) ? $PostData['class_desc'] : null);
            $PostData['class_created'] = date('Y-m-d H:i:s');
            $PostData['class_updated'] = date('Y-m-d H:i:s');
            $PostData['class_material'] = null;

            //CREATE 
            $Create->ExeCreate(DB_EAD_CLASSES, $PostData);
            $tmpName = Check::Name($PostData['class_title']);

            $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id != :id AND class_name = :name AND course_id = :course", "id={$Create->getResult()}&name={$tmpName}&course={$PostData['course_id']}");
            if ($Read->getResult()):
                $ClassName = $Create->getResult() . '-' . $tmpName;
                $ClassUpdate['class_name'] = $ClassName;
            else:
                $ClassUpdate['class_name'] = $tmpName;
            endif;

            //MATERIAL
            if (!empty($_FILES['class_material'])):
                $File = $_FILES['class_material'];
                $Upload = new Upload('../../uploads/');
                $Upload->File($File, $ClassUpdate['class_name'], "courses/material");
                if (!$Upload->getError()):
                    $ClassUpdate['class_material'] = $Upload->getResult();
                else:
                    $jSON['trigger'] = AjaxErro("<b class='icon-warning'>OPPSSS:</b> O arquivo enviado não é compatível, para cadastrar a aula <b>envie um arquivo .zip</b> ou não envie!", E_USER_WARNING);
                    break;
                endif;
            endif;

            //FIX CLASS UPDATE FOR NAME AND MATERIAL
            $Update->ExeUpdate(DB_EAD_CLASSES, $ClassUpdate, "WHERE class_id = :id", "id={$Create->getResult()}");

            //RETURN CLASSES
            $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :mod ORDER BY class_order ASC", "mod={$PostData['module_id']}");
            if ($Read->getResult()):
                $OrderCount = 1;
                $jSON['content'] = "";
                foreach ($Read->getResult() as $CLASS):
                    extract($CLASS);

                    $Read->FullRead("SELECT SUM(student_class_views) AS ClassTotalViews FROM " . DB_EAD_STUDENT_CLASSES . " WHERE class_id=:id", "id={$class_id}");
                    $ClassTotalViews = $Read->getResult()[0]['ClassTotalViews'];

                    $OrderCount++;
                    $jSON['content'] .= "<article class='course_gerent_class wc_draganddrop' callback='Courses' callback_action='class_order' id='{$class_id}'>
                            <h1 class='row_title'>
                                   {$class_title}
                                </h1><p class='row icon-clock'>
                                    " . str_pad($class_time, 2, 0, 0) . " min
                                </p><p class='row icon-bubbles3'>
                                    " . ($class_support ? 'Sim!' : 'Não!') . "
                                </p><p class='row icon-file-zip'>
                                    " . ($class_material && file_exists("../../uploads/{$class_material}") && !is_dir("../../uploads/{$class_material}") ? "<a target='blank' href='" . BASE . "/admin/_sis/teach/courses_downloads_f.php?f={$class_id}' title='Baixar Material de Aopoio!'>Baixar</a>" : 'Não!') . "
                                </p><p class='row icon-eye'>
                                    " . str_pad($ClassTotalViews, 4, 0, 0) . "
                                </p><p class='actions'>
                                    <a href='dashboard.php?wc=teach/courses_classedit&id={$course_id}&module={$module_id}&class={$class_id}' title='Editar Aula' class='btn btn_blue icon-pencil2 icon-notext'></a>
                                    <span rel='course_gerent_class' class='j_delete_action icon-cancel-circle icon-notext btn btn_red' id='{$class_id}'></span>
                                    <span rel='course_gerent_class' callback='Courses' callback_action='class_delete' class='j_delete_action_confirm icon-warning icon-notext btn btn_yellow' style='display: none' id='{$class_id}'></span>
                                </p>
                        </article>";
                endforeach;

                $jSON['inpuval'] = $OrderCount;
                $jSON['clear'] = true;
                $jSON['reorder'] = true;
            endif;
            break;

        //CLASS EDIT
        case 'class_edit':
            $ClassId = $PostData['class_id'];
            unset($PostData['class_id']);

            $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id=:id", "id={$ClassId}");
            if (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>OPPSSS:</b> Não foi possível obter os dados desta aula. Você pode tentar atualizar a página para continuar editando!", E_USER_WARNING);
            else:
                extract($Read->getResult()[0]);

                $PostData['class_updated'] = date('Y-m-d H:i:s');
                $PostData['class_material'] = null;

                //UPDATE
                $tmpName = Check::Name($PostData['class_title']);

                $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id != :id AND class_name = :name AND course_id = :course", "id={$class_id}&name={$tmpName}&course={$PostData['course_id']}");
                if ($Read->getResult()):
                    $ClassName = $class_id . '-' . $tmpName;
                    $PostData['class_name'] = $ClassName;
                else:
                    $PostData['class_name'] = $tmpName;
                endif;

                $PostData['class_support'] = (!empty($PostData['class_support']) ? 1 : 0);

                //MATERIAL
                if (!empty($_FILES['class_material'])):
                    if ($class_material && file_exists("../../uploads/{$class_material}") && !is_dir("../../uploads/{$class_material}")):
                        unlink("../../uploads/{$class_material}");
                    endif;

                    $File = $_FILES['class_material'];
                    $Upload = new Upload('../../uploads/');
                    $Upload->File($File, $PostData['class_name'], "courses/material");
                    $PostData['class_material'] = $Upload->getResult();

                    if ($Upload->getError()):
                        $jSON['trigger'] = AjaxErro("<b class='icon-warning'>AULA ATUALIZADA,</b> Mas o arquivo enviado não é compatível, portanto não foi cadastrado na aula.<p>Para cadastrar material <b>envie arquivos .zip</b></p>", E_USER_WARNING);
                    else:
                        $jSON['download'] = "<div class='course_gerent_class_download' id='{$class_id}' style='display: block'>
                        <a target='blank' href='" . BASE . "/admin/_sis/teach/courses_downloads_f.php?f={$class_id}' class='btn btn_green icon-download' title='Baixar Material de Apoio!'>Baixar!</a>
                        <span rel='course_gerent_class_download' class='j_delete_action icon-cancel-circle btn btn_red' id='{$class_id}'>Deletar Material!</span>
                        <span rel='course_gerent_class_download' callback='Courses' callback_action='class_delete_file' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$class_id}'>Excluir Arquivo!</span>
                    </div>";
                    endif;
                elseif (empty($class_material)):
                    $PostData['class_material'] = null;
                else:
                    unset($PostData['class_material']);
                endif;

                //FIX
                $Update->ExeUpdate(DB_EAD_CLASSES, $PostData, "WHERE class_id = :id", "id={$class_id}");

                if ($PostData['class_video'] && $PostData['class_video'] != $class_video):
                    $jSON['content'] = "<div class='embed-container'>";
                    if (is_numeric($PostData['class_video'])):
                        $jSON['content'] .= "<iframe src='https://player.vimeo.com/video/{$PostData['class_video']}?color=596273&title=0&byline=0&portrait=0' width='640' height='360' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
                    else:
                        $jSON['content'] .= "<iframe width='640' height='360' src='https://www.youtube.com/embed/{$PostData['class_video']}?showinfo=0&amp;rel=0' frameborder='0' allowfullscreen></iframe>";
                    endif;
                    $jSON['content'] .= "</div>";
                elseif (!$class_video):
                    $jSON['content'] = " ";
                endif;

                if (empty($jSON['trigger'])):
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>AULA ATUALIZADA:</b> {$PostData['class_title']}!");
                endif;
                $jSON['success'] = true;
            endif;
            break;

        //CLASS DELETE FILE
        case 'class_delete_file':
            $ClassId = $PostData['del_id'];

            //ARQUIVOS DA AULA
            $Read->FullRead("SELECT class_material FROM " . DB_EAD_CLASSES . " WHERE class_id=:cs", "cs={$ClassId}");
            if ($Read->getResult()):
                if (file_exists("../../uploads/{$Read->getResult()[0]['class_material']}") && !is_dir("../../uploads/{$Read->getResult()[0]['class_material']}")):
                    unlink("../../uploads/{$Read->getResult()[0]['class_material']}");
                endif;
            endif;

            $UpdateClass = ['class_material' => null];
            $Update->ExeUpdate(DB_EAD_CLASSES, $UpdateClass, "WHERE class_id=:id", "id={$ClassId}");
            $jSON['sucess'] = true;
            break;

        //CLASS ORDER   
        case 'class_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdateMod = ['class_order' => $RE[1]];
                    $Update->ExeUpdate(DB_EAD_CLASSES, $UpdateMod, "WHERE class_id=:class", "class={$RE[0]}");
                endforeach;

                $jSON['sucess'] = true;
            endif;
            break;

        //STUDENT REMOVE
        case 'student_remove':
            $UserId = $PostData['del_id'];
            $Read->ExeRead(DB_USERS, "WHERE user_id=:user", "user={$UserId}");
            if (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>ALUNO NÃO EXISTE:</b> Olá {$_SESSION['userLogin']['user_name']}, você tentou deletar um aluno que não existe ou já foi removido!", E_USER_WARNING);
            else:
                extract($Read->getResult()[0]);
                if ($user_id == $_SESSION['userLogin']['user_id']):
                    $jSON['trigger'] = AjaxErro("<b class='icon-warning'>OPPSSS:</b> Olá {$_SESSION['userLogin']['user_name']}, por questões de segurança, o sistema não permite que você remova sua própria conta!", E_USER_WARNING);
                elseif ($user_level > $_SESSION['userLogin']['user_level']):
                    $jSON['trigger'] = AjaxErro("<b class='icon-warning'>PERMISSÃO NEGADA:</b> Desculpe {$_SESSION['userLogin']['user_name']}, mas {$user_name} tem acesso superior ao seu. Você não pode remove-lo!", E_USER_WARNING);
                else:
                    if (file_exists("../../uploads/{$user_thumb}") && !is_dir("../../uploads/{$user_thumb}")):
                        unlink("../../uploads/{$user_thumb}");
                    endif;

                    $Delete->ExeDelete(DB_USERS, "WHERE user_id=:user", "user={$user_id}");
                    $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>ALUNO REMOVIDO COM SUCESSO!</b>");
                    $jSON['redirect'] = "dashboard.php?wc=teach/students";
                endif;
            endif;
            break;

        //STUDENT ENROLLMENT EDIT:
        case 'student_enrollment':
            $EnrollId = $PostData['enrollment_id'];
            unset($PostData['enrollment_id']);

            $PostData['enrollment_bonus'] = (!empty($PostData['enrollment_bonus']) ? $PostData['enrollment_bonus'] : null);
            $PostData['enrollment_start'] = Check::Data($PostData['enrollment_start']);
            $PostData['enrollment_end'] = (!empty($PostData['enrollment_end']) ? Check::Data($PostData['enrollment_end']) : null);
            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $PostData, "WHERE enrollment_id = :enroll", "enroll={$EnrollId}");

            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>MATRÍCULA ATUALIZADA COM SUCESSO!</b>");
            break;

        //STUDENTE ENROLLMENT REMOVE
        case 'student_course_remove':
            $EnrollmentId = $PostData['del_id'];

            $Read->FullRead("SELECT enrollment_id, course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=(SELECT user_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_id=:enrol) AND enrollment_id != :enrol", "enrol={$EnrollmentId}");
            if (!$Read->getResult()):
                $jSON['content'] = "<div class='trigger trigger_info icon-info al_center'>Todos os cursos foram removidos para a conta do aluno!</div>";
            endif;

            //GET COURSE ENROLLMENT
            $Read->FullRead(""
                    . "SELECT "
                    . "e.*, "
                    . "c.* "
                    . "FROM " . DB_EAD_ENROLLMENTS . " e "
                    . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                    . "WHERE e.enrollment_bonus = :enroll", "enroll={$EnrollmentId}"
            );
            if ($Read->getResult()):
                $UserId = $Read->getResult()[0]['user_id'];

                foreach ($Read->getResult() as $EnrollmentBonus):
                    $Read->FullRead(""
                            . "SELECT "
                            . "b.* "
                            . "FROM " . DB_EAD_COURSES_BONUS . " b "
                            . "WHERE b.bonus_course_id = :course "
                            . "AND b.course_id IN (SELECT e.course_id FROM " . DB_EAD_ENROLLMENTS . " e WHERE e.enrollment_id != :enrollmentMain AND e.enrollment_id != :enrollmentBonus AND e.user_id = :user)", "enrollmentMain={$EnrollmentId}&enrollmentBonus={$EnrollmentBonus['enrollment_id']}&user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}"
                    );

                    if ($Read->getResult()):
                        $Read->FullRead(""
                                . "SELECT "
                                . "e.* "
                                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                . "WHERE user_id = :user AND course_id = :course", "user={$EnrollmentBonus['user_id']}&course={$Read->getResult()[0]['course_id']}"
                        );
                        if ($Read->getResult()):
                            $UpdateEnrollment = [
                                'enrollment_bonus' => $Read->getResult()[0]['enrollment_id'],
                                'enrollment_end' => $Read->getResult()[0]['enrollment_end']
                            ];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrollment", "enrollment={$EnrollmentBonus['enrollment_id']}");
                        endif;
                    else:
                        $Read->FullRead(""
                                . "SELECT "
                                . "o.*, "
                                . "c.* "
                                . "FROM " . DB_EAD_ORDERS . " o "
                                . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = o.course_id "
                                . "WHERE o.user_id = :user "
                                . "AND o.course_id = :course "
                                . "AND o.order_status IN ('approved' 'completed', 'admin_free')"
                                . "ORDER BY o.order_purchase_date DESC LIMIT 1", "user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}");

                        if ($Read->getResult()):
                            $UpdateEnrollmentOrder = [
                                'enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['order_purchase_date'] . "+{$Read->getResult()[0]['course_vendor_access']}months")),
                                'enrollment_order' => $Read->getResult()[0]['order_id'],
                                'enrollment_bonus' => null
                            ];

                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentOrder, "WHERE enrollment_id = :enroll", "enroll={$EnrollmentBonus['enrollment_id']}");

                        endif;
                    endif;
                endforeach;

                $jSON['redirect'] = 'dashboard.php?wc=teach/students_gerent&id=' . $UserId . '#courses';
            endif;
            $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_id = :id", "id={$EnrollmentId}");

            $jSON['sucess'] = true;
            break;

        //STUDEND ENROLLMENT ADD OR UPDATE
        case 'student_course_add':
            $CreateOrder = [
                'user_id' => $PostData['user_id'],
                'course_id' => $PostData['course_id'],
                'order_transaction' => time(),
                'order_callback_type' => "1",
                'order_price' => "0.00",
                'order_currency' => "BRL",
                'order_payment_type' => "admin_free",
                'order_purchase_date' => date('Y-m-d H:i:s'),
                'order_warranty_date' => date('Y-m-d H:i:s'),
                'order_confirmation_purchase_date' => date('Y-m-d H:i:s'),
                'order_sck' => "admin_free",
                'order_src' => $_SESSION['userLogin']['user_id'],
                'order_cms_aff' => "0.00",
                'order_cms_marketplace' => "0.00",
                'order_cms_vendor' => "0.00",
                'order_status' => "admin_free",
                'order_delivered' => 1
            ];
            $Create->ExeCreate(DB_EAD_ORDERS, $CreateOrder);

            $Enrollment['user_id'] = $PostData['user_id'];
            $Enrollment['course_id'] = $PostData['course_id'];
            $Enrollment['enrollment_order'] = $Create->getResult();
            $Enrollment['enrollment_end'] = !empty($PostData['course_end']) ? date("Y-m-d H:i:s", strtotime("+{$PostData['course_end']}months")) : null;

            //TIME ALERT
            if (empty($PostData['course_end'])):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>ATENÇÃO:</b> Você não definiu um tempo de liberação, o curso foi liberado para sempre!", E_USER_WARNING);
            endif;

            $Read->FullRead("SELECT enrollment_end, enrollment_id FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:us AND course_id=:cs", "us={$Enrollment['user_id']}&cs={$Enrollment['course_id']}");
            if ($Read->getResult()):
                //UPDATE ENROLLMENTE
                if (!empty($PostData['course_end'])):
                    $UpdateEnrollmentData = ['enrollment_end' => date("Y-m-d H:i:s", strtotime(($Read->getResult()[0]['enrollment_end'] && $Read->getResult()[0]['enrollment_end'] != '0000-00-00 00:00:00' ? $Read->getResult()[0]['enrollment_end'] : date('Y-m-d H:i:s')) . "+{$PostData['course_end']}months"))];
                else:
                    $UpdateEnrollmentData = ['enrollment_end' => null];
                endif;
                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentData, "WHERE enrollment_id=:id", "id={$Read->getResult()[0]['enrollment_id']}");
            else:
                //CREATE ENROLLMENTE
                $Enrollment['enrollment_start'] = date('Y-m-d H:i:s');
                $Create->ExeCreate(DB_EAD_ENROLLMENTS, $Enrollment);
            endif;

            //RETURN COURSES
            $jSON['content'] = null;
            $Read->FullRead("SELECT " . DB_EAD_ENROLLMENTS . ".*, " . DB_EAD_COURSES . ".* FROM " . DB_EAD_ENROLLMENTS . ", " . DB_EAD_COURSES . " WHERE " . DB_EAD_ENROLLMENTS . ".user_id=:user AND " . DB_EAD_ENROLLMENTS . ".course_id=" . DB_EAD_COURSES . ".course_id ORDER BY " . DB_EAD_COURSES . ".course_order ASC, " . DB_EAD_COURSES . ".course_title ASC", "user={$Enrollment['user_id']}");
            foreach ($Read->getResult() as $Encollment):
                extract($Encollment);
                $Cover = (file_exists("../../uploads/{$course_cover}") && !is_dir("../../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

                $DayNow = new DateTime();
                $DayEnd = new DateTime($enrollment_end);
                $DayDif = $DayNow->diff($DayEnd);

                //PROGRESS
                $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id=:cs)", "cs={$course_id}");
                $ClassCount = $Read->getResult()[0]['ClassCount'];

                $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id=:user AND course_id=:course", "user={$user_id}&course={$course_id}");
                $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                $CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");

                //SUPPORT
                $Read->FullRead("SELECT COUNT(support_id) AS SupportOpen FROM " . DB_EAD_SUPPORT . " WHERE user_id=:user AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id=:course)) AND support_status=1", "user={$user_id}&course={$course_id}");
                $SupportOpen = str_pad($Read->getResult()[0]['SupportOpen'], 2, 0, 0);

                if (!$enrollment_end):
                    $EnrollStatus = "<b class='icon-heart'>Bônus de Matrícula!</b>";
                elseif (!$DayDif->invert):
                    $EnrollStatus = "<b class='icon-checkmark' style='color: #00B494;'>Assinatura Expira em {$DayDif->days} dias!</b>";
                else:
                    $EnrollStatus = "<b class='icon-cross' style='color: #C54550;'>Assinatura Vencida a {$DayDif->days} dias!</b>";
                endif;

                $Read->FullRead("SELECT COUNT(support_id) AS SupportSolved FROM " . DB_EAD_SUPPORT . " WHERE user_id=:user AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id=:course)) AND support_status > 1", "user={$user_id}&course={$course_id}");
                $SupportSolved = str_pad($Read->getResult()[0]['SupportSolved'], 2, 0, 0);
                $jSON['content'] .= "<article class='box box33 students_gerent_course' style='margin:0;' id='{$enrollment_id}'>
                        <img src='../tim.php?src={$Cover}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "' title='{$course_title}' alt='{$course_title}'/>
                        <div class='upload_bar'><span class='upload_progress' style='width: {$CourseCompletedPercent}%'>{$CourseCompletedPercent}%</span></div>
                        <div class='students_gerent_course_content'>
                            <h1>{$course_title}</h1>
                            <p>Último acesso em: " . ($enrollment_access ? date('d/m/Y', strtotime($enrollment_access)) : "<b>NUNCA ACESSOU</b>") . "</p>
                            <p>Tickets abertos: {$SupportOpen} | Resolvidos: {$SupportSolved}</p>
                            <p>Liberado de: " . date('d/m/Y', strtotime($enrollment_start)) . " a: " . ($enrollment_end ? date('d/m/Y', strtotime($enrollment_end)) : "<b>PARA SEMPRE</b>") . "</p>
                            <p style='border-bottom: none;'>{$EnrollStatus}</p>
                        </div>
                        <div class='students_gerent_course_actions'>
                            <a href='dashboard.php?wc=teach/students_course&enrollment={$enrollment_id}&student={$user_id}' title='Ver Andamento' class='icon-stats-dots icon-notext btn btn_green'></a>
                            <a href='dashboard.php?wc=teach/students_enrollment&enrollment={$enrollment_id}' title='Editar Matrícula' class='icon-pencil icon-notext btn btn_blue'></a>
                            <span rel='students_gerent_course' class='j_delete_action icon-notext icon-cancel-circle btn btn_red' id='{$enrollment_id}'></span>
                            <span rel='students_gerent_course' callback='Courses' callback_action='student_course_remove' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$enrollment_id}'>Excluir?</span>
                        </div>
                    </article>";
            endforeach;

            if (!empty($PostData['send_notification'])):
                $Read->LinkResult(DB_USERS, "user_id", $Enrollment['user_id']);
                $Student = $Read->getResult()[0];

                $Read->LinkResult(DB_EAD_COURSES, "course_id", $Enrollment['course_id']);
                $Course = $Read->getResult()[0];

                require '../../_ead/wc_ead.email.php';
                $MailBody = "
                    <p style='font-size: 1.4em;'>Olá {$Student['user_name']},</p>
                    <p>Este e-mail é para informar que {$_SESSION['userLogin']['user_name']} acabou de atualizar ou liberar sua matrícula para o curso <b>{$Course['course_title']}</b> em sua escola online :)</p>
                    <p>Você pode ver mais detalhes dessa matrícula <a href='" . BASE . "/campus' title='Acessar minha conta na plataforma!'>acessando sua conta</a> e verificando em seus cursos!</b></p>
                    <p>DADOS DA MATRÍCULA:</p>
                    <p>
                    <b>Curso:</b> {$Course['course_title']}<br>
                    <b>Liberação:</b> " . date('d/m/Y H\hi') . "<br>
                    <b>Validade:</b> " . (!empty($Enrollment['enrollment_end']) ? date("d/m/Y H\hi", strtotime($Enrollment['enrollment_end'])) : 'Para Sempre') . "
                    </p>
                    <p>...</p>
                    <p>Se tiver qualquer problema, não deixe de responder este e-mail!</p>
                ";

                $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
                $Email = new Email;
                $Email->EnviarMontando("Sua matrícula do curso {$Course['course_title']} foi atualizada!", $MailContent, MAIL_SENDER, MAIL_USER, "{$Student['user_name']} {$Student['user_lastname']}", $Student['user_email']);
            endif;

            //CLEAR FORM
            $jSON['clear'] = true;
            break;

        //GET ORDER DETAIL
        case 'student_get_order':
            $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_id=:order", "order={$PostData['order_id']}");
            if (!$Read->getResult()):
                $jSON['order'] = "<div class='trigger trigger_error trigger_none icon-warning al_center font_medium' style='margin-top: 20px;'>Desculpe {$_SESSION['userLogin']['user_name']}, não foi possível recuperar o pedido!</div><div class='clear'></div>";
            else:
                extract($Read->getResult()[0]);
                $order_currency = ($order_currency ? $order_currency : "BRL");

                if ($order_sck == 'admin_free'):
                    $Read->FullRead("SELECT user_name, user_lastname FROM " . DB_USERS . " WHERE user_id=:user", "user={$order_src}");
                    $order_src = ($Read->getResult() ? "Por {$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}" : str_pad($order_sck, 4, 0, 0));
                endif;

                //GET COURSE
                $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id=:course", "course={$course_id}");
                $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$order_product_id} na Hotmart");

                //GET GUARANTED
                $DayThis = new DateTime(date("Y-m-d H:i:s"));
                $DayWarn = new DateTime(date($order_warranty_date));
                $GuarantedDays = ($order_warranty_date && $DayThis->diff($DayWarn)->days && !$DayThis->diff($DayWarn)->invert ? $DayThis->diff($DayWarn)->days : 0);

                $jSON['order'] = "
                    <p class='title'>Pedido: " . str_pad($order_id, 5, 0, 0) . "</p>
                    <p class='item'><b>Transação:</b><span><a title='Ver Pedido' href='dashboard.php?wc=teach/orders_gerent&id={$order_id}'>{$order_transaction}</a></span></p>
                    <p class='item'><b>Produto:</b><span>{$CourseTitle}</span></p>
                    <p class='item'><b>Status:</b><span class='bar_" . getWcHotmartStatusClass($order_status) . " radius' style='display: inline'>" . getWcHotmartStatus($order_status) . "</span></p>
                    <p class='item'><b>Data do pedido:</b><span>" . date('d/m/Y H\hi', strtotime($order_purchase_date)) . "</span></p>
                    <p class='item'><b>Data da aprovação:</b><span>" . date('d/m/Y H\hi', strtotime($order_confirmation_purchase_date)) . "</span></p>
                    " . ($GuarantedDays >= 1 ? "<p class='item'><b>Garantia Hotmart:</b><span> {$GuarantedDays} dias restantes</span></p>" : '') . "
                    " . ($order_off ? "<p class='item'><b>Oferta:</b><span>{$order_off}</span></p>" : '') . "
                    " . ($order_sck ? "<p class='item'><b>Origem:</b><span>{$order_sck}</span></p>" : '') . "
                    " . ($order_src ? "<p class='item'><b>Referência:</b><span>{$order_src}</span></p>" : '') . "
                    <p class='item'><b>Valor:</b><span>$ " . number_format($order_price, '2', ',', ',') . "&nbsp;({$order_currency})&nbsp;&nbsp;&nbsp;<img width='25' src='" . BASE . "/_cdn/bootcss/images/pay_{$order_payment_type}.png' alt='Pago com {$order_payment_type}' title='Pago com {$order_payment_type}'/></span></p>
                    <p class='item'><b>Tarifas:</b><span>$ " . number_format($order_cms_marketplace, '2', ',', ',') . "&nbsp;({$order_currency})</span></p>
                    " . ($order_aff_name ? "<p class='item'><b>Afiliado(s):</b><span>" . str_replace(";", ",  ", $order_aff_name) . " - $ " . ($order_cms_aff ? str_replace(";", ", $ ", str_replace('.', ',', $order_cms_aff)) : '0,00') . "&nbsp;({$order_currency})</span></p>" : '') . "
                    <p class='item'><b>Comissão:</b><span>$ " . number_format($order_cms_vendor, '2', ',', ',') . "&nbsp;({$order_currency})</span></p>
                ";
            endif;
            break;

        //SUPPORT :: PUBLISH    
        case 'ead_support_publish':
            $Publish = ['support_published' => 1];
            $Update->ExeUpdate(DB_EAD_SUPPORT, $Publish, 'WHERE support_id = :id', "id={$PostData['id']}");
            $jSON['success'] = true;
            break;

        //SUPPORT :: UNPUBLISH
        case 'ead_support_unpublish':
            $Publish = ['support_published' => null];
            $Update->ExeUpdate(DB_EAD_SUPPORT, $Publish, 'WHERE support_id = :id', "id={$PostData['id']}");
            $jSON['success'] = true;
            break;

        //SUPPORT :: ADD RESPONSE
        case 'ead_support_add':
            $SupportId = $PostData['support_id'];

            //GER RESPONSE
            if (empty($PostData['response_content'])):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>Oppsss, </b> você esqueceu de escrever a resposta {$_SESSION['userLogin']['user_name']}, para responder informe a resposta!", E_USER_WARNING);
                break;
            endif;

            //SET RESPONSE
            $PostData['user_id'] = $_SESSION['userLogin']['user_id'];
            $PostData['response_open'] = date("Y-m-d H:i:s");
            $Create->ExeCreate(DB_EAD_SUPPORT_REPLY, $PostData);

            //UPDATE SUPPORT STATUS
            $UpdateSupportStatus = ['support_status' => 2, 'support_reply' => date("Y-m-d H:i:s")];

            $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateSupportStatus, "WHERE support_id = :support", "support={$SupportId}");

            $jSON['divcontent'] = [".j_ead_support_status" => "<span class='status bar_blue radius'>Respondido</span>"];
            $jSON['divremove'] = '.ead_support_finish';
            $jSON['clear'] = true;

            //SEND NOTIFICARION
            $Read->ExeRead(DB_USERS, "WHERE user_id=(SELECT user_id FROM " . DB_EAD_SUPPORT . " WHERE support_id = :support)", "support={$SupportId}");
            $UserResponderData = $Read->getResult()[0];

            //GET CLASS ID
            $Read->LinkResult(DB_EAD_SUPPORT, "support_id", $SupportId, 'class_id');
            $ClassId = $Read->getResult()[0]['class_id'];

            //GET COURSE NAME
            $Read->FullRead("SELECT course_title, course_name FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id=(SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_id=:class))", "class={$ClassId}");
            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');
            $MailCourseName = ($Read->getResult() ? $Read->getResult()[0]['course_name'] : '');

            $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE class_id=:class", "class={$ClassId}");
            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');
            $MailClassName = ($Read->getResult() ? $Read->getResult()[0]['class_name'] : 'N/A');

            require '../../_ead/wc_ead.email.php';
            $MailBody = "
                <p style='font-size: 1.4em;'>Olá {$UserResponderData['user_name']}, </p>
                <p>{$_SESSION['userLogin']['user_name']} acabou de enviar uma resposta em sua dúvida!</p>
                <p>Para responder, efetue <a href='" . BASE . "/campus' title='Acessar minha conta na plataforma!'>login aqui</a> e acesse a aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}.</b></p>
                <p>Já esta logado(a) na plataforma? Então acesse diretamente <a href='" . BASE . "/campus/curso/{$MailCourseName}/{$MailClassName}#{$SupportId}' title='Acessar aula {$MailClassTitle}!'>clicando aqui!</a></p>
                <p><b>IMPORTANTE:</b> Para concluir sua dúvida envie sua avaliação no ticket, ou adicione outra resposta para tirar mais dúvidas!</p>
                <p>...</p>
                <p>Se tiver qualquer problema, não deixe de responder este e-mail!</p>
            ";

            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
            $Email = new Email;
            $Email->EnviarMontando("#" . str_pad($SupportId, 4, 0, 0) . " - Sua dúvida foi respondida!", $MailContent, MAIL_SENDER, MAIL_USER, "{$UserResponderData['user_name']} {$UserResponderData['user_lastname']}", $UserResponderData['user_email']);

            //RETURN RESPONSES
            $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id=:support ORDER BY response_open ASC", "support={$SupportId}");
            if ($Read->getResult()):
                $jSON['content'] = "";
                foreach ($Read->getResult() as $ResponseReply):
                    $Read->LinkResult(DB_USERS, "user_id", "{$ResponseReply['user_id']}", 'user_id, user_name, user_lastname, user_email, user_thumb');
                    $user_reply = $Read->getResult()[0];

                    $UserThumb = "../../uploads/{$user_reply['user_thumb']}";
                    $user_reply['user_thumb'] = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_reply['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                    $jSON['content'] .= "<article class='ead_support_response ead_support_response_reply' id='{$ResponseReply['response_id']}'>
                        <div class='ead_support_response_avatar'>
                            <img class='rounded' src='" . BASE . "/tim.php?src={$user_reply['user_thumb']}&w=" . round(AVATAR_W / 2) . "&h=" . round(AVATAR_H / 2) . "' alt='{$user_reply['user_name']}' title='{$user_reply['user_lastname']}'/>
                        </div><div class='ead_support_response_content'>
                            <header class='ead_support_response_content_header'>
                                <h1>Ticket de <a target='_blank' href='dashboard.php?wc=teach/students_gerent&id={$user_reply['user_id']}' title='{$user_reply['user_name']} {$user_reply['user_lastname']}'>{$user_reply['user_name']} {$user_reply['user_lastname']}</a> dia " . date('d/m/Y H\hi', strtotime($ResponseReply['response_open'])) . "</h1>
                            </header>
                            <div class='htmlchars reply_chars'>{$ResponseReply['response_content']}</div>
                            <div class='ead_support_response_actions'>
                                <span rel='ead_support_response_reply' class='j_delete_action icon-cross btn btn_red' id='{$ResponseReply['response_id']}'>Apagar</span>
                                <span rel='ead_support_response_reply' callback='Courses' callback_action='ead_support_reply_delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$ResponseReply['response_id']}'>Deletar Resposta?</span>
                                <span class='btn btn_blue icon-pencil2 center j_ead_support_action' data-action='ead_support_reply_edit' id='{$ResponseReply['response_id']}'>Editar</span>
                            </div>
                        </div>
                        <div class='ead_support_response_edit_modal reply'>
                            <form name='class_add' action='' method='post' enctype='multipart/form-data'>
                                <p class='title icon-pencil2'>Atualizar Responsta de {$user_reply['user_name']} {$user_reply['user_lastname']}</p>
                                <span class='btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_ead_support_action_close'></span>

                                <input type='hidden' name='callback' value='Courses'/>
                                <input type='hidden' name='callback_action' value='ead_support_reply_edit_confirm'/>
                                <input type='hidden' name='response_id' value='{$ResponseReply['response_id']}'/>

                                <label class='label'>
                                    <textarea class='work_mce_basic' style='font-size: 1em;' name='response_content' rows='3'>{$ResponseReply['response_content']}</textarea>
                                </label>

                                <div class='wc_actions' style='margin-top: 15px;'>
                                    <button class='btn btn_blue icon-pencil2'>ATUALIZAR RESPOSTA</button>
                                    <img class='form_load none' style='margin-left: 10px;' alt='Enviando Requisição!' title='Enviando Requisição!' src='_img/load.gif'/>
                                </div>
                            </form>
                        </div>
                    </article>";
                endforeach;
            endif;

            //RESPONSE NULL
            $jSON['success'] = true;
            break;

        //SUPPORT :: DELETE
        case 'ead_support_delete_confirm':
            $SupportId = $PostData['support_id'];

            //SEND NOTIFICARION
            $Read->ExeRead(DB_USERS, "WHERE user_id=(SELECT user_id FROM " . DB_EAD_SUPPORT . " WHERE support_id=:support)", "support={$SupportId}");
            $UserResponderData = $Read->getResult()[0];

            //GET SUPPORT
            $Read->LinkResult(DB_EAD_SUPPORT, "support_id", $SupportId, 'class_id, support_content');
            $ClassId = $Read->getResult()[0]['class_id'];
            $TicketC = $Read->getResult()[0]['support_content'];

            //GET COURSE NAME
            $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id=(SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id=(SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_id=:class))", "class={$ClassId}");
            $MailCourseTitle = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : 'N/A');

            $Read->FullRead("SELECT class_title FROM " . DB_EAD_CLASSES . " WHERE class_id=:class", "class={$ClassId}");
            $MailClassTitle = ($Read->getResult() ? $Read->getResult()[0]['class_title'] : 'N/A');

            require '../../_ead/wc_ead.email.php';
            $MailBody = "
                    <p style='font-size: 1.1em;'>Oppsss {$UserResponderData['user_name']},</p>
                    <p>Este e-mail é para informar que tive que recusar seu ticket na aula <b>{$MailClassTitle}</b> do curso <b>{$MailCourseTitle}</b>...</p>
                    " . ($PostData['mail_body'] ? "<p>{$PostData['mail_body']}</p><p><i>Atenciosamente {$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}!<br>Equipe de Suporte " . SITE_NAME . ".</i></p><p>...</p>" : '') . "
                    <p>As recusas geralmente ocorrem pois o ambiente de suporte é direcionado a aula, e os motivos mais comuns são:</p>
                    <p>
                     <ul>
                      <li>O ticket já foi resolvido por outro canal,</li>
                      <li>O assunto do ticket não é sobre a aula,</li>
                      <li>O ticket não apresenta uma pergunta,</li>
                      <li>A pergunta já foi respondida na aula,</li>
                      <li>Existem ofensas ou textos proibidos.</li>
                     </ul>
                    </p>
                    <p><b>IMPORTANTE:</b> Caso os motivos acima não estejam em conformidade com a recusa, por favor encaminhe esse e-mail para " . SITE_ADDR_EMAIL . ".</p>
                    <p>É importante sempre prestar atenção nessas regras básicas {$UserResponderData['user_name']}, pois o suporte é um forum destinado a todos os alunos, e com a ajuda de cada um podemos sempre melhorar o atendimento.</p>
                    <p><b>Caso a dúvida permaneça, não deixe de abrir outro ticket!</b></p> 
                    <p>...</p>
                    <p>Antes de abrir um novo ticket, pedimos que verifique se sua dúvida já não foi respondida, e se ela está dentro das regras básicas do suporte...</p>
                    <p>Assim teremos sempre um ambiênte de estudos organizado e de alta produtividade para você e para todos os alunos!</p>
                    <p>Obrigado pela compreensão {$UserResponderData['user_name']}.</p>
                    <div style='font-size: 0.8em'><b>CÓPIA DO TICKET:</b> {$TicketC}</div>
                ";

            $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
            $Email = new Email;
            $Email->EnviarMontando("#" . str_pad($SupportId, 4, 0, 0) . " - Um ticket foi recusado", $MailContent, MAIL_SENDER, MAIL_USER, "{$UserResponderData['user_name']} {$UserResponderData['user_lastname']}", $UserResponderData['user_email']);

            //TICKET DELETE
            $Delete->ExeDelete(DB_EAD_SUPPORT, "WHERE support_id=:support", "support={$SupportId}");
            $jSON['forceclick'] = "#{$SupportId} .j_ead_support_action_close";
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>Ticket Removido:</b> Recarregando suporte...");
            $jSON['redirect'] = "dashboard.php?wc=teach/support_response";
            break;

        //SUPPORT :: EDIT
        case 'ead_support_edit_confirm':
            $SupportUpdate = ['support_content' => $PostData['support_content']];
            $Update->ExeUpdate(DB_EAD_SUPPORT, $SupportUpdate, "WHERE support_id=:support", "support={$PostData['support_id']}");

            $jSON['divcontent'] = ["#{$PostData['support_id']} .response_chars" => $PostData['support_content']];
            $jSON['forceclick'] = "#{$PostData['support_id']} .j_ead_support_action_close";
            break;

        //SUPPORT :: SET COMPLETED
        case 'ead_support_set_answered':
            $UpdateSupport = ['support_status' => 2];
            $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateSupport, "WHERE support_id=:support", "support={$PostData['id']}");
            break;

        //SUPPORT :: REPLY EDIT
        case 'ead_support_reply_edit_confirm':
            $ResponseUpdate = ['response_content' => $PostData['response_content']];
            $Update->ExeUpdate(DB_EAD_SUPPORT_REPLY, $ResponseUpdate, "WHERE response_id=:response", "response={$PostData['response_id']}");

            $jSON['divcontent'] = ["#{$PostData['response_id']} .reply_chars" => $PostData['response_content']];
            $jSON['forceclick'] = "#{$PostData['response_id']} .j_ead_support_action_close";
            break;

        //SUPPORT :: DELETE REPLY
        case 'ead_support_reply_delete':
            //READ SUPPORT ID
            $Read->LinkResult(DB_EAD_SUPPORT_REPLY, "response_id", $PostData['del_id'], 'support_id');
            if (!$Read->getResult()):
                $jSON['success'] = true;
                break;
            else:
                $SupportId = $Read->getResult()[0]['support_id'];
            endif;

            //UPDATE SUPPORT STATUS
            $Read->FullRead("SELECT response_id FROM " . DB_EAD_SUPPORT_REPLY . " WHERE support_id=:support AND response_id != :resp", "support={$SupportId}&resp={$PostData['del_id']}");
            if (!$Read->getResult()):
                $UpdateSupportStatus = ['support_status' => 1, 'support_reply' => null];
                $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateSupportStatus, "WHERE support_id=:support", "support={$SupportId}");
                $jSON['divcontent'] = [".j_ead_support_status" => "<span class='status bar_red radius'>Em Aberto</span>"];
            endif;

            $Delete->ExeDelete(DB_EAD_SUPPORT_REPLY, "WHERE response_id=:reply", "reply={$PostData['del_id']}");
            $jSON['success'] = true;
            break;

        case 'filter_support':
            $returndata = array();
            $strArray = explode("&", $PostData['FormGetData']);
            $i = 0;
            foreach ($strArray as $item) {
                $array = explode("=", $item);
                $returndata[$array[0]] = $array[1];
            }

            $jSON['redirect'] = "dashboard.php?wc=teach/support_response&course_id={$returndata['course_id']}&module_id={$returndata['module_id']}&class_id={$returndata['class_id']}&support_status={$returndata['support_status']}";
            break;

        case 'filter_support_list':
            $returndata = array();
            $strArray = explode("&", $PostData['FormGetData']);
            $i = 0;
            foreach ($strArray as $item) {
                $array = explode("=", $item);
                $returndata[$array[0]] = $array[1];
            }

            $jSON['redirect'] = "dashboard.php?wc=teach/support&course_id={$returndata['course_id']}&module_id={$returndata['module_id']}&class_id={$returndata['class_id']}&support_status={$returndata['support_status']}";
            break;

        //COURSE MATERIAL :: CLASS SELECT
        case 'module_filter':
            $CourseId = $PostData['key'];
            $Read->FullRead("SELECT module_id, module_title FROM " . DB_EAD_MODULES . " WHERE course_id = :course ORDER BY module_order ASC", "course={$CourseId}");
            if (!$Read->getResult()):
                $jSON['content'] = "<option value=''>Não existem módulos para esse curso!</option>";
            else:
                $jSON['content'] = "<option value=''>Selecione um módulo (OPCIONAL)</option>";
                foreach ($Read->getResult() as $CLASS):
                    $jSON['content'] .= "<option value='{$CLASS['module_id']}'>{$CLASS['module_title']}</option>";
                endforeach;
            endif;

            $jSON['target'] = ".jwc_combo_target_module";
            break;

        //EAD ORDER :: MANAGER
        case 'ead_order_single_gerent':
            $OrderId = $PostData['order_id'];
            unset($PostData['order_id']);

            //GET ORDER SALE
            $Read->LinkResult(DB_EAD_ORDERS, "order_id", $OrderId);
            $OrderSale = ($Read->getResult() ? $Read->getResult()[0] : null);

            //GET COURSE SALE
            $Read->LinkResult(DB_EAD_COURSES, "course_id", $OrderSale['course_id']);
            $OrderCourse = ($Read->getResult() ? $Read->getResult()[0] : null);

            //UPDATE ORDER
            $Update->ExeUpdate(DB_EAD_ORDERS, $PostData, "WHERE order_id=:order", "order={$OrderId}");

            //CHANGE RELATED TABLES IF PRINCIPAL ORDER
            $Read->FullRead("SELECT enrollment_id, course_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_order=:order", "order={$OrderId}");
            if ($Read->getResult()):
                //CHANGE COURSE
                if ($PostData['course_id'] != $Read->getResult()[0]['course_id']):
                    $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_id=:enroll OR enrollment_bonus=:enroll", "enroll={$Read->getResult()[0]['enrollment_id']}");

                //UPDATE ENROLLMENT, CLASSES, SUPPORTS
                else:
                    $UpdateEnroll = ['user_id' => $PostData['user_id']];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnroll, "WHERE enrollment_order=:order OR enrollment_bonus=:enroll", "order={$OrderId}&enroll={$Read->getResult()[0]['enrollment_id']}");
                    $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateEnroll, "WHERE enrollment_id=:enroll", "enroll={$Read->getResult()[0]['enrollment_id']}");
                    $Update->ExeUpdate(DB_EAD_SUPPORT, $UpdateEnroll, "WHERE enrollment_id=:enroll OR enrollment_id IN(SELECT enrollment_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_bonus=:enroll)", "enroll={$Read->getResult()[0]['enrollment_id']}");
                    $Update->ExeUpdate(DB_EAD_SUPPORT_REPLY, $UpdateEnroll, "WHERE enrollment_id=:enroll OR enrollment_id IN(SELECT enrollment_id FROM " . DB_EAD_ENROLLMENTS . " WHERE enrollment_bonus=:enroll)", "enroll={$Read->getResult()[0]['enrollment_id']}");
                endif;
            endif;

            //ORDER STATUS CHANGE ACTIONS
            switch ($PostData['order_status']):
                case 'approved':
                case 'admin_free':
                    //GET ORDER SALE
                    $Read->LinkResult(DB_EAD_ORDERS, "order_id", $OrderId);
                    $OrderSale = ($Read->getResult() ? $Read->getResult()[0] : null);

                    //GET COURSE SALE
                    $Read->LinkResult(DB_EAD_COURSES, "course_id", $OrderSale['course_id']);
                    $OrderCourse = ($Read->getResult() ? $Read->getResult()[0] : null);

                    //ENROLLMENT CREATE, UPDATE
                    $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:user AND course_id=:course", "user={$OrderSale['user_id']}&course={$OrderSale['course_id']}");
                    if (!$Read->getResult()):
                        $CreateEnrollment = [
                            'user_id' => $OrderSale['user_id'],
                            'course_id' => $OrderSale['course_id'],
                            'enrollment_order' => $OrderSale['order_id'],
                            'enrollment_start' => date('Y-m-d H:i:s'),
                            'enrollment_access' => null,
                            'enrollment_end' => (!empty($OrderSale['order_signature_period']) ? date("Y-m-d H:i:s", strtotime("+{$OrderSale['order_signature_period']}days")) : ($OrderCourse['course_vendor_access'] ? date('Y-m-d H:i:s', strtotime("+{$OrderCourse['course_vendor_access']}months")) : null)),
                        ];
                        $Create->ExeCreate(DB_EAD_ENROLLMENTS, $CreateEnrollment);
                    elseif (empty($OrderSale['order_delivered'])):
                        if (!empty($OrderSale['order_signature_period'])):
                            $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s", strtotime("+{$OrderSale['order_signature_period']}days"))];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                        elseif (!$OrderCourse['course_vendor_access']):
                            $UpdateEnrollment = ['enrollment_end' => null];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                        else:
                            $DateThis = date("Y-m-d H:i:s");
                            $DateAccess = $Read->getResult()[0]['enrollment_end'];
                            $EnrollmentDateaEnd = ($DateAccess > $DateThis ? $DateAccess : $DateThis);

                            $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($EnrollmentDateaEnd . "+{$OrderCourse['course_vendor_access']}months"))];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                        endif;
                    endif;

                    //UPDATE ORDER STATUS
                    $UpdateOrderStatus = ['order_delivered' => 1, 'order_confirmation_purchase_date' => date('Y-m-d H:i:s')];
                    $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id=:order", "order={$OrderSale['order_id']}");
                    break;

                case 'canceled':
                    //CANCEL ACCESS WHERE SIGNATURE
                    if ($OrderSale['order_signature']):
                        $UpdateOrderStatus = ['order_status' => 'canceled', 'order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s"), 'order_signature_status' => 'canceled'];
                        $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id=:order", "order={$OrderSale['order_id']}");

                        $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s")];
                        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE user_id=:user AND course_id=:course", "user={$OrderSale['user_id']}&course={$OrderSale['course_id']}");
                    else:
                        //UPDATE ORDER STATUS
                        $UpdateOrderStatus = ['order_status' => 'canceled', 'order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
                        $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id=:order", "order={$OrderSale['order_id']}");

                        $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$OrderSale['order_id']}");

                        if ($Read->getResult()):
                            $EnrollmentId = $Read->getResult()[0]['enrollment_id'];
                            //GET COURSE BONUS
                            $Read->FullRead(""
                                    . "SELECT "
                                    . "e.*, "
                                    . "c.* "
                                    . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                    . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                                    . "WHERE e.enrollment_bonus = :enroll", "enroll={$EnrollmentId}"
                            );
                            if ($Read->getResult()):
                                $UserId = $Read->getResult()[0]['user_id'];

                                foreach ($Read->getResult() as $EnrollmentBonus):
                                    $Read->FullRead(""
                                            . "SELECT "
                                            . "b.* "
                                            . "FROM " . DB_EAD_COURSES_BONUS . " b "
                                            . "WHERE b.bonus_course_id = :course "
                                            . "AND b.course_id IN (SELECT e.course_id FROM " . DB_EAD_ENROLLMENTS . " e WHERE e.enrollment_id != :enrollmentMain AND e.enrollment_id != :enrollmentBonus AND e.user_id = :user)", "enrollmentMain={$EnrollmentId}&enrollmentBonus={$EnrollmentBonus['enrollment_id']}&user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}"
                                    );

                                    if ($Read->getResult()):
                                        $Read->FullRead(""
                                                . "SELECT "
                                                . "e.* "
                                                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                                . "WHERE user_id = :user AND course_id = :course", "user={$EnrollmentBonus['user_id']}&course={$Read->getResult()[0]['course_id']}"
                                        );
                                        if ($Read->getResult()):
                                            $UpdateEnrollment = [
                                                'enrollment_bonus' => $Read->getResult()[0]['enrollment_id'],
                                                'enrollment_end' => $Read->getResult()[0]['enrollment_end']
                                            ];
                                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrollment", "enrollment={$EnrollmentBonus['enrollment_id']}");
                                        endif;
                                    else:
                                        $Read->FullRead(""
                                                . "SELECT "
                                                . "o.*, "
                                                . "c.* "
                                                . "FROM " . DB_EAD_ORDERS . " o "
                                                . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = o.course_id "
                                                . "WHERE o.user_id = :user "
                                                . "AND o.course_id = :course "
                                                . "AND o.order_status IN ('approved' 'completed', 'admin_free')"
                                                . "ORDER BY o.order_purchase_date DESC LIMIT 1", "user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}");

                                        if ($Read->getResult()):
                                            $UpdateEnrollmentOrder = [
                                                'enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['order_purchase_date'] . "+{$Read->getResult()[0]['course_vendor_access']}months")),
                                                'enrollment_order' => $Read->getResult()[0]['order_id'],
                                                'enrollment_bonus' => null
                                            ];

                                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentOrder, "WHERE enrollment_id = :enroll", "enroll={$EnrollmentBonus['enrollment_id']}");

                                        endif;
                                    endif;
                                endforeach;
                            endif;
                        endif;

                        //DELETE DIRECT ENROLLMENT
                        $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_order=:order", "order={$OrderSale['order_id']}");

                        //UPDATE RESET DELIVERED :: ACCESS ROLLBACK
                        $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:user AND course_id=:course", "user={$OrderSale['user_id']}&course={$OrderCourse['course_id']}");
                        if (!empty($OrderSale['order_delivered']) && $OrderCourse['course_vendor_access'] && $Read->getResult()):
                            $UpdateEnrolmentCanceled = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['enrollment_end'] . "-{$OrderCourse['course_vendor_access']}months"))];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrolmentCanceled, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                        endif;
                    endif;
                    break;

                case 'refunded':
                    //UPDATE ORDER STATUS
                    $UpdateOrderStatus = ['order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
                    $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id=:order", "order={$OrderSale['order_id']}");

                    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$OrderSale['order_id']}");

                    if ($Read->getResult()):
                        $EnrollmentId = $Read->getResult()[0]['enrollment_id'];
                        //GET COURSE BONUS
                        $Read->FullRead(""
                                . "SELECT "
                                . "e.*, "
                                . "c.* "
                                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                                . "WHERE e.enrollment_bonus = :enroll", "enroll={$EnrollmentId}"
                        );
                        if ($Read->getResult()):
                            $UserId = $Read->getResult()[0]['user_id'];

                            foreach ($Read->getResult() as $EnrollmentBonus):
                                $Read->FullRead(""
                                        . "SELECT "
                                        . "b.* "
                                        . "FROM " . DB_EAD_COURSES_BONUS . " b "
                                        . "WHERE b.bonus_course_id = :course "
                                        . "AND b.course_id IN (SELECT e.course_id FROM " . DB_EAD_ENROLLMENTS . " e WHERE e.enrollment_id != :enrollmentMain AND e.enrollment_id != :enrollmentBonus AND e.user_id = :user)", "enrollmentMain={$EnrollmentId}&enrollmentBonus={$EnrollmentBonus['enrollment_id']}&user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}"
                                );

                                if ($Read->getResult()):
                                    $Read->FullRead(""
                                            . "SELECT "
                                            . "e.* "
                                            . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                            . "WHERE user_id = :user AND course_id = :course", "user={$EnrollmentBonus['user_id']}&course={$Read->getResult()[0]['course_id']}"
                                    );
                                    if ($Read->getResult()):
                                        $UpdateEnrollment = [
                                            'enrollment_bonus' => $Read->getResult()[0]['enrollment_id'],
                                            'enrollment_end' => $Read->getResult()[0]['enrollment_end']
                                        ];
                                        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrollment", "enrollment={$EnrollmentBonus['enrollment_id']}");
                                    endif;
                                else:
                                    $Read->FullRead(""
                                            . "SELECT "
                                            . "o.*, "
                                            . "c.* "
                                            . "FROM " . DB_EAD_ORDERS . " o "
                                            . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = o.course_id "
                                            . "WHERE o.user_id = :user "
                                            . "AND o.course_id = :course "
                                            . "AND o.order_status IN ('approved' 'completed', 'admin_free')"
                                            . "ORDER BY o.order_purchase_date DESC LIMIT 1", "user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}");

                                    if ($Read->getResult()):
                                        $UpdateEnrollmentOrder = [
                                            'enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['order_purchase_date'] . "+{$Read->getResult()[0]['course_vendor_access']}months")),
                                            'enrollment_order' => $Read->getResult()[0]['order_id'],
                                            'enrollment_bonus' => null
                                        ];

                                        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentOrder, "WHERE enrollment_id = :enroll", "enroll={$EnrollmentBonus['enrollment_id']}");

                                    endif;
                                endif;
                            endforeach;
                        endif;
                    endif;

                    //DELETE DIRECT ENROLLMENT
                    $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_order=:order", "order={$OrderSale['order_id']}");

                    //UPDATE RESET DELIVERED :: ACCESS ROLLBACK
                    if (!empty($OrderSale) && !empty($OrderCourse)):
                        $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:user AND course_id=:course", "user={$OrderSale['user_id']}&course={$OrderSale['course_id']}");
                        if (!empty($OrderSale['order_delivered']) && $OrderCourse['course_vendor_access'] && $Read->getResult()):
                            $UpdateEnrolmentCanceled = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['enrollment_end'] . "-{$OrderCourse['course_vendor_access']}months"))];
                            $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrolmentCanceled, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                        endif;
                    endif;
                    break;
            endswitch;

            $jSON['trigger'] = AjaxErro("<b>PEDIDO ATUALIZADO:</b> O pedido foi atualizado, assim como matrículas e bônus relacionados!");
            $jSON['redirect'] = "dashboard.php?wc=teach/orders_gerent&id={$OrderSale['order_id']}";
            break;

        //EAD ORDER :: DELETE
        case 'ead_order_single_delete':
            $OrderId = $PostData['del_id'];

            $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$OrderId}");

            if ($Read->getResult()):
                $EnrollmentId = $Read->getResult()[0]['enrollment_id'];
                //GET COURSE BONUS
                $Read->FullRead(""
                        . "SELECT "
                        . "e.*, "
                        . "c.* "
                        . "FROM " . DB_EAD_ENROLLMENTS . " e "
                        . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = e.course_id "
                        . "WHERE e.enrollment_bonus = :enroll", "enroll={$EnrollmentId}"
                );
                if ($Read->getResult()):
                    $UserId = $Read->getResult()[0]['user_id'];

                    foreach ($Read->getResult() as $EnrollmentBonus):
                        $Read->FullRead(""
                                . "SELECT "
                                . "b.* "
                                . "FROM " . DB_EAD_COURSES_BONUS . " b "
                                . "WHERE b.bonus_course_id = :course "
                                . "AND b.course_id IN (SELECT e.course_id FROM " . DB_EAD_ENROLLMENTS . " e WHERE e.enrollment_id != :enrollmentMain AND e.enrollment_id != :enrollmentBonus AND e.user_id = :user)", "enrollmentMain={$EnrollmentId}&enrollmentBonus={$EnrollmentBonus['enrollment_id']}&user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}"
                        );

                        if ($Read->getResult()):
                            $Read->FullRead(""
                                    . "SELECT "
                                    . "e.* "
                                    . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                    . "WHERE user_id = :user AND course_id = :course", "user={$EnrollmentBonus['user_id']}&course={$Read->getResult()[0]['course_id']}"
                            );
                            if ($Read->getResult()):
                                $UpdateEnrollment = [
                                    'enrollment_bonus' => $Read->getResult()[0]['enrollment_id'],
                                    'enrollment_end' => $Read->getResult()[0]['enrollment_end']
                                ];
                                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrollment", "enrollment={$EnrollmentBonus['enrollment_id']}");
                            endif;
                        else:
                            $Read->FullRead(""
                                    . "SELECT "
                                    . "o.*, "
                                    . "c.* "
                                    . "FROM " . DB_EAD_ORDERS . " o "
                                    . "INNER JOIN " . DB_EAD_COURSES . " c ON c.course_id = o.course_id "
                                    . "WHERE o.user_id = :user "
                                    . "AND o.course_id = :course "
                                    . "AND o.order_status IN ('approved' 'completed', 'admin_free')"
                                    . "ORDER BY o.order_purchase_date DESC LIMIT 1", "user={$EnrollmentBonus['user_id']}&course={$EnrollmentBonus['course_id']}");

                            if ($Read->getResult()):
                                $UpdateEnrollmentOrder = [
                                    'enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['order_purchase_date'] . "+{$Read->getResult()[0]['course_vendor_access']}months")),
                                    'enrollment_order' => $Read->getResult()[0]['order_id'],
                                    'enrollment_bonus' => null
                                ];

                                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentOrder, "WHERE enrollment_id = :enroll", "enroll={$EnrollmentBonus['enrollment_id']}");

                            endif;
                        endif;
                    endforeach;
                endif;
            endif;
            $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_id=:order", "order={$OrderId}");
            if ($Read->getResult()):
                $OrderSale = $Read->getResult()[0];

                $Read->LinkResult(DB_EAD_COURSES, "course_id", $OrderSale['course_id']);
                $OrderCourse = ($Read->getResult() ? $Read->getResult()[0] : null);

                //UPDATE RESET DELIVERED :: ACCESS ROLLBACK
                if ($OrderCourse):
                    $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id=:user AND course_id=:course", "user={$OrderSale['user_id']}&course={$OrderSale['course_id']}");
                    if (!empty($OrderSale['order_delivered']) && $OrderCourse['course_vendor_access'] && $Read->getResult()):
                        $UpdateEnrolmentCanceled = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['enrollment_end'] . "-{$OrderCourse['course_vendor_access']}months"))];
                        $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrolmentCanceled, "WHERE enrollment_id=:enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                    endif;
                endif;

                //DELETE ORDER
                $Delete->ExeDelete(DB_EAD_ORDERS, "WHERE order_id=:order", "order={$OrderId}");
            endif;

            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>SUCESSO:</b> O pedido foi removido!");
            $jSON['redirect'] = "dashboard.php?wc=teach/orders";
            break;

        //SEGMENTS :: MANAGER
        case 'segment_manager':
            $SegmentId = $PostData['segment_id'];
            array_map('strip_tags', $PostData);

            $PostData['segment_name'] = Check::Name($PostData['segment_title']);
            $Read->FullRead("SELECT segment_id FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id != :id AND segment_name=:name", "id={$SegmentId}&name={$PostData['segment_name']}");
            if ($Read->getResult()):
                $PostData['segment_name'] = "{$SegmentId}-{$PostData['segment_name']}";
            endif;

            $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id= :id", "id={$SegmentId}");
            $ThisSegment = $Read->getResult()[0];

            $Update->ExeUpdate(DB_EAD_COURSES_SEGMENTS, $PostData, "WHERE segment_id=:id", "id={$SegmentId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-checkmark'>SUCESSO:</b> O segmento {$PostData['segment_title']} foi atualizado com sucesso!");
            break;

        //COURSE ORDER    
        case 'segment_order':
            if (is_array($PostData['Data'])):
                foreach ($PostData['Data'] as $RE):
                    $UpdateCourse = ['segment_order' => $RE[1]];
                    $Update->ExeUpdate(DB_EAD_COURSES_SEGMENTS, $UpdateCourse, "WHERE segment_id = :segment", "segment={$RE[0]}");
                endforeach;

                $jSON['sucess'] = true;
            endif;
            break;

        //SEGMENTS :: REMOVE
        case 'segment_remove':
            $SegmentId = $PostData['del_id'];
            array_map('strip_tags', $PostData);

            // FIX RODOLFO RICHARD 2016-11-26
            // VERIFICA SE HÁ CURSOS NO SEGMENTO ANTES DE EXCLUIR
            $Read->ExeRead(DB_EAD_COURSES, " WHERE course_segment = :segment", "segment={$SegmentId}");
            if ($Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-info'>OPPSSS:</b> Há Cursos cadastrados neste Segmento. Por favor, desvincule os cursos antes de excluir o segmento!", E_USER_WARNING);
            else:
                $Delete->ExeDelete(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id = :id", "id={$SegmentId}");
                $jSON['success'] = true;
            endif;
            break;

        //COURSE CERTIFICATION
        case 'course_certification':
            $CourseId = $PostData['course_id'];
            unset($PostData['course_id'], $PostData['course_certification_mockup']);

            if (in_array('', $PostData)):
                $jSON['trigger'] = AjaxErro("<b class='icon-info'>OPPSSS:</b> Para cadastrar um certificado é preciso informar todos os campos!", E_USER_NOTICE);
                $jSON['error'] = true;
                break;
            endif;

            $CertificationMockup = (!empty($_FILES['course_certification_mockup']) ? $_FILES['course_certification_mockup'] : null);
            $Read->FullRead("SELECT course_certification_mockup FROM " . DB_EAD_COURSES . " WHERE course_id=:id AND course_certification_mockup IS NOT NULL", "id={$CourseId}");
            if ($CertificationMockup):
                if ($Read->getResult() && !empty($Read->getResult()[0]['course_certification_mockup']) && file_exists("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}") && !is_dir("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}")):
                    unlink("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}");
                endif;

                $Upload = new Upload('../../uploads/');
                $Upload->Image($CertificationMockup, md5($CourseId), '3508', 'courses');
                $PostData['course_certification_mockup'] = $Upload->getResult();
            elseif (!$Read->getResult()):
                $jSON['trigger'] = AjaxErro("<b class='icon-warning'>OPPSSS:</b> Você esqueceu de enviar a imagem do cetificado. Falta apenas ela para atualizar!", E_USER_WARNING);
                $jSON['error'] = true;
                break;
            endif;

            $Update->ExeUpdate(DB_EAD_COURSES, $PostData, "WHERE course_id=:id", "id={$CourseId}");
            $jSON['trigger'] = AjaxErro("<b class='icon-trophy'>TUDO CERTO:</b> O certificado foi enviado e o curso foi atualizado com sucesso!");
            break;

        //COURSE CERTIFICATION REMOVE
        case 'course_certification_remove':
            $CourseId = $PostData['del_id'];

            $Read->FullRead("SELECT course_certification_mockup FROM " . DB_EAD_COURSES . " WHERE course_id=:id", "id={$CourseId}");
            if ($Read->getResult() && !empty($Read->getResult()[0]['course_certification_mockup']) && file_exists("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}") && !is_dir("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}")):
                unlink("../../uploads/{$Read->getResult()[0]['course_certification_mockup']}");
            endif;

            $ReserCertification = ['course_certification_workload' => null, 'course_certification_request' => null, 'course_certification_mockup' => null];
            $Update->ExeUpdate(DB_EAD_COURSES, $ReserCertification, "WHERE course_id=:id", "id={$CourseId}");
            $jSON['redirect'] = "dashboard.php?wc=teach/courses_create&id={$CourseId}#certification";
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
