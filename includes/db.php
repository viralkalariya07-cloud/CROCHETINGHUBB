<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch website settings globally
$site_settings = [
    'website_name' => 'CrochetingHubb',
    'support_email' => 'support@crochetinghub.com',
    'support_phone' => '',
    'currency' => 'INR',
    'gst_percentage' => 0.00,
    'shipping_charge' => 0.00
];

$settings_table_check = $conn->query("SHOW TABLES LIKE 'website_settings'");
if ($settings_table_check && $settings_table_check->num_rows > 0) {
    $res = $conn->query("SELECT * FROM website_settings LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $site_settings = $res->fetch_assoc();
    }
}
?>
