<?php
$AdminLevel = LEVEL_WC_EAD_STUDENTS;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$Search = filter_input_array(INPUT_POST);
$SearchClause = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$WhereClause = null;
if ($SearchClause):
    $S = strip_tags($SearchClause);
    $WhereClause = "AND (user_email = '{$S}' OR user_name LIKE '%{$S}%' OR user_lastname LIKE '%{$S}%' OR concat_ws(' ', user_name, user_lastname) LIKE '%{$S}%' OR user_id = '{$S}')";
endif;

$CourseClause = filter_input(INPUT_GET, 'course', FILTER_VALIDATE_INT);
$WhereCourse = null;
if ($CourseClause):
    $WhereCourse = "AND user_id IN (SELECT user_id FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = {$CourseClause})";
endif;

if ($Search && $Search['s']):
    $S = urlencode($Search['s']);
    header("Location: dashboard.php?wc=teach/students&s={$S}&course={$CourseClause}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-user-check">Alunos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <?php if (!$CourseClause && !$SearchClause): ?>
                <span class="crumb">/</span>
                Alunos
            <?php else: ?>
                <span class="crumb">/</span>
                <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students">Alunos</a>
                <span class="crumb">/</span>
                Encontrar Aluno
            <?php endif; ?>
        </p>
    </div>

    <div class="dashboard_header_search">
        <form style="width: 80%; display: inline-block" name="searchUsers" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <input type="search" name="s" placeholder="Pesquisar Aluno:" required/>
            <button class="btn btn_blue icon icon-search icon-notext"></button>
        </form>
        <a title="Cadastrar Aluno!" href="dashboard.php?wc=teach/students_gerent" class="wc_view btn btn_green icon-user-plus icon-notext"></a>
    </div>
</header>
<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Pager = new Pager("dashboard.php?wc=teach/students&s={$SearchClause}&course={$CourseClause}&page=", "<<", ">>", 5);
    $Pager->ExePager($Page, 12);
    $Read->ExeRead(DB_USERS, "WHERE 1 = 1 {$WhereClause} {$WhereCourse} ORDER BY user_name ASC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
    if (!$Read->getResult()):
        $Pager->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem usuários cadastrados {$Admin['user_name']}. Comece agora mesmo cadastrando um novo usuário. Ou aguarde novos clientes!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Users):
            extract($Users);

            $Read->FullRead("SELECT count(enrollment_id) as TotalEnrollment FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :id", "id={$user_id}");
            $EnrollmentCount = $Read->getResult()[0]['TotalEnrollment'];

            $user_name = ($user_name ? $user_name : 'Novo');
            $user_lastname = ($user_lastname ? $user_lastname : 'Aluno');
            $UserThumb = "../uploads/{$user_thumb}";
            $user_thumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');
            echo "<article class='single_user box box25 al_center'>
                    <div class='box_content wc_normalize_height'>
                        <img alt='Este é {$user_name}' title='Este é {$user_name}' src='../tim.php?src={$user_thumb}&w=400&h=400'/>
                        <h1>{$user_name} {$user_lastname}</h1>
                        <p class='nivel icon-lab'>MATRICULAD" . ($user_genre == 1 ? 'O' : 'A') . " EM " . str_pad($EnrollmentCount, 2, 0, 0) . " CURSOS</p>
                        <p class='info icon-envelop'>{$user_email}</p>
                        <p class='info icon-calendar'>Desde " . date('d/m/Y \a\s H\h\si', strtotime($user_registration)) . "</p>
                    </div>
                    <div class='single_user_actions'>
                        <a class='btn btn_green icon-user-check' href='dashboard.php?wc=teach/students_gerent&id={$user_id}' title='Gerenciar Usuário!'>Gerenciar Alun" . ($user_genre == 2 ? 'a' : 'o') . "!</a>
                    </div>
                </article>";
        endforeach;
        $Pager->ExePaginator(DB_USERS, "WHERE 1 = 1 {$WhereClause} {$WhereCourse}");
        echo $Pager->getPaginator();
    endif;

    $Read->FullRead("SELECT user_name, user_lastname, user_email FROM " . DB_USERS . " WHERE 1 = 1 {$WhereClause} {$WhereCourse}");
    if ($Read->getResult()):
        $LookALike = filter_input(INPUT_GET, 'look', FILTER_VALIDATE_BOOLEAN);
        if ($LookALike):
            $LookGenerate = "";
            foreach ($Read->getResult() as $Look):
                $LookGenerate .= "{$Look['user_name']} {$Look['user_lastname']}, {$Look['user_email']}\r\n";
            endforeach;

            $ZipLook = new ZipArchive;
            $ZipLook->open("../uploads/look_a_like.zip", ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
            $ZipLook->addFromString('look_a_like_' . date('d_m_Y') . '.txt', $LookGenerate);
            $ZipLook->close();

            header("Location: " . BASE . "/uploads/look_a_like.zip");
            exit;
        endif;
        echo "<div class='clear'></div><div class='student_content_look'><a href='dashboard.php?wc=teach/students&s={$SearchClause}&course={$CourseClause}&page={$Page}&look=true' class='icon-download'>GERAR LISTA DE E-MAILS ( " . str_pad($Read->getRowCount(), 4, 0, 0) . " ALUNOS )</a></div>";
    endif;

    if (file_exists("../uploads/look_a_like.zip") && empty($LookALike)):
        unlink("../uploads/look_a_like.zip");
    endif;
    ?>
</div>