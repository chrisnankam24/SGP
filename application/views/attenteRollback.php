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
    <!-- Glyphicons 
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">-->
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
            <?php $this->session->set_userdata("page", "Rollbacks en attente");?>
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
                    <h2>Liste des rollbacks</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="" role="tabpanel" data-example-id="togglable-tabs">
                        <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                          <li role="presentation" class=""><a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">Plusieurs heures<span>2</span></a>
                          </li>
                          <li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Une heure<span>8</span></a>
                          </li>
                          <li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">A l'instant<span>14</span></a>
                          </li>
                        </ul>
                        <div id="myTabContent" class="tab-content">
                          <div role="tabpanel" class="cache tab-pane fade active in" id="tab_content2" aria-labelledby="profile-tab">
                             <table class="table table-striped table-bordered">
                              <thead>
                                <tr>
                                  <th>Porting ID1</th>
                                  <th>MSISDN</th>
                                  <th>OPD</th>
                                  <th>OPR</th>
                                  <th>RIO</th>
                                  <th>Date soumission</th>
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
                                  <td>Accountant</td>
                                  <td>Tokyo</td>
                                  <td>
                                      <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                      <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                      <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>Brielle Williamson</td>
                                  <td>Integration Specialist</td>
                                  <td>New York</td>
                                  <td>61</td>
                                  <td>2012/12/02</td>
                                  <td>Regional Director</td>
                                  <td>Francisco</td>
                                  <td>
                                      <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                      <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                      <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>Sonya Frost</td>
                                  <td>Software Engineer</td>
                                  <td>Edinburgh</td>
                                  <td>23</td>
                                  <td>2008/12/13</td>
                                  <td>Pre-Sales</td>
                                  <td>New York</td>
                                  <td>
                                      <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                      <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                      <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>Jena Gaines</td>
                                  <td>Office Manager</td>
                                  <td>London</td>
                                  <td>30</td>
                                  <td>2008/12/19</td>
                                  <td>Regional Director</td>
                                  <td>Singapore</td>
                                  <td>
                                      <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                      <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                      <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                              <table class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>Porting ID2</th>
                                      <th>MSISDN</th>
                                      <th>OPD</th>
                                      <th>OPR</th>
                                      <th>RIO</th>
                                      <th>Date soumission</th>
                                      <th>Etat</th>
                                      <th>Action</th>
                                    </tr>
                                  </thead>


                                  <tbody>
                                    <tr>
                                      <td>Brielle Williamson</td>
                                      <td>Integration Specialist</td>
                                      <td>New York</td>
                                      <td>61</td>
                                      <td>2012/12/02</td>
                                      <td>Regional Director</td>
                                      <td>San Francisco</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Herrod Chandler</td>
                                      <td>Sales Assistant</td>
                                      <td>San Francisco</td>
                                      <td>59</td>
                                      <td>2012/08/06</td>
                                      <td>Michael Silva</td>
                                      <td>Marketing</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                          </div>
                          <div role="tabpanel" class="cache tab-pane fade active in" id="tab_content3" aria-labelledby="profile-tab">
                              <table class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>Porting ID3</th>
                                      <th>MSISDN</th>
                                      <th>OPD</th>
                                      <th>OPR</th>
                                      <th>RIO</th>
                                      <th>Date soumission</th>
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
                                      <td>Accountant</td>
                                      <td>Tokyo</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Garrett Winters</td>
                                      <td>Accountant</td>
                                      <td>Tokyo</td>
                                      <td>63</td>
                                      <td>2011/07/25</td>
                                      <td>Tiger Nixon</td>
                                      <td>Edinburgh</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Herrod Chandler</td>
                                      <td>Sales Assistant</td>
                                      <td>San Francisco</td>
                                      <td>59</td>
                                      <td>2012/08/06</td>
                                      <td>Michael Silva</td>
                                      <td>Marketing</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Rhona Davidson</td>
                                      <td>Integration Specialist</td>
                                      <td>Tokyo</td>
                                      <td>55</td>
                                      <td>2010/10/14</td>
                                      <td>Sales Assistant</td>
                                      <td>Sidney</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Colleen Hurst</td>
                                      <td>Javascript Developer</td>
                                      <td>San Francisco</td>
                                      <td>39</td>
                                      <td>2009/09/15</td>
                                      <td>Regional Director</td>
                                      <td>London</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Sonya Frost</td>
                                      <td>Software Engineer</td>
                                      <td>Edinburgh</td>
                                      <td>23</td>
                                      <td>2008/12/13</td>
                                      <td>Pre-Sales</td>
                                      <td>New York</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Jena Gaines</td>
                                      <td>Office Manager</td>
                                      <td>London</td>
                                      <td>30</td>
                                      <td>2008/12/19</td>
                                      <td>Regional Director</td>
                                      <td>Singapore</td>
                                      <td>
                                          <button class="valid btn btn-success"><i class="fa fa-check"></i>Valider</button>
                                          <button class="supp btn btn-warning"><i class="fa fa-outdent"></i>Rejeter</button>
                                          <button class="vuedetail btn btn-primary"><i class="fa fa-eye"></i>Détail</button>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
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
        $('#profile-tab').trigger("click");
        var handleDataTableButtons = function() {
          if ($("table").length) {
            $("table").DataTable({
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
    
  </body>
</html>