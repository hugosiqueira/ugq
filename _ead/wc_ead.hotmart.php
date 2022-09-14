<?php

require '../_app/Config.inc.php';

header("access-control-allow-origin: https://app-vlc.hotmart.com");
header('Content-Type: text/html; charset=UTF-8');

//GET HOTMART POST
$HotmartSale = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//LOG GENERATE
if (EAD_HOTMART_LOG && !empty($HotmartSale)):
    $HotmartLog = null;
    foreach ($HotmartSale as $key => $value):
        $HotmartLog .= "{$key}: {$value}\r\n";
    endforeach;

    $HotmartLogFile = fopen('wc_ead.hotmart.txt', 'a');
    fwrite($HotmartLogFile, "\r\n########## " . date('d/m/Y H\hi') . " ##########\r\n\r\n" . $HotmartLog);
    fclose($HotmartLogFile);
endif;

if ($HotmartSale && !empty($HotmartSale['hottok']) && $HotmartSale['hottok'] == EAD_HOTMART_TOKEN):
    //CLEAR DATA
    array_map('strip_tags', $HotmartSale);
    array_map('trim', $HotmartSale);
    array_map('rtrim', $HotmartSale);

    //GET HOTMART TRANSACTION
    $HotmartTransaction = (!empty($HotmartSale['transaction_ext']) ? $HotmartSale['transaction_ext'] : $HotmartSale['transaction']);

    //PRODUCT NEGATIVATE
    if (!empty(EAD_HOTMART_NEGATIVATE)):
        $NegativateProductsExmplode = explode(',', EAD_HOTMART_NEGATIVATE);
        $NegativateProductsTrim = array_map('trim', $NegativateProductsExmplode);
        $NegativateProducts = array_map('rtrim', $NegativateProductsTrim);

        if (in_array($HotmartSale['prod'], $NegativateProducts)):
            exit;
        endif;
    endif;

    //START CRUD
    $Create = new Create;
    $Read = new Read;
    $Update = new Update;
    $Delete = new Delete;

    //GET COURSE :: EAD
    $Read->ExeRead(DB_EAD_COURSES, " WHERE course_vendor_id = :prod", "prod={$HotmartSale['prod']}");
    $OrderCourse = ($Read->getResult() ? $Read->getResult()[0] : null);

    //GET USER BY DOCUMENT
    $GetUserByDocument = null;
    $HotmartSale['doc'] = (!empty($HotmartSale['doc']) && Check::CPF($HotmartSale['doc']) ? substr($HotmartSale['doc'], 0, 3) . "." . substr($HotmartSale['doc'], 3, 3) . "." . substr($HotmartSale['doc'], 6, 3) . "-" . substr($HotmartSale['doc'], 9, 2) : null);
    if ($HotmartSale['doc']):
        $Read->ExeRead(DB_USERS, "WHERE user_document = :documet", "documet={$HotmartSale['doc']}");
        $GetUserByDocument = ($Read->getResult() ? $Read->getResult()[0] : null);
    endif;

    //READ BY EMAIL
    $Read->ExeRead(DB_USERS, "WHERE user_email = :email", "email={$HotmartSale['email']}");
    $GetUserByEmail = ($Read->getResult() ? $Read->getResult()[0] : null);

    if ($GetUserByDocument):
        $OrderUser = $GetUserByDocument;
    elseif ($GetUserByEmail):
        $OrderUser = $GetUserByEmail;
    else:
        //CREATE NEW USER
        $CreateNewUserPassword = Check::NewPass(10);
        $CreateNewUser = [
            'user_name' => (!empty($HotmartSale['first_name']) ? $HotmartSale['first_name'] : explode(" ", $HotmartSale['name'])[0]),
            'user_lastname' => (!empty($HotmartSale['last_name']) ? $HotmartSale['last_name'] : (!empty(explode(" ", $HotmartSale['name'])[2]) ? explode(" ", $HotmartSale['name'])[2] : (!empty(explode(" ", $HotmartSale['name'])[1]) ? explode(" ", $HotmartSale['name'])[1] : null))),
            'user_document' => (!empty($HotmartSale['doc']) ? $HotmartSale['doc'] : null),
            'user_telephone' => "({$HotmartSale['phone_local_code']}) {{$HotmartSale['phone_number']}}",
            'user_email' => $HotmartSale['email'],
            'user_password' => hash('sha512', $CreateNewUserPassword),
            'user_channel' => 'Hotmart',
            'user_registration' => date("Y-m-d H:i:s"),
            'user_level' => 1
        ];
        $Create->ExeCreate(DB_USERS, $CreateNewUser);
        $OrderUser['user_id'] = $Create->getResult();

        //REGISTER ADDR
        $RegisterNewUserAddr = [
            'user_id' => $OrderUser['user_id'],
            'addr_key' => 1,
            'addr_name' => "Meu Endereço",
            'addr_zipcode' => $HotmartSale['address_zip_code'],
            'addr_street' => $HotmartSale['address'],
            'addr_number' => $HotmartSale['address_number'],
            'addr_complement' => $HotmartSale['address_comp'],
            'addr_district' => $HotmartSale['address_district'],
            'addr_city' => $HotmartSale['address_city'],
            'addr_state' => $HotmartSale['address_state'],
            'addr_country' => $HotmartSale['address_country']
        ];
        $Create->ExeCreate(DB_USERS_ADDR, $RegisterNewUserAddr);

        $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$OrderUser['user_id']}");
        $OrderUser = $Read->getResult()[0];

        //SEND ACCOUNT MAIL
        require './wc_ead.email.php';
        $MailBody = "
            <p>Olá {$OrderUser['user_name']} {$OrderUser['user_lastname']},</p>
            <p>Identificamos seu pedido para o <b>{$HotmartSale['prod_name']}</b>, e sua conta foi criada em nossa plataforma!</p>
            <p>Seja muito bem-vindo(a)!</p>
            <p><b>*IMPORTANTE:</b> A compra é processada pela Hotmart, e assim que o pagamento for aprovado você receberá (ou já recebeu) outro e-mail avisando sobre a liberação do pedido. Assim que receber basta acessar sua conta!</p>
            <p><b>ACESSE SUA CONTA:</b></p>
            <p>
                <b>E-mail:</b> {$OrderUser['user_email']}<br>
                <b>Senha:</b> {$CreateNewUserPassword}
            </p>
            <p>{$OrderUser['user_name']}, ao acessar sua conta aproveite para alterar sua senha e completar seu perfil, enviando sua foto e outros dados :)</p>
            <p><a target='_blank' href='" . BASE . "/campus' title='Acessar Minha Conta Agora!'>➜ ACESSAR MINHA CONTA AGORA!</a></p>
            <p>...</p>
            <p><b>PRECISA DE AJUDA?</b></p>
            <p>Não deixe de encaminhar um e-mail para " . SITE_ADDR_EMAIL . ". Nossa equipe esta de prontidão para atender sua demanda!</p>
            <p><em>Atenciosamente " . SITE_NAME . "!</em></p>
        ";
        $MailContent = str_replace("#mail_body#", $MailBody, $MailContent);
        $Email = new Email;
        $Email->EnviarMontando("Seja muito bem-vindo(a) {$OrderUser['user_name']}!", $MailContent, MAIL_SENDER, MAIL_USER, "{$OrderUser['user_name']} {$OrderUser['user_lastname']}", $OrderUser['user_email']);
    endif;

    //GET ORDER :: STUDENT
    $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_transaction = :trans AND user_id = :user", "trans={$HotmartTransaction}&user={$OrderUser['user_id']}");
    if ($Read->getResult()):
        $OrderSale = $Read->getResult()[0];
    else:
        $CreateNewOrder = [
            'user_id' => $OrderUser['user_id'],
            'course_id' => $OrderCourse['course_id'],
            'order_product_id' => $HotmartSale['prod'],
            'order_transaction' => $HotmartTransaction,
            'order_payment_type' => $HotmartSale['payment_type'],
            'order_purchase_date' => date('Y-m-d H:i:s', strtotime($HotmartSale['purchase_date'])),
            'order_warranty_date' => date('Y-m-d H:i:s', strtotime($HotmartSale['warranty_date'])),
            'order_confirmation_purchase_date' => date('Y-m-d H:i:s', strtotime($HotmartSale['confirmation_purchase_date'])),
            'order_callback_type' => $HotmartSale['callback_type'],
            'order_aff' => (!empty($HotmartSale['aff']) ? $HotmartSale['aff'] : null),
            'order_aff_name' => (!empty($HotmartSale['aff_name']) ? $HotmartSale['aff_name'] : SITE_NAME),
            'order_cms_aff' => (!empty($HotmartSale['cms_aff']) ? $HotmartSale['cms_aff'] : '0.00'),
            'order_cms_marketplace' => $HotmartSale['cms_marketplace'],
            'order_cms_vendor' => $HotmartSale['cms_vendor'],
            'order_off' => $HotmartSale['off'],
            'order_price' => $HotmartSale['price'],
            'order_currency' => $HotmartSale['currency'],
            'order_sck' => (!empty($HotmartSale['sck']) ? $HotmartSale['sck'] : null),
            'order_src' => (!empty($HotmartSale['src']) ? $HotmartSale['src'] : null),
            'order_status' => $HotmartSale['status']
        ];

        if (!empty($HotmartSale['subscriber_code'])):
            $CreateNewOrder['order_signature'] = $HotmartSale['subscriber_code'];
            $CreateNewOrder['order_signature_plan'] = $HotmartSale['name_subscription_plan'];
            $CreateNewOrder['order_signature_recurrency'] = $HotmartSale['recurrency'];
            $CreateNewOrder['order_signature_period'] = $HotmartSale['recurrency_period'];
            $CreateNewOrder['order_signature_status'] = $HotmartSale['subscription_status'];
        endif;

        $Create->ExeCreate(DB_EAD_ORDERS, $CreateNewOrder);
        $OrderSale['order_id'] = $Create->getResult();

        $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
        $OrderSale = $Read->getResult()[0];
    endif;

    //TRANSACTION VENDOR :: ACTIONS
    switch ($HotmartSale['status']):
        case 'started':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'started', 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'billet_printed':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'billet_printed', 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'pending_analysis':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'pending_analysis', 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'delayed':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'delayed', 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'canceled':
        case 'dispute':
            //CANCEL ACCESS WHERE SIGNATURE
            if ($OrderSale['order_signature'] && !empty($HotmartSale['subscription_status'])):
                $UpdateOrderStatus = ['order_status' => 'canceled', 'order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s"), 'order_signature_status' => $HotmartSale['subscription_status']];
                $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");

                $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s")];
                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE user_id = :user AND course_id = :course", "user={$OrderSale['user_id']}&course={$OrderSale['course_id']}");
            else:
                //UPDATE ORDER STATUS
                $UpdateOrderStatus = ['order_status' => 'canceled', 'order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
                $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");

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
                $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$OrderSale['order_id']}");

                //UPDATE RESET DELIVERED :: ACCESS ROLLBACK
                $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user AND course_id = :course", "user={$OrderUser['user_id']}&course={$OrderCourse['course_id']}");
                if (!empty($OrderSale['order_delivered']) && $OrderCourse['course_vendor_access'] && $Read->getResult()):
                    $UpdateEnrolmentCanceled = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['enrollment_end'] . "-{$OrderCourse['course_vendor_access']}months"))];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrolmentCanceled, "WHERE enrollment_id = :enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                endif;
            endif;
            break;

        case 'approved':
        case 'completed':
            //ENROLLMENT CREATE, UPDATE
            $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user AND course_id = :course", "user={$OrderUser['user_id']}&course={$OrderCourse['course_id']}");
            if (!$Read->getResult()):
                $CreateEnrollment = [
                    'user_id' => $OrderUser['user_id'],
                    'course_id' => $OrderCourse['course_id'],
                    'enrollment_order' => $OrderSale['order_id'],
                    'enrollment_start' => date('Y-m-d H:i:s'),
                    'enrollment_access' => null,
                    'enrollment_end' => (!empty($HotmartSale['recurrency_period']) ? date("Y-m-d H:i:s", strtotime("+{$HotmartSale['recurrency_period']}days")) : ($OrderCourse['course_vendor_access'] ? date('Y-m-d H:i:s', strtotime("+{$OrderCourse['course_vendor_access']}months")) : null))
                ];
                $Create->ExeCreate(DB_EAD_ENROLLMENTS, $CreateEnrollment);
            elseif (empty($OrderSale['order_delivered'])):
                if (!empty($HotmartSale['recurrency_period'])):
                    $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s", strtotime("+{$HotmartSale['recurrency_period']}days"))];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                elseif (!$OrderCourse['course_vendor_access']):
                    $UpdateEnrollment = ['enrollment_end' => null];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                else:
                    $DateThis = date("Y-m-d H:i:s");
                    $DateAccess = $Read->getResult()[0]['enrollment_end'];
                    $EnrollmentDateaEnd = ($DateAccess > $DateThis ? $DateAccess : $DateThis);

                    $UpdateEnrollment = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($EnrollmentDateaEnd . "+{$OrderCourse['course_vendor_access']}months")), "enrollment_bonus" => null];
                    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollment, "WHERE enrollment_id = :enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
                endif;
            endif;

            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'approved', 'order_delivered' => 1, 'order_confirmation_purchase_date' => date('Y-m-d H:i:s', strtotime($HotmartSale['confirmation_purchase_date']))];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'chargeback':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'chargeback', 'order_chargeback' => date("Y-m-d H:i:s"), 'order_confirmation_purchase_date' => date('Y-m-d H:i:s', strtotime($HotmartSale['confirmation_purchase_date']))];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'blocked':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'blocked', 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");
            break;

        case 'refunded':
            //UPDATE ORDER STATUS
            $UpdateOrderStatus = ['order_status' => 'refunded', 'order_delivered' => null, 'order_confirmation_purchase_date' => date("Y-m-d H:i:s")];
            $Update->ExeUpdate(DB_EAD_ORDERS, $UpdateOrderStatus, "WHERE order_id = :order", "order={$OrderSale['order_id']}");

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
            $Delete->ExeDelete(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$OrderSale['order_id']}");

            //UPDATE RESET DELIVERED :: ACCESS ROLLBACK
            $Read->FullRead("SELECT enrollment_id, enrollment_end FROM " . DB_EAD_ENROLLMENTS . " WHERE user_id = :user AND course_id = :course", "user={$OrderUser['user_id']}&course={$OrderCourse['course_id']}");
            if (!empty($OrderSale['order_delivered']) && $OrderCourse['course_vendor_access'] && $Read->getResult()):
                $UpdateEnrolmentCanceled = ['enrollment_end' => date("Y-m-d H:i:s", strtotime($Read->getResult()[0]['enrollment_end'] . "-{$OrderCourse['course_vendor_access']}months"))];
                $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrolmentCanceled, "WHERE enrollment_id = :enrol", "enrol={$Read->getResult()[0]['enrollment_id']}");
            endif;
            break;
    endswitch;
endif;