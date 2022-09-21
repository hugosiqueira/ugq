<?php
if (!ACC_MANAGER):
    require REQUIRE_PATH . '/404.php';
else:
    ?>
    <div class="container user_account" id="acc">
        <div class="content" style="padding: 40px 0 50px 0;">
            <?php require '_cdn/widgets/account/account.php'; ?>
            <div class="clear"></div>
        </div>
    </div>
<?php
endif;