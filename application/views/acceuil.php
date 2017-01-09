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

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">      
            <!-- sidebar menu -->
                <?php include_once ('static/menu.php');?>
            <!-- /sidebar menu -->
        </div>
          
          
        <!-- top navigation -->
            <?php $this->session->set_userdata("page", "Acceuil");?>
            <?php include_once ('static/header.php');?>
        <!-- /top navigation -->
        
        
        <!-- page content -->
         <div class="right_col" role="main">

          <div class="">
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row top_tiles">
                            <div class="row">
                                <div class="">
                                </div>
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                  <div class="tile-stats">
                                    <div class="icon">
                                        <img src="<?php echo site_url(); ?>assets/images/machine.png" alt="" class="">
                                    </div>
                                    <div class="warning count">1769</div>
                                    <h3>Auto-validate</h3>
                                    <p>Commentaire sur les données</p>
                                  </div>
                                </div>
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                  <div class="tile-stats">
                                    <div class="icon">
                                        <img src="<?php echo site_url(); ?>assets/images/users.png" alt="" class="">
                                    </div>
                                    <div class="count">76</div>
                                    <h3>Utilisateurs </h3>
                                    <br/>
                                  </div>
                                </div>
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                  <div class="tile-stats">
                                    <div class="icon">
                                        <img src="<?php echo site_url(); ?>assets/images/restitution.png" alt="" class="">
                                    </div>
                                    <div class="count">486229</div>
                                    <h3>Restitutions </h3>
                                    <br/>
                                  </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                    <div class="tile-stats">
                                        <div class="icon">
                                            <img src="<?php echo site_url(); ?>assets/images/notification.png" alt="" class="">
                                        </div>
                                        <div class="danger count">39</div>
                                        <h3>Notifications</h3>
                                        <p>Commentaire sur les données</p>
                                    </div>
                                </div>
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                    <div class="tile-stats">
                                        <div class="icon">
                                            <img src="<?php echo site_url(); ?>assets/images/portage.png" alt="" class="">
                                        </div>
                                        <div class="count">5245542</div>
                                        <h3>Portages</h3>
                                        <p>Commentaire sur les données</p>
                                    </div>
                                </div>
                                <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                  <div class="tile-stats">
                                    <div class="icon">
                                        <img src="<?php echo site_url(); ?>assets/images/rollback.png" alt="" class="">
                                    </div>
                                    <div class="count">5526805</div>
                                    <h3>Rollback </h3>
                                    <br/>
                                  </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content-->

        
        <!-- footer content -->
            <?php include_once ('static/footer.php');?>
        <!-- /footer content -->
        
      </div>
    </div>


    <!-- jQuery -->
    <script src="<?php echo site_url(); ?>assets/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?php echo site_url(); ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="<?php echo site_url(); ?>assets/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="<?php echo site_url(); ?>assets/vendors/nprogress/nprogress.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="<?php echo site_url(); ?>assets/js/custom.js"></script>
    
    
  </body>
</html>