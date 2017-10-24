<?php if (is_sticky()): ?>
  <article <?php post_class('blog-list__sticky'); ?>>
    <div class="blog-list__sticky__categories"><?php the_category(' ', ' '); ?></div>
    <h2 class="blog-list__sticky__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php the_post_thumbnail('slide', array('class' => 'blog-list__sticky__thumb')) ?>
  </article>
<?php else : ?>
  <article <?php post_class('blog-list__post'); ?>>
    <div class="blog-list__post__wrap">
      <div class="blog-list__post__categories"><?php the_category(' ', ' '); ?></div>
      <h2 class="blog-list__post__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <div class="blog-list__post__excerpt">
        <?php the_excerpt(); ?>
      </div>
    </div>
    <?php the_post_thumbnail('square', array('class' => 'blog-list__post__thumb')) ?>
  </article>
<?php endif ?>

