<?php
// =========================
// CONFIG
// =========================
$secret_key = "YOUR_KORA_SECRET_KEY"; // <-- replace with your real Kora secret key
$base_url   = "https://api.korapay.com/merchant/api/v1";

// =========================
// HANDLE DEPOSIT
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['deposit_amount'])) {
    $amount = intval($_POST['deposit_amount']);
    $currency = "GHS";
    $tx_ref = "tx_" . time();

    $payload = [
        "amount" => $amount,
        "currency" => $currency,
        "redirect_url" => "https://yourdomain.com/index.php",
        "customer" => [
            "name" => "Cyrus User",
            "email" => "customer@example.com"
        ],
        "reference" => $tx_ref
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/charges/initialize");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] === "success") {
        $checkout_url = $result['data']['checkout_url'];
        header("Location: $checkout_url");
        exit;
    } else {
        echo "<script>alert('Deposit failed: " . json_encode($result) . "');</script>";
    }
}

// =========================
// HANDLE WITHDRAW
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['withdraw_amount'])) {
    $amount = intval($_POST['withdraw_amount']);
    $currency = "GHS";
    $tx_ref = "wd_" . time();

    $payload = [
        "reference" => $tx_ref,
        "destination" => [
            "type" => "mobile_money",
            "amount" => $amount,
            "currency" => $currency,
            "narration" => "Withdrawal",
            "recipient" => [
                "name" => $_POST['name'],
                "phone_number" => $_POST['phone_number'],
                "provider" => $_POST['provider']
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/payouts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $secret_key",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] === "success") {
        echo "<script>alert('Withdrawal successful');</script>";
    } else {
        echo "<script>alert('Withdrawal failed: " . json_encode($result) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cyrus Data Hub</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; padding:0; display:flex; }
    .sidebar { width:220px; background:#222; color:#fff; height:100vh; padding:20px; }
    .sidebar h2 { margin-bottom:20px; }
    .sidebar a { color:#fff; display:block; padding:10px; text-decoration:none; }
    .sidebar a:hover { background:#444; }
    .content { flex:1; padding:20px; }
    .hidden { display:none; }
    table { width:100%; border-collapse:collapse; margin:20px 0; }
    th, td { border:1px solid #ccc; padding:10px; text-align:left; }
    h1 { margin-top:0; }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Cyrus Data Hub</h2>
    <a href="#" onclick="showSection('bundles')">Bundles</a>
    <a href="#" onclick="showSection('deposit')">Deposit</a>
    <a href="#" onclick="showSection('withdraw')">Withdraw</a>
  </div>

  <div class="content">
    <h1>Welcome to Cyrus Data Hub</h1>

    <!-- Bundles -->
    <div id="bundles">
      <h2>MTN Bundles</h2>
      <table>
        <tr><th>Bundle</th><th>Price (GHC)</th></tr>
        <tr><td>3GB</td><td>17</td></tr>
        <tr><td>4GB</td><td>22</td></tr>
      </table>

      <h2>Vodafone Bundles</h2>
      <table>
        <tr><th>Bundle</th><th>Price (GHC)</th></tr>
        <tr><td>3GB</td><td>17</td></tr>
        <tr><td>4GB</td><td>22</td></tr>
      </table>
    </div>

    <!-- Deposit -->
    <div id="deposit" class="hidden">
      <h2>Deposit Funds</h2>
      <form method="POST" action="">
        <input type="number" name="deposit_amount" placeholder="Enter amount (₵)" required>
        <button type="submit">Deposit</button>
      </form>
    </div>

    <!-- Withdraw -->
    <div id="withdraw" class="hidden">
      <h2>Withdraw Funds</h2>
      <form method="POST" action="">
        <input type="number" name="withdraw_amount" placeholder="Enter amount (₵)" required><br>
        <input type="text" name="name" placeholder="Your Name" required><br>
        <input type="text" name="phone_number" placeholder="233XXXXXXXXX" required><br>
        <select name="provider" required>
          <option value="mtn">MTN</option>
          <option value="vodafone">Vodafone</option>
        </select><br>
        <button type="submit">Withdraw</button>
      </form>
    </div>
  </div>

  <script>
    function showSection(id) {
      document.getElementById("bundles").classList.add("hidden");
      document.getElementById("deposit").classList.add("hidden");
      document.getElementById("withdraw").classList.add("hidden");
      document.getElementById(id).classList.remove("hidden");
    }
  </script>
</body>
</html>
