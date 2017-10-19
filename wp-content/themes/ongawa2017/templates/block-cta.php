<?php if (get_field('cta_title', get_option('page_on_front'))): ?>
  <section class="cta">
    <div class="container">
      <div class="cta__wrap">
        <h2 class="cta__title"><?php the_field('cta_title', get_option('page_on_front')); ?></h2>
        <div class="cta__text"><?php the_field('cta_text', get_option('page_on_front')); ?></div>
      </div>
      <?php $cta_link = get_field('cta_link', get_option('page_on_front')); ?>
      <?php if ($cta_link) : ?>
        <a href="<?php echo $cta_link['url']; ?>" target="<?php echo $cta_link['target']; ?>" class="btn btn-alt"><?php echo $cta_link['title']; ?></a>
      <?php endif; ?>
    </div>
  </section>
<?php endif ?>
