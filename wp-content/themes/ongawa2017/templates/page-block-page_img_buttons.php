<?php if (have_rows('page_content_img_buttons')) : ?>
  <div class="page__content__buttons page__content__buttons--image">
    <?php while (have_rows('page_content_img_buttons')): the_row(); ?>
      <?php $button = get_sub_field('page_content_img_button_link'); ?>
        <?php if ($button) : ?>
          <a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn btn-image">
            <?php echo wp_get_attachment_image(get_sub_field('page_content_img_button'), 'square') ?>
            <div class="btn-image-text"><?php echo $button['title']; ?></div>
          </a>
        <?php endif; ?>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
