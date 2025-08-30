<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKIUTC</title>
    <style>
        html {
            scroll-behavior: smooth;
        }

        .banner-image {
            width: 60%;
            height: auto;
            border-bottom: 5px solid #faf9ed;
        }

        .tarifs-ski-snow {
            width: 70%;
        }

        nav {
            font-size: 1.5em;
        }

        .content-section {
            width: 70%;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .banner-image {
                width: 90%;
            }
            .tarifs-ski-snow {
                width: 90%;
            }
            nav {
                font-size: 0.9em;
            }
            .content-section {
                width: 80%;
                margin: 0 auto;
                padding: 20px;
            }
        }

        body {
            font-family: 'Avenir', sans-serif;
            background-color: #faf9ed;
            color: #2E4057;
            margin: 0;
            padding: 0;
        }

        .important {
            font-weight: bold;
        }

        header {
            background-color: #b1cfcd;
            color: #FFF;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            font-family: 'Din Condensed', sans-serif;
            font-size: 2.5em;
        }

        ul {
            list-style-position: inside;
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin: 0 10px;
        }

        nav ul li a {
            color: #faf9ed;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            text-decoration: underline;
        }

        main {
            padding: 20px;
        }

        h2 {
            color: #e93d2f; 
        }

        a#insta {
            color: #e93d2f;
        }

        footer {
            background-color: #b1cfcd;
            color: #FFF;
            text-align: center;
            padding: 10px;
        }

        footer a {
            color: #A8DADC;
        }

        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <header class="banner">
        <div style="background-color: #b1cfcd;">
            <img src="{{ asset('storage/site/baniere.png') }}" alt="Bannière SKIUTC" class="banner-image">
        </div>
        <nav style="display: flex; justify-content: center;">
            <ul style="display: flex; list-style: none; padding: 0;">
            <li><a href="#about">L'asso</a></li>
            <li style="margin: 0 10px; color: #faf9ed;">|</li>
            <li><a href="#voyage">Le voyage</a></li>
            <li style="margin: 0 10px; color: #faf9ed;">|</li>
            <li><a href="#place">Obtenir sa place</a></li>
            <li style="margin: 0 10px; color: #faf9ed;">|</li>
            <li><a href="#tombola">Tombola</a></li>
            <li style="margin: 0 10px; color: #faf9ed;">|</li>
            <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section id="about" style="text-align: center;" class="content-section">
            <div style="display: flex; align-items: center; justify-content: center;">
            <h2><u>SKIUTC c'est quoi?</u></h2>
            </div>
            <p><span class="important">SKIUTC organise l'un des événements phares des assos étudiantes de l'UTC</span>, 
            avec sa <u>36ᵉ édition</u> cette année!</p>
            <p><span class="important">Chaque année, environ 400 étudiants partent ensemble</span> pour une semaine de glisse,
            de fun et de détente après les examens finaux. Il s'agit 
            d'un voyage mémorable pour tous les participants avec une ambiance conviviale, 
            des soirées, des activités et des moments inoubliables entre utcéens. </p>
            <p><span class="important">SKIUTC est conçu pour permettre à chacun de profiter d'une expérience unique 
            à un prix réduit</span> et accessible au plus grand nombre d'entre nous.</p>
        </section>
        
        <div style="text-align: center; margin: 20px 0;">
            <h2>Teaser de skutskut 2025!!</h2>
        </div>
        <div style="text-align: center;">
            <iframe width="70%" height="400" src="https://www.youtube.com/embed/-2wCCmHyfvo" frameborder="0" allowfullscreen></iframe>
        </div>
        <section id="voyage" style="text-align: center;" class="content-section">
            <div style="display: flex; align-items: center; justify-content: center;">
                <h2><u>Le voyage</u></h2>
            </div>
            <p>Le voyage se déroulera du <u>18 au 25 janvier 2025</u> dans la station des Deux Alpes. 
                Voici ce qui est inclus dans le pack de base :</p>

            <ul>
                <li><span class="important">Transport :</span> Aller-retour en bus depuis Compiègne ou Paris jusqu'à la station.</li>
                <li><span class="important">Hébergement :</span> Logement en chambres de 6 à 8 personnes, avec tout le groupe dans
                     un ou deux bâtiments.</li>
                <li><span class="important">Forfait ski de 6 jours :</span> Accès illimité à l'ensemble du domaine skiable.</li>
            </ul>

            <p>Les participants pourront également ajouter des options telles que :</p>

            <ul>
                <li><span class="important">Location de matériel :</span> ski, snowboard, chaussures, bâtons, casque.</li>
                <li><span class="important">Cours de ski ou snowboard pour tous niveaux :</span> débutant, amateur, confirmé.</li>
                <li><span class="important">Activités :</span> Ski freestyle, ski compétition, bouées sur neige, parapente, etc.</li>
                <li><span class="important">Pack bouffe :</span> Repas Halal, Végétarien ou Classique, ainsi que des
                     petits déjeuners.</li>
            </ul>

            <p>Les prix sont de <span class="important">479 € pour les étudiants cotisants au BDE</span>, <span class="important">515 € pour 
                les alumni</span>, et <span class="important">525 € pour les extérieurs</span> (uniquement avec un alumni), 
                sans compter la taxe de séjour.</p>
            <div style="text-align: center; margin: 20px 0;">
                <img class="tarifs-ski-snow" src="{{ asset('storage/site/tarifs_ski_snow.png') }}" alt="Tarifs Ski et Snow">
            </div>
        </section>

        <section id="place" style="text-align: center;" class="content-section">
            <div style="display: flex; align-items: center; justify-content: center;">
                <h2><u>Comment obtenir sa place?</u></h2>
            </div>
            <p>La réservation pour le voyage se fait par un système de <span class="important">shotgun</span>, où il faut
                 se connecter rapidement pour réserver sa place. Le prochain shotgun aura lieu 
                 dans la nuit du <span class="important">1er au 2 octobre à 00h30</span>. Le lien pour y accéder sera partagé
                  sur les réseaux sociaux de l'asso! (Notre compte insta est en bas de la page!!)
            </p>
            <p>Pour valider l'inscription, un premier acompte de <span class="important">150 €</span> est nécessaire, 
                à régler par carte bancaire, virement ou chèque. Le paiement complet devra 
                être effectué <span class="important">une semaine après la confirmation de réservation</span>, sinon la
                 place sera remise en jeu lors d'un shotgun physique.
            </p>
            <p><span class="important">Les places partent très vite</span>, donc il est essentiel de se connecter rapidement !</p>
        </section>

        <section id="tombola" style="text-align: center;" class="content-section">
            <div style="display: flex; align-items: center; justify-content: center;">
                <h2><u>Participez à notre tombola !</u></h2>
            </div>
            <p>Nous organisons une tombola avec des prix incroyables à gagner. Tentez votre chance pour remporter l'un de ces lots !</p>
        
            <ul>
                <li><span class="important">Le pack de base offert</span></li>
                <li><span class="important">650 € de bons d'achat sur le site Younly</span> pour des voyages de ski de cette année</li>
                <li><span class="important">Des masques Salomon</span></li>
                <li>Et plein d'autres lots à découvrir !</li>
            </ul>
            <div style="text-align: center; margin: 20px 0;">
                <h2>Scanne ce QR code pour participer à la tombola!!</h2>
                <img src="{{ asset('storage/site/qr_tombola.png') }}" alt="QR Code Tombola" style="width: 30%;">
            </div>
            <p><span class="important">Bonne chance à tous les participants !</span></p>
        </section>
        
    </main>

    <footer>
        <section id="contact" style="margin-bottom: 40px;">
            <h2><u>Pour nous contacter</u></h2>
            <p>Des questions en plus?? Tu peux nous contacter sur le compte Instagram suivant: <a href="https://www.instagram.com/roger_skiutc?igsh=MTRxa3J4cHR3aGhnaA==" id="insta" target="_blank">@roger_skiutc</a></p>
        </section>
        <p>&copy; 2024 SKIUTC - UTC. Tous droits réservés.</p>
        <div style="display: flex; justify-content: center; margin-top: 20px;">
            <img src="{{ asset('storage/site/logo_utc.png') }}" alt="Logo UTC" style="width: 100px; height: auto;">
        </div>
    </footer>
</body>
</html>