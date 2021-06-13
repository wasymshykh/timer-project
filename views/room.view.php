<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<div class="page-body <?=$dafault_section?>">
    
    <div class="page-title room-title">
        <h2><?=$room['room_name']?></h2>
    </div>

    <div class="room-page">
        <div class="page-sub-title room-status">
            <h3><?php if (!$timer_configured):?>Waiting for host... <?php else: ?> <?=$room['room_status'] === 'A'?'Work':'Pause'?><?php endif;?></h3>
        </div>
        <div class="page-counter">
            <h1></h1>
        </div>
        <?php if ($member['member_type'] === 'H'): ?>
        <div class="page-timer-button">
            <button class="btn-submit pause-btn">Pause</button>
            <div class="timer-reset">
                <button class="reset-btn reset-btn">Reset</button>
            </div>
            <div class="timer-config">
                <button class="config-btn config-change"><i class="fa fa-cog"></i></button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="room-finished">
        <?php if ($member['member_type'] === 'M'): ?>
        <div class="page-sub-title">
            <h3>Waiting for the host...</h3>
        </div>
        <?php endif; ?>
        <div class="page-counter-finished">
            <h1>Finished</h1>
        </div>
        <?php if ($member['member_type'] === 'H'): ?>
        <div class="page-timer-button">
            <button class="btn-submit config-change">Back to configuration</button>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($member['member_type'] === 'H'): ?>
    <div class="room-config">
        
        <div class="page-input">
            <div class="page-input-td">
                <label for="work-time-1">Work Time</label>
            </div>
            <div class="page-input-td time-td">
                <input type="text" id="work-time-1" value="<?=$room['work_hour']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="work-time-2" value="<?=$room['work_minute']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="pause-time-1">Pause</label>
            </div>
            <div class="page-input-td time-td">
                <input type="text" id="pause-time-1" value="<?=$room['pause_hour']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">:</div>
                <input type="text" id="pause-time-2" value="<?=$room['pause_minute']?>" minlength="2" maxlength="2" placeholder="00" required>
                <div class="page-input-td-span">min</div>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label for="sound">Sound</label>
            </div>
            <div class="page-input-td">
                <select id="sound">
                    <option value="bip bip bip" <?=$room['room_sound_type']==='bip bip bip'?'selected':''?>>Bip Bip Bip</option>
                    <option value="buzzer" <?=$room['room_sound_type']==='buzzer'?'selected':''?>>Buzzer</option>
                    <option value="dring" <?=$room['room_sound_type']==='dring'?'selected':''?>>Dring</option>
                </select>
            </div>
        </div>

        <div class="page-input">
            <div class="page-input-td">
                <label>Round</label>
            </div>
            <div class="page-input-td">
                <div class="room-round">1</div>
            </div>
        </div>

        <div class="page-input-submit">
            <button type="button" class="btn-submit btn-start-timer">Start Timer</button>
        </div>

    </div>
    <?php endif; ?>

    <div class="room-connected">
        <p><span id="members">0</span> members connected</p>
        
        <div class="view-all">
            <button type="button" class="members-btn">view all</button>
    
            <div class="room-connected-drop">
                <ul class="no">
                    <li></li>
                </ul>
            </div>
        </div>
    </div>

</div>

<audio id="alert-sound" src="" preload="auto"></audio>


<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    toastr.options.closeButton = true;

    let members_online = [];
    let room_config = {
        sound: '<?=$room['room_sound_type']??''?>',
        work_time: '<?=$room['room_work_time']??''?>',
        pause_time: '<?=$room['room_pause_time']??''?>',
        round: <?=$room['room_round']??1?>,
        configure_date: '<?=$room['room_configure_date']??''?>',
        work_end: '<?=$room['room_work_end_date']??''?>',
        pause_start: '<?=$room['room_pause_start_date']??''?>',
        room_status: '<?=$room['room_status']??''?>'
    };
    let finished = false;
    let awaiting = <?=$timer_configured ? 'false' : 'true' ?>;
    let paused = room_config.room_status == 'P' ? true : false;

    function apply_configure_difference (changes) {
        if (JSON.stringify(changes) != JSON.stringify(room_config)) {
            room_config = changes;
            finished = false;
            paused = false;
            awaiting = false;
            $('.room-status h3').text('Work');
        }
    }

    function validate_field (data) {
        if (data == undefined || data == "" || data.trim() == "") {
            return {status: false, message: 'Field cannot be empty'};
        }
        return {status: true};
    }

    $('.btn-start-timer').on('click', (e) => {
        
        let el_work_time_1 = $('#work-time-1');
        let el_work_time_2 = $('#work-time-2');
        let el_pause_time_1 = $('#pause-time-1');
        let el_pause_time_2 = $('#pause-time-2');
        let el_sound = $('#sound');
        
        let valid = true;
        
        var r = validate_field(el_work_time_1.val());
        if (!r.status) {
            valid = false;
            toastr.error('Work time hours field cannot be empty')
        }
        var r = validate_field(el_work_time_2.val());
        if (!r.status) {
            valid = false;
            toastr.error('Work time minutes field cannot be empty')
        }
        var r = validate_field(el_pause_time_1.val());
        if (!r.status) {
            valid = false;
            toastr.error('Pause time hours field cannot be empty')
        }
        var r = validate_field(el_pause_time_2.val());
        if (!r.status) {
            valid = false;
            toastr.error('Pause time minutes field cannot be empty')
        }
        var r = validate_field(el_sound.val());
        if (!r.status) {
            valid = false;
            toastr.error('Select the sound')
        }

        if (valid) {
            
            $.ajax({
                'url': '<?=URL?>/api/room.php?config=<?=$room['room_id']?>',
                'method': 'POST',
                data: {work_hour: el_work_time_1.val(), work_minute: el_work_time_2.val(),
                    pause_hour: el_pause_time_1.val(), pause_minute: el_pause_time_2.val(), sound: el_sound.val()},
                success: (data, status) => {
                    data = JSON.parse(data);
                    if (data.status == 200) {
                        data = data.data;
                        var changes = { sound: data.sound, work_time: data.work_time, pause_time: data.pause_time, round: data.round, configure_date: data.configure_date, work_end: data.work_end, pause_start: data.pause_start, room_status: 'A' };
                        apply_configure_difference(changes);
                        $('.page-body').removeClass('config').addClass('room');
                    }
                }
            });

        }

    });

    let sound_path = {
        'bip bip bip': '<?=URL?>/assets/sound/bip bip bip.wav',
        'buzzer': '<?=URL?>/assets/sound/bip bip bip.wav',
        'dring': '<?=URL?>/assets/sound/bip bip bip.wav'
    }

    function handle_change_status (isPaused) {
        paused = isPaused;
        room_config.room_status = paused ? 'P' : 'A';
        $('.pause-btn').text(paused ? 'Resume' : 'Pause');
        $('.room-status h3').text(paused ? 'Pause' : 'Work');
        $('#alert-sound').attr('src', sound_path[room_config.sound])[0].play();
    }

    $('.pause-btn').on('click', (e) => {
        
        let parm = paused ? 'resume' : 'pause';
        $.ajax({
            'url': '<?=URL?>/api/room.php?status=<?=$room['room_id']?>&'+parm+'=true',
            'method': 'GET',
            success: (data, status) => {
                handle_change_status(!paused);
            }
        });

    });

    $('.reset-btn').on('click', (e) => {

        $.ajax({
            'url': '<?=URL?>/api/room.php?reset=<?=$room['room_id']?>',
            'method': 'GET',
            success: (data, status) => {
                data = JSON.parse(data);
                if (data.status == 200) {
                    data = data.data;
                    var changes = { sound: data.sound, work_time: data.work_time, pause_time: data.pause_time, round: data.round, configure_date: data.configure_date, work_end: data.work_end, pause_start: data.pause_start, room_status: 'A' };
                    apply_configure_difference(changes);
                }
            }
        });

    });

    $('.members-btn').on('click', (e) => {
        $('.room-connected-drop').toggleClass('active');
    });

    $('.config-change').on('click', (e) => {
        $('.page-body').removeClass('room').removeClass('finished').addClass('config');
    });

    $(document).on('click', (e) => {
        let t = $(e.target);
        if (!t.hasClass('members-btn') && !t.hasClass('no') && !t.parent().hasClass('no')) {
            let rcd = $('.room-connected-drop');
            if (rcd.hasClass('active')) {
                rcd.removeClass('active');
            }
        }
    });

    function apply_online_difference (members) {
        if (JSON.stringify(members) != JSON.stringify(members_online)) {
            members_online = members;
            $('#members').text(members_online.length);

            $('.room-connected-drop ul li').remove();
            members_online.forEach(member => {
                $('.room-connected-drop ul').append(`<li>${member}</li>`);
            })
        }
    }

    function apply_change_difference (changes) {
        if (JSON.stringify(changes) != JSON.stringify(room_config)) {
            
            change_status = false;

            if (changes.configure_date == null) { return }

            if (changes.work_end != room_config.work_end) {
                if ($('.page-body').hasClass('finished')) {
                    $('.page-body').removeClass('finished').addClass('room');
                    finished = false;
                }

                if (awaiting) { awaiting = false; change_status = true; }
            }

            if (changes.sound != room_config.sound) {
                room_config.sound = changes.sound;
            }

            if (change_status || (changes.room_status != room_config.room_status)) {
                let isPaused = false;
                if (changes.room_status == 'P') { isPaused = true; }
                handle_change_status(isPaused);
            }

            room_config = changes;
        }
    }

    function get_room_info () {
        $.ajax({
            'url': '<?=URL?>/api/room.php?info=<?=$room['room_id']?>',
            'method': 'GET',
            success: (data, status) => {
                data = JSON.parse(data);

                if (data.status == 200) {
                    let check_online = [];
                    data.data.members.forEach((member) => {
                        if (member.online) {
                            check_online.push(member.member_name);
                        }
                    })
                    apply_online_difference(check_online);

                    config = data.data.config;
                    var changes = { sound: config.room_sound_type, work_time: config.room_work_time, pause_time: config.room_pause_time, round: config.room_round, configure_date: config.room_configure, work_end: config.work_end, pause_start: config.pause_start, room_status: config.room_status };
                    apply_change_difference(changes);

                }
            }
        });
    }
    setInterval(() => {
        get_room_info();
    }, 5000);

    get_room_info();
    
    var timer_datetime = new Date("<?=$room['room_work_end_date']?>").getTime();
    var coun = setInterval(function() {
        if (!finished) {

            if (!awaiting) {
                
                var now = new Date().getTime();
                var timer_datetime = new Date(room_config.work_end).getTime();
    
                var difference = timer_datetime - now;
    
                var hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                
                if (minutes < 10) { minutes = "0"+minutes; } 
                if (hours < 10) { hours = "0"+hours; } 
                $('.page-counter h1').text(`${hours}:${minutes}`);
    
                if (difference < 0) {
                    finished = true;
                    $('.page-body').removeClass('room').removeClass('config').addClass('finished');
                    $('.page-counter h1').text("");
                }

            }
        }
    }, 1000);



</script>