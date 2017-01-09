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
    <!-- Time Line -->
    <link href="<?php echo site_url(); ?>assets/css/timeline.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="<?php echo site_url(); ?>assets/vendors/select2/dist/css/select2.min.css" rel="stylesheet">
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
            <?php $this->session->set_userdata("page", "Détail de rollback N° XXX");?>
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
                    <h2>Détail de portage</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div style="display:inline-block;width:100%;overflow-y:auto;">
                            <ul class="timeline timeline-horizontal">
                                <li class="timeline-item">
                                    <div class="timeline-badge primary"><i class="glyphicon glyphicon-unchecked"></i></div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Ordered</h4>
                                            <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 12-03-16 13:45</small></p>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Auto-Ordered</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-item">
                                        <div class="timeline-badge success"><i class="glyphicon glyphicon-check"></i></div>
                                        <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                        <h4 class="timeline-title">Approved</h4>
                                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 11-04-16 19:36</small></p>
                                                </div>
                                                <div class="timeline-body">
                                                        <p>Commentaire</p>
                                                </div>
                                        </div>
                                </li>
                                <li class="timeline-item">
                                        <div class="timeline-badge info"><i class="glyphicon glyphicon-remove-circle"></i></div>
                                        <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                        <h4 class="timeline-title">Denied</h4>
                                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 11-09-14 21:13</small></p>
                                                </div>
                                                <div class="timeline-body">
                                                        <p></p>
                                                </div>
                                        </div>
                                </li>
                                <li class="timeline-item">
                                        <div class="timeline-badge danger"><i class="glyphicon glyphicon-check"></i></div>
                                        <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                        <h4 class="timeline-title">Accepted</h4>
                                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 14-10-16 15:54</small></p>
                                                </div>
                                                <div class="timeline-body">
                                                        <p>Comment</p>
                                                </div>
                                        </div>
                                </li>
                                <li class="timeline-item">
                                        <div class="timeline-badge warning"><i class="glyphicon glyphicon-unchecked"></i></div>
                                        <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                        <h4 class="timeline-title">Rejected</h4>
                                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 03-06-15 20:36</small></p>
                                                </div>
                                                <div class="timeline-body">
                                                        <p>Auto-rejected</p>
                                                </div>
                                        </div>
                                </li>
                                <li class="timeline-item">
                                        <div class="timeline-badge"><i class="glyphicon glyphicon-check"></i></div>
                                        <div class="timeline-panel">
                                                <div class="timeline-heading">
                                                        <h4 class="timeline-title">Confirmed</h4>
                                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> 16-10-16 23:25</small></p>
                                                </div>
                                                <div class="timeline-body">
                                                        <p></p>
                                                </div>
                                        </div>
                                </li>
                        </ul>
                        </div>
                    </div>
                      
                    <table id="mytable" class="col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                      <thead>
                        <tr>
                          <th></th>
                          <th>
                              <span>Données LDB</span>
                          </th>
                          <th>
                              <span>Données CADB</span>
                          </th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <td>recipientNrn</td>
                          <td>System Architect</td>
                          <td>Edinburgh</td>
                        </tr>
                        <tr>
                          <td>donorNrn</td>
                          <td>Accountant</td>
                          <td>Tokyo</td>
                        </tr>
                        <tr>
                          <td>portingDateTime</td>
                          <td>Junior Technical</td>
                          <td>San Francisco</td>
                        </tr>
                        <tr>
                          <td>rio</td>
                          <td>Senior Developer</td>
                          <td>Edinburgh</td>
                        </tr>
                        <tr>
                          <td>numberRange</td>
                          <td>Accountant</td>
                          <td>Tokyo</td>
                        </tr>
                        <tr>
                          <td>subscriberInfo</td>
                          <td>Integration Specialist</td>
                          <td>New York</td>
                        </tr>
                        <tr>
                          <td>Datetime Porting</td>
                          <td>Sales Assistant</td>
                          <td>San Francisco</td>
                        </tr>
                      </tbody>
                      
                      <tfoot>
                        <tr>
                          <th></th>
                          <th>
                              <span class="btn-danger">State LDB</span>
                          </th>
                          <th>
                              <span class="btn-warning">State CADB</span>
                          </th>
                        </tr>
                      </tfoot>
                    </table>
                      
                    <table id="formbottom" class="col-md-offset-1 col-md-10 col-sm-12 col-xs-12">
                        <tr>
                          <td>Raison</td>
                          <td>
                              <select id="raison" class="form-control" tabindex="-1">
                                <option></option>
                                <option value="raison1">Raison 1</option>
                                <option value="raison2">Raison 2</option>
                              </select>
                          </td>
                        </tr>
                        <tr>
                          <td>Observations</td>
                          <td>
                              <textarea  placeholder="Renseignez vos observations"></textarea>
                          </td>
                        </tr>
                        <tr>
                            <td>
                                <button id="annuler" type="reset" class="btn btn-primary">Rejeter</button>
                            </td>
                            <td>
                                <button id="valider" type="submit" class="btn btn-success">Valider</button>
                            </td>
                        </tr>
                    </table>
                      
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
    <!-- Select2 -->
    <script src="<?php echo site_url(); ?>assets/vendors/select2/dist/js/select2.full.min.js"></script>
    

    <!-- Select2 -->
    <script>
      $(document).ready(function() {
        $("#raison").select2({
          placeholder: "Selectioner la raison",
          allowClear: true
        });
      });
    </script>
    <!-- /Select2 -->
    
    
  </body>
</html>