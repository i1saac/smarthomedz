<?php
require_once 'C:\xampp\htdocs\smart home dz\backend\config.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\auth.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\reservations.php';

$recent_reservations = [];
if (isLoggedIn()) {
    $recent_reservations = getReservations($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHomeDZ - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        nav a:hover { color: #3B82F6; transition: color 0.2s; }
        .hero-section { background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; }
        .cta-button { transition: background-color 0.3s; }
        .reservation-card { transition: transform 0.3s, box-shadow 0.3s; }
        .reservation-card:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        @media (max-width: 768px) {
            .nav-links { flex-direction: column; display: none; }
            .nav-links.active { display: flex; }
            .hamburger { display: block; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold text-blue-900">SmartHomeDZ</div>
            <div class="hamburger hidden md:hidden cursor-pointer">
                <i class="fas fa-bars text-xl text-blue-900"></i>
            </div>
            <div class="nav-links flex space-x-6 items-center">
                <a href="./design.php" class="text-gray-800 hover:text-blue-500">Design</a>
                <a href="./services.php" class="text-gray-800 hover:text-blue-500">Services</a>
                <a href="./products.php" class="text-gray-800 hover:text-blue-500">Produits</a>
                <?php if (isLoggedIn()): ?>
                     <a href="./profile.php" class="text-gray-800 hover:text-blue-500">Mon Profil</a>
                     <a href="./connexion.php?logout=1" class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-blue-900">Déconnexion</a>
               <?php else: ?>
                     <a href="./connexion.php" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-rose-600">Connexion</a>
               <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-16 text-center text-white">
        <div class="container mx-auto px-4">
            <?php if (isLoggedIn()): ?>
                <h1 class="text-4xl md:text-5xl font-bold mb-4" data-aos="fade-up">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
                <p class="text-lg md:text-xl mb-6" data-aos="fade-up" data-aos-delay="100">Gérez vos réservations et découvrez nos dernières offres.</p>
                <a href="./profile.php" class="cta-button bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600" data-aos="fade-up" data-aos-delay="200">Voir mon profil</a>
            <?php else: ?>
                <h1 class="text-4xl md:text-5xl font-bold mb-4" data-aos="fade-up">Bienvenue chez SmartHomeDZ</h1>
                <p class="text-lg md:text-xl mb-6" data-aos="fade-up" data-aos-delay="100">Votre partenaire pour une maison intelligente et connectée.</p>
                <a href="#start" class="cta-button bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 hover:text-blue-500" data-aos="fade-up" data-aos-delay="200">commencé</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features or Reservations Section -->
    <section class="py-12 bg-gray-50" id="start">
        <div class="container mx-auto px-4">
            <?php if (isLoggedIn()): ?>
                <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Vos Réservations Récentes</h2>
                <?php if (empty($recent_reservations)): ?>
                    <p class="text-gray-600 text-center mb-8" data-aos="fade-up" data-aos-delay="100">Aucune réservation pour le moment.</p>
                <?php else: ?>
                    <div class="flex flex-wrap justify-center gap-6">
                        <?php foreach (array_slice($recent_reservations, 0, 3) as $reservation): ?>
                            <div class="reservation-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                    <?php echo $reservation['type'] === 'technician' ? 'Réservation Technicien' : 'Réservation Produit'; ?>
                                </h3>
                                <p class="text-gray-600 mb-2">
                                    <?php echo $reservation['type'] === 'technician' ? htmlspecialchars($reservation['service_name'] ?? 'Service') : htmlspecialchars($reservation['product_name'] ?? 'Produit'); ?>
                                </p>
                                <p class="text-gray-600 mb-2">Date : <?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></p>
                                <p class="text-gray-600 mb-4">Statut : <?php echo htmlspecialchars($reservation['status']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Pourquoi choisir SmartHomeDZ ?</h2>
                <div class="flex flex-wrap justify-center gap-6">
                    <!-- Service Card 1 -->
                <div class="service-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up">
                    <i class="fas fa-hard-hat text-4xl text-blue-900 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Design</h3>
                    <p class="text-gray-600 mb-4">Conception sur mesure pour votre maison intelligente, adaptée à vos besoins.</p>
                    <a href="./design.php" class="text-blue-500 hover:underline">En savoir plus</a>
                </div>
                <!-- Service Card 2 -->
                <div class="service-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-tools text-4xl text-blue-900 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Techniciens</h3>
                    <p class="text-gray-600 mb-4">Installation professionnelle et support technique pour tous vos appareils.</p>
                    <a href="./services.php" class="text-blue-500 hover:underline">En savoir plus</a>
                </div>
                <!-- Service Card 3 -->
                <div class="service-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-shopping-cart text-4xl text-blue-900 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Produits</h3>
                    <p class="text-gray-600 mb-4">Large gamme de produits connectés pour une maison moderne et sécurisée.</p>
                    <a href="./products.php" class="text-blue-500 hover:underline">En savoir plus</a>
                </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

   <!-- Footer -->
    <footer class="bg-blue-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-between items-center">
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-2">SmartHomeDZ</h3>
                    <p class="text-gray-200">Votre partenaire pour une maison intelligente et connectée.</p>
                </div>
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-2">Liens utiles</h3>
                    <ul class="space-y-2">
                        <li><a href="#about" class="hover:text-rose-500 hover:text-blue-500 ">À propos</a></li>
                        <li><a href="#contact" class="hover:text-rose-500 hover:text-blue-500 ">Contact</a></li>
                        <li><a href="#faq" class="hover:text-rose-500 hover:text-blue-500 ">FAQ</a></li>
                    </ul>
                </div>
                <div class="w-full md:w-1/3">
                    <h3 class="text-lg font-semibold mb-2">Suivez-nous</h3>
                    <div class="flex space-x-4">
                        <a href="#facebook" class="text-2xl hover:text-rose-500 hover:text-blue-500"><i class="fab fa-facebook"></i></a>
                        <a href="#twitter" class="text-2xl hover:text-rose-500 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
                        <a href="#instagram" class="text-2xl hover:text-rose-500 hover:text-blue-500"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-6 text-center text-gray-300">
                <p>© 2025 SmartHomeDZ. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    </script>
</body>
</html>