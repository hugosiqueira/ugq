<article class="box box<?= (!empty($BOX) ? $BOX : 3); ?> post_list">
    <a class="post_list_thumb" href="<?= BASE; ?>/artigo/<?= $post_name; ?>" title="<?= $post_title; ?>">
        <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $post_cover; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>" alt="<?= $post_title; ?>" title="<?= $post_title; ?>"/>
    </a><div class="post_list_content">
        <h1><a href="<?= BASE; ?>/artigo/<?= $post_name; ?>" title="<?= $post_title; ?>"><?= $post_title; ?></a></h1>
        <p class="tagline"><?= Check::Chars($post_subtitle, 120); ?></p>
        <a class="link" href="<?= BASE; ?>/artigo/<?= $post_name; ?>" title="<?= $post_title; ?>">IR PARA O POST!</a>
    </div>
</article>