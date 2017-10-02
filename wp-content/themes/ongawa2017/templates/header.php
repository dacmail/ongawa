<header class="header sticky-top">
<nav class="menu nav-primary">
  <div class="container">
    <div class="navbar navbar-expand-md justify-content-between">
      <?php if (has_custom_logo()): ?>
        <?php the_custom_logo(); ?>
      <?php else: ?>
        <a class="header__site-name" href="<?= esc_url(home_url('/')); ?>">
          <?php bloginfo('name'); ?>
        </a>
      <?php endif ?>
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#primary-nav" aria-controls="primary-nav" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon">
          &#9776;
        </span>
      </button>
      <?php
      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu([
          'theme_location' => 'primary_navigation',
          'menu_class' => 'nav navbar-nav ml-auto',
          'container_id' => 'primary-nav',
          'container_class' => 'collapse navbar-collapse navbar-right'
        ]);
      endif;
      ?>
    </div>
  </div>
</nav>
</header>
