<?php
session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['verified_phone'])) {
    header('Location: signup.php?next=sell');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $allowedCategories = ['Electronics', 'Furniture', 'Phones & gadgets', 'Textbooks', 'Fashion', 'Other'];
    if ($title === '' || !in_array($category, $allowedCategories, true) || $price <= 0 || $description === '') {
        $message = 'Please complete the title, product type, price, and description.';
    } else {
        $imageUrl = 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=700&q=80';
        if (!empty($_FILES['photos']['tmp_name'][0]) && is_uploaded_file($_FILES['photos']['tmp_name'][0])) {
            $extension = strtolower(pathinfo($_FILES['photos']['name'][0], PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $fileName = uniqid('listing_', true) . '.' . $extension;
                if (move_uploaded_file($_FILES['photos']['tmp_name'][0], __DIR__ . '/uploads/' . $fileName)) {
                    $imageUrl = 'uploads/' . $fileName;
                }
            }
        }
        $query = $pdo->prepare('INSERT INTO listings (title, category, price, condition_label, location_label, distance_label, seller_name, seller_initials, image_url, status_label, detail_label) VALUES (:title, :category, :price, :condition_label, :location_label, :distance_label, :seller_name, :seller_initials, :image_url, :status_label, :detail_label)');
        $query->execute([
            'title' => $title,
            'category' => $category,
            'price' => $price,
            'condition_label' => 'New listing',
            'location_label' => 'Campus',
            'distance_label' => 'Nearby',
            'seller_name' => 'CampusMarket seller',
            'seller_initials' => 'CM',
            'image_url' => $imageUrl,
            'status_label' => 'New listing',
            'detail_label' => mb_substr($description, 0, 80),
        ]);
        $message = 'Your ' . $category . ' listing was added to the catalog.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sell an item | CampusMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#142b38;--muted:#60727a;--paper:#f7faf8;--white:#fff;--line:#dce7e3;--teal:#087f78;--teal-dark:#075b59;--coral:#f07c63}
        *{box-sizing:border-box}body{margin:0;color:var(--ink);background:var(--paper);font-family:"DM Sans",sans-serif;line-height:1.5}a{color:inherit;text-decoration:none}.container{width:min(900px,calc(100% - 32px));margin:auto}.top{padding:18px 0;background:#fff;border-bottom:1px solid var(--line)}.nav{display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:10px;font-family:"Space Grotesk",sans-serif;font-size:1.2rem;font-weight:700}.mark{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;color:#fff;background:var(--teal)}.back{color:var(--teal);font-size:.85rem;font-weight:700}.page{padding:48px 0}.heading{margin-bottom:25px}.eyebrow{color:var(--teal);font-size:.76rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.heading h1{margin:10px 0 5px;font-family:"Space Grotesk",sans-serif;font-size:2.7rem;letter-spacing:-.05em}.heading p{margin:0;color:var(--muted)}.layout{display:grid;grid-template-columns:1.1fr .9fr;gap:24px}.card{padding:25px;border:1px solid var(--line);border-radius:12px;background:#fff;box-shadow:0 14px 35px rgba(20,43,56,.06)}.card h2{margin:0 0 5px;font-family:"Space Grotesk",sans-serif;font-size:1.25rem}.card-copy{margin:0 0 20px;color:var(--muted);font-size:.82rem}.field{display:grid;gap:7px;margin-bottom:16px}.field label{font-size:.8rem;font-weight:700}.field input,.field select,.field textarea{width:100%;padding:12px;border:1px solid #c8ded5;border-radius:7px;outline:0;color:var(--ink);background:#fff;font:inherit}.field textarea{min-height:130px;resize:vertical}.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--teal)}.photo-actions{display:grid;grid-template-columns:1fr 1fr;gap:9px}.photo-label{display:flex;align-items:center;justify-content:center;min-height:45px;border:1px solid #b8d9ce;border-radius:7px;color:var(--teal);background:#f7faf8;cursor:pointer;font-size:.78rem;font-weight:700;text-align:center}.photo-label:hover{background:#e7f3ee}.photo-label input{position:absolute;width:1px;height:1px;opacity:0}.preview{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px}.photo{position:relative;height:88px;overflow:hidden;border-radius:6px;background:#dce7e3}.photo img{width:100%;height:100%;object-fit:cover}.remove{position:absolute;top:4px;right:4px;width:23px;height:23px;border:0;border-radius:50%;color:#fff;background:rgba(20,43,56,.8);cursor:pointer}.primary{width:100%;padding:13px;border:0;border-radius:7px;color:#fff;background:var(--teal);cursor:pointer;font-weight:700}.primary:hover{background:var(--teal-dark)}.notice{margin-bottom:18px;padding:11px;border-radius:6px;color:var(--teal-dark);background:#e7f3ee;font-size:.82rem}.tips{display:grid;gap:17px}.tip{display:flex;gap:12px}.tip b{display:grid;place-items:center;flex:0 0 31px;height:31px;border-radius:50%;color:var(--teal-dark);background:#dcefe8}.tip strong{display:block;font-size:.85rem}.tip span{display:block;color:var(--muted);font-size:.78rem}.delete{width:100%;margin-top:18px;padding:10px;border:1px solid #f0c4bb;border-radius:7px;color:#a94b39;background:#fff6f4;cursor:pointer;font-size:.8rem;font-weight:700}.message{margin-bottom:18px;padding:12px;border-radius:7px;color:var(--teal-dark);background:#e7f3ee;font-size:.84rem}
        @media(max-width:720px){.page{padding:30px 0}.layout{grid-template-columns:1fr}.heading h1{font-size:2.25rem}.tips{grid-template-columns:1fr 1fr}.photo-actions{grid-template-columns:1fr}}
    </style>
</head>
<body><header class="top"><nav class="container nav"><a class="brand" href="index.php"><span class="mark">M</span> CampusMarket</a><a class="back" href="index.php">&larr; Back to marketplace</a></nav></header><main class="container page"><div class="heading"><span class="eyebrow">Sell on campus</span><h1>Add a product</h1><p>Choose the product type, describe it clearly, and add photos buyers can trust.</p></div><?php if ($message !== ''): ?><div class="message" role="status"><?= htmlspecialchars($message) ?></div><?php endif; ?><div class="layout"><section class="card"><h2>Product details</h2><p class="card-copy">The more detail you add, the faster your item can find a buyer.</p><form method="post" id="product-form"><div class="field"><label for="title">Product name</label><input id="title" name="title" placeholder="e.g. Samsung Galaxy A54" required></div><div class="field"><label for="category">Product type</label><select id="category" name="category" required><option value="">Select a type</option><option>Electronics</option><option>Furniture</option><option>Phones &amp; gadgets</option><option>Textbooks</option><option>Fashion</option><option>Other</option></select></div><div class="field"><label for="price">Price in KSh</label><input id="price" name="price" type="number" min="1" step="1" placeholder="2500" required></div><div class="field"><label for="description">Description</label><textarea id="description" name="description" placeholder="Tell buyers about the condition, features, size, and pickup details..." required></textarea></div><div class="field"><label>Add product photos</label><div class="photo-actions"><label class="photo-label">&#128247; Take photo<input id="camera" type="file" accept="image/*" capture="environment"></label><label class="photo-label">&#128444; Choose from gallery<input id="gallery" type="file" accept="image/*" multiple></label></div><div class="preview" id="preview"></div></div><button class="primary" type="submit">Publish product</button></form><button class="delete" id="clear-form" type="button">Delete / clear this product</button></section><aside class="card"><h2>Good listings sell faster</h2><p class="card-copy">A few thoughtful details help buyers decide quickly.</p><div class="tips"><div class="tip"><b>1</b><div><strong>Use the right type</strong><span>Choose electronics, furniture, phones and gadgets, or another clear category.</span></div></div><div class="tip"><b>2</b><div><strong>Show the real condition</strong><span>Use bright photos and mention scratches, age, or included accessories.</span></div></div><div class="tip"><b>3</b><div><strong>Be specific</strong><span>Include size, model, location, and whether the price is negotiable.</span></div></div></div></aside></div></main><script>
const productForm = document.getElementById('product-form');
productForm.enctype = 'multipart/form-data';
document.getElementById('camera').name = 'photos[]';
document.getElementById('gallery').name = 'photos[]';
const preview = document.getElementById('preview');
const selectedFiles = [];
document.querySelectorAll('.photo-label input').forEach(function(input){input.addEventListener('change', function(){Array.from(input.files).forEach(function(file){selectedFiles.push(file)});renderPhotos()})});
function renderPhotos(){preview.innerHTML='';selectedFiles.forEach(function(file,index){const wrapper=document.createElement('div');wrapper.className='photo';const image=document.createElement('img');image.src=URL.createObjectURL(file);image.alt='Product photo '+(index+1);const remove=document.createElement('button');remove.className='remove';remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Delete photo '+(index+1));remove.onclick=function(){selectedFiles.splice(index,1);renderPhotos()};wrapper.append(image,remove);preview.appendChild(wrapper)})}
document.getElementById('clear-form').addEventListener('click',function(){document.getElementById('product-form').reset();selectedFiles.length=0;renderPhotos()});
+</script></body></html>
