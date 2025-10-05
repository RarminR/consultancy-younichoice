<!DOCTYPE html><!--  This site was created in Webflow. https://www.webflow.com  -->
<!--  Last Published: Mon Feb 19 2024 10:13:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="65d32744ecac5261e14fd05b" data-wf-site="65d32744ecac5261e14fd055">
<head>

  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="<?php echo $base_url; ?>css/normalize.css" rel="stylesheet" type="text/css">
  <link href="<?php echo $base_url; ?>css/webflow.css" rel="stylesheet" type="text/css">
  <link href="<?php echo $base_url; ?>css/youni-navbar.webflow.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin="anonymous">
  <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js" type="text/javascript"></script>
  <script type="text/javascript">WebFont.load({  google: {    families: ["Poppins:100,200,300,regular,500,600,700,800,900"]  }});</script>
  <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
  <script src="https://kit.fontawesome.com/e081130a1b.js" crossorigin="anonymous"></script>
  <style>
    a:hover {
      text-decoration: none;
    }

    ::after {
      display: none !important;
    }

  </style>

</head>
<body>
  <div class="navbar-logo-left">
    <div data-animation="default" data-collapse="medium" data-duration="400" data-easing="ease" data-easing2="ease" role="banner" class="navbar-logo-left-container shadow-three w-nav">
      <div class="navbar-wrapper">
        <a href="<?php echo $base_url; ?>index.php" class="navbar-brand w-nav-brand"><img src="<?php echo $base_url; ?>images/Youni-Logo.svg" loading="lazy" width="153" alt=""></a>
        <nav role="navigation" class="nav-menu-wrapper w-nav-menu">
          <ul role="list" class="nav-menu-two w-list-unstyled">

            <li style = "padding-right: 20px;"class="list-item-2">
                <div data-hover="false" data-delay="0" class="nav-menu-link dropdown w-dropdown">
                  <div class="dropdown-toggle w-dropdown-toggle">
                    <div class="div-block">
                      <div> <?php echo $_SESSION["fullNameStudent"]; ?></div>
                      <div class="icon-4 w-icon-dropdown-toggle"></div>
                    </div>
                  </div>
                  <nav class="dropdown-list w-dropdown-list">
                    <a href="<?php echo $base_url; ?>resetPassword.php" class="dropdown-link w-dropdown-link">Change password</a>
                    <!-- <a href="changeCalendly.php" class="dropdown-link w-dropdown-link">Update Calendly</a> -->

                    <a href="<?php echo $base_url; ?>signOut.php" class="dropdown-link w-dropdown-link"> <i class="fa fa-sign-out" aria-hidden="true"></i> Sign Out</a>
                  </nav>
                </div>
              </li>
          </ul>
        </nav>
        <div class="menu-button-2 w-nav-button">
          <div class="icon-3 w-icon-nav-menu"></div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=65d32744ecac5261e14fd055" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="<?php echo $base_url; ?>js/webflow.js" type="text/javascript"></script>
</body>
</html>