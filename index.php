<!-- PHP INCLUDES -->

<?php

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

    // Display message if order was cancelled
    if(isset($_GET['msg']) && $_GET['msg'] == 'order_cancelled') {
        echo '<div class="container" style="margin-top: 20px;">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong>Pesanan dibatalkan!</strong> Pesanan Anda telah dibatalkan berhasil.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
              </div>';
    }

    //Getting website settings

    $stmt_web_settings = $con->prepare("SELECT * FROM website_settings");
    $stmt_web_settings->execute();
    $web_settings = $stmt_web_settings->fetchAll();

    $restaurant_name = "";
    $restaurant_email = "";
    $restaurant_address = "";
    $restaurant_phonenumber = "";

    foreach ($web_settings as $option)
    {
        if($option['option_name'] == 'restaurant_name')
        {
            $restaurant_name = $option['option_value'];
        }

        elseif($option['option_name'] == 'restaurant_email')
        {
            $restaurant_email = $option['option_value'];
        }

        elseif($option['option_name'] == 'restaurant_phonenumber')
        {
            $restaurant_phonenumber = $option['option_value'];
        }
        elseif($option['option_name'] == 'restaurant_address')
        {
            $restaurant_address = $option['option_value'];
        }
    }

?>

	<!-- HOME SECTION -->

	<section class="home-section" id="home" >
		<div class="container" >
			<div class="row" style="flex-wrap: nowrap;">
				<div class="col-md-6 home-left-section">
					<div>
						<h1>
							DAPOER MINANG
						</h1>
						<h2>
							MENGHADIRKAN KEBAHAGIAAN
						</h2>
						<h2>
							MELALUI MASAKAN PADANG
						</h2>
						<hr>
						<p>
							Dapoer Minang menyajikan masakan tradisional Minangkabau yang kaya akan rempah dan cita rasa, dengan berbagai pilihan menu yang pedas dan menggugah selera.
						</p>
						<div style="display: flex;">
							<a href="order_food.php" target="_blank" class="bttn_style_1" style="margin-right: 10px; display: flex;justify-content: center;align-items: center;">
								PESAN SEKARANG
								<i class="fas fa-angle-right"></i>
							</a>
							<a href="#menus" class="bttn_style_2" style="display: flex;justify-content: center;align-items: center;">
								LIHAT MENU
								<i class="fas fa-angle-right"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- OUR QUALITIES SECTION -->

	<section class="our_qualities" style="padding:70px 0px;">
		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<div class="our_qualities_column">
	                    <img src="Design/images/quality_food_img.png" >
	                    <div class="caption">
	                        <h3>
	                            Makanan Berkualitas
	                        </h3>
	                        <p>
	                        	<b>Dapoer Minang menyajikan hidangan dengan bahan-bahan pilihan dan menggunakan rempah-rempah khas Minangkabau yang berkualitas tinggi. Setiap hidangan kami dijamin tidak hanya enak tetapi juga bergizi dan menyegarkan.</b>
	                        </p>
	                    </div>
	                </div>
				</div>
				<div class="col-md-4">
					<div class="our_qualities_column">
	                    <img src="Design/images/fast_delivery_img.png" >
	                    <div class="caption">
	                        <h3>
	                            Pengiriman Cepat
	                        </h3>
	                        <p>
	                        	<b>Proses pemesanan dan pengiriman yang cepat dan terpercaya. Kami memastikan pesanan Anda sampai dengan aman dan tepat waktu langsung ke pintu rumah Anda.</b>
	                        </p>
	                    </div>
	                </div>
				</div>
				<div class="col-md-4">
					<div class="our_qualities_column">
	                    <img src="Design/images/original_taste_img.png" >
	                    <div class="caption">
	                        <h3>
	                            Rasa Asli Minangkabau
	                        </h3>
	                        <p>
	                        	<b>Hidangan kami disiapkan dengan resep tradisional Minang yang autentik, mengutamakan keseimbangan rasa pedas, gurih, dan manis, yang tidak akan Anda temukan di tempat lain.</b>
	                        </p>
	                    </div>
	                </div>
				</div>

			</div>
		</div>
	</section>

	<!-- OUR MENUS SECTION -->

	<section class="our_menus" id="menus">
		<div class="container">
			<div class="section-title">
				<h2>TEMUKAN MENU KAMI</h2>
				<div class="divider"></div>
				<p>Nikmati berbagai pilihan hidangan otentik Minangkabau yang disajikan dengan cita rasa tradisional.</p>
			</div>
			
			<!-- Menu Search and Controls -->
			<div class="menu-controls">
			    <div class="menu-search">
			        <i class="fas fa-search"></i>
			        <input type="text" id="menuSearch" placeholder="Cari menu..." onkeyup="searchMenus()">
			    </div>
			</div>
			
			<div class="menus_tabs">
				<div class="menus_tabs_picker">
					<ul>
						<?php
	                        $stmt = $con->prepare("Select * from menu_categories");
	                        $stmt->execute();
	                        $rows = $stmt->fetchAll();
	                        $count = $stmt->rowCount();

	                        $x = 0;

	                        foreach($rows as $row)
	                        {
	                        	if($x == 0)
	                        	{
	                        		echo "<li class='menu_category_name tab_category_links active_category' onclick=showCategoryMenus(event,'".str_replace(' ', '', $row['category_name'])."')>";
	                        			echo $row['category_name'];
	                        		echo "</li>";
	                        	}
	                        	else
	                        	{
	                        		echo "<li class='menu_category_name tab_category_links' onclick=showCategoryMenus(event,'".str_replace(' ', '', $row['category_name'])."')>";
	                        			echo $row['category_name'];
	                        		echo "</li>";
	                        	}
	                        	$x++;
	                        }
						?>
					</ul>
				</div>

				<div class="menus_tab">
					<?php
                        $stmt = $con->prepare("Select * from menu_categories");
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        $count = $stmt->rowCount();

                        $i = 0;

                        foreach($rows as $row) 
                        {
                            if($i == 0)
                            {
                                echo '<div class="menu_item tab_category_content" id="'.str_replace(' ', '', $row['category_name']).'" style="display:block">';

                                    $stmt_menus = $con->prepare("Select * from menus where category_id = ?");
                                    $stmt_menus->execute(array($row['category_id']));
                                    $rows_menus = $stmt_menus->fetchAll();

                                    if($stmt_menus->rowCount() == 0)
                                    {
                                        echo "<div class='no_menus_div'>Belum ada menu tersedia untuk kategori ini</div>";
                                    }

                                    echo "<div class='row'>";
	                                    foreach($rows_menus as $index => $menu)
	                                    {
	                                        ?>
	                                            <div class="col-md-4 col-lg-3 menu-column menu-item-card" data-name="<?php echo strtolower($menu['menu_name']); ?>" data-category="<?php echo strtolower($row['category_name']); ?>">
	                                                <div class="menu-card" onclick="openMenuModal(<?php echo $menu['menu_id']; ?>)">
	                                                    <?php 
                                                            // Add popular badge to some items
                                                            if($index % 5 == 0) {
                                                                echo '<div class="popular-badge">Populer</div>';
                                                            }
                                                        ?>
	                                                    <div class="category-label"><?php echo $row['category_name']; ?></div>
                                                        <div class="menu-image-container">
                                                            <div class="menu-image-bg" style="background-image: url('admin/Uploads/images/<?php echo $menu['menu_image']; ?>');" onerror="this.style.backgroundImage='url(Design/images/logo-restouran.png)'"></div>
                                                            <button class="quick-view">Lihat Detail</button>
                                                        </div>
	                                                    <div class="menu-content">
	                                                        <h3 class="menu-title"><?php echo $menu['menu_name']; ?></h3>
	                                                        <p class="menu-description"><?php echo $menu['menu_description']; ?></p>
	                                                        <div class="menu-footer">
                                                                <span class="menu-price"><?php echo "Rp ".number_format($menu['menu_price']*1000, 0, ',', '.'); ?></span>
                                                                <?php if(isset($_SESSION['user_id'])): ?>
                                                                <a href="order_food.php?menu_id=<?php echo $menu['menu_id']; ?>" class="menu-order-btn">Pesan</a>
                                                                <?php else: ?>
                                                                    <a href="javascript:void(0)" onclick="alert('Anda harus login terlebih dahulu untuk melakukan pemesanan.'); window.location.href='login.php';" class="menu-order-btn">Pesan</a>
                                                                <?php endif; ?>
                                                            </div>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        <?php
	                                    }
	                                echo "</div>";

                                echo '</div>';
                            }
                            else
                            {
                                echo '<div class="menus_categories tab_category_content" id="'.str_replace(' ', '', $row['category_name']).'">';

                                    $stmt_menus = $con->prepare("Select * from menus where category_id = ?");
                                    $stmt_menus->execute(array($row['category_id']));
                                    $rows_menus = $stmt_menus->fetchAll();

                                    if($stmt_menus->rowCount() == 0)
                                    {
                                        echo "<div class='no_menus_div'>Belum ada menu tersedia untuk kategori ini</div>";
                                    }

                                    echo "<div class='row'>";
	                                    foreach($rows_menus as $index => $menu)
	                                    {
	                                        ?>
	                                            <div class="col-md-4 col-lg-3 menu-column menu-item-card" data-name="<?php echo strtolower($menu['menu_name']); ?>" data-category="<?php echo strtolower($row['category_name']); ?>">
	                                                <div class="menu-card" onclick="openMenuModal(<?php echo $menu['menu_id']; ?>)">
	                                                    <?php 
                                                            // Add popular badge to some items
                                                            if($index % 4 == 0) {
                                                                echo '<div class="popular-badge">Populer</div>';
                                                            }
                                                        ?>
	                                                    <div class="category-label"><?php echo $row['category_name']; ?></div>
                                                        <div class="menu-image-container">
                                                            <div class="menu-image-bg" style="background-image: url('admin/Uploads/images/<?php echo $menu['menu_image']; ?>');" onerror="this.style.backgroundImage='url(Design/images/logo-restouran.png)'"></div>
                                                            <button class="quick-view">Lihat Detail</button>
                                                        </div>
	                                                    <div class="menu-content">
	                                                        <h3 class="menu-title"><?php echo $menu['menu_name']; ?></h3>
	                                                        <p class="menu-description"><?php echo $menu['menu_description']; ?></p>
	                                                        <div class="menu-footer">
                                                                <span class="menu-price"><?php echo "Rp ".number_format($menu['menu_price']*1000, 0, ',', '.'); ?></span>
                                                                <?php if(isset($_SESSION['user_id'])): ?>
                                                                <a href="order_food.php?menu_id=<?php echo $menu['menu_id']; ?>" class="menu-order-btn">Pesan</a>
                                                                <?php else: ?>
                                                                    <a href="javascript:void(0)" onclick="alert('Anda harus login terlebih dahulu untuk melakukan pemesanan.'); window.location.href='login.php';" class="menu-order-btn">Pesan</a>
                                                                <?php endif; ?>
                                                            </div>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        <?php
	                                    }
	                               	echo "</div>";

                                echo '</div>';
                            }
                            $i++;
                        }
                        echo "</div>";
                    ?>
				</div>
				
				<!-- Loading Animation -->
				<div class="menu-loader" id="menuLoader">
				    <div class="loader-spinner"></div>
				    <div class="loader-text">Mencari...</div>
				</div>
				
				<!-- No Results Message -->
				<div id="noResults" style="display:none;" class="no_menus_div">
				    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
				    <p>Tidak ditemukan menu yang sesuai dengan pencarian Anda</p>
				</div>
			</div>
		</div>
		
		<!-- Menu Modal -->
		<div id="menuModal" class="menu-modal">
			<div class="modal-content" id="modalContent">
				<!-- Modal content will be loaded dynamically -->
			</div>
		</div>
	</section>

	<!-- IMAGE GALLERY -->

	<section class="image-gallery" id="gallery">
		<div class="container">
			<h2 style="text-align: center;margin-bottom: 30px">IMAGE GALLERY</h2>
			<?php
				$stmt_image_gallery = $con->prepare("Select * from image_gallery");
                $stmt_image_gallery->execute();
                $rows_image_gallery = $stmt_image_gallery->fetchAll();

                echo "<div class = 'row'>";

	                foreach($rows_image_gallery as $row_image_gallery)
	                {
	                	echo "<div class = 'col-md-4 col-lg-3' style = 'padding: 15px;'>";
	                		$source = "admin/Uploads/images/".$row_image_gallery['image'];
	                		?>

	                		<div style = "background-image: url('<?php echo $source; ?>') !important;background-repeat: no-repeat;background-position: 50% 50%;background-size: cover;background-clip: border-box;box-sizing: border-box;overflow: hidden;height: 230px;">
	                		</div>

	                		<?php
	                	echo "</div>";
	                }

	            echo "</div>";
			?>
		</div>
	</section>

	<!-- CONTACT US SECTION -->

	<section class="contact-section" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 sm-padding">
                    <div class="contact-info">
                        <h2>
                            Get in touch with us & 
                            <br>send us message today!
                        </h2>
                        <p>
							<b>Dapoer Minang offers the best traditional dishes from Minang culture. We're here to satisfy your cravings!</b>
                        </p>
                        <h3>
                            <?php echo $restaurant_address; ?>
                        </h3>
                        <h4>
                            <span>Email:</span> 
                            <?php echo $restaurant_email; ?>
                            <br> 
                            <span>Phone:</span> 
                            <?php echo $restaurant_phonenumber; ?>
                        </h4>
                    </div>
                </div>
                <div class="col-lg-6 sm-padding">
                    <div class="contact-form">
                        <div id="contact_ajax_form" class="contactForm">
                            <div class="form-group colum-row row">
                                <div class="col-sm-6">
                                    <input type="text" id="contact_name" name="name" oninput="document.getElementById('invalid-name').innerHTML = ''" onkeyup="this.value=this.value.replace(/[^\sa-zA-Z]/g,'');" class="form-control" placeholder="Name">
                                    <div class="invalid-feedback" id="invalid-name" style="display: block">
                                    	
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <input type="email" id="contact_email" name="email" oninput="document.getElementById('invalid-email').innerHTML = ''" class="form-control" placeholder="Email">
                                    <div class="invalid-feedback" id="invalid-email" style="display: block">
                                    	
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <input type="text" id="contact_subject" name="subject" oninput="document.getElementById('invalid-subject').innerHTML = ''" onkeyup="this.value=this.value.replace(/[^\sa-zA-Z]/g,'');" class="form-control" placeholder="Subject">
                                    <div class="invalid-feedback" id="invalid-subject" style="display: block">
                                    	
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <textarea id="contact_message" name="message" oninput="document.getElementById('invalid-message').innerHTML = ''" cols="30" rows="5" class="form-control message" placeholder="Message"></textarea>
                                    <div class="invalid-feedback" id="invalid-message" style="display: block">
                                    	
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <button id="contact_send" class="bttn_style_2">Send Message</button>
                                </div>
                            </div>
                            <div id="sending_load" style="display: none;">Sending...</div>
                            <div id="contact_status_message"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

	<!-- OUR QUALITIES SECTION -->
	
	<section class="our_qualities_v2">
		<div class="container">
			<div class="row">
				<div class="col-md-4" style="padding: 10px;">
					<div class="quality quality_1">
						<div class="text_inside_quality">
							<h5>Quality Foods</h5>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="padding: 10px;">
					<div class="quality quality_2">
						<div class="text_inside_quality">
							<h5>Fastest Delivery</h5>
						</div>
					</div>
				</div>
				<div class="col-md-4" style="padding: 10px;">
					<div class="quality quality_3">
						<div class="text_inside_quality">
							<h5>Original Recipes</h5>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- WIDGET SECTION / FOOTER -->

    <section class="widget_section" style="background-color: #222227;padding: 100px 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer_widget">
                        <img src="Design/images/logo.png" alt="Restaurant Logo" style="width: 150px;margin-bottom: 20px;">
                        <p>
						Dapoer Minang menawarkan pengalaman bersantap dengan pelayanan terbaik di tempat yang mewah dan cozy. Nikmati hidangan tradisional Minang yang lezat dalam suasana yang elegan dan nyaman, membuat setiap kunjungan Anda semakin spesial.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                     <div class="footer_widget">
                        <h3>Restaurant Kami</h3>
                        <p>
                            <?php echo $restaurant_address; ?>
                        </p>
                        <p>
                            <?php echo $restaurant_email; ?>
                            <br>
                            <?php echo $restaurant_phonenumber; ?>   
                        </p>
                     </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer_widget">
                        <h3>
                            Jam Buka
                        </h3>
                        <ul class="opening_time">
						    <li>Senin - Jumat 08.00 - 21.00</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER BOTTOM  -->

    <?php include "Includes/templates/footer.php"; ?> 

    <script type="text/javascript">

	    $(document).ready(function()
	    {
	        $('#contact_send').click(function()
	        {
	            var contact_name = $('#contact_name').val();
	            var contact_email = $('#contact_email').val();
	            var contact_subject = $('#contact_subject').val();
	            var contact_message = $('#contact_message').val();

	            var flag = 0;

	            if($.trim(contact_name) == "")
	            {
	            	$('#invalid-name').text('This is a required field!');
	            	flag = 1;
	            }
	            else
	            {
	            	if(contact_name.length < 5)
	            	{
	            		$('#invalid-name').text('Length is less than 5 letters!');
	            		flag = 1;
	            	}
	            }

	            if(!ValidateEmail(contact_email))
	            {
	            	$('#invalid-email').text('Invalid e-mail!');
	            	flag = 1;
	            }

	            if($.trim(contact_subject) == "")
	            {
	            	$('#invalid-subject').text('This is a required field!');
	            	flag = 1;
	            }

	            if($.trim(contact_message) == "")
	            {
	            	$('#invalid-message').text('This is a required field!');
	            	flag = 1;
	            }

	            if(flag == 0)
	            {
	            	$('#sending_load').show();

		            $.ajax({
		                url: "Includes/php-files-ajax/contact.php",
		                type: "POST",
		                data:{contact_name:contact_name, contact_email:contact_email, contact_subject:contact_subject, contact_message:contact_message},
		                success: function (data) 
		                {
		                	$('#contact_status_message').html(data);
		                },
		                beforeSend: function()
		                {
					        $('#sending_load').show();
					    },
					    complete: function()
					    {
					        $('#sending_load').hide();
					    },
		                error: function(xhr, status, error) 
		                {
		                    alert("Internal ERROR has occured, please, try later!");
		                }
		            });
	            }
	            
	        });
	    }); 
	    
	</script>

	<!-- Menu Modal JavaScript -->
	<script>
		// Show the selected category
		function showCategoryMenus(evt, categoryName) {
			// Hide loader and no results message
			document.getElementById('menuLoader').style.display = 'none';
			document.getElementById('noResults').style.display = 'none';
			
			// Reset search
			document.getElementById('menuSearch').value = '';
			
			// Show all menu items
			var menuItems = document.querySelectorAll('.menu-item-card');
			menuItems.forEach(function(item) {
				item.style.display = 'block';
			});
			
			// Hide all tab content
			var tabcontent = document.getElementsByClassName("tab_category_content");
			for (var i = 0; i < tabcontent.length; i++) {
				tabcontent[i].style.display = "none";
			}
			
			// Remove active class from all tab links
			var tablinks = document.getElementsByClassName("tab_category_links");
			for (var i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active_category", "");
			}
			
			// Show the selected category and add active class to the button
			document.getElementById(categoryName).style.display = "block";
			evt.currentTarget.className += " active_category";
		}
		
		// Search functionality
		function searchMenus() {
			// Show loader
			document.getElementById('menuLoader').style.display = 'block';
			
			// Hide no results message
			document.getElementById('noResults').style.display = 'none';
			
			// Get the search query
			var searchQuery = document.getElementById('menuSearch').value.toLowerCase();
			
			// Get all menu items
			var menuItems = document.querySelectorAll('.menu-item-card');
			var visibleCount = 0;
			
			// Delay search execution for better UX
			setTimeout(function() {
				// Show all tab content
				var tabcontent = document.getElementsByClassName("tab_category_content");
				for (var i = 0; i < tabcontent.length; i++) {
					tabcontent[i].style.display = "block";
				}
				
				// Filter menu items
				menuItems.forEach(function(item) {
					var menuName = item.getAttribute('data-name');
					var categoryName = item.getAttribute('data-category');
					
					if (menuName.includes(searchQuery) || categoryName.includes(searchQuery) || searchQuery === '') {
						item.style.display = 'block';
						visibleCount++;
					} else {
						item.style.display = 'none';
					}
				});
				
				// Hide loader
				document.getElementById('menuLoader').style.display = 'none';
				
				// Show no results message if no items visible
				if (visibleCount === 0 && searchQuery !== '') {
					document.getElementById('noResults').style.display = 'block';
				}
			}, 300);
		}
		
		// Menu modal functionality
		var modal = document.getElementById("menuModal");
		
		function openMenuModal(menuId) {
			// Get menu details
			fetch(`get_menu_details.php?menu_id=${menuId}`)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						var menu = data.menu;
						var modalContent = `
							<div class="modal-header">
								<img src="admin/Uploads/images/${menu.menu_image}" class="modal-header-image" onerror="this.src='Design/images/logo-restouran.png'" alt="${menu.menu_name}">
								<span class="modal-close" onclick="closeMenuModal()">&times;</span>
							</div>
							<div class="modal-body">
								<h2 class="modal-title">${menu.menu_name}</h2>
								<div class="modal-specs">
									<div class="modal-spec">
										<div class="modal-spec-label">Kategori</div>
										<div class="modal-spec-value">${getCategoryName(menu.category_id)}</div>
									</div>
									<div class="modal-spec">
										<div class="modal-spec-label">Porsi</div>
										<div class="modal-spec-value">1 Orang</div>
									</div>
									<div class="modal-spec">
										<div class="modal-spec-label">Rating</div>
										<div class="modal-spec-value">★★★★★</div>
									</div>
								</div>
								<p class="modal-description">${menu.menu_description}</p>
								<div class="modal-price">Rp ${formatNumber(menu.menu_price * 1000)}</div>
								<div class="modal-actions">
									<div class="modal-quantity">
										<button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
										<span id="quantity-value">1</span>
										<button class="quantity-btn" onclick="changeQuantity(1)">+</button>
									</div>
									<a href="order_food.php?menu_id=${menu.menu_id}" class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Tambahkan ke Pesanan</a>
								</div>
							</div>
						`;
						document.getElementById("modalContent").innerHTML = modalContent;
					} else {
						// Fallback if API call fails
						var menuCard = document.querySelector(`.menu-card[onclick*="openMenuModal(${menuId})"]`);
						if (menuCard) {
							var menuTitle = menuCard.querySelector('.menu-title').innerText;
							var menuDesc = menuCard.querySelector('.menu-description').innerText;
							var menuPrice = menuCard.querySelector('.menu-price').innerText;
							var categoryLabel = menuCard.querySelector('.category-label').innerText;
							var backgroundImage = menuCard.querySelector('.menu-image-bg').style.backgroundImage.replace('url("', '').replace('")', '');
							
							var modalContent = `
								<div class="modal-header">
									<img src="${backgroundImage}" class="modal-header-image" onerror="this.src='Design/images/logo-restouran.png'" alt="${menuTitle}">
									<span class="modal-close" onclick="closeMenuModal()">&times;</span>
								</div>
								<div class="modal-body">
									<h2 class="modal-title">${menuTitle}</h2>
									<div class="modal-specs">
										<div class="modal-spec">
											<div class="modal-spec-label">Kategori</div>
											<div class="modal-spec-value">${categoryLabel}</div>
										</div>
										<div class="modal-spec">
											<div class="modal-spec-label">Porsi</div>
											<div class="modal-spec-value">1 Orang</div>
										</div>
										<div class="modal-spec">
											<div class="modal-spec-label">Rating</div>
											<div class="modal-spec-value">★★★★★</div>
										</div>
									</div>
									<p class="modal-description">${menuDesc}</p>
									<div class="modal-price">${menuPrice}</div>
									<div class="modal-actions">
										<div class="modal-quantity">
											<button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
											<span id="quantity-value">1</span>
											<button class="quantity-btn" onclick="changeQuantity(1)">+</button>
										</div>
										<a href="order_food.php?menu_id=${menuId}" class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Tambahkan ke Pesanan</a>
									</div>
								</div>
							`;
							document.getElementById("modalContent").innerHTML = modalContent;
						}
					}
					
					// Show modal
					modal.style.display = "flex";
					document.body.style.overflow = "hidden"; // Prevent scrolling
				})
				.catch(error => {
					console.error('Error fetching menu details:', error);
				});
		}
		
		// Helper function to get category name from ID
		function getCategoryName(categoryId) {
			// This is a simple implementation
			// You might want to fetch this from the server
			const categories = {
				<?php
					$cats = $con->prepare("SELECT * FROM menu_categories");
					$cats->execute();
					$categories = $cats->fetchAll();
					foreach($categories as $cat) {
						echo $cat['category_id'] . ': "' . $cat['category_name'] . '",';
					}
				?>
			};
			return categories[categoryId] || "Tidak diketahui";
		}
		
		function closeMenuModal() {
			modal.style.display = "none";
			document.body.style.overflow = "auto"; // Re-enable scrolling
		}
		
		// Close modal when clicking outside of it
		window.onclick = function(event) {
			if (event.target == modal) {
				closeMenuModal();
			}
		}
		
		// Quantity buttons
		function changeQuantity(change) {
			var quantityElement = document.getElementById('quantity-value');
			var currentQuantity = parseInt(quantityElement.innerHTML);
			var newQuantity = currentQuantity + change;
			
			if (newQuantity >= 1) {
				quantityElement.innerHTML = newQuantity;
			}
		}
		
		// Function to format numbers with thousands separator
		function formatNumber(num) {
			return new Intl.NumberFormat('id-ID').format(num);
		}
	</script>
