<?php
// Start the session
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'nc_homecare';

// Connect to the database
$connection = new mysqli($host, $username, $password, $database);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch logged-in user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, phone FROM user WHERE user_id = ?";
$stmt = $connection->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $user = ['first_name' => 'User', 'last_name' => 'User', 'email' => 'email@example.com', 'phone' => '+233000000000'];
    }
    $stmt->close();
} else {
    die("Error preparing statement: " . $connection->error);
}

$userData = [];
$result = $connection->query("SELECT * FROM user");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userData[] = $row;
    }
}

$services = [];
$result = $connection->query("SELECT * FROM service");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

if (!empty($services)) {
    foreach ($services as $service) {
    }
} else {
    echo "No services found.";
}

$bookingData = [];
$result = $connection->query("SELECT * FROM booking");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookingData[] = $row;
    }
}


$jobApplicationData = [];

$result = $connection->query("SELECT * FROM job_application");

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobApplicationData[] = $row;
        }
    } else {
        echo "No job applications found.";
    }
} else {
    echo "Error: " . $connection->error;
}


// Fetch all payments
$paymentData = [];
$result = $connection->query("SELECT * FROM payment");
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $paymentData[] = $row;
        }
    } else {
        echo "No payments found.";
    }
} else {
    echo "Error: " . $connection->error;
}

// Function to redirect to self (refresh the page)
function redirectToSelf()
{
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Add, Edit, and Delete actions for users and services
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Add User
    if (isset($_POST['add_user'])) {
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

        if ($first_name && $last_name && $email && $phone) {
            // Add new user to the database
            $stmt = $connection->prepare("INSERT INTO user (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $phone);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error adding user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Please provide valid input for all fields.";
        }
    }

    // Handle Delete User
    if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
        $user_id_to_delete = $_POST['user_id'];

        // Ensure the user ID exists before deleting
        if ($user_id_to_delete) {
            $stmt = $connection->prepare("DELETE FROM user WHERE user_id = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error deleting user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Invalid user ID.";
        }
    }

    // Handle Edit User
    if (isset($_POST['edit_user'])) {
        $user_id_to_edit = $_POST['user_id'];
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

        if ($user_id_to_edit && $first_name && $last_name && $email && $phone) {
            // Edit user details
            $stmt = $connection->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id_to_edit);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error editing user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Please provide valid input for all fields.";
        }
    }

    // Handle Add Service
    if (isset($_POST['add_service'])) {
        $service_name = filter_input(INPUT_POST, 'service_name', FILTER_SANITIZE_STRING);
        $service_description = filter_input(INPUT_POST, 'service_description', FILTER_SANITIZE_STRING);
        $service_price = filter_input(INPUT_POST, 'service_price', FILTER_VALIDATE_FLOAT);

        if ($service_name && $service_description && $service_price) {
            // Prepare and bind SQL statement to insert service data
            $stmt = $connection->prepare("INSERT INTO service (service_name, service_description, service_price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $service_name, $service_description, $service_price);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error adding service: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Please provide valid input for all fields.";
        }
    }

    // Handle Delete Service
    if (isset($_POST['delete_service']) && isset($_POST['service_id'])) {
        $service_id_to_delete = $_POST['service_id'];

        // Ensure the service ID exists before deleting
        if ($service_id_to_delete) {
            $stmt = $connection->prepare("DELETE FROM service WHERE service_id = ?");
            $stmt->bind_param("i", $service_id_to_delete);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error deleting service: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Invalid service ID.";
        }
    }

    // Handle Edit Service
    if (isset($_POST['edit_service'])) {
        $service_id_to_edit = $_POST['service_id'];
        $service_name = filter_input(INPUT_POST, 'service_name', FILTER_SANITIZE_STRING);
        $service_description = filter_input(INPUT_POST, 'service_description', FILTER_SANITIZE_STRING);
        $service_price = filter_input(INPUT_POST, 'service_price', FILTER_VALIDATE_FLOAT);

        if ($service_id_to_edit && $service_name && $service_description && $service_price) {
            // Edit service details
            $stmt = $connection->prepare("UPDATE service SET service_name = ?, service_description = ?, service_price = ? WHERE service_id = ?");
            $stmt->bind_param("ssdi", $service_name, $service_description, $service_price, $service_id_to_edit);
            if ($stmt->execute()) {
                redirectToSelf();
            } else {
                echo "Error editing service: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Please provide valid input for all fields.";
        }
    }
}

$connection->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link rel="stylesheet" href="useradmin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .avatar {
            border-radius: 50%;
            width: 40px;
        }

        .main-container {
            display: flex;
        }

        .vertical-navbar {
            width: 250px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>

<body>
    <header class="horizontal-navbar bg-primary text-white d-flex justify-content-between p-3">
        <h1>Admin Portal</h1>
        <div class="dropdown">
            <img src="./avatar.png" class="avatar" id="avatar" alt="Avatar">
            <span class="username"><?php echo htmlspecialchars($user['first_name']); ?></span>
            <div class="dropdown-content">
                <a href="#" onclick="loadContent('profile')"><i class="fas fa-user"></i> Profile</a>
                <a href="#" onclick="loadContent('settings')"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="vertical-navbar bg-light border">
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('dashboard')"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('messages')"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('jobs')"><i class="fas fa-briefcase"></i> Add Jobs</a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('profile')"><i class="fas fa-user"></i> View Profiles</a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('settings')"><i class="fas fa-cogs"></i> Settings</a></li>
            </ul>
        </nav>

        <div class="main-content" id="content-area">
            <h1>Welcome to the User Admin Portal</h1>
        </div>
    </div>

    <script>
        const contentArea = document.querySelector('#content-area');

        function loadContent(page) {
            contentArea.innerHTML = `<p>Loading...</p>`;
            setTimeout(() => {
                switch (page) {
                    case 'dashboard':
                        contentArea.innerHTML = `
   <div id="contentArea" class="container">
    <!-- Dashboard Header -->
    <div class="row">
        <div class="col-12 text-center">
            <h1 class="header-title">Homecare Dashboard</h1>
        </div>
    </div>

    <div class="row">
        <!-- Admin Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Admin Details</div>
                <div class="card-body">
                    <table class="table table-bordered" id="adminTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Last Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userData as $user) { ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                    <td><?php echo $user['last_name']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bookings Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Bookings</div>
                <div class="card-body">
                    <table class="table table-bordered" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User ID</th>
                                <th>Service ID</th>
                                <th>Date</th>
                                <th>Start Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookingData as $booking) { ?>
                                <tr>
                                    <td><?php echo $booking['booking_id']; ?></td>
                                    <td><?php echo $booking['user_id']; ?></td>
                                    <td><?php echo $booking['service_id']; ?></td>
                                    <td><?php echo $booking['booking_date']; ?></td>
                                    <td><?php echo $booking['start_time']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Job Applications Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Jobs Applications</div>
                <div class="card-body">
                    <table class="table table-bordered" id="jobApplicationsTable">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>User ID</th>
                                <th>Job Position</th>
                                <th>Application Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobApplicationData as $application) { ?>
                                <tr>
                                    <td><?php echo $application['application_id']; ?></td>
                                    <td><?php echo $application['user_id']; ?></td>
                                    <td><?php echo $application['job_position']; ?></td>
                                    <td><?php echo $application['application_status']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments Section -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Payments</div>
                <div class="card-body">
                    <table class="table table-bordered" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Amount</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentData as $payment) { ?>
                                <tr>
                                    <td><?php echo $payment['payment_id']; ?></td>
                                    <td><?php echo $payment['booking_id']; ?></td>
                                    <td><?php echo $payment['amount']; ?></td>
                                    <td><?php echo $payment['payment_status']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Services Section -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">Services</div>
                <div class="card-body">
                    <table class="table table-bordered" id="servicesTable">
                        <thead>
                            <tr>
                                <th>Service ID</th>
                                <th>Service Name</th>
                                <th>Service Description</th>
                                <th>Service Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service) { ?>
                                <tr>
                                    <td><?php echo $service['service_id']; ?></td>
                                    <td><?php echo $service['service_name']; ?></td>
                                    <td><?php echo $service['service_description']; ?></td>
                                    <td><?php echo $service['service_price']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
   <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
            margin-bottom: 30px;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #6c757d;
            color: #fff;
            font-size: 1.25rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .table thead {
            background-color: #f1f3f5;
            color: #495057;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        th, td {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: #5c6bc0;
            border-color: #5c6bc0;
            border-radius: 8px;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #3f51b5;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 30px;
        }

        .card-body {
            padding: 30px;
        }

        .container {
            margin-top: 50px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f1f3f5;
            margin-top: 30px;
            border-radius: 12px;
        }

        /* Ensure tables are scrollable on small screens */
        .table-responsive {
            overflow-x: auto;
        }

        /* Make card width more consistent and wrap content */
        .card {
            width: 100%;
        }

        /* Add responsive behavior */
        @media (max-width: 768px) {
            .header-title {
                font-size: 2rem;
            }
        }
    </style> `;
                        break;
                    case 'messages':
                        contentArea.innerHTML = `<h1>Messages</h1><p>No new messages.</p>`;
                        break;
                    case 'jobs':
                        contentArea.innerHTML = `
    <div class="form-container">
        <h1 class="form-title">Add Service</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST"  class="service-form">
            <div class="form-group">
                <label for="service_name" class="form-label">Service Name:</label>
                <input type="text" id="service_name" name="service_name" class="form-input" required placeholder="Enter service name">
            </div>

            <div class="form-group">
                <label for="service_description" class="form-label">Service Description:</label>
                <textarea id="service_description" name="service_description" rows="4" class="form-input" required placeholder="Enter service description"></textarea>
            </div>

            <div class="form-group">
                <label for="service_price" class="form-label">Service Price:</label>
                <input type="number" id="service_price" name="service_price" class="form-input" step="0.01" required placeholder="Enter price">
            </div>

            <div class="form-group">
                <button type="submit" name="add_service" class="submit-btn">Add Service</button>
            </div>
        </form>
    </div>
<div class="container mt-5">
    <h2 class="mb-4">Manage Service, Jobs, Payments, and Users</h2>

    <!-- Tabs to navigate between different tables -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="service-tab" data-bs-toggle="tab" href="#service" role="tab" aria-controls="service" aria-selected="true">Services</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="jobs-tab" data-bs-toggle="tab" href="#jobs" role="tab" aria-controls="jobs" aria-selected="false">Jobs Applications</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="payment-tab" data-bs-toggle="tab" href="#payment" role="tab" aria-controls="payment" aria-selected="false">Payments</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="user-tab" data-bs-toggle="tab" href="#user" role="tab" aria-controls="user" aria-selected="false">Users</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-3" id="myTabContent">
        <!-- Services Tab -->
        <div class="tab-pane fade show active" id="service" role="tabpanel" aria-labelledby="service-tab">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">Add New Service</button>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                 <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['service_id']; ?></td>
                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['service_description']); ?></td>
                            <td><?php echo htmlspecialchars($service['service_price']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal" onclick="editService(<?php echo $service['service_id']; ?>)">Edit</button>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">
                                    <button type="submit" name="delete_service" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                       <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No Service records found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Jobs Applications Tab -->
        <div class="tab-pane fade" id="jobs" role="tabpanel" aria-labelledby="jobs-tab">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addJobModal">Add Job Application</button>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>User ID</th>
                        <th>Job Position</th>
                        <th>Status</th>
                        <th>Application TimeStamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($jobApplications)): ?>
    <?php foreach ($jobApplications as $job): ?>
        <tr>
            <td><?php echo htmlspecialchars($job['application_id']); ?></td>
            <td><?php echo htmlspecialchars($job['user_id']); ?></td>
            <td><?php echo htmlspecialchars($job['job_position']); ?></td>
            <td><?php echo htmlspecialchars($job['application_status']); ?></td>
            <td><?php echo htmlspecialchars($job['application_timestamp']); ?></td>
            <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editJobModal" onclick="editJob(<?php echo $job['application_id']; ?>)">Edit</button>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="application_id" value="<?php echo $job['application_id']; ?>">
                    <button type="submit" name="delete_job" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
                        <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No Job records found.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- Payments Tab -->
        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Booking ID</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                         <th>Status</th>
                        <th>Payment TimeStamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($payments)): ?>
    <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
            <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
            <td><?php echo htmlspecialchars($payment['amount']); ?></td>
            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
            <td><?php echo htmlspecialchars($payment['payment_status']); ?></td>
            <td><?php echo htmlspecialchars($payment['payment_timestamp']); ?></td>
            <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPaymentModal" onclick="editPayment(<?php echo $payment['payment_id']; ?>)">Edit</button>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                    <button type="submit" name="delete_payment" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7">No payment records found.</td>
    </tr>
<?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- Users Tab -->
        <div class="tab-pane fade" id="user" role="tabpanel" aria-labelledby="user-tab">
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userData as $userData): ?>
                        <tr>
                            <td><?php echo $userData['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($userData['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($userData['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($userData['email']); ?></td>
                            <td><?php echo htmlspecialchars($userData['phone']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="editUser(<?php echo $userData['user_id']; ?>)">Edit</button>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $userData['user_id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modals -->
    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_add_service.php" method="POST">
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="service_description" class="form-label">Description</label>
                            <textarea class="form-control" id="service_description" name="service_description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="service_price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="service_price" name="service_price" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Other modals can follow similar patterns for Job, Payment, and User -->
</div>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .form-title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .service-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }

        .form-input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: #4e73df;
            outline: none;
        }

        .submit-btn {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #4e73df;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #2e59d9;
        }

        .submit-btn:active {
            background-color: #1e48b0;
        }
            
    </style>
    `;
                        break;

                    case 'profile':
                        contentArea.innerHTML = `
                            <div class="main-content" id="content-area">
                                    <h1>User Profiles</h1>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Date Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userData as $user): ?>
                                                <tr>
                                                    <td><?php echo $user['user_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['timestamp_created']); ?></td>
                                                    <td>
                                                        <!-- Edit button -->
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" onclick="editUser(<?php echo $user['user_id']; ?>)">Edit</button>

                                                        <!-- Delete button -->
                                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="display:inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <!-- Add User Form -->
                                    <h3>Add New User</h3>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                        <div class="form-group">
                                            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                                        </div>
                                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Edit User Modal (You can implement it using Bootstrap Modal) -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                                <input type="hidden" name="user_id" id="edit_user_id">
                                                <div class="form-group">
                                                    <input type="text" name="first_name" id="edit_first_name" class="form-control" placeholder="First Name" required>
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" name="last_name" id="edit_last_name" class="form-control" placeholder="Last Name" required>
                                                </div>
                                                <div class="form-group">
                                                    <input type="email" name="email" id="edit_email" class="form-control" placeholder="Email" required>
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" name="phone" id="edit_phone" class="form-control" placeholder="Phone Number" required>
                                                </div>
                                                <button type="submit" name="edit_user" class="btn btn-warning">Update User</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        break;
                    case 'settings':
                        contentArea.innerHTML = `
    <div class="set_container">
        <!-- Account Header -->
        <h1 class="set_page-title">Account Settings</h1>

        <!-- Job Alerts Section -->
        <div class="set_section set_job-alerts">
            <div class="set_section-header">
                <h2>Job Alerts <span class="set_beta-badge">Beta</span></h2>
                <button class="set_add-alert-btn btn btn-primary">+ Add job alert</button>
            </div>
            <div class="set_job-alert">
                <p class="set_job-title">Back End Developer</p>
                <p class="set_job-location">Germany</p>
                <div class="set_job-actions">
                    <button class="set_edit-btn btn btn-warning">✏️ Edit</button>
                    <button class="set_delete-btn btn btn-danger">🗑️ Delete</button>
                </div>
            </div>
            <p class="set_alert-info">
                This feature is currently in beta and is available only for jobs in Germany. You’ll receive one email per job alert each week, featuring up to 6 relevant job opportunities.
            </p>
        </div>

        <!-- Language Section -->
        <div class="set_section set_language-settings">
            <h2>Language Settings</h2>
            <label for="set_language-select">Select Language <span class="set_required">*</span></label>
            <select id="set_language-select" class="form-control">
                <option>English</option>
                <option>German</option>
                <option>French</option>
            </select>
            <a href="#" class="set_change-password">Change password</a>
            <div class="set_action-buttons">
                <button class="set_cancel-btn btn btn-secondary">Cancel</button>
                <button class="set_save-btn btn btn-success">Save Changes</button>
            </div>
        </div>

        <!-- Important Links Section -->
        <div class="set_section set_important-links">
            <h2>Important Links</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="#">Terms & Conditions</a></li>
                <li class="list-group-item"><a href="#">Data Privacy Policy</a></li>
            </ul>
            <p class="set_options-info">
                If you don’t agree with the terms, <a href="#">see your options</a>.
            </p>
        </div>
    </div>
    `;
                        break;

                    default:
                        contentArea.innerHTML = `<h1>404: Page not found</h1>`;
                }
            }, 500);
        }

        function editUser(userId) {
            var userRow = document.querySelector('tr[data-user-id="' + userId + '"]');
            var firstName = userRow.querySelector('.first-name').innerText;
            var lastName = userRow.querySelector('.last-name').innerText;
            var email = userRow.querySelector('.email').innerText;
            var phone = userRow.querySelector('.phone').innerText;

            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const tabTitles = document.querySelectorAll('.tab-title');
            const tabContents = document.querySelectorAll('.tab-content');

            tabTitles.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove the active class from all tabs and content
                    tabTitles.forEach(title => title.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add the active class to the clicked tab and the corresponding content
                    tab.classList.add('active');
                    const activeTabContent = document.getElementById(tab.getAttribute('data-tab'));
                    activeTabContent.classList.add('active');
                });
            });

            // Optionally, set the default active tab
            tabTitles[0].classList.add('active');
            tabContents[0].classList.add('active');
        });

        const adminData = [{
                id: 1,
                username: 'admin1',
                role: 'Manager'
            },
            {
                id: 2,
                username: 'admin2',
                role: 'Supervisor'
            }
        ];

        const bookingsData = [{
                bookingId: 101,
                userId: 201,
                serviceId: 301,
                date: '2024-11-20',
                status: 'Completed'
            },
            {
                bookingId: 102,
                userId: 202,
                serviceId: 302,
                date: '2024-11-21',
                status: 'Pending'
            }
        ];

        const jobApplicationsData = [{
                applicationId: 401,
                userId: 501,
                jobPosition: 'Caregiver',
                status: 'Approved'
            },
            {
                applicationId: 402,
                userId: 502,
                jobPosition: 'Nurse',
                status: 'Pending'
            }
        ];

        const paymentsData = [{
                paymentId: 601,
                bookingId: 101,
                amount: 100,
                status: 'Paid'
            },
            {
                paymentId: 602,
                bookingId: 102,
                amount: 150,
                status: 'Unpaid'
            }
        ];

        const servicesData = [{
                serviceId: 301,
                name: 'Home Cleaning',
                description: 'Deep cleaning of home',
                price: '$50'
            },
            {
                serviceId: 302,
                name: 'Medical Assistance',
                description: '24/7 medical support',
                price: '$100'
            }
        ];

        // Function to render data into tables
        function renderTable(tableId, data) {
            const tableBody = document.querySelector(`#${tableId} tbody`);
            tableBody.innerHTML = ''; // Clear existing rows
            data.forEach(item => {
                let row = '<tr>';
                for (let key in item) {
                    row += `<td>${item[key]}</td>`;
                }
                row += '</tr>';
                tableBody.innerHTML += row;
            });
        }

        // Rendering all tables with mock data
        renderTable('adminTable', adminData);
        renderTable('bookingsTable', bookingsData);
        renderTable('jobApplicationsTable', jobApplicationsData);
        renderTable('paymentsTable', paymentsData);
        renderTable('servicesTable', servicesData);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>