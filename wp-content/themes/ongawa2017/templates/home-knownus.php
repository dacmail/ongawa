<?php use Roots\Sage\Extras; ?>
<section class="knownus">
  <div class="row">
    <div class="col-lg-8 ml-auto mr-auto">
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
          <a href="<?php the_sub_field('knownus_link'); ?>"><?= Extras\ungrynerd_svg(get_sub_field('knownus_icon')); ?></a>
          <h4 class="knownus__link-title"><?php the_sub_field('knownus_title'); ?></h4>
          <a href="<?php the_sub_field('knownus_link'); ?>" class="knownus__link"><?php esc_html_e('+info', 'ungrynerd'); ?></a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>
