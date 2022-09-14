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

        $Author = $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname, user_thumb");

        $Thumb = (file_exists("../uploads/{$Author['user_thumb']}") && !is_dir("../uploads/{$Author['user_thumb']}") ? "uploads/{$Author['user_thumb']}" : 'admin/_img/no_avatar.jpg');
        $Cover = (file_exists("../uploads/{$course_cover}") && !is_dir("../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');
        $Status = ($course_status != 1 ? 'inactive' : '');
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um curso que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=teach/courses');
    endif;
else:
    $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um curso que não existe ou que foi removido recentemente!";
    header('Location: dashboard.php?wc=teach/courses');
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-folder">Material em <?= $course_title ? $course_title : 'Curso'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_create&id=<?= $course_id; ?>"><?= $course_title; ?></a>
            <span class="crumb">/</span>
            Downloads
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Gerenciar Curso!" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab">Gerenciar Curso!</a>
    </div>
</header>

<div class="dashboard_content">

    <?php
    $Read->FullRead("SELECT "
            . "cl.course_id, "
            . "cl.class_id, "
            . "cl.class_title, "
            . "cl.class_material, "
            . "m.module_title, "
            . "m.module_id, "
            . "COUNT(d.download_id) as class_downloads "
            . "FROM " . DB_EAD_CLASSES . " cl "
            . "INNER JOIN " . DB_EAD_MODULES . " m ON cl.module_id = m.module_id "
            . "LEFT JOIN " . DB_EAD_STUDENT_DOWNLOADS . " d ON cl.class_id = d.class_id "
            . "WHERE cl.course_id = :course AND cl.class_material IS NOT NULL "
            . "GROUP BY cl.class_id "
            . "ORDER BY m.module_order ASC, cl.class_order ASC ", "course={$course_id}"
    );

    if (!$Read->getResult()):
        echo Erro("<span class='al_center icon-notification'>Ainda não existe material para download registrado para {$course_title}!</span>", E_USER_NOTICE);
    else:
        ?>
        <div class="box box100">
            <div class="course_sigle_download course_sigle_download_title">
                <p class="row">
                    MÓDULO
                </p><p class="row">
                    AULA
                </p><p class="row">
                    DOWNLOADS
                </p><p class="row">
                    MATERIAL
                </p>
            </div>
            <?php
            foreach ($Read->getResult() as $DOWNLOAD):
                extract($DOWNLOAD);
                ?>
                <div class="course_sigle_download">
                    <p class="row title">
                        <?= $module_title; ?>
                    </p><p class="row">
                        <a class="icon-play2 a" target="_blank" title="Gerenciar Aula" href="dashboard.php?wc=teach/courses_classedit<?= "&id={$course_id}&module={$module_id}&class={$class_id}"; ?>"><?= $class_title; ?></a>
                    </p><p class="row">
                        <span class="bar bar_green radius icon-download2"><?= str_pad($class_downloads, 4, 0, 0); ?></span>
                    </p><p class="row views">
                        <a target="_blank" title="Baixar Material" href="<?= BASE; ?>/admin/_sis/teach/courses_downloads_f.php?f=<?= $class_id; ?>" class="btn btn_blue icon-download"><?= substr(strrchr($class_material, "/"), 1); ?></a>
                    </p>
                </div>
                <?php
            endforeach;
            ?>
        </div>
    <?php
    endif;
    ?>
</div>