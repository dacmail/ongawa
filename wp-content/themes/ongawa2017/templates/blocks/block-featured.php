<section class="c-featured <?php echo $block['className'] ?>">
  <?php echo wp_get_attachment_image(get_field('image'), 'slide', false, array('class' => 'c-featured__image')) ?>
  <div class="c-featured__text"><?php the_field('texto') ?></div>
</section>
