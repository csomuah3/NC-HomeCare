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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_POST['user_id'];
  $service_id = $_POST['service_id'];

  // Insert the application into the database
  $sql = "INSERT INTO jobs_application (user_id, service_id, application_status) VALUES (?, ?, 'Pending')";

  if ($stmt = $connection->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $service_id);
    if ($stmt->execute()) {
      echo "Your application has been submitted successfully!";
    } else {
      echo "Error: " . $stmt->error;
    }
    $stmt->close();
  } else {
    echo "Error: " . $connection->error;
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
        <li class="nav-item"><a href="#" class="nav-link" onclick="loadContent('jobs')"><i class="fas fa-briefcase"></i>Jobs for You</a></li>
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
              <div class="header">
                        <h1>Welcome to the Dashboard</h1>
                    </div>
                    <div class="cards">
                        <div class="card">
                            <h3>100</h3>
                            <p>Active Jobs</p>
                        </div>
                        <div class="card">
                            <h3>50</h3>
                            <p>Applied Candidates</p>
                        </div>
                        <div class="card">
                            <h3>20</h3>
                            <p>Pending Approvals</p>
                        </div>
                    </div>`;
            break;
          case 'messages':
            contentArea.innerHTML = `<h1>Messages</h1><p>No new messages.</p>`;
            break;
          case 'jobs':
            contentArea.innerHTML = `
        <div class="jobs_header">
          <h1>Jobs for you</h1>
          <div class="jobs_tabs">
            <div class="jobs_tab active">Recommended</div>
            <div class="jobs_tab">Saved</div>
          </div>
          <div class="dropdown">
            <select>
              <option>All job alerts</option>
              <option>Custom alerts</option>
            </select>
          </div>
        </div>

<!-- Job Cards -->
<?php
if (!empty($services)) {
  foreach ($services as $service) {
    echo '
    <div class="jobs_job-card">
      <div class="jobs_job-info">
        <div class="jobs_logo">
          <!-- Add company logo or image here if available -->
        </div>
        <div class="jobs_job-details">
          <h3 class="jobs_job-title">' . htmlspecialchars($service['service_name']) . ' (m/w/d)</h3>
          <p class="jobs_job-meta">' . htmlspecialchars($service['service_description']) . '</p>
          <p class="jobs_job-meta">Price: $' . htmlspecialchars($service['service_price']) . '</p>
        </div>
      </div>
      <div class="job-actions">
        <button class="apply-btn">Apply</button>
      </div>
    </div>';
  }
} else {
  echo "<p>No services available at the moment.</p>";
}
?>

 <style>
 /* Container for the job header */
.jobs_header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: #f8f9fa;
  border-bottom: 2px solid #e2e6ea;
}

/* Styling for the job tabs */
.jobs_tabs {
  display: flex;
  gap: 15px;
}

.jobs_tab {
  padding: 10px 20px;
  background-color: #ffffff;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.jobs_tab.active {
  background-color: #007bff;
  color: white;
}

.jobs_tab:hover {
  background-color: #f1f1f1;
}

/* Dropdown for job alerts */
.dropdown select {
  padding: 8px 15px;
  border-radius: 30px;
  border: 1px solid #ccc;
  background-color: #ffffff;
  font-size: 16px;
  cursor: pointer;
}

/* Job card container */
.jobs_job-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  margin: 10px 0;
  border-radius: 5px;
  background-color: #ffffff;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

/* Job info section */
.jobs_job-info {
  display: flex;
  gap: 20px;
  align-items: center;
}

.jobs_logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #007bff;
}

.jobs_job-details {
  display: flex;
  flex-direction: column;
}

.jobs_job-title {
  font-size: 18px;
  font-weight: bold;
}

.jobs_job-meta {
  font-size: 14px;
  color: #888;
}

/* Apply button */
.apply-btn {
  padding: 10px 20px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
}

.apply-btn:hover {
  background-color: #218838;
}
</style>
`;
            break;

          case 'profile':
            contentArea.innerHTML = `
                          <div class="container">
                        <h1>Candidate Profile</h1>
                        <p>Stand out against other candidates. Enrich your applications with your profile.</p>

                        <!-- Profile Section -->
                        <div class="section">
                            <div class="profile-header">
                                <!-- Use the dynamically fetched user avatar -->
                                <img src="./avatar.png" alt="Profile Avatar">
                                <div class="profile-info">
                                   <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                                    <p><i class="fas fa-map-marker-alt"></i> Ghana</p>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>

                                </div>
                                <i class="fas fa-pen edit-icon"></i>
                            </div>
                        </div>

                        <!-- CV Section -->
                        <div class="section">
                            <h2>CV</h2>
                            <p>This CV will by default be used for your future applications. You will always have the option to upload another CV during each application process.</p>
                            <div class="cv-section">
                                <div class="cv-details">
                                    <i class="fas fa-file-alt cv-icon"></i>
                                    <span> </span>
                                </div>
                                <div class="cv-actions">
                                    <button title="View"><i class="fas fa-eye"></i></button>
                                    <button title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Links Section -->
                        <div class="section">
                            <h2>Professional links</h2>
                            <i class="fas fa-pen edit-icon"></i>
                            <div class="links-section">
                                <div>
                                    <p>LinkedIn</p>
                                    <a href="#" target="_blank">      </a>
                                    <p>GitHub</p>
                                    <a href="#" target="_blank">      </a>
                                </div>
                            </div>
                        </div>

                        <!-- Completeness Section -->
                        <div class="completeness-section">
                            <h4>Completeness</h4>
                            <ul>
                                <li>✔ Profile photo</li>
                                <li>✔ CV</li>
                                <li>✔ Professional links</li>
                            </ul>
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

    // Function to render data into tables
    function renderTable(tableId, data) {
      const tableBody = document.querySelector(`#${tableId} tbody`);
      tableBody.innerHTML = ''; 
      data.forEach(item => {
        let row = '<tr>';
        for (let key in item) {
          row += `<td>${item[key]}</td>`;
        }
        row += '</tr>';
        tableBody.innerHTML += row;
      });
    }

    function openApplyForm(service_id) {
      document.getElementById('service_id').value = service_id;
      document.getElementById('apply-modal').style.display = 'block';
    }

    function closeApplyForm() {
      document.getElementById('apply-modal').style.display = 'none';
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