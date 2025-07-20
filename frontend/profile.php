<?php
require_once 'C:\xampp\htdocs\smart home dz\backend\config.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\auth.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\reservations.php';

if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserProfile($user_id);
$recent_reservations = getReservations($user_id);
$favorites = getFavorites($user_id);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone']);
    $profile_picture = trim($_POST['profile_picture']);
    $result = updateProfile($user_id, $phone, $profile_picture);
    $message = $result['message'];
    if ($result['success']) {
        $user = getUserProfile($user_id); // Rafraîchir les données
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_favorite'])) {
    $type = trim($_POST['type']);
    $item_id = trim($_POST['item_id']);
    $result = addFavorite($user_id, $type, $item_id);
    $message = $result['message'];
    if ($result['success']) {
        $favorites = getFavorites($user_id); // Rafraîchir les favoris
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $type = trim($_POST['type']);
    $item_id = trim($_POST['item_id']);
    $result = removeFavorite($user_id, $type, $item_id);
    $message = $result['message'];
    if ($result['success']) {
        $favorites = getFavorites($user_id); // Rafraîchir les favoris
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHomeDZ - Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        nav a:hover { color: #3B82F6; transition: color 0.2s; }
        .profile-card, .reservation-card, .favorite-card { transition: transform 0.3s, box-shadow 0.3s; }
        .profile-card:hover, .reservation-card:hover, .favorite-card:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
        .profile-picture { object-fit: cover; border-radius: 50%; }
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

    <!-- Profile Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Mon Profil</h2>
            <?php if ($message): ?>
                <p class="text-center text-<?php echo strpos($message, 'succès') !== false ? 'green' : 'red'; ?>-500 mb-4"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <div class="max-w-3xl mx-auto">
                <!-- User Info -->
                <div class="profile-card bg-white p-6 rounded-lg shadow-md mb-8" data-aos="fade-up">
                    <div class="flex items-center space-x-6">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: 'https://via.placeholder.com/150'); ?>" alt="Photo de profil" class="profile-picture w-24 h-24">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="text-gray-600">Nom d’utilisateur : <?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="text-gray-600">Email : <?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-gray-600">Type de compte : <?php echo htmlspecialchars($user['account_type']); ?></p>
                            <p class="text-gray-600">Téléphone : <?php echo htmlspecialchars($user['phone'] ?: 'Non défini'); ?></p>
                        </div>
                    </div>
                    <form method="POST" class="mt-6">
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-800 font-medium mb-2">Numéro de téléphone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Ex. : +213123456789">
                        </div>
                        <div class="mb-4">
                            <label for="profile_picture" class="block text-gray-800 font-medium mb-2">URL de la photo de profil</label>
                            <input type="url" id="profile_picture" name="profile_picture" value="<?php echo htmlspecialchars($user['profile_picture'] ?: ''); ?>" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Ex. : https://example.com/photo.jpg">
                        </div>
                        <button type="submit" name="update_profile" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 w-full">Mettre à jour</button>
                    </form>
                </div>

                <!-- Reservations -->
                <h3 class="text-2xl font-semibold text-gray-800 mb-4" data-aos="fade-up">Mes Réservations</h3>
                <?php if (empty($recent_reservations)): ?>
                    <p class="text-gray-600 mb-8" data-aos="fade-up">Aucune réservation pour le moment.</p>
                <?php else: ?>
                    <div class="flex flex-wrap justify-center gap-6 mb-8">
                        <?php foreach ($recent_reservations as $reservation): ?>
                            <div class="reservation-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">
                                    <?php echo $reservation['type'] === 'technician' ? 'Réservation Technicien' : 'Réservation Produit'; ?>
                                </h4>
                                <p class="text-gray-600 mb-2">
                                    <?php echo $reservation['type'] === 'technician' ? htmlspecialchars($reservation['service_name'] ?? 'Service') : htmlspecialchars($reservation['product_name'] ?? 'Produit'); ?>
                                </p>
                                <p class="text-gray-600 mb-2">Date : <?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></p>
                                <p class="text-gray-600 mb-2">
                                    <?php echo $reservation['type'] === 'technician' ? 'Technicien : ' . htmlspecialchars($reservation['technician_name'] ?? 'Non assigné') : 'Magasin : ' . htmlspecialchars($reservation['store_name'] ?? 'Non défini'); ?>
                                </p>
                                <p class="text-gray-600 mb-4">Statut : <?php echo htmlspecialchars($reservation['status']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Favorites -->
                <h3 class="text-2xl font-semibold text-gray-800 mb-4" data-aos="fade-up">Mes Favoris</h3>
                <?php if (empty($favorites)): ?>
                    <p class="text-gray-600 mb-8" data-aos="fade-up">Aucun favori pour le moment.</p>
                <?php else: ?>
                    <div class="flex flex-wrap justify-center gap-6">
                        <?php foreach ($favorites as $favorite): ?>
                            <div class="favorite-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($favorite['item_name']); ?></h4>
                                <p class="text-gray-600 mb-2">Type : <?php echo $favorite['type'] === 'service' ? 'Service' : 'Produit'; ?></p>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($favorite['item_description']); ?></p>
                                <form method="POST">
                                    <input type="hidden" name="type" value="<?php echo $favorite['type']; ?>">
                                    <input type="hidden" name="item_id" value="<?php echo $favorite['item_id']; ?>">
                                    <button type="submit" name="remove_favorite" class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-blue-900">Retirer des favoris</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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