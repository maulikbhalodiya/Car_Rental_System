<html>
    <head>
        <style>
            /* Style for submenu */
            .sidebar ul li ul.submenu {
                display: none;
                list-style-type: none;
                padding-left: 20px;
            }

            .sidebar ul li:hover > ul.submenu {
                display: block;
            }

            .sidebar ul li ul.submenu li a {
                font-size: 14px;
                color: white;
                text-decoration: none;
                padding: 8px 0;
                display: block;
            }

            .sidebar ul li ul.submenu li a:hover {
                color: #007bff;
            }

        </style>
    </head>
<body>
    <div class="sidebar">
        <h1>Car Rental System | Admin Panel</h1>
        <ul>
            <li><a href="admindashboard.php" ><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage-brands.php"><i class="fas fa-car"></i> Brands</a></li>
            <li><a href="admin_view_requests.php"><i class="fas fa-file-alt"></i> View Pending Requests</a></li>
        
            <li>
                <a href="manage-vehicles.php"><i class="fas fa-car"></i> Vehicles</a>
                <ul class="submenu">
                    <li><a href="admin_car_status.php"><i class="fas fa-circle"></i> Vehicle Status</a></li>
                </ul>
            </li>
            <li><a href="manage_bookings.php"><i class="fas fa-book"></i> Bookings </a></li>
            <!-- <li><a href="#"><i class="fas fa-comments"></i> Manage Testimonials</a></li> -->
            <li><a href="manage-contact-queries.php"><i class="fas fa-envelope"></i> Manage Contact Queries</a></li>
            <li><a href="registered_users.php"><i class="fas fa-users"></i> Registered Users</a></li>
            <li><a href="admin_earnings.php"><i class="fa-solid fa-indian-rupee-sign"></i> Earnings Report</a></li>
            <!-- <li><a href="#"><i class="fas fa-user-plus"></i> Manage Subscribers</a></li> -->
        </ul>
    </div>
    <div class="content">
        <div class="top-bar">
            <h2>Dashboard</h2>
            <div class="user-info">
                <img src="admindp.png" alt="User Avatar">
                <span>Admin</span>
                <a href="adminlogin.php" class="logout-btn">Log Out</a>
            </div>
        </div>
</body></html>
