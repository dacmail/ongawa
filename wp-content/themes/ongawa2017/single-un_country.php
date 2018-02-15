<?php use Roots\Sage\Extras; ?>

<section class="container">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class('country'); ?>>
      <?php if (has_post_thumbnail()): ?>
        <?php the_post_thumbnail('full', array('class' => 'country__header')) ?>
      <?php endif ?>
      <div class="country__container">
        <div class="country__content">
          <h1 class="section-title country__content__title"><?php the_title(); ?></h1>
          <?php the_content(); ?>
          <div class="svg-icon-one svg-icon-one-dims"></div>
          <div class="svg-icon-two svg-icon-two-dims"></div>
          <div class="svg-icon-three svg-icon-three-dims"></div>
        </div>
        <aside class="country__aside">
          <div class="country__aside__header">
            <h1 class="country__aside__title"><?php the_title(); ?></h1>
            <?php the_field('country_summary') ?>
          </div>
          <div class="country__aside__map">
            <?php the_field('country_map'); ?>
          </div>
          <div class="country__aside__data">
            <?php the_field('country_data'); ?>
          </div>
          <?php $button = get_field('country_link'); ?>
          <?php if ($button) : ?>
            <a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn-simple country__aside__link"><?php echo $button['title']; ?></a>
          <?php endif; ?>
        </aside>
      </div>

      <?php if (have_rows('country_related')) : ?>
      <section class="country-related">
        <h2 class="section-title"><?php esc_html_e('Actualidad en', 'ungrynerd'); ?> <?php the_title(); ?></h2>
        <div class="country-related__wrap">
          <?php while (have_rows('country_related')): the_row(); ?>
            <artcile class="country-related__item">
              <?php $image = get_sub_field('country_related_img'); ?>
              <a href="<?php the_sub_field('country_related_link'); ?>"><?php echo wp_get_attachment_image($image, 'square', false, array('class' => 'country-related__item__image')); ?></a>
              <div class="country-related__item__wrap">
                <p class="country-related__item__pretitle"><?php the_sub_field('country_related_pretitle'); ?></p>
                <h2 class="country-related__item__title"><a href="<?php the_sub_field('country_related_link'); ?>"><?php the_sub_field('country_related_title'); ?></a></h2>
                <a class="country-related__item__link" href="<?php the_sub_field('country_related_link'); ?>"><?= Extras\ungrynerd_svg('icon-arrow'); ?></a>
              </div>
            </artcile>
          <?php endwhile; ?>
        </div>
      </section>
    <?php endif; ?>
    </article>
  <?php endwhile; ?>
</section>
