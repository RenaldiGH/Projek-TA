<nav class="navbar">

    <div class="navbar-container">

        <!-- LOGO -->
        <a href="<?= base_url() ?>" class="logo">

            <span>SKARIGA</span>

            <strong>SECRET SANTA</strong>

        </a>


        <!-- MENU -->
        <div class="nav-menu">

            <a href="<?= base_url() ?>#beranda">
                BERANDA
            </a>

            <a href="<?= base_url() ?>#cara-kerja">
                CARA KERJA
            </a>

            <a href="<?= base_url() ?>#timeline">
                TIMELINE
            </a>

            <a href="<?= base_url() ?>#tentang">
                TENTANG
            </a>

        </div>


        <!-- BUTTON -->
        <div class="nav-buttons">

            <a
                href="<?= base_url('login.php') ?>"
                class="nav-login"
            >
                LOGIN
            </a>

            <a
                href="<?= base_url('register.php') ?>"
                class="nav-register"
            >
                REGISTER
            </a>

        </div>

    </div>

</nav>