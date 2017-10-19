<?php use Roots\Sage\Extras; ?>
<?php $banner_img = get_field('banner_image'); ?>
<?php if ($banner_img): ?>
  <section class="banner">
    <a href="<?php the_field('banner_link'); ?>"><img src="<?php echo $banner_img; ?>" alt="Banner"></a>
  </section>
<?php endif ?>
