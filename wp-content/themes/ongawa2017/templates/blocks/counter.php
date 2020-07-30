<section class="c-counters">
  <?php while (have_rows('counter')) : the_row() ?>
    <div class="c-counter">
      <span class="c-counter__number"><?php the_sub_field('number') ?></span>
      <span class="c-counter__label"><?php the_sub_field('label') ?></span>
      <span class="c-counter__text"><?php the_sub_field('text') ?></span>
    </div>
  <?php endwhile; ?>
</section>
