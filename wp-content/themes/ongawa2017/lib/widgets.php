<?php
class UN_Newsletter_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'newsletter_widget',
      'description' => 'Formulario de newsletter',
    );
    parent::__construct( 'newsletter_widget', 'Formulario Newsletter', $widget_ops );
  }
  
  public function form( $instance ) {
   
  }

  public function widget( $args, $instance ) {
    echo $args['before_widget'];
    echo $args['before_title'] . apply_filters( 'widget_title', 'Infórmate' ) . $args['after_title'];
    echo '<p>Recibe información sobre lo que hacemos en nuestras diferentes áreas temáticas:</p>
        <form action="https://ongawa.us2.list-manage.com/subscribe/post?u=83debf66e351d8f0e766b642a&amp;id=391ce2ed39" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
          <input placeholder="correo electrónico" type="email" name="EMAIL">
          <input type="submit" name="subscribe" value="Suscríbete">
        </form>';
    echo $args['after_widget'];
  }
}
