<section class="page-wrapper">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class() ?> id="page-<?php the_ID(); ?>">
      <?php if (has_post_thumbnail()): ?>
        <?php the_post_thumbnail('full', array('class' => 'page__header')) ?>
      <?php endif ?>
      <?php if (have_rows('page_menu')) : ?>
        <div class="page__menu">
          <?php while (have_rows('page_menu')): the_row(); ?>
            <?php $button = get_sub_field('page_menu_item'); ?>
              <?php if ($button) : ?>
                <a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="page__menu__item"><?php echo $button['title']; ?></a>
              <?php endif; ?>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
      <div class="page__content container">
        <?php if (have_rows('page_content')) : ?>
          <?php while (have_rows('page_content')) : the_row(); ?>
            <?php get_template_part('templates/page-block', get_row_layout()) ?>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </article>
  <?php endwhile; ?>
</section>
