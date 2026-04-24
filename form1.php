<?php
// Database Connection
$conn = new mysqli("localhost", "root", "", "cleaning_services");

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Fetch Packages from Database
$sql = "SELECT * FROM package_table";
$result = $conn->query($sql);

// Debugging - Check if the query runs successfully
if (!$result) {
    die("Query Failed: " . $conn->error);
}

$packages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script> <!-- QR Code Generator -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8eff1;
            text-align: center;
        }
        .booking-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
            text-align: left;
        }
        .booking-form h3 {
            color: #333;
            text-align: center;
            margin-bottom: 15px;
        }
        .form-label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        .booking-form select, .booking-form input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #218838;
        }
        #qrCodeContainer {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        #qrCode {
            margin: 10px auto;
            padding: 10px;
            background: white;
            display: inline-block;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>Book Your Cleaning Service</h2>

<!-- Booking Form -->
<div class="booking-form" id="bookingForm">
    <h3>Booking Details</h3>

    <label class="form-label">Select Package:</label>
    <select id="packageSelect" onchange="updatePackageDetails()">
        <option value="">-- Select a Package --</option>
        <?php foreach ($packages as $package) { ?>
            <option value="<?php echo $package['package_name']; ?>" data-price="<?php echo $package['price']; ?>">
                <?php echo $package['package_name']; ?> - ₹<?php echo $package['price']; ?>
            </option>
        <?php } ?>
    </select>

    <label class="form-label">Package Name:</label>
    <p id="packageName">Please select a package</p>

    <label class="form-label">Total Price:</label>
    <p id="totalPrice">₹0</p>

    <label class="form-label">Advance Payment (50%):</label>
    <p id="advancePrice">₹0</p>

    <label class="form-label">UPI ID:</label>
    <input type="text" id="upi" name="upi" placeholder="Enter UPI ID" required oninput="generateQRCode()">

    <label class="form-label">Booking Date:</label>
    <input type="date" id="bookingDate" name="booking_date" required>

    <!-- QR Code Display -->
    <div id="qrCodeContainer">
        <h3>Scan QR Code to Pay</h3>
        <div id="qrCode"></div>
    </div>

    <button class="btn" type="submit">Confirm Booking</button>
</div>

<script>
    function updatePackageDetails() {
        let select = document.getElementById("packageSelect");
        let selectedOption = select.options[select.selectedIndex];

        if (select.value === "") {
            document.getElementById("packageName").innerText = "Please select a package";
            document.getElementById("totalPrice").innerText = "₹0";
            document.getElementById("advancePrice").innerText = "₹0";
            document.getElementById("qrCodeContainer").style.display = "none";
            return;
        }

        let packageName = selectedOption.value;
        let price = selectedOption.getAttribute("data-price");

        document.getElementById("packageName").innerText = packageName;
        document.getElementById("totalPrice").innerText = `₹${price}`;
        document.getElementById("advancePrice").innerText = `₹${price * 0.5}`;

        generateQRCode();
    }

    function generateQRCode() {
        let upiId = document.getElementById("upi").value;
        let packageName = document.getElementById("packageName").innerText;
        let advancePrice = document.getElementById("advancePrice").innerText.replace("₹", "");

        if (!upiId || packageName === "Please select a package") {
            document.getElementById("qrCodeContainer").style.display = "none";
            return;
        }

        let upiPaymentUrl = `upi://pay?pa=${upiId}&pn=Cleaning%20Service&mc=&tid=&tr=&tn=Payment%20for%20${packageName}&am=${advancePrice}&cu=INR`;

        document.getElementById("qrCodeContainer").style.display = "block";
        document.getElementById("qrCode").innerHTML = "";
        new QRCode(document.getElementById("qrCode"), {
            text: upiPaymentUrl,
            width: 150,
            height: 150
        });
    }
</script>

</body>
</html>
