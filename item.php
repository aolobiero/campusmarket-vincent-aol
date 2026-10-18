<?php
require_once __DIR__ . '/db.php';

function item_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$samples = require __DIR__ . '/sample-products.php';
$sampleId = filter_input(INPUT_GET, 'sample', FILTER_VALIDATE_INT);
$listingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = null;
$isSample = false;

if ($sampleId !== null && $sampleId !== false) {
    foreach ($samples as $sample) {
        if ($sample['id'] === $sampleId) {
            $item = [
                'id' => 'product-' . $sample['id'],
                'title' => $sample['title'],
                'price' => $sample['price'],
                'category' => $sample['category'],
                'condition' => $sample['condition'],
                'image_url' => $sample['image_url'],
                'seller_name' => 'Campus seller',
                'location_label' => 'Campus marketplace',
                'detail_label' => 'Contact seller for details',
                'is_verified' => false,
            ];
            $isSample = true;
            break;
        }
    }
} elseif ($listingId !== null && $listingId !== false && $pdo instanceof PDO) {
    $query = $pdo->prepare('SELECT id, title, price, category, condition_label, location_label, seller_name, image_url, detail_label, is_verified FROM listings WHERE id = :id LIMIT 1');
    $query->execute(['id' => $listingId]);
    $listing = $query->fetch();
    if ($listing) {
        $item = [
            'id' => 'catalog-' . $listing['id'],
            'title' => $listing['title'],
            'price' => $listing['price'],
            'category' => $listing['category'],
            'condition' => $listing['condition_label'],
            'image_url' => $listing['image_url'],
            'seller_name' => $listing['seller_name'],
            'location_label' => $listing['location_label'],
            'detail_label' => $listing['detail_label'],
            'is_verified' => (bool) $listing['is_verified'],
        ];
    }
}

if (!$item) {
    http_response_code(404);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View item details on CampusMarket, the student marketplace.">
    <title><?= $item ? item_e($item['title']) . ' | CampusMarket' : 'Item not found | CampusMarket' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#1e1e24;--muted:#666873;--paper:#f9f9fb;--orange:#e65f2b;--teal:#005f55;--olive:#4e6e58;--line:rgba(0,0,0,.07);--shadow:0 4px 20px -2px rgba(0,0,0,.07)}
        *{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font-family:"DM Sans",sans-serif;line-height:1.55}a{color:inherit;text-decoration:none}button{font:inherit}.container{width:min(1120px,calc(100% - 36px));margin:auto}
        header{position:sticky;top:0;z-index:5;border-bottom:1px solid var(--line);background:rgba(255,255,255,.86);backdrop-filter:blur(8px)}.nav{display:flex;align-items:center;justify-content:space-between;min-height:70px}.brand{font-family:"Plus Jakarta Sans",sans-serif;font-size:1.15rem;font-weight:800}.brand-mark{display:inline-grid;place-items:center;width:34px;height:34px;margin-right:8px;border-radius:10px;color:#fff;background:var(--teal)}.back{color:var(--teal);font-weight:700}
        main{padding:42px 0 70px}.crumb{margin:0 0 22px;color:var(--muted);font-size:.88rem}.crumb a{color:var(--teal);font-weight:700}.detail{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:42px;align-items:start}.photo-wrap{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:var(--shadow)}.photo-wrap img{display:block;width:100%;aspect-ratio:1/1;object-fit:cover}.detail-panel{padding:10px 0}.badge{display:inline-flex;padding:6px 10px;border-radius:999px;color:var(--olive);background:#e8efe9;font-size:.76rem;font-weight:700}.category{margin:18px 0 5px;color:var(--teal);font-size:.78rem;font-weight:700;text-transform:uppercase}.title{margin:0;font-family:"Plus Jakarta Sans",sans-serif;font-size:clamp(1.8rem,4vw,2.7rem);line-height:1.15}.price{margin:17px 0;color:var(--ink);font-family:"Plus Jakarta Sans",sans-serif;font-size:1.7rem;font-weight:800}.facts{display:grid;gap:0;margin:22px 0;border-top:1px solid var(--line)}.fact{display:flex;justify-content:space-between;gap:16px;padding:13px 0;border-bottom:1px solid var(--line)}.fact span:first-child{color:var(--muted)}.fact strong{text-align:right}.seller{margin:22px 0;padding:18px;border:1px solid var(--line);border-radius:12px;background:#fff}.seller small{display:block;color:var(--muted)}.seller strong{font-family:"Plus Jakarta Sans",sans-serif}.actions{display:grid;gap:10px}.button{display:flex;align-items:center;justify-content:center;min-height:48px;padding:12px 18px;border:0;border-radius:12px;color:#fff;background:var(--orange);cursor:pointer;font-weight:700}.button:hover{background:#cf4f20}.button.secondary{border:1px solid var(--teal);color:var(--teal);background:transparent}.message{margin-top:12px;color:var(--olive);font-weight:700}.not-found{padding:80px 0;text-align:center}.not-found h1{font-family:"Plus Jakarta Sans",sans-serif}
        @media(max-width:760px){main{padding:26px 0 50px}.detail{grid-template-columns:1fr;gap:20px}.photo-wrap img{aspect-ratio:4/3}.detail-panel{padding:0}.title{font-size:1.8rem}}
        .photo-wrap img{object-fit:contain;background:#f1f3f2}
    </style>
    <script>try{document.documentElement.classList.add(localStorage.getItem('campusmarket-theme')==='light'?'light-theme':'dark-theme')}catch(error){document.documentElement.classList.add('dark-theme')}</script>
    <link rel="stylesheet" href="assets/theme.css">
    <script src="assets/theme.js" defer></script>
</head>
<body>
<header><nav class="container nav" aria-label="Main navigation"><a class="brand" href="index.php"><span class="brand-mark"><img class="brand-symbol" src="assets/brand-mark.svg" alt=""></span>CampusMarket</a><a class="back" href="index.php#student-products">&larr; Back to marketplace</a></nav></header>
<main class="container">
<?php if (!$item): ?>
    <section class="not-found"><h1>We couldn't find that item.</h1><p>It may have been removed or the link may be out of date.</p><a class="button" href="index.php#student-products">Browse the marketplace</a></section>
<?php else: ?>
    <p class="crumb"><a href="index.php">CampusMarket</a> / <?= item_e(ucwords(str_replace('-', ' ', $item['category']))) ?> / <?= item_e($item['title']) ?></p>
    <article class="detail">
        <div class="photo-wrap"><img src="<?= item_e($item['image_url']) ?>" alt="<?= item_e($item['title']) ?>"></div>
        <div class="detail-panel">
            <span class="badge"><?= $isSample ? 'Campus pick' : ($item['is_verified'] ? 'Verified Student Seller' : 'Student listing') ?></span>
            <p class="category"><?= item_e(ucwords(str_replace('-', ' ', $item['category']))) ?></p>
            <h1 class="title"><?= item_e($item['title']) ?></h1>
            <p class="price">KSh <?= number_format((float) $item['price']) ?></p>
            <div class="facts"><div class="fact"><span>Condition</span><strong><?= item_e($item['condition']) ?></strong></div><div class="fact"><span>Pickup</span><strong><?= item_e($item['location_label']) ?></strong></div><div class="fact"><span>Details</span><strong><?= item_e($item['detail_label']) ?></strong></div></div>
            <div class="seller"><small>Listed by</small><strong><?= item_e($item['seller_name']) ?></strong></div>
            <div class="actions"><button class="button" id="add-to-cart" type="button">Add to cart</button><a class="button secondary" href="index.php#student-products">Continue browsing</a></div>
            <p class="message" id="cart-message" role="status" aria-live="polite"></p>
        </div>
    </article>
<?php endif; ?>
</main>
<?php if ($item): ?>
<script>
    const addButton = document.getElementById('add-to-cart');
    addButton.addEventListener('click', function () {
        const cartKey = 'campusmarket-cart';
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const item = {
            id: <?= json_encode($item['id']) ?>,
            title: <?= json_encode($item['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            price: <?= json_encode((float) $item['price']) ?>,
            image: <?= json_encode($item['image_url'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            category: <?= json_encode($item['category'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
        };
        if (!cart.some(function (saved) { return saved.id === item.id; })) cart.push(item);
        localStorage.setItem(cartKey, JSON.stringify(cart));
        addButton.textContent = 'Added to cart';
        document.getElementById('cart-message').textContent = 'Added to your cart.';
    });
</script>
<?php endif; ?>
</body>
</html>
