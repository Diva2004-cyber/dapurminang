<?php
// Function to add content to the head section
$head_content = array();
function add_to_head($content) {
    global $head_content;
    $head_content[] = $content;
}
?>
<!DOCTYPE html>
<html lang="en">
	
	<!-- HEAD -->

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0"/>
		<meta name="author" content="DAPOER MINANG">
		<title><?php getTitle(); ?></title>

		<!-- EXTERNAL CSS LINKS -->

		<link rel="stylesheet" type="text/css" href="Design/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="Design/fonts/css/all.min.css">
		<link rel="stylesheet" type="text/css" href="Design/css/main.css">
		<link rel="stylesheet" type="text/css" href="Design/css/responsive.css">
		<!-- Modern Menu Styles -->
		<link rel="stylesheet" type="text/css" href="Design/css/menu-styles.css">
		
		<!-- FONTAWESOME CDN (if not already included in fonts/css/all.min.css) -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
		
		<!-- BOOTSTRAP JS FOR DROPDOWN -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

		<!-- HUBUNGKAN users.css -->
		<link rel="stylesheet" type="text/css" href="Design/css/users.css">
		
		<!-- GOOGLE FONTS -->

		<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;1,100;1,200;1,300;1,400;1,500&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Prata&display=swap" rel="stylesheet">

        <!-- DYNAMIC HEAD CONTENT -->
        <?php 
            global $head_content;
            if (!empty($head_content)) {
                foreach ($head_content as $content) {
                    echo $content;
                }
            }
        ?>
	</head>

	<!-- BODY -->

	<body>
	