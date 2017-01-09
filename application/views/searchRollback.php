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
    <!-- Datatables -->
    <link href="<?php echo site_url(); ?>assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
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
            <?php $this->session->set_userdata("page", "Rechercher rollback");?>
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

                      
                          
                          
                    <form class="form-horizontal form-label-left" novalidate>

                      <div class="item form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" id="searchone">
                          <div class="input-group">
                            <input id="portingid" class="form-control" type="text" placeholder="Numéro téléphone" data-inputmask="'mask': '699 99 99 99'">
                            <span class="input-group-btn">
                                <button id="btnsearch" type="button" class="btn btn-default"><span class="fa fa-search"></span></button>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                        </div>
                      </div>
                    </form>
                  </div>
                    
                  <div class="resultat x_content">
                    <table id="datatable-buttons" class="table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th>Porting ID</th>
                          <th>MSISDN</th>
                          <th>OPD</th>
                          <th>OPR</th>
                          <th>RIO</th>
                          <th>Date système</th>
                          <th>Date CADB</th>
                          <th>Etat</th>
                          <th>Action</th>
                        </tr>
                      </thead>


                      <tbody>
                        <tr>
                          <td>Tiger Nixon</td>
                          <td>System Architect</td>
                          <td>Edinburgh</td>
                          <td>61800</td>
                          <td>2011/04/25</td>
                          <td>$320,800</td>
                          <td>Accountant</td>
                          <td>Tokyo</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Garrett Winters</td>
                          <td>Accountant</td>
                          <td>Tokyo</td>
                          <td>63</td>
                          <td>2011/07/25</td>
                          <td>$170,750</td>
                          <td>Tiger Nixon</td>
                          <td>Edinburgh</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Ashton Cox</td>
                          <td>Junior Technical</td>
                          <td>San Francisco</td>
                          <td>66</td>
                          <td>2009/01/12</td>
                          <td>$86,000</td>
                          <td>Rhona Davidson</td>
                          <td>Integration</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Cedric Kelly</td>
                          <td>Senior Developer</td>
                          <td>Edinburgh</td>
                          <td>22</td>
                          <td>2012/03/29</td>
                          <td>$433,060</td>
                          <td>Developer</td>
                          <td>San Francisco</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Airi Satou</td>
                          <td>Accountant</td>
                          <td>Tokyo</td>
                          <td>33</td>
                          <td>2008/11/28</td>
                          <td>$162,700</td>
                          <td>Jena Gaines</td>
                          <td>Office Manager</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Brielle Williamson</td>
                          <td>Integration Specialist</td>
                          <td>New York</td>
                          <td>61</td>
                          <td>2012/12/02</td>
                          <td>$372,000</td>
                          <td>Regional Director</td>
                          <td>San Francisco</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Herrod Chandler</td>
                          <td>Sales Assistant</td>
                          <td>San Francisco</td>
                          <td>59</td>
                          <td>2012/08/06</td>
                          <td>$137,500</td>
                          <td>Michael Silva</td>
                          <td>Marketing</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Rhona Davidson</td>
                          <td>Integration Specialist</td>
                          <td>Tokyo</td>
                          <td>55</td>
                          <td>2010/10/14</td>
                          <td>$327,900</td>
                          <td>Sales Assistant</td>
                          <td>Sidney</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Colleen Hurst</td>
                          <td>Javascript Developer</td>
                          <td>San Francisco</td>
                          <td>39</td>
                          <td>2009/09/15</td>
                          <td>$205,500</td>
                          <td>Regional Director</td>
                          <td>London</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Sonya Frost</td>
                          <td>Software Engineer</td>
                          <td>Edinburgh</td>
                          <td>23</td>
                          <td>2008/12/13</td>
                          <td>$103,600</td>
                          <td>Pre-Sales</td>
                          <td>New York</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                        <tr>
                          <td>Jena Gaines</td>
                          <td>Office Manager</td>
                          <td>London</td>
                          <td>30</td>
                          <td>2008/12/19</td>
                          <td>$90,560</td>
                          <td>Regional Director</td>
                          <td>Singapore</td>
                          <td><button class="voir btn btn-primary"><i class="fa fa-eye"></i>Voir</button></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content 2999[0-1]9[0-3]9-02-237699999999-***  -->

        
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
    <!-- Custom Theme Scripts -->
    <script src="<?php echo site_url(); ?>assets/js/custom.js"></script>
    <!-- jquery.inputmask -->
    <script src="<?php echo site_url(); ?>assets/vendors/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <!-- validator -->
    
    
    <!-- Datatables -->
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/vendors/pdfmake/build/vfs_fonts.js"></script>
    
    <script>
      $(document).ready(function() {
        var handleDataTableButtons = function() {
          if ($("#datatable-buttons").length) {
            $("#datatable-buttons").DataTable({
              dom: "Bfrtip",
              buttons: [
                {
                  extend: "copy",
                  className: "btn-sm"
                },
                {
                  extend: "csv",
                  className: "btn-sm"
                },
                {
                  extend: "excel",
                  className: "btn-sm"
                },
                {
                  extend: "pdfHtml5",
                  className: "btn-sm"
                },
                {
                  extend: "print",
                  className: "btn-sm"
                },
              ],
              responsive: true
            });
          }
        };

        TableManageButtons = function() {
          "use strict";
          return {
            init: function() {
              handleDataTableButtons();
            }
          };
        }();

        TableManageButtons.init();
      });
    </script>
    <!-- /Datatables -->
    
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
    
    <script>
    $(document).ready(function() {
        $('.resultat').hide();
        $('#btnsearch').click(function(event){
           event.preventDefault();
           $('.resultat').show();
           $('#searchone').css({
                'margin-top': '1%',
                'margin-bottom' : '1%' 
            });
        });
        $('#portingid').keypress(function(event){
            if(event.which == 13){
                event.preventDefault();
               $('.resultat').show();
               $('#searchone').css({
                    'margin-top': '1%',
                    'margin-bottom' : '1%' 
                });
            }
        });
        
    });
    </script>
    
  </body>
</html>