<?php if (have_rows('page_content_buttons')) : ?>
  <div class="page__content__buttons">
    <?php while (have_rows('page_content_buttons')): the_row(); ?>
      <?php $button = get_sub_field('page_content_button'); ?>
        <?php if ($button) : ?>
          <a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn btn-simple"><?php echo $button['title']; ?></a>
        <?php endif; ?>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
