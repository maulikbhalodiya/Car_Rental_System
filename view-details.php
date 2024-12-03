<?php
session_start(); // Start the session to check if the user is logged in

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

include('db_connection.php');

// Initialize variables
$photos = []; // Initialize the photos array
$brand = '';
$vehicleName = '';
$price = 0;
$hprice = 0;
$fuelType = '';
$seatingCapacity = '';
$engine = '';
$transmission = '';
$power = '';
$torque = '';
$fuelTankCapacity = '';
$mileage = '';
$year = '';

// Fetch car details from the database
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT v.*, b.brand_name FROM vehicles v JOIN brands b ON v.brand_id = b.id WHERE v.id = '$id'";
    $result = $conn->query($query);
    $car = $result->fetch_assoc();

    // Extract car data (make sure columns match your DB)
    $vehicleName = $car['vehicle_name'];
    $brand = $car['brand_name'];
    $price = $car['price_per_day'];
    $hprice = $car['rent_per_hour'];
    $fuelType = $car['fuel_type'];
    $seatingCapacity = $car['seating_capacity'];
    $engine = $car['engine'];
    $transmission = $car['transmission'];
    $power = $car['power'];
    $torque = $car['torque'];
    $fuelTankCapacity = $car['fuel_tank_capacity'];
    $mileage = $car['mileage'];
    $year = $car['model_year'];
    $photos = array($car['photos'], $car['photos1'], $car['photos2'], $car['photos3']);
}

// Calculate the average rating for this car
$avgQuery = "SELECT AVG(rating) AS avg_rating, COUNT(rating) AS count FROM vehicle_ratings WHERE vehicle_id = '$id'";
$avgResult = $conn->query($avgQuery);
$avgRow = $avgResult->fetch_assoc();
$averageRating = round($avgRow['avg_rating'], 1);
$totalRatings = $avgRow['count'];


// Display individual user ratings
// $reviewsQuery = "SELECT r.rating, r.comment, u.fullname, r.rating_date 
//                  FROM vehicle_ratings r 
//                  JOIN users u ON r.user_id = u.id 
//                  WHERE r.vehicle_id = '$id'
//                  ORDER BY r.rating_date DESC 
//                  LIMIT 5"; // Show 5 most recent ratings
// $reviewsResult = $conn->query($reviewsQuery);

// if ($reviewsResult->num_rows > 0) {
//     echo "<div class='user-reviews'>";
//     echo "<h4>User Reviews</h4>";
//     while ($review = $reviewsResult->fetch_assoc()) {
//         echo "<div class='review'>";
//         echo "<strong>" . htmlspecialchars($review['fullname']) . "</strong>";
//         echo "<span>Rating: " . $review['rating'] . "/5</span>";
//         echo "<p>" . htmlspecialchars($review['comment']) . "</p>";
//         echo "<small>Reviewed on: " . $review['rating_date'] . "</small>";
//         echo "</div>";
//     }
//     echo "</div>";
// } else {
//     echo "<p>No reviews yet.</p>";
// }
// logic for raitng fomr submit
if (isset($_POST['submit_rating']) && $isLoggedIn) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $userId = $_SESSION['user_id']; 
    
    // Check if the user has already rated this car
    $checkQuery = "SELECT * FROM vehicle_ratings WHERE user_id = '$userId' AND vehicle_id = '$id'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('You have already rated this car.')</script>";
    } else {
        // Insert the rating into the database
        $insertQuery = "INSERT INTO vehicle_ratings (vehicle_id, user_id, rating, comment) VALUES ('$id', '$userId', '$rating', '$comment')";
        if ($conn->query($insertQuery)) {
            echo "<p>Thank you for your rating!</p>";
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details</title>
    <!-- Include Bootstrap CSS for Carousel -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="home.css"> <!-- For header and footer -->
    <link rel="stylesheet" href="car-listing.css"> <!-- For car listing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <style>
         body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        } 
        .details {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center child elements */
            width: 100%; /* Full width */
        }

        .car-info {
            width: 100%;
            max-width: 1200px; /* Limit maximum width */
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            margin-left: auto; /* Center align */
            margin-right: auto; /* Center align */
        }


        .car-info-left {
            width: 65%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .car-info-right {
            width: 30%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .price {
            font-size: 20px;
            color: #ff6600;
        }

        .book-now-form {
            margin-top: 20px;
        }

        .book-now-form input, .book-now-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .book-now-form button {
            width: 100%;
            padding: 15px;
            background-color: #0ef;
            border: none;
            color: white;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .book-now-form button:hover {
            background-color: #008fbb;
        }
        .car-specs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .car-specs div {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 5px;
            width: 24%;
        }

        .car-specs i {
            font-size: 24px;
            color: #0ef;
        }

        .car-specs div span {
            display: block;
            margin-top: 5px;
        }

        .description, .accessories {
            margin-top: 20px;
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
        }

        .description h3, .accessories h3 {
            font-size: 22px;
        }
/* 
        .carousel-inner img {
            width: 800;
            height: 560;
        } 
        .carousel-inner .row {
            display: flex;
        }
        .carousel-inner .col {
            padding: 0;
        } */


        .carousel-inner .row {
            display: flex;
        }

        .carousel-inner .col-md-3 {
            padding: 0 5px;
        }

        .carousel-inner img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: orange; /* Change the control color as per your preference */
        }

        .rating-form, .average-rating, .user-reviews {
    margin-top: 20px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.rating-form h3, .user-reviews h4 {
    margin-bottom: 10px;
}

.rating-form label, .rating-form select, .rating-form textarea, .rating-form button {
    display: block;
    width: 100%;
    margin-bottom: 10px;
}

.review {
    border-bottom: 1px solid #ddd;
    padding: 10px 0;
}

.review strong {
    color: #333;
}

.review span {
    color: #f39c12;
}

.review p {
    margin: 5px 0;
    color: #666;
}

.review small {
    color: #999;
}
/*design of rating form*/ 
/* Star Rating - Make sure all stars appear in a single row */
/* Star Rating - Make sure all stars appear in a single row */
.star-rating {
        display: flex; /* This ensures all stars are in a row */
        justify-content: flex-start; /* Aligns stars to the left */
        gap: 5px; /* Adds a small space between the stars */
        font-size: 30px; /* Size of the stars */
    }

    .star-rating input {
        display: none; /* Hide the radio buttons */
    }

    .star-rating label {
        color: #ccc; /* Default color for unselected stars */
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .star-rating input:checked ~ label {
        color: #ffcc00; /* Color for selected stars */
    }

    .star-rating input:not(:checked) ~ label:hover {
        color: #f39c12; /* Color on hover before selecting */
    }

    .star-rating input:checked ~ label:hover {
        color: #f39c12; /* Change color on hover after selection */
    }

    /* Styling for the comment textarea */
    #comment {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ddd;
        resize: vertical; /* Allow vertical resize only */
        margin-bottom: 15px;
    }

    /* Styling for the submit button */
    button[type="submit"] {
        width: 100%;
        padding: 15px;
        background-color: #0ef;
        border: none;
        color: white;
        font-size: 18px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
        background-color: #008fbb;
    }

    /* General form styling */
    .rating-form {
        background-color: #f4f4f4;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    .rating-form h3 {
        font-size: 24px;
        margin-bottom: 10px;
    }

    /* Optional: Styling for labels and form elements */
    label {
        font-size: 18px;
        margin-bottom: 5px;
        display: block;
    }

    /* Optional: Add spacing between stars and comment box */
    .star-rating {
        margin-bottom: 10px;
    }

    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <?php include('header.php')?>

<div class="details">
    <div id="carPhotosCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
        <div class="carousel-inner">
            <!-- Carousel items -->
            <div class="carousel-item active">
                <div class="row">
                    <?php if (!empty($photos)) : ?>
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="col-md-4">
                                <img class="d-block w-100" src="images/vehicles/<?php echo htmlspecialchars($photos[$i]); ?>" alt="Car Image <?php echo $i + 1; ?>" style="height:300px;">
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $totalPhotos = count($photos);
            for ($i = 3; $i < $totalPhotos; $i += 1): ?>
                <div class="carousel-item">
                    <div class="row">
                        <?php for ($j = 0; $j < 3; $j++): ?>
                            <div class="col-md-4">
                                <?php
                                $photoIndex = ($i + $j) % $totalPhotos; // Loop through the images
                                ?>
                                <img class="d-block w-100" src="images/vehicles/<?php echo htmlspecialchars($photos[$photoIndex]); ?>" alt="Car Image <?php echo $photoIndex + 1; ?>" style="height:300px;">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Carousel controls -->
        <a class="carousel-control-prev" href="#carPhotosCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carPhotosCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>



        <div class="car-info">
            <div class="car-info-left">
                <h1><?php echo htmlspecialchars($brand) . ', ' . htmlspecialchars($vehicleName); ?></h1>
                
                <div class="car-specs">
                    <div>
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo htmlspecialchars($year); ?></span>
                    </div>
                    <div>
                        <i class="fas fa-gas-pump"></i>
                        <span><?php echo htmlspecialchars($fuelType); ?></span>
                    </div>
                    <div>
                        <i class="fas fa-users"></i>
                        <span><?php echo htmlspecialchars($seatingCapacity); ?> Seater</span>
                    </div>
                </div>

                <div class="description">
                    <h3>Vehicle Details</h3>
                    <p><strong>Engine:</strong> <?php echo htmlspecialchars($engine); ?></p>
                    <p><strong>Transmission:</strong> <?php echo htmlspecialchars($transmission); ?></p>
                    <p><strong>Power:</strong> <?php echo htmlspecialchars($power); ?> bhp</p>
                    <p><strong>Torque:</strong> <?php echo htmlspecialchars($torque); ?> Nm</p>
                    <p><strong>Fuel Tank Capacity:</strong> <?php echo htmlspecialchars($fuelTankCapacity); ?> L</p>
                    <p><strong>Mileage:</strong> <?php echo htmlspecialchars($mileage); ?> Kmpl</p>
                    <p><strong>Ratings:</strong> <?php echo htmlspecialchars($averageRating . " / 5 (" . $totalRatings . " Ratings)"); ?></p>

                   <!-- User Reviews Section -->
    <h4>User Reviews</h4>
    <?php
    // Fetch reviews from the database
    $reviewsQuery = "SELECT r.rating, r.comment, u.fullname, r.rating_date 
                     FROM vehicle_ratings r 
                     JOIN users u ON r.user_id = u.id 
                     WHERE r.vehicle_id = '$id'
                     ORDER BY r.rating_date DESC 
                     LIMIT 5"; // Show 5 most recent ratings
    $reviewsResult = $conn->query($reviewsQuery);

    if ($reviewsResult->num_rows > 0) {
        echo "<div class='user-reviews'>";
        while ($review = $reviewsResult->fetch_assoc()) {
            echo "<div class='review'>";
            echo "<strong>" . htmlspecialchars($review['fullname']) . "</strong>";
            echo "<span>Rating: " . $review['rating'] . "/5</span>";
            echo "<p>" . htmlspecialchars($review['comment']) . "</p>";
            echo "<small>Reviewed on: " . $review['rating_date'] . "</small>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No reviews yet.</p>";
    }
    ?>

                </div>
            </div>

            <div class="car-info-right">
                <h2>Price</h2>
                <div class="price">₹<?php echo htmlspecialchars($price); ?> / Day   ||   ₹<?php echo htmlspecialchars($hprice); ?> / Hour</div>
                
                     <!-- Buttons to toggle between Day and Hour booking -->
                  <div class="toggle-buttons">
                     <button class="btn btn-primary" id="day-btn" type="button">Book by Day</button>
                     <button class="btn btn-primary" id="hour-btn" type="button">Book by Hour</button>
                  </div>

                  <!-- Form for Day Selection (shown by default) -->
                  <form action="book-now.php?id=<?php echo $id; ?>" method="POST" class="book-now-form" id="day-form">
                     <input type="date" name="start_date" id="day-start-date" placeholder="Start Date" required>
                     <input type="date" name="end_date" id="day-end-date" placeholder="End Date" required>
                     <input type="hidden" name="booking_type" value="day">
                     <input type="hidden" name="vehicle_id" value="<?php echo $id; ?>">
                     <textarea placeholder="Message"></textarea>
                     <button type="submit">Book Now</button>
                  </form>

                  <!-- Form for Hour Selection (hidden by default) -->
                  <form action="book-now.php?id=<?php echo $id; ?>" method="POST" class="book-now-form" id="hour-form" style="display: none;">
                     <input type="date" name="date" id="hour-date" placeholder="Select Date" required>
                     <input type="time" name="start_time" id="hour-start-time" placeholder="Start Time" required>
                     <input type="time" name="end_time" id="hour-end-time" placeholder="End Time" required>
                     <input type="hidden" name="booking_type" value="hour">
                     <input type="hidden" name="vehicle_id" value="<?php echo $id; ?>">
                     <textarea placeholder="Message"></textarea>
                     <button  type="submit">Book Now</button>
                  </form>
             
                  <!-- for rating this car -->
                        <?php if ($isLoggedIn): ?>
                           <div class="rating-form">
   
        <!-- Star Rating System -->
        <div class="rating-form">
    <h3>Rate this Car</h3>
    <form action="view-details.php?id=<?php echo $id; ?>" method="POST">
        <label for="rating">Rating (1 to 5):</label>
        
        <!-- Star Rating System (All stars in a single row) -->
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5" />
            <label for="star5" class="star">&#9733;</label>

            <input type="radio" id="star4" name="rating" value="4" />
            <label for="star4" class="star">&#9733;</label>

            <input type="radio" id="star3" name="rating" value="3" />
            <label for="star3" class="star">&#9733;</label>

            <input type="radio" id="star2" name="rating" value="2" />
            <label for="star2" class="star">&#9733;</label>

            <input type="radio" id="star1" name="rating" value="1" />
            <label for="star1" class="star">&#9733;</label>
        </div>

        <!-- Comment Box -->
        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment" placeholder="Leave a comment..." rows="4"></textarea>
        
        <!-- Submit Button -->
        <button type="submit" name="submit_rating">Submit Rating</button>
    </form>
</div>
        <?php else: ?>
            <p>Please <a href="login.php">log in</a> to rate this car.</p>
        <?php endif; ?>

                  <script>
                     // Get today's date in the format yyyy-MM-dd
                     function getTodayDate() {
                        const today = new Date();
                        const year = today.getFullYear();
                        const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based, so add 1
                        const day = String(today.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                     }

                     // Set the min date for the Day form to today
                     const today = getTodayDate();
                     document.getElementById('day-start-date').min = today;
                     document.getElementById('day-end-date').min = today;

                     // Event listeners for toggling between Day and Hour forms
                     document.getElementById('day-btn').addEventListener('click', function() {
                        document.getElementById('day-form').style.display = 'block';
                        document.getElementById('hour-form').style.display = 'none';
                     });

                     document.getElementById('hour-btn').addEventListener('click', function() {
                        const hourDateInput = document.getElementById('hour-date');
                        hourDateInput.min = today; // No past date allowed, start from today
                        hourDateInput.value = ''; // Reset if previously filled

                        document.getElementById('day-form').style.display = 'none';
                        document.getElementById('hour-form').style.display = 'block';
                     });

                     // Disable end time selection if it's earlier than start time
                     document.getElementById('hour-start-time').addEventListener('change', function() {
                        const startTime = this.value;
                        const endTimeInput = document.getElementById('hour-end-time');
                        endTimeInput.min = startTime; // Set the end time min to start time
                     });

                     // Validate end time on change
                    //  document.getElementById('hour-end-time').addEventListener('change', function(event) {
                    //     const startTime = document.getElementById('hour-start-time').value;
                    //     const endTime = this.value;

                    //     // Check if end time is earlier than or not equal to start time
                    //     if (endTime < startTime) {
                    //        alert("End Time cannot be earlier than Start Time.");
                    //        this.value = ''; // Reset the end time
                    //        event.preventDefault(); // Prevent form submission
                    //     } 'else if (startTime && endTime && startTime !== endTime) {
                    //        alert("For hourly rentals, start and end times must be the same.");
                    //        this.value = startTime; // Reset to start time
                    //        event.preventDefault(); // Prevent form submission
                    //     }
                    //  });
                     
                    document.getElementById('hour-end-time').addEventListener('change', function(event) {
                    const startTime = document.getElementById('hour-start-time').value;
                    const endTime = this.value;

                    // Check if end time is earlier than start time
                    if (endTime < startTime) {
                        alert("End Time cannot be earlier than Start Time.");
                        this.value = ''; // Reset the end time
                        event.preventDefault(); // Prevent form submission
                    }
                });


                     // Disable end date selection if it's earlier than start date for day booking
                     document.getElementById('day-start-date').addEventListener('change', function() {
                        document.getElementById('day-end-date').min = this.value;
                     });

               //Rating logix
               const stars = document.querySelectorAll('.star-rating label');
    stars.forEach(star => {
        star.addEventListener('mouseover', () => {
            const index = Array.from(stars).indexOf(star);
            for (let i = 0; i <= index; i++) {
                stars[i].style.color = '#f39c12'; // Change color on hover
            }
            for (let i = index + 1; i < stars.length; i++) {
                stars[i].style.color = '#ccc'; // Reset color for non-hovered stars
            }
        });

        star.addEventListener('mouseout', () => {
            const checkedStar = document.querySelector('.star-rating input:checked');
            if (checkedStar) {
                const checkedIndex = Array.from(stars).indexOf(checkedStar.nextElementSibling);
                for (let i = 0; i <= checkedIndex; i++) {
                    stars[i].style.color = '#ffcc00'; // Keep selected stars yellow
                }
                for (let i = checkedIndex + 1; i < stars.length; i++) {
                    stars[i].style.color = '#ccc'; // Reset non-selected stars
                }
            } else {
                stars.forEach(s => s.style.color = '#ccc'); // Reset on mouse out if no star is selected
            }
        });
    });
                  </script>
            </div>
            
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
