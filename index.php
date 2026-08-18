<?php

require_once 'config/config.php';

include 'includes/header.php';

include 'includes/navbar.php';

?>




<section class="hero" id="beranda">

    <div class="hero-container">


        <!-- BAGIAN KIRI -->
        <div class="hero-content">

            <h1>
                SECRET SANTA
            </h1>

            <h2>
                SKARIGA
            </h2>

            <p>
                Tukar kado, berbagi kebahagiaan,<br>
                jalin keakraban bersama teman
            </p>


            <div class="hero-buttons">

                <a
                    href="<?= base_url('login.php') ?>"
                    class="hero-login"
                >
                    LOGIN
                </a>

                <a
                    href="<?= base_url('register.php') ?>"
                    class="hero-register"
                >
                    REGISTER
                </a>

            </div>

        </div>


        <!-- BAGIAN KANAN -->
        <div class="hero-image">

            <img
                src="<?= base_url('assets/img/santa1.png') ?>"
                alt="Santa Secret Santa"
            >

        </div>

    </div>

</section>



