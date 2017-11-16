<?php use Roots\Sage\Extras; ?>

<a target="_blank" href="https://twitter.com/home?status=<?= urlencode(get_the_title()) . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__twitter"><?= Extras\ungrynerd_svg('icon-twitter'); ?></a>
<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(get_permalink()); ?>" class="post__meta__share__facebook"><?= Extras\ungrynerd_svg('icon-facebook'); ?></a>
<a target="_blank" href="https://api.whatsapp.com/send?text=<?= get_the_title() . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__whatsapp"><?= Extras\ungrynerd_svg('icon-whatsapp'); ?></a>
