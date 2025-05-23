<?php
    ob_start();
    session_start();

    $pageTitle = 'Menu Categories';

    if(isset($_SESSION['username_restaurant_qRewacvAqzA']) && isset($_SESSION['password_restaurant_qRewacvAqzA']))
    {
        include 'connect.php';
        include 'Includes/functions/functions.php'; 
        include 'Includes/templates/header.php';
        include 'Includes/templates/navbar.php';

        // Ambil data kategori dari database
        $stmt = $con->prepare("SELECT * FROM menu_categories");
        $stmt->execute();
        $menu_categories = $stmt->fetchAll();
?>

    <script type="text/javascript">
        var vertical_menu = document.getElementById("vertical-menu");
        var current = vertical_menu.getElementsByClassName("active_link");
        if(current.length > 0) {
            current[0].classList.remove("active_link");   
        }
        vertical_menu.getElementsByClassName('menu_categories_link')[0].className += " active_link";
    </script>

    <style type="text/css">
        .categories-table {
            -webkit-box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
            text-align: center;
            vertical-align: middle;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <?php echo $pageTitle; ?>
        </div>
        <div class="card-body">

            <!-- ADD NEW CATEGORY BUTTON -->
            <button class="btn btn-success btn-sm" style="margin-bottom: 10px;" type="button" data-toggle="modal" data-target="#add_new_category" data-placement="top">
                <i class="fa fa-plus"></i> 
                Add Category
            </button>

            <!-- Add New Category Modal -->
            <div class="modal fade" id="add_new_category" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Category</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="category_name">Category name</label>
                                <input type="text" id="category_name_input" class="form-control" onkeyup="this.value=this.value.replace(/[^\sa-zA-Z]/g,'');" placeholder="Category Name" name="category_name">
                                <div id='required_category_name' class="invalid-feedback">
                                    <div>Category name is required!</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" id="add_category_bttn">Add Category</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MENU CATEGORIES TABLE -->
            <table class="table table-bordered categories-table">
                <thead>
                    <tr>
                        <th scope="col">Category ID</th>
                        <th scope="col">Category Name</th>
                        <th scope="col">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($menu_categories as $category) {
                            echo "<tr>";
                            echo "<td>" . $category['category_id'] . "</td>";
                            echo "<td style='text-transform:capitalize'>" . $category['category_name'] . "</td>";
                            echo "<td>";

                            // Tombol Edit
                            $edit_data = "edit_" . $category["category_id"];
                            ?>
                                <ul class="list-inline m-0">
                                    <li class="list-inline-item" data-toggle="tooltip" title="Edit">
                                        <button class="btn btn-warning btn-sm rounded-0" type="button" data-toggle="modal" data-target="#<?php echo $edit_data; ?>" data-placement="top">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <div class="modal fade" id="<?php echo $edit_data; ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $edit_data; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Category</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="category_name_edit_<?php echo $category['category_id']; ?>">Category name</label>
                                                            <input type="text" id="category_name_edit_<?php echo $category['category_id']; ?>" class="form-control" value="<?php echo $category['category_name']; ?>" name="category_name">
                                                            <div id="required_category_name_edit_<?php echo $category['category_id']; ?>" class="invalid-feedback">
                                                                <div>Category name is required!</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="button" data-id="<?php echo $category['category_id']; ?>" class="btn btn-info edit_category_bttn">Save Changes</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- DELETE BUTTON -->
                                    <li class="list-inline-item" data-toggle="tooltip" title="Delete">
                                        <button class="btn btn-danger btn-sm rounded-0" type="button" data-toggle="modal" data-target="#delete_<?php echo $category['category_id']; ?>" data-placement="top">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                        <div class="modal fade" id="delete_<?php echo $category['category_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="delete_<?php echo $category['category_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete Category</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this category "<?php echo strtoupper($category['category_name']); ?>"?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="button" data-id="<?php echo $category['category_id']; ?>" class="btn btn-danger delete_category_bttn">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            <?php
                            echo "</td>";
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>  
        </div>
    </div>

    <?php
        // Footer
        include 'Includes/templates/footer.php';
    }
    else
    {
        header('Location: index.php');
        exit();
    }
?>

<!-- JS SCRIPTS -->
<script type="text/javascript">
    // Ketika tombol tambah kategori diklik
    $('#add_category_bttn').click(function() {
        var category_name = $("#category_name_input").val();
        var do_ = "Add";

        if($.trim(category_name) == "") {
            $('#required_category_name').css('display','block');
        } else {
            $.ajax({
                url:"ajax_files/menu_categories_ajax.php",
                method:"POST",
                data:{category_name:category_name, do:do_},
                dataType:"JSON",
                success: function (data) {
                    if(data['alert'] == "Warning") {
                        swal("Warning",data['message'], "warning").then((value) => {});
                    }
                    if(data['alert'] == "Success") {
                        swal("New Category",data['message'], "success").then((value) => {
                            window.location.replace("menu_categories.php");
                        });
                    }
                },
                error: function(xhr, status, error) {
                    alert('AN ERROR HAS BEEN ENCOUNTERED WHILE TRYING TO EXECUTE YOUR REQUEST');
                }
            });
        }
    });

    // Ketika tombol delete kategori diklik
    $('.delete_category_bttn').click(function() {
        var category_id = $(this).data('id');
        var do_ = "Delete";

        $.ajax({
            url:"ajax_files/menu_categories_ajax.php",
            method:"POST",
            data:{category_id:category_id, do:do_},
            success: function (data) {
                swal("Delete Category", "The category has been deleted successfully!", "success").then((value) => {
                    window.location.replace("menu_categories.php");
                });     
            },
            error: function(xhr, status, error) {
                alert('AN ERROR HAS BEEN ENCOUNTERED WHILE TRYING TO EXECUTE YOUR REQUEST');
            }
        });
    });

    // Ketika tombol edit kategori diklik
    $('.edit_category_bttn').click(function() {
        var category_id = $(this).data('id');
        var category_name = $("#category_name_edit_" + category_id).val();
        var do_ = "Edit";

        $.ajax({
            url:"ajax_files/menu_categories_ajax.php",
            method:"POST",
            data:{category_id:category_id, category_name:category_name, do:do_},
            success: function (data) {
                var response = JSON.parse(data);
                if (response.alert === "Success") {
                    swal("Edit Category", response.message, "success").then((value) => {
                        window.location.replace("menu_categories.php");
                    });
                } else {
                    swal("Warning", response.message, "warning");
                }
            },
            error: function(xhr, status, error) {
                alert('AN ERROR HAS BEEN ENCOUNTERED WHILE TRYING TO EXECUTE YOUR REQUEST');
            }
        });
    });
</script>
