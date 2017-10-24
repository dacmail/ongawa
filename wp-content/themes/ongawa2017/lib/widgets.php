<?php
class UN_Newsletter_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'newsletter_widget',
      'description' => 'Formulario de newsletter',
    );
    parent::__construct( 'newsletter_widget', 'Formulario Newsletter', $widget_ops );
  }

  public function widget( $args, $instance ) {
    echo $args['before_widget'];
    echo $args['before_title'] . apply_filters( 'widget_title', 'Infórmate' ) . $args['after_title'];
    echo '<p>Recibe información sobre lo que hacemos en nuestras diferentes áreas temáticas:</p>
        <form>
          <input type="email" name="">
          <input type="submit" name="" value="Suscríbete">
        </form>';
    echo $args['after_widget'];
  }
}
