<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Orange-Cameroun | SGP</title>

    <!-- Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="<?php echo site_url(); ?>assets/css/custom.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  </head>

  <body style="background:#FFFFFF;">
    <div class="">
      <a class="hiddenanchor" id="toregister"></a>
      <a class="hiddenanchor" id="tologin"></a>

      <div id="wrapper">
        <div id="login" class=" form">
          <section class="login_content">
              <center>
                <img src="<?php echo site_url(); ?>assets/images/logo.gif" alt="Logo Orange" >   
              </center>  
            <form class="row">
                <h1 class="col-md-6 col-md-offset-3">Authentification</h1>
                <div class="col-md-4 col-md-offset-4" style="background: #EEEEEE;">
                    <div>
                      <input type="text" class="form-control" placeholder="Username" required="" />
                    </div>
                    <div>
                      <input type="password" class="form-control" placeholder="Password" required="" />
                    </div>
                    <div>
                        <a href="acceuil.html">
                            <button class="btn btn-default submit">Connexion</button>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                    <div class="separator">
                      <p class="change_link">
                      </p>
                    </div>
                </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
</html>