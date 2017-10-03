<?php use Roots\Sage\Extras; ?>
<footer class="footer">
  <div class="container">
    <div class="footer__wrap">
      <?php if (has_custom_logo()): ?>
        <?php the_custom_logo(); ?>
      <?php else: ?>
        <a class="header__site-name" href="<?= esc_url(home_url('/')); ?>">
          <?php bloginfo('name'); ?>
        </a>
      <?php endif ?>
      <div class="footer__social">
        <a href="#" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-twitter'); ?></a>
        <a href="#" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-facebook'); ?></a>
        <a href="#" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-youtube'); ?></a>
      </div>
    </div>
    <div class="footer__wrap footer__wrap--top">
      <div class="footer__newsletter">
        <h3>Informate</h3>
        <p>Recibe información sobre lo que hacemos en nuestras diferentes áreas temáticas:</p>
        <form>
          <input type="email" name="">
          <input type="submit" name="" value="<?php esc_html_e('Suscríbete', 'ungrynerd'); ?>">
        </form>
      </div>
      <?php
      if (has_nav_menu('footer_navigation')) :
        wp_nav_menu([
          'theme_location' => 'footer_navigation',
          'menu_class' => 'footer__nav__wrapper',
          'container_id' => '',
          'container_class' => 'footer__nav'
        ]);
      endif;
      ?>
    </div>
    <div class="footer__wrap">
      <?= Extras\ungrynerd_svg('creative-commons'); ?>
      <a href="http://ungrynerd.com" target="_blank" class="footer__by">Hecho por <strong>UNGRYNERD</strong></a>
    </div>
  </div>
</footer>
