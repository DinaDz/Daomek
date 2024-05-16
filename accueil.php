<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
//erruer
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';
mysqli_set_charset($con, "utf8");
if (!isset($_SESSION['id'])) {
$sql_announcements = "SELECT proprietes.*, GROUP_CONCAT(Images.path) AS image_paths
                      FROM proprietes 
                      LEFT JOIN Images ON proprietes.id_p = Images.id_p
                      GROUP BY proprietes.id_p 
                      ORDER BY proprietes.id_p DESC
                      LIMIT 9";
$result_announcements = mysqli_query($con, $sql_announcements);}
else {
  $userId = $_SESSION['id'];

  $sql_announcements = "SELECT proprietes.*, GROUP_CONCAT(Images.path) AS image_paths
                      FROM proprietes 
                      LEFT JOIN Images ON proprietes.id_p = Images.id_p
                      WHERE 
                        proprietes.id <> $userId
                      GROUP BY proprietes.id_p 
                      ORDER BY proprietes.id_p DESC
                      LIMIT 9";
$result_announcements = mysqli_query($con, $sql_announcements);

// Fetch user's favorites
$sql_favorites = "SELECT id_p FROM favoris WHERE id = $userId";
$result_favorites = mysqli_query($con, $sql_favorites);
$user_favorites = array();

if ($result_favorites->num_rows > 0) {
    while ($row = $result_favorites->fetch_assoc()) {
        $user_favorites[] = $row['id_p']; // Store the id_p values in an array
    }
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="accueil.css">
</head>

<body>
  <?php
  // Start or resume the session
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  // Check if the user is logged in
  if (isset($_SESSION['id']) && isset($_SESSION['username'])) {
    // User is logged in, include the navigation bar for logged-in users
    include 'navbar_loggedin.php';
  } else {
    // User is not logged in, include the navigation bar for non-logged-in users
    include 'navbar.php';
  }
  ?>
  <div class="dic">
    <div class="welcome">
      <h1 align="center" class="title">Bienvenus à Propriété Parfait</h1>
      <h2 align="center" id="st">votre guide qui vous accompagne pour trouver votre maison</h2>
    </div>
  </div>

  <div class="recherche">

    <form action="cherche.php" method="post">

      <label for="">À la recherche de</label>

      <select name="type_b" id="type_b">

        <option value=""> </option>

        <?php

        $sql_bien = "SELECT type_b FROM bien";

        $result_bien = mysqli_query($con, $sql_bien);

        if ($result_bien->num_rows > 0) {

          while ($row = $result_bien->fetch_assoc()) {

            echo "<option value='" . $row["type_b"] . "'>" . $row["type_b"] . "</option>";
          }
        } else {

          echo "0 résultats";
        }

        ?>

      </select>



      <label for="">Type de l'opération</label>

      <select name="type_o" id="type_o">

        <option value=""> </option>

        <?php

        $sql_op = "SELECT type_o FROM operation";

        $result_op = mysqli_query($con, $sql_op);

        if ($result_op->num_rows > 0) {

          while ($row = $result_op->fetch_assoc()) {

            echo "<option value='" . $row["type_o"] . "'>" . $row["type_o"] . "</option>";
          }
        } else {

          echo "0 résultats";
        } ?>

      </select>

      <label for="">wilaya</label>

      <select name="wilaya" id="wilaya">

        <option value=""> </option>

        <?php

        $sql_wilayas = "SELECT nom_w FROM wilaya";

        $result_wilayas = mysqli_query($con, $sql_wilayas);

        if ($result_wilayas->num_rows > 0) {

          while ($row = $result_wilayas->fetch_assoc()) {

            echo "<option value='" . $row["nom_w"] . "'>" . $row["nom_w"] . "</option>";
          }
        } else {

          echo "0 résultats";
        } ?>

      </select>

      <label for="">Dimension</label>

      <input type="text" id="dimension" name="dimension">

      <label for="">prix</label>

      <input type="text" id="prix" name="prix">

      <input type="submit" name="cherche" value="rechercher">

    </form>
  </div>

  <div class="announcements-slider">
    <div class="announcements">
      <?php
      // Check if there are announcements
      if (mysqli_num_rows($result_announcements) > 0) {
        // Loop through each announcement
        while ($row = mysqli_fetch_assoc($result_announcements)) {
          // Display announcement details
          echo '<div class="announcement">';

          // Start slider container for current announcement
          echo '<div class="slider">';

          // Navigation Arrows 
          echo '<button class="prev"><i class="fas fa-chevron-left"></i></button>';
          echo '<button class="next"><i class="fas fa-chevron-right"></i></button>';

          // Add a container for slider images
          echo '<div class="slider-container">';

          // Explode image paths into an array
          $image_paths = explode(",", $row['image_paths']);

          // Check if there are images for the current announcement
          if (!empty($image_paths)) {
            // Loop through each image path
            foreach ($image_paths as $image_path) {
              // Display image in the slider
              echo '<img src="' . $image_path . '" alt="Announcement Image">';
            }
          }

          // Close the slider container
          echo '</div>';

          // End slider container for current announcement
          echo '</div>';

          echo '<div class="content">';
          echo '<div class="price">';
          echo '<h3>' . $row['prix'] . ' DA</h3>';
          if (!isset($_SESSION['id'])) {
            echo '<a href="#" onclick="alert(\'You have to log in first.\');" class="fas fa-heart"></a>';
        } else {
            $is_favorite = in_array($row['id_p'], $user_favorites) ? 'is-favorite' : '';
            $action_url = $is_favorite ? 'remove_fav.php' : 'save_to_favorites.php';
        
            echo '<a href="#" onclick="toggleFavorite(' . $row['id_p'] . ')" class="fas fa-heart ' . $is_favorite . '"></a>';
        }
          echo '<a href="#" class="fas fa-phone"></a>';
          echo '</div>';

          echo '<div class="location">';
          echo '<h3>' . $row['titre'] . '</h3>';
          echo '<p>' . $row['emplacement'] . '</p>';
          echo '</div>';

          echo '<div class="details">';
          // Continue displaying announcement details
          /*echo '<p>' . $row['description'] . '</p>';*/
          echo '<a href="announcement_details.php?id=' . $row['id_p'] . '" class= "btn">Voir plus de détails</a>';
          echo '</div>';

          echo '</div>';



          echo '</div>'; // Close announcement div
        }
      } else {
        echo '<p>Aucune annonce disponible pour le moment.</p>';
      }
      ?>
    </div>

    <button class="prevv"><i class="fas fa-chevron-left"></i></button>
    <button class="nextt"><i class="fas fa-chevron-right"></i></button>

    <!-- Pagination dots will be dynamically added here -->
    <div class="pagination"></div>

  </div>
  <?php include 'footer.php' ?>
  <script>
    function toggleFavorite(id_p) {
    const isFavorite = document.querySelector(`a[onclick="toggleFavorite(${id_p})"]`).classList.contains('is-favorite');
    const url = isFavorite ? 'remove_fav.php' : 'save_to_favorites.php';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_p=${id_p}`, // Ensure the correct parameter name
    })
    .then(response => response.text())
    .then(result => {
        if (result.trim() === 'success') { // Check for success
            document.querySelector(`a[onclick="toggleFavorite(${id_p})"]`).classList.toggle('is-favorite');
        } else {
            alert('Failed to update favorites.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating favorites.');
    });
}


document.addEventListener('DOMContentLoaded', function () {
  const announcementsSlider = document.querySelector('.announcements-slider');
  const announcements = announcementsSlider.querySelector('.announcements');
  const prevButton = announcementsSlider.querySelector('.prevv');
  const nextButton = announcementsSlider.querySelector('.nextt');
  const pagination = announcementsSlider.querySelector('.pagination');
  const itemsPerPage = 3; 
  let currentIndex = 0;
  const announcementCount = announcements.children.length;
  const pageCount = Math.ceil(announcementCount / itemsPerPage);

  // Function to toggle visibility of "Prev" and "Next" buttons
  const toggleButtonVisibility = () => {
    prevButton.style.display = currentIndex === 0 ? 'none' : 'block';
    nextButton.style.display = currentIndex >= (pageCount - 1) ? 'none' : 'block';
  };

  // Function to show the appropriate announcements based on current index
  const showAnnouncements = () => {
    for (let i = 0; i < announcementCount; i++) {
      announcements.children[i].style.display = (i >= currentIndex * itemsPerPage && i < (currentIndex + 1) * itemsPerPage) ? 'block' : 'none';
    }
    toggleButtonVisibility();
    updatePagination();
  };

  // Function to create pagination dots for the announcements slider
  const createPaginationDots = () => {
    pagination.innerHTML = ''; // Clear existing dots
    for (let i = 0; i < pageCount; i++) {
      const dot = document.createElement('span');
      dot.classList.add('dot');
      if (i === currentIndex) {
        dot.classList.add('active');
      }
      pagination.appendChild(dot);
    }
  };

  // Function to update the active dot in the pagination
  const updatePagination = () => {
    const dots = pagination.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
      if (index === currentIndex) {
        dot.classList.add('active');
      } else {
        dot.classList.remove('active');
      }
    });
  };

  // Initialize the announcements and pagination
  showAnnouncements();
  createPaginationDots();

  // Handle the previous button click for the group slider
  prevButton.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex -= 1; // Go back one group
      showAnnouncements();
    }
  });

  // Handle the next button click for the group slider
  nextButton.addEventListener('click', () => {
    if (currentIndex < (pageCount - 1)) {
      currentIndex += 1; // Move to the next group
      showAnnouncements();
    }
  });

  // Handle pagination dot clicks to navigate through announcements
  pagination.addEventListener('click', (event) => {
    if (event.target.classList.contains('dot')) {
      const dotIndex = Array.from(pagination.children).indexOf(event.target);
      currentIndex = dotIndex;
      showAnnouncements();
    }
  });
});

// Slider for announcement images
document.querySelectorAll('.slider').forEach(slider => {
  const container = slider.querySelector('.slider-container');
  const prevButton = slider.querySelector('.prev');
  const nextButton = slider.querySelector('.next');
  let currentImageIndex = 0; // Index for navigating between images within each announcement

  // Function to show the correct image
  const showImage = (index) => {
    const slides = container.querySelectorAll('img');
    slides.forEach((slide, i) => {
      slide.style.display = i === index ? 'block' : 'none';
    });
  };

  // Update the initial state
  showImage(currentImageIndex);

  // Function to control the visibility of navigation buttons in the images slider
  const toggleImageButtonVisibility = () => {
    const slideCount = container.children.length;
    prevButton.style.display = currentImageIndex === 0 ? 'none' : 'block';
    nextButton.style.display = currentImageIndex === (slideCount - 1) ? 'none' : 'block';
  };

  toggleImageButtonVisibility();

  // Navigation logic for the image slider
  prevButton.addEventListener('click', () => {
    if (currentImageIndex > 0) {
      currentImageIndex -= 1;
      showImage(currentImageIndex);
      toggleImageButtonVisibility();
    }
  });

  nextButton.addEventListener('click', () => {
    if (currentImageIndex < (container.children.length - 1)) {
      currentImageIndex += 1;
      showImage(currentImageIndex);
      toggleImageButtonVisibility();
    }
  });
});

  </script>


</body>

</html>
