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
    <!-- iCheck -->
    <link href="<?php echo site_url(); ?>assets/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
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
            <?php $this->session->set_userdata("page", "Ajouter portage");?>
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
                    <h2>Formulaire de portage</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                    <form class="form-horizontal form-label-left" novalidate>
  
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Type de portage <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="control-label col-md-8 col-md-offset-2">
                                <span class="fleft">Simple:<input type="radio" class="flat" name="typportage" id="portsimple" value="Simple" checked="" required /></span> 
                                Bloc: <input type="radio" class="flat" name="typportage" id="portbloc" value="Bloc" />
                          </div>
                        </div>
                      </div>
                        
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Type abonné <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="typabonne" class="form-control" tabindex="-1">
                            <option></option>
                            <option value="particulier">Particulier</option>
                            <option value="entreprise">Entreprise</option>
                          </select>
                        </div>
                      </div>
                        
                      <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Numéro temporel <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '(+237) 699-99-99-99'">
                          <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nom <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="name" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name" required="required" type="text">
                          <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                     <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="surname">Prénom <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="surname" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name" required="required" type="text">
                          <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                        
                      <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="surname">Email <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="email" name="email" required="required" class="form-control col-md-7 col-xs-12" type="email">
                        </div>
                      </div>
                        
                      <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Type de document <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="typdoc" class="form-control" tabindex="-1">
                            <option></option>
                            <option value="CNI">CNI</option>
                            <option value="Passeport">Passeport</option>
                            <option value="Recépissé">Recépissé</option>
                          </select>
                        </div>
                      </div>
                        
                      <div class="form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Numéro du document</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '99999999'">
                        </div>
                      </div>
                      
                      <div class="form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">RIO <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '02 P ****** ***'">
                        </div>
                      </div>
                        
                      <div class="item form-group entreprise">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="namelegal">Nom légal <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input id="namelegal" class="form-control col-md-7 col-xs-12" data-validate-length-range="3" data-validate-words="1" name="name" placeholder="Nom exple: Orange" required="required" type="text">
                          <span class="fa fa-user form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                      
                      <div class="item form-group entreprise">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Numéro contribuable </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '999-999-999-999'">
                          <span class="fa fa-key form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="item form-group entreprise">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Contact <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '(+237) 699-99-99-99'">
                          <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      <div class="form-group entreprise">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Numéros à porter & RIO <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename"> <!-- don't give a name === doesn't send on POST/GET -->
                                <span class="input-group-btn">
                                    <!-- image-preview-clear button -->
                                    <button type="button" class="btn btn-dark image-preview-clear" style="display:none;">
                                        <span class="glyphicon glyphicon-remove"></span> Supprimer
                                    </button>
                                    <!-- image-preview-input -->
                                    <div class="btn btn-primary image-preview-input file-upload">
                                        <span class="glyphicon glyphicon-folder-open"></span>
                                        <span class="image-preview-input-title">Choisir fichier</span>
                                        <input type="file" accept=".csv, .xls, .xlsx" name="input-file-preview"/> <!-- rename it -->
                                    </div>
                                </span>

                            </div>
                            
                        </div>
                      </div>
                      
                      <div class="item form-group particulier">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Numéro à porter <span class="required">*</span> </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" data-inputmask="'mask' : '(+237) 699-99-99-99'">
                          <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
                        </div>
                      </div>
                        
                      
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">OPD <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="opd" class="form-control" tabindex="-1">
                            <option></option>
                            <option value="MTN">MTN</option>
                            <option value="Nextel">Nextel</option>
                            <option value="Camtel">Camtel</option>
                          </select>
                        </div>
                      </div>
                        
                     <div class="form-group portbloc">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Liste des numéros à porter <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename"> <!-- don't give a name === doesn't send on POST/GET -->
                                <span class="input-group-btn">
                                    <!-- image-preview-clear button -->
                                    <button type="button" class="btn btn-dark image-preview-clear" style="display:none;">
                                        <span class="glyphicon glyphicon-remove"></span> Supprimer
                                    </button>
                                    <!-- image-preview-input -->
                                    <div class="btn btn-primary image-preview-input file-upload">
                                        <span class="glyphicon glyphicon-folder-open"></span>
                                        <span class="image-preview-input-title">Choisir fichier</span>
                                        <input type="file" accept=".csv, .xls, .xlsx" name="input-file-preview"/> <!-- rename it -->
                                    </div>
                                </span>

                            </div>
                            
                        </div>
                      </div>
                        
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 col-sm-offset-3 col-xs-offset-0">
                          <input type="hidden" class="hidden" value="XX" id="idpreced">
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
    <!-- iCheck -->
    <script src="<?php echo site_url(); ?>assets/vendors/iCheck/icheck.min.js"></script>
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
        $("#typabonne").select2({
          placeholder: "Selectioner le type d'abonné",
          allowClear: true
        });
        $("#typdoc").select2({
          placeholder: "Selectioner le type de document",
          allowClear: true
        });
        $("#opd").select2({
          placeholder: "Selectioner l'opérateur donneur",
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

        <script>
      $(document).ready(function() {  
        $('.entreprise').hide();
        $('.particulier').hide();
        $('.portbloc').hide();
        $('#typabonne').change(function(){
            if($('input[name=typportage]:checked', 'form').val()=="Simple"){
                var elmnts = $("select option:selected").val();
                var elmpreced = $('#idpreced').val();
                $('.portbloc').hide();
                $("." + elmnts).show();
                $("." + elmpreced).hide();
                $('#idpreced').val(elmnts);
            }else{
                $('.portbloc').show();$('.entreprise').hide();
                $('.particulier').hide();
            }
        })
      });
    </script>
    
    
    <script>    
    
    $(document).on('click', '#close-preview', function(){ 
        $('.image-preview').popover('hide');
        // Hover befor close the preview    
    });
    $(function() {
        // Create the close button
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");

        // Clear event
        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('.image-preview-input input:file').val("");
            $(".image-preview-input-title").text("Browse"); 
        }); 
        // Create the preview image
        $(".image-preview-input input:file").change(function (){     
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200
            });      
            var file = this.files[0];
            var reader = new FileReader();
            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("Changer");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
            }        
            reader.readAsDataURL(file);
        });  
        $('.image-preview-filename').attr('disabled','disabled');
    });

    </script>
    
  </body>
</html>