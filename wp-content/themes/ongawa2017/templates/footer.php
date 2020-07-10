<?php use Roots\Sage\Extras; ?>
<?php get_template_part('templates/block-cta'); ?>
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
        <a href="https://twitter.com/ongawa4d" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-twitter'); ?></a>
        <a href="https://www.facebook.com/ongawa4d" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-facebook'); ?></a>
        <a href="https://www.youtube.com/user/isfapd" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-youtube'); ?></a>
        <a href="https://www.instagram.com/ongawa4d/" target="_blank" class="footer__social__link"><?= Extras\ungrynerd_svg('icon-instagram'); ?></a>
      </div>
    </div>
    <div class="footer__wrap footer__wrap--top">
      <div class="footer__newsletter">
        <h3>Informate</h3>
        <p>Recibe información sobre lo que hacemos en nuestras diferentes áreas temáticas:</p>
        <form action="https://ongawa.us2.list-manage.com/subscribe/post?u=83debf66e351d8f0e766b642a&amp;id=391ce2ed39" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
          <input placeholder="@ correo electrónico" type="email" name="EMAIL">
          <input type="submit" name="subscribe" value="<?php esc_html_e('Suscríbete', 'ungrynerd'); ?>">
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
