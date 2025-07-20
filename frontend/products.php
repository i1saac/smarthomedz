<?php
require_once 'C:\xampp\htdocs\smart home dz\backend\config.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\auth.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\reservations.php';
require_once 'C:\xampp\htdocs\smart home dz\backend\products.php';

if (isLoggedIn() && $_SESSION['account_type'] === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image_url = trim($_POST['image_url']);
    // Vérifier si l’utilisateur a un magasin associé
    try {
        $stmt = $pdo->prepare("SELECT id FROM stores WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($store) {
            $store_ids = [$store['id']];
            $result = addProduct($name, $description, $price, $image_url, $store_ids);
            $product_message = $result['message'];
        } else {
            $product_message = "Erreur : Aucun magasin associé à votre compte. Veuillez configurer votre magasin.";
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification du magasin : " . $e->getMessage());
        $product_message = "Erreur lors de l’ajout du produit. Veuillez réessayer.";
    }
}

if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_product'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = trim($_POST['product_id']);
    $store_id = trim($_POST['store_id']);
    $reservation_date = trim($_POST['reservation_date']);
    $result = addReservation($user_id, 'product', null, $product_id, $store_id, null, $reservation_date);
    $reservation_message = $result['message'];
}

if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_favorite'])) {
    $user_id = $_SESSION['user_id'];
    $type = trim($_POST['type']);
    $item_id = trim($_POST['item_id']);
    $result = addFavorite($user_id, $type, $item_id);
    $favorite_message = $result['message'];
}

$stores = getStores();
$selected_store = $_GET['store_id'] ?? null;
$products = getProducts($selected_store);

// Si l’utilisateur est un magasin, limiter les magasins affichés à son propre magasin
if (isLoggedIn() && $_SESSION['account_type'] === 'store') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM stores WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du magasin de l’utilisateur : " . $e->getMessage());
        $stores = [];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHomeDZ - Nos Produits Connectés</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        nav a:hover { color: #3B82F6; transition: color 0.2s; }
        .product-card, .store-card { transition: transform 0.3s, box-shadow 0.3s; }
        .product-card:hover, .store-card:hover { transform: translateY(-4px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); }
        .store-card.active { border: 2px solid #3B82F6; background-color: #EBF4FF; }
        .search-bar { transition: box-shadow 0.3s; }
        .search-bar:focus-within { box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); }
        @media (max-width: 768px) {
            .product-grid, .store-grid { flex-direction: column; }
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
                <?php if (isLoggedIn()): ?>
                    <a href="./profile.php" class="text-gray-800 hover:text-blue-500">Mon Profil</a>
                    <a href="./index.php?logout=1" class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-blue-900">Déconnexion</a>
                <?php else: ?>
                    <a href="./connexion.php" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-rose-600">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Products Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-8" data-aos="fade-up">Nos Produits Connectés</h2>
            <p class="text-black text-center mb-8" data-aos="fade-up" data-aos-delay="100">Explorez notre catalogue de produits pour une maison intelligente.</p>
            <?php if (isLoggedIn() && $_SESSION['account_type'] === 'store'): ?>
                <div class="max-w-md mx-auto mb-8" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4">Ajouter un produit</h3>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="product-name" class="block text-gray-800 font-medium mb-2">Nom du produit</label>
                            <input type="text" id="product-name" name="name" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="product-description" class="block text-gray-800 font-medium mb-2">Description</label>
                            <textarea id="product-description" name="description" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="product-price" class="block text-gray-800 font-medium mb-2">Prix (€)</label>
                            <input type="number" id="product-price" name="price" step="0.01" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="product-image" class="block text-gray-800 font-medium mb-2">URL de l’image</label>
                            <input type="url" id="product-image" name="image_url" class="w-full p-2 border border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-800 font-medium mb-2">Votre magasin</label>
                            <?php if (!empty($stores)): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($stores[0]['name']); ?> (<?php echo htmlspecialchars($stores[0]['city']); ?>)</p>
                                <input type="hidden" name="store_ids[]" value="<?php echo $stores[0]['id']; ?>">
                            <?php else: ?>
                                <p class="text-red-500">Aucun magasin associé à votre compte. Veuillez configurer votre magasin.</p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="add_product" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600 w-full">Ajouter</button>
                    </form>
                    <?php if (isset($product_message)): ?>
                        <p class="text-center text-<?php echo strpos($product_message, 'succès') !== false ? 'green' : 'red'; ?>-500 mt-4"><?php echo htmlspecialchars($product_message); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($favorite_message)): ?>
                <p class="text-center text-<?php echo strpos($favorite_message, 'succès') !== false ? 'green' : 'red'; ?>-500 mt-4"><?php echo htmlspecialchars($favorite_message); ?></p>
            <?php endif; ?>
            <div class="search-bar max-w-lg mx-auto mb-8" data-aos="fade-up" data-aos-delay="200">
                <input type="text" id="store-search" placeholder="Rechercher un magasin par nom ou ville..." class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <h3 class="text-2xl font-semibold text-center text-gray-800 mb-4" data-aos="fade-up" data-aos-delay="300">Magasins Disponibles</h3>
            <div class="store-grid flex flex-wrap justify-center gap-6 mb-12" id="store-grid" data-aos="fade-up" data-aos-delay="400">
                <?php foreach ($stores as $store): ?>
                    <div class="store-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3 cursor-pointer <?php echo $selected_store == $store['id'] ? 'active' : ''; ?>" data-store="<?php echo $store['id']; ?>">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($store['name']); ?></h4>
                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($store['city']); ?> - <?php echo htmlspecialchars($store['address']); ?></p>
                        <p class="text-gray-600">Produits disponibles</p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="product-grid flex flex-wrap justify-center gap-6" id="product-grid">
                <?php if (empty($products)): ?>
                    <p class="text-gray-600 text-center w-full" data-aos="fade-up">Aucun produit disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card bg-white p-6 rounded-lg shadow-md w-full md:w-1/3" data-stores="<?php 
                            try {
                                $stmt = $pdo->prepare("SELECT store_id FROM product_store WHERE product_id = ?");
                                $stmt->execute([$product['id']]);
                                $store_ids = array_column($stmt->fetchAll(), 'store_id');
                                echo implode(',', $store_ids);
                            } catch (PDOException $e) {
                                error_log("Erreur lors de la récupération des magasins pour le produit {$product['id']} : " . $e->getMessage());
                                echo '';
                            }
                        ?>" data-aos="fade-up">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover rounded-md mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="text-gray-800 font-bold mb-4"><?php echo number_format($product['price'], 2); ?> €</p>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="mb-4">
                                        <label for="store-id-<?php echo $product['id']; ?>" class="block text-gray-800 font-medium mb-2">Magasin</label>
                                        <select name="store_id" id="store-id-<?php echo $product['id']; ?>" class="w-full p-2 border border-gray-300 rounded-md" required>
                                            <?php 
                                            try {
                                                $stmt = $pdo->prepare("SELECT s.id, s.name FROM stores s JOIN product_store ps ON s.id = ps.store_id WHERE ps.product_id = ?");
                                                $stmt->execute([$product['id']]);
                                                $product_stores = $stmt->fetchAll();
                                                if (empty($product_stores)) {
                                                    echo '<option value="">Aucun magasin disponible</option>';
                                                } else {
                                                    foreach ($product_stores as $store): ?>
                                                        <option value="<?php echo $store['id']; ?>"><?php echo htmlspecialchars($store['name']); ?></option>
                                                    <?php endforeach;
                                                }
                                            } catch (PDOException $e) {
                                                error_log("Erreur lors de la récupération des magasins pour le produit {$product['id']} : " . $e->getMessage());
                                                echo '<option value="">Erreur de chargement des magasins</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="reservation-date-<?php echo $product['id']; ?>" class="block text-gray-800 font-medium mb-2">Date de réservation</label>
                                        <input type="datetime-local" id="reservation-date-<?php echo $product['id']; ?>" name="reservation_date" class="w-full p-2 border border-gray-300 rounded-md" required>
                                    </div>
                                    <button type="submit" name="reserve_product" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600">Commander</button>
                                </form>
                                <form method="POST" class="mt-4">
                                    <input type="hidden" name="type" value="product">
                                    <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="add_favorite" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Ajouter aux favoris</button>
                                </form>
                            <?php else: ?>
                                <a href="./connexion.php" class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-rose-600">Se connecter pour commander</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (isset($reservation_message)): ?>
                <p class="text-center text-<?php echo strpos($reservation_message, 'succès') !== false ? 'green' : 'red'; ?>-500 mt-4"><?php echo htmlspecialchars($reservation_message); ?></p>
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

        const storeSearch = document.querySelector('#store-search');
        const storeCards = document.querySelectorAll('.store-card');
        const productCards = document.querySelectorAll('.product-card');
        let selectedStore = <?php echo json_encode($selected_store); ?>;

        storeSearch.addEventListener('input', () => {
            const query = storeSearch.value.toLowerCase();
            storeCards.forEach(card => {
                const name = card.querySelector('h4').textContent.toLowerCase();
                const city = card.querySelector('p').textContent.split(' - ')[0].toLowerCase();
                if (name.includes(query) || city.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            if (query) {
                selectedStore = null;
                storeCards.forEach(card => card.classList.remove('active'));
                productCards.forEach(card => card.style.display = 'block');
            }
        });

        storeCards.forEach(card => {
            card.addEventListener('click', () => {
                selectedStore = card.dataset.store;
                storeCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                productCards.forEach(product => {
                    const stores = product.dataset.stores.split(',');
                    product.style.display = stores.includes(selectedStore) ? 'block' : 'none';
                });
            });
        });
    </script>
</body>
</html>