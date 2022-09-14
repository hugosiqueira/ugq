<ul class="main_nav_social">
    <?php if (SITE_SOCIAL_FB): ?><li><a target="_blank" title="<?= SITE_NAME; ?> no Facebook" href="https://www.facebook.com/<?= SITE_SOCIAL_FB_PAGE; ?>"><img alt="<?= SITE_NAME; ?> no Facebook" title="<?= SITE_NAME; ?> no Facebook" src="<?= INCLUDE_PATH; ?>/images/icons/facebook.png"/></a></li><?php
    endif;
    if (SITE_SOCIAL_YOUTUBE):
        ?><li><a target="_blank" title="<?= SITE_NAME; ?> no YouTube" href="https://www.youtube.com/user/<?= SITE_SOCIAL_YOUTUBE; ?>?sub_confirmation=1"><img alt="<?= SITE_NAME; ?> no YouTube" title="<?= SITE_NAME; ?> no YouTube" src="<?= INCLUDE_PATH; ?>/images/icons/youtube.png"/></a></li><?php
    endif;
    if (SITE_SOCIAL_INSTAGRAM):
        ?><li><a target="_blank" title="<?= SITE_NAME; ?> no Instagram" href="https://www.instagram.com/<?= SITE_SOCIAL_INSTAGRAM; ?>"><img alt="<?= SITE_NAME; ?> no Instagram" title="<?= SITE_NAME; ?> no Instagram" src="<?= INCLUDE_PATH; ?>/images/icons/instagram.png"/></a></li><?php
    endif;
    if (SITE_SOCIAL_SNAPCHAT):
        ?><li><a target="_blank" title="<?= SITE_NAME; ?> no Snapchat" href="https://www.snapchat.com/add/<?= SITE_SOCIAL_SNAPCHAT; ?>"><img alt="<?= SITE_NAME; ?> no Snapchat" title="<?= SITE_NAME; ?> no Snapchat" src="<?= INCLUDE_PATH; ?>/images/icons/snapchat.png"/></a></li><?php endif; ?>
</ul>