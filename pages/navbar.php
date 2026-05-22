<div class="main">
			<nav class="navbar navbar-expand navbar-light bg-white sticky-top">
				<a class="sidebar-toggle d-flex mr-3">
          <i class="align-self-center" data-feather="menu"></i>
        </a>

				<form class="form-inline d-none d-sm-inline-block">
					<input class="form-control form-control-no-border navbar-search mr-sm-2" type="text" placeholder="Search topics..." aria-label="Search" style="display: none !important;">
				</form>

				<div class="navbar-collapse collapse">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item dropdown">
							<a class="nav-icon dropdown-toggle ml-2 d-inline-block d-sm-none" href="#" id="userDropdown" data-toggle="dropdown">
								<div class="position-relative">
									<i class="align-middle mt-n1" data-feather="settings"></i>
								</div>
							</a>
							<a class="nav-link nav-link-user dropdown-toggle d-none d-sm-inline-block" href="#" id="userDropdown" data-toggle="dropdown">
                				<img  src="pages/assets/img/avatar00.jpg" class="avatar img-fluid rounded mr-1" alt="user name" /> <span id="profile-username" class="text-dark">Loading...</span>
              				</a>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
								<a class="dropdown-item" href="profile">Profile</a>
								<div class="dropdown-divider"></div>
								<!-- <a class="dropdown-item" href="pages-settings.html">Settings & Privacy</a> -->
								<form id="logout_form" action="pages/utils/auth/logout.php" method="POST">
									<button type="submit" class="dropdown-item">Sign out</button>
								</form>
							</div>
						</li>


					</ul>
				</div>
			</nav>

			<main class="content">