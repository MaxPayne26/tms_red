<?php
include '../../config.php';

// Get the ID from the query string
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Check if the ID corresponds to a tourist spot or a package
    $qry = $conn->query("SELECT b.*, p.title AS package_title, p.cost AS package_cost, 
                                 ts.title AS tourist_spot_title, ts.cost AS tourist_spot_cost, 
                                 concat(u.firstname, ' ', u.lastname) as name, b.status
                         FROM book_list b
                         LEFT JOIN `tourist_spot` ts ON ts.id = b.tourist_spot_id
                         LEFT JOIN `packages` p ON p.id = b.package_id
                         INNER JOIN users u ON u.id = b.user_id
                         WHERE b.id = '$id'");

    // Check if any data was found
    if ($qry->num_rows > 0) {
        $data = $qry->fetch_assoc();

        // Assign the relevant data based on whether the tourist_spot_id or package_id is found
        $id = $data['id'];
        $schedule = $data['schedule'];
        $status = $data['status'];
        $name = $data['name'];
        
        // Check if the tourist spot exists in the result
        if (!empty($data['tourist_spot_title'])) {
            $tourist_spot_title = $data['tourist_spot_title'];
            $tourist_spot_cost = $data['tourist_spot_cost'];  // Assuming `cost` is a column for the tourist spot
            $is_tourist_spot = true;
            $tourist_spot_id = $data['tourist_spot_id']; // If required, store tourist spot ID
        } else {
            $tourist_spot_title = null;
            $tourist_spot_cost = null;
            $is_tourist_spot = false;
        }

        // Check if the package exists in the result
        if (!empty($data['package_title'])) {
            $package_title = $data['package_title'];
            $package_cost = $data['package_cost'];  // Assuming `cost` is a column for the package
            $is_package = true;
        } else {
            $package_title = null;
            $package_cost = null;
            $is_package = false;
        }
    } else {
        echo "No booking found for the given ID.";
        exit;
    }
} else {
    echo "No ID provided.";
    exit;
}
?>

<style>
    #uni_modal .modal-content>.modal-footer {
        display: none;
    }
</style>

<p><b>Package/Tourist Spot:</b> 
    <?php 
    if (isset($tourist_spot_title)) {
        echo $tourist_spot_title;
    } elseif (isset($package_title)) {
        echo $package_title;
    } else {
        echo "No package or tourist spot available.";
    }
    ?>
</p>

<p><b>Cost:</b> 
    <?php 
    if (isset($tourist_spot_cost)) {
        echo "Ph " . number_format($tourist_spot_cost, 2); // Format cost with currency symbol for tourist spot
    } elseif (isset($package_cost)) {
        echo "Ph " . number_format($package_cost, 2); // Format cost with currency symbol for package
    } else {
        echo "No cost available.";
    }
    ?>
</p>

<p><b>Schedule:</b> <?php echo isset($schedule) ? date("F d, Y", strtotime($schedule)) : 'N/A'; ?></p>

<form action="" id="book-status">
    <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
    <div class="form-group">
        <label for="" class="control-label">Status</label>
        <select name="status" id="" class="select custom-select">
            <option value="0" <?php echo isset($status) && $status == 0 ? "selected" : ''; ?>>Pending</option>
            <option value="1" <?php echo isset($status) && $status == 1 ? "selected" : ''; ?>>Confirmed</option>
            <option value="2" <?php echo isset($status) && $status == 2 ? "selected" : ''; ?>>Cancelled</option>
            <option value="3" <?php echo isset($status) && $status == 3 ? "selected" : ''; ?>>Done</option>
        </select>
    </div>
</form>

<div class="modal-footer">
    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Update</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
</div>

<script>
    $(function(){
        $('#book-status').submit(function(e){
            e.preventDefault();
            start_loader();
            $.ajax({
                url:_base_url_+"classes/Master.php?f=update_book_status",
                method:"POST",
                data:$(this).serialize(),
                dataType:"json",
                error:err=>{
                    console.log(err);
                    alert_toast("an error occurred",'error');
                    end_loader();
                },
                success:function(resp){
                    if(typeof resp == 'object' && resp.status == 'success'){
                        location.reload();
                    }else{
                        console.log(resp);
                        alert_toast("an error occurred",'error');
                    }
                    end_loader();
                }
            });
        });
    });
</script>
