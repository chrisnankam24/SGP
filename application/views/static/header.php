<!-- top navigation -->
<div class="top_nav">

  <div class="nav_menu">
    <nav class="" role="navigation">
      <div class="nav toggle visible-sm visible-xs">
        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
      </div>
      <div id="titre">
          <h1><?php echo $this->session->userdata('page'); ?></h1>
      </div>

      <ul class="nav navbar-nav navbar-right">
        <li class="">
          <a href="<?php echo site_url(); ?>assets/javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <img src="<?php echo site_url(); ?>assets/images/img.jpg" alt="">John Doe
            <span class=" fa fa-angle-down"></span>
          </a>
          <ul class="dropdown-menu dropdown-usermenu pull-right">
            <li><a href="<?php echo site_url(); ?>assets/javascript:;">  Profil</a>
            </li>
            <li>
              <a href="<?php echo site_url(); ?>assets/javascript:;">
                <span class="badge bg-red pull-right">50%</span>
                <span>Paramètre</span>
              </a>
            </li>
            <li><a href="<?php echo site_url(); ?>assets/login.html"><i class="fa fa-sign-out pull-right"></i> Déconnexion</a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </div>

</div>
<!-- /top navigation -->