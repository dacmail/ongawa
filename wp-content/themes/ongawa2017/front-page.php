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

<div class="container">
  <section class="knownus">
    <div class="row">
      <div class="col-md-8 ml-auto mr-auto">
        <h2 class="knownus__title"><?php the_field('knownus_title') ?></h2>
        <div class="knownus__content">
          <?php the_field('knownus_content') ?>
          <?php $link = get_field('knownus_link'); ?>
          <?php if ($link) : ?>
            <a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" class="btn btn-primary"><?php echo $link['title']; ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php if (have_rows('knownus_blocks')) : ?>
      <div class="row justify-content-center">
        <?php while (have_rows('knownus_blocks')): the_row(); ?>
          <div class="col-sm-4">
            <a href="#"><?= Extras\ungrynerd_svg(get_sub_field('knownus_icon')); ?></a>
            <h4 class="knownus__link-title"><?php the_sub_field('knownus_title'); ?></h4>
            <a href="<?php the_sub_field('knownus_link'); ?>" class="knownus__link"><?php esc_html_e('+info', 'ungrynerd'); ?></a>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </section>

  <?php $banner_img = get_field('banner_image'); ?>
  <?php if ($banner_img): ?>
    <section class="banner">
      <a href="<?php the_field('banner_link'); ?>"><img src="<?php echo $banner_img; ?>" alt="Banner"></a>
    </section>
  <?php endif ?>

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
</div>

<?php if (get_field('cta_title')): ?>
  <section class="cta">
    <div class="container">
      <div class="cta__wrap">
        <h2 class="cta__title"><?php the_field('cta_title'); ?></h2>
        <div class="cta__text"><?php the_field('cta_text'); ?></div>
      </div>
      <?php $cta_link = get_field('cta_link'); ?>
      <?php if ($cta_link) : ?>
        <a href="<?php echo $cta_link['url']; ?>" target="<?php echo $cta_link['target']; ?>" class="btn btn-alt"><?php echo $cta_link['title']; ?></a>
      <?php endif; ?>
    </div>
  </section>
<?php endif ?>
