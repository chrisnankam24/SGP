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
    <!-- Select2 -->
    <link href="<?php echo site_url(); ?>assets/vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="<?php echo site_url(); ?>assets/css/custom.css" rel="stylesheet">
    <!-- Datetimepicker -->
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker-standalone.css" rel="stylesheet">
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
            <?php $this->session->set_userdata("page", "Ajouter utilisateur");?>
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
                    <h2>Formulaire utilisateur</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                    <form class="form-horizontal form-label-left" novalidate>

                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nameuser"> Nom d'utilisateur <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="nameuser" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name"required="required" type="text">
                          <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nom <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="name" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name" required="required" type="text">
                          <span class="fa fa-users form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                      
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="surname">Prénom </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="surname" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name" required="required" type="text">
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="email" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12">
                            <span class="fa fa-envelope form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">MSISDN</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '237-699999999'">
                          <span class="fa fa-key form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Rôle <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="role" class="form-control" tabindex="-1">
                            <option></option>
                            <option value="clientele">Chargé clientele</option>
                            <option value="teleconseille">Téléconseiller</option>
                            <option value="administrateur">Administrateur</option>
                          </select>
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Statut <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="statut" class="form-control" tabindex="-1">
                            <option></option>
                            <option value="activer">Activer</option>
                            <option value="desactiver">Désactiver</option>
                          </select>
                        </div>
                      </div>
                        
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 col-sm-offset-3 col-xs-offset-0">
                          <button id="annuler" type="reset" class="btn btn-primary">Annuler</button>
                          <button id="valider" type="submit" class="btn btn-success">Valider</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        
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
    <!-- validator -->
    <script src="<?php echo site_url(); ?>assets/vendors/validator/validator.min.js"></script>
    <!-- Select2 -->
    <script src="<?php echo site_url(); ?>assets/vendors/select2/dist/js/select2.full.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="<?php echo site_url(); ?>assets/js/custom.js"></script>
    <!-- jquery.inputmask -->
    <script src="<?php echo site_url(); ?>assets/vendors/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="<?php echo site_url(); ?>assets/js/moment/moment.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/js/moment-local.js"></script>
    <script src="<?php echo site_url(); ?>assets/js/datepicker/daterangepicker.js"></script>
    <!-- bootstrap-datetimepicker -->
    <script type="text/javascript" src="<?php echo site_url(); ?>assets/js/bootstrap-datetimepicker.js"></script>
    
    <!-- validator -->
    <script>
      // initialize the validator function
      validator.message.date = 'not a real date';

      // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
      $('form')
        .on('blur', 'input[required], input.optional, select.required', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

      $('.multi.required').on('keyup blur', 'input', function() {
        validator.checkField.apply($(this).siblings().last()[0]);
      });

      $('form').submit(function(e) {
        e.preventDefault();
        var submit = true;

        // evaluate the form using generic validaing
        if (!validator.checkAll($(this))) {
          submit = false;
        }

        if (submit)
          this.submit();

        return false;
      });
    </script>
    <!-- /validator -->
    
    <!-- jquery.inputmask -->
    <script>
      $(document).ready(function() {
        $(":input").inputmask();
      });
    </script>
    <!-- /jquery.inputmask -->
    
    <!-- Select2 -->
    <script>
      $(document).ready(function() {
        $("#role").select2({
          placeholder: "Selectioner le role",
          allowClear: true
        });
        $("#statut").select2({
          placeholder: "Selectioner le statut",
          allowClear: true
        });
      });
    </script>
    <!-- /Select2 -->
    
    <!-- Datepicker -->
    <script>
      $(document).ready(function() {
        $('#single_cal3').daterangepicker({
          singleDatePicker: true,
          calender_style: "picker_3"
        }, function(start, end, label) {
          console.log(start.toISOString(), end.toISOString(), label);
        });
    });
    </script>
    <!-- /Datepicker -->
    
    <!-- Datetimepicker -->
    <script type="text/javascript">
        $(function () {
            $('#datetimepicker2').datetimepicker({
                    locale: 'fr'
            });
        });
    </script>
    <!-- /Datepicker -->
    
    
  </body>
</html>