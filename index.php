<?php
require_once __DIR__ . '/db.php';

$searchTerm = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$minPrice = max(0, (float) ($_GET['min_price'] ?? 0));
$maxPrice = max(0, (float) ($_GET['max_price'] ?? 0));
$sortOrder = $_GET['sort'] ?? 'newest';
$listings = [];
$catalogReady = false;

if ($pdo instanceof PDO) {
	$catalogReady = true;
	$sql = 'SELECT * FROM listings WHERE 1=1';
	$parameters = [];

	if ($searchTerm !== '') {
		$sql .= ' AND (title LIKE :search OR category LIKE :search OR seller_name LIKE :search)';
		$parameters['search'] = '%' . $searchTerm . '%';
	}
	if ($categoryFilter !== '') {
		$sql .= ' AND category = :category';
		$parameters['category'] = $categoryFilter;
	}
	if ($minPrice > 0) {
		$sql .= ' AND price >= :min_price';
		$parameters['min_price'] = $minPrice;
	}
	if ($maxPrice > 0) {
		$sql .= ' AND price <= :max_price';
		$parameters['max_price'] = $maxPrice;
	}

	$sql .= $sortOrder === 'price_low' ? ' ORDER BY price ASC, id DESC' : ($sortOrder === 'price_high' ? ' ORDER BY price DESC, id DESC' : ' ORDER BY created_at DESC, id DESC');
	$query = $pdo->prepare($sql);
	$query->execute($parameters);
	$listings = $query->fetchAll();
}

function e(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="CampusMarket is the trusted marketplace for students to buy, sell, and discover more on campus.">
	<title>CampusMarket | Your campus, your marketplace</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
	<style>
		:root{--ink:#142b38;--muted:#60727a;--paper:#f7faf8;--white:#fff;--line:#dce7e3;--teal:#087f78;--teal-dark:#075b59;--coral:#f07c63;--shadow:0 18px 45px rgba(20,43,56,.1)}
		*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:var(--ink);background:var(--paper);font-family:"DM Sans",sans-serif;line-height:1.5}a{color:inherit;text-decoration:none}button,input{font:inherit}.container{width:min(1160px,calc(100% - 40px));margin:auto}
		.topbar{background:var(--ink);color:#d9e7e3;font-size:.82rem}.topbar .container{display:flex;justify-content:space-between;padding:9px 0}.topbar a{color:#fff;font-weight:700}header{background:var(--white);border-bottom:1px solid var(--line)}.nav{min-height:78px;display:flex;align-items:center;gap:34px}.brand{display:flex;align-items:center;gap:10px;font-family:"Space Grotesk",sans-serif;font-size:1.3rem;font-weight:700;letter-spacing:-.04em;white-space:nowrap}.brand-mark{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;color:#fff;background:var(--teal);font-size:1.1rem}.nav-links{display:flex;gap:25px;margin-right:auto;color:var(--muted);font-size:.92rem;font-weight:600}.nav-links a:hover{color:var(--teal)}.nav-actions{display:flex;align-items:center;gap:15px}.sell-link{color:var(--teal);font-weight:700;font-size:.92rem}.button{display:inline-flex;justify-content:center;align-items:center;gap:8px;border:0;border-radius:7px;padding:12px 18px;color:#fff;background:var(--teal);cursor:pointer;font-weight:700;transition:transform .2s,background .2s}.button:hover{background:var(--teal-dark);transform:translateY(-2px)}
		.hero{overflow:hidden;padding:74px 0 84px;background:#e8f3ed}.hero-grid{display:grid;grid-template-columns:1fr .92fr;align-items:center;gap:65px}.eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--teal);font-size:.77rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.eyebrow:before{width:24px;height:2px;background:var(--coral);content:""}h1,h2,h3{font-family:"Space Grotesk",sans-serif;line-height:1.08;letter-spacing:-.045em}h1{max-width:590px;margin:18px 0 20px;font-size:clamp(2.75rem,5vw,4.65rem)}.hero-copy{max-width:500px;color:var(--muted);font-size:1.08rem}.search{display:flex;max-width:510px;margin-top:30px;padding:6px;gap:8px;border:1px solid #c8ded5;border-radius:9px;background:#fff;box-shadow:0 8px 24px rgba(20,43,56,.07)}.search input{min-width:0;flex:1;border:0;outline:0;padding:9px 12px;color:var(--ink)}.search input::placeholder{color:#8a999c}.hero-art{position:relative;min-height:370px}.hero-photo{width:86%;height:350px;margin-left:auto;overflow:hidden;border-radius:16px 16px 16px 70px;box-shadow:var(--shadow);transform:rotate(2deg)}.hero-photo img{width:100%;height:100%;object-fit:cover}.float-card{position:absolute;right:0;bottom:18px;display:flex;align-items:center;gap:12px;padding:14px 17px;border-radius:10px;background:#fff;box-shadow:var(--shadow);font-size:.84rem}.avatar-stack{display:flex}.avatar-stack span{display:grid;place-items:center;width:29px;height:29px;margin-left:-7px;border:2px solid #fff;border-radius:50%;color:#fff;background:var(--coral);font-size:.68rem;font-weight:700}.avatar-stack span:first-child{margin-left:0;background:var(--teal)}.float-card strong{display:block;font-family:"Space Grotesk",sans-serif}
		.section{padding:76px 0}.section-heading{display:flex;align-items:end;justify-content:space-between;margin-bottom:26px}.section-heading h2{margin:9px 0 0;font-size:2.15rem}.section-heading a{color:var(--teal);font-size:.9rem;font-weight:700}.category-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.category{position:relative;min-height:165px;overflow:hidden;padding:20px;border-radius:10px;background:#fff;border:1px solid var(--line);transition:transform .2s,box-shadow .2s}.category:hover{transform:translateY(-4px);box-shadow:var(--shadow)}.category:after{position:absolute;right:-18px;bottom:-28px;width:100px;height:100px;border-radius:50%;background:#e7f3ee;content:""}.category:nth-child(2):after{background:#fff0da}.category:nth-child(3):after{background:#ffe3dc}.category:nth-child(4):after{background:#e4edf8}.category-icon{display:grid;place-items:center;width:44px;height:44px;margin-bottom:29px;border-radius:9px;color:var(--teal-dark);background:#e7f3ee;font-size:1.3rem}.category:nth-child(2) .category-icon{background:#fff0da}.category:nth-child(3) .category-icon{background:#ffe3dc}.category:nth-child(4) .category-icon{background:#e4edf8}.category strong{position:relative;z-index:1;display:block;font-family:"Space Grotesk",sans-serif;font-size:1.05rem}.category small{color:var(--muted);font-size:.78rem}
		.listings{display:none;background:#fff}.catalog-toolbar{display:flex;align-items:center;justify-content:space-between;margin:-5px 0 24px;padding:12px 14px;border:1px solid var(--line);border-radius:8px;background:#f7faf8;color:var(--muted);font-size:.82rem}.catalog-toolbar strong{color:var(--ink)}.filter-links{display:flex;gap:18px}.filter-links a{font-weight:700}.filter-links a:first-child{color:var(--teal)}.listing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}.listing{overflow:hidden;border:1px solid var(--line);border-radius:10px;background:#fff;transition:transform .2s,box-shadow .2s}.listing:hover{transform:translateY(-3px);box-shadow:var(--shadow)}.listing-image{position:relative;height:215px;overflow:hidden;background:#d9e6e0}.listing-image img{width:100%;height:100%;object-fit:cover;transition:transform .4s}.listing:hover img{transform:scale(1.04)}.tag{position:absolute;top:13px;left:13px;padding:5px 9px;border-radius:4px;color:var(--teal-dark);background:#e7f3ee;font-size:.7rem;font-weight:700}.save{position:absolute;top:12px;right:12px;display:grid;place-items:center;width:31px;height:31px;border:0;border-radius:50%;color:var(--ink);background:#fff;cursor:pointer;font-size:1rem;box-shadow:0 3px 12px rgba(20,43,56,.12)}.listing-body{padding:17px}.listing-body h3{margin:0 0 8px;font-size:1.12rem;letter-spacing:-.025em}.listing-meta{display:flex;justify-content:space-between;color:var(--muted);font-size:.79rem}.seller{display:flex;align-items:center;gap:7px;margin-top:13px;color:var(--muted);font-size:.75rem}.seller-avatar{display:grid;place-items:center;width:23px;height:23px;border-radius:50%;color:#fff;background:var(--coral);font-size:.58rem;font-weight:700}.verified{color:var(--teal);font-weight:700}.listing-bottom{display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid var(--line)}.price{margin:0;color:var(--ink);font-family:"Space Grotesk",sans-serif;font-size:1.2rem;font-weight:700}.price span{color:var(--muted);font-family:"DM Sans",sans-serif;font-size:.72rem;font-weight:400}.view-button{padding:7px 10px;border:1px solid #b8d9ce;border-radius:5px;color:var(--teal);font-size:.73rem;font-weight:700}.view-button:hover{background:#e7f3ee}
		.trust{padding:54px 0;border-top:1px solid var(--line);background:#f7faf8}.trust-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:35px}.trust-item{display:flex;gap:15px}.trust-item>b{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:50%;color:var(--teal-dark);background:#dcefe8}.trust-item strong{display:block;margin-bottom:3px;font-family:"Space Grotesk",sans-serif}.trust-item p{margin:0;color:var(--muted);font-size:.84rem}footer{padding:34px 0;color:#c7d8d5;background:var(--ink)}.footer-row{display:flex;align-items:center;justify-content:space-between;gap:25px}footer .brand{color:#fff}footer small{color:#9eb6b2}.footer-links{display:flex;gap:22px;font-size:.82rem}
		@media(max-width:800px){.container{width:min(100% - 28px,600px)}.topbar .container{display:block;text-align:center}.topbar .container span{display:none}.nav{min-height:68px;gap:15px}.nav-links{display:none}.sell-link{display:none}.hero{padding:55px 0 65px}.hero-grid{grid-template-columns:1fr;gap:38px}h1{font-size:3.1rem}.hero-art{min-height:280px}.hero-photo{height:270px;width:92%}.category-grid{grid-template-columns:repeat(2,1fr)}.listing-grid,.trust-grid{grid-template-columns:1fr}.listing-image{height:230px}.section{padding:57px 0}.footer-row{align-items:flex-start;flex-direction:column}}
		@media(max-width:430px){.nav-actions .button{padding:10px 12px;font-size:.82rem}.brand{font-size:1.1rem}h1{font-size:2.65rem}.search{flex-direction:column;border:0;padding:0;background:transparent;box-shadow:none}.search input{min-height:48px;border:1px solid #c8ded5;border-radius:7px}.search .button{min-height:48px}.section-heading{align-items:flex-start;flex-direction:column;gap:10px}}
		.cart-link{color:var(--teal);font-size:.88rem;font-weight:700}.cart-badge{display:inline-grid;place-items:center;min-width:20px;height:20px;margin-left:3px;padding:0 5px;border-radius:10px;color:#fff;background:var(--coral);font-size:.7rem}.add-cart{width:100%;margin-top:9px;padding:9px;border:0;border-radius:5px;color:#fff;background:var(--teal);cursor:pointer;font-size:.78rem;font-weight:700}.add-cart:hover{background:var(--teal-dark)}.add-cart.added{background:#60727a}
		.listing-bottom{flex-wrap:wrap;gap:9px}.listing-bottom .price{width:100%;margin-bottom:3px}.listing-bottom .view-button,.listing-bottom .add-cart{flex:1;width:auto;margin:0;text-align:center}.listing-bottom .view-button{min-height:36px;display:inline-flex;align-items:center;justify-content:center}.listing-bottom .add-cart{min-height:36px}
		.hero{position:relative;min-height:570px;display:flex;align-items:center;background:#17343e url("assets/hero-shopping.jpg") center/cover no-repeat;color:#fff}.hero:before{position:absolute;inset:0;background:linear-gradient(90deg,rgba(10,36,45,.9) 0%,rgba(10,36,45,.68) 48%,rgba(10,36,45,.25) 100%);content:""}.hero-grid{position:relative;z-index:1;grid-template-columns:minmax(0,640px);width:100%;min-height:570px}.hero-art{display:none}.hero .eyebrow{color:#f7d67a}.hero h1{max-width:640px;color:#fff}.hero-copy{color:rgba(255,255,255,.86)}.hero .search{background:rgba(255,255,255,.97)}
		.hero:after{position:absolute;right:0;bottom:0;left:0;z-index:0;height:170px;background:linear-gradient(to bottom,rgba(247,250,248,0),var(--paper));content:"";pointer-events:none}.hero-grid{z-index:1}
		.hero{background-image:url("assets/hero-shopping-black.jpg")}
		.filter-bar{display:flex;align-items:end;gap:12px;margin:-5px 0 24px;padding:16px;border:1px solid var(--line);border-radius:10px;background:#fff;box-shadow:0 6px 18px rgba(20,43,56,.05)}.filter-field{display:grid;gap:5px;min-width:0;flex:1}.filter-field label{color:var(--muted);font-size:.7rem;font-weight:700}.filter-field select,.filter-field input{width:100%;min-height:39px;padding:8px 10px;border:1px solid #c8ded5;border-radius:6px;color:var(--ink);background:#f7faf8;font:inherit;font-size:.8rem;outline:0}.filter-field select:focus,.filter-field input:focus{border-color:var(--teal)}.filter-submit{min-height:39px;padding:0 17px;border:0;border-radius:6px;color:#fff;background:var(--teal);cursor:pointer;font-size:.8rem;font-weight:700}.filter-submit:hover{background:var(--teal-dark)}.filter-reset{min-height:39px;display:inline-flex;align-items:center;padding:0 7px;color:var(--muted);font-size:.78rem;font-weight:700;white-space:nowrap}@media(max-width:650px){.filter-bar{align-items:stretch;flex-wrap:wrap}.filter-field{flex-basis:calc(50% - 6px)}.filter-submit{flex:1}.filter-reset{justify-content:center;flex:1}}
		.quick-catalog{background:#f7faf8}.quick-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}.quick-card{overflow:hidden;border:1px solid var(--line);border-radius:9px;background:#fff;transition:transform .2s,box-shadow .2s}.quick-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}.quick-card[hidden]{display:none}.quick-image{display:grid;place-items:center;height:105px;color:var(--teal-dark);background:#e7f3ee;font-size:2rem}.quick-card:nth-child(2) .quick-image{background:#fff0da}.quick-card:nth-child(3) .quick-image{background:#ffe3dc}.quick-card:nth-child(4) .quick-image{background:#e4edf8}.quick-card:nth-child(5) .quick-image{background:#f0e7f5}.quick-card h3{margin:14px 13px 6px;font-size:.9rem;letter-spacing:-.025em}.quick-card strong{display:block;margin:0 13px 15px;color:var(--teal);font-family:"Space Grotesk",sans-serif;font-size:.9rem}.quick-filter{padding:9px 16px;border:1px solid var(--line);border-radius:6px;color:var(--teal);background:#fff;cursor:pointer;font-weight:700}.quick-filter.active{color:#fff;background:var(--teal);border-color:var(--teal)}.category-filter.active{outline:3px solid rgba(8,127,120,.2);border-color:var(--teal)}
	</style>
</head>
<body>
	<div class="topbar"><div class="container"><span>Built for students, by students</span><span>New here? <a href="#how-it-works">See how it works</a></span></div></div>
	<header><nav class="container nav" aria-label="Main navigation"><a class="brand" href="index.php"><span class="brand-mark">M</span> CampusMarket</a><div class="nav-links"><a href="#browse">Browse</a><a href="#how-it-works">How it works</a><a href="#about">About us</a></div><div class="nav-actions"><a class="cart-link" href="cart.php">Cart <span class="cart-badge" id="cart-count">0</span></a><a class="sell-link" href="signup.php?next=sell">Sell an item</a><a class="button" href="signup.php?next=account">Join free</a></div></nav></header>
	<main>
		<section class="hero"><div class="container hero-grid"><div><span class="eyebrow">The student marketplace</span><h1>Make room for what matters next.</h1><p class="hero-copy">Find useful things nearby, give your old gear a second life, and keep more money in your pocket.</p><form class="search" action="index.php#listings" method="get" role="search"><input type="search" name="q" value="<?= e($searchTerm) ?>" aria-label="Search listings" placeholder="Search textbooks, bikes, furniture..."><button class="button" type="submit">Search</button></form></div><div class="hero-art" aria-label="Shoppers browsing CampusMarket"><div class="hero-photo"><img src="assets/hero-shopping.jpg" alt="Shoppers carrying colorful bags together"></div><div class="float-card"><div class="avatar-stack"><span>AJ</span><span>KM</span><span>+</span></div><div><strong>12,000+ users</strong><span>are already buying local</span></div></div></div></div></section>
		<section class="section" id="browse"><div class="container"><div class="section-heading"><div><span class="eyebrow">Explore campus finds</span><h2>What are you looking for?</h2></div><a href="#category-directory">View all categories &rarr;</a></div><div class="category-grid" id="category-directory"><a class="category" href="index.php?category=Textbooks#listings"><span class="category-icon">&#9878;</span><strong>Textbooks</strong><small>Save on your next semester</small></a><a class="category" href="index.php?category=Furniture#listings"><span class="category-icon">&#9733;</span><strong>Furniture</strong><small>Make your space yours</small></a><a class="category" href="index.php?category=Fashion#listings"><span class="category-icon">&#9829;</span><strong>Fashion</strong><small>Good style, better prices</small></a><a class="category" href="index.php?category=Electronics#listings"><span class="category-icon">&#9673;</span><strong>Electronics</strong><small>Find useful campus tech</small></a><a class="category" href="index.php?category=Phones+%26+gadgets#listings"><span class="category-icon">&#9742;</span><strong>Phones &amp; gadgets</strong><small>Stay connected for less</small></a><a class="category" href="index.php?category=Transport#listings"><span class="category-icon">&#9736;</span><strong>Transport</strong><small>Get moving around campus</small></a></div></div></section>
		<section class="section listings" id="listings"><div class="container"><div class="section-heading"><div><span class="eyebrow">Fresh this week</span><h2>Popular near you</h2></div><a href="#browse">See all listings &rarr;</a></div><div class="catalog-toolbar"><span><strong>128 items</strong> available on campus</span><div class="filter-links"><a href="#listings">All items</a><a href="#listings">Newest</a><a href="#listings">Under KSh 5,000</a></div></div><div class="listing-grid"><article class="listing"><div class="listing-image"><span class="tag">Like new</span><button class="save" type="button" aria-label="Save calculus and physics bundle">&#9825;</button><img src="https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=700&q=80" alt="Stack of university textbooks"></div><div class="listing-body"><h3>Calculus &amp; Physics bundle</h3><div class="listing-meta"><span>Engineering</span><span>0.4 mi</span></div><div class="seller"><span class="seller-avatar">NM</span> Nadia M. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 5,850 <span>for 3 books</span></div><a class="view-button" href="#listings">View item</a></div></div></article><article class="listing"><div class="listing-image"><span class="tag">Pickup today</span><button class="save" type="button" aria-label="Save olive reading chair">&#9825;</button><img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=700&q=80" alt="Comfortable green lounge chair"></div><div class="listing-body"><h3>Olive reading chair</h3><div class="listing-meta"><span>Home &amp; room</span><span>0.8 mi</span></div><div class="seller"><span class="seller-avatar">DK</span> David K. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 7,800 <span>negotiable</span></div><a class="view-button" href="#listings">View item</a></div></div></article><article class="listing"><div class="listing-image"><span class="tag">Verified seller</span><button class="save" type="button" aria-label="Save city commuter bike">&#9825;</button><img src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=700&q=80" alt="Black bicycle parked outdoors"></div><div class="listing-body"><h3>City commuter bike</h3><div class="listing-meta"><span>Transport</span><span>1.2 mi</span></div><div class="seller"><span class="seller-avatar">JO</span> Joseph O. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 23,500 <span>excellent condition</span></div><a class="view-button" href="#listings">View item</a></div></div></article><article class="listing"><div class="listing-image"><span class="tag">Popular</span><button class="save" type="button" aria-label="Save noise cancelling headphones">&#9825;</button><img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=700&q=80" alt="White wireless headphones on a table"></div><div class="listing-body"><h3>Noise-cancelling headphones</h3><div class="listing-meta"><span>Electronics</span><span>0.6 mi</span></div><div class="seller"><span class="seller-avatar">AW</span> Amina W. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 4,200 <span>barely used</span></div><a class="view-button" href="#listings">View item</a></div></div></article><article class="listing"><div class="listing-image"><span class="tag">Good condition</span><button class="save" type="button" aria-label="Save study desk">&#9825;</button><img src="https://images.unsplash.com/photo-1518455027359-f3f8164ba6b7?auto=format&fit=crop&w=700&q=80" alt="Wooden study desk with a lamp"></div><div class="listing-body"><h3>Compact study desk</h3><div class="listing-meta"><span>Home &amp; room</span><span>1.5 mi</span></div><div class="seller"><span class="seller-avatar">BM</span> Brian M. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 6,500 <span>pickup only</span></div><a class="view-button" href="#listings">View item</a></div></div></article><article class="listing"><div class="listing-image"><span class="tag">New arrival</span><button class="save" type="button" aria-label="Save denim jacket">&#9825;</button><img src="https://images.unsplash.com/photo-1543076447-215ad9ba6923?auto=format&fit=crop&w=700&q=80" alt="Blue denim jacket hanging on a rack"></div><div class="listing-body"><h3>Classic denim jacket</h3><div class="listing-meta"><span>Fashion</span><span>0.9 mi</span></div><div class="seller"><span class="seller-avatar">SN</span> Sarah N. <span class="verified">&#10003; Verified</span></div><div class="listing-bottom"><div class="price">KSh 2,750 <span>size medium</span></div><a class="view-button" href="#listings">View item</a></div></div></article></div></div></section>
		<section class="section listings-live" id="listings"><div class="container"><div class="section-heading"><div><span class="eyebrow">Live campus catalog</span><h2><?= $searchTerm !== '' ? 'Search results' : 'Popular near you' ?></h2></div><a href="index.php#browse">Browse categories &rarr;</a></div><?php if (!$catalogReady): ?><div class="catalog-toolbar"><span><strong>Database not connected.</strong> Import <a href="database.sql">database.sql</a> into XAMPP MySQL to load listings.</span></div><?php elseif (count($listings) === 0): ?><div class="catalog-toolbar"><span><strong>No listings found.</strong> Try another search or category.</span><a href="index.php#listings">Clear filters</a></div><?php else: ?><div class="catalog-toolbar"><span><strong><?= count($listings) ?> items</strong> available on campus</span><div class="filter-links"><a href="index.php#listings">All items</a><a href="index.php?category=Home+%26+room#listings">Home</a><a href="index.php?category=Fashion#listings">Fashion</a></div></div><div class="listing-grid"><?php foreach ($listings as $listing): ?><article class="listing"><div class="listing-image"><span class="tag"><?= e($listing['status_label']) ?></span><button class="save" type="button" aria-label="Save <?= e($listing['title']) ?>" data-title="<?= e($listing['title']) ?>">&#9825;</button><img src="<?= e($listing['image_url']) ?>" alt="<?= e($listing['title']) ?>"></div><div class="listing-body"><h3><?= e($listing['title']) ?></h3><div class="listing-meta"><span><?= e($listing['category']) ?></span><span><?= e($listing['distance_label']) ?></span></div><div class="seller"><span class="seller-avatar"><?= e($listing['seller_initials']) ?></span> <?= e($listing['seller_name']) ?> <?php if ($listing['is_verified']): ?><span class="verified">&#10003; Verified</span><?php endif; ?></div><div class="listing-bottom"><div class="price">KSh <?= number_format((float) $listing['price']) ?> <span><?= e($listing['detail_label']) ?></span></div><button class="view-button view-item" type="button" data-title="<?= e($listing['title']) ?>">View item</button></div></div></article><?php endforeach; ?></div><?php endif; ?></div></section>
		<section class="trust" id="how-it-works"><div class="container trust-grid"><div class="trust-item"><b>1</b><div><strong>List in a minute</strong><p>Snap a photo, set your price, and reach people on your campus.</p></div></div><div class="trust-item"><b>2</b><div><strong>Meet with confidence</strong><p>Chat safely and choose a public campus pickup spot.</p></div></div><div class="trust-item"><b>3</b><div><strong>Keep it circular</strong><p>Save cash, reduce waste, and pass good things forward.</p></div></div></div></section>
	</main>
	<footer id="about"><div class="container footer-row"><a class="brand" href="index.php"><span class="brand-mark">M</span> CampusMarket</a><div class="footer-links"><a href="#about">About</a><a href="#how-it-works">Safety</a><a href="#about">Contact</a><a href="https://www.facebook.com/vincent.mentelilo" target="_blank" rel="noopener" aria-label="CampusMarket on Facebook" title="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M14 8h3V4h-3c-3.3 0-5 2-5 5v3H6v4h3v8h4v-8h3.2l.8-4H13V9c0-.7.3-1 1-1Z"/></svg></a><a href="https://www.instagram.com/accounts/edit/" target="_blank" rel="noopener" aria-label="CampusMarket on Instagram" title="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".7" fill="currentColor" stroke="none"/></svg></a></div><small>&copy; 2026 CampusMarket</small></div></footer>
<script>
	document.querySelectorAll('.save').forEach(function (button) {
		button.addEventListener('click', function () {
			button.classList.toggle('saved');
			button.innerHTML = button.classList.contains('saved') ? '&#9829;' : '&#9825;';
			button.setAttribute('aria-label', button.classList.contains('saved') ? 'Remove ' + button.dataset.title + ' from saved items' : 'Save ' + button.dataset.title);
		});
	});
	document.querySelectorAll('.view-item').forEach(function (button) {
		button.addEventListener('click', function () {
			alert(button.dataset.title + ' selected. Messaging and checkout will be available after account setup.');
		});
	});
	document.querySelectorAll('[data-demo-action]').forEach(function (link) {
		link.addEventListener('click', function (event) {
			event.preventDefault();
			alert(link.dataset.demoAction === 'sell' ? 'The seller form is ready for the next step.' : 'Account registration is ready for the next step.');
		});
	});
	const cartKey = 'campusmarket-cart';
	function readCart() { return JSON.parse(localStorage.getItem(cartKey) || '[]'); }
	function updateCartCount() { const count = readCart().length; const badge = document.getElementById('cart-count'); if (badge) badge.textContent = count; }
	const liveCatalog = document.querySelector('.listings-live');
	if (liveCatalog) {
		const params = new URLSearchParams(window.location.search);
		const filterBar = document.createElement('form');
		filterBar.className = 'filter-bar';
		filterBar.method = 'get';
		filterBar.action = 'index.php#listings';
		filterBar.innerHTML = '<div class="filter-field"><label for="filter-category">Category</label><select id="filter-category" name="category"><option value="">All categories</option><option>Textbooks</option><option>Furniture</option><option>Fashion</option><option>Electronics</option><option>Phones &amp; gadgets</option><option>Transport</option></select></div><div class="filter-field"><label for="filter-min">Minimum KSh</label><input id="filter-min" name="min_price" type="number" min="0" step="100" placeholder="0"></div><div class="filter-field"><label for="filter-max">Maximum KSh</label><input id="filter-max" name="max_price" type="number" min="0" step="100" placeholder="No limit"></div><div class="filter-field"><label for="filter-sort">Sort by</label><select id="filter-sort" name="sort"><option value="newest">Newest</option><option value="price_low">Price: low to high</option><option value="price_high">Price: high to low</option></select></div><button class="filter-submit" type="submit">Apply filters</button><a class="filter-reset" href="index.php#listings">Reset</a>';
		filterBar.querySelector('#filter-category').value = params.get('category') || '';
		filterBar.querySelector('#filter-min').value = params.get('min_price') || '';
		filterBar.querySelector('#filter-max').value = params.get('max_price') || '';
		filterBar.querySelector('#filter-sort').value = params.get('sort') || 'newest';
		const toolbar = liveCatalog.querySelector('.catalog-toolbar');
		if (toolbar) toolbar.insertAdjacentElement('beforebegin', filterBar);
	}
	document.querySelectorAll('.listings-live .listing').forEach(function (card, index) {
		const title = card.querySelector('h3').textContent.trim();
		const priceMatch = card.querySelector('.price').textContent.match(/KSh\s*([\d,]+)/);
		const priceText = priceMatch ? priceMatch[1].replace(/,/g, '') : '0';
		const category = card.querySelector('.listing-meta span').textContent.trim();
		const image = card.querySelector('img').src;
		const button = document.createElement('button');
		button.className = 'add-cart';
		button.type = 'button';
		button.dataset.id = 'catalog-' + index + '-' + title;
		button.dataset.title = title;
		button.dataset.price = priceText;
		button.dataset.image = image;
		button.dataset.category = category;
		button.textContent = 'Add to cart';
		card.querySelector('.listing-bottom').appendChild(button);
	});
	document.querySelectorAll('.add-cart').forEach(function (button) {
		button.addEventListener('click', function () {
			const cart = readCart();
			const item = { id: button.dataset.id, title: button.dataset.title, price: Number(button.dataset.price), image: button.dataset.image, category: button.dataset.category };
			if (!cart.some(function (saved) { return saved.id === item.id; })) { cart.push(item); localStorage.setItem(cartKey, JSON.stringify(cart)); }
			button.textContent = 'Added to cart';
			button.classList.add('added');
			updateCartCount();
		});
	});
	updateCartCount();
	const categoryButton = document.querySelector('.section-heading a[href="#category-directory"]');
	if (categoryButton) categoryButton.href = 'categories.php';
	const categoryGrid = document.getElementById('category-directory');
	if (categoryGrid) {
		const categoryMap = {'Textbooks':'textbooks','Furniture':'furniture','Fashion':'fashion','Electronics':'electronics','Phones & gadgets':'phone-gadgets','Transport':'transport'};
		const cards = Object.keys(categoryMap).map(function (label) { return Array.from(categoryGrid.querySelectorAll('.category')).find(function (card) { return card.textContent.includes(label); }); }).filter(Boolean);
		cards.forEach(function (card, index) { card.dataset.category = Object.values(categoryMap)[index]; card.classList.add('category-filter'); if (card.textContent.includes('Phones')) card.querySelector('strong').textContent = 'Phone & Gadgets'; });
		const allButton = document.createElement('button');
		allButton.className = 'category category-filter active';
		allButton.type = 'button';
		allButton.dataset.category = 'all';
		allButton.innerHTML = '<span class="category-icon">&#9673;</span><strong>All items</strong><small>Browse every product</small>';
		categoryGrid.prepend(allButton);
		const quickSection = document.createElement('section');
		quickSection.className = 'section quick-catalog';
		quickSection.innerHTML = '<div class="container"><div class="section-heading"><div><span class="eyebrow">Quick matches</span><h2>Browse popular finds</h2></div></div><div class="quick-grid"><article class="quick-card" data-category="textbooks"><div class="quick-image">&#128218;</div><h3>Academic textbook bundle</h3><strong>KSh 3,500</strong></article><article class="quick-card" data-category="furniture"><div class="quick-image">&#128717;</div><h3>Compact study desk</h3><strong>KSh 6,500</strong></article><article class="quick-card" data-category="fashion"><div class="quick-image">&#128087;</div><h3>Classic denim jacket</h3><strong>KSh 2,750</strong></article><article class="quick-card" data-category="electronics"><div class="quick-image">&#127911;</div><h3>Wireless headphones</h3><strong>KSh 4,200</strong></article><article class="quick-card" data-category="phone-gadgets"><div class="quick-image">&#128241;</div><h3>Samsung Galaxy A54</h3><strong>KSh 24,500</strong></article></div></div>';
		categoryGrid.closest('.section').insertAdjacentElement('afterend', quickSection);
		const quickCards = Array.from(quickSection.querySelectorAll('.quick-card'));
		function applyQuickFilter(filter, clicked) { document.querySelectorAll('.category-filter').forEach(function (button) { button.classList.toggle('active', button === clicked); }); quickCards.forEach(function (card) { card.hidden = filter !== 'all' && card.dataset.category !== filter; }); }
		document.querySelectorAll('.category-filter').forEach(function (button) { button.addEventListener('click', function (event) { if (button.tagName === 'A') event.preventDefault(); applyQuickFilter(button.dataset.category, button); quickSection.scrollIntoView({behavior:'smooth', block:'start'}); }); });
	}
</script>
</body>
</html>
