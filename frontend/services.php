<?php
require_once 'C:\xampp\htdocs\smart home dz\backend\config.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\auth.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\reservations.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\services.php';

if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $icon = trim($_POST['icon']);
    $result = addService($name, $description, $icon);
    $service_message = $result['message'];
}

if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_technician'])) {
    $user_id = $_SESSION['user_id'];
    $service_id = trim($_POST['service_id']);
    $technician_id = trim($_POST['technician_id']);
    $reservation_date = trim($_POST['reservation_date']);
    $result = addReservation($user_id, 'technician', $service_id, null, null, $technician_id, $reservation_date);
    $reservation_message = $result['message'];
}

if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_favorite'])) {
    $user_id = $_SESSION['user_id'];
    $type = trim($_POST['type']);
    $item_id = trim($_POST['item_id']);
    $result = addFavorite($user_id, $type, $item_id);
    $favorite_message = $result['message'];
}

$services = getServices();
$technicians = $pdo->query("SELECT * FROM users WHERE account_type = 'technician'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHomeDZ - Nos Services Techniques</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        nav a:hover { color: #3B82F6; transition: color 0.2s; }
        .service-card, .technician-card { transition: transform 0.3s, box-shadow 0.3s; }
        .service-card:hover, .technician-card:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
        .chatbot-container { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
        .chatbot-button { background-color: #1E3A8A; color: white; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .chatbot-window { display: none; width: 320px; height: 400px; background-color: #F9FAFB; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); overflow: hidden; flex-direction: column; }
        .chatbot-window.active { display: flex; }
        .chatbot-header { background-color: #1E3A8A; color: white; padding: 12px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .chatbot-body { flex-grow: 1; padding: 12px; overflow-y: auto; background-color: #F9FAFB; }
        .chatbot-message { margin-bottom: 10px; padding: 8px 12px; border-radius: 8px; }
        .chatbot-message.user { background-color: #3B82F6; color: white; margin-left: 20%; margin-right: 10px; }
        .chatbot-message.bot { background-color: #E5E7EB; color: #1F2937; margin-right: 20%; margin-left: 10px; }
        .chatbot-input { padding: 12px; border-top: 1px solid #E5E7EB; }
        .chatbot-input input { width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 4px; }
        .search-bar { transition: box-shadow 0.3s; }
        .search-bar:focus-within { box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); }
        @media (max-width: 768px) {
            .service-container, .technician-grid { flex-direction: column; }
            .nav-links { flex-direction: column; display: none; }
            .nav-links.active { display: flex; }
            .hamburger { display: block; }
            .chatbot-window { width: 100%; max-width: 300px; height: 350px; }
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
    <?php if (isLoggedIn()): ?>
        <a href="./profile.php" class="text-gray-800 hover:text-blue-500">Mon Profil</a>
        <a href="./connexion.php?logout=1" class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-blue-900">Déconnexion</a>
    <?php else: ?>
        <a href="./connexion.php" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-rose-600">Connexion</a>
    <?php endif; ?>
</div>
        </div>
    </nav>

    <!-- Services Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Nos Services Techniques</h2>
            <p class="text-gray-600 text-center mb-8" data-aos="fade-up" data-aos-delay="100">Nos techniciens experts sont là pour installer, configurer et réparer vos systèmes intelligents.</p>
            <?php if (isLoggedIn() && $_SESSION['account_type'] === 'store'): ?>
                <div class="max-w-md mx-auto mb-8" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4">Ajouter un service</h3>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="service-name" class="block text-gray-800 font-medium mb-2">Nom du service</label>
                            <input type="text" id="service-name" name="name" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="service-description" class="block text-gray-800 font-medium mb-2">Description</label>
                            <textarea id="service-description" name="description" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="service-icon" class="block text-gray-800 font-medium mb-2">Icône (FontAwesome)</label>
                            <input type="text" id="service-icon" name="icon" class="w-full p-2 border border-gray-300 rounded-md" placeholder="ex. fa-hard-hat" required>
                        </div>
                        <button type="submit" name="add_service" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 w-full">Ajouter</button>
                    </form>
                    <?php if (isset($service_message)): ?>
                        <p class="text-center text-red-500 mt-4"><?php echo htmlspecialchars($service_message); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($favorite_message)): ?>
                <p class="text-center text-<?php echo strpos($favorite_message, 'succès') !== false ? 'green' : 'red'; ?>-500 mt-4"><?php echo htmlspecialchars($favorite_message); ?></p>
            <?php endif; ?>
            <div class="service-container flex flex-wrap justify-center gap-6">
                <?php foreach ($services as $service): ?>
                    <div class="service-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-aos="fade-up">
                        <i class="fas <?php echo htmlspecialchars($service['icon']); ?> text-4xl text-blue-900 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($service['description']); ?></p>
                        <a href="./services.php#technicians" class="text-blue-500 hover:underline">En savoir plus</a>
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" class="mt-4">
                                <input type="hidden" name="type" value="service">
                                <input type="hidden" name="item_id" value="<?php echo $service['id']; ?>">
                                <button type="submit" name="add_favorite" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Ajouter aux favoris</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <h3 class="text-2xl font-semibold text-center text-gray-800 mt-8 mb-4" data-aos="fade-up" id="technicians">Nos Techniciens</h3>
            <div class="search-bar max-w-lg mx-auto mb-8" data-aos="fade-up" data-aos-delay="100">
                <input type="text" id="technician-search" placeholder="Rechercher un technicien par nom, ville ou spécialité..." class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="technician-grid flex flex-wrap justify-center gap-6" id="technician-grid" data-aos="fade-up" data-aos-delay="200">
                <?php foreach ($technicians as $technician): ?>
                    <div class="technician-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" 
                         data-name="<?php echo htmlspecialchars($technician['name']); ?>" 
                         data-city="<?php echo htmlspecialchars($technician['city'] ?? 'Alger'); ?>" 
                         data-specialty="<?php echo htmlspecialchars($technician['specialty'] ?? 'Spécialiste IoT'); ?>">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="<?php echo htmlspecialchars($technician['name']); ?>" 
                             class="w-24 h-24 object-cover rounded-full mx-auto mb-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($technician['name']); ?></h4>
                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($technician['city'] ?? 'Alger'); ?> - <?php echo htmlspecialchars($technician['specialty'] ?? 'Spécialiste IoT'); ?></p>
                        <p class="text-gray-600 mb-4">Note : ★★★★☆</p>
                        <?php if (isLoggedIn()): ?>
                            <form method="POST">
                                <input type="hidden" name="technician_id" value="<?php echo $technician['id']; ?>">
                                <input type="hidden" name="service_id" value="<?php echo $services[0]['id'] ?? ''; ?>">
                                <div class="mb-4">
                                    <label for="reservation-date-<?php echo $technician['id']; ?>" class="block text-gray-800 font-medium mb-2">Date</label>
                                    <input type="datetime-local" id="reservation-date-<?php echo $technician['id']; ?>" name="reservation_date" class="w-full p-2 border border-gray-300 rounded-md" required>
                                </div>
                                <button type="submit" name="reserve_technician" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600">Réserver ce technicien</button>
                            </form>
                        <?php else: ?>
                            <a href="./connexion.php" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600">Se connecter pour réserver</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (isset($reservation_message)): ?>
                <p class="text-center text-red-500 mt-4"><?php echo htmlspecialchars($reservation_message); ?></p>
            <?php endif; ?>
            <a href="#help" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 block w-max mx-auto mt-8" data-aos="fade-up">Publier une demande d’aide</a>
        </div>
    </section>

    <!-- Chatbot -->
    <div class="chatbot-container">
        <div class="chatbot-button" id="chatbot-toggle">
            <i class="fas fa-comment-alt text-2xl"></i>
        </div>
        <div class="chatbot-window" id="chatbot-window">
            <div class="chatbot-header">
                <span>Support SmartHomeDZ</span>
                <button id="chatbot-close" class="text-white"><i class="fas fa-times"></i></button>
            </div>
            <div class="chatbot-body" id="chatbot-messages">
                <div class="chatbot-message bot">Bonjour ! Comment puis-je vous aider avec vos appareils intelligents ?</div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input" placeholder="Décrivez votre problème..." autocomplete="off">
            </div>
        </div>
    </div>

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

        const chatbotToggle = document.querySelector('#chatbot-toggle');
        const chatbotWindow = document.querySelector('#chatbot-window');
        const chatbotClose = document.querySelector('#chatbot-close');
        const chatbotInput = document.querySelector('#chatbot-input');
        const chatbotMessages = document.querySelector('#chatbot-messages');

        chatbotToggle.addEventListener('click', () => {
            chatbotWindow.classList.toggle('active');
        });

        chatbotClose.addEventListener('click', () => {
            chatbotWindow.classList.remove('active');
        });

        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && chatbotInput.value.trim()) {
                const userMessage = document.createElement('div');
                userMessage.className = 'chatbot-message user';
                userMessage.textContent = chatbotInput.value;
                chatbotMessages.appendChild(userMessage);
                const botMessage = document.createElement('div');
                botMessage.className = 'chatbot-message bot';
                botMessage.textContent = 'Merci de votre message ! Un technicien vous répondra bientôt.';
                chatbotMessages.appendChild(botMessage);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                chatbotInput.value = '';
            }
        });

        const searchInput = document.querySelector('#technician-search');
        const technicianCards = document.querySelectorAll('.technician-card');
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            technicianCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const city = card.dataset.city.toLowerCase();
                const specialty = card.dataset.specialty.toLowerCase();
                if (name.includes(query) || city.includes(query) || specialty.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>