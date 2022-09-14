<?php
$AdminLevel = LEVEL_WC_EAD_COURSES;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$Segment = filter_input(INPUT_GET, 'segment', FILTER_VALIDATE_INT);
if ($Segment):
    $Read->FullRead("SELECT segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " WHERE segment_id = :id", "id={$Segment}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);
    endif;
endif;

$Search = filter_input_array(INPUT_POST);
if ($Search && ($Search['s'] || isset($Search['segment']))):
    $S = urlencode($Search['segment']);
    header("Location: dashboard.php?wc=teach/courses&segment={$S}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-books">Cursos<?= !empty($segment_title) ? " em {$segment_title}" : ""; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <?php
            if (empty($segment_title)):
                echo "Cursos";
            else:
                echo "<a title='" . ADMIN_NAME . "' href='dashboard.php?wc=teach/courses'>Cursos</a> <span class='crumb'>/</span> {$segment_title}";
            endif;
            ?>
        </p>
    </div>

    <div class="dashboard_header_search">
        <form style="width: 60%; display: inline-block" name="searchOrders" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <select name="segment" style="width: 55%; margin-right: 3px; padding: 5px 10px">
                <option value="">Todos</option>
                <?php
                $Read->FullRead("SELECT segment_id, segment_title FROM " . DB_EAD_COURSES_SEGMENTS . " ORDER BY segment_order ASC, segment_title ASC");
                if (!$Read->getResult()):
                    echo "<option value='' disabled='disabled'>Não existem segmentos cadastrados!</option>";
                else: foreach ($Read->getResult() as $SearchSegment):
                        echo "<option " . ($SearchSegment['segment_id'] == $Segment ? 'selected="selected"' : '') . " value='{$SearchSegment['segment_id']}'>{$SearchSegment['segment_title']}</option>";
                    endforeach;
                endif;
                ?>
            </select>

            <button class="btn btn_blue icon icon-search icon-notext"></button>
        </form>
        <?php if (empty($segment_title)): ?>
            <span class="btn btn_green icon-spinner9 wc_drag_active" title="Organizar Cursos">Ordenar</span>
        <?php endif; ?>
    </div>
</header>
<div class="dashboard_content">
    <?php
    $WhereSegment = ($Segment ? "WHERE course_segment = '{$Segment}'" : '');
    $Read->ExeRead(DB_EAD_COURSES, "{$WhereSegment} ORDER BY course_order ASC, course_title ASC");
    if (!$Read->getResult()):
        Erro("<span class='al_center icon-notification'>Ainda não existem cursos cadastrados em sua escola. Começe cadastrando seu primeiro curso!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Course):
            extract($Course);
            $Author = $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname, user_thumb");

            $Thumb = (file_exists("../uploads/{$Author['user_thumb']}") && !is_dir("../uploads/{$Author['user_thumb']}") ? "uploads/{$Author['user_thumb']}" : 'admin/_img/no_avatar.jpg');
            $Cover = (file_exists("../uploads/{$course_cover}") && !is_dir("../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');
            $Status = ($course_status != 1 ? 'inactive' : '');

            $Read->FullRead("SELECT count(module_id) AS ModCount FROM " . DB_EAD_MODULES . " WHERE course_id = :cs", "cs={$course_id}");
            $ModCount = str_pad($Read->getResult()[0]['ModCount'], 2, 0, 0);

            $Read->FullRead("SELECT count(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
            $ClassCount = str_pad($Read->getResult()[0]['ClassCount'], 4, 0, 0);
            $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);

            $Read->FullRead("SELECT count(enrollment_id) AS TotalEnrollment FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs", "cs={$course_id}");
            $StudentCount = str_pad($Read->getResult()[0]['TotalEnrollment'], 4, 0, 0);

            $Read->FullRead("SELECT COUNT(bonus_id) AS BonusCount FROM " . DB_EAD_COURSES_BONUS . " WHERE course_id = :course", "course={$course_id}");
            $CourseBonus = "<a title='Ver Bonus' href='dashboard.php?wc=teach/courses_create&id={$course_id}#bonus' style='display: inline; border:none; color: #888;'>+ {$Read->getResult()[0]['BonusCount']} Bônus</a>";

            $Read->FullRead("SELECT bonus_id FROM " . DB_EAD_COURSES_BONUS . " WHERE bonus_course_id = :course", "course={$course_id}");
            $CourseRocket = ($Read->getResult() ? 'icon-rocket' : 'icon-lab');

            $Read->LinkResult(DB_EAD_COURSES_SEGMENTS, "segment_id", $course_segment, 'segment_title');
            $CourseSegment = ($Read->getResult() ? "<p class='wc_ead_course_segment icon-price-tag'>{$Read->getResult()[0]['segment_title']}</p>" : "");

            $CourseDragAndDrop = (empty($segment_title) ? 'wc_draganddrop' : null);

            echo "<article class='box box25 wc_ead_course {$Status} {$CourseDragAndDrop}' callback='Courses' callback_action='courses_order' id='{$course_id}'>";
            echo "<div class='wc_ead_coursecover'>";
            echo "{$CourseSegment}" . ($course_certification_request ? "<b class='icon-trophy wc_ead_course_certification wc_tooltip'><span class='wc_tooltip_baloon'>Carga: " . $course_certification_workload . "h<br>Emissão: " . $course_certification_request . "%</span></b>" : '');
            echo "<img src='../tim.php?src={$Cover}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "' title='{$course_title}' alt='{$course_title}'/>";
            echo "<img class='wc_ead_courseauthor' src='../tim.php?src={$Thumb}&w=" . AVATAR_W / 4 . "&h=" . AVATAR_H / 4 . "' title='{$Author['user_name']} {$Author['user_lastname']}' alt='{$Author['user_name']} {$Author['user_lastname']}'/>";
            echo "</div>";
            echo "<div class='box_content wc_normalize_height'>";
            echo "<h1><span>Curso {$CourseBonus}</span> <a target='_blank' href='" . BASE . "/campus/curso/{$course_name}' title='" . ($course_title ? $course_title : "Edite esse curso!") . "'>" . ($course_title ? $course_title : "Edite esse curso!") . "</a></h1>";
            echo "<p class='wc_ead_coursestats'><span>{$StudentCount} ALUNOS</span><span>{$ClassCount} AULAS</span><span>{$ModCount} MÓDULOS</span><span>{$ClassTime} HORAS</span></p>";
            echo "</div>";
            echo "<div class='wc_ead_courseactions' style='font-size: 0.875em;'>";
            echo "<a class='btn " . ($Status ? "btn_yellow" : "btn_blue") . " m_top icon-pencil2' style='margin-right: 10px;' href='dashboard.php?wc=teach/courses_create&id={$course_id}' title='Editar Curso'>Editar</a>";
            echo "<a class='btn btn_green {$CourseRocket} m_top' href='dashboard.php?wc=teach/courses_gerent&id={$course_id}' title='Gerenciar Curso'>Gerenciar</a>";
            echo "</div>";
            echo "</article>";
        endforeach;
    endif;
    ?>
</div> 
