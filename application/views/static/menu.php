<!-- sidebar menu -->
<div class="left_col scroll-view">
    <div class="navbar nav_title" style="border: 0;">
        <a href="<?php echo site_url(); ?>Acceuil/index.html" class="site_title">
            <img src="<?php echo site_url(); ?>assets/images/logo.gif" alt="Logo Orange" id="profile">
        </a>
    </div>

    <div class="clearfix"></div>

    <!-- menu profile quick info -->
    <div class="profile">
      <div class="profile_pic">
        <img src="<?php echo site_url(); ?>assets/images/img.jpg" alt="..." class="img-circle profile_img">
      </div>
      <div class="profile_info">
        <span>Welcome,</span>
        <h2>John Doe</h2>
      </div>
    </div>
    <!-- /menu profile quick info -->

    <br />

    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
        <div class="menu_section">
          <h3>Rôle User</h3>
          <ul class="nav side-menu">
            <li><a href="<?php echo site_url(); ?>Acceuil/index.html"><img src="<?php echo site_url(); ?>assets/images/home.png" alt=""> Acceuil </a>
            </li>
            <li><a><img src="<?php echo site_url(); ?>assets/images/users.png" alt=""> Utilisateurs <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <li><a href="<?php echo site_url(); ?>User/addUser.html">Ajouter</a>
                </li>
                <li><a href="<?php echo site_url(); ?>User/viewUser.html">Liste</a>
                </li>
              </ul>
            </li>
            <li><a><img src="<?php echo site_url(); ?>assets/images/portage.png" alt=""> Portages <span class="nummalert">38</span><span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <li><a href="<?php echo site_url(); ?>Portage/addPortage.html">Ajouter</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Portage/viewPortage.html">Liste</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Portage/attentePortage.html">En attente<span class="nummalert">38</span></a>
                </li>
                <li><a href="<?php echo site_url(); ?>Portage/searchPortage.html">Recherche</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Portage/livePortage.html">Direct</a>
                </li>
              </ul>
            </li>
            <li><a><img src="<?php echo site_url(); ?>assets/images/rollback.png" alt=""> Roll Back <span class="nummalert">24</span><span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <li><a href="<?php echo site_url(); ?>Rollback/addRollback.html">Ajouter</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Rollback/viewRollback.html">Liste</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Rollback/attenteRollback.html">En attente <span class="nummalert">24</span></a>
                </li>
                <li><a href="<?php echo site_url(); ?>Rollback/searchRollback.html">Recherche</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Rollback/liveRollback.html">Direct</a>
                </li>
              </ul>
            </li>
            <li><a><img src="<?php echo site_url(); ?>assets/images/restitution.png" alt=""> Restitution  <span class="nummalert">46</span><span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <li><a href="<?php echo site_url(); ?>Restitution/addRestitution.html">Ajouter</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Restitution/viewRestitution.html">Liste</a>
                </li>
                <li><a href="<?php echo site_url(); ?>Restitution/attenteRestitution.html">En attente <span class="nummalert">46</span></a>
                </li>
                <li><a href="<?php echo site_url(); ?>Restitution/searchRestitution.html">Recherche</a>
                </li>
              </ul>
            </li>
            <li><a href="<?php echo site_url(); ?>Rapport/rapport.html"><img src="<?php echo site_url(); ?>assets/images/report.png" alt=""> Rapports </a>
            <li><a href="<?php echo site_url(); ?>Rio/viewrio.html"><img src="<?php echo site_url(); ?>assets/images/key.png" alt=""> RIO </a>
            </li>
          </ul>
        </div>

    </div>
</div>
<!-- /sidebar menu -->