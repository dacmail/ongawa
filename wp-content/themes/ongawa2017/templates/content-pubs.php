<article <?php post_class('pubs__pub'); ?>>
  <div class="pubs__pub__wrapper">
    <?php the_post_thumbnail('big', array('class' => 'pubs__pub__thumb')) ?>
    <div class="pubs__pub__wrap">
      <div class="pubs__pub__date"><?php the_field('doc_date'); ?></div>
      <h2 class="pubs__pub__title"><?php the_title(); ?></h2>
      <a class="pubs__pub__view btn-fill" href="<?php the_permalink(); ?>"><?php esc_html_e('Ver', 'ungrynerd'); ?></a>
    </div>
  </div>
  <a download class="pubs__pub__download btn-fill" href="<?php echo wp_get_attachment_url(get_field('doc_file')); ?>"><?php esc_html_e('Descargar', 'ungrynerd'); ?></a>
  <div class="pubs__pub__share">
    <?php get_template_part('templates/share') ?>
  </div>
</article>

