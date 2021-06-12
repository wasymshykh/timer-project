
<div class="page-body">
    
    <div class="page-title main">
        <h2>Create Room</h2>
    </div>

    <form method="POST" action="" class="page-form form-main">
        
        <?php if (!empty($errors)): ?>
        <div class="page-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?=$error?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="page-input">
            <div class="page-input-td">
                <label for="room_name">Room Name</label>
            </div>
            <div class="page-input-td">
                <input type="text" name="room_name" id="room_name" value="<?=$_POST['room_name']??''?>" required>
            </div>
        </div>
        <div class="page-input">
            <div class="page-input-td">
                <label for="room_url">Room URL</label>
            </div>
            <div class="page-input-td">
                <div class="page-input-td-span"><?=URL.'/'?></div>
                <input type="text" name="room_url" id="room_url" value="<?=$_POST['room_url']??''?>" required>
            </div>
        </div>

        <div class="page-input-submit">
            <button type="submit" class="btn-submit" name="start">Start Room</button>
        </div>

    </form>
    
</div>

<script>
    function change_text_to_slug (v) {
        return v.trimLeft().replaceAll(/[^A-Za-z0-9\s-]/g, '').replaceAll(/\s+/g, '-')
    }
    function handle_url_type (e) {
        if (e.target.value != "") {
            e.target.value = change_text_to_slug(e.target.value);
        }
    }
    $('#room_url').on('keyup', handle_url_type);
    $('#room_url').on('change', handle_url_type);

    <?php if (isset($_POST)): ?>
        $('#room_url').val((change_text_to_slug($('#room_url').val())));
    <?php endif; ?>

</script>
