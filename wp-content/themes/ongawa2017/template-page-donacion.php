<?php
/**
 * Template Name: Página Donación
 */
?>

<section class="page-wrapper page-wrapper--simple">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class() ?> id="page-<?php the_ID(); ?>">
      <header class="container">
        <h1 class="section-title"><?php the_title(); ?></h1>
      </header>
      <div class="page__content">
        <div class="page__content__block">
            <?php the_content(); ?>
            <script type="text/javascript">
            function validateForm()
            {
            var nombre=document.forms["formulario"]["nombre"].value;
            var apellidos=document.forms["formulario"]["apellidos"].value;
            var documento=document.forms["formulario"]["documento"].value;
            var importe=document.forms["formulario"]["importe"].value;
            var email=document.forms["formulario"]["email"].value;
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;var dotpos=email.lastIndexOf(".");

            if (nombre=="")
              {
              document.getElementById("nombre-wrap").innerHTML="Este campo es obligatorio";
              return false;
              }
            if (apellidos=="")
              {
              document.getElementById("apellidos-wrap").innerHTML="Este campo es obligatorio";
              return false;
              }
            if (documento=="")
              {
              document.getElementById("documento-wrap").innerHTML="Este campo es obligatorio";
              return false;
              }
            if (!filter.test(email))
              {
              document.getElementById("email-wrap").innerHTML="Debe indicar una dirección de email correcta: nombre@dominio.com";
              return false;
              }
            if (importe=="")
              {
              document.getElementById("importe-wrap").innerHTML="Este campo es obligatorio";
              return false;
              }

            }
            </script>
            <div class="gf_browser_chrome gform_wrapper" id="gform_wrapper_1" style="">
              <form action="https://secure.ongawa.org/formulario_donacion/procesar.php?action=go" name="formulario" onsubmit="return validateForm()" method="post" id="mainform">
                  <div class="container">
                    <div class="gform_body">
                      <div id="gform_page_1_1" class="gform_page">
                            <div class="gform_page_fields">
                              <ul style="margin:0px -10px" id="gform_fields_1" class="gform_fields top_label form_sublabel_below description_below">
                                <li id="field_1_33" class="gfield col-5 gfield_contains_required field_sublabel_below field_description_below">
                                  <label class="gfield_label" for="nombre">Nombre *</label>
                                  <div class="ginput_container ginput_container_number">
                                    <input name="nombre" id="nombre" type="text" class="medium">
                                    <div  id="nombre-wrap">

                                    </div>
                                  </div>
                                </li>
                                <li id="field_1_33" class="gfield col-6 gfield_contains_required field_sublabel_below field_description_below">
                                  <label class="gfield_label" for="apellidos">Apellidos *</label>
                                  <div class="ginput_container ginput_container_number">
                                    <input name="apellidos" id="apellidos" type="text" class="medium">
                                    <div  id="apellidos-wrap">

                                    </div>
                                  </div>
                                </li>
                                <li id="field_1_33" class="gfield col-5 gfield_contains_required field_sublabel_below field_description_below">
                                  <label class="gfield_label" for="documento">DNI, NIE o Pasaporte *</label>
                                  <div class="ginput_container ginput_container_number" >
                                    <input name="documento" id="documento" type="text" class="medium">
                                    <div id="documento-wrap">

                                    </div>
                                  </div>
                                </li>
                                <li id="field_1_33" class="gfield col-6 gfield_contains_required field_sublabel_below field_description_below">
                                  <label class="gfield_label" for="email">Email *</label>
                                  <div class="ginput_container ginput_container_number" >
                                    <input name="email" id="email" type="text" class="medium">
                                    <div id="email-wrap"></div>
                                  </div>
                                </li>
                                <li id="field_1_33" class="gfield col-4 gfield_contains_required field_sublabel_below field_description_below">
                                  <label class="gfield_label" for="importe">Importe *</label>
                                  <div class="ginput_container ginput_container_number">
                                    <input name="importe" id="importe" type="text" class="medium">
                                    <div id="importe-wrap"></div>
                                  </div>
                                </li>
                              </ul>
                        </div>
                            <div class="gform_page_footer">
                                 <input type="submit" id="gform_next_button_1_2" class="gform_next_button button" value="Finalizar">
                            </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
        </div>
      </div>
    </article>
  <?php endwhile; ?>
</section>
