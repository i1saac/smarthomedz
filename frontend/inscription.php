<?php
require_once 'C:\xampp\htdocs\smart home dz\backend\config.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $account_type = trim($_POST['type']);
    $result = registerUser($username, $email, $password, $name, $account_type);
    $message = $result['message'];
    if ($result['success']) {
        header('Location: connexion.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHomeDZ - Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        nav a:hover { color: #3B82F6; transition: color 0.2s; }
        .auth-form { transition: transform 0.3s, box-shadow 0.3s; }
        .auth-form:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
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
                <a href="./index.php" class="text-gray-800 hover:text-blue-500">Accueil</a>
                <a href="./design.php" class="text-gray-800 hover:text-blue-500">Design</a>
                <a href="./services.php" class="text-gray-800 hover:text-blue-500">Services</a>
                <a href="./products.php" class="text-gray-800 hover:text-blue-500">Produits</a>
                <a href="./connexion.php" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-rose-600">Connexion</a>
            </div>
        </div>
    </nav>

    <!-- Inscription Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Inscription</h2>
            <div class="max-w-md mx-auto">
                <?php if ($message): ?>
                    <p class="text-center text-red-500 mb-4"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
                <p class="text-center text-gray-600 mb-4" data-aos="fade-up" data-aos-delay="100">
                    Déjà inscrit ? <a href="./connexion.php" class="text-blue-500 hover:underline">Se connecter</a>
                </p>
                <div class="auth-form bg-white p-6 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4 text-center">Inscription</h3>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="signup-name" class="block text-gray-800 font-medium mb-2">Nom</label>
                            <input type="text" id="signup-name" name="name" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="signup-username" class="block text-gray-800 font-medium mb-2">Nom d’utilisateur</label>
                            <input type="text" id="signup-username" name="username" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="signup-email" class="block text-gray-800 font-medium mb-2">Email</label>
                            <input type="email" id="signup-email" name="email" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="signup-password" class="block text-gray-800 font-medium mb-2">Mot de passe</label>
                            <input type="password" id="signup-password" name="password" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="signup-type" class="block text-gray-800 font-medium mb-2">Type de compte</label>
                            <select id="signup-type" name="type" class="w-full p-2 border border-gray-300 rounded-md" required>
                                <option value="client">Client</option>
                                <option value="technician">Technicien</option>
                                <option value="store">Magasin</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 w-full">S’inscrire</button>
                    </form>
                </div>
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