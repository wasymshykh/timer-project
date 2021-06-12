
<div class="page-body">
    
    <div class="page-title main">
        <h2>Timer</h2>
    </div>

    <form method="POST" action="" class="page-form form-main">

        <div class="page-input">
            <div class="page-input-td">
                <label for="name">Name</label>
            </div>
            <div class="page-input-td">
                <input type="text" name="name" id="name" value="<?=$_POST['name']??($random_username ?? '')?>" required>
            </div>
            <div class="page-input-td">
                <div class="page-validate error"><i class="fa fa-times"></i></div>
            </div>
        </div>

        <div class="page-input-submit">
            <button type="submit" class="btn-submit" name="create">Create room</button>
            <button type="submit" class="btn-submit" name="join">Join room</button>
        </div>

    </form>
    
</div>
