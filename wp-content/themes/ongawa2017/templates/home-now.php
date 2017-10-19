<?php use Roots\Sage\Extras; ?>

<?php if (have_rows('now')) : ?>
  <section class="now">
    <h2 class="section-title"><?php esc_html_e('Actualidad', 'ungrynerd'); ?></h2>
    <div class="now__wrap">
      <?php while (have_rows('now')): the_row(); ?>
        <artcile class="now__item">
          <?php $image = get_sub_field('now_image'); ?>
          <a href="<?php the_sub_field('now_link'); ?>"><?php echo wp_get_attachment_image($image["ID"], 'full', false, array('class' => 'now__item__image')); ?></a>
          <div class="now__item__wrap">
            <p class="now__item__pretitle"><?php the_sub_field('now_pretitle'); ?></p>
            <h2 class="now__item__title"><a href="<?php the_sub_field('now_link'); ?>"><?php the_sub_field('now_title'); ?></a></h2>
            <a class="now__item__link" href="<?php the_sub_field('now_link'); ?>"><?= Extras\ungrynerd_svg('icon-arrow'); ?></a>
          </div>
        </artcile>
      <?php endwhile; ?>
    </div>
  </section>
<?php endif; ?>
