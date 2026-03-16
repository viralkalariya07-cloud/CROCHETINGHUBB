<?php
session_name('SELLER_SESSION');
session_start();

// ===== DATABASE CONNECTION =====
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Ensure seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}
$seller_id = $_SESSION['user_id'];

/* ===== ENSURE shipping_charges COLUMN EXISTS ===== */
mysqli_query($conn, "ALTER TABLE sellerproducts ADD COLUMN IF NOT EXISTS shipping_charges DECIMAL(10,2) DEFAULT 0.00");

/* ===== CREATE product_images TABLE IF NOT EXISTS ===== */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

/* ===== DELETE EXTRA IMAGE ===== */
if (isset($_GET['delete_img']) && isset($_GET['product_id'])) {
    $img_id     = intval($_GET['delete_img']);
    $product_id = intval($_GET['product_id']);
    // Verify this image belongs to seller's product
    $img_row = mysqli_query($conn, "SELECT pi.image FROM product_images pi
        JOIN sellerproducts sp ON sp.id = pi.product_id
        WHERE pi.id = $img_id AND sp.seller_id = '$seller_id'");
    if ($img_row && mysqli_num_rows($img_row) > 0) {
        $r = mysqli_fetch_assoc($img_row);
        @unlink('uploads/' . $r['image']);
        mysqli_query($conn, "DELETE FROM product_images WHERE id = $img_id");
    }
    header("Location: selleraddproducts.php?edit_id=$product_id");
    exit();
}

/* ===== FETCH CATEGORIES (Filter by Current Seller) ===== */
$cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE seller_id = '$seller_id' ORDER BY id DESC");
if (!$cat_result) {
    $cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE seller_id = '$seller_id'");
}

/* ===== DELETE PRODUCT ===== */
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM sellerproducts WHERE id='$delete_id' AND seller_id='$seller_id'";

    if(mysqli_query($conn, $sql)){
        echo "<script>
        alert('Product Deleted Successfully');
        window.location='sellerproducts.php';
        </script>";
        exit();
    }
}

/* ===== FETCH PRODUCT FOR EDIT ===== */
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = mysqli_query($conn, "SELECT * FROM sellerproducts WHERE id = $edit_id AND seller_id = '$seller_id'");
    if ($edit_res && mysqli_num_rows($edit_res) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_res);
    }
}

/* ===== INSERT / UPDATE PRODUCT ===== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name             = mysqli_real_escape_string($conn, $_POST['name']);
    $category         = mysqli_real_escape_string($conn, $_POST['category']);
    $price            = floatval($_POST['price']);
    $stock            = intval($_POST['stock']);
    $shipping_charges = floatval($_POST['shipping_charges'] ?? 0);
    $status           = mysqli_real_escape_string($conn, $_POST['status']);
    $description      = mysqli_real_escape_string($conn, $_POST['description']);
    $video            = mysqli_real_escape_string($conn, $_POST['video']);

    $existing_id = isset($_POST['existing_id']) ? intval($_POST['existing_id']) : null;

    // Create uploads folder if not exists
    if (!file_exists('uploads')) mkdir('uploads', 0777, true);

    /* ── Handle multiple image uploads ── */
    $uploaded_images = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $i => $fname) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK && !empty($fname)) {
                $safe_name = time() . '_' . $i . '_' . basename($fname);
                $target    = 'uploads/' . $safe_name;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target)) {
                    $uploaded_images[] = $safe_name;
                }
            }
        }
    }

    // Legacy single image field (kept for backward compat)
    $image_sql  = "";
    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target = "uploads/" . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_sql = ", image='$image_name'";
            $uploaded_images[] = $image_name; // also save to product_images
        }
    }

    if ($existing_id) {
        // UPDATE
        $sql = "UPDATE sellerproducts SET
                name='$name',
                category='$category',
                price='$price',
                stock='$stock',
                shipping_charges='$shipping_charges',
                status='$status',
                description='$description',
                video_link='$video'
                $image_sql
                WHERE id='$existing_id' AND seller_id='$seller_id'";
        $msg = "Product Updated Successfully";

        if (mysqli_query($conn, $sql)) {
            // Save extra images to product_images
            foreach ($uploaded_images as $idx => $img) {
                $is_primary = ($idx === 0 && empty($existing_id)) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO product_images (product_id, image, is_primary) VALUES ('$existing_id', '$img', '$is_primary')");
            }
            echo "<script>alert('$msg'); window.location='sellerproducts.php';</script>";
            exit();
        }
    } else {
        // INSERT
        $first_img = !empty($uploaded_images) ? $uploaded_images[0] : '';
        $sql = "INSERT INTO sellerproducts
        (name, category, price, stock, shipping_charges, status, description, image, video_link, seller_id)
        VALUES
        ('$name','$category','$price','$stock','$shipping_charges','$status','$description','$first_img','$video','$seller_id')";
        $msg = "Product Added Successfully";

        if (mysqli_query($conn, $sql)) {
            $new_id = mysqli_insert_id($conn);
            foreach ($uploaded_images as $idx => $img) {
                $is_primary = ($idx === 0) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO product_images (product_id, image, is_primary) VALUES ('$new_id', '$img', '$is_primary')");
            }
            echo "<script>alert('$msg'); window.location='sellerproducts.php';</script>";
            exit();
        }
    }
}

/* ===== FETCH PRODUCTS (Filter by Current Seller) ===== */
$result = mysqli_query($conn, "SELECT * FROM sellerproducts WHERE seller_id = '$seller_id'");
?>

<?php include("../includes/seller_header.php"); ?>
<title>Seller Products |🧶 CrochetingHubb</title>
<link rel="stylesheet" href="http://localhost/CROCHETINGHUBB/assets/css/selleraddproducts.css">
<style>
 .out-of-stock-img {
 filter: blur(3px);
 opacity: 0.6;
}
</style>



<!-- MAIN -->
<main class="product-main">
    <div class="container">

        <!-- PAGE TITLE -->
        <h3 class="page-title">Manage Products</h3>

        <!-- ADD PRODUCT FORM -->
        <div class="product-card">
            <h4><?php echo $edit_data ? 'Update Product' : 'Add New Product'; ?></h4>

            <form class="row g-3"  method="POST" enctype="multipart/form-data">
                <input type="hidden" name="existing_id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                
                <div class="col-md-6">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>

                    <?php if($cat_result && mysqli_num_rows($cat_result) == 0){ ?>
                         <div class="alert alert-danger">
                             Category must add first!
                         </div>
                    <?php } ?>

                    <select name="category" class="form-select" required>
                    <option value="">Select Category</option>

                    <?php
                    mysqli_data_seek($cat_result, 0); // reset pointer
                    if($cat_result && mysqli_num_rows($cat_result) > 0){
                         while($cat = mysqli_fetch_assoc($cat_result)){
                             $selected = (isset($edit_data['category']) && $edit_data['category'] == $cat['name']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $cat['name']; ?>" <?php echo $selected; ?>>
                    <?php echo $cat['name']; ?>
                    </option>
                    <?php }} ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" name="price" class="form-control" value="<?php echo $edit_data['price'] ?? ''; ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $edit_data['stock'] ?? ''; ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Shipping Charges (₹)</label>
                    <input type="number" step="0.01" min="0" name="shipping_charges" class="form-control" value="<?php echo $edit_data['shipping_charges'] ?? '0'; ?>" placeholder="0.00">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Available" <?php echo ($edit_data['status'] ?? '') == 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Out of Stock" <?php echo ($edit_data['status'] ?? '') == 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Product Photos <span class="text-muted fw-normal">(Add as many as you want)</span></label>

                    <?php
                    /* Show existing images when editing */
                    if ($edit_data) {
                        $existing_imgs = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = '{$edit_data['id']}' ORDER BY is_primary DESC, id ASC");
                        if ($existing_imgs && mysqli_num_rows($existing_imgs) > 0):
                    ?>
                    <div class="mb-3">
                        <p class="text-muted small mb-2"><i class="bi bi-images me-1"></i>Current photos (click ✕ to delete):</p>
                        <div class="d-flex flex-wrap gap-2" id="existingImgsGrid">
                            <?php while ($ei = mysqli_fetch_assoc($existing_imgs)): ?>
                            <div class="position-relative" style="width:90px;">
                                <img src="uploads/<?php echo htmlspecialchars($ei['image']); ?>"
                                     style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:2px solid #f9c8e0;"
                                     alt="product photo">
                                <?php if ($ei['is_primary']): ?>
                                    <span style="position:absolute;bottom:3px;left:3px;background:#e8247e;color:#fff;font-size:.6rem;padding:1px 5px;border-radius:4px;">Main</span>
                                <?php endif; ?>
                                <a href="selleraddproducts.php?delete_img=<?php echo $ei['id']; ?>&product_id=<?php echo $edit_data['id']; ?>"
                                   onclick="return confirm('Delete this photo?')"
                                   style="position:absolute;top:-6px;right:-6px;background:#e8247e;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:.75rem;text-decoration:none;box-shadow:0 2px 6px rgba(0,0,0,.2);">✕</a>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; } ?>

                    <!-- Drop Zone -->
                    <div id="dropZone"
                         style="border:2px dashed #f9c8e0;border-radius:14px;padding:30px;text-align:center;cursor:pointer;background:#fff8fb;transition:background .2s;"
                         onclick="document.getElementById('multiImgInput').click()"
                         ondragover="event.preventDefault();this.style.background='#ffe8f4';"
                         ondragleave="this.style.background='#fff8fb';"
                         ondrop="handleDrop(event)">
                        <i class="bi bi-cloud-arrow-up" style="font-size:2.5rem;color:#e8247e;"></i>
                        <p class="mb-1 mt-2 fw-semibold" style="color:#e8247e;">Click or drag &amp; drop photos here</p>
                        <p class="text-muted small mb-0">You can select multiple photos at once &middot; JPG, PNG, WEBP</p>
                    </div>

                    <input type="file" id="multiImgInput" name="images[]" multiple accept="image/*"
                           class="d-none" onchange="previewImages(this.files)">

                    <!-- Live Preview Grid -->
                    <div id="imgPreviewGrid" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tutorial Video Link </label>
                    <input type="url" name="video"  class="form-control" value="<?php echo htmlspecialchars($edit_data['video_link'] ?? ''); ?>">
                </div>

                <div class="col-12 text-end">
                    <?php if($edit_data): ?>
                        <a href="selleraddproducts.php" class="btn btn-secondary me-2">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="btn-register">
                        <?php echo $edit_data ? 'Update Product' : 'Save Product'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- PRODUCT LIST -->
        <div class="product-card mt-5">
            <h4>Product List</h4>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Video</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                <!-- ===== PRODUCT DATA (ADDED) ===== -->
                  <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td style="position: relative;">
                               <img src="uploads/<?php echo $row['image']; ?>"
                                width="60"
                                class="<?php echo ($row['stock'] <= 0) ? 'out-of-stock-img' : ''; ?>">

                                <?php if($row['stock'] <= 0){ ?>
                                <span style="position:absolute; top:5px; left:5px; 
                                background:red; color:white; padding:2px 5px; font-size:12px;">
                                SOLD OUT
                                </span>
                                <?php } ?>
                                </td>

                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['category']; ?></td>
                            <td>₹<?php echo $row['price']; ?></td>
                            <td><?php echo $row['stock']; ?></td>
                            <td>
                                <a href="<?php echo $row['video_link']; ?>" target="_blank">Watch</a>
                            </td>
                            <td><?php echo $row['status']; ?></td>
                                                        <td>
                                <a href="selleraddproducts.php?delete_id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Are you sure? Delete this product!')"
                                   class="btn btn-danger btn-sm">
                                   Delete
                                </a>

                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <!-- ===== END ===== -->

                </table>
            </div>
        </div>

    </div>
</main>

<?php include("../includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ── Multi-image preview & drag-drop ── */
let selectedFiles = []; // DataTransfer trick for live removal

const input    = document.getElementById('multiImgInput');
const grid     = document.getElementById('imgPreviewGrid');
const dropZone = document.getElementById('dropZone');

function previewImages(files) {
    for (const file of files) {
        if (!file.type.startsWith('image/')) continue;
        selectedFiles.push(file);
    }
    syncInput();
    renderPreviews();
}

function renderPreviews() {
    grid.innerHTML = '';
    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            wrapper.style.cssText = 'width:90px;';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:90px;height:90px;object-fit:cover;border-radius:10px;border:2px solid #f9c8e0;';

            const badge = document.createElement('span');
            badge.style.cssText = 'position:absolute;bottom:3px;left:3px;background:#e8247e;color:#fff;font-size:.6rem;padding:1px 5px;border-radius:4px;';
            badge.innerText = idx === 0 ? 'Main' : `#${idx + 1}`;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = '&times;';
            btn.title = 'Remove this photo';
            btn.style.cssText = 'position:absolute;top:-6px;right:-6px;background:#e8247e;color:#fff;border:none;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.2);line-height:1;';
            btn.onclick = () => { selectedFiles.splice(idx, 1); syncInput(); renderPreviews(); };

            wrapper.appendChild(img);
            wrapper.appendChild(badge);
            wrapper.appendChild(btn);
            grid.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });

    // Update drop zone text
    if (selectedFiles.length > 0) {
        dropZone.querySelector('p').innerText = `${selectedFiles.length} photo(s) selected — click to add more`;
    } else {
        dropZone.querySelector('p').innerText = 'Click or drag & drop photos here';
    }
}

function syncInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
}

function handleDrop(e) {
    e.preventDefault();
    dropZone.style.background = '#fff8fb';
    previewImages(e.dataTransfer.files);
}
</script>
</body>
</html>