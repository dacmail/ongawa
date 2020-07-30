<section class="c-cta <?php echo $block['className'] ?>">
  <?php echo wp_get_attachment_image(get_field('image'), 'slide', false, array('class' => 'c-cta__image')) ?>
  <div class="c-cta__text" style="background-color: rgba(255,255,255,<?php the_field('bgcolor') ?>);">
    <?php the_field('texto') ?>
    <?php $button = get_field('link'); ?>
    <?php if ($button) : ?>
      <a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn btn-primary">
        <?php echo $button['title']; ?>
      </a>
    <?php endif; ?>
  </div>

</section>
