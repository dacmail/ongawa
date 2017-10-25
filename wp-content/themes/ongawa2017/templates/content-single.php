<?php use Roots\Sage\Extras; ?>

<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <div class="post__meta">
      <div class="post__meta__categories"><?php the_category(' ', ' '); ?></div>
      <div class="post__meta__share">
        <a target="_blank" href="https://twitter.com/home?status=<?= urlencode(get_the_title()) . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__twitter"><?= Extras\ungrynerd_svg('icon-twitter'); ?></a>
        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(get_permalink()); ?>" class="post__meta__share__facebook"><?= Extras\ungrynerd_svg('icon-facebook'); ?></a>
        <a target="_blank" href="https://api.whatsapp.com/send?text=<?= get_the_title() . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__whatsapp"><?= Extras\ungrynerd_svg('icon-whatsapp'); ?></a>
      </div>
    </div>
    <?php the_post_thumbnail('slide', array('class' => 'post__thumb')) ?>
    <h1 class="post__title"><?php the_title(); ?></h1>
    <div class="post__meta post__meta--author">
      <p class="post__meta__by"><a href="<?= get_author_posts_url(get_the_author_meta('ID')); ?>" rel="author"><?= get_the_author(); ?></a></p>
      <time class="post__meta__date"><?php the_time(get_option('date_format')); ?></time>
    </div>
    <div class="post__content">
      <?php the_content(); ?>
    </div>
    <div class="post__meta post__meta--footer">
      <div class="post__meta__tags"><?php the_tags(' ', ' '); ?></div>
      <div class="post__meta__share">
        <a target="_blank" href="https://twitter.com/home?status=<?= urlencode(get_the_title()) . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__twitter"><?= Extras\ungrynerd_svg('icon-twitter'); ?></a>
        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(get_permalink()); ?>" class="post__meta__share__facebook"><?= Extras\ungrynerd_svg('icon-facebook'); ?></a>
        <a target="_blank" href="https://api.whatsapp.com/send?text=<?= get_the_title() . ' ' . urlencode(get_permalink()); ?>" class="post__meta__share__whatsapp"><?= Extras\ungrynerd_svg('icon-whatsapp'); ?></a>
      </div>
    </div>
  </article>
<?php endwhile; ?>
