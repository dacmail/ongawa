<?php use Roots\Sage\Extras; ?>
<?php if (have_rows('slide')) : ?>
  <section class="slides owl-carousel">
  <?php while (have_rows('slide')): the_row(); ?>
    <artcile class="slides__slide">
      <?php $image = get_sub_field('slide_image'); ?>
      <?php echo wp_get_attachment_image($image["ID"], 'slide'); ?>
      <div class="slides__slide__wrap">
        <h2 class="slides__slide__title"><?php the_sub_field('slide_title'); ?></h2>
        <p class="slides__slide__text"><?php the_sub_field('slide_text'); ?></p>
        <a class="slides__slide__link" href="<?php the_sub_field('slide_link'); ?>"><?= Extras\ungrynerd_svg('icon-arrow'); ?></a>
      </div>
    </artcile>
  <?php endwhile; ?>
  </section>
<?php endif; ?>
