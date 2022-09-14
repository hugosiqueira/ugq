<?php
if (empty($_SESSION['userLogin'])):
    header("Location: " . BASE . "/campus");
endif;

$CertificateKey = (!empty($URL[2]) ? strip_tags($URL[2]) : null);
if (empty($CertificateKey)):
    $_SESSION['wc_ead_alert'] = ["red", "Erro ao acessar {$_SESSION['userLogin']['user_name']},", "É preciso informar a chave de autenticação do certificado!"];
    header("Location: " . BASE . "/campus");
    exit;
else:
    if (!$Read):
        $Read = new Read;
    endif;

    $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user AND certificate_key = :key AND certificate_status = 1", "user={$_SESSION['userLogin']['user_id']}&key={$CertificateKey}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ["red", "Erro ao acessar {$_SESSION['userLogin']['user_name']},", "O certificado solicitado não existe ou é restrito!"];
        header("Location: " . BASE . "/campus");
    else:
        extract($Read->getResult()[0]);

        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :course", "course={$course_id}");
        extract($Read->getResult()[0]);
        ?>
        <div class="wc_ead_certificate">
            <img alt="Certificado do Curso <?= $course_title; ?>" title="Certificado do Curso <?= $course_title; ?>" src="<?= BASE; ?>/uploads/<?= $course_certification_mockup; ?>"/>
            <p class="wc_ead_certificate_name"><?= "{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}"; ?></p>
            <p class="wc_ead_certificate_course">Curso <?= $course_title; ?></p>
            <p class="wc_ead_certificate_document">CPF: <?= $_SESSION['userLogin']['user_document']; ?></p>
            <p class="wc_ead_certificate_workload">Carga Horária: <?= $course_certification_workload; ?> Horas</p>
        </div>

        <div class="wc_ead_certificate_overload">
            <div class="wc_ead_certificate_overload_box">
                <img src="<?= BASE; ?>/_ead/images/load_w.gif" alt="Emitindo Certificado" title="Emitindo Certificado!"/>
                <p>Validando Certificado, Aguarde!</p>
            </div>
        </div>

        <script src="<?= BASE; ?>/_cdn/jspdf.min.js"></script>
        <script>
            $(function () {
                var pdf = new jsPDF({
                    orientation: 'landscape'
                });
                pdf.addHTML($('.wc_ead_certificate'), function () {
                    //pdf.output('datauri');
                    pdf.save("certificado-<?= $course_name; ?>.pdf");

                    $('.wc_ead_certificate_overload_box img').fadeOut(function(){
                        $('.wc_ead_certificate_overload_box p').html("Certificado Emitido com sucesso!");
                    });
                    //window.close();
                    //window.history.back();
                });
            });
        </script>
    <?php
    endif;
endif;