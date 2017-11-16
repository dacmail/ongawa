<?php use Roots\Sage\Extras; ?>

<section class="container">
  <div class="pub__back"><a href="<?php echo get_post_type_archive_link('un_doc') ?>" class="btn-fill">Volver a publicaciones <?= Extras\ungrynerd_svg('icon-back'); ?></a></div>
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class('pub'); ?>>
      <div class="pub__meta">
        <?php the_post_thumbnail('big', array('class' => 'pub__thumb')) ?>
        <a download class="pub__download btn-fill" href="<?php echo wp_get_attachment_url(get_field('doc_file')); ?>"><?php esc_html_e('Descargar', 'ungrynerd'); ?></a>
        <div class="pub__share share-buttons">
          <?php get_template_part('templates/share') ?>
        </div>
      </div>
      <div class="pub__data">
        <div class="pub__cats"><?php the_terms(get_the_ID(), 'un_cat', '', ' ') ?></div>
        <h2 class="pub__title"><?php the_title(); ?></h2>
        <div class="pub__date"><?php the_field('doc_date'); ?></div>
        <div class="pub__author"><?php the_field('doc_author'); ?></div>
        <?php the_content(); ?>
      </div>

    </article>
  <?php endwhile; ?>
</section>
